<?php

namespace App\Http\Controllers;

use App\Mail\SendPinsMail;
use App\Models\Api;
use App\Models\Code;
use App\Models\Order;
use App\Models\ShellSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CronJobController extends Controller
{
    public function freeFireAutoTopUpJob()
    {

        $lockFile = storage_path('locks/freefire_cron.lock');

//        if (file_exists($lockFile)) {
//            exit("Another instance is running.");
//        }

        file_put_contents($lockFile, getmypid());

        try {

            $orders = Order::where('status', 'processing')->whereNull('order_note')->limit(4)->get();

            $denomsForShell = ["108593", "108592", "108591", "108590", "108589", "108588", "LITE", "3D", "7D", "30D"];


            $uid = null;
            $api = Api::where('running', 1)
                ->where('updated_at', '<', now()->subMinutes(3))
                ->first();

            if ($api) $api->update(['running' => 0]);

            try {
                foreach ($orders as $order) {

                    if (in_array($order->item->denom, $denomsForShell)) {
                        $success = $this->shellsTopUp($order);
                        if ($success) {
                            DB::commit();
                        } else {
                            DB::rollBack();
                        }
                        continue;
                    }


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

                    if ($denom == null) {
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

                    $apiData = Api::where('type', 'auto')->where('status', 1)->where('running', 0)->first();

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
                     $uid = bin2hex(random_bytes(21));
                            try {
                                $response = Http::withHeaders([
                                    'Content-Type' => 'application/json',
//                                    'Accept' => 'application/json',
//                                    'RA-SECRET-KEY' => $apiData->key,
                                ])->post($apiData->url, [
//                                    "playerId"   => $order->customer_data,
//                                    "denom"      => $d,
//                                    "type"       => $type,
//                                    "voucherCode"=> $code->code,
//                                    "webhook"    => "https://admin.gmpapa.com/api/auto-webhooks",

                                    "playerid" => "$order->customer_data",
                                    "pacakge" => $this->denomToPkge($denom),
                                    "code" => "$code->code",
                                    "orderid" => $uid,
                                    "url" => "https://admin.gmpapa.com/api/auto-webhooks",
                                    "tgbotid" => "701657976",
                                    "shell_balance" => 28,
                                    "ourstock" => 1

                                ]);

                            }catch (\Exception $exception){$order->order_note = 'server error';}

                            $data = $response->json();
                            $order->status = 'Delivery Running';
                            $order->order_note = $uid ?? null;
                            $order->save();
                            $code->status = 'used';
                            $code->uid = $uid;
                            $code->order_id = $order->id;
                            if (empty($uid)){
                                $code->active = false;
                            }
                            $code->save();
                        }

                    $apiData->order_id = $uid ?? null;
                    $apiData->running = 0;
                    $apiData->save();

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

    public function denomToPkge($denom)
    {
        if ($denom == 0) {
            return 25;
        }
        if ($denom == 1) {
            return 50;
        }elseif ($denom == 2) {
            return 115;
        }elseif ($denom == 3) {
            return 240;
        }elseif ($denom == 4) {
            return 610;
        }elseif ($denom == 5) {
            return 1240;
        }elseif ($denom == 6) {
            return 1625;
        }elseif ($denom == 7) {
            return 161;
        }elseif ($denom == 8) {
            return 800;
        }

        return null;
    }


    public function shellsTopUp($order): bool
    {
        $denom = (string) $order->item->denom ?? '';
        $apiData = Api::where('type', 'auto')->where('status', 1)->where('running', 0)->first();
        $shellAcount = ShellSetting::where('servername', 'servername')->first() ?? null;
        $url =  $apiData->url;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ],)->post($url,[
                "playerid" => "$order->customer_data",
                "pacakge" => "$denom",
                "code" => "shell",
                "orderid" => $order->id,
                "url" => "https://admin.gmpapa.com/api/auto-webhooks",
                "username" => "$shellAcount->username",
                "password" => "$shellAcount->password",
                "autocode" => "$shellAcount->key",
                "tgbotid" => "701657976",
                "shell_balance" => 28,
                "ourstock" => 1,
            ]);
        }catch (\Exception $exception){
            return false;
        }
        if ($response->successful()) {
            $order->order_note = $order->id;
            $order->status = 'Delivery Running';
            $order->save();
            return true;
        }
        return false;
    }

}
