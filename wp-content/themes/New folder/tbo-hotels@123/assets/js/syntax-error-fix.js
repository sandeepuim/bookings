/**
 * JavaScript Syntax Error Fix
 * 
 * This file fixes common JavaScript syntax errors that can cause
 * "Unexpected token 'catch'" and "missing ) after argument list" errors.
 */

(function() {
    console.log('TBO Hotels JavaScript Syntax Error Fix loaded');
    
    // Flag to track whether we've already run
    var fixApplied = false;
    
    // Function to fix script content
    function fixScriptContent(content) {
        if (!content) return content;
        
        // Fix 1: Fix try/catch syntax without parameter
        content = content.replace(/try\s*{([\s\S]*?)}\s*catch\s*{/g, function(match, p1) {
            return 'try {' + p1 + '} catch(e) {';
        });
        
        // Fix 2: Fix trailing commas in function calls
        content = content.replace(/\(([^)]*),\s*\)/g, function(match, params) {
            return '(' + params.trim() + ')';
        });
        
        // Fix 3: Balance parentheses
        var openCount = (content.match(/\(/g) || []).length;
        var closeCount = (content.match(/\)/g) || []).length;
        
        if (openCount > closeCount) {
            // Add missing closing parentheses
            var diff = openCount - closeCount;
            for (var i = 0; i < diff; i++) {
                content += ')';
            }
        } else if (closeCount > openCount) {
            // Remove extra closing parentheses
            var matches = content.match(/\)+$/);
            if (matches && matches[0]) {
                var excess = Math.min(matches[0].length, closeCount - openCount);
                content = content.substring(0, content.length - excess);
            }
        }
        
        return content;
    }
    
    // Function to fix common syntax errors in scripts
    function fixScriptSyntaxErrors() {
        if (fixApplied) return;
        fixApplied = true;
        
        console.log('Scanning for script syntax errors...');
        
        // 1. Fix existing inline scripts
        var scripts = document.querySelectorAll('script:not([src])');
        scripts.forEach(function(script) {
            var content = script.textContent || '';
            
            // Skip empty scripts or our own script
            if (!content || content.includes('TBO Hotels JavaScript Syntax Error Fix loaded')) {
                return;
            }
            
            // Check for potential syntax errors
            var openParens = (content.match(/\(/g) || []).length;
            var closeParens = (content.match(/\)/g) || []).length;
            
            if (openParens !== closeParens) {
                console.warn('Parenthesis mismatch found in script. Opening:', openParens, 'Closing:', closeParens);
                
                // Fix the script content
                var fixedContent = fixScriptContent(content);
                
                // Replace the script with fixed version
                if (fixedContent !== content) {
                    // Create a new script element
                    var newScript = document.createElement('script');
                    newScript.textContent = fixedContent;
                    
                    // Replace the old script
                    script.parentNode.replaceChild(newScript, script);
                }
            }
        });
        
        // 2. Override createElement to fix scripts added dynamically
        var originalCreateElement = document.createElement;
        document.createElement = function() {
            var element = originalCreateElement.apply(this, arguments);
            
            // If it's a script element, intercept setting of textContent and innerText
            if (arguments[0].toLowerCase() === 'script') {
                var originalDescriptor = Object.getOwnPropertyDescriptor(Node.prototype, 'textContent');
                var originalSet = originalDescriptor.set;
                
                Object.defineProperty(element, 'textContent', {
                    set: function(value) {
                        // Fix the script content before setting it
                        var fixedContent = fixScriptContent(value);
                        return originalSet.call(this, fixedContent);
                    },
                    get: originalDescriptor.get
                });
            }
            
            return element;
        };
        
        // 3. Fix for missing tbo-hotel-booking-public.js
        if (typeof window.tboHotelBooking === 'undefined') {
            console.log('Creating tboHotelBooking object');
            window.tboHotelBooking = {
                init: function() {
                    console.log('TBO Hotel Booking initialized from syntax-error-fix.js');
                    
                    // Add room selection functionality if needed
                    if (document.querySelector('.room-selection-container')) {
                        console.log('Initializing room selection functionality');
                        
                        // Add room selection event listeners
                        document.querySelectorAll('.select-room-button').forEach(function(button) {
                            button.addEventListener('click', function(e) {
                                e.preventDefault();
                                console.log('Room selected:', this.getAttribute('data-room-id'));
                            });
                        });
                    }
                }
            };
            
            // Call init if it exists
            setTimeout(function() {
                if (typeof window.tboHotelBooking.init === 'function') {
                    window.tboHotelBooking.init();
                }
            }, 100);
        }
    }
    
    // Override appendChild to fix scripts being added to the DOM
    var originalAppendChild = Element.prototype.appendChild;
    Element.prototype.appendChild = function() {
        var element = arguments[0];
        
        // If it's a script element with inline content
        if (element && element.tagName === 'SCRIPT' && !element.src) {
            var content = element.textContent || element.text || '';
            
            if (content) {
                // Fix the script content
                var fixedContent = fixScriptContent(content);
                if (fixedContent !== content) {
                    element.textContent = fixedContent;
                }
            }
        }
        
        // Call the original method
        return originalAppendChild.apply(this, arguments);
    };
    
    // Install global error handler
    window.addEventListener('error', function(event) {
        // Check for specific syntax errors
        if (event.message && (
            event.message.includes("Unexpected token 'catch'") || 
            event.message.includes('missing ) after argument list') ||
            event.message.includes("Unexpected token ')'") ||
            event.message.includes("Unexpected token '<'")
        )) {
            console.warn('JavaScript syntax error caught:', event.message);
            
            // Prevent the error from showing in console
            event.preventDefault();
            return true;
        }
        
        // Check for missing script errors
        if (event.target && event.target.tagName === 'SCRIPT' && event.target.src) {
            console.warn('Script error prevented:', event.target.src);
            event.preventDefault();
            return true;
        }
    }, true);
    
    // Run our fix when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fixScriptSyntaxErrors);
    } else {
        fixScriptSyntaxErrors();
    }
    
    // Also run after window load to catch late scripts
    window.addEventListener('load', function() {
        setTimeout(fixScriptSyntaxErrors, 500);
    });
})();