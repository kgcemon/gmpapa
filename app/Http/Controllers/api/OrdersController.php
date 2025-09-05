<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Item;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Support\Facades\Cache;

class OrdersController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function store(StoreOrderRequest $request)
    {
        try {
            $status = 'hold';
            $validated = $request->validated();
            $user = auth('sanctum')->user();

            $payments = PaymentMethod::where('id', $validated['method_id'])->first();
            if ($payments == null) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Payment method not found',
                ]);
            }

            $product = Product::find($validated['product_id']);
            if (!$product) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Product not found',
                ], 404);
            }

            if ($validated['items_id']) {
                $item = Item::find($validated['items_id']);
                if (!$item) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Item not found',
                    ]);
                }
            } else {
                $item = null;
            }

            $totalPrice = $item ? $item->price * $validated['quantity'] : $product->price * $validated['quantity'];


            if ($payments->method == 'Wallet' && $user) {
                $deducted = $this->walletService->deductBalance($user, $totalPrice);
                if (!$deducted) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Insufficient wallet balance',
                    ], 400);
                }
                WalletTransaction::create([
                    'user_id'   => $user->id,
                    'amount'    => $totalPrice,
                    'type'      => 'debit',
                    'description' => 'order'. $item->name ?? '',
                    'status'    => 1,
                ]);

                $status = 'processing';
            }

            $order = new Order();
            $order->product_id     = $product->id;
            $order->quantity       = $validated['quantity'];
            $order->total          = $totalPrice;
            $order->item_id        = $item ? $item->id : null;
            $order->customer_data  = $validated['customer_data'];
            $order->others_data    = $validated['others'] ?? null;
            $order->payment_method = $payments->id;
            $order->transaction_id = $validated['transaction_id'] ?? null;
            $order->number         = $validated['number'] ?? null;

            if ($user) {
                $order->user_id = $user->id;
                $order->name    = $user->name;
                $order->phone   = $user->phone;
                $order->email   = $user->email;
            } else {
                $order->user_id = null;
                $order->name    = $validated['name'];
                $order->phone   = $validated['phone'];
                $order->email   = $validated['email'] ?? null;
            }
            $order->status = $status;
            $order->save();
            $order->load('product');

            Cache::forget('dashboardData');

            return response()->json([
                'status'       => true,
                'message'      => 'Order placed successfully.',
                'customer_data'=> $order->customer_data,
                'others_data'  => $order->others_data ?? null,
                'order'        => $order,
            ], 201);

        } catch (\Exception $exception) {
            return response()->json([
                'status'  => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
