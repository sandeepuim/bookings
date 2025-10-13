/**
 * TBO Hotels - AJAX Request Fix
 * 
 * This script fixes issues with AJAX requests for city and country dropdowns
 * by adding additional error handling and fallback mechanisms.
 */

(function($) {
    'use strict';
    
    // Wait for DOM to be ready
    $(document).ready(function() {
        console.log('TBO Hotels AJAX Fix loaded');
        
        // Cache original loading functions
        var originalLoadCountries = null;
        var originalLoadCities = null;
        
        // Find and patch the loadCountries function
        if (window.loadCountries) {
            originalLoadCountries = window.loadCountries;
            window.loadCountries = enhancedLoadCountries;
        }
        
        // Find and patch the loadCities function
        if (window.loadCities) {
            originalLoadCities = window.loadCities;
            window.loadCities = enhancedLoadCities;
        }
        
        // Enhanced function to load countries
        function enhancedLoadCountries() {
            console.log('Enhanced loadCountries called');
            
            // Attempt to call original function if it exists
            if (originalLoadCountries) {
                try {
                    originalLoadCountries();
                } catch(e) {
                    console.error('Error in original loadCountries:', e);
                    loadCountriesFallback();
                }
            } else {
                loadCountriesFallback();
            }
        }
        
        // Fallback function for loading countries
        function loadCountriesFallback() {
            console.log('Using loadCountriesFallback');
            
            var $countrySelect = $('#country_code');
            if (!$countrySelect.length) return;
            
            showLoading($countrySelect, 'Loading countries...');
            
            // Try direct endpoint first
            $.ajax({
                url: window.location.origin + '/bookings/wp-content/themes/tbo-hotels/includes/php-leak-fix.php',
                type: 'GET',
                data: {
                    fallback_countries: 1
                },
                success: function(response) {
                    if (response.success && response.data) {
                        console.log('Direct endpoint countries loaded successfully');
                        populateCountrySelect(response.data);
                    } else {
                        console.log('Direct endpoint failed, using hardcoded countries');
                        useHardcodedCountries();
                    }
                },
                error: function() {
                    console.log('Direct endpoint error, using hardcoded countries');
                    useHardcodedCountries();
                },
                complete: function() {
                    hideLoading($countrySelect);
                }
            });
        }
        
        // Use hardcoded countries as last resort
        function useHardcodedCountries() {
            var fallbackCountries = [
                {Code: 'IN', Name: 'India'},
                {Code: 'US', Name: 'United States'},
                {Code: 'GB', Name: 'United Kingdom'},
                {Code: 'AE', Name: 'United Arab Emirates'},
                {Code: 'TH', Name: 'Thailand'}
            ];
            populateCountrySelect(fallbackCountries);
        }
        
        // Enhanced function to load cities
        function enhancedLoadCities(countryCode) {
            console.log('Enhanced loadCities called for country:', countryCode);
            
            // Call original function if it exists
            if (originalLoadCities) {
                try {
                    originalLoadCities(countryCode);
                } catch(e) {
                    console.error('Error in original loadCities:', e);
                    loadCitiesFallback(countryCode);
                }
            } else {
                loadCitiesFallback(countryCode);
            }
        }
        
        // Fallback function for loading cities
        function loadCitiesFallback(countryCode) {
            console.log('Using loadCitiesFallback for country:', countryCode);
            
            var $citySelect = $('#city_code');
            if (!$citySelect.length || !countryCode) return;
            
            showLoading($citySelect, 'Loading cities...');
            $citySelect.prop('disabled', true);
            
            // Try direct endpoint for cities
            $.ajax({
                url: window.location.origin + '/bookings/wp-content/themes/tbo-hotels/includes/city-dropdown-test.php',
                type: 'GET',
                data: {
                    country_code: countryCode,
                    mode: 'auto'
                },
                success: function(response) {
                    console.log('City direct endpoint response:', response);
                    
                    if (response.success && response.data) {
                        populateCitySelect(response.data);
                    } else {
                        console.log('Direct endpoint failed, using hardcoded cities');
                        useHardcodedCities(countryCode);
                    }
                },
                error: function() {
                    console.log('Direct endpoint error, using hardcoded cities');
                    useHardcodedCities(countryCode);
                },
                complete: function() {
                    hideLoading($citySelect);
                    $citySelect.prop('disabled', false);
                }
            });
        }
        
        // Use hardcoded cities as last resort
        function useHardcodedCities(countryCode) {
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
            } else {
                fallbackCities = [
                    {Code: '0', Name: 'Direct Search - Enter hotel name'}
                ];
            }
            
            populateCitySelect(fallbackCities);
        }
        
        // Helper function to populate country select
        function populateCountrySelect(countries) {
            var $countrySelect = $('#country_code');
            if (!$countrySelect.length) return;
            
            var options = '<option value="">Select Country</option>';
            $.each(countries, function(i, country) {
                options += '<option value="' + country.Code + '">' + country.Name + '</option>';
            });
            
            $countrySelect.html(options);
            
            // Set default to India if available
            setTimeout(function() {
                $countrySelect.val('IN').trigger('change');
            }, 500);
        }
        
        // Helper function to populate city select
        function populateCitySelect(cities) {
            var $citySelect = $('#city_code');
            if (!$citySelect.length) return;
            
            var options = '<option value="">Select City</option>';
            $.each(cities, function(i, city) {
                options += '<option value="' + city.Code + '">' + city.Name + '</option>';
            });
            
            $citySelect.html(options);
        }
        
        // Helper function to show loading indicator
        function showLoading($element, message) {
            $element.prop('disabled', true);
            $element.addClass('loading');
            $element.html('<option value="">' + message + '</option>');
        }
        
        // Helper function to hide loading indicator
        function hideLoading($element) {
            $element.removeClass('loading');
            $element.prop('disabled', false);
        }
        
        // Initialize the fix by triggering country load
        setTimeout(function() {
            var $countrySelect = $('#country_code');
            if ($countrySelect.length && $countrySelect.find('option').length <= 1) {
                console.log('Country dropdown needs loading, triggering enhanced loadCountries');
                enhancedLoadCountries();
            }
        }, 1000);
    });
    
})(jQuery);