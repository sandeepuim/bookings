/**
 * TBO Hotels Browser Diagnostic Tool
 * 
 * This script helps identify common JavaScript issues on the browser side.
 * Copy this entire code into the browser console when troubleshooting.
 */

(function() {
    console.log('TBO Hotels Browser Diagnostic Tool - Starting diagnostics...');
    
    // Create UI for results
    function createDiagnosticUI() {
        // Create container
        const container = document.createElement('div');
        container.id = 'tbo-diagnostic-panel';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            width: 400px;
            max-height: 80vh;
            overflow-y: auto;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 999999;
            font-family: Arial, sans-serif;
            font-size: 14px;
            padding: 15px;
        `;
        
        // Add header
        const header = document.createElement('div');
        header.innerHTML = `
            <h2 style="margin-top: 0; color: #0066cc;">TBO Hotels Diagnostics</h2>
            <p style="margin-bottom: 15px;">Checking for common issues...</p>
        `;
        container.appendChild(header);
        
        // Add results container
        const results = document.createElement('div');
        results.id = 'tbo-diagnostic-results';
        container.appendChild(results);
        
        // Add footer with close button
        const footer = document.createElement('div');
        footer.style.marginTop = '15px';
        footer.style.textAlign = 'right';
        
        const closeButton = document.createElement('button');
        closeButton.textContent = 'Close';
        closeButton.style.cssText = `
            background: #0066cc;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        `;
        closeButton.onclick = function() {
            document.body.removeChild(container);
        };
        
        footer.appendChild(closeButton);
        container.appendChild(footer);
        
        // Add to page
        document.body.appendChild(container);
        
        return results;
    }
    
    // Add a test result
    function addResult(container, title, status, message, details = null) {
        const result = document.createElement('div');
        result.style.cssText = `
            margin-bottom: 15px;
            padding: 10px;
            border-left: 4px solid ${status === 'pass' ? '#2ecc71' : status === 'warning' ? '#f39c12' : '#e74c3c'};
            background: #f9f9f9;
        `;
        
        let content = `
            <h3 style="margin-top: 0; margin-bottom: 5px; color: ${status === 'pass' ? '#2ecc71' : status === 'warning' ? '#f39c12' : '#e74c3c'}">
                ${status === 'pass' ? '✅' : status === 'warning' ? '⚠️' : '❌'} ${title}
            </h3>
            <p style="margin: 0;">${message}</p>
        `;
        
        if (details) {
            content += `
                <div style="margin-top: 10px;">
                    <pre style="margin: 0; padding: 10px; background: #f1f1f1; overflow: auto; max-height: 100px; font-family: monospace; font-size: 12px;">${details}</pre>
                </div>
            `;
        }
        
        result.innerHTML = content;
        container.appendChild(result);
    }
    
    // Run diagnostics
    function runDiagnostics() {
        const resultsContainer = createDiagnosticUI();
        
        // 1. Check for jQuery
        if (typeof jQuery !== 'undefined') {
            addResult(
                resultsContainer,
                'jQuery Detection',
                'pass',
                `jQuery v${jQuery.fn.jquery} is loaded`
            );
            
            // Check for jQuery UI
            if (typeof jQuery.ui !== 'undefined') {
                addResult(
                    resultsContainer,
                    'jQuery UI Detection',
                    'pass',
                    `jQuery UI v${jQuery.ui.version} is loaded`
                );
            }
        } else {
            addResult(
                resultsContainer,
                'jQuery Detection',
                'fail',
                'jQuery is not loaded. This may cause problems with the TBO Hotels functionality.'
            );
        }
        
        // 2. Check for error fix scripts
        const scripts = Array.from(document.querySelectorAll('script[src]')).map(script => script.src);
        const errorFixScripts = [
            '/wp-content/themes/tbo-hotels/assets/js/console-error-fix.js',
            '/wp-content/themes/tbo-hotels/assets/js/syntax-error-fix.js',
            '/wp-content/themes/tbo-hotels/assets/js/tbo-optimization.js'
        ];
        
        let missingScripts = [];
        
        errorFixScripts.forEach(scriptPath => {
            const isLoaded = scripts.some(src => src.includes(scriptPath));
            if (!isLoaded) {
                missingScripts.push(scriptPath);
            }
        });
        
        if (missingScripts.length === 0) {
            addResult(
                resultsContainer,
                'Error Fix Scripts',
                'pass',
                'All error fix scripts are loaded correctly'
            );
        } else {
            addResult(
                resultsContainer,
                'Error Fix Scripts',
                'warning',
                `${missingScripts.length} error fix scripts are missing`,
                missingScripts.join('\n')
            );
        }
        
        // 3. Check for JavaScript errors
        const originalConsoleError = console.error;
        const originalConsoleWarn = console.warn;
        
        let errors = [];
        let warnings = [];
        
        console.error = function() {
            errors.push(Array.from(arguments).join(' '));
            originalConsoleError.apply(console, arguments);
        };
        
        console.warn = function() {
            warnings.push(Array.from(arguments).join(' '));
            originalConsoleWarn.apply(console, arguments);
        };
        
        // Force errors to be collected
        setTimeout(function() {
            console.error = originalConsoleError;
            console.warn = originalConsoleWarn;
            
            if (errors.length === 0) {
                addResult(
                    resultsContainer,
                    'JavaScript Errors',
                    'pass',
                    'No JavaScript errors detected'
                );
            } else {
                addResult(
                    resultsContainer,
                    'JavaScript Errors',
                    'fail',
                    `${errors.length} JavaScript errors detected`,
                    errors.slice(0, 10).join('\n') + (errors.length > 10 ? '\n...' : '')
                );
            }
            
            if (warnings.length > 0) {
                addResult(
                    resultsContainer,
                    'JavaScript Warnings',
                    'warning',
                    `${warnings.length} JavaScript warnings detected`,
                    warnings.slice(0, 10).join('\n') + (warnings.length > 10 ? '\n...' : '')
                );
            }
        }, 1000);
        
        // 4. Check network requests
        if (window.performance && window.performance.getEntries) {
            setTimeout(function() {
                const resources = window.performance.getEntries().filter(entry => entry.initiatorType === 'xmlhttprequest');
                const apiRequests = resources.filter(req => req.name.includes('api.tbotechnology.in'));
                
                let slowRequests = apiRequests.filter(req => req.duration > 2000);
                
                if (apiRequests.length > 0) {
                    if (slowRequests.length > 0) {
                        addResult(
                            resultsContainer,
                            'API Performance',
                            'warning',
                            `Detected ${slowRequests.length} slow API requests (>2s)`,
                            slowRequests.map(req => `${req.name}: ${Math.round(req.duration)}ms`).join('\n')
                        );
                    } else {
                        addResult(
                            resultsContainer,
                            'API Performance',
                            'pass',
                            `All ${apiRequests.length} API requests completed in good time`
                        );
                    }
                }
                
                // Check for failed requests
                const failedRequests = resources.filter(req => req.encodedBodySize === 0 || req.transferSize === 0);
                
                if (failedRequests.length > 0) {
                    addResult(
                        resultsContainer,
                        'Failed Requests',
                        'warning',
                        `Detected ${failedRequests.length} potentially failed requests`,
                        failedRequests.slice(0, 10).map(req => req.name).join('\n') + 
                        (failedRequests.length > 10 ? '\n...' : '')
                    );
                }
            }, 2000);
        }
        
        // 5. DOM Issues
        setTimeout(function() {
            // Check for hotel results container
            const resultsContainer = document.getElementById('hotel-results-container');
            
            if (resultsContainer) {
                const hotelItems = resultsContainer.querySelectorAll('.hotel-item');
                
                if (hotelItems.length > 0) {
                    addResult(
                        resultsContainer,
                        'Hotel Results',
                        'pass',
                        `Found ${hotelItems.length} hotel results displayed`
                    );
                } else {
                    addResult(
                        resultsContainer,
                        'Hotel Results',
                        'warning',
                        'Hotel results container exists but no hotels are displayed'
                    );
                }
            } else if (document.location.href.includes('hotel-results') || 
                      document.location.href.includes('search-hotels')) {
                addResult(
                    resultsContainer,
                    'Hotel Results',
                    'fail',
                    'Hotel results container not found on results page'
                );
            }
            
            // Check for broken images
            const brokenImages = Array.from(document.querySelectorAll('img')).filter(img => 
                img.complete && (img.naturalWidth === 0 || img.naturalHeight === 0)
            );
            
            if (brokenImages.length > 0) {
                addResult(
                    resultsContainer,
                    'Broken Images',
                    'warning',
                    `Found ${brokenImages.length} broken images`,
                    brokenImages.slice(0, 10).map(img => img.src).join('\n') + 
                    (brokenImages.length > 10 ? '\n...' : '')
                );
            }
        }, 3000);
        
        // 6. Check for any custom TBO Hotels global functions
        setTimeout(function() {
            const tboFunctions = [];
            
            for (const key in window) {
                if (typeof window[key] === 'function' && 
                   (key.startsWith('tbo') || key.includes('TBO') || key.includes('Hotel'))) {
                    tboFunctions.push(key);
                }
            }
            
            if (tboFunctions.length > 0) {
                addResult(
                    resultsContainer,
                    'TBO Functions',
                    'pass',
                    `Found ${tboFunctions.length} TBO related functions`,
                    tboFunctions.join('\n')
                );
            }
            
            // Final summary
            addResult(
                resultsContainer,
                'Diagnostic Complete',
                'pass',
                'Browser-side diagnostics completed. Check the results above for issues.'
            );
        }, 4000);
    }
    
    // Run diagnostics
    runDiagnostics();
})();