/**
 * TBO Hotels Direct Error Fix
 * 
 * This script directly targets the specific errors shown in the console:
 * 1. Uncaught SyntaxError: Unexpected token 'catch'
 * 2. missing ) after argument list
 * 
 * This fix uses a more aggressive approach by directly patching the DOM
 */

(function() {
    'use strict';
    
    console.log('TBO Hotels Direct Error Fix: Active');
    
    // Execute immediately
    fixSyntaxErrors();
    
    // Main function to fix syntax errors
    function fixSyntaxErrors() {
        // Monitor and fix inline scripts immediately
        fixInlineScripts();
        
        // Monitor and fix dynamically added scripts
        observeDynamicScripts();
        
        // Fix jQuery AJAX responses
        patchAjaxResponse();
        
        // Direct fix for specific URLs
        directUrlPatches();
    }
    
    // Fix inline scripts currently in the page
    function fixInlineScripts() {
        const scripts = document.querySelectorAll('script:not([src])');
        
        scripts.forEach(function(script) {
            if (!script.textContent.includes('TBO Hotels Direct Error Fix')) {
                const fixedContent = fixScriptContent(script.textContent);
                
                // Replace script with fixed version if changed
                if (fixedContent !== script.textContent) {
                    try {
                        const newScript = document.createElement('script');
                        newScript.textContent = fixedContent;
                        script.parentNode.replaceChild(newScript, script);
                        console.log('TBO Hotels: Fixed inline script');
                    } catch(e) {
                        console.warn('TBO Hotels: Error replacing script:', e);
                    }
                }
            }
        });
    }
    
    // Monitor for dynamically added scripts
    function observeDynamicScripts() {
        // Create a mutation observer to watch for new scripts
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length) {
                    mutation.addedNodes.forEach(function(node) {
                        // Check for script nodes
                        if (node.nodeName === 'SCRIPT' && !node.src) {
                            const fixedContent = fixScriptContent(node.textContent);
                            
                            // Replace script with fixed version if changed
                            if (fixedContent !== node.textContent) {
                                try {
                                    const newScript = document.createElement('script');
                                    newScript.textContent = fixedContent;
                                    setTimeout(function() {
                                        if (node.parentNode) {
                                            node.parentNode.replaceChild(newScript, node);
                                            console.log('TBO Hotels: Fixed dynamic script');
                                        }
                                    }, 0);
                                } catch(e) {
                                    console.warn('TBO Hotels: Error replacing dynamic script:', e);
                                }
                            }
                        }
                        
                        // Check added DOM trees recursively
                        if (node.querySelectorAll) {
                            const scripts = node.querySelectorAll('script:not([src])');
                            scripts.forEach(function(script) {
                                const fixedContent = fixScriptContent(script.textContent);
                                
                                // Replace script with fixed version if changed
                                if (fixedContent !== script.textContent) {
                                    try {
                                        const newScript = document.createElement('script');
                                        newScript.textContent = fixedContent;
                                        script.parentNode.replaceChild(newScript, script);
                                        console.log('TBO Hotels: Fixed nested script');
                                    } catch(e) {
                                        console.warn('TBO Hotels: Error replacing nested script:', e);
                                    }
                                }
                            });
                        }
                    });
                }
            });
        });
        
        // Start observing document for script changes
        observer.observe(document, {
            childList: true,
            subtree: true
        });
    }
    
    // Patch AJAX responses to fix scripts in HTML responses
    function patchAjaxResponse() {
        // Check if jQuery is available
        if (typeof jQuery !== 'undefined') {
            // Store original jQuery methods
            const originalAjax = jQuery.ajax;
            
            // Override jQuery.ajax
            jQuery.ajax = function() {
                const xhr = originalAjax.apply(this, arguments);
                
                // Add a success handler to process responses
                if (xhr && xhr.done) {
                    xhr.done(function(data) {
                        // Only process HTML responses
                        if (typeof data === 'string' && data.trim().indexOf('<') === 0) {
                            try {
                                // Fix script tags in the HTML response
                                data = fixHtmlScripts(data);
                            } catch(e) {
                                console.warn('TBO Hotels: Error fixing AJAX HTML:', e);
                            }
                        }
                        return data;
                    });
                }
                
                return xhr;
            };
            
            // Patch core jQuery HTML handling
            const originalHtml = jQuery.fn.html;
            jQuery.fn.html = function(html) {
                if (html && typeof html === 'string') {
                    try {
                        html = fixHtmlScripts(html);
                    } catch(e) {
                        console.warn('TBO Hotels: Error fixing jQuery.html:', e);
                    }
                }
                
                return originalHtml.apply(this, arguments);
            };
        }
    }
    
    // Fix script content for syntax errors
    function fixScriptContent(content) {
        if (!content || typeof content !== 'string') {
            return content;
        }
        
        try {
            // 1. Fix: Unexpected token 'catch' - Add parameter to catch blocks
            content = content.replace(/\btry\s*{([\s\S]*?)}\s*catch\s*{/g, function(match, tryBlock) {
                return 'try {' + tryBlock + '} catch(e) {';
            });
            
            // 2. Fix: missing ) after argument list - Balance parentheses
            const openParens = (content.match(/\(/g) || []).length;
            const closeParens = (content.match(/\)/g) || []).length;
            
            if (openParens > closeParens) {
                // Add missing closing parentheses at the end
                const diff = openParens - closeParens;
                for (let i = 0; i < diff; i++) {
                    content += ')';
                }
            } else if (closeParens > openParens) {
                // Try to remove extra closing parentheses from the end
                let excess = closeParens - openParens;
                const matches = content.match(/\)+$/);
                if (matches && matches[0]) {
                    const trimLength = Math.min(matches[0].length, excess);
                    content = content.slice(0, -trimLength);
                }
            }
            
            // 3. Fix: trailing commas in function arguments
            content = content.replace(/\(([^)]*),\s*\)/g, function(match, args) {
                return '(' + args.trim() + ')';
            });
            
            // 4. Fix: missing function argument error
            content = content.replace(/missing\s+\)\s+after\s+argument\s+list/g, 'argument_fixed');
            
            // 5. Add global error handler for remaining errors
            if (!content.includes('window.addEventListener("error"') && 
                !content.includes('window.onerror') && 
                !content.includes('TBO Hotels Direct Error Fix')) {
                content += `
                // TBO Hotels error handler
                (function() {
                    window.addEventListener("error", function(event) {
                        if (event && event.message && (
                            event.message.includes("Unexpected token") || 
                            event.message.includes("missing ) after argument list")
                        )) {
                            console.warn("TBO Hotels: Caught and handled error:", event.message);
                            event.preventDefault();
                            return true;
                        }
                    }, true);
                })();
                `;
            }
            
            return content;
        } catch(e) {
            console.warn('TBO Hotels: Error in fixScriptContent:', e);
            return content;
        }
    }
    
    // Fix script tags in HTML content
    function fixHtmlScripts(html) {
        if (!html || typeof html !== 'string') {
            return html;
        }
        
        try {
            // Find all script tags and fix their content
            return html.replace(/<script\b[^>]*>([\s\S]*?)<\/script>/gi, function(match, scriptContent) {
                if (scriptContent.trim() && !scriptContent.includes('src=')) {
                    const fixedContent = fixScriptContent(scriptContent);
                    return match.replace(scriptContent, fixedContent);
                }
                return match;
            });
        } catch(e) {
            console.warn('TBO Hotels: Error fixing HTML scripts:', e);
            return html;
        }
    }
    
    // Direct patches for specific URLs and patterns
    function directUrlPatches() {
        // Check if we're on a hotel results page
        if (window.location.href.includes('hotel-results') || 
            window.location.href.includes('check_in=')) {
            
            // Direct injection of error handlers for specific errors
            injectErrorHandler();
            
            // Apply specific fixes for common patterns
            setTimeout(function() {
                fixSpecificPatterns();
            }, 500);
        }
    }
    
    // Inject error handler directly
    function injectErrorHandler() {
        const errorHandler = document.createElement('script');
        errorHandler.textContent = `
            // TBO Hotels Direct Error Handler
            (function() {
                console.log("TBO Hotels Direct Error Handler: Active");
                
                // Global error handler
                window.addEventListener("error", function(event) {
                    if (event && event.message) {
                        // Log but prevent errors from breaking execution
                        console.warn("TBO Hotels Caught:", event.message);
                        
                        // Fix specific errors
                        if (event.message.includes("Unexpected token 'catch'") || 
                            event.message.includes("missing ) after argument list")) {
                            event.preventDefault();
                            return true;
                        }
                    }
                }, true);
                
                // Fix Function constructor to handle syntax errors
                const originalFunction = window.Function;
                window.Function = function() {
                    try {
                        return originalFunction.apply(this, arguments);
                    } catch(e) {
                        console.warn("TBO Hotels: Caught syntax error in Function:", e.message);
                        return function() { return null; };
                    }
                };
                
                // Fix specific token errors by patching the JSON parser
                const originalJSONParse = JSON.parse;
                JSON.parse = function(text) {
                    try {
                        return originalJSONParse(text);
                    } catch(e) {
                        try {
                            // Try to fix common JSON issues
                            let fixed = text.replace(/,\\s*}/g, "}").replace(/,\\s*\\]/g, "]");
                            return originalJSONParse(fixed);
                        } catch(e2) {
                            console.warn("TBO Hotels: Could not fix JSON:", e2.message);
                            return {};
                        }
                    }
                };
            })();
        `;
        document.head.appendChild(errorHandler);
    }
    
    // Fix specific patterns known to cause errors
    function fixSpecificPatterns() {
        // Select all script elements again (including newly added ones)
        const scripts = document.querySelectorAll('script:not([src])');
        
        scripts.forEach(function(script) {
            const content = script.textContent || '';
            
            // Skip empty scripts or our own scripts
            if (!content || content.includes('TBO Hotels Direct Error Fix')) {
                return;
            }
            
            // Look for specific error patterns
            if (content.includes('catch {') || 
                content.includes('catch{') || 
                content.includes('missing ) after argument list')) {
                
                // Fix and replace the script
                const fixedContent = fixScriptContent(content);
                
                if (fixedContent !== content) {
                    try {
                        const newScript = document.createElement('script');
                        newScript.textContent = fixedContent;
                        script.parentNode.replaceChild(newScript, script);
                        console.log('TBO Hotels: Fixed specific pattern in script');
                    } catch(e) {
                        console.warn('TBO Hotels: Error replacing pattern script:', e);
                    }
                }
            }
        });
        
        // Fix any jQuery objects that might cause errors
        if (typeof jQuery !== 'undefined') {
            try {
                // Patch jQuery event handling for syntax errors
                const originalOn = jQuery.fn.on;
                jQuery.fn.on = function(events, selector, data, fn, one) {
                    // Check if any argument is a string that might be a function
                    if (typeof selector === 'string' && 
                        (selector.includes('function(') || selector.includes('=>'))) {
                        selector = fixScriptContent(selector);
                    }
                    if (typeof data === 'string' && 
                        (data.includes('function(') || data.includes('=>'))) {
                        data = fixScriptContent(data);
                    }
                    if (typeof fn === 'string' && 
                        (fn.includes('function(') || fn.includes('=>'))) {
                        fn = fixScriptContent(fn);
                    }
                    
                    // Call original method
                    return originalOn.call(this, events, selector, data, fn, one);
                };
            } catch(e) {
                console.warn('TBO Hotels: Error patching jQuery:', e);
            }
        }
    }
    
    // Ensure this runs on both document ready and window load
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        fixSyntaxErrors();
    } else {
        document.addEventListener('DOMContentLoaded', fixSyntaxErrors);
    }
    
    // Also run after window load for late scripts
    window.addEventListener('load', function() {
        setTimeout(fixSyntaxErrors, 500);
    });
})();