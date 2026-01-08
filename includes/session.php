<?php

if (!defined('SAJILO_APP')) {
    die('Direct access not permitted');
}

/**
 * Check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool
 */
function is_admin() {
    return is_logged_in() && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if user is seller
 * @return bool
 */
function is_seller() {
    return is_logged_in() && ($_SESSION['user_role'] === 'seller' || $_SESSION['user_role'] === 'admin');
}

/**
 * Get current user ID
 * @return int|null
 */
function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 * @return array|null
 */
function get_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    $sql = "SELECT * FROM users WHERE user_id = ?";
    return db_fetch_one($sql, "i", [get_user_id()]);
}

/**
 * Login user
 * @param array $user User data
 */
function login_user($user) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['is_verified'] = $user['is_verified'];
    $_SESSION['last_activity'] = time();
}

/**
 * Logout user
 */
function logout_user() {
    session_unset();
    session_destroy();
}

/**
 * Require login
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash('error', 'Please login to continue');
        redirect(BASE_URL . '/auth/login.php');
    }
}

/**
 * Require admin
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        set_flash('error', 'Access denied');
        redirect(BASE_URL . '/index.php');
    }
}

/**
 * Require seller
 */
function require_seller() {
    require_login();
    if (!is_seller()) {
        set_flash('error', 'Seller access required');
        redirect(BASE_URL . '/index.php');
    }
}

// Check session timeout
if (is_logged_in()) {
    $last_activity = $_SESSION['last_activity'] ?? 0;
    if (time() - $last_activity > SESSION_TIMEOUT) {
        logout_user();
        set_flash('warning', 'Session expired. Please login again');
        redirect(BASE_URL . '/auth/login.php');
    }
    $_SESSION['last_activity'] = time();
}
?>