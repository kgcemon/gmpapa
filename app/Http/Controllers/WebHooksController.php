<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class WebHooksController extends Controller
{
    public function OrderUpdate(Request $request)
    {
        $status = $request->input('status');
        $message = $request->input('message');
        $uid = $request->input('uid');
        $order = Order::where('order_note', $uid)->first();
        if ($order) {
            if ($status) {
                $order->status = 'delivered';
                $order->order_note = $message;
                $order->save();
            }else{
                $order->status = 'processing';
                $order->order_note = $message;
                $order->save();
            }
        }
    }
}
