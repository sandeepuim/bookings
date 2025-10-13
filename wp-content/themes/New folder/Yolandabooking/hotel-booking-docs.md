# Yolanda Hotel Booking System Documentation

## Overview
The Yolanda Hotel Booking System integrates with TBO Hotel API to provide a complete hotel search and booking solution for your WordPress site. This document explains how to use the system and troubleshoot common issues.

## Features
- Hotel search by country and city
- Date selection with validation
- Room and guest configuration
- Detailed hotel results with pricing
- Admin dashboard for managing bookings

## How to Use

### Search for Hotels
1. Navigate to the homepage
2. Select a country from the dropdown
3. Select a city from the dropdown
4. Choose check-in and check-out dates
5. Select the number of rooms, adults, and children
6. Click "Search Hotels"

### View Search Results
- Results will display available hotels with details
- Each hotel card shows:
  - Hotel name and rating
  - Address and location
  - Available amenities
  - Room prices and availability
  - Booking options

### Make a Booking
1. Find a hotel you like in the search results
2. Click "Book Now" button
3. Complete the guest information form
4. Confirm booking details
5. Complete payment process
6. Receive booking confirmation

## Troubleshooting

### Common Issues

#### 1. Invalid Date Error
If you see "Invalid date entered" or "Check-in date should be less than Check-out date" errors:
- Make sure check-in date is today or later
- Make sure check-out date is after check-in date
- Try selecting dates from the calendar picker instead of typing

#### 2. No Hotels Found
If no hotels are displayed in search results:
- Try a different city or country
- Try different dates (some periods may be fully booked)
- Reduce the number of rooms or guests

#### 3. API Errors
If you see "API request failed" or other technical errors:
- Refresh the page and try again
- Check your internet connection
- If the problem persists, contact the site administrator

## For Administrators

### Debug Information
For site administrators, detailed debug information is available:
- API request and response details are logged in WordPress debug log
- Full error messages are shown when logged in as admin
- Check PHP error logs for detailed troubleshooting

### API Configuration
The system uses TBO Hotel API with the following credentials:
- Username: YOLANDATHTest
- Password: Yol@40360746

### Plugin Dependencies
This theme works with the following plugins:
- TBO Hotel Booking plugin (for admin functionality)

## Support
For additional support, please contact your site administrator or developer.
