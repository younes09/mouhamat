<?php
if (!defined('DB_HOST') && !function_exists('sendResponse')) {
    exit('Restricted access');
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
}
