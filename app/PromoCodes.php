<?php
declare(strict_types=1);

/**
 * Dynamic Promo/Loyalty Codes System
 */
class PromoCodes {
    // Mock database of active codes
    private static $activeCodes = [
        'SHAHKOT50' => ['type' => 'fixed', 'value' => 50, 'min_cart' => 500, 'uses_left' => 100],
        'WELCOME20' => ['type' => 'percent', 'value' => 20, 'min_cart' => 0, 'uses_left' => 999],
        'FREEDELIVERY' => ['type' => 'shipping', 'value' => 0, 'min_cart' => 1000, 'uses_left' => 50]
    ];

    /**
     * Validate and calculate discount for a cart total
     * Returns ['valid' => true, 'discount' => amount, 'message' => '...']
     */
    public static function applyCode(string $code, float $cartTotal): array {
        $code = strtoupper(trim($code));
        
        if (!isset(self::$activeCodes[$code])) {
            return ['valid' => false, 'discount' => 0, 'message' => 'Invalid or expired promo code.'];
        }
        
        $promo = self::$activeCodes[$code];
        
        if ($promo['uses_left'] <= 0) {
            return ['valid' => false, 'discount' => 0, 'message' => 'This promo code has reached its usage limit.'];
        }
        
        if ($cartTotal < $promo['min_cart']) {
            return ['valid' => false, 'discount' => 0, 'message' => "Minimum order amount of Rs. {$promo['min_cart']} required for this code."];
        }
        
        $discount = 0;
        if ($promo['type'] === 'fixed') {
            $discount = min($promo['value'], $cartTotal);
        } elseif ($promo['type'] === 'percent') {
            $discount = ($cartTotal * $promo['value']) / 100;
        } elseif ($promo['type'] === 'shipping') {
            // Logic for free shipping (returns custom discount value for frontend to handle)
            return ['valid' => true, 'discount' => 0, 'type' => 'shipping', 'message' => 'Free delivery applied!'];
        }
        
        return [
            'valid' => true,
            'discount' => round($discount, 2),
            'message' => "Promo code applied successfully! You saved Rs. {$discount}."
        ];
    }
}
