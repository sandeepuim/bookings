<?php
/**
 * Debug Logger Class for TBO Hotel Booking
 * 
 * This class handles debug logging for the TBO Hotel Booking plugin.
 */
class TBO_Hotel_Booking_Logger {
    
    /**
     * Log directory
     */
    private $log_dir;
    
    /**
     * Log file path
     */
    private $log_file;
    
    /**
     * Debug mode enabled
     */
    private $debug_enabled;
    
    /**
     * Initialize the logger
     */
    public function __construct() {
        // Set log directory
        $this->log_dir = TBO_HOTEL_BOOKING_PLUGIN_DIR . 'logs/';
        
        // Create logs directory if it doesn't exist
        if (!file_exists($this->log_dir)) {
            mkdir($this->log_dir, 0755, true);
        }
        
        // Set log file path
        $this->log_file = $this->log_dir . 'tbo-api-debug.log';
        
        // Check if debug is enabled
        $settings = get_option('tbo_hotel_booking_settings', array());
        $this->debug_enabled = isset($settings['debug_mode']) ? (bool)$settings['debug_mode'] : false;
    }
    
    /**
     * Log a message
     *
     * @param string $message The message to log
     * @param string $level   The log level (info, error, debug)
     * @return bool Success or failure
     */
    public function log($message, $level = 'info') {
        // Only log if debugging is enabled
        if (!$this->debug_enabled) {
            return false;
        }
        
        // Format the log entry
        $timestamp = current_time('mysql');
        $entry = sprintf("[%s] [%s] %s\n", $timestamp, strtoupper($level), $message);
        
        // Write to log file
        return error_log($entry, 3, $this->log_file);
    }
    
    /**
     * Log API request
     *
     * @param string $endpoint API endpoint
     * @param array  $data     Request data
     * @param string $method   HTTP method
     * @return void
     */
    public function log_api_request($endpoint, $data, $method) {
        if (!$this->debug_enabled) {
            return;
        }
        
        $message = sprintf(
            "API Request to %s\nMethod: %s\nData: %s",
            $endpoint,
            $method,
            json_encode($data, JSON_PRETTY_PRINT)
        );
        
        $this->log($message, 'debug');
    }
    
    /**
     * Log API response
     *
     * @param string $endpoint API endpoint
     * @param mixed  $response Response data
     * @param int    $status   HTTP status code
     * @return void
     */
    public function log_api_response($endpoint, $response, $status) {
        if (!$this->debug_enabled) {
            return;
        }
        
        $message = sprintf(
            "API Response from %s\nStatus: %d\nResponse: %s",
            $endpoint,
            $status,
            is_string($response) ? $response : json_encode($response, JSON_PRETTY_PRINT)
        );
        
        $this->log($message, 'debug');
    }
    
    /**
     * Log an error
     *
     * @param string $message Error message
     * @param mixed  $context Additional context (optional)
     * @return void
     */
    public function log_error($message, $context = null) {
        if (!$this->debug_enabled) {
            return;
        }
        
        if ($context !== null) {
            $message .= "\nContext: " . (is_string($context) ? $context : json_encode($context, JSON_PRETTY_PRINT));
        }
        
        $this->log($message, 'error');
    }
    
    /**
     * Get the full path to the log file
     *
     * @return string Log file path
     */
    public function get_log_file_path() {
        return $this->log_file;
    }
    
    /**
     * Clear the log file
     *
     * @return bool Success or failure
     */
    public function clear_log() {
        if (file_exists($this->log_file)) {
            return file_put_contents($this->log_file, '');
        }
        return false;
    }
    
    /**
     * Get the log file contents
     *
     * @param int $lines Number of lines to return (0 for all)
     * @return string Log contents
     */
    public function get_log_contents($lines = 0) {
        if (!file_exists($this->log_file)) {
            return '';
        }
        
        if ($lines > 0) {
            // Get only the last N lines
            $file = new SplFileObject($this->log_file, 'r');
            $file->seek(PHP_INT_MAX);
            $total_lines = $file->key();
            
            $log_lines = array();
            $start = max(0, $total_lines - $lines);
            
            $file->seek($start);
            while (!$file->eof()) {
                $log_lines[] = $file->fgets();
            }
            
            return implode('', $log_lines);
        } else {
            // Get all log contents
            return file_get_contents($this->log_file);
        }
    }
}
