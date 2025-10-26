<?php
class Session {
    public static function start() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        self::start();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    public static function destroy() {
        self::start();
        session_destroy();
    }

    public static function isLoggedIn() {
        return self::get('user_id') !== null;
    }

    public static function getUserRole() {
        return self::get('user_role');
    }

    public static function hasPermission($permission) {
        $user_permissions = self::get('user_permissions');
        return in_array($permission, $user_permissions);
    }
}
?>