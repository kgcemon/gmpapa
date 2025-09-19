<?php

namespace App\Http\Controllers\api\auth;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class UserOrderController extends Controller
{
    public function userOrder(Request $request)
    {
        $user = $request->user();
        $orders = Order::where('user_id',$user->id)->with('paymentMethod','item')->orderBy('created_at','desc')->paginate(15);
        return response()->json([
            'status'  => true,
            'message' => 'order list',
            'data'    => $orders->items(),
            'total'   => $orders->total(),
            'current' => $orders->currentPage(),
            'lastpage' => $orders->lastPage(),
            'first'    => $orders->firstItem(),
            'from'     => $orders->firstItem(),
            'perPage'  => $orders->perPage(),
        ]);
    }
}
