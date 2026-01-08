<?php


require_once "../config/config.php";

// Logout user
logout_user();

// Set flash message
set_flash('success', 'You have been logged out successfully!');

// Redirect to home page
redirect(BASE_URL . "/project.php");
?>