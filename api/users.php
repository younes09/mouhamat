<?php
if (!defined('DB_HOST') && !function_exists('sendResponse')) {
    exit('Restricted access');
}

switch ($action) {
    case 'get_users':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')
            sendResponse(['error' => 'غير مصرح'], 403);
        $users = dbFetchAll("SELECT id, first_name, last_name, email, phone, oath_date, is_syndicate_member, role, status, id_card_url FROM users ORDER BY created_at DESC");
        // Format to camelCase for client compatibility
        $formatted = array_map(function ($u) {
            return [
                'id' => $u['id'],
                'firstName' => $u['first_name'],
                'lastName' => $u['last_name'],
                'email' => $u['email'] ?? '',
                'phone' => $u['phone'] ?? '',
                'oathDate' => $u['oath_date'],
                'isSyndicateMember' => (bool) $u['is_syndicate_member'],
                'role' => $u['role'],
                'status' => $u['status'],
                'idCardUrl' => $u['id_card_url']
            ];
        }, $users);
        sendResponse($formatted);
        break;

    case 'update_user_status':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')
            sendResponse(['error' => 'غير مصرح'], 403);
        $userId = $input['id'] ?? '';
        $status = $input['status'] ?? 'approved';

        dbQuery("UPDATE users SET status = ? WHERE id = ?", [$status, $userId]);
        sendResponse(['success' => true]);
        break;

    case 'add_user':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')
            sendResponse(['error' => 'غير مصرح'], 403);
        $lastName = trim($input['lastName'] ?? '');
        $firstName = trim($input['firstName'] ?? '');
        $email = trim(strtolower($input['email'] ?? ''));
        $phone = trim($input['phone'] ?? '');
        $password = $input['password'] ?? '';
        $role = $input['role'] ?? 'lawyer';
        $status = $input['status'] ?? 'approved';
        $oathDate = $input['oathDate'] ?? '';
        $isSyndicateMember = (bool) ($input['isSyndicateMember'] ?? false);

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
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')
            sendResponse(['error' => 'غير مصرح'], 403);
        $id = $input['id'] ?? '';
        $lastName = trim($input['lastName'] ?? '');
        $firstName = trim($input['firstName'] ?? '');
        $email = trim(strtolower($input['email'] ?? ''));
        $phone = trim($input['phone'] ?? '');
        $password = $input['password'] ?? '';
        $role = $input['role'] ?? 'lawyer';
        $status = $input['status'] ?? 'approved';
        $oathDate = $input['oathDate'] ?? '';
        $isSyndicateMember = (bool) ($input['isSyndicateMember'] ?? false);

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
                $firstName,
                $lastName,
                $email ?: null,
                $phone ?: null,
                $hashedPassword,
                $isSyndicateMember ? 'عضو نقابة' : $oathDate,
                $isSyndicateMember ? 1 : 0,
                $role,
                $status,
                $id
            ]);
        } else {
            dbQuery("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, oath_date = ?, is_syndicate_member = ?, role = ?, status = ? WHERE id = ?", [
                $firstName,
                $lastName,
                $email ?: null,
                $phone ?: null,
                $isSyndicateMember ? 'عضو نقابة' : $oathDate,
                $isSyndicateMember ? 1 : 0,
                $role,
                $status,
                $id
            ]);
        }
        sendResponse(['success' => true]);
        break;

    case 'delete_user':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')
            sendResponse(['error' => 'غير مصرح'], 403);
        $id = $input['id'] ?? $_GET['id'] ?? '';
        if (empty($id))
            sendResponse(['error' => 'المعرف مطلوب'], 400);

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
}
