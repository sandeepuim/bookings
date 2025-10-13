# Yolanda Hotel Booking - Administrator Setup Guide

## Installation & Configuration

### Required Pages
The booking system requires the following pages to be set up:

1. **Home Page (Front Page)**
   - Should use the "Front Page" template
   - Contains the hotel search form

2. **Hotel Results Page**
   - Create a new page called "Hotel Results"
   - Set the template to "Hotel Results"
   - This page will display search results

### Theme Settings

1. **Set Front Page**
   - Go to Settings > Reading
   - Set "Your homepage displays" to "A static page"
   - Select your page with the Front Page template as "Homepage"

2. **Menu Setup**
   - Go to Appearance > Menus
   - Add the Home and Hotel Results pages to your main menu

### TBO API Configuration

The API credentials are currently hardcoded, but can be updated in the following files:

1. **Theme API Client**
   - File: `wp-content\themes\Yolandabooking\inc\TboApiClient.php`
   - Update username and password in the constructor if needed

2. **Plugin API Client** 
   - File: `wp-content\plugins\tbo-hotel-booking\includes\class-tbo-hotel-booking-api.php`
   - Update API credentials if needed

## Troubleshooting

### Debug Mode

To enable detailed debugging:

1. Add the following to wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

2. Check the debug log at wp-content/debug.log

### Common Issues

1. **Missing Templates**
   - If you see "Template not found" errors, ensure the theme is properly activated
   - Check that template files exist in wp-content/themes/Yolandabooking/templates/

2. **API Connection Issues**
   - Check the WordPress debug log for API errors
   - Verify your server can connect to api.tbotechnology.in
   - Check that your API credentials are valid

3. **Form Submission Errors**
   - Check that AJAX URL is correctly configured in functions.php
   - Verify nonce creation and validation
   - Check JavaScript console for client-side errors

## Customization

### Styling

The booking form and results can be customized by editing:

1. **CSS Files**
   - `wp-content\themes\Yolandabooking\assets\css\hotel-search.css`
   - `wp-content\themes\Yolandabooking\assets\css\hotel-results.css`

### Templates

To modify the look and functionality:

1. **Search Form**
   - Edit: `wp-content\themes\Yolandabooking\templates\front-page.php`

2. **Results Page**
   - Edit: `wp-content\themes\Yolandabooking\templates\hotel-results.php`

### JavaScript

To modify form behavior:

1. **Search Form Functionality**
   - Edit: `wp-content\themes\Yolandabooking\assets\js\hotel-search.js`

## Performance Optimization

1. **API Caching**
   - Consider implementing transient API caching for country/city lists
   - Example implementation available in theme functions.php

2. **Image Optimization**
   - Optimize hotel images for faster loading
   - Consider a CDN for image delivery

## Security Considerations

1. **Input Validation**
   - All user inputs are sanitized before use
   - Additional validation can be added in front-page.php and hotel-results.php

2. **API Credentials**
   - Consider moving API credentials to wp-config.php for better security
   - Example: `define('TBO_API_USERNAME', 'your-username');`

## Support & Updates

For support or feature requests, please contact the theme developer.

Last updated: <?php echo date('Y-m-d'); ?>
