# Testing the TBO Hotels Integration

## Immediate Troubleshooting Steps

1. **Run the Direct API Test**
   - Visit `http://localhost/bookings/wp-content/themes/tbo-hotels/test-api.php`
   - This makes a direct cURL call to the TBO API bypassing WordPress
   - Verify that the API returns city data correctly

2. **Clear API Caches**
   - Visit `http://localhost/bookings/wp-content/themes/tbo-hotels/clear-cache.php`
   - This clears all transient caches to ensure fresh API calls

3. **Check Network Activity**
   - Open browser developer tools (F12)
   - Go to the Network tab
   - Select a country from the dropdown
   - Look for the AJAX request to admin-ajax.php
   - Check if it returns success:true and city data

4. **Check Console Logs**
   - In browser developer tools, go to Console tab
   - Look for any JavaScript errors
   - Check the debug logs we added for city processing

## API Connection Troubleshooting

If the API calls are failing:

1. **Check if your web server can access external URLs**
   - Some hosting environments restrict outbound connections
   - Try using the test-api.php script which uses direct cURL

2. **Verify API credentials**
   - The username and password are defined in functions.php
   - Make sure they match what works in Postman

3. **SSL Issues**
   - We've disabled SSL verification in our latest code
   - If still having issues, check your PHP/cURL SSL settings

4. **DNS Resolution**
   - If getting "Could not resolve host" errors
   - Check your server's DNS settings
   - Try using an IP address instead of domain name if possible

## Complete Testing Steps

1. **Test Hotel Search Form**
   - Go to the hotel search page
   - Select a country from the dropdown
   - Verify that cities load correctly
   - Select check-in and check-out dates
   - Select number of rooms, adults, and children
   - Submit the form

2. **Verify Search Results**
   - Check that results are displayed correctly
   - Verify pagination works
   - Verify sorting options work

## Debugging Tools and Techniques

1. **Enhanced Error Logging**
   - All API calls now log detailed information
   - Check your PHP error log for entries starting with "TBO API"

2. **Fallback Mechanisms**
   - We've added direct cURL as a fallback when WordPress HTTP API fails
   - The cities AJAX handler tries multiple approaches to get data

3. **Enable WordPress Debugging**
   - Add to wp-config.php:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

4. **Test with Mock Data**
   - In functions.php, change `if ($is_local && false)` to `if ($is_local && true)`
   - This will use mock data instead of real API calls

## API Reference

- **Base URL**: http://api.tbotechnology.in/TBOHolidays_HotelAPI/
- **Authentication**: Basic Auth with username:password
- **Endpoints Used**:
  - CountryList (POST): Get list of countries
  - CityList (POST): Get cities for a country
  - HotelCodeList (POST): Get hotel codes for a city
  - HotelSearch (POST): Search for hotels