<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query();

        if ($request->filled('filter')) {
            $query->where('status', $request->filter);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', '=', $searchTerm)
                    ->orWhere('name', 'LIKE', "%$searchTerm%")
                    ->orWhere('phone', 'LIKE', "%$searchTerm%")
                    ->orWhere('transaction_id', 'LIKE', "%$searchTerm%");
            });
        }

        $orders = $query->orderByDesc('id')->paginate(10)->appends($request->all());

        return view('admin.orders.index', compact('orders'));
    }

    public function updateStatus(Request $request)
    {
        $action = $request->input('action');
        $orderIds = $request->input('order_ids', []);
        $singleOrderId = $request->input('order_id');
        $singleStatus = $request->input('status');

        try {
            // Single order update
            if ($singleOrderId && $singleStatus) {
                $order = Order::findOrFail($singleOrderId);
                $order->status = $singleStatus;
                $order->save();

                return redirect()->back()->with('success', 'Order status updated to ' . $singleStatus);
            }

            // Bulk action update
            if (empty($orderIds) || !$action) {
                return redirect()->back()->with('error', 'Please select at least one order and an action.');
            }

            switch ($action) {
                case 'delete':
                    Order::whereIn('id', $orderIds)->delete();
                    return redirect()->back()->with('success', 'Selected orders deleted successfully.');

                case 'processing':
                case 'delivered':
                case 'cancelled':
                    Order::whereIn('id', $orderIds)->update(['status' => $action]);
                    return redirect()->back()->with('success', 'Selected orders updated to "' . ucfirst($action) . '".');

                default:
                    return redirect()->back()->with('error', 'Invalid action selected.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
