<?php
if (!defined('DB_HOST') && !function_exists('sendResponse')) {
    exit('Restricted access');
}

switch ($action) {
    case 'get_settings':
        $councils = array_column(dbFetchAll("SELECT name FROM councils ORDER BY id ASC"), 'name');
        $courts = array_column(dbFetchAll("SELECT name FROM courts ORDER BY id ASC"), 'name');
        $sections = array_column(dbFetchAll("SELECT name FROM sections ORDER BY id ASC"), 'name');
        $chambers = array_column(dbFetchAll("SELECT name FROM chambers ORDER BY id ASC"), 'name');

        // Court Mapping to Councils
        $courtsDb = dbFetchAll("SELECT name, council_name FROM courts ORDER BY id ASC");
        $mapping = [];
        foreach ($councils as $c) {
            $mapping[$c] = [];
        }
        foreach ($courtsDb as $ct) {
            if (isset($mapping[$ct['council_name']])) {
                $mapping[$ct['council_name']][] = $ct['name'];
            }
        }

        sendResponse([
            'isListOpen' => isListOpen(),
            'restrictedDays' => getSystemSetting('restricted_days', '5,6'),
            'startTime' => getSystemSetting('start_time', '06:00'),
            'endTime' => getSystemSetting('end_time', '14:30'),
            'councils' => $councils,
            'courts' => $courts,
            'sections' => $sections,
            'chambers' => $chambers,
            'mapping' => $mapping
        ]);
        break;

    case 'update_list_status':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            sendResponse(['error' => 'غير مصرح'], 403);
        }
        $status = ($input['isOpen'] ?? false) ? '1' : '0';
        dbQuery("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'is_list_open'", [$status]);
        sendResponse(['success' => true]);
        break;

    case 'update_constraints':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            sendResponse(['error' => 'غير مصرح'], 403);
        }
        $restrictedDays = isset($input['restrictedDays']) ? implode(',', array_map('intval', $input['restrictedDays'])) : '5,6';
        $startTime = isset($input['startTime']) ? trim($input['startTime']) : '06:00';
        $endTime = isset($input['endTime']) ? trim($input['endTime']) : '14:30';

        // Validate time formats (HH:MM)
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $startTime) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $endTime)) {
            sendResponse(['error' => 'تنسيق الوقت غير صحيح'], 400);
        }

        dbQuery("INSERT INTO system_settings (setting_key, setting_value) VALUES ('restricted_days', ?) ON DUPLICATE KEY UPDATE setting_value = ?", [$restrictedDays, $restrictedDays]);
        dbQuery("INSERT INTO system_settings (setting_key, setting_value) VALUES ('start_time', ?) ON DUPLICATE KEY UPDATE setting_value = ?", [$startTime, $startTime]);
        dbQuery("INSERT INTO system_settings (setting_key, setting_value) VALUES ('end_time', ?) ON DUPLICATE KEY UPDATE setting_value = ?", [$endTime, $endTime]);
        
        sendResponse(['success' => true]);
        break;

    case 'add_council':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')
            sendResponse(['error' => 'غير مصرح'], 403);
        $name = trim($input['name'] ?? '');
        if (empty($name))
            sendResponse(['error' => 'الاسم مطلوب'], 400);

        try {
            dbQuery("INSERT INTO councils (name) VALUES (?)", [$name]);
            sendResponse(['success' => true]);
        } catch (Exception $e) {
            sendResponse(['error' => 'المجلس موجود بالفعل'], 400);
        }
        break;

    case 'delete_council':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')
            sendResponse(['error' => 'غير مصرح'], 403);
        $name = $input['name'] ?? '';
        dbQuery("DELETE FROM councils WHERE name = ?", [$name]);
        // Also cascade delete courts belonging to it
        dbQuery("DELETE FROM courts WHERE council_name = ?", [$name]);
        sendResponse(['success' => true]);
        break;

    case 'add_court':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')
            sendResponse(['error' => 'غير مصرح'], 403);
        $name = trim($input['name'] ?? '');
        $council = trim($input['council'] ?? '');
        if (empty($name) || empty($council))
            sendResponse(['error' => 'البيانات ناقصة'], 400);

        try {
            dbQuery("INSERT INTO courts (name, council_name) VALUES (?, ?)", [$name, $council]);
            sendResponse(['success' => true]);
        } catch (Exception $e) {
            sendResponse(['error' => 'المحكمة موجودة بالفعل'], 400);
        }
        break;

    case 'delete_court':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')
            sendResponse(['error' => 'غير مصرح'], 403);
        $name = $input['name'] ?? '';
        dbQuery("DELETE FROM courts WHERE name = ?", [$name]);
        sendResponse(['success' => true]);
        break;

    case 'add_section':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')
            sendResponse(['error' => 'غير مصرح'], 403);
        $name = trim($input['name'] ?? '');
        if (empty($name))
            sendResponse(['error' => 'الاسم مطلوب'], 400);

        try {
            dbQuery("INSERT INTO sections (name) VALUES (?)", [$name]);
            sendResponse(['success' => true]);
        } catch (Exception $e) {
            sendResponse(['error' => 'القسم موجود بالفعل'], 400);
        }
        break;

    case 'delete_section':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')
            sendResponse(['error' => 'غير مصرح'], 403);
        $name = $input['name'] ?? '';
        dbQuery("DELETE FROM sections WHERE name = ?", [$name]);
        sendResponse(['success' => true]);
        break;

    case 'add_chamber':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')
            sendResponse(['error' => 'غير مصرح'], 403);
        $name = trim($input['name'] ?? '');
        if (empty($name))
            sendResponse(['error' => 'الاسم مطلوب'], 400);

        try {
            dbQuery("INSERT INTO chambers (name) VALUES (?)", [$name]);
            sendResponse(['success' => true]);
        } catch (Exception $e) {
            sendResponse(['error' => 'الغرفة موجودة بالفعل'], 400);
        }
        break;

    case 'delete_chamber':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')
            sendResponse(['error' => 'غير مصرح'], 403);
        $name = $input['name'] ?? '';
        dbQuery("DELETE FROM chambers WHERE name = ?", [$name]);
        sendResponse(['success' => true]);
        break;
}
