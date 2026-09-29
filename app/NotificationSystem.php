<?php
declare(strict_types=1);

/**
 * Real-time Notifications System
 */
class NotificationSystem {
    public static function createNotification(int $userId, string $title, string $message, string $link = '#'): bool {
        try {
            $stmt = db()->prepare("INSERT INTO notifications (user_id, title, message, link, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
            return $stmt->execute([$userId, $title, $message, $link]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public static function getUnreadCount(int $userId): int {
        try {
            $stmt = db()->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$userId]);
            return (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0; // Mock 0 if table missing
        }
    }

    public static function renderBellHtml(int $userId): string {
        $count = self::getUnreadCount($userId);
        $badge = $count > 0 ? "<span style='background:red;color:white;border-radius:50%;padding:2px 6px;font-size:10px;position:absolute;top:-5px;right:-10px;'>$count</span>" : "";
        return "<div class='notification-bell' style='position:relative;display:inline-block;cursor:pointer;margin-left:15px;font-size:20px;'>🔔$badge</div>";
    }
}
