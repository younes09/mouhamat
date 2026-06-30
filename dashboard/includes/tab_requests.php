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
            <span class="position-absolute start-0 top-50 translate-middle-y ps-3 text-muted"><i data-lucide="search"
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
              class="btn btn-dark delegate-admin-only d-none d-flex align-items-center gap-2 scale-active"
              title="نقل القائمة للأرشيف">
              <i data-lucide="history" class="w-5 h-5"></i>
              أرشفة
            </button>
            <button onclick="clearCurrentList()"
              class="btn btn-danger delegate-admin-only d-none d-flex align-items-center gap-2 scale-active"
              title="مسح القائمة الحالية">
              <i data-lucide="trash-2" class="w-5 h-5"></i>
              مسح القائمة
            </button>

            <!-- Export formats -->
            <button onclick="openPrintPage()" class="btn btn-dark d-flex align-items-center gap-2 scale-active"
              title="تنزيل كملف PDF">
              <i data-lucide="download" class="w-5 h-5"></i>
              تحميل PDF
            </button>

            <?php if ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'delegate'): ?>
              <button onclick="openPrintPage()" class="btn btn-light border d-flex align-items-center gap-2 scale-active"
                title="طباعة">
                <i data-lucide="printer" class="w-5 h-5"></i>
                طباعة
              </button>
            <?php endif; ?>
          </div>
        </div>

        <!-- Desktop requests table -->
        <div class="card premium-card overflow-hidden shadow-sm d-none d-md-block print-hidden">
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
          <img src="../assets/img/logo.png" alt="Logo" class="opacity-25 grayscale" style="width: 50px;">
          <p class="mb-0 text-xs">يتم ترتيب القائمة تلقائياً حسب تاريخ أداء اليمين (الأقدمية)</p>
          <p class="mb-0 fw-bold text-xs">جميع الحقوق محفوظة لمنظمة محامي البليدة © 2026</p>
        </div>

      </section>
