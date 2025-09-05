<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(Request $request){
        $user = $request->user();
        if (!$user){
            $payment_methods = PaymentMethod::all();
        }else{
            $payment_methods = PaymentMethod::where('method', '!=', 'Wallet')->get();
        }

        return response()->json([
            'status' => true,
            'data' => $payment_methods,
        ]);
    }
}
