<?php
/**
 * TBO Hotels Button Implementation Guide
 * 
 * This page provides instructions for implementing and troubleshooting
 * the "Choose Room" button functionality.
 */

// Basic setup
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TBO Hotels Button Implementation Guide</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #0066cc;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border-left: 5px solid #0066cc;
        }
        .code-block {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-family: monospace;
            margin: 10px 0;
        }
        .step {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .tip {
            background-color: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            border-left: 5px solid #43a047;
            margin: 15px 0;
        }
        .warning {
            background-color: #fff3e0;
            padding: 15px;
            border-radius: 5px;
            border-left: 5px solid #ef6c00;
            margin: 15px 0;
        }
        .debug-tools {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            border-left: 5px solid #1976d2;
            margin: 15px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>TBO Hotels Button Implementation Guide</h1>
    
    <div class="section">
        <h2>Overview</h2>
        <p>This guide explains how to implement and troubleshoot the "Choose Room" button functionality in the TBO Hotels theme. The button should redirect users to a room selection page with details about available rooms for the selected hotel.</p>
    </div>
    
    <div class="section">
        <h2>Implementation Options</h2>
        
        <div class="step">
            <h3>Option 1: Standard WordPress Implementation</h3>
            <p>This approach uses WordPress functions and hooks to add the button functionality:</p>
            <ol>
                <li>The following files are included in functions.php:
                    <ul>
                        <li><code>tbo-room-functions.php</code> - Provides room data functions</li>
                        <li><code>direct-button-fix.php</code> - Adds direct click handlers to buttons</li>
                        <li><code>hotel-button-enhancement.php</code> - Enhances buttons with attributes</li>
                    </ul>
                </li>
                <li>Button clicks redirect to <code>hotel-room-selection.php</code> with appropriate parameters</li>
            </ol>
        </div>
        
        <div class="step">
            <h3>Option 2: Direct JavaScript Implementation</h3>
            <p>This approach uses direct JavaScript to add click handlers to the buttons:</p>
            <div class="code-block">
&lt;script&gt;
document.addEventListener('DOMContentLoaded', function() {
    var buttons = document.querySelectorAll('.choose-room-btn');
    
    buttons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            var hotelCode = this.getAttribute('data-hotel-code');
            var cityCode = this.getAttribute('data-city-code');
            var checkIn = this.getAttribute('data-check-in');
            var checkOut = this.getAttribute('data-check-out');
            
            window.location.href = 'hotel-room-selection-fixed.php?hotel_code=' + 
                encodeURIComponent(hotelCode) + '&city_code=' + 
                encodeURIComponent(cityCode) + '&check_in=' + 
                encodeURIComponent(checkIn) + '&check_out=' + 
                encodeURIComponent(checkOut);
        });
    });
});
&lt;/script&gt;
            </div>
        </div>
        
        <div class="step">
            <h3>Option 3: Using Data Attributes and Forms</h3>
            <p>You can also use HTML data attributes and forms to handle the button clicks:</p>
            <div class="code-block">
&lt;button class="choose-room-btn"
    data-hotel-code="12345"
    data-city-code="150184"
    data-check-in="2023-07-01"
    data-check-out="2023-07-05"&gt;
    Choose Room
&lt;/button&gt;

&lt;!-- OR using a form --&gt;
&lt;form action="hotel-room-selection-fixed.php" method="get"&gt;
    &lt;input type="hidden" name="hotel_code" value="12345"&gt;
    &lt;input type="hidden" name="city_code" value="150184"&gt;
    &lt;input type="hidden" name="check_in" value="2023-07-01"&gt;
    &lt;input type="hidden" name="check_out" value="2023-07-05"&gt;
    &lt;button type="submit" class="choose-room-btn"&gt;Choose Room&lt;/button&gt;
&lt;/form&gt;
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2>Required Parameters</h2>
        <p>The "Choose Room" button needs to pass the following parameters to the room selection page:</p>
        
        <table>
            <tr>
                <th>Parameter</th>
                <th>Description</th>
                <th>Required</th>
            </tr>
            <tr>
                <td>hotel_code</td>
                <td>The unique identifier for the hotel</td>
                <td>Yes</td>
            </tr>
            <tr>
                <td>city_code</td>
                <td>The city code where the hotel is located</td>
                <td>Yes</td>
            </tr>
            <tr>
                <td>check_in</td>
                <td>Check-in date (YYYY-MM-DD format)</td>
                <td>Yes</td>
            </tr>
            <tr>
                <td>check_out</td>
                <td>Check-out date (YYYY-MM-DD format)</td>
                <td>Yes</td>
            </tr>
            <tr>
                <td>adults</td>
                <td>Number of adults</td>
                <td>No (defaults to 2)</td>
            </tr>
            <tr>
                <td>children</td>
                <td>Number of children</td>
                <td>No (defaults to 0)</td>
            </tr>
            <tr>
                <td>rooms</td>
                <td>Number of rooms</td>
                <td>No (defaults to 1)</td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <h2>Troubleshooting</h2>
        
        <div class="warning">
            <h3>Common Issues</h3>
            <ol>
                <li><strong>PHP Code Displayed in Browser:</strong> This indicates that PHP is not being processed correctly. Make sure PHP is enabled on your server and that the file has a .php extension.</li>
                <li><strong>Button Not Redirecting:</strong> Check if JavaScript is enabled and if there are any console errors.</li>
                <li><strong>Room Selection Page Not Working:</strong> Verify that the tbo_hotels_get_room_details function exists and is working correctly.</li>
            </ol>
        </div>
        
        <div class="debug-tools">
            <h3>Debug Tools</h3>
            <p>We've provided several debug tools to help you troubleshoot issues:</p>
            <ul>
                <li><a href="button-debug.php">Button Debug Tool</a> - Tests different button implementations</li>
                <li><a href="php-test.php">PHP Test</a> - Checks if PHP is working correctly</li>
                <li><a href="hotel-room-selection-fixed.php?hotel_code=12345&city_code=150184&check_in=2025-09-20&check_out=2025-09-25">Test Room Selection Page</a> - Tests the room selection page with sample data</li>
            </ul>
        </div>
    </div>
    
    <div class="section">
        <h2>Implementation Tips</h2>
        
        <div class="tip">
            <h3>Best Practices</h3>
            <ol>
                <li>Always sanitize URL parameters to prevent security issues</li>
                <li>Use data attributes to store hotel and booking information</li>
                <li>Implement error handling for cases when parameters are missing</li>
                <li>Test the functionality in different browsers</li>
                <li>Ensure the room selection page is mobile-friendly</li>
            </ol>
        </div>
        
        <div class="tip">
            <h3>Advanced Implementation</h3>
            <p>For a more robust implementation, consider:</p>
            <ul>
                <li>Adding AJAX functionality to load room data without page reload</li>
                <li>Implementing session storage to remember user selections</li>
                <li>Adding a booking confirmation step</li>
                <li>Integrating with a payment gateway</li>
            </ul>
        </div>
    </div>
    
    <div class="section">
        <h2>Contact Support</h2>
        <p>If you continue to experience issues with the "Choose Room" button functionality, please contact our support team at support@tbohotels.com or create a support ticket in the admin dashboard.</p>
    </div>
</body>
</html>