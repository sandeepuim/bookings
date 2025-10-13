<?php
/**
 * Generate theme screenshot
 * 
 * This script generates a screenshot for the theme.
 * Run it once to create the screenshot.png file.
 */

// Set the image dimensions
$width = 1200;
$height = 900;

// Create a new image
$image = imagecreatetruecolor($width, $height);

// Define colors
$bg_color = imagecolorallocate($image, 245, 245, 245);
$blue = imagecolorallocate($image, 0, 115, 170);
$dark_blue = imagecolorallocate($image, 0, 81, 119);
$text_color = imagecolorallocate($image, 51, 51, 51);
$light_gray = imagecolorallocate($image, 221, 221, 221);

// Fill the background
imagefill($image, 0, 0, $bg_color);

// Draw header
imagefilledrectangle($image, 0, 0, $width, 80, $blue);

// Draw site title
$font_path = __DIR__ . '/assets/fonts/Arial.ttf';
imagettftext($image, 24, 0, 30, 50, 255, $font_path, 'TBO Hotels');

// Draw search form
imagefilledrectangle($image, 50, 130, $width - 50, 350, 255);
imagerectangle($image, 50, 130, $width - 50, 350, $light_gray);

// Draw form title
imagettftext($image, 24, 0, 80, 170, $text_color, $font_path, 'Find Your Perfect Hotel');

// Draw form fields
imagefilledrectangle($image, 80, 200, 580, 240, $light_gray);
imagefilledrectangle($image, 600, 200, 1120, 240, $light_gray);
imagefilledrectangle($image, 80, 260, 380, 300, $light_gray);
imagefilledrectangle($image, 400, 260, 700, 300, $light_gray);
imagefilledrectangle($image, 720, 260, 850, 300, $light_gray);
imagefilledrectangle($image, 870, 260, 1000, 300, $light_gray);
imagefilledrectangle($image, 1020, 260, 1120, 300, $blue);

// Draw hotel results
for ($i = 0; $i < 3; $i++) {
    $y_offset = 400 + ($i * 150);
    
    // Hotel card
    imagefilledrectangle($image, 50, $y_offset, $width - 50, $y_offset + 130, 255);
    imagerectangle($image, 50, $y_offset, $width - 50, $y_offset + 130, $light_gray);
    
    // Hotel image
    imagefilledrectangle($image, 70, $y_offset + 15, 270, $y_offset + 115, $light_gray);
    
    // Hotel info
    imagettftext($image, 18, 0, 290, $y_offset + 35, $text_color, $font_path, 'Hotel Name ' . ($i + 1));
    
    // Star rating
    for ($star = 0; $star < 5; $star++) {
        $star_x = 290 + ($star * 20);
        imagettftext($image, 14, 0, $star_x, $y_offset + 60, $blue, $font_path, '★');
    }
    
    // Price
    imagefilledrectangle($image, $width - 200, $y_offset + 15, $width - 70, $y_offset + 115, $light_gray);
    imagettftext($image, 20, 0, $width - 170, $y_offset + 60, $blue, $font_path, '$299');
    imagefilledrectangle($image, $width - 170, $y_offset + 80, $width - 100, $y_offset + 100, $blue);
}

// Save the image
imagepng($image, __DIR__ . '/screenshot.png');
imagedestroy($image);

echo "Screenshot generated successfully!";