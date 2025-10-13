/**
 * TBO Hotels AJAX Response Fix
 * 
 * This script intercepts AJAX responses that contain mixed content
 * (both script tags and JSON data) which can cause syntax errors.
 */

(function($) {
    'use strict';
    
    // Store the original jQuery ajax function
    var originalAjax = $.ajax;
    
    // Override jQuery's ajax function
    $.ajax = function(url, options) {
        // If url is an object, it's actually the options
        if (typeof url === 'object') {
            options = url;
            url = undefined;
        }
        
        // Create new options object if not provided
        options = options || {};
        
        // Store the original success callback
        var originalSuccess = options.success;
        
        // Override the success callback
        options.success = function(response, textStatus, jqXHR) {
            // Process the response if it's a string (possibly containing script tags)
            if (typeof response === 'string' && 
                options.dataType !== 'html' && 
                options.dataType !== 'text') {
                
                // Try to extract JSON from the response
                var cleanedResponse = sanitizeAjaxResponse(response);
                
                try {
                    // Parse the cleaned response
                    var parsedResponse = $.parseJSON(cleanedResponse);
                    
                    // Call the original success with the parsed object
                    if (originalSuccess) {
                        originalSuccess.call(this, parsedResponse, textStatus, jqXHR);
                    }
                    
                    return;
                } catch (e) {
                    console.error('Failed to parse AJAX response:', e);
                    // If parsing fails, just pass the original response
                }
            }
            
            // Call the original success callback with the original response
            if (originalSuccess) {
                originalSuccess.call(this, response, textStatus, jqXHR);
            }
        };
        
        // Call the original ajax function with the modified options
        return originalAjax.call($, options);
    };
    
    /**
     * Sanitize AJAX response by removing script tags and HTML comments
     * 
     * @param {string} response The AJAX response string
     * @return {string} Cleaned response containing only valid JSON
     */
    function sanitizeAjaxResponse(response) {
        // If not a string, return as is
        if (typeof response !== 'string') {
            return response;
        }
        
        // Save original response for debugging
        var originalResponse = response;
        
        try {
            // First try - find the first '{' character and take everything after it
            var jsonStartPos = response.indexOf('{');
            if (jsonStartPos >= 0) {
                response = response.substr(jsonStartPos);
                
                // Find matching closing brace
                var openBraces = 0;
                var inString = false;
                var escape = false;
                var endPos = -1;
                
                for (var i = 0; i < response.length; i++) {
                    var char = response.charAt(i);
                    
                    if (escape) {
                        escape = false;
                        continue;
                    }
                    
                    if (char === '\\') {
                        escape = true;
                        continue;
                    }
                    
                    if (char === '"' && !escape) {
                        inString = !inString;
                        continue;
                    }
                    
                    if (!inString) {
                        if (char === '{') {
                            openBraces++;
                        } else if (char === '}') {
                            openBraces--;
                            
                            if (openBraces === 0) {
                                endPos = i + 1;
                                break;
                            }
                        }
                    }
                }
                
                if (endPos > 0) {
                    response = response.substr(0, endPos);
                }
            }
            
            // Second try - remove script tags and HTML comments
            response = response.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
            response = response.replace(/<!--[\s\S]*?-->/g, '');
            
            // Third try - look for JSON response pattern
            var jsonPattern = /\{[\s\S]*\}/;
            var match = response.match(jsonPattern);
            
            if (match) {
                response = match[0];
            }
            
            // Try to parse the result to validate it's proper JSON
            JSON.parse(response);
            
            return response;
        } catch (e) {
            console.error('Error sanitizing AJAX response:', e);
            
            // Last resort - try to find success/data pattern
            try {
                // Look for {"success":true,"data": pattern
                var successPattern = /\{"success":(true|false),"data":/;
                var match = originalResponse.match(successPattern);
                
                if (match) {
                    var startPos = originalResponse.indexOf(match[0]);
                    var partialResponse = originalResponse.substr(startPos);
                    
                    // Find matching closing brace
                    var openBraces = 0;
                    var inString = false;
                    var escape = false;
                    var endPos = -1;
                    
                    for (var i = 0; i < partialResponse.length; i++) {
                        var char = partialResponse.charAt(i);
                        
                        if (escape) {
                            escape = false;
                            continue;
                        }
                        
                        if (char === '\\') {
                            escape = true;
                            continue;
                        }
                        
                        if (char === '"' && !escape) {
                            inString = !inString;
                            continue;
                        }
                        
                        if (!inString) {
                            if (char === '{') {
                                openBraces++;
                            } else if (char === '}') {
                                openBraces--;
                                
                                if (openBraces === 0) {
                                    endPos = i + 1;
                                    break;
                                }
                            }
                        }
                    }
                    
                    if (endPos > 0) {
                        var cleanedResponse = partialResponse.substr(0, endPos);
                        
                        // Try to parse to validate
                        JSON.parse(cleanedResponse);
                        
                        return cleanedResponse;
                    }
                }
            } catch (innerError) {
                console.error('Final attempt to clean AJAX response failed:', innerError);
            }
            
            // If all else fails, return the original
            return originalResponse;
        }
    }
    
    // Log that the AJAX response fix is loaded
    console.log('TBO Hotels AJAX Response Fix loaded');
    
})(jQuery);