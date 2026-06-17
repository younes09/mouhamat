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
