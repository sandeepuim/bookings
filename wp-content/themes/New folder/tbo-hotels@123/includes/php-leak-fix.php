<?php
/**
 * PHP Code Leak Fix
 * 
 * This file contains a fix for PHP code being displayed in the browser.
 * Updated to fix country dropdown loading issue.
 */

// Include our AJAX fix code
require_once(dirname(__FILE__) . '/ajax-fix.php');

// Function to add inline JavaScript to hide PHP code and fix country dropdown
function tbo_hotels_add_php_leak_fix() {
    ?>
    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Find all elements that contain PHP code
        var nodeList = document.querySelectorAll("body > *");
        for (var i = 0; i < nodeList.length; i++) {
            var node = nodeList[i];
            
            // Check if the node's content resembles PHP code
            if (node.textContent && 
                (node.textContent.includes('<?php') || 
                 node.textContent.includes('function') && node.textContent.includes('array(') && node.textContent.includes('return'))) {
                
                // Hide the node
                node.style.display = 'none';
                console.log('PHP code leak detected and hidden');
            }
        }
        
        // Clean up direct text nodes in the body
        var bodyElement = document.body;
        for (var i = 0; i < bodyElement.childNodes.length; i++) {
            var node = bodyElement.childNodes[i];
            
            if (node.nodeType === Node.TEXT_NODE && 
                (node.textContent.includes('<?php') || 
                 node.textContent.includes('function') && node.textContent.includes('array('))) {
                
                // Remove the text node
                bodyElement.removeChild(node);
                i--; // Adjust for the removed node
                console.log('Direct PHP code leak detected and removed');
            }
        }
        
        // Fix country dropdown loading issue
        setTimeout(function() {
            // Check if country dropdown exists but is empty
            var countrySelect = document.getElementById('country_code');
            if (countrySelect && countrySelect.options.length <= 1) {
                console.log('Country dropdown not loaded properly. Attempting fix...');
                
                // Trigger AJAX request directly to load countries
                if (typeof jQuery !== 'undefined' && typeof tbo_hotels_params !== 'undefined') {
                    jQuery.ajax({
                        url: tbo_hotels_params.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'tbo_hotels_get_countries',
                            nonce: tbo_hotels_params.nonce
                        },
                        dataType: 'json',
                        xhrFields: {
                            withCredentials: true
                        },
                        success: function(response) {
                            console.log('Manual countries API response:', response);
                            
                            if (response.success && response.data) {
                                // Populate country dropdown
                                var options = '<option value="">Select Country</option>';
                                response.data.forEach(function(country) {
                                    options += '<option value="' + country.Code + '">' + country.Name + '</option>';
                                });
                                
                                jQuery('#country_code').html(options);
                                
                                // Set default to India if available
                                setTimeout(function() {
                                    jQuery('#country_code').val('IN').trigger('change');
                                }, 500);
                            } else {
                                loadFallbackCountries();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Country AJAX error:', error);
                            loadFallbackCountries();
                        }
                    });
                }
                        }
                    });
                }
            }
        }, 2000); // Give the original script 2 seconds to run before our fix
        
        // Load fallback countries when AJAX fails
        function loadFallbackCountries() {
            console.log('Loading fallback countries');
            var fallbackCountries = [
                {Code: 'IN', Name: 'India'},
                {Code: 'US', Name: 'United States'},
                {Code: 'GB', Name: 'United Kingdom'},
                {Code: 'AE', Name: 'United Arab Emirates'},
                {Code: 'TH', Name: 'Thailand'},
                {Code: 'SG', Name: 'Singapore'},
                {Code: 'MY', Name: 'Malaysia'},
                {Code: 'ID', Name: 'Indonesia'},
                {Code: 'JP', Name: 'Japan'},
                {Code: 'CN', Name: 'China'}
            ];
            
            var options = '<option value="">Select Country</option>';
            fallbackCountries.forEach(function(country) {
                options += '<option value="' + country.Code + '">' + country.Name + '</option>';
            });
            
            jQuery('#country_code').html(options);
            
            // Set default to India
            setTimeout(function() {
                jQuery('#country_code').val('IN').trigger('change');
            }, 500);
        }
        
        // Fix city dropdown when country changes
        jQuery(document).on('change', '#country_code', function() {
            var countryCode = jQuery(this).val();
            if (countryCode) {
                loadCities(countryCode);
            }
        });
        
        // Function to load cities for a country
        function loadCities(countryCode) {
            console.log('Loading cities for country:', countryCode);
            var citySelect = jQuery('#city_code');
            
            if (citySelect.length === 0) {
                console.log('City select not found');
                return;
            }
            
            // Show loading state
            citySelect.html('<option value="">Loading cities...</option>');
            citySelect.prop('disabled', true);
            
            jQuery.ajax({
                url: tbo_hotels_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'tbo_hotels_get_cities',
                    country_code: countryCode,
                    nonce: tbo_hotels_params.nonce
                },
                dataType: 'json',
                xhrFields: {
                    withCredentials: true
                },
                success: function(response) {
                    console.log('Cities API response:', response);
                    
                    if (response.success && response.data && response.data.length > 0) {
                        var options = '<option value="">Select City</option>';
                        response.data.forEach(function(city) {
                            options += '<option value="' + city.Code + '">' + city.Name + '</option>';
                        });
                        citySelect.html(options);
                    } else {
                        loadFallbackCities(countryCode);
                    }
                    
                    citySelect.prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('City AJAX error:', error);
                    loadFallbackCities(countryCode);
                    citySelect.prop('disabled', false);
                }
            });
        }
        
        // Load fallback cities when AJAX fails
        function loadFallbackCities(countryCode) {
            console.log('Loading fallback cities for', countryCode);
            var fallbackCities = [];
            
            if (countryCode === 'IN') {
                fallbackCities = [
                    {Code: '150184', Name: 'Mumbai'},
                    {Code: '150489', Name: 'New Delhi'},
                    {Code: '150089', Name: 'Bangalore'},
                    {Code: '151145', Name: 'Kolkata'},
                    {Code: '150787', Name: 'Chennai'},
                    {Code: '150186', Name: 'Goa'}
                ];
            } else if (countryCode === 'US') {
                fallbackCities = [
                    {Code: '150642', Name: 'New York'},
                    {Code: '150157', Name: 'Los Angeles'},
                    {Code: '150201', Name: 'Chicago'},
                    {Code: '150152', Name: 'Miami'},
                    {Code: '150161', Name: 'Las Vegas'}
                ];
            } else if (countryCode === 'GB') {
                fallbackCities = [
                    {Code: '150351', Name: 'London'},
                    {Code: '150447', Name: 'Manchester'},
                    {Code: '150093', Name: 'Birmingham'},
                    {Code: '150193', Name: 'Edinburgh'},
                    {Code: '150223', Name: 'Glasgow'}
                ];
            } else if (countryCode === 'AE') {
                fallbackCities = [
                    {Code: '150195', Name: 'Dubai'},
                    {Code: '150009', Name: 'Abu Dhabi'},
                    {Code: '150803', Name: 'Sharjah'},
                    {Code: '151091', Name: 'Ras Al Khaimah'}
                ];
            } else {
                fallbackCities = [
                    {Code: '0', Name: 'Direct Search - Enter hotel name'}
                ];
            }
            
            var citySelect = jQuery('#city_code');
            var options = '<option value="">Select City</option>';
            fallbackCities.forEach(function(city) {
                options += '<option value="' + city.Code + '">' + city.Name + '</option>';
            });
            
            citySelect.html(options);
        }
    });
    </script>
    <?php
}

