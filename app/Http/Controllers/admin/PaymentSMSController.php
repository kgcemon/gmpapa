<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSms;
use Illuminate\Http\Request;

class PaymentSMSController extends Controller
{
    public function index(){
        $data = PaymentSMS::orderBy('id', 'desc')->paginate(10);
        return view('admin.paymentSms.sms', compact('data'));
    }


    public function search(Request $request)
    {
        $keywords = $request->get('search');
        $data = PaymentSms::where('trxID', 'like', '%' . $keywords . '%')->orderBy('id', 'desc')->paginate(10);
        return view('admin.paymentSms.sms', compact('data'));
    }

    public function SmsWhooks(Request $request)
    {
        $sms = $request->input();

    }
}
