// TBO Hotels City Dropdown Debugging Script
// Add this to your page template or use the Chrome DevTools Console

(function() {
    console.log("=== TBO Hotels City Dropdown Debugging Tool ===");
    
    // Function to check jQuery version and availability
    function checkJQuery() {
        if (typeof jQuery === 'undefined') {
            console.error("jQuery is not loaded!");
            return false;
        }
        
        console.info(`jQuery version: ${jQuery.fn.jquery}`);
        return true;
    }
    
    // Function to check if elements exist
    function checkElements() {
        const elements = {
            'country_dropdown': $('#country_code'),
            'city_dropdown': $('#city_code'),
            'checkin_date': $('#checkin_date'),
            'checkout_date': $('#checkout_date'),
            'search_form': $('#hotel-search-form')
        };
        
        let allFound = true;
        
        for (const [name, $el] of Object.entries(elements)) {
            if ($el.length === 0) {
                console.error(`Element not found: #${name}`);
                allFound = false;
            } else {
                console.info(`Element found: #${name}`);
            }
        }
        
        return allFound;
    }
    
    // Function to manually trigger city loading
    function triggerCityLoading(countryCode) {
        console.log(`Manually triggering city loading for country: ${countryCode}`);
        
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'tbo_hotels_get_cities',
                country_code: countryCode,
                security: ajax_object.nonce
            },
            beforeSend: function() {
                console.log('AJAX request initiated...');
                $('#city_code').html('<option value="">Loading cities...</option>');
            },
            success: function(response) {
                console.log('AJAX response received:', response);
                
                if (response.success) {
                    // Success case - Data should be in response.data
                    console.log('Success! Cities data:', response.data);
                    
                    // Check if data exists and has cities
                    if (response.data && response.data.cities) {
                        const cities = response.data.cities;
                        
                        console.log(`Processing ${cities.length} cities...`);
                        
                        // Build options HTML
                        let options = '<option value="">Select City</option>';
                        
                        // Log the first few cities for inspection
                        for (let i = 0; i < Math.min(5, cities.length); i++) {
                            console.log(`City ${i+1}:`, cities[i]);
                        }
                        
                        for (let i = 0; i < cities.length; i++) {
                            const city = cities[i];
                            options += `<option value="${city.CityCode}">${city.CityName}</option>`;
                        }
                        
                        // Update dropdown and log HTML for debugging
                        console.log('Setting city dropdown HTML...');
                        $('#city_code').html(options);
                        
                        // Verify the HTML was set correctly
                        console.log('City dropdown HTML after update:', $('#city_code').html());
                        console.log('Number of option elements:', $('#city_code option').length);
                        
                    } else {
                        console.error('Response format unexpected:', response);
                        $('#city_code').html('<option value="">Error loading cities - Invalid data format</option>');
                    }
                } else {
                    // Error case - Check for error message
                    console.error('AJAX request failed:', response);
                    
                    let errorMsg = 'Error loading cities';
                    if (response.data && response.data.message) {
                        errorMsg += ' - ' + response.data.message;
                    }
                    
                    $('#city_code').html(`<option value="">${errorMsg}</option>`);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', {xhr, status, error});
                $('#city_code').html('<option value="">Error loading cities - Network error</option>');
            },
            complete: function() {
                console.log('AJAX request complete');
            }
        });
    }
    
    // Function to monitor DOM changes on the city dropdown
    function monitorCityDropdown() {
        console.log('Starting city dropdown monitoring...');
        
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    console.log('City dropdown changed:', {
                        addedNodes: mutation.addedNodes.length,
                        removedNodes: mutation.removedNodes.length
                    });
                    console.log('Current city options:', $('#city_code option').length);
                }
            });
        });
        
        observer.observe(document.getElementById('city_code'), {
            childList: true
        });
        
        console.log('City dropdown observer attached');
    }
    
    // Initialize debug script
    function init() {
        console.log('Initializing debugging script...');
        
        if (!checkJQuery()) {
            return;
        }
        
        if (!checkElements()) {
            console.warn('Some elements are missing, debugging may be limited');
        }
        
        // Check if AJAX object is defined
        if (typeof ajax_object === 'undefined') {
            console.error('ajax_object is not defined! WordPress AJAX will not work.');
        } else {
            console.info('ajax_object found:', ajax_object);
        }
        
        // Monitor city dropdown for changes
        if ($('#city_code').length) {
            monitorCityDropdown();
        }
        
        // Add manual trigger button for debugging
        $('<div style="margin: 20px 0; padding: 10px; background: #f0f0f0; border: 1px solid #ccc;">' +
          '<h4>City Dropdown Debug Tools</h4>' +
          '<button id="debug-load-cities" style="margin-right: 10px;">Load Cities Manually</button>' +
          '<button id="debug-inspect-dropdown">Inspect City Dropdown</button>' +
          '</div>').insertAfter('#hotel-search-form');
        
        // Add event handlers for debug buttons
        $('#debug-load-cities').on('click', function() {
            const countryCode = $('#country_code').val();
            if (countryCode) {
                triggerCityLoading(countryCode);
            } else {
                console.error('Please select a country first');
                alert('Please select a country first');
            }
        });
        
        $('#debug-inspect-dropdown').on('click', function() {
            console.log('City dropdown inspection:');
            console.log('- HTML content:', $('#city_code').html());
            console.log('- Number of options:', $('#city_code option').length);
            console.log('- Selected value:', $('#city_code').val());
            console.log('- Is visible:', $('#city_code').is(':visible'));
            console.log('- Dimensions:', {
                width: $('#city_code').width(),
                height: $('#city_code').height(),
                outerWidth: $('#city_code').outerWidth(),
                outerHeight: $('#city_code').outerHeight()
            });
        });
        
        // Override the original country change handler for better debugging
        $('#country_code').off('change').on('change', function() {
            const countryCode = $(this).val();
            console.log(`Country changed to: ${countryCode}`);
            
            if (countryCode) {
                triggerCityLoading(countryCode);
            } else {
                $('#city_code').html('<option value="">Select Country First</option>');
            }
        });
        
        console.log('Debugging script initialized!');
        console.log('To test city loading, select a country or click "Load Cities Manually"');
    }
    
    // Wait for document ready
    $(document).ready(function() {
        console.log('Document ready, starting debug script...');
        init();
    });
})();