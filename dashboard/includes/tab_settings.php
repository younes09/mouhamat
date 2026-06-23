<!-- ==================== TAB: SETTINGS VIEW (Admin) ==================== -->
<section id="settingsView" class="tab-content-panel fade-in d-none">
  
  <!-- Header & Toggle State Card -->
  <div class="card premium-card bg-emerald-subtle border-success p-4 mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-white text-success rounded-4 shadow-sm border">
          <i data-lucide="settings" class="w-6 h-6"></i>
        </div>
        <div>
          <h4 class="fw-black text-dark mb-1">لوحة إعدادات النظام</h4>
          <p class="text-muted text-xs mb-0">تحديث قيود تسجيل الجلسات، إدارة مجالس القضاء، المحاكم والإعلانات العامة.</p>
        </div>
      </div>
      <div class="d-flex align-items-center gap-3 bg-white p-3 rounded-4 border shadow-sm">
        <span id="settingListStateText" class="fw-black text-sm text-success">القائمة مفتوحة</span>
        <button id="settingToggleListBtn" onclick="updateAdminListStatus(!systemSettings.isListOpen)"
          class="btn btn-danger btn-sm fw-bold px-4 py-2 rounded-3 scale-active">
          غلق القائمة
        </button>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Dynamic Constraints Settings -->
    <div class="col-12">
      <div class="card premium-card p-4 border shadow-sm">
        <h5 class="fw-bold text-base mb-3 d-flex align-items-center gap-2 text-success border-bottom pb-2">
          <i data-lucide="clock" class="w-5 h-5"></i>
          أوقات وقيود تسجيل القضايا للمحامين
        </h5>
        <form id="constraintsForm" onsubmit="saveAdminConstraints(event)">
          <div class="row g-4">
            <!-- Time constraints -->
            <div class="col-12 col-md-5">
              <label class="form-label text-xs fw-black text-muted mb-2">أوقات العمل اليومية (تفتح - تغلق)</label>
              <div class="d-flex gap-2 align-items-center">
                <div class="input-group">
                  <span class="input-group-text bg-light text-muted border-end-0"><i data-lucide="play" class="w-4 h-4"></i></span>
                  <input type="time" id="constraintStartTime" class="form-control form-input-custom border-start-0 text-sm fw-bold" required>
                </div>
                <span class="text-muted text-xs fw-bold px-1">إلى</span>
                <div class="input-group">
                  <span class="input-group-text bg-light text-muted border-end-0"><i data-lucide="square" class="w-4 h-4"></i></span>
                  <input type="time" id="constraintEndTime" class="form-control form-input-custom border-start-0 text-sm fw-bold" required>
                </div>
              </div>
            </div>
            
            <!-- Close Days -->
            <div class="col-12 col-md-7">
              <label class="form-label text-xs fw-black text-muted mb-2">أيام الإغلاق الأسبوعية (الويكند المعطلة)</label>
              <div class="d-flex flex-wrap gap-2 pt-1">
                <div class="weekday-pill">
                  <input class="btn-check" type="checkbox" id="day_0" value="0">
                  <label class="btn btn-outline-success btn-sm fw-bold px-3 py-2 rounded-3 text-xs" for="day_0">الأحد</label>
                </div>
                <div class="weekday-pill">
                  <input class="btn-check" type="checkbox" id="day_1" value="1">
                  <label class="btn btn-outline-success btn-sm fw-bold px-3 py-2 rounded-3 text-xs" for="day_1">الإثنين</label>
                </div>
                <div class="weekday-pill">
                  <input class="btn-check" type="checkbox" id="day_2" value="2">
                  <label class="btn btn-outline-success btn-sm fw-bold px-3 py-2 rounded-3 text-xs" for="day_2">الثلاثاء</label>
                </div>
                <div class="weekday-pill">
                  <input class="btn-check" type="checkbox" id="day_3" value="3">
                  <label class="btn btn-outline-success btn-sm fw-bold px-3 py-2 rounded-3 text-xs" for="day_3">الأربعاء</label>
                </div>
                <div class="weekday-pill">
                  <input class="btn-check" type="checkbox" id="day_4" value="4">
                  <label class="btn btn-outline-success btn-sm fw-bold px-3 py-2 rounded-3 text-xs" for="day_4">الخميس</label>
                </div>
                <div class="weekday-pill">
                  <input class="btn-check" type="checkbox" id="day_5" value="5">
                  <label class="btn btn-outline-danger btn-sm fw-bold px-3 py-2 rounded-3 text-xs" for="day_5">الجمعة</label>
                </div>
                <div class="weekday-pill">
                  <input class="btn-check" type="checkbox" id="day_6" value="6">
                  <label class="btn btn-outline-danger btn-sm fw-bold px-3 py-2 rounded-3 text-xs" for="day_6">السبت</label>
                </div>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-end mt-4 pt-3 border-top">
            <button type="submit" class="btn btn-emerald btn-sm fw-bold px-4 py-2 rounded-3 scale-active">
              <i data-lucide="save" class="w-4 h-4 inline me-1"></i> حفظ القيود
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Councils settings -->
    <div class="col-12 col-md-6">
      <div class="card premium-card p-4 border shadow-sm h-100 d-flex flex-column justify-content-between">
        <div>
          <h5 class="fw-bold text-sm mb-3 d-flex justify-content-between align-items-center border-bottom pb-2">
            <span class="d-flex align-items-center gap-2">
              <i data-lucide="award" class="text-success w-4 h-4"></i>
              مجالس القضاء
            </span>
            <button class="btn btn-emerald btn-xs px-2.5 py-1 text-xs rounded-2"
              onclick="document.getElementById('addCouncilFormGroup').classList.toggle('d-none')">
              <i data-lucide="plus" class="w-3.5 h-3.5 inline"></i> إضافة
            </button>
          </h5>
          <div id="addCouncilFormGroup" class="mb-3 d-none d-flex gap-2 bg-light-custom p-2.5 rounded-3 border">
            <input type="text" id="newCouncilInput" class="form-control form-control-sm form-input-custom"
              placeholder="اسم المجلس القضائي الجديد...">
            <button onclick="addAdminSettingItem('council')" class="btn btn-success btn-sm fw-bold px-3">حفظ</button>
          </div>
          <div id="councilsListContainer" class="overflow-y-auto max-height-200 pe-1">
            <!-- dynamic -->
          </div>
        </div>
      </div>
    </div>

    <!-- Courts settings -->
    <div class="col-12 col-md-6">
      <div class="card premium-card p-4 border shadow-sm h-100 d-flex flex-column justify-content-between">
        <div>
          <h5 class="fw-bold text-sm mb-3 d-flex justify-content-between align-items-center border-bottom pb-2">
            <span class="d-flex align-items-center gap-2">
              <i data-lucide="landmark" class="text-success w-4 h-4"></i>
              المحاكم الابتدائية
            </span>
            <button class="btn btn-emerald btn-xs px-2.5 py-1 text-xs rounded-2"
              onclick="document.getElementById('addCourtFormGroup').classList.toggle('d-none')">
              <i data-lucide="plus" class="w-3.5 h-3.5 inline"></i> إضافة
            </button>
          </h5>
          <div id="addCourtFormGroup" class="mb-3 d-none bg-light-custom p-3 rounded-3 border">
            <label class="form-label text-xs fw-bold text-muted mb-1">الربط بمجلس القضاء</label>
            <select id="newCourtCouncilSelect" class="form-select form-select-sm form-input-custom mb-2">
              <!-- filled dynamically -->
            </select>
            <div class="d-flex gap-2">
              <input type="text" id="newCourtInput" class="form-control form-control-sm form-input-custom"
                placeholder="اسم المحكمة الجديدة...">
              <button onclick="addAdminSettingItem('court')" class="btn btn-success btn-sm fw-bold px-3">حفظ</button>
            </div>
          </div>
          <div id="courtsListContainer" class="overflow-y-auto max-height-200 pe-1">
            <!-- dynamic -->
          </div>
        </div>
      </div>
    </div>

    <!-- Sections settings -->
    <div class="col-12 col-md-6">
      <div class="card premium-card p-4 border shadow-sm h-100 d-flex flex-column justify-content-between">
        <div>
          <h5 class="fw-bold text-sm mb-3 d-flex justify-content-between align-items-center border-bottom pb-2">
            <span class="d-flex align-items-center gap-2">
              <i data-lucide="folder" class="text-success w-4 h-4"></i>
              الأقسام القضائية (المحكمة)
            </span>
            <button class="btn btn-emerald btn-xs px-2.5 py-1 text-xs rounded-2"
              onclick="document.getElementById('addSectionFormGroup').classList.toggle('d-none')">
              <i data-lucide="plus" class="w-3.5 h-3.5 inline"></i> إضافة
            </button>
          </h5>
          <div id="addSectionFormGroup" class="mb-3 d-none d-flex gap-2 bg-light-custom p-2.5 rounded-3 border">
            <input type="text" id="newSectionInput" class="form-control form-control-sm form-input-custom"
              placeholder="اسم القسم الجديد...">
            <button onclick="addAdminSettingItem('section')" class="btn btn-success btn-sm fw-bold px-3">حفظ</button>
          </div>
          <div id="sectionsListContainer" class="overflow-y-auto max-height-200 pe-1">
            <!-- dynamic -->
          </div>
        </div>
      </div>
    </div>

    <!-- Chambers settings -->
    <div class="col-12 col-md-6">
      <div class="card premium-card p-4 border shadow-sm h-100 d-flex flex-column justify-content-between">
        <div>
          <h5 class="fw-bold text-sm mb-3 d-flex justify-content-between align-items-center border-bottom pb-2">
            <span class="d-flex align-items-center gap-2">
              <i data-lucide="grid" class="text-success w-4 h-4"></i>
              الغرف القضائية (المجلس)
            </span>
            <button class="btn btn-emerald btn-xs px-2.5 py-1 text-xs rounded-2"
              onclick="document.getElementById('addChamberFormGroup').classList.toggle('d-none')">
              <i data-lucide="plus" class="w-3.5 h-3.5 inline"></i> إضافة
            </button>
          </h5>
          <div id="addChamberFormGroup" class="mb-3 d-none d-flex gap-2 bg-light-custom p-2.5 rounded-3 border">
            <input type="text" id="newChamberInput" class="form-control form-control-sm form-input-custom"
              placeholder="اسم الغرفة الجديدة...">
            <button onclick="addAdminSettingItem('chamber')" class="btn btn-success btn-sm fw-bold px-3">حفظ</button>
          </div>
          <div id="chambersListContainer" class="overflow-y-auto max-height-200 pe-1">
            <!-- dynamic -->
          </div>
        </div>
      </div>
    </div>

    <!-- Announcement settings manager -->
    <div class="col-12 text-right">
      <div class="card premium-card p-4 border shadow-sm">
        <h5 class="fw-bold text-base mb-3 d-flex justify-content-between align-items-center border-bottom pb-2 text-dark">
          <span class="d-flex align-items-center gap-2">
            <i data-lucide="bell" class="text-success w-5 h-5"></i>
            إدارة الإعلانات والتنبيهات العامة للمحامين
          </span>
          <button class="btn btn-emerald btn-sm px-3"
            onclick="document.getElementById('addAnnouncementContainer').classList.toggle('d-none')">
            <i data-lucide="plus" class="w-4 h-4 inline me-1"></i> إعلان جديد
          </button>
        </h5>

        <div id="addAnnouncementContainer" class="mb-4 p-4 rounded bg-light-custom border d-none shadow-inner fade-in">
          <label class="form-label text-xs fw-black text-muted mb-2">نص الإعلان العاجل (سيظهر في الشريط العلوي)</label>
          <textarea id="newAnnouncementText" class="form-control form-input-custom mb-3 text-sm fw-bold" rows="3"
            placeholder="اكتب هنا نص الإعلان الذي سيظهر لجميع المحامين في لوحة القيادة..."></textarea>
          <div class="d-flex justify-content-end gap-2">
            <button onclick="document.getElementById('addAnnouncementContainer').classList.add('d-none')"
              class="btn btn-light btn-sm px-3 rounded-2 text-muted fw-bold">إلغاء</button>
            <button onclick="postAnnouncement()" class="btn btn-emerald btn-sm px-4 rounded-2">نشر الإعلان</button>
          </div>
        </div>

        <div id="adminAnnouncementsList" class="d-flex flex-column gap-3">
          <!-- dynamic list -->
        </div>
      </div>
    </div>

  </div>
</section>
