<?php
class AuthMiddleware {
    public static function checkAuth($redirect_to = '/login') {
        if (!Session::isLoggedIn()) {
            Helper::redirect($redirect_to);
            exit;
        }
    }

    public static function checkPermission($permission, $redirect_to = '/') {
        self::checkAuth();
        
        if (!Session::hasPermission($permission)) {
            $_SESSION['error'] = 'You do not have permission to access this page';
            Helper::redirect($redirect_to);
            exit;
        }
    }

    public static function checkRole($allowed_roles, $redirect_to = '/') {
        self::checkAuth();
        
        $user_role = Session::getUserRole();
        if (!in_array($user_role, $allowed_roles)) {
            $_SESSION['error'] = 'Access denied for your role';
            Helper::redirect($redirect_to);
            exit;
        }
    }

    // Check if user is already logged in (for login page)
    public static function redirectIfLoggedIn($redirect_to = '/') {
        if (Session::isLoggedIn()) {
            Helper::redirect($redirect_to);
            exit;
        }
    }
}
?>