<?php

// Prevent direct access
if (!defined('SAJILO_APP')) {
    die('Direct access not permitted');
}

/**
 * Sanitize input data
 * 
 * @param mixed $data Data to sanitize
 * @return mixed Sanitized data
 */
function sanitize($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize($value);
        }
        return $data;
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Clean string for output
 * 
 * @param string $string String to clean
 * @return string Cleaned string
 */
function clean($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Hash password securely
 * 
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 * 
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool True if password matches
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate random token
 * 
 * @param int $length Token length
 * @return string Random token
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Generate unique filename
 * 
 * @param string $original_name Original filename
 * @return string Unique filename
 */
function generate_unique_filename($original_name) {
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    return uniqid() . '_' . time() . '.' . $extension;
}

// ============================================
// VALIDATION FUNCTIONS
// ============================================

/**
 * Validate email address
 * 
 * @param string $email Email to validate
 * @return bool True if valid
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Nepal format)
 * 
 * @param string $phone Phone number
 * @return bool True if valid
 */
function validate_phone($phone) {
    // Nepal phone format: 98XXXXXXXX or 97XXXXXXXX (10 digits)
    return preg_match('/^(98|97)\d{8}$/', $phone);
}

/**
 * Validate password strength
 * 
 * @param string $password Password to validate
 * @return array Array with 'valid' boolean and 'message' string
 */
function validate_password($password) {
    $result = ['valid' => true, 'message' => ''];
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $result['valid'] = false;
        $result['message'] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long';
    }
    
    return $result;
}

/**
 * Validate image file
 * 
 * @param array $file $_FILES array element
 * @return array Array with 'valid' boolean and 'message' string
 */
function validate_image($file) {
    $result = ['valid' => true, 'message' => ''];
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        $result['valid'] = false;
        $result['message'] = 'No file uploaded';
        return $result;
    }
    
    // Check file size
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        $result['valid'] = false;
        $result['message'] = 'File size exceeds maximum allowed size of ' . (UPLOAD_MAX_SIZE / 1024 / 1024) . 'MB';
        return $result;
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, ALLOWED_IMAGE_TYPES)) {
        $result['valid'] = false;
        $result['message'] = 'Invalid file type. Only images are allowed';
        return $result;
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_IMAGE_EXTENSIONS)) {
        $result['valid'] = false;
        $result['message'] = 'Invalid file extension';
        return $result;
    }
    
    return $result;
}

// ============================================
// FILE UPLOAD FUNCTIONS
// ============================================

/**
 * Upload image file
 * 
 * @param array $file $_FILES array element
 * @param string $directory Upload directory (relative to UPLOADS_PATH)
 * @return array Array with 'success' boolean, 'filename' string, and 'message' string
 */
function upload_image($file, $directory = 'products') {
    $result = ['success' => false, 'filename' => '', 'message' => ''];
    
    // Validate image
    $validation = validate_image($file);
    if (!$validation['valid']) {
        $result['message'] = $validation['message'];
        return $result;
    }
    
    // Create directory if not exists
    $upload_dir = UPLOADS_PATH . '/' . $directory;
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $filename = generate_unique_filename($file['name']);
    $filepath = $upload_dir . '/' . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $result['success'] = true;
        $result['filename'] = $directory . '/' . $filename;
        $result['message'] = 'File uploaded successfully';
    } else {
        $result['message'] = 'Failed to upload file';
    }
    
    return $result;
}

/**
 * Delete image file
 * 
 * @param string $filename Filename relative to UPLOADS_PATH
 * @return bool True if deleted
 */
function delete_image($filename) {
    if (empty($filename)) {
        return false;
    }
    
    $filepath = UPLOADS_PATH . '/' . $filename;
    
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    
    return false;
}

// ============================================
// STRING MANIPULATION FUNCTIONS
// ============================================

/**
 * Truncate string
 * 
 * @param string $string String to truncate
 * @param int $length Maximum length
 * @param string $append Append string
 * @return string Truncated string
 */
function truncate($string, $length = 100, $append = '...') {
    if (mb_strlen($string) <= $length) {
        return $string;
    }
    
    return mb_substr($string, 0, $length) . $append;
}

/**
 * Generate slug from string
 * 
 * @param string $string String to slugify
 * @return string Slug
 */
