<?php
session_start();
require_once "database.php";

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}
?>