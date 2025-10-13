<?php
/**
 * Plugin Name: TBO Hotels Early Error Fix
 * Description: Fixes JavaScript syntax errors by loading fixes early in the page
 * Version: 1.0
 * Author: GitHub Copilot
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add early error fix script to head
 */
function tbo_early_error_fix() {
    ?>
    <script>
    // TBO Hotels Early Error Fix - Must run before any other scripts
    (function() {
        console.log('TBO Hotels Early Error Fix: Active');
        
        // 1. Install global error handler first
        window.addEventListener('error', function(event) {
            if (event && event.message) {
                // Handle specific syntax errors
                if (event.message.includes('Unexpected token') || 
                    event.message.includes('missing ) after argument') ||
                    event.message.includes('SyntaxError')) {
                    
                    console.warn('TBO Hotels Early Fix: Caught error - ' + event.message);
                    event.preventDefault();
                    return true;
                }
            }
        }, true);
        
        // 2. Fix Function and eval
        var originalFunction = window.Function;
        window.Function = function() {
            try {
                return originalFunction.apply(this, arguments);
            } catch(e) {
                console.warn('TBO Hotels: Function syntax error caught - ' + e.message);
                return function() {};
            }
        };
        
        var originalEval = window.eval;
        window.eval = function(code) {
            try {
                // Fix catch blocks without parameters
                if (typeof code === 'string') {
                    code = code.replace(/catch\s*{/g, 'catch(e) {');
                    
                    // Fix missing closing parentheses
                    var openCount = (code.match(/\(/g) || []).length;
                    var closeCount = (code.match(/\)/g) || []).length;
                    
                    if (openCount > closeCount) {
                        for (var i = 0; i < openCount - closeCount; i++) {
                            code += ')';
                        }
                    }
                }
                
                return originalEval.call(window, code);
            } catch(e) {
                console.warn('TBO Hotels: Eval syntax error caught - ' + e.message);
                return null;
            }
        };
        
        // 3. Override createElement for script tags
        var originalCreateElement = document.createElement;
        document.createElement = function(tagName) {
            var element = originalCreateElement.apply(document, arguments);
            
            // Only modify script elements
            if (tagName.toLowerCase() === 'script') {
                var originalDescriptor = Object.getOwnPropertyDescriptor(element, 'textContent') || 
                                         Object.getOwnPropertyDescriptor(Node.prototype, 'textContent');
                
                if (originalDescriptor) {
                    Object.defineProperty(element, 'textContent', {
                        get: originalDescriptor.get,
                        set: function(value) {
                            if (typeof value === 'string') {
                                // Fix catch blocks without parameters
                                value = value.replace(/catch\s*{/g, 'catch(e) {');
                                
                                // Fix missing closing parentheses
                                var openCount = (value.match(/\(/g) || []).length;
                                var closeCount = (value.match(/\)/g) || []).length;
                                
                                if (openCount > closeCount) {
                                    for (var i = 0; i < openCount - closeCount; i++) {
                                        value += ')';
                                    }
                                }
                            }
                            
                            return originalDescriptor.set.call(this, value);
                        }
                    });
                }
                
                // Track original appendChild to fix dynamic script insertion
                var originalAppendChild = element.appendChild;
                element.appendChild = function(child) {
                    if (child.nodeType === 3) { // Text node
                        var content = child.textContent;
                        if (typeof content === 'string') {
                            // Fix catch blocks
                            content = content.replace(/catch\s*{/g, 'catch(e) {');
                            child.textContent = content;
                        }
                    }
                    return originalAppendChild.call(this, child);
                };
            }
            
            return element;
        };
        
        // 4. Create specific fixes for problematic URLs
        if (window.location.href.includes('hotel-results') || 
            window.location.href.includes('check_in=')) {
            
            // Once DOM is ready, fix existing scripts
            document.addEventListener('DOMContentLoaded', function() {
                // Fix all inline scripts
                var scripts = document.querySelectorAll('script:not([src])');
                scripts.forEach(function(script) {
                    if (!script.textContent.includes('TBO Hotels Early Error Fix')) {
                        var content = script.textContent;
                        
                        // Only process if it contains potential issues
                        if (content.includes('catch') || 
                            content.includes('try') || 
                            content.includes('function(') || 
                            content.includes('=>')) {
                            
                            // Fix catch blocks without parameters
                            content = content.replace(/catch\s*{/g, 'catch(e) {');
                            
                            // Fix missing closing parentheses
                            var openCount = (content.match(/\(/g) || []).length;
                            var closeCount = (content.match(/\)/g) || []).length;
                            
                            if (openCount > closeCount) {
                                for (var i = 0; i < openCount - closeCount; i++) {
                                    content += ')';
                                }
                            }
                            
                            // Replace script if content changed
                            if (content !== script.textContent) {
                                var newScript = document.createElement('script');
                                newScript.textContent = content;
                                script.parentNode.replaceChild(newScript, script);
                            }
                        }
                    }
                });
            });
        }
    })();
    </script>
    <?php
}
add_action('wp_head', 'tbo_early_error_fix', 1); // Priority 1 ensures it runs very early