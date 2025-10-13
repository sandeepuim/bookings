/**
 * TBO Hotels AJAX Fix
 * 
 * This JavaScript file fixes issues with the country and city dropdowns
 * by providing multiple fallback mechanisms.
 */
jQuery(document).ready(function($) {
    console.log('TBO Hotels AJAX Fix loaded');
    
    // Wait for the dropdown elements to be loaded
    function checkAndFixDropdowns() {
        var $countrySelect = $('#country_code');
        var $citySelect = $('#city_code');
        
        if ($countrySelect.length) {
            // Check if country dropdown is empty or has only 1 option
            if ($countrySelect.find('option').length <= 1) {
                console.log('Country dropdown needs fixing');
                fixCountryDropdown();
            }
            
            // Add change event handler to country dropdown if not already added
            if (!$countrySelect.data('event-added')) {
                $countrySelect.on('change', function() {
                    var countryCode = $(this).val();
                    if (countryCode) {
                        fixCityDropdown(countryCode);
                    }
                });
                $countrySelect.data('event-added', true);
            }
        }
    }
    
    // Fix country dropdown
    function fixCountryDropdown() {
        var $countrySelect = $('#country_code');
        
        // Show loading state
        $countrySelect.html('<option value="">Loading countries...</option>');
        $countrySelect.prop('disabled', true);
        
        // Try AJAX request with proper headers
        $.ajax({
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
                console.log('Country AJAX response:', response);
                
                if (response && response.success && response.data && response.data.length > 0) {
                    populateCountryDropdown(response.data);
                } else {
                    console.log('Invalid response, trying fallback');
                    loadFallbackCountries();
                }
            },
            error: function(xhr, status, error) {
                console.error('Country AJAX error:', error);
                loadFallbackCountries();
            },
            complete: function() {
                $countrySelect.prop('disabled', false);
            }
        });
    }
    
    // Fix city dropdown
    function fixCityDropdown(countryCode) {
        var $citySelect = $('#city_code');
        
        if (!$citySelect.length || !countryCode) {
            return;
        }
        
        // Show loading state
        $citySelect.html('<option value="">Loading cities...</option>');
        $citySelect.prop('disabled', true);
        
        // Try AJAX request with proper headers
        $.ajax({
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
                console.log('City AJAX response:', response);
                
                if (response && response.success && response.data && response.data.length > 0) {
                    populateCityDropdown(response.data);
                } else {
                    console.log('Invalid response, trying fallback');
                    loadFallbackCities(countryCode);
                }
            },
            error: function(xhr, status, error) {
                console.error('City AJAX error:', error);
                loadFallbackCities(countryCode);
            },
            complete: function() {
                $citySelect.prop('disabled', false);
            }
        });
    }
    
    // Load fallback countries
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
        
        populateCountryDropdown(fallbackCountries);
    }
    
    // Load fallback cities
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
        
        populateCityDropdown(fallbackCities);
    }
    
    // Helper function to populate country dropdown
    function populateCountryDropdown(countries) {
        var $countrySelect = $('#country_code');
        
        var options = '<option value="">Select Country</option>';
        $.each(countries, function(i, country) {
            options += '<option value="' + country.Code + '">' + country.Name + '</option>';
        });
        
        $countrySelect.html(options);
        
        // Set default to India if available
        setTimeout(function() {
            var hasIndia = $countrySelect.find('option[value="IN"]').length > 0;
            if (hasIndia) {
                $countrySelect.val('IN').trigger('change');
            }
        }, 500);
    }
    
    // Helper function to populate city dropdown
    function populateCityDropdown(cities) {
        var $citySelect = $('#city_code');
        
        var options = '<option value="">Select City</option>';
        $.each(cities, function(i, city) {
            options += '<option value="' + city.Code + '">' + city.Name + '</option>';
        });
        
        $citySelect.html(options);
    }
    
    // Run fix after a short delay to allow normal loading first
    setTimeout(checkAndFixDropdowns, 1000);
    
    // Also run fix when document is fully loaded
    $(window).on('load', function() {
        setTimeout(checkAndFixDropdowns, 500);
    });
});