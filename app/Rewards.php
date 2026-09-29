<?php
declare(strict_types=1);

/**
 * ShahkotPK Gamification & Rewards System
 */
class Rewards {
    // Action constants
    public const ACTION_LOGIN = 'daily_login';
    public const ACTION_REVIEW = 'post_review';
    public const ACTION_REFERRAL = 'refer_friend';
    public const ACTION_CHECKIN = 'location_checkin';

    // Point values
    private const POINTS_MAP = [
        self::ACTION_LOGIN => 5,
        self::ACTION_REVIEW => 20,
        self::ACTION_REFERRAL => 50,
        self::ACTION_CHECKIN => 10
    ];

    /**
     * Add points to a user's wallet.
     * Needs a `reward_points` column in the `users` table.
     */
    public static function awardPoints(int $userId, string $action): bool {
        if (!isset(self::POINTS_MAP[$action])) return false;
        
        $points = self::POINTS_MAP[$action];
        
        try {
            // First check if column exists, if not this is a mock implementation
            $stmt = db()->prepare("UPDATE users SET reward_points = COALESCE(reward_points, 0) + ? WHERE id = ?");
            $stmt->execute([$points, $userId]);
            
            // Log the reward
            self::logTransaction($userId, $action, $points);
            return true;
        } catch (Throwable $e) {
            // Column might not exist, silently fail for the mock
            error_log("Rewards error: " . $e->getMessage());
            return false;
        }
    }

    private static function logTransaction(int $userId, string $action, int $points): void {
        // Ideally save to a reward_transactions table
        error_log("User $userId awarded $points points for $action.");
    }

    public static function getUserPoints(int $userId): int {
        try {
            $stmt = db()->prepare("SELECT reward_points FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            return (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }
}
