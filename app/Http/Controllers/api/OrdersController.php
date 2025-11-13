<?php

namespace App\Http\Controllers\api;

use App\Helpers\EpsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Item;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentSms;
use App\Models\Product;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;
use function Pest\Laravel\json;
use function PHPUnit\Framework\isEmpty;

class OrdersController extends Controller
{
    protected $walletService;
    protected $epsHelper;

    public function __construct(WalletService $walletService, EpsHelper $epsHelper)
    {
        $this->walletService = $walletService;
        $this->epsHelper = $epsHelper;
    }

    public function store(StoreOrderRequest $request)
    {

        // Step 1: Basic validation rules
        $rules = [
            'product_id'     => 'required|exists:products,id',
            'items_id'        => 'required|exists:items,id',
            'customer_data'  => 'required',
            'method_id'     => 'required|exists:payment_methods,id',
            'transaction_id' => 'nullable|string',
            'number' => 'nullable|string',
            'quantity' => 'required|numeric|min:1',
        ];

        $validated = $request->validate($rules);

        $user          = auth('sanctum')->user();
        $product       = Product::find($validated['product_id']);
        $item          = Item::find($validated['items_id']);
        $paymentMethod = PaymentMethod::find($validated['method_id']);
        $paymentUrl = null;


        if (!$product || !$item || !$paymentMethod) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid product, item, or payment method',
            ], 404);
        }

        try {

            return DB::transaction(function () use ($validated, $user, $product, $item, $paymentMethod, $request) {
                $total = $item->price * $validated['quantity'];

                // Duplicate trxID চেক (only if provided)
                if (!empty($validated['transaction_id'])) {
                    $checkDuplicate = Order::where('transaction_id', $validated['transaction_id'])->count();
                    if ($checkDuplicate > 0) {
                        return response()->json([
                            'status'  => false,
                            'message' => 'This transaction ID is already used.',
                        ], 409);
                    }
                }

                $order = new Order();
                $order->quantity      = $request['quantity'] ?? 1;
                $order->total         = $total;
                $order->product_id    = $validated['product_id'];
                $order->item_id       = $validated['items_id'];
                $order->customer_data = $validated['customer_data'];
                $order->payment_method = $validated['method_id'];

                if ($user) {
                    $order->user_id = $user->id;
                    $order->name    = $user->name;
                    $order->email   = $user->email;
                } else {
                    $order->email = $request['email'] ?? null;
                    $order->phone  = $request['phone'] ?? null;
                    $order->user_id = null;
                    $order->name    = $request->input('name') ?? null;
                }

                // ✅ Wallet Payment
                if ($paymentMethod->method === 'Wallet') {
                    if (!$user) {
                        return response()->json([
                            'status'  => false,
                            'message' => "Wallet payment requires login.",
                        ], 401);
                    }

                    if ($user->wallet < $total) {
                        return response()->json([
                            'status'  => false,
                            'message' => "আপনার ওয়ালেটে যথেষ্ট টাকা নেই। দয়া করে টাকা এড করে আবার চেষ্টা করুন।",
                        ]);
                    }

                    $user->wallet -= $total;
                    WalletTransaction::create([
                        'user_id'   => $user->id,
                        'amount'    => $total,
                        'type'      => 'debit',
                        'description' => "Order for $item->name",
                        'status'    => 1,
                    ]);
                    $user->save();
                    $order->status = 'processing';

                } else {
                    $paySMS = null;
                    if (!empty($validated['transaction_id'])) {
                        $paySMS = PaymentSms::where('trxID', $validated['transaction_id'])
                            ->where('amount', '>=', (integer)$total)
                            ->where('status', 0)
                            ->first();
                    }

                    if ($paySMS != null) {
                        $order->transaction_id = $paySMS->trxID;
                        $order->number         = $paySMS->number;
                        $paySMS->status = 1;
                        $paySMS->save();
                        $order->status         = 'processing';
                    }else if ($validated['transaction_id'] == null &&
                        $validated['number'] == null &&
                        $paymentMethod->method === 'eps') {
                        $eps = $this->epsHelper->initializePayment();
                        $order->status  = 'Pending Payment';

                        if ($eps['TransactionId'] !== null) {
                            $order->transaction_id = $eps['TransactionId'];
                            //$paymentUrl = $eps['PaymentUrl'];
                            return $eps;
                        }

                    } else {
                        if (empty($validated['transaction_id']) || empty($validated['number'])) {
                            return response()->json([
                                'status'  => false,
                                'message' => 'Transaction ID and payment number are required for this payment method.',
                            ], 422);
                        }

                        $order->transaction_id = $validated['transaction_id'];
                        $order->number         = $validated['number'];
                        $order->status         = 'hold';
                    }
                }

                $order->save();

                return response()->json([
                    'status'  => true,
                    'message' => 'Order created successfully',
                    'order'   => $order
                ], 201);

            });

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }
}
