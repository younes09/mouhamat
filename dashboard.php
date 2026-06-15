<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: index.php");
  exit;
}
$user_json = json_encode($_SESSION['user'], JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>لوحة التحكم | منظمة محامي البليدة</title>
  <link rel="icon" type="image/x-icon" href="logo.png">

  <!-- SEO Meta Tags -->
  <meta name="description"
    content="نظام إدارة الجلسات الرقمي لمنظمة محامي البليدة. تسجيل استخراج قوائم التسبيقات والتأجيلات وتسهيل التنسيق القضائي.">
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#059669">

  <!-- Apple Touch Support -->
  <link rel="apple-touch-icon"
    href="https://storage.googleapis.com/static.ai.studio/build/Rachid.Mca.Chido%40gmail.com/Rachid.Mca.Chido%40gmail.com_1775555917000_0.png">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!-- Bootstrap 5 RTL -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">

  <!-- Flatpickr (Calendar component replacement) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_green.css">

  <!-- Custom Styles -->
  <link rel="stylesheet" href="style.css">
</head>

<body>

  <!-- ==================== AUTHENTICATED APP VIEWS CONTAINER ==================== -->
  <div id="appContainer" class="fade-in">

    <!-- HEADER -->
    <header class="bg-white border-bottom sticky-top z-3 print-hidden">
      <div class="max-w-5xl mx-auto px-4 py-3 d-flex align-items-center justify-content-between">

        <!-- Syndicate Title -->
        <div class="d-flex align-items-center gap-3">
          <div
            class="border rounded-3 shadow-sm overflow-hidden d-flex align-items-center justify-content-center bg-white"
            style="width: 55px; height: 55px;">
            <img src="logo.png" alt="Logo" class="w-100 h-100 object-fit-contain p-1">
          </div>
          <div>
            <h1 class="fs-5 fw-black text-dark mb-0 leading-tight">منظمة محامي البليدة</h1>
            <p class="text-xs fw-bold text-success mb-0">مجلس قضاء البليدة</p>
          </div>
        </div>

        <!-- User status & theme triggers -->
        <div class="d-flex align-items-center gap-3">
          <!-- Notification Button -->
          <button type="button" onclick="showTab('announcements')"
            class="btn btn-light p-2 rounded-3 border-0 text-muted position-relative" title="الإعلانات والمستجدات">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span id="announcementBadge"
              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
              0
            </span>
          </button>

          <!-- Theme Toggle Button (show message on click this future is not rady yet , work in progress) -->
          <button type="button" id="darkToggle" class="btn btn-light p-2 rounded-3 border-0 text-muted"
            title="الوضع الليلي" disabled>
            <i data-lucide="moon" class="w-5 h-5"></i>
          </button>

          <!-- User Details card (Desktop) -->
          <div class="d-none d-sm-block text-start border-start ps-3 py-1">
            <p class="text-sm fw-bold text-dark mb-0" id="headerUserFullName">الأستاذ ...</p>
            <small class="text-xs text-muted" id="headerUserRole">محامي</small>
          </div>

          <!-- Logout Button -->
          <button type="button" id="logoutBtn" class="btn btn-light p-2 rounded-3 border-0 text-danger"
            title="تسجيل الخروج">
            <i data-lucide="log-out" class="w-5 h-5"></i>
          </button>
        </div>

      </div>
    </header>


    <!-- NAVIGATION TABS -->
    <div class="bg-white border-bottom sticky-top z-2 print-hidden" style="top: 79px;">
      <div class="max-w-5xl mx-auto px-4 d-flex align-items-center gap-4 overflow-x-auto scrollbar-hide">
        <button class="nav-tab-btn active" data-tab="requests">الطلبات</button>
        <button class="nav-tab-btn" data-tab="announcements">الإعلانات</button>
        <button class="nav-tab-btn delegate-admin-only d-none" data-tab="archive">الأرشيف</button>
        <button class="nav-tab-btn" data-tab="calendar">التقويم</button>
        <button class="nav-tab-btn delegate-admin-only d-none" data-tab="stats">الإحصائيات</button>
        <button class="nav-tab-btn delegate-admin-only d-none" data-tab="users">إدارة المستخدمين</button>
        <button class="nav-tab-btn delegate-admin-only d-none" data-tab="settings">الإعدادات</button>
        <button class="nav-tab-btn" data-tab="profile">الملف الشخصي</button>
      </div>
    </div>


    <!-- MAIN CONTENT VIEW WRAPPER -->
    <main class="max-w-5xl mx-auto px-4 py-4">

      <!-- ==================== TAB: REQUESTS VIEW ==================== -->
      <section id="requestsView" class="tab-content-panel fade-in">

        <!-- Announcements Alerts Board -->
        <div id="announcementsAlertCard"
          class="card premium-card overflow-hidden mb-4 border-success d-none print-hidden">
          <div class="bg-success text-white px-4 py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
              <i data-lucide="bell"></i>
              <h5 class="fw-bold mb-0 text-sm">لوحة الإعلانات والمستجدات</h5>
            </div>
            <button onclick="showTab('announcements')"
              class="btn btn-sm btn-light py-1 px-3 text-xs fw-bold border-0 text-success">عرض الكل</button>
          </div>
          <div class="p-3" id="announcementsCompactList">
            <!-- loaded dynamically -->
          </div>
        </div>

        <!-- Session Date Header Panel -->
        <div id="sessionDateHeaderPanel"
          class="premium-card p-4 mb-4 d-flex align-items-center justify-content-between print-hidden">
          <div class="text-right">
            <small class="text-xs fw-bold text-muted text-uppercase tracking-wider">تاريخ الجلسة المستهدفة</small>
            <h4 class="fw-black text-dark mb-0 mt-1" id="sessionTargetDateText">الأحد، 15 جوان 2026</h4>
          </div>
          <span class="badge bg-success-subtle text-success py-2 px-3 fw-bold text-xs">الجلسة القادمة</span>
        </div>

        <!-- Search Bar -->
        <div id="searchBarContainer" class="mb-4 print-hidden">
          <div class="position-relative">
            <input type="text" id="searchInput"
              class="form-control form-input-custom ps-5 py-3 text-sm fw-bold shadow-sm"
              placeholder="البحث برقم القضية، اسم المحامي أو الأطراف...">
            <span class="position-absolute start-0 top-50 translate-middle-y ps-4 text-muted"><i data-lucide="search"
                class="w-5 h-5"></i></span>
          </div>
        </div>

        <!-- Jurisdiction Selector Card -->
        <div id="jurisdictionSelectorCard" class="card premium-card p-4 mb-4 print-hidden">
          <div class="d-flex align-items-center gap-2 mb-3 fw-bold text-dark text-sm border-bottom pb-2">
            <i data-lucide="scale" class="text-success"></i>
            إعدادات القائمة الحالية
          </div>
          <div class="row g-3">
            <div class="col-12 col-md-3">
              <label class="form-label text-xs text-muted mb-1">نوع الهيئة</label>
              <select id="jurTypeSelect" class="form-select form-input-custom text-sm fw-bold">
                <option value="court">محكمة</option>
                <option value="council">مجلس قضاء</option>
              </select>
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label text-xs text-muted mb-1">المجلس القضائي</label>
              <select id="jurCouncilSelect" class="form-select form-input-custom text-sm fw-bold">
                <!-- Councils dynamic -->
              </select>
            </div>
            <div class="col-12 col-md-3" id="courtSelectGroup">
              <label class="form-label text-xs text-muted mb-1">المحكمة</label>
              <select id="jurCourtSelect" class="form-select form-input-custom text-sm fw-bold">
                <!-- Courts dynamic -->
              </select>
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label text-xs text-muted mb-1" id="subEntityLabel">القسم / الغرفة</label>
              <select id="jurSubSelect" class="form-select form-input-custom text-sm fw-bold">
                <!-- sub-entity dynamic -->
              </select>
            </div>
          </div>
        </div>

        <!-- Delegate Reminder -->
        <div id="delegateReminder" class="alert alert-primary text-sm fw-bold p-3 rounded-3 mb-4 d-none print-hidden">
          <div class="d-flex align-items-center gap-2">
            <i data-lucide="info" class="text-primary shrink-0"></i>
            تذكير للمندوب: يرجى طبع القائمة النهائية في حدود الساعة 15:00 لتقديمها للجهة القضائية.
          </div>
        </div>

        <!-- Notes Board Collapse -->
        <div id="notesContainer" class="mb-4 print-hidden">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="text-sm fw-bold text-dark mb-0 d-flex align-items-center gap-2">
              <i data-lucide="alert-circle" class="text-success"></i>
              ملاحظات هامة:
            </h5>
            <button id="toggleNotesBtn"
              class="btn btn-link text-success text-xs fw-bold p-0 m-0 border-0 text-decoration-none">
              <i data-lucide="eye-off" class="w-3.5 h-3.5 inline"></i> إخفاء الملاحظات
            </button>
          </div>
          <div id="notesContent" class="card premium-card bg-warning-subtle p-4 border-warning">
            <div class="row g-3">
              <div class="col-12 col-md-4 text-xs text-warning-emphasis fw-bold d-flex gap-2">
                <span class="rounded-circle bg-warning d-inline-block mt-1 shrink-0"
                  style="width: 6px; height: 6px;"></span>
                يراعى عدم استخراج القضايا القديمة للتأجيل.
              </div>
              <div class="col-12 col-md-4 text-xs text-warning-emphasis fw-bold d-flex gap-2">
                <span class="rounded-circle bg-warning d-inline-block mt-1 shrink-0"
                  style="width: 6px; height: 6px;"></span>
                يراعى حضور الأستاذ الأصيل أو من ينوبه يوم الجلسة.
              </div>
              <div class="col-12 col-md-4 text-xs text-warning-emphasis fw-bold d-flex gap-2">
                <span class="rounded-circle bg-warning d-inline-block mt-1 shrink-0"
                  style="width: 6px; height: 6px;"></span>
                على الزملاء التنسيق فيما بينهم في القضايا المشتركة.
              </div>
            </div>
          </div>
        </div>

        <!-- Action tools toolbar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 print-hidden">
          <div class="text-right">
            <h3 class="fs-4 fw-black text-dark mb-0 d-flex align-items-center gap-2">
              <i data-lucide="file-text" class="text-success"></i>
              الطلبات المسجلة
            </h3>
            <p class="text-xs text-muted mb-0" id="listTitleHeader">محكمة البليدة - قسم الجنح</p>
          </div>

          <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Add request button -->
            <button id="addNewCaseBtn" onclick="openAddCaseModal()"
              class="btn btn-emerald d-flex align-items-center gap-2 scale-active">
              <i data-lucide="plus" class="w-5 h-5"></i>
              إضافة قضية جديدة
            </button>

            <!-- Admin archive/clear actions -->
            <button onclick="archiveCurrentList()"
              class="btn btn-dark admin-only d-none d-flex align-items-center gap-2 scale-active"
              title="نقل القائمة للأرشيف">
              <i data-lucide="history" class="w-5 h-5"></i>
              أرشفة
            </button>
            <button onclick="clearCurrentList()"
              class="btn btn-danger admin-only d-none d-flex align-items-center gap-2 scale-active"
              title="مسح القائمة الحالية">
              <i data-lucide="trash-2" class="w-5 h-5"></i>
              مسح القائمة
            </button>

            <!-- Export formats -->
            <button onclick="handleDownloadPDF()" class="btn btn-dark d-flex align-items-center gap-2 scale-active"
              title="تنزيل كملف PDF">
              <i data-lucide="download" class="w-5 h-5"></i>
              تحميل PDF
            </button>

            <?php if ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'delegate'): ?>
              <button onclick="handlePrint()" class="btn btn-light border d-flex align-items-center gap-2 scale-active"
                title="طباعة">
                <i data-lucide="printer" class="w-5 h-5"></i>
                طباعة
              </button>
            <?php endif; ?>
          </div>
        </div>

        <!-- Desktop requests table -->
        <div class="card premium-card overflow-hidden shadow-sm d-none d-md-block print-block">
          <div class="table-responsive">
            <table class="table custom-table mb-0">
              <thead>
                <tr>
                  <th class="print-border-black" style="width: 8%;">الترتيب</th>
                  <th class="print-border-black" style="width: 25%;">الأستاذ</th>
                  <th class="print-border-black" style="width: 27%;">الأطراف</th>
                  <th class="print-border-black" style="width: 15%;">رقم القضية</th>
                  <th class="print-border-black" style="width: 15%;">سنة اليمين</th>
                  <th class="print-border-black" style="width: 10%;">الغرض</th>
                  <th class="print-hidden" style="width: 10%;">إجراءات</th>
                </tr>
              </thead>
              <tbody id="requestsTableBody">
                <!-- dynamic -->
              </tbody>
            </table>
          </div>
        </div>

        <!-- Mobile requests cards list -->
        <div class="d-md-none print-hidden" id="requestsMobileContainer">
          <!-- dynamic -->
        </div>

        <!-- Footer Copyrights Info -->
        <div
          class="text-center py-5 print-hidden text-muted d-flex flex-col align-items-center justify-content-center gap-2">
          <img src="logo.png" alt="Logo" class="opacity-25 grayscale" style="width: 50px;">
          <p class="mb-0 text-xs">يتم ترتيب القائمة تلقائياً حسب تاريخ أداء اليمين (الأقدمية)</p>
          <p class="mb-0 fw-bold text-xs">جميع الحقوق محفوظة لمنظمة محامي البليدة © 2026</p>
        </div>

      </section>


      <!-- ==================== TAB: ANNOUNCEMENTS VIEW ==================== -->
      <section id="announcementsView" class="tab-content-panel fade-in d-none">

        <div class="card premium-card overflow-hidden border-0 shadow-lg">
          <div class="bg-success text-center py-5 text-white position-relative"
            style="background: linear-gradient(135deg, var(--emerald-700), var(--emerald-600));">
            <div class="position-absolute top-50 start-50 translate-middle opacity-10">
              <!-- <i data-lucide="bell" style="width: 200px; height: 200px; opacity: 10%;"></i> -->
            </div>
            <div class="position-relative z-1">
              <div class="bg-white bg-opacity-25 rounded-4 d-inline-flex p-3 mb-3 backdrop-blur shadow-sm">
                <i data-lucide="bell" class="w-10 h-10 text-white"></i>
              </div>
              <h2 class="fw-black mb-1 text-white">لوحة الإعلانات الرسمية</h2>
              <p class="text-success-emphasis mb-0 opacity-75">آخر المستجدات والتنبيهات من مندوبية النقابة</p>
            </div>
          </div>

          <div class="p-4" id="announcementsFullGrid">
            <!-- dynamic -->
          </div>
        </div>

      </section>


      <!-- ==================== TAB: CALENDAR VIEW ==================== -->
      <section id="calendarView" class="tab-content-panel fade-in d-none">
        <div class="premium-card p-4 text-center max-w-md mx-auto">
          <h4 class="fw-bold mb-3 d-flex align-items-center justify-content-center gap-2">
            <i data-lucide="calendar" class="text-success"></i>
            التقويم القضائي
          </h4>
          <p class="text-muted text-sm mb-4">اختر التاريخ لتصفح الطلبات المرتبطة بتلك الجلسة</p>
          <div id="calendarPicker" class="mx-auto"></div>
        </div>
      </section>


      <!-- ==================== TAB: STATS VIEW (Admin) ==================== -->
      <section id="statsView" class="tab-content-panel fade-in d-none">
        <div class="row g-4 mb-4">
          <div class="col-12 col-md-4">
            <div class="premium-card p-4 text-center">
              <i data-lucide="bar-chart-2" class="w-8 h-8 text-success mb-2"></i>
              <h5 class="text-muted fw-bold text-xs mb-1">إجمالي الطلبات</h5>
              <p class="fs-1 fw-black text-success mb-0" id="statTotalRequests">0</p>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="premium-card p-4 text-center border-warning">
              <span class="rounded-circle bg-warning d-inline-block mb-2" style="width: 15px; height: 15px;"></span>
              <h5 class="text-muted fw-bold text-xs mb-1">طلبات التأجيل</h5>
              <p class="fs-1 fw-black text-warning mb-0" id="statDelayRequests">0</p>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="premium-card p-4 text-center border-primary">
              <span class="rounded-circle bg-primary d-inline-block mb-2" style="width: 15px; height: 15px;"></span>
              <h5 class="text-muted fw-bold text-xs mb-1">طلبات التسبيق</h5>
              <p class="fs-1 fw-black text-primary mb-0" id="statAdvanceRequests">0</p>
            </div>
          </div>
        </div>
      </section>


      <!-- ==================== TAB: USERS LIST VIEW (Admin) ==================== -->
      <section id="usersView" class="tab-content-panel fade-in d-none">
        <div class="card premium-card p-4">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
              <i data-lucide="users" class="text-success"></i>
              إدارة حسابات المحامين
            </h4>
            <button onclick="openUserModal()" class="btn btn-emerald d-flex align-items-center gap-2 scale-active">
              <i data-lucide="user-plus" class="w-5 h-5"></i>
              إضافة حساب جديد
            </button>
          </div>
          <div class="table-responsive">
            <table class="table custom-table align-middle">
              <thead>
                <tr>
                  <th>الأستاذ</th>
                  <th>سنة اليمين</th>
                  <th>الصفة</th>
                  <th>الحالة</th>
                  <th>إجراءات</th>
                </tr>
              </thead>
              <tbody id="usersTableBody">
                <!-- dynamic -->
              </tbody>
            </table>
          </div>
        </div>
      </section>


      <!-- ==================== TAB: SETTINGS VIEW (Admin) ==================== -->
      <section id="settingsView" class="tab-content-panel fade-in d-none">
        <div class="card premium-card p-4 mb-4">
          <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
            <h4 class="fw-bold mb-0 d-flex align-items-center gap-2">
              <i data-lucide="settings" class="text-success"></i>
              إعدادات النظام
            </h4>
            <div class="d-flex align-items-center gap-3">
              <span id="settingListStateText" class="fw-bold">القائمة مفتوحة</span>
              <button id="settingToggleListBtn" onclick="updateAdminListStatus(!systemSettings.isListOpen)"
                class="btn btn-danger btn-sm fw-bold px-3">غلق القائمة</button>
            </div>
          </div>

          <div class="row g-4">
            <!-- Councils settings -->
            <div class="col-12 col-md-6">
              <div class="card p-3 border">
                <h5 class="fw-bold text-sm mb-3 d-flex justify-content-between">
                  مجالس القضاء
                  <button class="btn btn-link text-success p-0 m-0 border-0 text-xs text-decoration-none fw-bold"
                    onclick="document.getElementById('addCouncilFormGroup').classList.toggle('d-none')">+ إضافة</button>
                </h5>
                <div id="addCouncilFormGroup" class="mb-3 d-none d-flex gap-2">
                  <input type="text" id="newCouncilInput" class="form-control form-control-sm form-input-custom"
                    placeholder="اسم المجلس...">
                  <button onclick="addAdminSettingItem('council')" class="btn btn-success btn-sm fw-bold">حفظ</button>
                </div>
                <div id="councilsListContainer" class="overflow-y-auto max-height-200">
                  <!-- dynamic -->
                </div>
              </div>
            </div>

            <!-- Courts settings -->
            <div class="col-12 col-md-6">
              <div class="card p-3 border">
                <h5 class="fw-bold text-sm mb-3 d-flex justify-content-between">
                  المحاكم
                  <button class="btn btn-link text-success p-0 m-0 border-0 text-xs text-decoration-none fw-bold"
                    onclick="document.getElementById('addCourtFormGroup').classList.toggle('d-none')">+ إضافة</button>
                </h5>
                <div id="addCourtFormGroup" class="mb-3 d-none">
                  <select id="newCourtCouncilSelect" class="form-select form-select-sm form-input-custom mb-2">
                    <!-- filled dynamically -->
                  </select>
                  <div class="d-flex gap-2">
                    <input type="text" id="newCourtInput" class="form-control form-control-sm form-input-custom"
                      placeholder="اسم المحكمة...">
                    <button onclick="addAdminSettingItem('court')" class="btn btn-success btn-sm fw-bold">حفظ</button>
                  </div>
                </div>
                <div id="courtsListContainer" class="overflow-y-auto max-height-200">
                  <!-- dynamic -->
                </div>
              </div>
            </div>

            <!-- Sections settings -->
            <div class="col-12 col-md-6">
              <div class="card p-3 border">
                <h5 class="fw-bold text-sm mb-3 d-flex justify-content-between">
                  الأقسام
                  <button class="btn btn-link text-success p-0 m-0 border-0 text-xs text-decoration-none fw-bold"
                    onclick="document.getElementById('addSectionFormGroup').classList.toggle('d-none')">+ إضافة</button>
                </h5>
                <div id="addSectionFormGroup" class="mb-3 d-none d-flex gap-2">
                  <input type="text" id="newSectionInput" class="form-control form-control-sm form-input-custom"
                    placeholder="اسم القسم...">
                  <button onclick="addAdminSettingItem('section')" class="btn btn-success btn-sm fw-bold">حفظ</button>
                </div>
                <div id="sectionsListContainer" class="overflow-y-auto max-height-200">
                  <!-- dynamic -->
                </div>
              </div>
            </div>

            <!-- Chambers settings -->
            <div class="col-12 col-md-6">
              <div class="card p-3 border">
                <h5 class="fw-bold text-sm mb-3 d-flex justify-content-between">
                  الغرف
                  <button class="btn btn-link text-success p-0 m-0 border-0 text-xs text-decoration-none fw-bold"
                    onclick="document.getElementById('addChamberFormGroup').classList.toggle('d-none')">+ إضافة</button>
                </h5>
                <div id="addChamberFormGroup" class="mb-3 d-none d-flex gap-2">
                  <input type="text" id="newChamberInput" class="form-control form-control-sm form-input-custom"
                    placeholder="اسم الغرفة...">
                  <button onclick="addAdminSettingItem('chamber')" class="btn btn-success btn-sm fw-bold">حفظ</button>
                </div>
                <div id="chambersListContainer" class="overflow-y-auto max-height-200">
                  <!-- dynamic -->
                </div>
              </div>
            </div>

            <!-- Announcement settings manager -->
            <div class="col-12 border-top pt-4">
              <h5 class="fw-bold text-sm mb-3 d-flex justify-content-between align-items-center">
                إدارة الإعلانات والتنبيهات
                <button class="btn btn-emerald btn-sm"
                  onclick="document.getElementById('addAnnouncementContainer').classList.toggle('d-none')">+ إعلان
                  جديد</button>
              </h5>

              <div id="addAnnouncementContainer" class="mb-4 p-3 rounded bg-light border d-none">
                <label class="form-label text-xs fw-bold text-muted mb-2">نص الإعلان العاجل</label>
                <textarea id="newAnnouncementText" class="form-control form-input-custom mb-3" rows="3"
                  placeholder="اكتب هنا نص الإعلان الذي سيظهر لجميع المستخدمين..."></textarea>
                <div class="d-flex justify-content-end gap-2">
                  <button onclick="document.getElementById('addAnnouncementContainer').classList.add('d-none')"
                    class="btn btn-light btn-sm text-muted">إلغاء</button>
                  <button onclick="postAnnouncement()" class="btn btn-emerald btn-sm">نشر الإعلان</button>
                </div>
              </div>

              <div id="adminAnnouncementsList">
                <!-- dynamic list -->
              </div>
            </div>

          </div>
        </div>
      </section>


      <!-- ==================== TAB: PROFILE VIEW ==================== -->
      <section id="profileView" class="tab-content-panel fade-in d-none">
        <div class="card premium-card p-5 max-w-lg mx-auto">
          <div class="d-flex flex-col flex-sm-row align-items-center gap-4 border-bottom pb-4 mb-4">
            <div class="bg-light border rounded-3 p-3 overflow-hidden d-flex align-items-center justify-content-center"
              style="width: 100px; height: 100px;">
              <img src="logo.png" alt="Logo" class="w-100 h-100 object-fit-contain">
            </div>
            <div class="text-center text-sm-right">
              <h3 class="fw-black text-dark mb-1">الملف الشخصي</h3>
              <p class="text-success mb-0 fw-bold">منظمة محامي البليدة</p>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-12 col-sm-6">
              <label class="form-label text-xs fw-bold text-muted">اللقب</label>
              <input type="text" id="profileLastName" class="form-control form-input-custom" disabled>
            </div>
            <div class="col-12 col-sm-6">
              <label class="form-label text-xs fw-bold text-muted">الاسم</label>
              <input type="text" id="profileFirstName" class="form-control form-input-custom" disabled>
            </div>
            <div class="col-12 col-sm-6">
              <label class="form-label text-xs fw-bold text-muted">البريد الإلكتروني</label>
              <input type="email" id="profileEmail" class="form-control form-input-custom" disabled>
            </div>
            <div class="col-12 col-sm-6">
              <label class="form-label text-xs fw-bold text-muted">رقم الهاتف</label>
              <input type="tel" id="profilePhone" class="form-control form-input-custom" disabled>
            </div>
            <div class="col-12 col-sm-6">
              <label class="form-label text-xs fw-bold text-muted">سنة أداء اليمين</label>
              <input type="text" id="profileOathDate" class="form-control form-input-custom" disabled>
            </div>
            <div class="col-12 col-sm-6">
              <label class="form-label text-xs fw-bold text-muted">الصفة</label>
              <input type="text" id="profileRole" class="form-control form-input-custom" disabled>
            </div>
          </div>

          <p class="text-xs text-muted italic mt-4 mb-0">* البيانات الشخصية يتم سحبها من نظام المنظمة ولا يمكن تعديلها
            حالياً.</p>
        </div>
      </section>

    </main>
  </div>


  <!-- ==================== MODAL: ADD / EDIT REQUESTS ==================== -->
  <div id="addCaseModal"
    class="modal fixed-top w-100 h-100 print-hidden align-items-center justify-content-center d-none"
    style="background-color: rgba(0,0,0,0.5); z-index: 1050;">
    <div class="premium-card p-4 w-100 m-3" style="max-width: 450px;">

      <!-- Close trigger -->
      <button type="button" class="btn-close float-start close-modal-trigger" aria-label="Close"></button>

      <h3 class="fw-bold text-dark mb-1" id="caseModalTitle">إضافة قضية جديدة</h3>
      <p class="text-muted text-xs mb-4" id="caseModalSubHeader">محكمة البليدة - قسم الجنح</p>

      <form id="caseForm">

        <!-- Colleague logic checkbox -->
        <div class="form-check form-switch mb-3" id="forColleagueCheckboxGroup">
          <input class="form-check-input" type="checkbox" role="switch" id="forColleagueCheckbox">
          <label class="form-check-label text-sm fw-bold text-muted" for="forColleagueCheckbox">إضافة لزميل آخر</label>
        </div>

        <!-- Colleague fields -->
        <div id="colleagueFieldsContainer" class="p-3 bg-light rounded-3 border mb-3 d-none">
          <div class="row g-2 mb-2">
            <div class="col-6">
              <label class="form-label text-xs text-muted">لقب الزميل</label>
              <input type="text" id="colleagueLastName" class="form-control form-control-sm form-input-custom">
            </div>
            <div class="col-6">
              <label class="form-label text-xs text-muted">اسم الزميل</label>
              <input type="text" id="colleagueFirstName" class="form-control form-control-sm form-input-custom">
            </div>
          </div>

          <div>
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label text-xs text-muted mb-0">سنة اليمين للزميل</label>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="colleagueIsSyndicateMember">
                <label class="form-check-label text-xs fw-bold text-success" for="colleagueIsSyndicateMember">عضو
                  نقابة</label>
              </div>
            </div>
            <input type="number" min="1950" max="2026" id="colleagueOathDate"
              class="form-control form-control-sm form-input-custom" placeholder="مثال: 2005">
          </div>
        </div>

        <!-- Case inputs -->
        <div class="mb-3">
          <label class="form-label text-sm fw-bold text-muted mb-1">رقم القضية</label>
          <input type="text" id="caseNumberInput" class="form-control form-input-custom"
            placeholder="السنة-رقم الملف (مثال: 26-1234)" required>
        </div>

        <div class="mb-3">
          <label class="form-label text-sm fw-bold text-muted mb-1">الأطراف (اسم المتهم)</label>
          <input type="text" id="casePartiesInput" class="form-control form-input-custom" placeholder="أدخل اسم المتهم"
            required>
        </div>

        <!-- Purpose selection -->
        <div class="mb-4">
          <label class="form-label text-sm fw-bold text-muted mb-2">الغرض</label>
          <div class="row g-2">
            <div class="col-6">
              <button type="button"
                class="btn btn-outline-warning w-100 py-2 fw-bold text-sm purpose-select-btn active bg-warning-subtle border-warning text-warning"
                data-purpose="delay">تأجيل</button>
            </div>
            <div class="col-6">
              <button type="button" class="btn btn-light w-100 py-2 fw-bold text-sm purpose-select-btn text-muted"
                data-purpose="advance">تسبيق</button>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <button type="submit" class="btn btn-emerald w-100 py-3 rounded-3 mb-2 fw-bold shadow-sm">إضافة للقائمة</button>
        <button type="button" class="btn btn-light w-100 py-2 rounded-3 text-muted close-modal-trigger">إغلاق</button>

      </form>
    </div>
  </div>


  <!-- ==================== MODAL: CUSTOM CONFIRMATION DIALOG ==================== -->
  <div id="confirmModal"
    class="modal fixed-top w-100 h-100 print-hidden align-items-center justify-content-center d-none"
    style="background-color: rgba(0,0,0,0.6); z-index: 1100;">
    <div class="premium-card p-4 w-100 m-3 text-center" style="max-width: 380px;">
      <div
        class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4"
        style="width: 70px; height: 70px;">
        <i data-lucide="alert-circle" class="w-10 h-10"></i>
      </div>
      <h4 class="fw-bold text-dark mb-2">تأكيد الإجراء</h4>
      <p class="text-muted text-sm mb-4" id="confirmModalMessage">هل أنت متأكد؟</p>

      <div class="d-flex gap-2">
        <button type="button" id="confirmCancelBtn"
          class="btn btn-light flex-grow-1 py-3 rounded-3 text-muted">إلغاء</button>
        <button type="button" id="confirmModalBtn"
          class="btn btn-danger flex-grow-1 py-3 rounded-3 fw-bold">تأكيد</button>
      </div>
    </div>
  </div>


  <!-- ==================== MODAL: ID CARD IMAGE VIEWER ==================== -->
  <div id="idCardViewerModal"
    class="modal fixed-top w-100 h-100 print-hidden align-items-center justify-content-center d-none"
    style="background-color: rgba(0,0,0,0.8); z-index: 1060;" onclick="closeIDCardViewer()">
    <div class="premium-card p-0 w-100 m-3 overflow-hidden shadow-2xl" style="max-width: 650px;"
      onclick="event.stopPropagation()">
      <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between bg-white">
        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2 text-dark"><i data-lucide="qr-code"
            class="text-success"></i> بطاقة المحامي</h5>
        <button type="button" class="btn-close" onclick="closeIDCardViewer()"></button>
      </div>
      <div class="p-3 bg-light text-center min-vh-50 d-flex align-items-center justify-content-center">
        <img id="idCardImage" src="" alt="ID Card" class="img-fluid rounded shadow" style="max-height: 70vh;">
      </div>
    </div>
  </div>


  <!-- ==================== MODAL: ADD / EDIT USER ACCOUNT (Admin) ==================== -->
  <div id="userModal" class="modal fixed-top w-100 h-100 print-hidden align-items-center justify-content-center d-none"
    style="background-color: rgba(0,0,0,0.55); z-index: 1070;">
    <div class="premium-card p-4 w-100 m-3 overflow-y-auto" style="max-width: 520px; max-height: 95vh;">

      <!-- Modal Header -->
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
          <h4 class="fw-bold text-dark mb-0" id="userModalTitle">إضافة حساب جديد</h4>
          <p class="text-muted text-xs mb-0 mt-1">منظمة محامي البليدة</p>
        </div>
        <button type="button" class="btn-close" onclick="closeUserModal()"></button>
      </div>

      <!-- User Form -->
      <form id="userForm" autocomplete="off">
        <input type="hidden" id="userIdInput">

        <!-- Name Row -->
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label text-xs fw-bold text-muted mb-1">اللقب <span class="text-danger">*</span></label>
            <input type="text" id="userLastNameInput" class="form-control form-input-custom" placeholder="مثال: بن علي"
              required>
          </div>
          <div class="col-6">
            <label class="form-label text-xs fw-bold text-muted mb-1">الاسم <span class="text-danger">*</span></label>
            <input type="text" id="userFirstNameInput" class="form-control form-input-custom" placeholder="مثال: محمد"
              required>
          </div>
        </div>

        <!-- Email -->
        <div class="mb-3">
          <label class="form-label text-xs fw-bold text-muted mb-1">البريد الإلكتروني</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i data-lucide="mail"
                class="w-4 h-4 text-muted"></i></span>
            <input type="email" id="userEmailInput" class="form-control form-input-custom border-start-0"
              placeholder="example@email.com">
          </div>
        </div>

        <!-- Phone -->
        <div class="mb-3">
          <label class="form-label text-xs fw-bold text-muted mb-1">رقم الهاتف</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i data-lucide="phone"
                class="w-4 h-4 text-muted"></i></span>
            <input type="tel" id="userPhoneInput" class="form-control form-input-custom border-start-0"
              placeholder="0551234567">
          </div>
        </div>

        <!-- Oath Date + Syndicate -->
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label text-xs fw-bold text-muted mb-0">سنة أداء اليمين <span class="text-danger"
                id="userOathDateRequired">*</span></label>
            <div class="form-check mb-0">
              <input class="form-check-input" type="checkbox" id="userIsSyndicateMember">
              <label class="form-check-label text-xs fw-bold text-success" for="userIsSyndicateMember">عضو نقابة</label>
            </div>
          </div>
          <input type="number" min="1950" max="2030" id="userOathDateInput" class="form-control form-input-custom"
            placeholder="مثال: 2005">
        </div>

        <!-- Role -->
        <div class="mb-3">
          <label class="form-label text-xs fw-bold text-muted mb-1">الصفة / الدور <span
              class="text-danger">*</span></label>
          <select id="userRoleInput" class="form-select form-input-custom">
            <option value="lawyer">محامي</option>
            <option value="delegate">مندوب</option>
            <option value="admin">مسؤول</option>
          </select>
        </div>

        <!-- Status -->
        <div class="mb-3">
          <label class="form-label text-xs fw-bold text-muted mb-1">الحالة <span class="text-danger">*</span></label>
          <select id="userStatusInput" class="form-select form-input-custom">
            <option value="approved">نشط</option>
            <option value="pending">قيد المراجعة</option>
            <option value="rejected">مرفوض</option>
          </select>
        </div>

        <!-- Password -->
        <div class="mb-4">
          <label class="form-label text-xs fw-bold text-muted mb-1">
            كلمة السر
            <span class="text-danger" id="userPasswordRequired">*</span>
            <small class="text-muted fw-normal" id="userPasswordHint"></small>
          </label>
          <input type="password" id="userPasswordInput" class="form-control form-input-custom"
            placeholder="كلمة السر للحساب الجديد">
        </div>

        <!-- Error Alert -->
        <div id="userModalError" class="alert alert-danger text-sm d-none mb-3" role="alert"></div>

        <!-- Actions -->
        <button type="submit" id="userModalSubmitBtn"
          class="btn btn-emerald w-100 py-3 rounded-3 fw-bold shadow-sm mb-2">
          <i data-lucide="save" class="w-5 h-5 me-1"></i>
          حفظ الحساب
        </button>
        <button type="button" onclick="closeUserModal()"
          class="btn btn-light w-100 py-2 rounded-3 text-muted">إلغاء</button>

      </form>
    </div>
  </div>


  <!-- ==================== GLOBAL DYNAMIC TOASTS ==================== -->
  <div id="toastNotification" class="toast-toast d-none position-fixed bottom-4 start-50 translate-middle-x"
    style="z-index: 2000;">
    <div class="d-flex align-items-center gap-2">
      <div id="toastIcon"></div>
      <span class="text-sm fw-bold" id="toastMessageText">العملية ناجحة</span>
    </div>
  </div>


  <!-- ==================== PDF EXPORT CAPTURE TEMPLATE (HIDDEN FOR SCREEN) ==================== -->
  <div id="pdfPrintTemplate" class="d-none bg-white p-4"
    style="width: 1100px; color: #000000; direction: rtl; text-align: right;">

    <!-- PDF Header content -->
    <div class="border-bottom border-3 border-dark pb-4 mb-4">
      <div class="row align-items-start">
        <div class="col-8">
          <h2 class="fw-black mb-1">منظمة محامي البليدة</h2>
          <h5 class="fw-bold mb-1">الجهة القضائية: <span id="pdfJurisdictionName">محكمة البليدة</span></h5>
          <h6 class="fw-bold mb-3">القسم / الغرفة: <span id="pdfJurisdictionSubEntity">قسم الجنح</span></h6>
          <p class="text-sm fw-bold text-muted mt-2">تاريخ الجلسة: <span id="pdfSessionDate">15-06-2026</span></p>
        </div>

        <div class="col-4 d-flex flex-column align-items-end gap-2">
          <img src="logo.png" alt="Logo" style="width: 80px;">
          <div class="d-flex flex-column align-items-center gap-1 mt-1">
            <div id="pdfQrCodeContainer"></div>
            <small style="font-size: 8px; font-weight: bold; color: #555555;">تحقق من صحة القائمة</small>
          </div>
        </div>
      </div>

      <div class="text-center mt-3">
        <h2 class="fw-black text-decoration-underline" style="font-size: 26px;">قائمة التسبيقات والتأجيلات</h2>
      </div>
    </div>

    <!-- PDF table content -->
    <table class="table text-right border" style="border: 2px solid #000000 !important; width: 100%;">
      <thead>
        <tr class="bg-light">
          <th style="border: 1px solid #000000 !important; padding: 10px; width: 8%;">الترتيب</th>
          <th style="border: 1px solid #000000 !important; padding: 10px; width: 25%;">الأستاذ</th>
          <th style="border: 1px solid #000000 !important; padding: 10px; width: 27%;">الأطراف</th>
          <th style="border: 1px solid #000000 !important; padding: 10px; width: 15%;">رقم القضية</th>
          <th style="border: 1px solid #000000 !important; padding: 10px; width: 15%;">سنة اليمين</th>
          <th style="border: 1px solid #000000 !important; padding: 10px; width: 10%;">الغرض</th>
        </tr>
      </thead>
      <tbody id="pdfPrintTableBody">
        <!-- dynamic rows -->
      </tbody>
    </table>
  </div>


  <!-- ==================== EXTERNAL JS LIBRARIES ==================== -->

  <!-- Pre-inject state variables from session -->
  <script>
    window.preinjectedUser = <?php echo $user_json; ?>;
  </script>

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- Flatpickr (Calendar component replacement) -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>

  <!-- html2pdf.js for exporting lists -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

  <!-- QRCode.js for PDF list verifications -->
  <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>

  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Local logic controllers -->
  <script src="script.js"></script>
  <script>
    if (window.preinjectedUser) {
      currentUser = window.preinjectedUser;
    }
  </script>
</body>

</html>