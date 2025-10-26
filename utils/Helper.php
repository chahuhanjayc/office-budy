<?php
class Helper {
    // Generate random string for ticket numbers, etc.
    public static function generateRandomString($length = 10) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    // Generate ticket number
    public static function generateTicketNumber() {
        return 'TICKET-' . date('Ymd') . '-' . self::generateRandomString(6);
    }

    // Generate RMA number
    public static function generateRMANumber() {
        return 'RMA-' . date('Ymd') . '-' . self::generateRandomString(6);
    }

    // Format date for display
    public static function formatDate($date) {
        if(empty($date) || $date == '0000-00-00') return 'N/A';
        return date('M j, Y', strtotime($date));
    }

// Redirect function
public static function redirect($url) {
    // If it's a relative path, prepend the base URL
    if (strpos($url, 'http') !== 0 && strpos($url, '//') !== 0) {
        $url = BASE_URL . $url;
    }
    header("Location: $url");
    exit();
}

    // Sanitize input
    public static function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)));
    }
}
?>