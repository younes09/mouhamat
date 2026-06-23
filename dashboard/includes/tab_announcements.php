<!-- ==================== TAB: ANNOUNCEMENTS VIEW ==================== -->
<section id="announcementsView" class="tab-content-panel fade-in d-none">

  <!-- Premium Header Banner -->
  <div class="card premium-card overflow-hidden border-0 shadow-lg mb-4 position-relative"
    style="background: linear-gradient(135deg, var(--emerald-800) 0%, var(--emerald-600) 100%); min-height: 180px;">
    <!-- Abstract absolute shapes for rich premium design -->
    <div class="position-absolute top-0 end-0 p-5 opacity-10 text-white" style="transform: scale(3.5) translate(0%, 0%); pointer-events: none; direction: ltr;">
      <i data-lucide="bell"></i>
    </div>
    
    <div class="card-body p-4 p-md-5 d-flex align-items-center position-relative z-1 h-100">
      <div class="d-flex align-items-center gap-4 flex-wrap flex-md-nowrap">
        <div class="bg-white bg-opacity-20 rounded-4 d-inline-flex p-3 backdrop-blur shadow-sm border border-white border-opacity-20 shrink-0">
          <i data-lucide="megaphone" class="w-10 h-10 text-white"></i>
        </div>
        <div class="text-right text-white">
          <h2 class="fw-black mb-1 text-white text-3xl">لوحة الإعلانات الرسمية</h2>
          <p class="text-success-emphasis mb-0 opacity-90 fw-semibold text-sm">
            آخر القرارات والمستجدات والتنبيهات الصادرة عن مندوبية نقابة المحامين.
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Search & Stats section -->
  <div class="row g-3 mb-4">
    <div class="col-12 col-md-8">
      <div class="position-relative">
        <input type="text" id="announcementSearchInput"
          class="form-control form-input-custom ps-5 py-3 text-sm fw-bold shadow-sm"
          placeholder="البحث في نص الإعلان أو الكلمات المفتاحية...">
        <span class="position-absolute start-0 top-50 translate-middle-y ps-4 text-muted">
          <i data-lucide="search" class="w-5 h-5"></i>
        </span>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="bg-white p-3 rounded-3 border d-flex align-items-center justify-content-between shadow-sm h-100">
        <span class="text-muted text-xs fw-bold">إجمالي الإعلانات النشطة:</span>
        <span id="activeAnnouncementsCount" class="badge bg-success py-2 px-3 fw-bold text-sm">0</span>
      </div>
    </div>
  </div>

  <!-- Grid of Announcements -->
  <div id="announcementsFullGrid" class="row g-3">
    <!-- Loaded dynamically -->
  </div>

</section>
