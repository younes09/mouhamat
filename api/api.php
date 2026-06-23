<?php
date_default_timezone_set('Africa/Algiers');
header('Content-Type: application/json');
session_start();

// Change working directory to project root so that all relative paths (like 'uploads/')
// resolve correctly from the point of view of the parent folder.
chdir(dirname(__DIR__));

require_once dirname(__DIR__) . '/config/db.php';

// Helper function to generate UUID v4
function generateUUID()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

// Response helper
function sendResponse($data, $status = 200)
{
    http_response_code($status);
    echo json_code_response($data);
    exit;
}

function json_code_response($data)
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? '';

// Global Check: List Open setting
function isListOpen()
{
    $setting = dbFetch("SELECT setting_value FROM system_settings WHERE setting_key = 'is_list_open'");
    return $setting && $setting['setting_value'] === '1';
}

function getSystemSetting($key, $default = '')
{
    $setting = dbFetch("SELECT setting_value FROM system_settings WHERE setting_key = ?", [$key]);
    return $setting ? $setting['setting_value'] : $default;
}

switch ($action) {
    case 'auth_check':
    case 'login':
    case 'register':
    case 'update_profile':
    case 'logout':
        require_once __DIR__ . '/auth.php';
        break;

    case 'get_requests':
    case 'add_request':
    case 'edit_request':
    case 'delete_request':
    case 'archive_requests':
    case 'clear_requests':
        require_once __DIR__ . '/requests.php';
        break;

    case 'get_settings':
    case 'update_list_status':
    case 'update_constraints':
    case 'add_council':
    case 'delete_council':
    case 'add_court':
    case 'delete_court':
    case 'add_section':
    case 'delete_section':
    case 'add_chamber':
    case 'delete_chamber':
        require_once __DIR__ . '/settings.php';
        break;

    case 'get_announcements':
    case 'add_announcement':
    case 'toggle_announcement':
    case 'delete_announcement':
        require_once __DIR__ . '/announcements.php';
        break;

    case 'get_users':
    case 'update_user_status':
    case 'add_user':
    case 'edit_user':
    case 'delete_user':
        require_once __DIR__ . '/users.php';
        break;

    default:
        sendResponse(['error' => 'أمر غير صالح'], 404);
}
