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

/**
 * Renders a high-contrast, visually prominent security clearance / classification badge.
 */
function getClassificationBadge(string $level = 'confidential', bool $large = false): string {
    $map = [
        'top_secret' => [
            'label'  => 'TOP SECRET',
            'bg'     => '#881337',
            'border' => '#f43f5e',
            'color'  => '#ffffff',
            'icon'   => 'fa-shield-halved'
        ],
        'secret' => [
            'label'  => 'SECRET',
            'bg'     => '#c2410c',
            'border' => '#ea580c',
            'color'  => '#ffffff',
            'icon'   => 'fa-lock'
        ],
        'confidential' => [
            'label'  => 'CONFIDENTIAL',
            'bg'     => '#d97706',
            'border' => '#f59e0b',
            'color'  => '#ffffff',
            'icon'   => 'fa-key'
        ],
        'restricted' => [
            'label'  => 'RESTRICTED',
            'bg'     => '#1d4ed8',
            'border' => '#3b82f6',
            'color'  => '#ffffff',
            'icon'   => 'fa-user-shield'
        ],
        'unclassified' => [
            'label'  => 'UNCLASSIFIED',
            'bg'     => '#475569',
            'border' => '#64748b',
            'color'  => '#ffffff',
            'icon'   => 'fa-folder-open'
        ]
    ];

    $clean = strtolower(trim($level ?: 'confidential'));
    $info = $map[$clean] ?? $map['confidential'];
    $pad = $large ? 'px-3 py-2' : 'px-2 py-1';
    $fontSize = $large ? 'font-size: 0.82rem;' : 'font-size: 0.72rem;';

    return sprintf(
        '<span class="badge badge-classification-%s %s" style="background-color: %s !important; color: %s !important; border: 1px solid %s !important; font-weight: 700 !important; letter-spacing: 0.05em; %s display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.18); text-transform: uppercase;"><i class="fa-solid %s"></i> %s</span>',
        htmlspecialchars($clean),
        $pad,
        $info['bg'],
        $info['color'],
        $info['border'],
        $fontSize,
        $info['icon'],
        htmlspecialchars($info['label'])
    );
}
