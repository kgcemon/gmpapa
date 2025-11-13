<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EpsHelper
{
    /**
     * Generate EPS-compliant HMAC-SHA512 hash (Base64 encoded)
     *
     * @param string $key  EPS provided hash key (from merchant panel)
     * @param string $data Data to hash (e.g., username or merchantTransactionId)
     * @return string Base64 encoded HMAC hash
     */

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
    public function initializePayment(
        $product,
        $amount,
        $name,
        $email,
        $phone,
        $orderId,
    )
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
            "CustomerOrderId" => $orderId,
            "merchantTransactionId" => $merchantTransactionId,
            "transactionTypeId" => 1,
            "totalAmount" => $amount,
            "successUrl" => route('eps.success'),
            "failUrl" => route('eps.fail'),
            "cancelUrl" => route('eps.cancel'),
            "customerName" => $name,
            "customerEmail" => $email ?? 'test@gmail.com',
            "customerAddress" => "Dhaka",
            "customerCity" => "Dhaka",
            "customerState" => "Dhaka",
            "customerPostcode" => "1200",
            "customerCountry" => "BD",
            "customerPhone" => $phone,
            "productName" => $product,
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


    public static function generateHash(string $key, string $data): string
    {
        // EPS requires UTF-8 encoded key
        $encodedKey = utf8_encode($key);

        // Generate raw binary HMAC-SHA512
        $hmac = hash_hmac('sha512', $data, $encodedKey, true);

        // Return Base64 encoded string
        return base64_encode($hmac);
    }



}
