<?php
/**
 * Application Configuration
 * Cyber Incident Evidence Management System (CIEMS)
 * USIU-Africa - IST4900 Systems Design and Planning
 */

// Define application constants
define('APP_NAME', 'CIEMS - Cyber Incident Evidence Management System');
define('APP_VERSION', '1.0.0');
define('APP_INSTITUTION', 'United States International University – Africa');
define('APP_COURSE', 'IST4900 Systems Design and Planning');

// Path constants
define('BASE_PATH', dirname(__DIR__));
define('STORAGE_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'storage');
define('VAULT_PATH', STORAGE_PATH . DIRECTORY_SEPARATOR . 'vault');
define('REPORTS_PATH', STORAGE_PATH . DIRECTORY_SEPARATOR . 'reports');

// Base URL calculation (works with /ciems on XAMPP)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/ciems/index.php');
$scriptDir = rtrim(str_replace('\\', '/', $scriptDir), '/');
define('BASE_URL', $protocol . $host . $scriptDir);

// Timezone
date_default_timezone_set('Africa/Nairobi');

// Forensic configuration parameters
define('DEFAULT_HASH_ALGO', 'sha256');
define('MAX_FILE_SIZE_BYTES', 500 * 1024 * 1024); // 500MB default upload limit
define('ALLOWED_EXTENSIONS', [
    'pcap', 'pcapng', 'raw', 'dd', 'img', 'bin', 'vmdk', 'iso', 'mem', 
    'dmp', 'log', 'txt', 'csv', 'json', 'evtx', 'zip', 'tar', 'gz', '7z', 'pdf', 'png', 'jpg'
]);

// Error handling in development
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
