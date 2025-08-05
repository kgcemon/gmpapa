<?php

namespace App\Http\Controllers;

use App\Models\Code;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Pest\Support\Str;

class CronJobController extends Controller
{
    public function freeFireAutoTopUpJob()
    {
        $orders = Order::where('status', 'processing')->where('order_note', null)->whereHas('product', function ($query) {$query->where('is_auto', 1);})->get();

        try {
            foreach ($orders as $order) {
                $code = Code::where('item_id', $order->item_id)->where('status', 'unused')->first();
                if (!$code) {
                    continue;
                }
                $type = (Str::startsWith($code->code, 'UPBD') ? 1 : Str::startsWith($code, 'BDMB')) ? 2 : 1;
                $order->order_note = 'Delivery Running';
                $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'RA-SECRET-KEY' => 'kpDvM4m9AOTl0+4Gcnvm7a+VgLJFjSNvuDVC9Jl6wH/RxXJqqCb0RQ==',
                    ]
                )->post('https://autonow.codmshopbd.com/topup', [
                    "playerId" => $order->customer_data,
                    "denom" => "2",
                    "type" => $type,
                    "voucherCode" => $code->code,
                    "webhook" => "https://admin.gmpapa.com/api/auto-webhooks"
                ]);
                $data = json_decode($response->body());
                if ($response->successful()) {
                    $order->status = 'Delivery Running';
                    $order->order_note = $data->uid;
                    $code->status = 'used';
                    $code->order_id = $order->id;
                    $code->save();
                    $order->save();
                }
            }
            return 'Cron job run successfully';
        }catch (\Exception $exception){
            return $exception->getMessage();
        }
    }
}

