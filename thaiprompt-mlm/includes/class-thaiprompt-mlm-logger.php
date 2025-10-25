<?php
/**
 * Debug Logger Class
 *
 * Captures and logs errors, warnings, and debug information
 */

class Thaiprompt_MLM_Logger {

    /**
     * Log file path
     */
    private static $log_file;

    /**
     * Maximum log file size (5MB)
     */
    private static $max_log_size = 5242880;

    /**
     * Initialize logger
     */
    public static function init() {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/thaiprompt-mlm-logs';

        // Create log directory if it doesn't exist
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);

            // Add .htaccess to protect log files
            $htaccess_content = "Order Deny,Allow\nDeny from all";
            file_put_contents($log_dir . '/.htaccess', $htaccess_content);

            // Add index.php to prevent directory listing
            file_put_contents($log_dir . '/index.php', '<?php // Silence is golden');
        }

        self::$log_file = $log_dir . '/error-log-' . date('Y-m') . '.txt';

        // Register error handlers
        self::register_error_handlers();
    }

    /**
     * Register error handlers
     */
    private static function register_error_handlers() {
        // Register shutdown function to catch fatal errors
        register_shutdown_function(array(__CLASS__, 'handle_shutdown'));

        // Set custom error handler
        set_error_handler(array(__CLASS__, 'handle_error'));

        // Set custom exception handler
        set_exception_handler(array(__CLASS__, 'handle_exception'));
    }

    /**
     * Log a message
     */
    public static function log($level, $message, $context = array()) {
        // Check file size and rotate if needed
        self::rotate_log_if_needed();

        $timestamp = current_time('Y-m-d H:i:s');
        $user_id = get_current_user_id();
        $user_info = $user_id ? " [User: $user_id]" : " [Guest]";

        // Format context data
        $context_str = '';
        if (!empty($context)) {
            $context_str = "\nContext: " . print_r($context, true);
        }

        // Get backtrace
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = isset($backtrace[1]) ? $backtrace[1] : $backtrace[0];
        $file = isset($caller['file']) ? basename($caller['file']) : 'unknown';
        $line = isset($caller['line']) ? $caller['line'] : '0';

        $log_entry = sprintf(
            "[%s] %s%s (%s:%s)\n%s%s\n%s\n",
            $timestamp,
            strtoupper($level),
            $user_info,
            $file,
            $line,
            $message,
            $context_str,
            str_repeat('-', 100)
        );

        // Write to file
        error_log($log_entry, 3, self::$log_file);

        // If it's a critical error, also log to WordPress debug log
        if (in_array($level, array('error', 'critical', 'emergency')) && WP_DEBUG_LOG) {
            error_log('[Thaiprompt MLM] ' . $message);
        }
    }

    /**
     * Log debug message
     */
    public static function debug($message, $context = array()) {
        if (defined('THAIPROMPT_MLM_DEBUG') && THAIPROMPT_MLM_DEBUG) {
            self::log('debug', $message, $context);
        }
    }

    /**
     * Log info message
     */
    public static function info($message, $context = array()) {
        self::log('info', $message, $context);
    }

    /**
     * Log warning
     */
    public static function warning($message, $context = array()) {
        self::log('warning', $message, $context);
    }

    /**
     * Log error
     */
    public static function error($message, $context = array()) {
        self::log('error', $message, $context);
    }

    /**
     * Log critical error
     */
    public static function critical($message, $context = array()) {
        self::log('critical', $message, $context);
    }

    /**
     * Handle PHP errors
     */
    public static function handle_error($errno, $errstr, $errfile, $errline) {
        // Don't log errors that are suppressed with @
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $error_types = array(
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE ERROR',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE ERROR',
            E_CORE_WARNING => 'CORE WARNING',
            E_COMPILE_ERROR => 'COMPILE ERROR',
            E_COMPILE_WARNING => 'COMPILE WARNING',
            E_USER_ERROR => 'USER ERROR',
            E_USER_WARNING => 'USER WARNING',
            E_USER_NOTICE => 'USER NOTICE',
            E_STRICT => 'STRICT NOTICE',
            E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER DEPRECATED'
        );

        $error_type = isset($error_types[$errno]) ? $error_types[$errno] : 'UNKNOWN ERROR';

        $message = sprintf(
            "PHP %s: %s in %s on line %d",
            $error_type,
            $errstr,
            $errfile,
            $errline
        );

        self::log('error', $message);

        // Don't execute PHP internal error handler
        return true;
    }

    /**
     * Handle exceptions
     */
    public static function handle_exception($exception) {
        $message = sprintf(
            "Uncaught Exception: %s in %s on line %d\nStack trace:\n%s",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        self::log('critical', $message);
    }

    /**
     * Handle fatal errors on shutdown
     */
    public static function handle_shutdown() {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
            $message = sprintf(
                "Fatal Error: %s in %s on line %d",
                $error['message'],
                $error['file'],
                $error['line']
            );

            self::log('critical', $message);
        }
    }

    /**
     * Rotate log file if it's too large
     */
    private static function rotate_log_if_needed() {
        if (file_exists(self::$log_file) && filesize(self::$log_file) > self::$max_log_size) {
            $backup_file = self::$log_file . '.old';

            // Remove old backup if exists
            if (file_exists($backup_file)) {
                unlink($backup_file);
            }

            // Rename current log to backup
            rename(self::$log_file, $backup_file);
        }
    }

    /**
     * Get log file path
     */
    public static function get_log_file() {
        return self::$log_file;
    }

    /**
     * Get all log files
     */
    public static function get_log_files() {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/thaiprompt-mlm-logs';

        if (!is_dir($log_dir)) {
            return array();
        }

        $files = glob($log_dir . '/error-log-*.txt*');

        // Sort by modification time (newest first)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        return $files;
    }

    /**
     * Read log file
     */
    public static function read_log($file = null, $lines = 500) {
        $log_file = $file ? $file : self::$log_file;

        if (!file_exists($log_file)) {
            return '';
        }

        // Read last N lines
        $file_content = file($log_file);
        $total_lines = count($file_content);
        $start_line = max(0, $total_lines - $lines);

        return implode('', array_slice($file_content, $start_line));
    }

    /**
     * Clear log file
     */
    public static function clear_log($file = null) {
        $log_file = $file ? $file : self::$log_file;

        if (file_exists($log_file)) {
            return unlink($log_file);
        }

        return true;
    }

    /**
     * Clear all log files
     */
    public static function clear_all_logs() {
        $files = self::get_log_files();

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Get log statistics
     */
    public static function get_log_stats() {
        $files = self::get_log_files();
        $total_size = 0;
        $total_errors = 0;

        foreach ($files as $file) {
            if (file_exists($file)) {
                $total_size += filesize($file);

                // Count errors in file
                $content = file_get_contents($file);
                $total_errors += substr_count($content, '[ERROR]');
                $total_errors += substr_count($content, '[CRITICAL]');
                $total_errors += substr_count($content, '[EMERGENCY]');
            }
        }

        return array(
            'total_files' => count($files),
            'total_size' => $total_size,
            'total_size_formatted' => size_format($total_size),
            'total_errors' => $total_errors
        );
    }
}
