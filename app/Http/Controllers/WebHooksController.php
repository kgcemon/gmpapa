<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class WebHooksController extends Controller
{
    public function OrderUpdate(Request $request)
    {
        // Decode JSON as associative array
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['uid'])) {
            return response()->json(['status' => false, 'message' => 'Invalid data'], 400);
        }

        $status = $data['status'] ?? null;
        $message = $data['message'] ?? null;
        $uid = $data['uid'];

        $order = Order::where('order_note', $uid)->first();

        if ($order) {
            if ($status) {
                $order->status = 'delivered';
            } else {
                $order->status = 'processing';
            }

            if ($message !== null) {
                $order->order_note = $message;
            }

            $order->save();

            return response()->json(['status' => true, 'message' => 'Order updated']);
        } else {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }
    }
}
