<!-- ==================== TAB: CALENDAR VIEW ==================== -->
<section id="calendarView" class="tab-content-panel fade-in d-none">
  <div class="row g-4">
    <!-- Right Column: Calendar Picker -->
    <div class="col-12 col-lg-5">
      <div class="premium-card p-4 text-center h-100 ">
        <h4 class="fw-bold mb-3 d-flex align-items-center justify-content-center gap-2">
          <i data-lucide="calendar" class="text-success"></i>
          التقويم القضائي
        </h4>
        <p class="text-muted text-sm mb-4">اختر التاريخ من التقويم لتصفح الطلبات المرتبطة بتلك الجلسة</p>

        <!-- centre the calendar in the middle of the page -->
        <div class="d-flex justify-content-center align-items-center my-3">
          <div id="calendarPicker"></div>
        </div>

        <div class="mt-4 pt-3 border-top text-start">
          <h6 class="fw-bold text-xs text-muted mb-3">دليل الألوان:</h6>
          <div class="d-flex flex-wrap gap-3 text-xs">
            <span class="d-flex align-items-center gap-2 text-muted">
              <span class="rounded-circle bg-success d-inline-block" style="width: 10px; height: 10px;"></span>
              الأيام التي تحتوي على طلبات مسجلة
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Left Column: Selected Date Details & Request Preview -->
    <div class="col-12 col-lg-7">
      <div class="premium-card p-4 h-100 d-flex flex-column">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3 flex-wrap gap-2">
          <div>
            <h5 class="fw-bold text-dark mb-1" id="calendarSelectedDateLong">جلسة ...</h5>
            <p class="text-muted text-xs mb-0" id="calendarSelectedDateShort">يرجى تحديد تاريخ</p>
          </div>
          <!-- Statistics Badges for this day -->
          <div class="d-flex gap-2" id="calendarDayStats">
            <span
              class="badge bg-success-subtle text-success-emphasis border border-success border-opacity-25 rounded-pill px-2.5 py-1.5 text-xs d-flex align-items-center gap-1">
              <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
              <span id="calendarDayTotalCount">0</span> طلبات
            </span>
          </div>
        </div>

        <!-- Content: Requests list for this date -->
        <div class="flex-grow-1 overflow-auto pe-1" id="calendarRequestsContainer"
          style="max-height: 400px; min-height: 250px;">
          <!-- Will be populated dynamically -->
        </div>

        <!-- Footer actions -->
        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-3 flex-wrap gap-2">
          <button onclick="goToSelectedDateRequests()" class="btn btn-emerald btn-sm d-flex align-items-center gap-2">
            <i data-lucide="external-link" class="w-4 h-4"></i>
            الذهاب إلى جدول الجلسة الكامل
          </button>
          <button id="calendarAddNewCaseBtn" onclick="openAddCaseModalFromCalendar()"
            class="btn btn-outline-success btn-sm d-flex align-items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            إضافة قضية جديدة
          </button>
        </div>
      </div>
    </div>
  </div>
</section>