function slugify($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

/**
 * Format price
 * 
 * @param float $amount Amount to format
 * @return string Formatted price
 */
function format_price($amount) {
    return 'Rs ' . number_format($amount, 2);
}

/**
 * Format date
 * 
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function format_date($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Time ago
 * 
 * @param string $datetime Datetime string
 * @return string Time ago string
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return 'Just now';
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $timestamp);
    }
}

// ============================================
// URL & REDIRECT FUNCTIONS
// ============================================

/**
 * Redirect to URL
 * 
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

/**
 * Get current URL
 * 
 * @return string Current URL
 */
function current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Build URL with parameters
 * 
 * @param string $base Base URL
 * @param array $params Parameters
 * @return string Full URL
 */
function build_url($base, $params = []) {
    if (empty($params)) {
        return $base;
    }
    
    $query = http_build_query($params);
    $separator = strpos($base, '?') !== false ? '&' : '?';
    
    return $base . $separator . $query;
}

// ============================================
// PAGINATION FUNCTIONS
// ============================================

/**
 * Generate pagination
 * 
 * @param int $total_items Total number of items
 * @param int $items_per_page Items per page
 * @param int $current_page Current page number
 * @param string $base_url Base URL for pagination links
 * @return array Pagination data
 */
function paginate($total_items, $items_per_page, $current_page, $base_url) {
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $items_per_page;
    
    return [
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'items_per_page' => $items_per_page,
        'offset' => $offset,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages,
        'previous_page' => $current_page - 1,
        'next_page' => $current_page + 1,
        'base_url' => $base_url
    ];
}

// ============================================
// ORDER FUNCTIONS
// ============================================

/**
 * Generate unique order number
 * 
 * @return string Order number
 */
function generate_order_number() {
    return 'ORD' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

/**
 * Calculate order total
 * 
 * @param float $subtotal Subtotal amount
 * @param bool $cod Is cash on delivery
 * @return array Array with breakdown
 */
function calculate_order_total($subtotal, $cod = false) {
    $shipping = $subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_CHARGE;
    $cod_charge = $cod ? COD_CHARGE : 0;
    $total = $subtotal + $shipping + $cod_charge;
    
    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'cod_charge' => $cod_charge,
        'total' => $total
    ];
}

// ============================================
// EMAIL FUNCTIONS (Simple mail function)
// ============================================

/**
 * Send email (using PHP mail function)
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email message (HTML)
 * @return bool True if sent
 */
function send_email($to, $subject, $message) {
    $headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Send verification email
 * 
 * @param string $email User email
 * @param string $token Verification token
 * @return bool True if sent
 */
function send_verification_email($email, $token) {
    $subject = 'Verify Your Email - ' . SITE_NAME;
    $verify_url = BASE_URL . '/auth/verify-email.php?token=' . $token;
    
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Welcome to ' . SITE_NAME . '!</h1>
            </div>
            <div class="content">
                <p>Thank you for registering with Sajilo Marketplace.</p>
                <p>Please verify your email address by clicking the button below:</p>
                <a href="' . $verify_url . '" class="button">Verify Email</a>
                <p>Or copy and paste this link in your browser:</p>
                <p>' . $verify_url . '</p>
                <p>If you did not create an account, please ignore this email.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return send_email($email, $subject, $message);
}

// ============================================
// NOTIFICATION FUNCTIONS
// ============================================

/**
 * Set flash message
 * 
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message text
 */
function set_flash($type, $message) {
    $_SESSION['flash'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash messages
 * 
 * @return array Flash messages
 */
function get_flash() {
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/**
 * Display flash messages HTML
 * 
 * @return string HTML for flash messages
 */
function display_flash() {
    $messages = get_flash();
    $html = '';
    
    foreach ($messages as $flash) {
        $type = $flash['type'];
        $bg_color = [
            'success' => 'bg-green-500',
            'error' => 'bg-red-500',
            'warning' => 'bg-yellow-500',
            'info' => 'bg-blue-500'
        ][$type] ?? 'bg-gray-500';
        
        $html .= '
        <div class="' . $bg_color . ' text-white px-6 py-4 rounded-lg shadow-lg mb-4">
            <div class="flex items-center justify-between">
                <span>' . clean($flash['message']) . '</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">✕</button>
            </div>
        </div>
        ';
    }
    
    return $html;
}
?>