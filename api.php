<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/db.php';

// Helper function to generate UUID v4
function generateUUID() {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// Response helper
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_code_response($data);
    exit;
}

function json_code_response($data) {
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? '';

// Global Check: List Open setting
function isListOpen() {
    $setting = dbFetch("SELECT setting_value FROM system_settings WHERE setting_key = 'is_list_open'");
    return $setting && $setting['setting_value'] === '1';
}

switch ($action) {
    case 'auth_check':
        if (isset($_SESSION['user'])) {
            // Refresh user details from DB to reflect role/status updates
            $user = dbFetch("SELECT id, first_name, last_name, oath_date, is_syndicate_member, role, status FROM users WHERE id = ?", [$_SESSION['user']['id']]);
            if ($user) {
                $_SESSION['user'] = $user;
                sendResponse(['authenticated' => true, 'user' => $user]);
            }
        }
        sendResponse(['authenticated' => false]);
        break;

    case 'login':
        $email = trim($_POST['email'] ?? $input['email'] ?? '');
        $password = $_POST['password'] ?? $input['password'] ?? '';

        // Guest login shortcut
        $role = $_POST['role'] ?? $input['role'] ?? '';
        if ($role === 'guest') {
            $user = [
                'id' => 'guest',
                'first_name' => 'زائر',
                'last_name' => '',
                'email' => '',
                'phone' => '',
                'oath_date' => '',
                'is_syndicate_member' => 0,
                'role' => 'guest',
                'status' => 'approved'
            ];
            $_SESSION['user'] = $user;
            sendResponse(['success' => true, 'user' => $user]);
        }

        if (empty($email)) {
            sendResponse(['error' => 'الرجاء إدخال البريد الإلكتروني'], 400);
        }

        // Find user by email
        $user = dbFetch("SELECT * FROM users WHERE email = ?", [$email]);

        if ($user) {
            if (!password_verify($password, $user['password'])) {
                sendResponse(['error' => 'كلمة السر خاطئة'], 400);
            }

            if ($user['role'] === 'lawyer') {
                if ($user['status'] === 'pending') {
                    sendResponse(['error' => 'حسابك قيد المراجعة من طرف مندوب النقيب. يرجى المحاولة لاحقاً.'], 403);
                }
                if ($user['status'] === 'rejected') {
                    sendResponse(['error' => 'تم رفض طلب انضمامك. يرجى الاتصال بمندوب النقابة.'], 403);
                }
            }

            // Remove password before saving in session
            unset($user['password']);
            $_SESSION['user'] = $user;
            sendResponse(['success' => true, 'user' => $user]);
        } else {
            sendResponse(['error' => 'لا يوجد حساب مسجل بهذا البريد الإلكتروني. يرجى التسجيل أولاً.'], 404);
        }
        break;

    case 'register':
        $lastName = $_POST['lastName'] ?? $input['lastName'] ?? '';
        $firstName = $_POST['firstName'] ?? $input['firstName'] ?? '';
        $password = $_POST['password'] ?? $input['password'] ?? '';
        $email = trim(strtolower($_POST['email'] ?? $input['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? $input['phone'] ?? '');
        $oathDate = $_POST['oathDate'] ?? $input['oathDate'] ?? '';
        $isSyndicateMember = ($_POST['isSyndicateMember'] ?? $input['isSyndicateMember'] ?? '') === 'true' || ($_POST['isSyndicateMember'] ?? '') === 'on';

        if (empty($lastName) || empty($firstName) || empty($password)) {
            sendResponse(['error' => 'الرجاء ملء جميع الحقول المطلوبة'], 400);
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendResponse(['error' => 'يرجى إدخال بريد إلكتروني صحيح'], 400);
        }

        if (empty($phone)) {
            sendResponse(['error' => 'يرجى إدخال رقم الهاتف'], 400);
        }

        // Check if email already exists
        $existingEmail = dbFetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existingEmail) {
            sendResponse(['error' => 'هذا البريد الإلكتروني مسجل بالفعل. يرجى تسجيل الدخول.'], 400);
        }

        // Check if user name already exists
        $existingName = dbFetch("SELECT id FROM users WHERE first_name = ? AND last_name = ?", [$firstName, $lastName]);
        if ($existingName) {
            sendResponse(['error' => 'هذا الاسم مسجل بالفعل. يرجى تسجيل الدخول.'], 400);
        }

        if (!$isSyndicateMember && empty($oathDate)) {
            sendResponse(['error' => 'يرجى إدخال تاريخ أداء اليمين للتسجيل'], 400);
        }

        // File upload for ID card
        $idCardUrl = null;
        if (isset($_FILES['idCard']) && $_FILES['idCard']['error'] === UPLOAD_ERR_OK) {
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            $ext = pathinfo($_FILES['idCard']['name'], PATHINFO_EXTENSION);
            $filename = generateUUID() . '.' . $ext;
            $targetPath = 'uploads/' . $filename;
            
            if (move_uploaded_file($_FILES['idCard']['tmp_name'], $targetPath)) {
                $idCardUrl = $targetPath;
            }
        }

        if (empty($idCardUrl)) {
            sendResponse(['error' => 'يجب رفع صورة بطاقة المحامي للتسجيل'], 400);
        }

        $newUserId = generateUUID();
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        dbQuery("INSERT INTO users (id, first_name, last_name, email, phone, password, oath_date, is_syndicate_member, role, status, id_card_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'lawyer', 'pending', ?)", [
            $newUserId,
            $firstName,
            $lastName,
            $email,
            $phone,
            $hashedPassword,
            $isSyndicateMember ? 'عضو نقابة' : $oathDate,
            $isSyndicateMember ? 1 : 0,
            $idCardUrl
        ]);

        sendResponse(['pending' => true, 'message' => 'تم تسجيل طلبك بنجاح. سيتم مراجعة بطاقة المحامي من طرف المندوب قبل تفعيل الحساب.']);
        break;

    case 'logout':
        session_destroy();
        sendResponse(['success' => true]);
        break;

    case 'get_requests':
        $isHistory = ($_GET['history'] ?? 'false') === 'true';
        $requests = dbFetchAll("SELECT * FROM requests WHERE is_archived = ? ORDER BY created_at DESC", [$isHistory ? 1 : 0]);
        
        // Return type matching JS structures
        $formatted = array_map(function($r) {
            return [
                'id' => $r['id'],
                'lawyerName' => $r['lawyer_name'],
                'oathDate' => $r['oath_date'],
                'isSyndicateMember' => (bool)$r['is_syndicate_member'],
                'caseNumber' => $r['case_number'],
                'parties' => $r['parties'],
                'purpose' => $r['purpose'],
                'createdAt' => (float)$r['created_at'],
                'sessionDate' => $r['session_date'],
                'isColleague' => (bool)$r['is_colleague'],
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
        if (!isset($_SESSION['user'])) sendResponse(['error' => 'غير مصرح'], 401);
        $user = $_SESSION['user'];

        // Validation for session/roles and constraints
        $now = new DateTime();
        $day = (int)$now->format('w'); // 0 (Sunday) to 6 (Saturday)
        
        // Weekend constraint (Friday=5, Saturday=6 in original React logic, but let's check PHP: w is 0 to 6, Friday is 5, Saturday is 6)
        if (($day == 5 || $day == 6) && $user['role'] === 'lawyer') {
            sendResponse(['error' => 'عذراً، لا يوجد استخراج للقضايا يومي الجمعة والسبت.'], 400);
        }

        $currentTime = (int)$now->format('H') + ((int)$now->format('i') / 60);
        if (($currentTime < 6.0 || $currentTime >= 14.5) && $user['role'] === 'lawyer') {
            sendResponse(['error' => 'عذراً، القائمة مغلقة حالياً. تفتح القائمة من الساعة 06:00 صباحاً إلى غاية الساعة 14:30 مساءً.'], 400);
        }

        if (!isListOpen() && $user['role'] === 'lawyer') {
            sendResponse(['error' => 'عذراً، القائمة مغلقة حالياً من قبل المندوبية.'], 400);
        }

        $caseNumber = $input['caseNumber'] ?? '';
        $parties = $input['parties'] ?? '';
        $purpose = $input['purpose'] ?? 'delay';
        $sessionDate = $input['sessionDate'] ?? '';
        $isColleague = (bool)($input['isColleague'] ?? false);
        
        // Colleague details
        $colleagueFirstName = $input['colleagueFirstName'] ?? '';
        $colleagueLastName = $input['colleagueLastName'] ?? '';
        $colleagueOathDate = $input['colleagueOathDate'] ?? '';
        $colleagueIsSyndicateMember = (bool)($input['colleagueIsSyndicateMember'] ?? false);

        // Current Jurisdiction details
        $jurType = $input['jurisdiction']['type'] ?? 'court';
        $jurName = $input['jurisdiction']['name'] ?? '';
        $jurSub = $input['jurisdiction']['subEntity'] ?? '';

        // Validate Case Number formatting: e.g. "26-1024" or "25-392"
        $currentYear = (int)date('y');
        $prevYear = $currentYear - 1;
        $pattern = "/^(" . $currentYear . "|" . $prevYear . ")-\d{1,5}$/";
        if (!preg_match($pattern, $caseNumber)) {
            sendResponse(['error' => "رقم القضية غير صحيح. يجب أن يكون بالتنسيق: السنة-رقم الملف (مثلاً: {$currentYear}-1234) وأن تكون السنة هي {$currentYear} أو {$prevYear}"], 400);
        }

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
        if (!isset($_SESSION['user'])) sendResponse(['error' => 'غير مصرح'], 401);
        $user = $_SESSION['user'];

        $id = $input['id'] ?? '';
        $caseNumber = $input['caseNumber'] ?? '';
        $parties = $input['parties'] ?? '';
        $purpose = $input['purpose'] ?? 'delay';

        $req = dbFetch("SELECT * FROM requests WHERE id = ?", [$id]);
        if (!$req) sendResponse(['error' => 'الطلب غير موجود'], 404);

        $canEdit = ($user['role'] === 'admin' || $user['role'] === 'delegate' || $req['creator_id'] === $user['id']);
        if (!$canEdit) sendResponse(['error' => 'لا تملك الصلاحية لتعديل هذا الطلب'], 403);

        // Validate Case Number
        $currentYear = (int)date('y');
        $prevYear = $currentYear - 1;
        $pattern = "/^(" . $currentYear . "|" . $prevYear . ")-\d{1,5}$/";
        if (!preg_match($pattern, $caseNumber)) {
            sendResponse(['error' => "رقم القضية غير صحيح. يجب أن يكون بالتنسيق: السنة-رقم الملف (مثلاً: {$currentYear}-1234) وأن تكون السنة هي {$currentYear} أو {$prevYear}"], 400);
        }

        dbQuery("UPDATE requests SET case_number = ?, parties = ?, purpose = ? WHERE id = ?", [$caseNumber, $parties, $purpose, $id]);
        sendResponse(['success' => true]);
        break;

    case 'delete_request':
        if (!isset($_SESSION['user'])) sendResponse(['error' => 'غير مصرح'], 401);
        $user = $_SESSION['user'];

        $id = $input['id'] ?? $_GET['id'] ?? '';
        $req = dbFetch("SELECT * FROM requests WHERE id = ?", [$id]);
        if (!$req) sendResponse(['error' => 'الطلب غير موجود'], 404);

        $canDelete = ($user['role'] === 'admin' || $user['role'] === 'delegate' || $req['creator_id'] === $user['id']);
        if (!$canDelete) sendResponse(['error' => 'لا تملك الصلاحية لحذف هذا الطلب'], 403);

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

    case 'add_council':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') sendResponse(['error' => 'غير مصرح'], 403);
        $name = trim($input['name'] ?? '');
        if (empty($name)) sendResponse(['error' => 'الاسم مطلوب'], 400);

        try {
            dbQuery("INSERT INTO councils (name) VALUES (?)", [$name]);
            sendResponse(['success' => true]);
        } catch (Exception $e) {
            sendResponse(['error' => 'المجلس موجود بالفعل'], 400);
        }
        break;

    case 'delete_council':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') sendResponse(['error' => 'غير مصرح'], 403);
        $name = $input['name'] ?? '';
        dbQuery("DELETE FROM councils WHERE name = ?", [$name]);
        // Also cascade delete courts belonging to it
        dbQuery("DELETE FROM courts WHERE council_name = ?", [$name]);
        sendResponse(['success' => true]);
        break;

    case 'add_court':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') sendResponse(['error' => 'غير مصرح'], 403);
        $name = trim($input['name'] ?? '');
        $council = trim($input['council'] ?? '');
        if (empty($name) || empty($council)) sendResponse(['error' => 'البيانات ناقصة'], 400);

        try {
            dbQuery("INSERT INTO courts (name, council_name) VALUES (?, ?)", [$name, $council]);
            sendResponse(['success' => true]);
        } catch (Exception $e) {
            sendResponse(['error' => 'المحكمة موجودة بالفعل'], 400);
        }
        break;

    case 'delete_court':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') sendResponse(['error' => 'غير مصرح'], 403);
        $name = $input['name'] ?? '';
        dbQuery("DELETE FROM courts WHERE name = ?", [$name]);
        sendResponse(['success' => true]);
        break;

    case 'add_section':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') sendResponse(['error' => 'غير مصرح'], 403);
        $name = trim($input['name'] ?? '');
        if (empty($name)) sendResponse(['error' => 'الاسم مطلوب'], 400);

        try {
            dbQuery("INSERT INTO sections (name) VALUES (?)", [$name]);
            sendResponse(['success' => true]);
        } catch (Exception $e) {
            sendResponse(['error' => 'القسم موجود بالفعل'], 400);
        }
        break;

    case 'delete_section':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') sendResponse(['error' => 'غير مصرح'], 403);
        $name = $input['name'] ?? '';
        dbQuery("DELETE FROM sections WHERE name = ?", [$name]);
        sendResponse(['success' => true]);
        break;

    case 'add_chamber':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') sendResponse(['error' => 'غير مصرح'], 403);
        $name = trim($input['name'] ?? '');
        if (empty($name)) sendResponse(['error' => 'الاسم مطلوب'], 400);

        try {
            dbQuery("INSERT INTO chambers (name) VALUES (?)", [$name]);
            sendResponse(['success' => true]);
        } catch (Exception $e) {
            sendResponse(['error' => 'الغرفة موجودة بالفعل'], 400);
        }
        break;

    case 'delete_chamber':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') sendResponse(['error' => 'غير مصرح'], 403);
        $name = $input['name'] ?? '';
        dbQuery("DELETE FROM chambers WHERE name = ?", [$name]);
        sendResponse(['success' => true]);
        break;

    case 'get_announcements':
        $announcements = dbFetchAll("SELECT * FROM announcements ORDER BY created_at DESC");
        $formatted = array_map(function($a) {
            return [
                'id' => $a['id'],
                'text' => $a['text'],
                'authorName' => $a['author_name'],
                'isActive' => (bool)$a['is_active'],
                'createdAt' => (float)$a['created_at']
            ];
        }, $announcements);
        sendResponse($formatted);
        break;

    case 'add_announcement':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') sendResponse(['error' => 'غير مصرح'], 403);
        $text = trim($input['text'] ?? '');
        if (empty($text)) sendResponse(['error' => 'نص الإعلان مطلوب'], 400);

        $id = generateUUID();
        $authorName = "الأستاذ " . $_SESSION['user']['last_name'] . " " . $_SESSION['user']['first_name'];
        $created_timestamp = round(microtime(true) * 1000);

        dbQuery("INSERT INTO announcements (id, text, author_name, is_active, created_at) VALUES (?, ?, ?, 1, ?)", [
            $id, $text, $authorName, $created_timestamp
        ]);
        sendResponse(['success' => true]);
        break;

    case 'toggle_announcement':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') sendResponse(['error' => 'غير مصرح'], 403);
        $id = $input['id'] ?? '';
        $active = ($input['isActive'] ?? false) ? 1 : 0;
        dbQuery("UPDATE announcements SET is_active = ? WHERE id = ?", [$active, $id]);
        sendResponse(['success' => true]);
        break;

    case 'delete_announcement':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') sendResponse(['error' => 'غير مصرح'], 403);
        $id = $input['id'] ?? $_GET['id'] ?? '';
        dbQuery("DELETE FROM announcements WHERE id = ?", [$id]);
        sendResponse(['success' => true]);
        break;

    case 'get_users':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') sendResponse(['error' => 'غير مصرح'], 403);
        $users = dbFetchAll("SELECT id, first_name, last_name, email, phone, oath_date, is_syndicate_member, role, status, id_card_url FROM users ORDER BY created_at DESC");
        // Format to camelCase for client compatibility
        $formatted = array_map(function($u) {
            return [
                'id' => $u['id'],
                'firstName' => $u['first_name'],
                'lastName' => $u['last_name'],
                'email' => $u['email'] ?? '',
                'phone' => $u['phone'] ?? '',
                'oathDate' => $u['oath_date'],
                'isSyndicateMember' => (bool)$u['is_syndicate_member'],
                'role' => $u['role'],
                'status' => $u['status'],
                'idCardUrl' => $u['id_card_url']
            ];
        }, $users);
        sendResponse($formatted);
        break;

    case 'update_user_status':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') sendResponse(['error' => 'غير مصرح'], 403);
        $userId = $input['id'] ?? '';
        $status = $input['status'] ?? 'approved';
        
        dbQuery("UPDATE users SET status = ? WHERE id = ?", [$status, $userId]);
        sendResponse(['success' => true]);
        break;

    case 'add_user':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') sendResponse(['error' => 'غير مصرح'], 403);
        $lastName = trim($input['lastName'] ?? '');
        $firstName = trim($input['firstName'] ?? '');
        $email = trim(strtolower($input['email'] ?? ''));
        $phone = trim($input['phone'] ?? '');
        $password = $input['password'] ?? '';
        $role = $input['role'] ?? 'lawyer';
        $status = $input['status'] ?? 'approved';
        $oathDate = $input['oathDate'] ?? '';
        $isSyndicateMember = (bool)($input['isSyndicateMember'] ?? false);

        if (empty($lastName) || empty($firstName) || empty($password)) {
            sendResponse(['error' => 'الاسم واللقب وكلمة السر حقول مطلوبة'], 400);
        }

        // Check if user already exists (by name)
        $existing = dbFetch("SELECT id FROM users WHERE first_name = ? AND last_name = ?", [$firstName, $lastName]);
        if ($existing) {
            sendResponse(['error' => 'هذا المستخدم مسجل بالفعل'], 400);
        }

        // Check email uniqueness if provided
        if (!empty($email)) {
            $existingEmail = dbFetch("SELECT id FROM users WHERE email = ?", [$email]);
            if ($existingEmail) {
                sendResponse(['error' => 'هذا البريد الإلكتروني مستخدم مسبقاً'], 400);
            }
        }

        $id = generateUUID();
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        dbQuery("INSERT INTO users (id, first_name, last_name, email, phone, password, oath_date, is_syndicate_member, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $id,
            $firstName,
            $lastName,
            $email ?: null,
            $phone ?: null,
            $hashedPassword,
            $isSyndicateMember ? 'عضو نقابة' : $oathDate,
            $isSyndicateMember ? 1 : 0,
            $role,
            $status
        ]);
        sendResponse(['success' => true]);
        break;

    case 'edit_user':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') sendResponse(['error' => 'غير مصرح'], 403);
        $id = $input['id'] ?? '';
        $lastName = trim($input['lastName'] ?? '');
        $firstName = trim($input['firstName'] ?? '');
        $email = trim(strtolower($input['email'] ?? ''));
        $phone = trim($input['phone'] ?? '');
        $password = $input['password'] ?? '';
        $role = $input['role'] ?? 'lawyer';
        $status = $input['status'] ?? 'approved';
        $oathDate = $input['oathDate'] ?? '';
        $isSyndicateMember = (bool)($input['isSyndicateMember'] ?? false);

        if (empty($id) || empty($lastName) || empty($firstName)) {
            sendResponse(['error' => 'الاسم واللقب والمعرف حقول مطلوبة'], 400);
        }

        // Check name uniqueness
        $existing = dbFetch("SELECT id FROM users WHERE first_name = ? AND last_name = ? AND id != ?", [$firstName, $lastName, $id]);
        if ($existing) {
            sendResponse(['error' => 'هناك مستخدم آخر مسجل بهذا الاسم واللقب'], 400);
        }

        // Check email uniqueness if provided
        if (!empty($email)) {
            $existingEmail = dbFetch("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $id]);
            if ($existingEmail) {
                sendResponse(['error' => 'هذا البريد الإلكتروني مستخدم من حساب آخر'], 400);
            }
        }

        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            dbQuery("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, password = ?, oath_date = ?, is_syndicate_member = ?, role = ?, status = ? WHERE id = ?", [
                $firstName, $lastName, $email ?: null, $phone ?: null, $hashedPassword, $isSyndicateMember ? 'عضو نقابة' : $oathDate, $isSyndicateMember ? 1 : 0, $role, $status, $id
            ]);
        } else {
            dbQuery("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, oath_date = ?, is_syndicate_member = ?, role = ?, status = ? WHERE id = ?", [
                $firstName, $lastName, $email ?: null, $phone ?: null, $isSyndicateMember ? 'عضو نقابة' : $oathDate, $isSyndicateMember ? 1 : 0, $role, $status, $id
            ]);
        }
        sendResponse(['success' => true]);
        break;

    case 'delete_user':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') sendResponse(['error' => 'غير مصرح'], 403);
        $id = $input['id'] ?? $_GET['id'] ?? '';
        if (empty($id)) sendResponse(['error' => 'المعرف مطلوب'], 400);

        // Don't let admin delete their own account
        if ($id === $_SESSION['user']['id']) {
            sendResponse(['error' => 'لا يمكنك حذف حسابك الشخصي أثناء تسجيل الدخول به'], 400);
        }
        
        // delete user id card
        $user = dbFetch("SELECT * FROM users WHERE id = ?", [$id]);
        if ($user) {
            if (file_exists($user['id_card_url'])) {
                unlink($user['id_card_url']);
            }
        }

        dbQuery("DELETE FROM users WHERE id = ?", [$id]);
        sendResponse(['success' => true]);
        break;

    default:
        sendResponse(['error' => 'أمر غير صالح'], 404);
}
