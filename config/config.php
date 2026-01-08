<?php

// Define application constant
define('SAJILO_APP', true);

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
    session_start();
}

// SITE CONFIGURATION

define('SITE_NAME', 'Sajilo');
define('SITE_TITLE', 'Sajilo - Your Trusted Digital Marketplace in Nepal');
define('SITE_DESCRIPTION', 'Shop with confidence from verified sellers across Nepal');
define('SITE_KEYWORDS', 'online shopping nepal, e-commerce, marketplace, buy sell nepal');

// PATH CONFIGURATION

define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', 'http://localhost/project'); // Change in production

define('INCLUDES_PATH', BASE_PATH . '/includes');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('ASSETS_PATH', BASE_PATH . '/assets');

define('UPLOADS_URL', BASE_URL . '/uploads');
define('ASSETS_URL', BASE_URL . '/assets');

// ============================================
// UPLOAD CONFIGURATION
// ============================================

define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// ============================================
// PAGINATION CONFIGURATION
// ============================================

define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 10);
define('USERS_PER_PAGE', 20);

// ============================================
// SECURITY CONFIGURATION
// ============================================

define('PASSWORD_MIN_LENGTH', 6);
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// ============================================
// EMAIL CONFIGURATION
// ============================================

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Change this
define('SMTP_PASSWORD', 'your-app-password'); // Change this
define('SMTP_FROM_EMAIL', 'noreply@sajilo.com.np');
define('SMTP_FROM_NAME', 'Sajilo Marketplace');

// ============================================
// PAYMENT CONFIGURATION (Simulated - No API)
// ============================================

// Khalti Configuration (Simulated)
define('KHALTI_PUBLIC_KEY', 'test_public_key_xxxxx');
define('KHALTI_SECRET_KEY', 'test_secret_key_xxxxx');

// eSewa Configuration (Simulated)
define('ESEWA_MERCHANT_ID', 'EPAYTEST');
define('ESEWA_SECRET_KEY', '8gBm/:&EnhH.1/q');

// Payment Gateway Status
define('PAYMENT_GATEWAY_MODE', 'simulation'); // simulation or live

// ============================================
// BUSINESS RULES
// ============================================

define('MIN_ORDER_AMOUNT', 100); // Minimum order amount in Rs
define('SHIPPING_CHARGE', 100); // Flat shipping charge
define('FREE_SHIPPING_THRESHOLD', 5000); // Free shipping above this amount
define('COD_CHARGE', 50); // Cash on delivery charge

// ============================================
// CONTACT INFORMATION
// ============================================

define('CONTACT_EMAIL', 'support@sajilo.com.np');
define('CONTACT_PHONE', '+977-1-5970000');
define('CONTACT_ADDRESS', 'United College, Kathmandu, Nepal');

// ============================================
// SOCIAL MEDIA
// ============================================

define('FACEBOOK_URL', 'https://facebook.com/sajilo');
define('TWITTER_URL', 'https://twitter.com/sajilo');
define('INSTAGRAM_URL', 'https://instagram.com/sajilo');

// ============================================
// ERROR HANDLING
// ============================================

// Development mode - show errors (set to false in production)
define('DEVELOPMENT_MODE', true);

if (DEVELOPMENT_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/error.log');
}

// ============================================
// TIMEZONE
// ============================================

date_default_timezone_set('Asia/Kathmandu');

// ============================================
// AUTOLOAD FUNCTIONS
// ============================================

// Include required files
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/session.php';

// Include database connection
require_once __DIR__ . '/database.php';

// ============================================
// CSRF TOKEN GENERATION
// ============================================

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Get CSRF token
 * 
 * @return string CSRF token
 */
function get_csrf_token() {
    return $_SESSION['csrf_token'] ?? '';
}

/**
 * Generate CSRF input field
 * 
 * @return string HTML input field
 */
function csrf_field() {
    $token = get_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return bool True if valid
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ============================================
// PROJECT INFORMATION
// ============================================

define('PROJECT_AUTHORS', 'Aditya Rai & Prakash Thapa');
define('PROJECT_SUPERVISOR', 'Samir Thapa');
define('PROJECT_INSTITUTION', 'United College, Tribhuvan University');
define('PROJECT_YEAR', '2025');
define('PROJECT_VERSION', '1.0.0');
?>