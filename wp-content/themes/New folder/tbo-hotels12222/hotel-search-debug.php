<?php
/**
 * TBO Hotel Search Debug
 * 
 * This file helps debug the hotel search JavaScript functionality in isolation.
 */

// Load WordPress core
require_once('../../../wp-load.php');

// Get header
get_header();
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>TBO Hotel Search Debug</h1>
            
            <div class="card">
                <div class="card-header">
                    JavaScript Console
                </div>
                <div class="card-body">
                    <div id="console-log" style="background: #f5f5f5; border: 1px solid #ddd; padding: 15px; height: 200px; overflow-y: auto; font-family: monospace;"></div>
                </div>
            </div>
            
            <hr>
            
            <h2>Test Hotel Results</h2>
            
            <div class="hotel-results">
                <!-- Test hotel card -->
                <div class="yatra-hotel-card" data-hotel-code="TEST12345">
                    <div class="card">
                        <div class="card-header">
                            Test Hotel
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <img src="https://via.placeholder.com/300x200" alt="Test Hotel" class="img-fluid">
                                </div>
                                <div class="col-md-8">
                                    <h4>Test Hotel Name</h4>
                                    <p>123 Test Street, Test City</p>
                                    <div class="hotel-amenities">
                                        <span class="badge badge-primary">WiFi</span>
                                        <span class="badge badge-primary">Parking</span>
                                        <span class="badge badge-primary">Pool</span>
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-primary choose-room-btn">Choose Room</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Override console.log to display in our debug area
(function() {
    var oldLog = console.log;
    console.log = function() {
        // Call original console.log
        oldLog.apply(console, arguments);
        
        // Get arguments as string
        var args = Array.prototype.slice.call(arguments);
        var message = args.map(function(arg) {
            if (typeof arg === 'object') {
                return JSON.stringify(arg, null, 2);
            } else {
                return arg;
            }
        }).join(' ');
        
        // Append to our console log div
        var consoleDiv = document.getElementById('console-log');
        if (consoleDiv) {
            var logEntry = document.createElement('div');
            logEntry.innerHTML = '<span style="color:#888;">[' + new Date().toLocaleTimeString() + ']</span> ' + message;
            consoleDiv.appendChild(logEntry);
            // Auto-scroll to bottom
            consoleDiv.scrollTop = consoleDiv.scrollHeight;
        }
    };
})();

// Add a test parameter to the URL
if (!window.location.search.includes('city_code')) {
    // Add a fake city_code parameter
    var newUrl = window.location.href + (window.location.search ? '&' : '?') + 'city_code=TEST_CITY';
    history.replaceState(null, '', newUrl);
    console.log('Added test city_code parameter to URL');
}

// Log when jQuery is ready
jQuery(document).ready(function($) {
    console.log('jQuery is ready');
    console.log('jQuery version: ' + $.fn.jquery);
    
    // Test event handler
    console.log('Setting up test click handler');
    $(document).on('click', '.test-button', function() {
        console.log('Test button clicked');
    });
});

// Log when page is fully loaded
window.addEventListener('load', function() {
    console.log('Window loaded');
    console.log('Choose room buttons found: ' + document.querySelectorAll('.choose-room-btn').length);
    
    // Add a test button
    var testButton = document.createElement('button');
    testButton.className = 'btn btn-secondary test-button mt-3 mr-2';
    testButton.textContent = 'Test jQuery Event';
    document.querySelector('.hotel-amenities').appendChild(testButton);
});
</script>

<?php
// Get footer
get_footer();
?>