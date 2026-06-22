<?php
if (!defined('DB_HOST') && !function_exists('sendResponse')) {
    exit('Restricted access');
}

switch ($action) {
    case 'get_requests':
        $isHistory = ($_GET['history'] ?? 'false') === 'true';
        $requests = dbFetchAll("SELECT * FROM requests WHERE is_archived = ? ORDER BY created_at DESC", [$isHistory ? 1 : 0]);

        // Return type matching JS structures
        $formatted = array_map(function ($r) {
            return [
                'id' => $r['id'],
                'lawyerName' => $r['lawyer_name'],
                'oathDate' => $r['oath_date'],
                'isSyndicateMember' => (bool) $r['is_syndicate_member'],
                'caseNumber' => $r['case_number'],
                'parties' => $r['parties'],
                'purpose' => $r['purpose'],
                'createdAt' => (float) $r['created_at'],
                'sessionDate' => $r['session_date'],
                'isColleague' => (bool) $r['is_colleague'],
                'jurisdiction' => [
                    'type' => $r['jurisdiction_type'],
                    'name' => $r['jurisdiction_name'],
                    'subEntity' => $r['jurisdiction_sub_entity']
                ],
                'creatorId' => $r['creator_id'],
                'creatorRole' => $r['creator_role']
            ];
        }, $requests);

        sendResponse($formatted);
        break;

    case 'add_request':
        if (!isset($_SESSION['user']))
            sendResponse(['error' => 'غير مصرح'], 401);
        $user = $_SESSION['user'];

        // Validation for session/roles and constraints
        $now = new DateTime();
        $day = (int) $now->format('w'); // 0 (Sunday) to 6 (Saturday)

        // Weekend constraint
        $restrictedDaysSetting = getSystemSetting('restricted_days', '5,6');
        $restrictedDays = $restrictedDaysSetting !== '' ? array_map('intval', explode(',', $restrictedDaysSetting)) : [];
        if (in_array($day, $restrictedDays, true) && $user['role'] === 'lawyer') {
            $dayNames = [
                0 => 'الأحد',
                1 => 'الإثنين',
                2 => 'الثلاثاء',
                3 => 'الأربعاء',
                4 => 'الخميس',
                5 => 'الجمعة',
                6 => 'السبت'
            ];
            $restrictedNames = array_map(function($d) use ($dayNames) {
                return $dayNames[$d] ?? '';
            }, $restrictedDays);
            $restrictedNamesStr = implode(' و ', array_filter($restrictedNames));
            sendResponse(['error' => 'عذراً، لا يوجد استخراج للقضايا يوم/أيام: ' . $restrictedNamesStr . '.'], 400);
        }

        // Daily hours constraint
        $startTime = getSystemSetting('start_time', '12:00');
        $endTime = getSystemSetting('end_time', '14:30');
        
        list($startH, $startM) = explode(':', $startTime);
        list($endH, $endM) = explode(':', $endTime);
        $startFloat = (int)$startH + ((int)$startM / 60);
        $endFloat = (int)$endH + ((int)$endM / 60);

        $currentTime = (int) $now->format('H') + ((int) $now->format('i') / 60);
        if (($currentTime < $startFloat || $currentTime >= $endFloat) && $user['role'] === 'lawyer') {
            sendResponse(['error' => 'عذراً، القائمة مغلقة حالياً. تفتح القائمة من الساعة ' . $startTime . ' صباحاً إلى غاية الساعة ' . $endTime . ' مساءً.'], 400);
        }

        if (!isListOpen() && $user['role'] === 'lawyer') {
            sendResponse(['error' => 'عذراً، القائمة مغلقة حالياً من قبل المندوبية.'], 400);
        }

        $caseNumber = $input['caseNumber'] ?? '';
        $parties = $input['parties'] ?? '';
        $purpose = $input['purpose'] ?? 'delay';
        $sessionDate = $input['sessionDate'] ?? '';
        $isColleague = (bool) ($input['isColleague'] ?? false);

        // Colleague details
        $colleagueFirstName = $input['colleagueFirstName'] ?? '';
        $colleagueLastName = $input['colleagueLastName'] ?? '';
        $colleagueOathDate = $input['colleagueOathDate'] ?? '';
        $colleagueIsSyndicateMember = (bool) ($input['colleagueIsSyndicateMember'] ?? false);

        // Current Jurisdiction details
        $jurType = $input['jurisdiction']['type'] ?? 'court';
        $jurName = $input['jurisdiction']['name'] ?? '';
        $jurSub = $input['jurisdiction']['subEntity'] ?? '';

        // Validate Case Number formatting: e.g. "26-1024" or "25-392"
        // $currentYear = (int) date('y');
        // $prevYear = $currentYear - 1;
        // $pattern = "/^(" . $currentYear . "|" . $prevYear . ")-\d{1,5}$/";
        // if (!preg_match($pattern, $caseNumber)) {
        //     sendResponse(['error' => "رقم القضية غير صحيح. يجب أن يكون بالتنسيق: السنة-رقم الملف (مثلاً: {$currentYear}-1234) وأن تكون السنة هي {$currentYear} أو {$prevYear}"], 400);
        // }

        $lawyerName = "";
        $oathDate = "";
        $isSyndicate = 0;

        if ($isColleague) {
            $lawyerName = "الأستاذ " . $colleagueLastName . " " . $colleagueFirstName;
            $oathDate = $colleagueIsSyndicateMember ? 'عضو نقابة' : $colleagueOathDate;
            $isSyndicate = $colleagueIsSyndicateMember ? 1 : 0;
        } else {
            $lawyerName = "الأستاذ " . $user['last_name'] . " " . $user['first_name'];
            $oathDate = $user['oath_date'];
            $isSyndicate = $user['is_syndicate_member'] ? 1 : 0;
        }

        $id = generateUUID();
        $created_timestamp = round(microtime(true) * 1000);

        dbQuery(
            "INSERT INTO requests (id, lawyer_name, oath_date, is_syndicate_member, case_number, parties, purpose, session_date, is_colleague, jurisdiction_type, jurisdiction_name, jurisdiction_sub_entity, creator_id, creator_role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$id, $lawyerName, $oathDate, $isSyndicate, $caseNumber, $parties, $purpose, $sessionDate, $isColleague ? 1 : 0, $jurType, $jurName, $jurSub, $user['id'], $user['role'], $created_timestamp]
        );

        sendResponse(['success' => true]);
        break;

    case 'edit_request':
        if (!isset($_SESSION['user']))
            sendResponse(['error' => 'غير مصرح'], 401);
        $user = $_SESSION['user'];

        $id = $input['id'] ?? '';
        $caseNumber = $input['caseNumber'] ?? '';
        $parties = $input['parties'] ?? '';
        $purpose = $input['purpose'] ?? 'delay';

        $req = dbFetch("SELECT * FROM requests WHERE id = ?", [$id]);
        if (!$req)
            sendResponse(['error' => 'الطلب غير موجود'], 404);

        $canEdit = ($user['role'] === 'admin' || $user['role'] === 'delegate' || $req['creator_id'] === $user['id']);
        if (!$canEdit)
            sendResponse(['error' => 'لا تملك الصلاحية لتعديل هذا الطلب'], 403);

        // Validate Case Number
        // $currentYear = (int) date('y');
        // $prevYear = $currentYear - 1;
        // $pattern = "/^(" . $currentYear . "|" . $prevYear . ")-\d{1,5}$/";
        // if (!preg_match($pattern, $caseNumber)) {
        //     sendResponse(['error' => "رقم القضية غير صحيح. يجب أن يكون بالتنسيق: السنة-رقم الملف (مثلاً: {$currentYear}-1234) وأن تكون السنة هي {$currentYear} أو {$prevYear}"], 400);
        // }

        dbQuery("UPDATE requests SET case_number = ?, parties = ?, purpose = ? WHERE id = ?", [$caseNumber, $parties, $purpose, $id]);
        sendResponse(['success' => true]);
        break;

    case 'delete_request':
        if (!isset($_SESSION['user']))
            sendResponse(['error' => 'غير مصرح'], 401);
        $user = $_SESSION['user'];

        $id = $input['id'] ?? $_GET['id'] ?? '';
        $req = dbFetch("SELECT * FROM requests WHERE id = ?", [$id]);
        if (!$req)
            sendResponse(['error' => 'الطلب غير موجود'], 404);

        $canDelete = ($user['role'] === 'admin' || $user['role'] === 'delegate' || $req['creator_id'] === $user['id']);
        if (!$canDelete)
            sendResponse(['error' => 'لا تملك الصلاحية لحذف هذا الطلب'], 403);

        dbQuery("DELETE FROM requests WHERE id = ?", [$id]);
        sendResponse(['success' => true]);
        break;

    case 'archive_requests':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            sendResponse(['error' => 'غير مصرح'], 403);
        }

        dbQuery("UPDATE requests SET is_archived = 1 WHERE is_archived = 0");
        sendResponse(['success' => true]);
        break;

    case 'clear_requests':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            sendResponse(['error' => 'غير مصرح'], 403);
        }

        dbQuery("DELETE FROM requests WHERE is_archived = 0");
        sendResponse(['success' => true]);
        break;
}
