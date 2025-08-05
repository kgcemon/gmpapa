<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class WebHooksController extends Controller
{
    public function OrderUpdate(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $status = $data->status;
        $message = $data->message;
        $uid = $data->uid;
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