// Add the fix to the wp_head hook with high priority
add_action('wp_head', 'tbo_hotels_add_php_leak_fix', 1);

/**
 * Fallback function for country and city dropdowns
 * This serves as a direct endpoint for loading data when AJAX may be failing
 */
function tbo_hotels_fallback_data() {
    // Fallback for countries
    if (isset($_GET['fallback_countries']) && $_GET['fallback_countries'] == 1) {
        // Start output buffer to prevent any PHP code leakage
        ob_start();
        
        $countries = tbo_hotels_get_countries();
        
        // Clear the buffer before sending response
        ob_end_clean();
        
        header('Content-Type: application/json');
        
        if (!is_wp_error($countries)) {
            echo json_encode(array('success' => true, 'data' => $countries));
        } else {
            // Provide fallback countries if API fails
            $fallback_countries = array(
                array('Code' => 'IN', 'Name' => 'India'),
                array('Code' => 'US', 'Name' => 'United States'),
                array('Code' => 'GB', 'Name' => 'United Kingdom'),
                array('Code' => 'AE', 'Name' => 'United Arab Emirates'),
                array('Code' => 'TH', 'Name' => 'Thailand'),
                array('Code' => 'SG', 'Name' => 'Singapore'),
                array('Code' => 'MY', 'Name' => 'Malaysia')
            );
            echo json_encode(array(
                'success' => true, 
                'data' => $fallback_countries,
                'fallback' => true,
                'error' => $countries->get_error_message()
            ));
        }
        exit;
    }
    
    // Fallback for cities
    if (isset($_GET['fallback_cities']) && isset($_GET['country_code'])) {
        // Start output buffer to prevent any PHP code leakage
        ob_start();
        
        $country_code = sanitize_text_field($_GET['country_code']);
        $cities = tbo_hotels_get_cities($country_code);
        
        // Clear the buffer before sending response
        ob_end_clean();
        
        header('Content-Type: application/json');
        
        if (!is_wp_error($cities)) {
            echo json_encode(array('success' => true, 'data' => $cities));
        } else {
            // Provide fallback cities based on country code
            $fallback_cities = array();
            
            if ($country_code === 'IN') {
                $fallback_cities = array(
                    array('Code' => '150184', 'Name' => 'Mumbai'),
                    array('Code' => '150489', 'Name' => 'New Delhi'),
                    array('Code' => '150089', 'Name' => 'Bangalore'),
                    array('Code' => '151145', 'Name' => 'Kolkata'),
                    array('Code' => '150787', 'Name' => 'Chennai'),
                    array('Code' => '150186', 'Name' => 'Goa')
                );
            } else if ($country_code === 'US') {
                $fallback_cities = array(
                    array('Code' => '150642', 'Name' => 'New York'),
                    array('Code' => '150157', 'Name' => 'Los Angeles'),
                    array('Code' => '150201', 'Name' => 'Chicago'),
                    array('Code' => '150152', 'Name' => 'Miami'),
                    array('Code' => '150161', 'Name' => 'Las Vegas')
                );
            } else if ($country_code === 'GB') {
                $fallback_cities = array(
                    array('Code' => '150351', 'Name' => 'London'),
                    array('Code' => '150447', 'Name' => 'Manchester'),
                    array('Code' => '150093', 'Name' => 'Birmingham'),
                    array('Code' => '150193', 'Name' => 'Edinburgh'),
                    array('Code' => '150223', 'Name' => 'Glasgow')
                );
            } else {
                $fallback_cities = array(
                    array('Code' => '0', 'Name' => 'Direct Search - Enter hotel name')
                );
            }
            
            echo json_encode(array(
                'success' => true, 
                'data' => $fallback_cities,
                'fallback' => true,
                'error' => $cities->get_error_message()
            ));
        }
        exit;
    }
}
add_action('init', 'tbo_hotels_fallback_data');