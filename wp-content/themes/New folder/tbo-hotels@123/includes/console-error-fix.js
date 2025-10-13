/**
 * Console Error Fix
 * This script fixes common JavaScript errors and ensures proper loading of components
 */

(function() {
    // Run on document ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('TBO Hotels: Console error fix active');
        
        // Fix country dropdown if not properly loaded
        fixCountryDropdown();
        
        // Add global error handler
        addGlobalErrorHandler();
    });
    
    /**
     * Fix country dropdown if it fails to load
     */
    function fixCountryDropdown() {
        setTimeout(function() {
            // Check for country dropdown
            var countryDropdown = document.getElementById('country_code');
            if (countryDropdown && countryDropdown.options.length <= 1) {
                console.log('Country dropdown needs fixing');
                
                // If jQuery is available, use it
                if (typeof jQuery !== 'undefined') {
                    // Define fallback countries
                    var fallbackCountries = [
                        {Code: 'IN', Name: 'India'},
                        {Code: 'US', Name: 'United States'},
                        {Code: 'GB', Name: 'United Kingdom'},
                        {Code: 'AE', Name: 'United Arab Emirates'},
                        {Code: 'TH', Name: 'Thailand'}
                    ];
                    
                    // Build options
                    var options = '<option value="">Select Country</option>';
                    fallbackCountries.forEach(function(country) {
                        options += '<option value="' + country.Code + '">' + country.Name + '</option>';
                    });
                    
                    // Update dropdown
                    jQuery(countryDropdown).html(options);
                    
                    // Auto-select India
                    setTimeout(function() {
                        jQuery(countryDropdown).val('IN').trigger('change');
                    }, 500);
                }
                // If jQuery is not available, use vanilla JS
                else {
                    var options = '<option value="">Select Country</option>';
                    options += '<option value="IN">India</option>';
                    options += '<option value="US">United States</option>';
                    options += '<option value="GB">United Kingdom</option>';
                    options += '<option value="AE">United Arab Emirates</option>';
                    options += '<option value="TH">Thailand</option>';
                    
                    countryDropdown.innerHTML = options;
                    
                    // Auto-select India
                    setTimeout(function() {
                        countryDropdown.value = 'IN';
                        // Trigger change event
                        var event = document.createEvent('HTMLEvents');
                        event.initEvent('change', true, false);
                        countryDropdown.dispatchEvent(event);
                    }, 500);
                }
            }
        }, 3000); // Wait 3 seconds to see if the original code loads
    }
    
    /**
     * Add a global error handler to catch and potentially fix issues
     */
    function addGlobalErrorHandler() {
        window.addEventListener('error', function(e) {
            console.log('TBO Hotels Error Caught:', e.message);
            
            // Check if the error is related to undefined variables
            if (e.message.includes('undefined') || e.message.includes('null')) {
                // Try to fix common issues
                if (!window.tbo_hotels_params && typeof jQuery !== 'undefined') {
                    console.log('Fixing missing tbo_hotels_params');
                    window.tbo_hotels_params = {
                        ajax_url: '/bookings/wp-admin/admin-ajax.php',
                        nonce: '',
                        site_url: window.location.origin + '/bookings',
                        placeholder_image: '/bookings/wp-content/themes/tbo-hotels/assets/img/placeholder.jpg'
                    };
                }
            }
            
            // Don't prevent default error handling
            return false;
        });
    }
})();