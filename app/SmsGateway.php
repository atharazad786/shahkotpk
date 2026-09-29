<?php

class SmsGateway {
    private string $apiKey;
    private string $apiSecret;
    private string $senderId;

    public function __construct() {
        $this->apiKey = setting('sms_api_key', '');
        $this->apiSecret = setting('sms_api_secret', '');
        $this->senderId = setting('sms_sender_id', 'ShahkotPK');
    }

    public function sendOTP(string $phone, string $otp): bool {
        $message = "Your ShahkotPK verification code is: $otp. Do not share this with anyone.";
        return $this->sendMessage($phone, $message);
    }

    public function sendMessage(string $phone, string $message): bool {
        if (!$this->apiKey) {
            // Log mock send if API key not set
            error_log("Mock SMS Sent to $phone: $message");
            return true;
        }

        // Example cURL for a generic SMS Provider
        $url = "https://api.smsprovider.com/v1/send";
        $payload = json_encode([
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'to' => $phone,
            'from' => $this->senderId,
            'text' => $message
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return true;
        }

        error_log("SMS Gateway Error: " . $response);
        return false;
    }
}
