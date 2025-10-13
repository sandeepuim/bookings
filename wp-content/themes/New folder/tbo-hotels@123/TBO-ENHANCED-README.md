# TBO Hotels Enhanced Implementation

This enhanced implementation of the TBO Hotels API provides robust error handling, improved user experience, and better performance through intelligent caching.

## Features

- **Robust Error Handling**: Graceful handling of API errors with fallback to stale cache when necessary
- **Improved User Experience**: Loading indicators, error messages, and a more responsive interface
- **Intelligent Caching**: Dynamic cache expiration based on search date proximity
- **Enhanced Frontend**: Modern, responsive design for hotel listings and details
- **AJAX Response Sanitization**: Prevents JavaScript errors from mixed content responses
- **Parameter Validation**: Better validation of input parameters before making API requests
- **Multiple Shortcodes**: Flexible shortcodes for different use cases
- **Settings Page**: Easy configuration of API credentials and cache settings

## File Structure

- `includes/tbo-api-enhancement.php` - Core API implementation with improved error handling
- `includes/tbo-enhanced-loader.php` - Loader script that handles script/style enqueuing and shortcodes
- `includes/tbo-enhanced-install.php` - Installation script for creating demo pages
- `assets/js/tbo-enhanced.js` - Enhanced JavaScript for AJAX handling and UI
- `assets/js/ajax-response-fix.js` - Script to fix mixed content in AJAX responses
- `assets/css/tbo-enhanced.css` - Styles for the enhanced UI
- `functions-tbo-enhanced.php` - Integration with the theme

## Shortcodes

### Hotel Search Form

```
[tbo_enhanced_search title="Find Your Perfect Hotel" results_page="123" default_city_id="" default_city_name=""]
```

### Hotel Results

```
[tbo_enhanced_results title="Hotels for Your Stay" default_city_id=""]
```

### Hotel Details

```
[tbo_enhanced_details title="Hotel Information"]
```

## Usage

1. Configure API credentials in Settings > TBO API Settings
2. Add the shortcodes to your pages
3. Customize the appearance using the theme customizer

## Installation

The enhanced functionality is automatically loaded when the theme is activated. You can create demo pages by visiting the TBO Enhanced Install page from the admin notice or by going to the hidden menu at `admin.php?page=tbo-enhanced-install`.

## Fallback Mechanism

The implementation uses a stale cache mechanism to ensure availability even when the API is down:

1. First checks for fresh cache
2. If no fresh cache, makes API request
3. If API request fails, falls back to stale cache
4. If no stale cache, shows error message

## Cache Expiration

Cache expiration is dynamically determined based on search date proximity:

- Less than 3 days: 30 minutes
- Less than 7 days: 1 hour
- Less than 30 days: 2 hours
- More than 30 days: 24 hours

This ensures fresher results for imminent travel dates while reducing API calls for far-future searches.

## Error Handling

The implementation provides comprehensive error handling:

- Network errors
- API response errors
- Invalid response format
- Parameter validation errors

Each error is logged and displayed to the user in a user-friendly way, with fallback to stale cache when possible.

## Performance Optimization

The implementation includes several performance optimizations:

- Intelligent caching based on search proximity
- Pagination for hotel results
- Lazy loading of hotel images
- Client-side caching of city search results

## Browser Compatibility

The enhanced implementation is compatible with:

- Chrome 49+
- Firefox 52+
- Safari 10+
- Edge 16+
- IE 11 (with polyfills)

## Credits

Developed as an enhancement to the TBO Hotels API integration.

## License

This implementation is licensed under the same license as the parent theme.