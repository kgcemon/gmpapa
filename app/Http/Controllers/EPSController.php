<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\EpsHelper;
use Illuminate\Support\Facades\Http;

class EPSController extends Controller
{
    private $baseUrl = "https://pgapi.eps.com.bd/v1";
    private $hashKey = "FMUNISHOY2lWZXDH0009890VertexTraders";
    private $userName = "support@vertexbazaar.com";
    private $password = "VertexTraders#09";
    private $merchantId = "7df25e31-cc32-47dc-a1e3-a4bf651b3471";
    private $storeId = "f48574ea-3f1e-43eb-99e4-3fe5db1ef140";

    // Step 1: Get Token
    public function getToken()
    {
        $xHash = EpsHelper::generateHash($this->hashKey, $this->userName);

        $response = Http::withHeaders([
            'x-hash' => $xHash,
        ])->post($this->baseUrl . '/Auth/GetToken', [
            'userName' => $this->userName,
            'password' => $this->password,
        ]);

        return $response->json();
    }

    // Step 2: Initialize Payment
    public function initializePayment(Request $request)
    {
        $tokenData = $this->getToken();

        $token = $tokenData['token'] ?? null;

        if (!$token) {
            return response()->json(['error' => 'Token not found'], 500);
        }

        $merchantTransactionId = uniqid('txn_');
        $xHash = EpsHelper::generateHash($this->hashKey, $merchantTransactionId);

        $body = [
            "merchantId" => $this->merchantId,
            "storeId" => $this->storeId,
            "CustomerOrderId" => uniqid('order_'),
            "merchantTransactionId" => $merchantTransactionId,
            "transactionTypeId" => 1, // 1=Web
            "totalAmount" => 5.00,
            "successUrl" => route('eps.success'),
            "failUrl" => route('eps.fail'),
            "cancelUrl" => route('eps.cancel'),
            "customerName" => "Test User",
            "customerEmail" => "test@example.com",
            "customerAddress" => "Dhaka",
            "customerCity" => "Dhaka",
            "customerState" => "Dhaka",
            "customerPostcode" => "1200",
            "customerCountry" => "BD",
            "customerPhone" => "01700000000",
            "productName" => "Demo Product",
        ];

        $response = Http::withHeaders([
            'x-hash' => $xHash,
            'Authorization' => 'Bearer ' . $token,
        ])->post($this->baseUrl . '/EPSEngine/InitializeEPS', $body);

        return $response->json();
    }

    // Step 3: Verify Transaction
    public function verifyTransaction($merchantTransactionId)
    {
        $xHash = EpsHelper::generateHash($this->hashKey, $merchantTransactionId);

        $tokenData = $this->getToken();
        $token = $tokenData['token'] ?? null;

        $url = $this->baseUrl . "/EPSEngine/CheckMerchantTransactionStatus?merchantTransactionId=$merchantTransactionId";

        $response = Http::withHeaders([
            'x-hash' => $xHash,
            'Authorization' => 'Bearer ' . $token,
        ])->get($url);

        return $response->json();
    }

    // Success/Fail/Cancel Redirects
    public function success(Request $request) {

        $id = $request->MerchantTransactionId;

        return "Payment Successful! $id";
    }
    public function fail() { return "Payment Failed!"; }
    public function cancel() { return "Payment Cancelled!"; }
}
