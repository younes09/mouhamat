<?php
if (!defined('DB_HOST') && !function_exists('sendResponse')) {
    exit('Restricted access');
}

switch ($action) {
    case 'get_announcements':
        $announcements = dbFetchAll("SELECT * FROM announcements ORDER BY created_at DESC");
        $formatted = array_map(function ($a) {
            return [
                'id' => $a['id'],
                'text' => $a['text'],
                'authorName' => $a['author_name'],
                'isActive' => (bool) $a['is_active'],
                'createdAt' => (float) $a['created_at']
            ];
        }, $announcements);
        sendResponse($formatted);
        break;

    case 'add_announcement':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')
            sendResponse(['error' => 'غير مصرح'], 403);
        $text = trim($input['text'] ?? '');
        if (empty($text))
            sendResponse(['error' => 'نص الإعلان مطلوب'], 400);

        $id = generateUUID();
        $authorName = "الأستاذ " . $_SESSION['user']['last_name'] . " " . $_SESSION['user']['first_name'];
        $created_timestamp = round(microtime(true) * 1000);

        dbQuery("INSERT INTO announcements (id, text, author_name, is_active, created_at) VALUES (?, ?, ?, 1, ?)", [
            $id,
            $text,
            $authorName,
            $created_timestamp
        ]);
        sendResponse(['success' => true]);
        break;

    case 'toggle_announcement':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')
            sendResponse(['error' => 'غير مصرح'], 403);
        $id = $input['id'] ?? '';
        $active = ($input['isActive'] ?? false) ? 1 : 0;
        dbQuery("UPDATE announcements SET is_active = ? WHERE id = ?", [$active, $id]);
        sendResponse(['success' => true]);
        break;

    case 'delete_announcement':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')
            sendResponse(['error' => 'غير مصرح'], 403);
        $id = $input['id'] ?? $_GET['id'] ?? '';
        dbQuery("DELETE FROM announcements WHERE id = ?", [$id]);
        sendResponse(['success' => true]);
        break;
}
