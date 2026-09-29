<?php
declare(strict_types=1);

/**
 * User Ratings and Reviews Manager
 */
class ReviewManager {
    /**
     * Submit a new review
     */
    public static function submitReview(int $userId, string $entityType, int $entityId, int $rating, string $comment): bool {
        if ($rating < 1 || $rating > 5) return false;
        
        try {
            // Using a generic mock table structure for reviews
            $stmt = db()->prepare("INSERT INTO reviews (user_id, entity_type, entity_id, rating, comment, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            return $stmt->execute([$userId, $entityType, $entityId, $rating, htmlspecialchars($comment)]);
        } catch (Throwable $e) {
            // Table might not exist, mock fail gracefully
            error_log("Review error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get average rating and total count
     */
    public static function getAggregateRating(string $entityType, int $entityId): array {
        try {
            $stmt = db()->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE entity_type = ? AND entity_id = ?");
            $stmt->execute([$entityType, $entityId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'average' => round((float)($res['avg_rating'] ?? 0), 1),
                'total' => (int)($res['total_reviews'] ?? 0)
            ];
        } catch (Throwable $e) {
            return ['average' => 0.0, 'total' => 0];
        }
    }
}
