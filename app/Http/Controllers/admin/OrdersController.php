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
                $q->where('id', $searchTerm)
                    ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('transaction_id', 'LIKE', "%{$searchTerm}%");
            });
        }

        $orders = $query->orderByDesc('id')->paginate(10)->appends($request->all());
        return view('admin.orders.index', compact('orders'));
    }

    public function show($id)
    {
        // Fetch the order, optionally with related user/product/item data
        $order = Order::findOrFail($id);

        // Pass the order to the Blade view
        return view('admin.orders.view', compact('order'));
    }



    public function update(Request $request, Order $order)
    {
        try {
            $request->validate([
                'status' => 'required|in:hold,processing,Delivery Running,delivered,cancelled',
            ]);

            if($request->input('order_note')) $order->order_note = $request->input('order_note');

            $order->status = $request->status;
            $order->save();

            return redirect()->route('admin.orders.index')
                ->with('success', 'Order status updated successfully.');
        }catch (\Exception $exception){
            return back()->with('error', $exception->getMessage());
        }
    }

    public function editFrom($id){

        $order = Order::findOrFail($id);

        $statuses = ['hold', 'processing', 'Delivery Running', 'delivered', 'cancelled'];

        return view('admin.orders.edit', compact('order', 'statuses'));
    }

    public function edit(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);

            // সব field update
            $order->update($request->input());

            return redirect()->route('admin.orders.index')
                ->with('success', '✅ Order updated successfully.');
        }catch (\Exception $exception){
            return back()->with('error', $exception->getMessage());
        }
    }


    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'order_ids' => 'required|array'
        ]);

        $orders = Order::whereIn('id', $request->order_ids);

        switch ($request->action) {
            case 'delivered':
                $orders->update(['status' => 'delivered']);
                break;
            case 'processing':
                $orders->update(['status' => 'processing']);
                break;
            case 'cancelled':
                $orders->update(['status' => 'cancelled']);
                break;
            case 'delete':
                $orders->delete();
                break;
        }

        return redirect()->back()->with('success', 'Bulk action applied successfully.');
    }


}
