<?php
class DepartmentMiddleware {
    
    // Check if user can access department-specific content
    public static function checkDepartmentAccess($department_id, $redirect_to = '/') {
        $user_role = Session::getUserRole();
        $user_dept = Session::get('user_department');
        
        // Super Admin and Admin have access to all departments
        if ($user_role == 1 || $user_role == 2) {
            return true;
        }
        
        // Managers and Vendors can only access their assigned department
        if (($user_role == 3 || $user_role == 4) && $user_dept != $department_id) {
            $_SESSION['error'] = 'Access denied for your department';
            Helper::redirect($redirect_to);
            exit;
        }
        
        return true;
    }
    
    // Filter data based on user's department
    public static function filterByDepartment($query, $department_field = 'department_id') {
        $user_role = Session::getUserRole();
        $user_dept = Session::get('user_department');
        
        // Super Admin and Admin see all data
        if ($user_role == 1 || $user_role == 2) {
            return $query;
        }
        
        // Managers and Vendors only see their department's data
        if ($user_role == 3 || $user_role == 4) {
            if ($user_dept) {
                $where_keyword = (stripos($query, 'WHERE') !== false) ? 'AND' : 'WHERE';
                $query .= " $where_keyword $department_field = $user_dept";
            } else {
                // If no department assigned, show nothing
                $where_keyword = (stripos($query, 'WHERE') !== false) ? 'AND' : 'WHERE';
                $query .= " $where_keyword 1=0"; // Force no results
            }
        }
        
        // Regular users only see their own created items
        if ($user_role == 5) {
            $user_id = Session::get('user_id');
            $where_keyword = (stripos($query, 'WHERE') !== false) ? 'AND' : 'WHERE';
            $query .= " $where_keyword t.created_by = $user_id";
        }
        
        return $query;
    }
}
?>