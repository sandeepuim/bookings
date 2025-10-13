// Monitoring script for AJAX requests
console.log('TBO Hotels AJAX Monitoring active');

// Keep track of original methods
var originalXHROpen = XMLHttpRequest.prototype.open;
var originalXHRSend = XMLHttpRequest.prototype.send;
var originalFetch = window.fetch;

// Monitor XHR requests
XMLHttpRequest.prototype.open = function(method, url) {
    this._tboMethod = method;
    this._tboUrl = url;
    
    // Check if it's one of our AJAX requests
    if (url.includes('admin-ajax.php') && 
        (url.includes('tbo_hotels_get_countries') || url.includes('tbo_hotels_get_cities'))) {
        console.log('Monitoring TBO AJAX request:', method, url);
        
        // Add load event listener to log response
        this.addEventListener('load', function() {
            console.log('TBO AJAX response received:', {
                url: this._tboUrl,
                status: this.status,
                response: this.responseText.substring(0, 500) + (this.responseText.length > 500 ? '...' : '')
            });
            
            // Try to parse the response
            try {
                var jsonResponse = JSON.parse(this.responseText);
                console.log('Parsed JSON response:', jsonResponse);
                
                // Check if it's a valid response
                if (!jsonResponse.success) {
                    console.error('AJAX response indicates an error:', jsonResponse);
                }
            } catch (e) {
                console.error('Failed to parse AJAX response as JSON:', e);
                console.log('Response starts with:', this.responseText.substring(0, 100));
                
                // Look for PHP or HTML in the response
                if (this.responseText.includes('<?php') || 
                    this.responseText.includes('<!DOCTYPE') || 
                    this.responseText.includes('<html')) {
                    console.error('Response contains PHP or HTML code which will break JSON parsing');
                }
            }
        });
        
        // Add error event listener
        this.addEventListener('error', function() {
            console.error('TBO AJAX request failed:', {
                url: this._tboUrl,
                status: this.status
            });
        });
    }
    
    // Call original method
    return originalXHROpen.apply(this, arguments);
};

XMLHttpRequest.prototype.send = function(body) {
    // Log request body for our AJAX requests
    if (this._tboUrl && this._tboUrl.includes('admin-ajax.php')) {
        console.log('TBO AJAX request body:', body);
    }
    
    // Call original method
    return originalXHRSend.apply(this, arguments);
};

// Monitor fetch requests
window.fetch = function(url, options) {
    // Check if it's one of our AJAX requests
    if (url.includes('admin-ajax.php')) {
        console.log('Fetch request to AJAX endpoint:', url, options);
        
        // Return a promise to handle the response
        return originalFetch.apply(this, arguments)
            .then(function(response) {
                console.log('Fetch response received:', response);
                return response;
            })
            .catch(function(error) {
                console.error('Fetch request failed:', error);
                throw error;
            });
    }
    
    // Call original method for other requests
    return originalFetch.apply(this, arguments);
};

// Add event listener for jQuery AJAX requests
if (typeof jQuery !== 'undefined') {
    jQuery(document).ajaxSend(function(event, xhr, settings) {
        if (settings.url.includes('admin-ajax.php')) {
            console.log('jQuery AJAX request:', settings.type, settings.url, settings.data);
        }
    });
    
    jQuery(document).ajaxSuccess(function(event, xhr, settings, data) {
        if (settings.url.includes('admin-ajax.php')) {
            console.log('jQuery AJAX success:', settings.url, data);
        }
    });
    
    jQuery(document).ajaxError(function(event, xhr, settings, error) {
        if (settings.url.includes('admin-ajax.php')) {
            console.error('jQuery AJAX error:', settings.url, error, xhr.responseText);
        }
    });
}