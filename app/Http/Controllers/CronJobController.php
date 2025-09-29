<?php

namespace App\Http\Controllers;

use App\Mail\SendPinsMail;
use App\Models\Api;
use App\Models\Code;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CronJobController extends Controller
{
    public function freeFireAutoTopUpJob()
    {

        $lockFile = storage_path('locks/freefire_cron.lock');

        if (file_exists($lockFile)) {
            exit("Another instance is running.");
        }

        file_put_contents($lockFile, getmypid());

        try {

            $orders = Order::where('status', 'processing')->whereNull('order_note')->limit(4)->get();

            try {
                foreach ($orders as $order) {
                    DB::beginTransaction();

                    if ($order->product->tags == "gift") {
                        $success = $this->sendGiftCard($order);
                        if ($success) {
                            DB::commit();
                        } else {
                            DB::rollBack();
                        }

                        continue;
                    }

                    $order = Order::lockForUpdate()->find($order->id);

                    if ($order->status !== 'processing' || $order->order_note !== null) {
                        DB::rollBack();
                        continue;
                    }

                    $denom = (string) $order->item->denom ?? '';

                    if (empty($denom)) {
                        DB::rollBack();
                        continue;
                    }
                    $denoms = explode(',', $denom);

                    $allDenoms = [];
                    for ($i = 0; $i < $order->quantity; $i++) {
                        $allDenoms = array_merge($allDenoms, $denoms);
                    }

                    $counts = array_count_values($allDenoms);

                    $missing = [];

                    foreach ($counts as $value => $needed) {
                        $available = Code::where('denom', $value)->where('status', 'unused')->count();

                        if ($available < $needed) {
                            $missing[$value] = [
                                'needed'    => $needed,
                                'available' => $available
                            ];
                        }
                    }

                    if ($missing) {
                        DB::rollBack();
                        continue;
                    }

                    $apiData = Api::where('type', 'auto')->where('status', 1)->first();
                    if (!$apiData) {
                        DB::rollBack();
                        continue;
                    }

                 foreach ($allDenoms as $d) {

                            $code = Code::where('denom', $d)->where('status', 'unused')
                                ->lockForUpdate()
                                ->first();

                            if (!$code) {
                                DB::rollBack();
                                continue;
                            }
                            $type = (Str::startsWith($code->code, 'UPBD')) ? 2 : ((Str::startsWith($code->code, 'BDMB')) ? 1 : 1);

                            try {
                                $response = Http::withHeaders([
                                    'Content-Type' => 'application/json',
                                    'Accept' => 'application/json',
                                    'RA-SECRET-KEY' => $apiData->key,
                                ])->post($apiData->url, [
                                    "playerId"   => $order->customer_data,
                                    "denom"      => $d,
                                    "type"       => $type,
                                    "voucherCode"=> $code->code,
                                    "webhook"    => "https://admin.gmpapa.com/api/auto-webhooks"
                                ]);

                            }catch (\Exception $exception){$order->order_note = 'server error';}

                            $data = $response->json();
                            $uid = $data['uid'] ?? null;
                            $order->status = 'Delivery Running';
                            $order->order_note = $uid ?? null;
                            $order->save();
                            $code->status = 'used';
                            $code->uid = $uid ?? null;
                            $code->order_id = $order->id;
                            if (empty($uid)){
                                $code->active = false;
                            }
                            $code->save();
                        }


                    DB::commit();
                }

                return 'Cron job run successfully';
            } catch (\Exception $exception) {
                DB::rollBack();
                return $exception->getMessage();
            }
        }    finally {
            @unlink($lockFile);
        }
    }

    private function sendGiftCard($order): bool
    {
        // Lock the order row
        $order = Order::lockForUpdate()->find($order->id);

        if (!$order || $order->status === 'delivered') {
            return false; // already processed
        }

        DB::beginTransaction();
        try {
            $email = $order->email;
            $total = Code::where('item_id', $order->item_id)
                ->where('status', 'unused')
                ->lockForUpdate()
                ->count();

            if ($total < $order->quantity || !$email) {
                DB::rollBack();
                return false;
            }

            $codes = Code::where('item_id', $order->item_id)
                ->where('status', 'unused')
                ->lockForUpdate()
                ->limit($order->quantity)
                ->get();

            if ($codes->isEmpty()) {
                DB::rollBack();
                return false;
            }

            $pins = $codes->map(function ($code) use ($order) {
                return [
                    'pin'    => $code->code,
                    'name'   => $order->item->name,
                ];
            })->toArray();

            $pinsNote = collect($pins)->map(function ($pin) {
                return $pin['pin'] . "      \n    \n     ";
            })->implode("\n \n");

            // Update codes
            Code::whereIn('id', $codes->pluck('id'))->update([
                'status'   => 'used',
                'order_id' => $order->id,
            ]);

            // Update order
            $order->status = 'delivered';
            $order->order_note = $pinsNote;
            $order->save();

            DB::commit();

            // Mail send after commit
            try {
                Mail::to($email)->send(new SendPinsMail($order->name ?? 'Customer', $pins));
            } catch (\Exception $exception) {
                \Log::error("SendPinsMail failed for order {$order->id}: {$exception->getMessage()}");
            }

            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }


}
