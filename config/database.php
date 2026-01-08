<?php

// Prevent direct access
if (!defined('SAJILO_APP')) {
    die('Direct access not permitted');
}

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Change in production
define('DB_PASS', '');                // Change in production
define('DB_NAME', 'sajilo_db');
define('DB_CHARSET', 'utf8mb4');

// Global database connection variable
$conn = null;

/**
 * Create database connection
 * 
 * @return mysqli|false Database connection object or false on failure
 */
function db_connect() {
    global $conn;
    
    // Return existing connection if already connected
    if ($conn !== null && $conn->ping()) {
        return $conn;
    }
    
    // Create new connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        error_log("Database Connection Failed: " . $conn->connect_error);
        return false;
    }
    
    // Set charset
    $conn->set_charset(DB_CHARSET);
    
    return $conn;
}

/**
 * Close database connection
 */
function db_close() {
    global $conn;
    if ($conn !== null) {
        $conn->close();
        $conn = null;
    }
}

/**
 * Execute a prepared statement with parameters
 * 
 * @param string $sql SQL query with placeholders
 * @param string $types Parameter types (e.g., "ssi" for string, string, integer)
 * @param array $params Array of parameters
 * @return mysqli_stmt|false Prepared statement or false on failure
 */
function db_execute($sql, $types = "", $params = []) {
    global $conn;
    
    if ($conn === null) {
        db_connect();
    }
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    return $stmt;
}

/**
 * Fetch single row from database
 * 
 * @param string $sql SQL query
 * @param string $types Parameter types
 * @param array $params Parameters
 * @return array|null Associative array of row or null
 */
function db_fetch_one($sql, $types = "", $params = []) {
    $stmt = db_execute($sql, $types, $params);
    
    if (!$stmt) {
        return null;
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row;
}

/**
 * Fetch all rows from database
 * 
 * @param string $sql SQL query
 * @param string $types Parameter types
 * @param array $params Parameters
 * @return array Array of associative arrays
 */
function db_fetch_all($sql, $types = "", $params = []) {
    $stmt = db_execute($sql, $types, $params);
    
    if (!$stmt) {
        return [];
    }
    
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $rows;
}

/**
 * Get last inserted ID
 * 
 * @return int Last insert ID
 */
function db_last_id() {
    global $conn;
    return $conn->insert_id;
}

/**
 * Get number of affected rows
 * 
 * @return int Number of affected rows
 */
function db_affected_rows() {
    global $conn;
    return $conn->affected_rows;
}

/**
 * Escape string for safe SQL usage
 * 
 * @param string $string String to escape
 * @return string Escaped string
 */
function db_escape($string) {
    global $conn;
    if ($conn === null) {
        db_connect();
    }
    return $conn->real_escape_string($string);
}

/**
 * Begin transaction
 */
function db_begin_transaction() {
    global $conn;
    if ($conn === null) {
        db_connect();
    }
    $conn->begin_transaction();
}

/**
 * Commit transaction
 */
function db_commit() {
    global $conn;
    $conn->commit();
}

/**
 * Rollback transaction
 */
function db_rollback() {
    global $conn;
    $conn->rollback();
}

// Initialize database connection
db_connect();

// Register shutdown function to close connection
register_shutdown_function('db_close');
?>