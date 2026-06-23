<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: dashboard/");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>دخول | منظمة محامي البليدة</title>
  <link rel="icon" type="image/x-icon" href="assets/img/logo.png">
  
  <!-- SEO Meta Tags -->
  <meta name="description" content="نظام إدارة الجلسات الرقمي لمنظمة محامي البليدة. تسجيل استخراج قوائم التسبيقات والتأجيلات وتسهيل التنسيق القضائي.">
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#059669">
  
  <!-- Apple Touch Support -->
  <link rel="apple-touch-icon" href="https://storage.googleapis.com/static.ai.studio/build/Rachid.Mca.Chido%40gmail.com/Rachid.Mca.Chido%40gmail.com_1775555917000_0.png">
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  
  <!-- Bootstrap 5 RTL -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
  
  <!-- Custom Styles -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <!-- ==================== LOGIN / REGISTRATION CONTAINER ==================== -->
  <div id="loginContainer" class="min-vh-screen d-flex align-items-center justify-content-center p-4 fade-in">
    <div class="premium-card p-5 w-100" style="max-width: 450px;">
      
      <div class="text-center mb-4">
        <div class="mx-auto mb-3 border rounded-4 shadow-sm overflow-hidden d-flex align-items-center justify-content-center bg-white" style="width: 120px; height: 120px;">
          <img src="assets/img/logo.png" alt="Logo" class="w-100 h-100 object-fit-contain p-2">
        </div>
        <h2 class="fw-black text-dark mb-1">منظمة محامي البليدة</h2>
        <p class="text-success fw-bold text-sm">نظام إدارة الجلسات الرقمي</p>
      </div>

      <!-- Tabs for Switching between Login & Register -->
      <div class="d-flex justify-content-center gap-3 mb-4 border-bottom pb-2">
        <button type="button" id="toggleLoginTab" class="btn btn-link text-decoration-none fw-black pb-2 border-bottom border-3 border-success text-success" style="font-size: 15px;">تسجيل الدخول</button>
        <button type="button" id="toggleRegisterTab" class="btn btn-link text-decoration-none fw-bold pb-2 text-muted" style="font-size: 15px;">إنشاء حساب جديد</button>
      </div>

      <!-- Login Form -->
      <form id="loginForm" class="needs-validation" novalidate>

        <!-- Email Input -->
        <div class="mb-3">
          <label class="form-label text-sm fw-bold text-muted mb-1">البريد الإلكتروني</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i data-lucide="mail" class="w-5 h-5 text-muted"></i></span>
            <input type="email" name="email" class="form-control form-input-custom border-start-0 ps-3" placeholder="example@email.com" required autocomplete="email">
          </div>          
        </div>

        <!-- Password -->
        <div class="mb-4">
          <label class="form-label text-sm fw-bold text-muted mb-1">كلمة السر</label>
          <input type="password" name="password" class="form-control form-input-custom" placeholder="أدخل كلمة السر" required autocomplete="current-password">
        </div>

        <!-- Error Panel -->
        <div id="loginErrorAlert" class="alert alert-danger text-xs fw-bold p-3 rounded-3 d-none mb-4" role="alert">
          خطأ
        </div>

        <!-- Submit Buttons -->
        <button type="submit" class="btn btn-emerald w-100 py-3 rounded-3 shadow-sm scale-active mb-3">دخول</button>
        
        <button type="button" id="guestLoginBtn" class="btn btn-light w-100 py-2 rounded-3 text-muted scale-active">الاطلاع على القائمة فقط</button>

        <!-- Install PWA Button -->
        <button type="button" id="installAppBtn" class="btn btn-dark w-100 py-3 rounded-3 mt-4 scale-active d-none d-flex align-items-center justify-content-center gap-2">
          <i data-lucide="plus"></i>
          تثبيت التطبيق على الهاتف (Android)
        </button>

        <!-- IFrame Warning -->
        <div id="iframeInstallMsg" class="alert alert-warning text-xs p-3 rounded-3 mt-4 d-none">
          <p class="mb-2 fw-bold text-dark"><i data-lucide="alert-circle" class="w-4 h-4 inline me-1"></i>يجب فتح التطبيق في المتصفح لتتمكن من تثبيته</p>
          <button type="button" onclick="window.open(window.location.href, '_blank')" class="btn btn-warning w-100 btn-sm font-weight-bold">فتح في نافذة جديدة للتثبيت</button>
        </div>

      </form>

      <!-- Register Form -->
      <form id="registerForm" class="needs-validation d-none" novalidate enctype="multipart/form-data">

        <!-- Name Inputs -->
        <div class="mb-3">
          <label class="form-label text-sm fw-bold text-muted mb-1">اللقب</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i data-lucide="user" class="w-5 h-5 text-muted"></i></span>
            <input type="text" name="lastName" class="form-control form-input-custom border-start-0 ps-2" placeholder="بن علي" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label text-sm fw-bold text-muted mb-1">الاسم</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i data-lucide="user" class="w-5 h-5 text-muted"></i></span>
            <input type="text" name="firstName" class="form-control form-input-custom border-start-0 ps-2" placeholder="محمد" required>
          </div>
        </div>

        <!-- Email -->
        <div class="mb-3">
          <label class="form-label text-sm fw-bold text-muted mb-1">البريد الإلكتروني <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i data-lucide="mail" class="w-5 h-5 text-muted"></i></span>
            <input type="email" name="email" class="form-control form-input-custom border-start-0 ps-3" placeholder="example@email.com" required autocomplete="email">
          </div>
        </div>

        <!-- Phone -->
        <div class="mb-3">
          <label class="form-label text-sm fw-bold text-muted mb-1">رقم الهاتف <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i data-lucide="phone" class="w-5 h-5 text-muted"></i></span>
            <input type="tel" name="phone" class="form-control form-input-custom border-start-0 ps-3" placeholder="0551234567" required>
          </div>
        </div>

        <!-- Oath Date / Seniority -->
        <div class="mb-3" id="registerOathGroup">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label text-sm fw-bold text-muted mb-0">سنة أداء اليمين</label>
            <div class="form-check form-check-inline m-0">
              <input class="form-check-input" type="checkbox" name="isSyndicateMember" id="isSyndicateRegister">
              <label class="form-check-label text-xs fw-bold text-success" for="isSyndicateRegister">عضو نقابة</label>
            </div>
          </div>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i data-lucide="calendar" class="w-5 h-5 text-muted"></i></span>
            <input type="number" name="oathDate" id="oathDateRegisterInput" min="1950" max="2030" class="form-control form-input-custom border-start-0 ps-3" placeholder="مثال: 1995" required>
          </div>
          <p class="text-xs text-muted mt-1 mb-0">
            <i data-lucide="alert-circle" class="w-3.5 h-3.5 inline-block text-success me-1"></i>
            تستخدم السنة لتحديد الأولوية التلقائية في القائمة.
          </p>
        </div>

        <!-- Password -->
        <div class="mb-3">
          <label class="form-label text-sm fw-bold text-muted mb-1">كلمة السر</label>
          <input type="password" name="password" class="form-control form-input-custom" placeholder="اختر كلمة السر" required autocomplete="new-password">
        </div>

        <!-- ID Card Upload -->
        <div class="mb-4" id="registerIdCardGroup">
          <label class="form-label text-sm fw-bold text-muted mb-1 d-flex align-items-center gap-2">
            <i data-lucide="qr-code" class="text-success"></i>
            رفع بطاقة المحامي (مطلوب للتسجيل)
          </label>
          <input type="file" name="idCard" accept="image/*" class="form-control form-input-custom text-xs" required>
        </div>

        <!-- Error Panel -->
        <div id="registerErrorAlert" class="alert alert-danger text-xs fw-bold p-3 rounded-3 d-none mb-4" role="alert">
          خطأ
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-emerald w-100 py-3 rounded-3 shadow-sm scale-active">إنشاء حساب جديد</button>
      </form>
    </div>
  </div>

  <!-- ==================== GLOBAL DYNAMIC TOASTS ==================== -->
  <div id="toastNotification" class="toast-toast d-none position-fixed bottom-4 start-50 translate-middle-x" style="z-index: 2000;">
    <div class="d-flex align-items-center gap-2">
      <div id="toastIcon"></div>
      <span class="text-sm fw-bold" id="toastMessageText">العملية ناجحة</span>
    </div>
  </div>

  <!-- ==================== EXTERNAL JS LIBRARIES ==================== -->
  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>
  
  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Local logic controllers -->
  <script src="js/auth.js"></script>
</body>
</html>
