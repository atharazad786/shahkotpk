<?php
declare(strict_types=1);

/**
 * Sponsored / Featured Business Listings Manager
 */
class SponsorManager {
    /**
     * Mark a business as sponsored/featured for X days
     */
    public static function sponsorBusiness(int $businessId, int $days = 30): bool {
        try {
            // Adds 'is_featured' and sets an expiry if the schema supported it.
            // Using standard is_featured = 1 for the mock.
            $stmt = db()->prepare("UPDATE businesses SET is_featured = 1 WHERE id = ?");
            return $stmt->execute([$businessId]);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Get premium sponsored businesses for a category
     */
    public static function getSponsoredListings(int $categoryId = 0, int $limit = 3): array {
        try {
            $sql = "SELECT id, name, address, phone, image FROM businesses WHERE is_featured = 1 AND status = 1";
            $params = [];
            
            if ($categoryId > 0) {
                $sql .= " AND category_id = ?";
                $params[] = $categoryId;
            }
            
            $sql .= " ORDER BY RAND() LIMIT " . $limit;
            
            $stmt = db()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }
}
