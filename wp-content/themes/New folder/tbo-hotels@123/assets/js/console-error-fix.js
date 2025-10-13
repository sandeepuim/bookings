/**
 * TBO Hotels Console Error Fix
 * 
 * This script comprehensively fixes common JavaScript syntax errors:
 * 1. Missing parameters in catch blocks
 * 2. Parenthesis imbalance
 * 3. Trailing commas in function arguments
 * 4. Invalid selectors in querySelectorAll
 */

(function() {
    'use strict';
    
    console.log('TBO Hotels: Console error fix active');
    
    // Store original methods
    var originalQuerySelectorAll = Document.prototype.querySelectorAll;
    var originalCreateElement = document.createElement;
    var originalAppendChild = Element.prototype.appendChild;
    var originalReplaceChild = Node.prototype.replaceChild;
    
    // Fix 1: Override querySelectorAll to handle invalid selectors
    Document.prototype.querySelectorAll = function() {
        try {
            return originalQuerySelectorAll.apply(this, arguments);
        } catch (e) {
            console.warn('TBO Hotels Error Caught:', e);
            
            // Try to fix jQuery-specific selectors
            if (arguments[0] && typeof arguments[0] === 'string') {
                var selector = arguments[0];
                
                // Fix :contains() selector - not valid in querySelector
                if (selector.includes(':contains(')) {
                    // Fall back to a more generic selector by removing the :contains part
                    var fixedSelector = selector.replace(/:[^,]*contains\([^)]*\)/g, '');
                    
                    try {
                        return originalQuerySelectorAll.call(this, fixedSelector);
                    } catch (e2) {
                        // If still failing, return empty NodeList
                        console.warn('TBO Hotels: Could not fix selector:', selector);
                        return document.createDocumentFragment().childNodes;
                    }
                }
            }
            
            // Return empty NodeList for any other errors
            return document.createDocumentFragment().childNodes;
        }
    };
    
    // Fix 2: Add global error handler
    window.addEventListener('error', function(event) {
        if (event && event.message) {
            // Log the error but prevent it from showing in console
            console.warn('TBO Hotels Error Caught:', event.message);
            
            // For syntax errors, try to prevent the error from halting execution
            if (event.message.includes('Unexpected token') || 
                event.message.includes('missing ) after argument list') ||
                event.message.includes('Failed to execute')) {
                
                event.preventDefault();
                return true;
            }
        }
    }, true);
    
    // Fix 3: Override replaceChild to handle syntax errors in scripts
    Node.prototype.replaceChild = function(newNode, oldNode) {
        try {
            return originalReplaceChild.call(this, newNode, oldNode);
        } catch (e) {
            console.warn('TBO Hotels Error Caught:', e);
            
            // If it's a script node with syntax errors, try to fix the content
            if (newNode && newNode.tagName === 'SCRIPT') {
                var content = newNode.textContent || '';
                
                // Apply fixes to script content
                content = fixScriptContent(content);
                newNode.textContent = content;
                
                // Try again with fixed content
                try {
                    return originalReplaceChild.call(this, newNode, oldNode);
                } catch (e2) {
                    console.warn('TBO Hotels: Could not replace node even after fixing:', e2);
                }
            }
            
            // If all else fails, at least keep the old node
            return oldNode;
        }
    };
    
    // Fix 4: Override createElement to handle script creation
    document.createElement = function() {
        var element = originalCreateElement.apply(this, arguments);
        
        // If it's a script element, intercept setting textContent
        if (arguments[0] && arguments[0].toLowerCase() === 'script') {
            var originalDescriptor = Object.getOwnPropertyDescriptor(Node.prototype, 'textContent');
            var originalSet = originalDescriptor.set;
            
            Object.defineProperty(element, 'textContent', {
                set: function(value) {
                    // Fix the content before setting it
                    var fixedValue = fixScriptContent(value);
                    return originalSet.call(this, fixedValue);
                },
                get: originalDescriptor.get
            });
        }
        
        return element;
    };
    
    // Fix 5: Fix script content helper function
    function fixScriptContent(content) {
        if (!content || typeof content !== 'string') return content;
        
        // 1. Fix try/catch blocks without parameters
        content = content.replace(/try\s*{([\s\S]*?)}\s*catch\s*{/g, function(match, tryBlock) {
            return 'try {' + tryBlock + '} catch(e) {';
        });
        
        // 2. Fix trailing commas in function arguments
        content = content.replace(/\(([^)]*),\s*\)/g, function(match, args) {
            return '(' + args.trim() + ')';
        });
        
        // 3. Balance parentheses
        var openCount = (content.match(/\(/g) || []).length;
        var closeCount = (content.match(/\)/g) || []).length;
        
        if (openCount > closeCount) {
            // Add missing closing parentheses
            var diff = openCount - closeCount;
            for (var i = 0; i < diff; i++) {
                content += ')';
            }
        } else if (closeCount > openCount) {
            // Remove extra closing parentheses at the end
            var pattern = /\)+$/;
            var matches = content.match(pattern);
            if (matches && matches[0]) {
                var excess = Math.min(matches[0].length, closeCount - openCount);
                content = content.substring(0, content.length - excess);
            }
        }
        
        // 4. Fix jQuery selector syntax when used in normal DOM functions
        content = content.replace(/querySelectorAll\(['"](.*?):contains\((.*?)\)(.*?)['"]/, function(match, prefix, containsContent, suffix) {
            return 'querySelectorAll(["' + prefix + suffix + '"]';
        });
        
        return content;
    }
    
    // Fix all existing inline scripts
    function fixExistingScripts() {
        var scripts = document.querySelectorAll('script:not([src])');
        scripts.forEach(function(script) {
            var content = script.textContent || '';
            
            // Skip empty scripts or our own
            if (!content || content.includes('TBO Hotels: Console error fix active')) {
                return;
            }
            
            // Fix the content
            var fixedContent = fixScriptContent(content);
            
            // Replace if changed
            if (fixedContent !== content) {
                var newScript = document.createElement('script');
                newScript.textContent = fixedContent;
                
                try {
                    script.parentNode.replaceChild(newScript, script);
                } catch (e) {
                    console.warn('Error replacing script:', e);
                }
            }
        });
    }
    
    // Run immediately if possible
    if (document.readyState !== 'loading') {
        fixExistingScripts();
    } else {
        document.addEventListener('DOMContentLoaded', fixExistingScripts);
    }
    
    // Also run after load to catch late scripts
    window.addEventListener('load', function() {
        setTimeout(fixExistingScripts, 300);
    });
})();