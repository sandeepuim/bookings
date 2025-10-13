<?php
/**
 * TBO Hotels Button Fix - Button Behavior Logger
 * 
 * This script logs button click behavior to help diagnose issues with the
 * "Choose Room" buttons on the hotel results page.
 */

// Basic settings
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define log file path
$logFile = __DIR__ . '/logs/button-clicks.log';

// Get the request data
$data = json_decode(file_get_contents('php://input'), true);

// If no data received, check if it's in $_POST or $_GET
if (empty($data)) {
    if (!empty($_POST)) {
        $data = $_POST;
    } elseif (!empty($_GET)) {
        $data = $_GET;
    }
}

// Add timestamp and IP address
$data['timestamp'] = date('Y-m-d H:i:s');
$data['ip'] = $_SERVER['REMOTE_ADDR'];
$data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

// Format log entry
$logEntry = json_encode($data) . "\n";

// Write to log file
file_put_contents($logFile, $logEntry, FILE_APPEND);

// Return success response
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Button click logged']);
?>