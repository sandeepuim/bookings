<?php
/**
 * TBO Hotels Frontend Debug Tool
 * 
 * This script adds a debug console to the frontend of the site
 * to help troubleshoot JavaScript and AJAX issues.
 * 
 * Usage: Include this script in your theme's header.php file:
 * <?php include_once(get_template_directory() . '/debug-console.php'); ?>
 */

// Only show debug console in development environments
$is_local = (
    strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
    strpos($_SERVER['HTTP_HOST'], '.test') !== false ||
    strpos($_SERVER['HTTP_HOST'], '.local') !== false
);

if ($is_local) {
    add_action('wp_footer', 'tbo_hotels_debug_console');
}

function tbo_hotels_debug_console() {
    ?>
    <style>
        #tbo-debug-console {
            position: fixed;
            bottom: 0;
            right: 0;
            width: 400px;
            height: 300px;
            background: rgba(0,0,0,0.8);
            color: #00ff00;
            font-family: monospace;
            font-size: 12px;
            padding: 10px;
            overflow: auto;
            z-index: 9999;
            border-top-left-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        #tbo-debug-console .log {
            margin: 2px 0;
            border-bottom: 1px solid #333;
            padding-bottom: 2px;
        }
        #tbo-debug-console .error {
            color: #ff5555;
        }
        #tbo-debug-console .warn {
            color: #ffff55;
        }
        #tbo-debug-console .info {
            color: #55aaff;
        }
        #tbo-debug-console .ajax {
            color: #ff55ff;
        }
        #tbo-debug-console-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #555;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        #tbo-debug-console-title {
            font-weight: bold;
        }
        #tbo-debug-console-controls button {
            background: #333;
            color: #fff;
            border: none;
            padding: 2px 5px;
            margin-left: 5px;
            cursor: pointer;
        }
        #tbo-debug-toggle {
            position: fixed;
            bottom: 10px;
            right: 10px;
            background: #000;
            color: #0f0;
            border: 1px solid #0f0;
            padding: 5px 10px;
            cursor: pointer;
            z-index: 9998;
            display: none;
        }
        .tbo-debug-collapsed {
            height: 30px !important;
            overflow: hidden;
        }
    </style>
    
    <button id="tbo-debug-toggle">Show Debug</button>
    
    <div id="tbo-debug-console">
        <div id="tbo-debug-console-header">
            <span id="tbo-debug-console-title">TBO Hotels Debug Console</span>
            <div id="tbo-debug-console-controls">
                <button id="tbo-debug-clear">Clear</button>
                <button id="tbo-debug-minimize">Minimize</button>
                <button id="tbo-debug-close">Close</button>
            </div>
        </div>
        <div id="tbo-debug-console-content"></div>
    </div>
    
    <script>
    (function() {
        const console_elem = document.getElementById('tbo-debug-console');
        const content = document.getElementById('tbo-debug-console-content');
        const toggle = document.getElementById('tbo-debug-toggle');
        const clearBtn = document.getElementById('tbo-debug-clear');
        const minimizeBtn = document.getElementById('tbo-debug-minimize');
        const closeBtn = document.getElementById('tbo-debug-close');
        
        // Store original console methods
        const originalConsole = {
            log: console.log,
            error: console.error,
            warn: console.warn,
            info: console.info
        };
        
        // Functions to handle UI
        function appendToConsole(message, type = 'log') {
            const entry = document.createElement('div');
            entry.className = `log ${type}`;
            
            let formattedMessage = message;
            
            // Format objects and arrays nicely
            if (typeof message === 'object' && message !== null) {
                try {
                    formattedMessage = JSON.stringify(message, null, 2);
                } catch (e) {
                    formattedMessage = message.toString();
                }
            }
            
            // Add timestamp
            const now = new Date();
            const timestamp = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
            
            entry.innerHTML = `<span class="timestamp">[${timestamp}]</span> ${formattedMessage}`;
            content.appendChild(entry);
            
            // Auto-scroll to bottom
            content.scrollTop = content.scrollHeight;
        }
        
        // Override console methods
        console.log = function() {
            originalConsole.log.apply(console, arguments);
            appendToConsole(Array.from(arguments).join(' '), 'log');
        };
        
        console.error = function() {
            originalConsole.error.apply(console, arguments);
            appendToConsole(Array.from(arguments).join(' '), 'error');
        };
        
        console.warn = function() {
            originalConsole.warn.apply(console, arguments);
            appendToConsole(Array.from(arguments).join(' '), 'warn');
        };
        
        console.info = function() {
            originalConsole.info.apply(console, arguments);
            appendToConsole(Array.from(arguments).join(' '), 'info');
        };
        
        // Intercept AJAX requests
        const originalXHROpen = XMLHttpRequest.prototype.open;
        const originalXHRSend = XMLHttpRequest.prototype.send;
        
        XMLHttpRequest.prototype.open = function() {
            this._requestMethod = arguments[0];
            this._requestUrl = arguments[1];
            originalXHROpen.apply(this, arguments);
        };
        
        XMLHttpRequest.prototype.send = function() {
            const xhr = this;
            
            xhr.addEventListener('load', function() {
                try {
                    let responseData = xhr.responseText;
                    
                    // Try to parse JSON
                    try {
                        const jsonResponse = JSON.parse(responseData);
                        responseData = JSON.stringify(jsonResponse, null, 2);
                    } catch (e) {
                        // Not JSON, leave as is
                    }
                    
                    appendToConsole(`AJAX ${xhr._requestMethod} ${xhr._requestUrl} - Status: ${xhr.status}`, 'ajax');
                    appendToConsole(`Response: ${responseData}`, 'ajax');
                } catch (e) {
                    appendToConsole(`Error processing AJAX response: ${e.message}`, 'error');
                }
            });
            
            xhr.addEventListener('error', function() {
                appendToConsole(`AJAX ${xhr._requestMethod} ${xhr._requestUrl} - Failed`, 'error');
            });
            
            originalXHRSend.apply(this, arguments);
        };
        
        // Intercept jQuery AJAX if available
        if (typeof jQuery !== 'undefined') {
            jQuery(document).ajaxSuccess(function(event, xhr, settings) {
                try {
                    let responseData = xhr.responseText;
                    
                    // Try to parse JSON
                    try {
                        const jsonResponse = JSON.parse(responseData);
                        responseData = JSON.stringify(jsonResponse, null, 2);
                    } catch (e) {
                        // Not JSON, leave as is
                    }
                    
                    appendToConsole(`jQuery AJAX ${settings.type} ${settings.url}`, 'ajax');
                    appendToConsole(`Response: ${responseData}`, 'ajax');
                } catch (e) {
                    appendToConsole(`Error processing jQuery AJAX response: ${e.message}`, 'error');
                }
            });
            
            jQuery(document).ajaxError(function(event, xhr, settings, error) {
                appendToConsole(`jQuery AJAX ${settings.type} ${settings.url} - Failed: ${error}`, 'error');
            });
        }
        
        // Event Listeners
        clearBtn.addEventListener('click', function() {
            content.innerHTML = '';
        });
        
        let minimized = false;
        minimizeBtn.addEventListener('click', function() {
            if (minimized) {
                console_elem.classList.remove('tbo-debug-collapsed');
                minimizeBtn.textContent = 'Minimize';
            } else {
                console_elem.classList.add('tbo-debug-collapsed');
                minimizeBtn.textContent = 'Expand';
            }
            minimized = !minimized;
        });
        
        closeBtn.addEventListener('click', function() {
            console_elem.style.display = 'none';
            toggle.style.display = 'block';
            
            // Restore original console functions
            console.log = originalConsole.log;
            console.error = originalConsole.error;
            console.warn = originalConsole.warn;
            console.info = originalConsole.info;
        });
        
        toggle.addEventListener('click', function() {
            console_elem.style.display = 'block';
            toggle.style.display = 'none';
            
            // Re-override console functions
            console.log = function() {
                originalConsole.log.apply(console, arguments);
                appendToConsole(Array.from(arguments).join(' '), 'log');
            };
            
            console.error = function() {
                originalConsole.error.apply(console, arguments);
                appendToConsole(Array.from(arguments).join(' '), 'error');
            };
            
            console.warn = function() {
                originalConsole.warn.apply(console, arguments);
                appendToConsole(Array.from(arguments).join(' '), 'warn');
            };
            
            console.info = function() {
                originalConsole.info.apply(console, arguments);
                appendToConsole(Array.from(arguments).join(' '), 'info');
            };
        });
        
        // Log initialization
        console.info('Debug console initialized');
    })();
    </script>
    <?php
}