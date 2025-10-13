<?php
/**
 * TBO Hotels Dropdown Diagnostic Tool
 * This tool helps diagnose issues with the country and city dropdowns
 */

// Define constants
define('TBO_DIAGNOSTIC_VERSION', '1.0.0');

// Basic security check
if (!isset($_GET['run_diagnostic']) || $_GET['run_diagnostic'] !== '1') {
    echo 'Add ?run_diagnostic=1 to URL to run this diagnostic tool.';
    exit;
}

// Try to include WordPress
$wp_loaded = false;
if (file_exists('../../../wp-load.php')) {
    require_once('../../../wp-load.php');
    $wp_loaded = function_exists('wp_head');
}

// Header output
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TBO Hotels Dropdown Diagnostic</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            color: #333;
        }
        h1, h2, h3 {
            color: #0056b3;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .test-result {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        code {
            font-family: 'Courier New', Courier, monospace;
            background: #f1f1f1;
            padding: 2px 5px;
            border-radius: 3px;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            white-space: pre-wrap;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background: #0056b3;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background: #003d82;
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
            background: #f2f2f2;
        }
        .action-links {
            margin-top: 20px;
        }
        .action-links a {
            display: inline-block;
            margin-right: 15px;
            color: #0056b3;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>TBO Hotels Dropdown Diagnostic Tool</h1>
        <p>This tool helps diagnose issues with the country and city dropdowns in the TBO Hotels plugin.</p>
        
        <div class="section">
            <h2>Environment Information</h2>
            
            <table>
                <tr>
                    <th>WordPress Loaded</th>
                    <td><?php echo $wp_loaded ? 'Yes' : 'No'; ?></td>
                </tr>
                <tr>
                    <th>PHP Version</th>
                    <td><?php echo phpversion(); ?></td>
                </tr>
                <tr>
                    <th>jQuery Status</th>
                    <td id="jquery-status">Checking...</td>
                </tr>
                <tr>
                    <th>tbo_hotels_params Status</th>
                    <td id="params-status">Checking...</td>
                </tr>
                <tr>
                    <th>Browser</th>
                    <td><?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT']); ?></td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <h2>Dropdown Test</h2>
            
            <div id="dropdown-test-area">
                <div class="form-group">
                    <label for="country_code">Country:</label>
                    <select id="country_code">
                        <option value="">Select Country</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="city_code">City:</label>
                    <select id="city_code" disabled>
                        <option value="">Select Country First</option>
                    </select>
                </div>
                
                <button id="test-button">Run Dropdown Test</button>
                <div id="dropdown-test-results"></div>
            </div>
        </div>
        
        <div class="section">
            <h2>API Connection Test</h2>
            <button id="api-test-button">Test API Connection</button>
            <div id="api-test-results"></div>
        </div>
        
        <div class="section">
            <h2>Ajax Endpoint Test</h2>
            <button id="ajax-test-button">Test Ajax Endpoints</button>
            <div id="ajax-test-results"></div>
        </div>
        
        <div class="section">
            <h2>Recommendations</h2>
            <div id="recommendations">
                <p>Click the test buttons above to get specific recommendations.</p>
            </div>
        </div>
        
        <div class="action-links">
            <a href="<?php echo $wp_loaded ? esc_url(home_url('/hotel-search-page/')) : '../../../hotel-search-page/'; ?>">Go to Hotel Search Page</a>
            <a href="<?php echo $wp_loaded ? esc_url(admin_url()) : '../../../wp-admin/'; ?>">Go to WordPress Admin</a>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check jQuery
        var jqueryStatus = document.getElementById('jquery-status');
        var paramsStatus = document.getElementById('params-status');
        
        if (typeof jQuery !== 'undefined') {
            jqueryStatus.textContent = 'Loaded (v' + jQuery.fn.jquery + ')';
            jqueryStatus.classList.add('success');
        } else {
            jqueryStatus.textContent = 'Not loaded';
            jqueryStatus.classList.add('error');
            addRecommendation('jQuery is not loaded. Check if the WordPress enqueue system is working correctly.', 'error');
        }
        
        // Check tbo_hotels_params
        if (typeof window.tbo_hotels_params !== 'undefined') {
            paramsStatus.textContent = 'Loaded';
            paramsStatus.classList.add('success');
            
            // Display parameters
            var paramsList = document.createElement('pre');
            paramsList.textContent = JSON.stringify(window.tbo_hotels_params, null, 2);
            paramsStatus.appendChild(paramsList);
        } else {
            paramsStatus.textContent = 'Not loaded';
            paramsStatus.classList.add('error');
            addRecommendation('tbo_hotels_params is not defined. Check if the script localization is working correctly.', 'error');
        }
        
        // Dropdown Test
        var $countrySelect = document.getElementById('country_code');
        var $citySelect = document.getElementById('city_code');
        var $testButton = document.getElementById('test-button');
        var $testResults = document.getElementById('dropdown-test-results');
        
        // Load countries when test button clicked
        $testButton.addEventListener('click', function() {
            $testResults.innerHTML = '<div class="test-result">Loading countries...</div>';
            
            // Try to load countries using our known methods
            if (typeof jQuery !== 'undefined') {
                // Define fallback AJAX URL
                var ajaxUrl = '';
                
                if (typeof window.tbo_hotels_params !== 'undefined' && window.tbo_hotels_params.ajax_url) {
                    ajaxUrl = window.tbo_hotels_params.ajax_url;
                } else {
                    // Fallback URL based on typical WordPress structure
                    ajaxUrl = window.location.origin + '/bookings/wp-admin/admin-ajax.php';
                }
                
                // Try to load countries
                jQuery.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'tbo_hotels_get_countries',
                        nonce: (window.tbo_hotels_params && window.tbo_hotels_params.nonce) ? window.tbo_hotels_params.nonce : ''
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            $testResults.innerHTML = '<div class="test-result success">Countries loaded successfully!</div>';
                            
                            // Populate country dropdown
                            var options = '<option value="">Select Country</option>';
                            response.data.forEach(function(country) {
                                options += '<option value="' + country.Code + '">' + country.Name + '</option>';
                            });
                            
                            jQuery($countrySelect).html(options);
                            
                            // Enable test for cities
                            setupCityTest();
                            
                            addRecommendation('Country dropdown is working correctly through AJAX.', 'success');
                        } else {
                            $testResults.innerHTML = '<div class="test-result error">Failed to load countries. Using fallback.</div>';
                            tryFallbackCountries();
                        }
                    },
                    error: function() {
                        $testResults.innerHTML = '<div class="test-result error">Error loading countries via AJAX. Using fallback.</div>';
                        tryFallbackCountries();
                    }
                });
            } else {
                $testResults.innerHTML = '<div class="test-result error">jQuery not available. Using static fallback.</div>';
                useStaticFallback();
            }
        });
        
        // API Connection Test
        var $apiTestButton = document.getElementById('api-test-button');
        var $apiTestResults = document.getElementById('api-test-results');
        
        $apiTestButton.addEventListener('click', function() {
            $apiTestResults.innerHTML = '<div class="test-result">Testing API connection...</div>';
            
            if (typeof jQuery !== 'undefined') {
                // Define fallback API test URL
                var apiTestUrl = window.location.origin + '/bookings/wp-content/themes/tbo-hotels/includes/php-leak-fix.php?fallback_countries=1';
                
                jQuery.ajax({
                    url: apiTestUrl,
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.data) {
                            $apiTestResults.innerHTML = '<div class="test-result success">API connection successful!</div>';
                            var countSample = '<pre>' + JSON.stringify(response.data.slice(0, 3), null, 2) + '\n... (showing 3 of ' + response.data.length + ' items)</pre>';
                            $apiTestResults.innerHTML += countSample;
                            
                            addRecommendation('The direct API connection is working correctly.', 'success');
                        } else {
                            $apiTestResults.innerHTML = '<div class="test-result error">API connection failed with response: ' + JSON.stringify(response) + '</div>';
                            addRecommendation('The direct API connection failed. Check PHP error logs and API credentials.', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        $apiTestResults.innerHTML = '<div class="test-result error">API connection error: ' + error + '</div>';
                        addRecommendation('The direct API connection failed. Check server connectivity and PHP error logs.', 'error');
                    }
                });
            } else {
                $apiTestResults.innerHTML = '<div class="test-result error">jQuery not available. Cannot test API connection.</div>';
                addRecommendation('Install jQuery to enable API testing.', 'error');
            }
        });
        
        // Ajax Endpoint Test
        var $ajaxTestButton = document.getElementById('ajax-test-button');
        var $ajaxTestResults = document.getElementById('ajax-test-results');
        
        $ajaxTestButton.addEventListener('click', function() {
            $ajaxTestResults.innerHTML = '<div class="test-result">Testing Ajax endpoints...</div>';
            
            if (typeof jQuery !== 'undefined') {
                // Test WordPress admin-ajax.php
                var adminAjaxUrl = window.location.origin + '/bookings/wp-admin/admin-ajax.php';
                
                jQuery.ajax({
                    url: adminAjaxUrl,
                    type: 'POST',
                    data: {
                        action: 'tbo_hotels_get_countries'
                    },
                    success: function(response) {
                        if (response) {
                            $ajaxTestResults.innerHTML = '<div class="test-result success">Admin-ajax.php endpoint is reachable!</div>';
                            
                            if (typeof response === 'object' && response.success !== undefined) {
                                $ajaxTestResults.innerHTML += '<div class="test-result success">WordPress AJAX handler is working correctly.</div>';
                                addRecommendation('The WordPress AJAX system is working correctly.', 'success');
                            } else {
                                $ajaxTestResults.innerHTML += '<div class="test-result warning">WordPress AJAX handler response format is unusual. Check the action hook registration.</div>';
                                addRecommendation('Check if the AJAX action hooks are properly registered in functions.php.', 'warning');
                            }
                        } else {
                            $ajaxTestResults.innerHTML = '<div class="test-result error">Received empty response from admin-ajax.php</div>';
                            addRecommendation('The WordPress AJAX system returned an empty response. Check PHP error logs.', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        $ajaxTestResults.innerHTML = '<div class="test-result error">Admin-ajax.php endpoint error: ' + error + '</div>';
                        addRecommendation('The WordPress AJAX endpoint is not reachable. Check server configuration.', 'error');
                    }
                });
            } else {
                $ajaxTestResults.innerHTML = '<div class="test-result error">jQuery not available. Cannot test Ajax endpoints.</div>';
                addRecommendation('Install jQuery to enable AJAX endpoint testing.', 'error');
            }
        });
        
        // Utility functions
        function tryFallbackCountries() {
            if (typeof jQuery !== 'undefined') {
                var fallbackUrl = window.location.origin + '/bookings/wp-content/themes/tbo-hotels/includes/php-leak-fix.php?fallback_countries=1';
                
                jQuery.ajax({
                    url: fallbackUrl,
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.data) {
                            $testResults.innerHTML += '<div class="test-result success">Fallback countries loaded successfully!</div>';
                            
                            // Populate country dropdown
                            var options = '<option value="">Select Country</option>';
                            response.data.forEach(function(country) {
                                options += '<option value="' + country.Code + '">' + country.Name + '</option>';
                            });
                            
                            jQuery($countrySelect).html(options);
                            
                            // Enable test for cities
                            setupCityTest();
                            
                            addRecommendation('The fallback country loading mechanism is working correctly. Use this as a backup.', 'warning');
                        } else {
                            $testResults.innerHTML += '<div class="test-result error">Fallback countries failed too. Using static list.</div>';
                            useStaticFallback();
                        }
                    },
                    error: function() {
                        $testResults.innerHTML += '<div class="test-result error">Error loading fallback countries. Using static list.</div>';
                        useStaticFallback();
                    }
                });
            } else {
                useStaticFallback();
            }
        }
        
        function useStaticFallback() {
            var fallbackCountries = [
                {Code: 'IN', Name: 'India'},
                {Code: 'US', Name: 'United States'},
                {Code: 'GB', Name: 'United Kingdom'},
                {Code: 'AE', Name: 'United Arab Emirates'},
                {Code: 'TH', Name: 'Thailand'}
            ];
            
            // Populate country dropdown using vanilla JS
            var options = '<option value="">Select Country</option>';
            fallbackCountries.forEach(function(country) {
                options += '<option value="' + country.Code + '">' + country.Name + '</option>';
            });
            
            $countrySelect.innerHTML = options;
            
            $testResults.innerHTML += '<div class="test-result warning">Using static country list as last resort.</div>';
            
            // Enable test for cities
            setupCityTest();
            
            addRecommendation('Both AJAX and direct API calls are failing. Check your server configuration, PHP error logs, and API credentials.', 'error');
        }
        
        function setupCityTest() {
            if (typeof jQuery !== 'undefined') {
                jQuery($countrySelect).on('change', function() {
                    var countryCode = this.value;
                    
                    if (!countryCode) {
                        jQuery($citySelect).prop('disabled', true).html('<option value="">Select Country First</option>');
                        return;
                    }
                    
                    $testResults.innerHTML += '<div class="test-result">Loading cities for ' + countryCode + '...</div>';
                    jQuery($citySelect).prop('disabled', true).html('<option value="">Loading cities...</option>');
                    
                    // Define fallback AJAX URL
                    var ajaxUrl = '';
                    
                    if (typeof window.tbo_hotels_params !== 'undefined' && window.tbo_hotels_params.ajax_url) {
                        ajaxUrl = window.tbo_hotels_params.ajax_url;
                    } else {
                        // Fallback URL based on typical WordPress structure
                        ajaxUrl = window.location.origin + '/bookings/wp-admin/admin-ajax.php';
                    }
                    
                    // Try to load cities
                    jQuery.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'tbo_hotels_get_cities',
                            country_code: countryCode,
                            nonce: (window.tbo_hotels_params && window.tbo_hotels_params.nonce) ? window.tbo_hotels_params.nonce : ''
                        },
                        success: function(response) {
                            if (response.success && response.data) {
                                $testResults.innerHTML += '<div class="test-result success">Cities loaded successfully!</div>';
                                
                                // Populate city dropdown
                                var options = '<option value="">Select City</option>';
                                response.data.forEach(function(city) {
                                    options += '<option value="' + city.Code + '">' + city.Name + '</option>';
                                });
                                
                                jQuery($citySelect).html(options).prop('disabled', false);
                                
                                addRecommendation('City dropdown is working correctly through AJAX.', 'success');
                            } else {
                                $testResults.innerHTML += '<div class="test-result error">Failed to load cities. Using fallback.</div>';
                                tryFallbackCities(countryCode);
                            }
                        },
                        error: function() {
                            $testResults.innerHTML += '<div class="test-result error">Error loading cities via AJAX. Using fallback.</div>';
                            tryFallbackCities(countryCode);
                        }
                    });
                });
            } else {
                // Vanilla JS change handler
                $countrySelect.addEventListener('change', function() {
                    var countryCode = this.value;
                    
                    if (!countryCode) {
                        $citySelect.disabled = true;
                        $citySelect.innerHTML = '<option value="">Select Country First</option>';
                        return;
                    }
                    
                    $testResults.innerHTML += '<div class="test-result warning">jQuery not available. Using static city list.</div>';
                    
                    // For India, use static list
                    if (countryCode === 'IN') {
                        var indianCities = [
                            {Code: '150184', Name: 'Mumbai'},
                            {Code: '150489', Name: 'New Delhi'},
                            {Code: '150089', Name: 'Bangalore'},
                            {Code: '150787', Name: 'Chennai'},
                            {Code: '150186', Name: 'Goa'}
                        ];
                        
                        var options = '<option value="">Select City</option>';
                        indianCities.forEach(function(city) {
                            options += '<option value="' + city.Code + '">' + city.Name + '</option>';
                        });
                        
                        $citySelect.innerHTML = options;
                        $citySelect.disabled = false;
                    } else {
                        // For other countries
                        $citySelect.innerHTML = '<option value="0">Direct Search Available</option>';
                        $citySelect.disabled = false;
                    }
                });
            }
        }
        
        function tryFallbackCities(countryCode) {
            if (typeof jQuery !== 'undefined') {
                // For India, provide common cities as fallback
                if (countryCode === 'IN') {
                    var fallbackCities = [
                        {Code: '150184', Name: 'Mumbai'},
                        {Code: '150489', Name: 'New Delhi'},
                        {Code: '150089', Name: 'Bangalore'},
                        {Code: '150787', Name: 'Chennai'},
                        {Code: '150186', Name: 'Goa'}
                    ];
                    
                    var options = '<option value="">Select City</option>';
                    fallbackCities.forEach(function(city) {
                        options += '<option value="' + city.Code + '">' + city.Name + '</option>';
                    });
                    
                    jQuery($citySelect).html(options).prop('disabled', false);
                    $testResults.innerHTML += '<div class="test-result warning">Using fallback city list for India.</div>';
                    
                    addRecommendation('Using fallback city list for India. The city AJAX call is not working.', 'warning');
                    return;
                }
                
                // For US, provide some major cities
                if (countryCode === 'US') {
                    var fallbackCities = [
                        {Code: '150642', Name: 'New York'},
                        {Code: '150157', Name: 'Los Angeles'},
                        {Code: '150201', Name: 'Chicago'},
                        {Code: '150152', Name: 'Miami'},
                        {Code: '150161', Name: 'Las Vegas'}
                    ];
                    
                    var options = '<option value="">Select City</option>';
                    fallbackCities.forEach(function(city) {
                        options += '<option value="' + city.Code + '">' + city.Name + '</option>';
                    });
                    
                    jQuery($citySelect).html(options).prop('disabled', false);
                    $testResults.innerHTML += '<div class="test-result warning">Using fallback city list for US.</div>';
                    
                    addRecommendation('Using fallback city list for US. The city AJAX call is not working.', 'warning');
                    return;
                }
                
                // For other countries, try the direct API endpoint
                var fallbackUrl = window.location.origin + '/bookings/wp-content/themes/tbo-hotels/includes/php-leak-fix.php?fallback_cities=1&country_code=' + countryCode;
                
                jQuery.ajax({
                    url: fallbackUrl,
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.data) {
                            $testResults.innerHTML += '<div class="test-result success">Fallback cities loaded successfully!</div>';
                            
                            // Populate city dropdown
                            var options = '<option value="">Select City</option>';
                            response.data.forEach(function(city) {
                                options += '<option value="' + city.Code + '">' + city.Name + '</option>';
                            });
                            
                            jQuery($citySelect).html(options).prop('disabled', false);
                            
                            addRecommendation('The fallback city loading mechanism is working correctly. Use this as a backup.', 'warning');
                        } else {
                            $testResults.innerHTML += '<div class="test-result error">Fallback cities failed. Using direct search option.</div>';
                            jQuery($citySelect).html('<option value="0">Direct Search - Enter hotel name</option>').prop('disabled', false);
                            
                            addRecommendation('Both AJAX and direct API calls for cities are failing. Check your server configuration and API credentials.', 'error');
                        }
                    },
                    error: function() {
                        $testResults.innerHTML += '<div class="test-result error">Error loading fallback cities. Using direct search option.</div>';
                        jQuery($citySelect).html('<option value="0">Direct Search - Enter hotel name</option>').prop('disabled', false);
                        
                        addRecommendation('Both AJAX and direct API calls for cities are failing. Check your server configuration and API credentials.', 'error');
                    }
                });
            } else {
                // Without jQuery, just use a static option
                $citySelect.innerHTML = '<option value="0">Direct Search Available</option>';
                $citySelect.disabled = false;
                
                $testResults.innerHTML += '<div class="test-result error">jQuery not available. Using direct search option.</div>';
                
                addRecommendation('jQuery is required for proper city dropdown functionality. Install jQuery to enable proper dropdown behavior.', 'error');
            }
        }
        
        function addRecommendation(message, type) {
            var $recommendations = document.getElementById('recommendations');
            var existingText = $recommendations.innerHTML;
            
            if (existingText.indexOf('<p>Click the test buttons above to get specific recommendations.</p>') !== -1) {
                $recommendations.innerHTML = ''; // Clear default message
            }
            
            var recommendation = document.createElement('div');
            recommendation.className = 'test-result ' + type;
            recommendation.textContent = message;
            
            $recommendations.appendChild(recommendation);
        }
    });
    </script>
</body>
</html>