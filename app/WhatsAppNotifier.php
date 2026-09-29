<?php
declare(strict_types=1);

/**
 * WhatsApp Integration / Push Notification Module
 */
class WhatsAppNotifier {
    
    // Simulate sending a WhatsApp message
    public static function sendOrderAlert(string $phone, string $orderId, float $total): bool {
        $msg = "ShahkotPK: Your order #{$orderId} of Rs. {$total} has been confirmed. Thank you for shopping locally!";
        return self::pushToProvider($phone, $msg);
    }

    public static function sendAppointmentAlert(string $phone, string $doctor, string $time): bool {
        $msg = "ShahkotPK Health: Your appointment with Dr. {$doctor} is confirmed for {$time}.";
        return self::pushToProvider($phone, $msg);
    }

    private static function pushToProvider(string $phone, string $message): bool {
        // Here you would integrate with a WhatsApp Business API like Twilio, Meta Graph API, or local PK providers
        $token = setting('whatsapp_api_token', '');
        
        if (!$token) {
            // Mock mode
            error_log("[MOCK WHATSAPP] To $phone: $message");
            return true;
        }

        // Real API Call
        $payload = json_encode([
            "messaging_product" => "whatsapp",
            "to" => $phone,
            "type" => "text",
            "text" => ["body" => $message]
        ]);

        $ch = curl_init("https://graph.facebook.com/v17.0/PHONE_NUMBER_ID/messages");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $token,
            "Content-Type: application/json"
        ]);
        
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $code === 200;
    }
}
