jQuery(document).ready(function($) {
    console.log('Hotel search script loaded');
    
    // Set default dates (check-in: today + 1 day, check-out: today + 3 days)
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    const dayAfterTomorrow = new Date();
    dayAfterTomorrow.setDate(dayAfterTomorrow.getDate() + 3);
    
    // Format dates as YYYY-MM-DD for the date inputs
    $('#check_in').val(formatDate(tomorrow));
    $('#check_out').val(formatDate(dayAfterTomorrow));
    
    // Form validation before submit
    $('.hotel-search-form').on('submit', function(e) {
        console.log('Form submit triggered');
        
        // Make sure country is selected
        if (!$('#country_code').val()) {
            alert('Please select a country');
            e.preventDefault();
            return false;
        }
        
        // Make sure city is selected
        if (!$('#city_code').val()) {
            alert('Please select a city');
            e.preventDefault();
            return false;
        }
        
        // Validate dates
        const checkInStr = $('#check_in').val();
        const checkOutStr = $('#check_out').val();
        
        if (!checkInStr || !checkOutStr) {
            alert('Please select both check-in and check-out dates');
            e.preventDefault();
            return false;
        }
        
        const checkIn = new Date(checkInStr);
        const checkOut = new Date(checkOutStr);
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Reset time to beginning of the day
        
        // Check for valid date objects
        if (isNaN(checkIn.getTime()) || isNaN(checkOut.getTime())) {
            alert('Please enter valid dates');
            e.preventDefault();
            return false;
        }
        
        // Check if check-in is in the past
        if (checkIn < today) {
            alert('Check-in date cannot be in the past');
            e.preventDefault();
            return false;
        }
        
        // Check if check-out is before check-in (allow same day)
        if (checkOut < checkIn) {
            alert('Check-out date cannot be before check-in date');
            e.preventDefault();
            return false;
        }
        
    console.log('Form validation passed, submitting...');
    // Perform AJAX request to get rendered HTML fragment and insert into the page
    e.preventDefault();
    const payload = {
        action: 'tbo_get_results_fragment',
        nonce: ajax_object.nonce,
        country_code: $('#country_code').val() || '',
        city_code: $('#city_code').val() || '',
        check_in: $('#check_in').val() || '',
        check_out: $('#check_out').val() || '',
        adults: $('#adults').val() || '1',
        rooms: $('#rooms').val() || '1',
        children: $('#children').val() || '0'
    };

    const resultsEl = $('#search-results');
    resultsEl.html('<div class="spinner">Searching…</div>');

    $.post(ajax_object.ajax_url, payload, function(resp) {
        if (resp && resp.success && resp.data && resp.data.html) {
            resultsEl.html(resp.data.html).show();
            // Scroll to results
            $('html, body').animate({ scrollTop: resultsEl.offset().top - 50 }, 400);
        } else {
            const err = (resp && resp.data) ? resp.data : 'No results';
            resultsEl.html('<div class="error">' + String(err) + '</div>').show();
        }
    }).fail(function(xhr) {
        resultsEl.html('<div class="error">Search failed. Please try again.</div>').show();
        console.error('Fragment request failed', xhr);
    });
    return false;
    });
    
    // When check-in date changes, update the minimum check-out date
    $('#check_in').on('change', function() {
        const checkInDate = new Date($(this).val());
        
        if (!isNaN(checkInDate.getTime())) {
            // Set check-out min date to same as check-in date (allow same-day checkout)
            const minCheckOut = new Date(checkInDate);
            
            // Format and set the min attribute
            const minCheckOutStr = formatDate(minCheckOut);
            $('#check_out').attr('min', minCheckOutStr);
            
            // If current check-out date is before new min date, update it
            const currentCheckOut = new Date($('#check_out').val());
            if (isNaN(currentCheckOut.getTime()) || currentCheckOut < checkInDate) {
                $('#check_out').val(minCheckOutStr);
            }
        }
    });
    
    // Fetch countries when the page loads
    fetchCountries();
    
    // When country is selected, fetch cities
    $('#country_code').on('change', function() {
        const countryCode = $(this).val();
        if (countryCode) {
            fetchCities(countryCode);
        } else {
            // Reset city dropdown
            $('#city_code').html('<option value="">Select a country first</option>');
        }
    });
    
    // Helper function to format dates
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // Fetch countries from TBO API
    function fetchCountries() {
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'tbo_get_countries',
                nonce: ajax_object.nonce
            },
            beforeSend: function() {
                $('#country_code').html('<option value="">Loading countries...</option>');
            },
            success: function(response) {
                if (response.success && response.data) {
                    let options = '<option value="">Select a country</option>';
                    
                    // Sort countries alphabetically
                    response.data.sort((a, b) => a.Name.localeCompare(b.Name));
                    
                    // Add options to dropdown
                    $.each(response.data, function(index, country) {
                        options += `<option value="${country.Code}">${country.Name}</option>`;
                    });
                    
                    $('#country_code').html(options);
                    
                    // Set India as default
                    $('#country_code').val('IN');
                    // Trigger change to load cities for India
                    $('#country_code').trigger('change');
                } else {
                    $('#country_code').html('<option value="">Error loading countries</option>');
                    console.error('Error fetching countries:', response);
                }
            },
            error: function(xhr, status, error) {
                $('#country_code').html('<option value="">Error loading countries</option>');
                console.error('AJAX error:', error);
            }
        });
    }
    
    // Fetch cities for a specific country
    function fetchCities(countryCode) {
        // session cache key
        const key = `yola_cities_${countryCode}`;
        const cached = sessionStorage.getItem(key);
        if (cached) {
            populateCitySelect(JSON.parse(cached));
            return;
        }

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'tbo_get_cities',
                country_code: countryCode,
                nonce: ajax_object.nonce
            },
            beforeSend: function() {
                $('#city_code').html('<option value="">Loading cities...</option>');
            },
            success: function(response) {
                if (response.success && response.data) {
                    // cache in session
                    try { sessionStorage.setItem(key, JSON.stringify(response.data)); } catch(e){}
                    populateCitySelect(response.data);
                } else {
                    $('#city_code').html('<option value="">No cities available</option>');
                    console.error('Error fetching cities:', response);
                }
            },
            error: function(xhr, status, error) {
                $('#city_code').html('<option value="">Error loading cities</option>');
                console.error('AJAX error:', error);
            }
        });
    }

    function populateCitySelect(data) {
        if (!Array.isArray(data)) data = [];
        let options = '<option value="">Select a city</option>';
        data.sort((a, b) => (a.Name || a.CityName || a.name || '').localeCompare(b.Name || b.CityName || b.name || ''));
        $.each(data, function(index, city) {
            const code = city.Code || city.CityCode || city.Id || city.CityId || '';
            const name = city.Name || city.CityName || city.Name || city.city || '';
            options += `<option value="${code}">${name}</option>`;
        });
        $('#city_code').html(options);
        $('#city_code').prop('disabled', false);
    }

    // Get hotel codes for a city (cache in session)
    function getHotelCodes(cityCode) {
        return new Promise(function(resolve, reject) {
            const key = `yola_hotelcodes_${cityCode}`;
            const cached = sessionStorage.getItem(key);
            if (cached) {
                resolve(JSON.parse(cached));
                return;
            }

            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: { action: 'tbo_get_hotel_codes', city_code: cityCode, nonce: ajax_object.nonce },
                success: function(resp) {
                    if (resp.success && resp.data && Array.isArray(resp.data.hotelCodes)) {
                        try { sessionStorage.setItem(key, JSON.stringify(resp.data.hotelCodes)); } catch(e){}
                        resolve(resp.data.hotelCodes);
                    } else {
                        resolve([]);
                    }
                },
                error: function() {
                    resolve([]);
                }
            });
        });
    }

    // Perform search (calls server-side proxy which does chunking)
    async function performSearch() {
        const cityCode = $('#city_code').val();
        const checkIn = $('#check_in').val();
        const checkOut = $('#check_out').val();
        const adults = parseInt($('#adults').val() || '1', 10);

        const resultsEl = $('#search-results');
        resultsEl.html('<div class="spinner">Searching…</div>');

        try {
            const resp = await $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: { action: 'tbo_search_hotels', city_code: cityCode, check_in: checkIn, check_out: checkOut, adults: adults, nonce: ajax_object.nonce },
            });

            // Always log response for debug
            console.log('tbo_search_hotels response:', resp);
            showRawDebug(resp);

            if (resp && resp.success && resp.data && Array.isArray(resp.data.hotels)) {
                renderResults(resp.data.hotels);
            } else {
                resultsEl.html('<div class="error">No hotels found</div>');
            }
        } catch (err) {
            resultsEl.html('<div class="error">Search failed. Please try again.</div>');
            console.error('Search error', err);
            showRawDebug({ error: String(err) });
        }
    }

    function renderResults(hotels) {
        const resultsEl = $('#search-results');
        if (!hotels || hotels.length === 0) {
            resultsEl.html('<div class="no-results">No hotels found for selected dates.</div>');
            return;
        }
        const html = hotels.map(h => (`\n            <article class="hotel">\n                <h3>${escapeHtml(h.name || 'Unnamed')}</h3>\n                <div class="price">₹${h.price !== null ? (h.price).toFixed(2) : 'N/A'}</div>\n                <div class="meta">Code: ${escapeHtml(h.code || '')}</div>\n            </article>`)).join('');
        resultsEl.html(html);
    }

    function escapeHtml(s){ return (s+'').replace(/[&<>"]+/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]; }); }

    // Debug helper: show raw response JSON under results
    function showRawDebug(obj) {
        try {
            let debugEl = $('#search-debug');
            if (!debugEl.length) {
                debugEl = $('<pre id="search-debug" style="white-space:pre-wrap;background:#f5f5f5;border:1px solid #ddd;padding:10px;margin-top:10px;font-size:12px;">');
                $('#search-results').after(debugEl);
            }
            debugEl.text(JSON.stringify(obj, null, 2));
        } catch (e) { console.error('debug show failed', e); }
    }
});
