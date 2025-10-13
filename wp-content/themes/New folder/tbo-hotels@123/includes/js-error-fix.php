<?php
/**
 * JavaScript Error Fix
 * 
 * This file adds a script to fix common JavaScript syntax errors on the hotel results page.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add JavaScript error fix to footer
 * This helps catch and fix common syntax errors
 */
function tbo_hotels_add_js_error_fix() {
    // Only add to hotel results page
    if (is_page('hotel-results') || strpos($_SERVER['REQUEST_URI'], 'hotel-results') !== false) {
        ?>
        <script type="text/javascript">
        // Execute when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('TBO Hotels JS Error Fix active');
            
            // Find all script tags in the document
            var scripts = document.querySelectorAll('script:not([src])');
            var errorCount = 0;
            
            // Process each script to fix syntax errors
            scripts.forEach(function(script) {
                var content = script.textContent || '';
                if (!content || content.includes('TBO Hotels JS Error Fix active')) {
                    return;
                }
                
                var newContent = content;
                var modified = false;
                
                // Fix 1: Add parameter to catch blocks
                if (content.includes('try') && content.includes('catch')) {
                    newContent = newContent.replace(/try\s*{([\s\S]*?)}\s*catch\s*{/g, function(match, tryBlock) {
                        modified = true;
                        errorCount++;
                        return 'try {' + tryBlock + '} catch(e) {';
                    });
                }
                
                // Fix 2: Fix trailing commas in function arguments
                newContent = newContent.replace(/\(([^)]*),\s*\)/g, function(match, args) {
                    modified = true;
                    errorCount++;
                    return '(' + args.trim() + ')';
                });
                
                // Fix 3: Balance parentheses
                var openCount = (newContent.match(/\(/g) || []).length;
                var closeCount = (newContent.match(/\)/g) || []).length;
                
                if (openCount > closeCount) {
                    // Add missing closing parentheses
                    var diff = openCount - closeCount;
                    for (var i = 0; i < diff; i++) {
                        newContent += ')';
                    }
                    modified = true;
                    errorCount++;
                } else if (closeCount > openCount) {
                    // Try to remove extra closing parentheses
                    var pattern = /\)+$/;
                    var matches = newContent.match(pattern);
                    if (matches && matches[0]) {
                        var excess = Math.min(matches[0].length, closeCount - openCount);
                        newContent = newContent.substring(0, newContent.length - excess);
                        modified = true;
                        errorCount++;
                    }
                }
                
                // Replace the script if modified
                if (modified) {
                    try {
                        // Create a new script element with fixed content
                        var newScript = document.createElement('script');
                        newScript.textContent = newContent;
                        
                        // Replace the old script
                        script.parentNode.replaceChild(newScript, script);
                    } catch (e) {
                        console.error('Error replacing script:', e);
                    }
                }
            });
            
            if (errorCount > 0) {
                console.log('Fixed ' + errorCount + ' JavaScript syntax errors');
            }
            
            // Fix missing scripts
            if (typeof tboHotelBooking === 'undefined') {
                console.log('Adding tboHotelBooking placeholder');
                window.tboHotelBooking = {
                    init: function() {
                        console.log('TBO Hotel Booking placeholder initialized from PHP fix');
                        
                        // Add any critical functionality here
                        this.initHotelResults();
                    },
                    
                    initHotelResults: function() {
                        console.log('Hotel results initialized');
                        
                        // Add basic hotel results functionality
                        document.querySelectorAll('.hotel-card').forEach(function(card) {
                            card.addEventListener('click', function(e) {
                                if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON') {
                                    var link = card.querySelector('.hotel-details-link');
                                    if (link) {
                                        window.location.href = link.href;
                                    }
                                }
                            });
                        });
                    }
                };
                
                // Initialize
                setTimeout(function() {
                    if (typeof window.tboHotelBooking.init === 'function') {
                        window.tboHotelBooking.init();
                    }
                }, 500);
            }
        });
        </script>
        <?php
    }
}
add_action('wp_footer', 'tbo_hotels_add_js_error_fix', 999);