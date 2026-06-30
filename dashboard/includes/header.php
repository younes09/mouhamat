    <!-- HEADER -->
    <header class="bg-white border-bottom sticky-top z-3 print-hidden">
      <div class="max-w-5xl mx-auto px-4 py-3 d-flex align-items-center justify-content-between">

        <!-- Syndicate Title -->
        <div class="d-flex align-items-center gap-3">
          <div
            class="border rounded-3 shadow-sm overflow-hidden d-flex align-items-center justify-content-center bg-white"
            style="width: 55px; height: 55px;">
            <img src="../assets/img/logo.png" alt="Logo" class="w-100 h-100 object-fit-contain p-1">
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

          <!-- PWA Install Button -->
          <button type="button" id="installAppBtn" class="btn btn-light p-2 rounded-3 border-0 text-success d-none scale-active"
            title="تثبيت التطبيق على الهاتف">
            <i data-lucide="download" class="w-5 h-5"></i>
          </button>

          <!-- Theme Toggle Button -->
          <button type="button" id="darkToggle" class="btn btn-light p-2 rounded-3 border-0 text-muted"
            title="الوضع الليلي">
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
