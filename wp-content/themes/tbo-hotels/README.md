# TBO Hotels WordPress Theme

A custom WordPress theme that integrates with the TBO Holidays Hotel API for hotel search and booking functionality.

## Features

- Fully integrated with the TBO Holidays Hotel API
- Hotel search functionality with country and city selection
- Date range picker for check-in and check-out dates
- Room and guest configuration options
- Display of hotel search results with filtering and sorting
- Detailed hotel information pages
- Responsive design for all device sizes
- Caching system for API responses to improve performance

## Installation

1. Download the theme zip file or clone the repository
2. Upload the theme to your WordPress site via the admin panel or FTP
3. Go to Appearance > Themes and activate the "TBO Hotels" theme
4. Create the following pages:
   - Hotel Search: Use the "Hotel Search Page" template
   - Hotel Results: Use the "Hotel Results" template
   - Hotel Details: Use the "Hotel Details" template

## Usage

### Creating the Hotel Search Page

1. Go to Pages > Add New
2. Enter a title (e.g., "Hotel Search")
3. Select the "Hotel Search Page" template from the Page Attributes panel
4. Publish the page

### Creating the Hotel Results Page

1. Go to Pages > Add New
2. Enter a title (e.g., "Hotel Results")
3. Select the "Hotel Results" template from the Page Attributes panel
4. Publish the page

### Creating the Hotel Details Page

1. Go to Pages > Add New
2. Enter a title (e.g., "Hotel Details")
3. Select the "Hotel Details" template from the Page Attributes panel
4. Publish the page

### Setting Up Navigation

1. Go to Appearance > Menus
2. Create a new menu or edit an existing one
3. Add the Hotel Search page to your menu
4. Set the menu location to "Primary Menu"
5. Save the menu

## API Integration

This theme integrates with the TBO Holidays Hotel API with the following features:

- Get countries list with caching (24-hour cache)
- Get cities by country with caching (12-hour cache)
- Get hotel codes with caching (6-hour cache)
- Search hotels with chunking logic for large requests
- Get detailed hotel information

## Customization

### Changing Theme Colors

The theme uses CSS variables for colors. You can modify the colors by editing the `:root` section in the `style.css` file:

```css
:root {
    --primary-color: #0073aa;
    --secondary-color: #005177;
    --accent-color: #f7f7f7;
    --text-color: #333;
    --light-text: #777;
    --border-color: #ddd;
    --success-color: #46b450;
    --error-color: #dc3232;
}
```

### Adding Custom CSS

You can add custom CSS by:

1. Going to Appearance > Customize
2. Selecting "Additional CSS"
3. Adding your custom styles

### Modifying Templates

The theme includes the following templates that you can modify:

- `templates/hotel-search.php`: Hotel search form
- `templates/hotel-results.php`: Search results display
- `templates/hotel-details.php`: Hotel details page

## Support and Documentation

For support or additional documentation, please contact the theme developer.

## Credits

This theme was developed as a custom solution for integrating the TBO Holidays Hotel API with WordPress.