      <!-- ==================== TAB: PROFILE ==================== -->
      <section id="profileView" class="tab-content-panel fade-in d-none">
        <div class="max-w-2xl mx-auto d-flex flex-column gap-4">

          <!-- ── Profile Header Card ── -->
          <div class="card premium-card p-4 p-sm-5">
            <div class="d-flex flex-column flex-sm-row align-items-center gap-4">
              <!-- Avatar -->
              <div class="flex-shrink-0 rounded-circle bg-success bg-opacity-10 border border-success border-opacity-25 d-flex align-items-center justify-content-center"
                style="width:88px;height:88px;">
                <i data-lucide="user" class="text-success" style="width:40px;height:40px;"></i>
              </div>
              <!-- Identity overview (read-only) -->
              <div class="text-center text-sm-end flex-grow-1">
                <h3 id="profileHeaderName" class="fw-black mb-1 text-dark">—</h3>
                <div class="d-flex flex-wrap justify-content-center justify-content-sm-end gap-2 mt-2">
                  <span id="profileRoleBadge"
                    class="badge bg-success bg-opacity-15 text-white fw-bold px-3 py-1.5 rounded-pill fs-xs"></span>
                  <span id="profileOathBadge"
                    class="badge bg-light-custom text-muted fw-semibold px-3 py-1.5 rounded-pill fs-xs border"></span>
                </div>
              </div>
            </div>
          </div>

          <!-- ── Edit Info Card ── -->
          <div class="card premium-card p-4 p-sm-5">
            <h5 class="fw-bold text-sm mb-4 d-flex align-items-center gap-2 border-bottom pb-3">
              <i data-lucide="pencil" class="text-success w-4 h-4"></i>
              تعديل المعلومات الشخصية
            </h5>

            <!-- Feedback banner -->
            <div id="profileInfoAlert" class="d-none alert alert-dismissible rounded-3 mb-3 text-sm fw-semibold" role="alert">
              <span id="profileInfoAlertMsg"></span>
              <button type="button" class="btn-close btn-sm" onclick="document.getElementById('profileInfoAlert').classList.add('d-none')"></button>
            </div>

            <form id="profileInfoForm" onsubmit="saveProfileInfo(event)" novalidate>
              <div class="row g-3">
                <div class="col-12 col-sm-6">
                  <label class="form-label text-xs fw-bold text-muted">اللقب</label>
                  <input type="text" id="profileLastName" class="form-control form-input-custom" required>
                </div>
                <div class="col-12 col-sm-6">
                  <label class="form-label text-xs fw-bold text-muted">الاسم</label>
                  <input type="text" id="profileFirstName" class="form-control form-input-custom" required>
                </div>
                <div class="col-12 col-sm-6">
                  <label class="form-label text-xs fw-bold text-muted">البريد الإلكتروني</label>
                  <input type="email" id="profileEmail" class="form-control form-input-custom">
                </div>
                <div class="col-12 col-sm-6">
                  <label class="form-label text-xs fw-bold text-muted">رقم الهاتف</label>
                  <input type="tel" id="profilePhone" class="form-control form-input-custom">
                </div>
              </div>

              <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                <button type="submit" id="profileSaveBtn"
                  class="btn btn-emerald btn-sm fw-bold px-4 py-2 rounded-3 scale-active d-flex align-items-center gap-2">
                  <i data-lucide="save" class="w-4 h-4"></i>
                  حفظ التعديلات
                </button>
              </div>
            </form>
          </div>

          <!-- ── Change Password Card ── -->
          <div class="card premium-card p-4 p-sm-5">
            <button class="d-flex align-items-center justify-content-between gap-2 border-0 bg-transparent w-100 text-start mb-0 pb-0"
              type="button" onclick="togglePasswordSection()">
              <h5 class="fw-bold text-sm text-dark mb-0 d-flex align-items-center gap-2">
                <i data-lucide="lock" class="text-warning w-4 h-4"></i>
                تغيير كلمة السر
              </h5>
              <i id="passwordToggleIcon" data-lucide="chevron-down" class="text-muted w-4 h-4 transition-all"></i>
            </button>

            <div id="passwordSection" class="d-none mt-3 pt-3 border-top">
              <!-- Feedback banner -->
              <div id="profilePasswordAlert" class="d-none alert alert-dismissible rounded-3 mb-3 text-sm fw-semibold" role="alert">
                <span id="profilePasswordAlertMsg"></span>
                <button type="button" class="btn-close btn-sm" onclick="document.getElementById('profilePasswordAlert').classList.add('d-none')"></button>
              </div>

              <form id="profilePasswordForm" onsubmit="saveNewPassword(event)" novalidate>
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label text-xs fw-bold text-muted">كلمة السر الحالية</label>
                    <div class="position-relative">
                      <input type="password" id="currentPassword" class="form-control form-input-custom pe-5"
                        placeholder="●●●●●●●●" autocomplete="current-password">
                      <button type="button" class="btn btn-link position-absolute top-50 end-0 translate-middle-y px-3 text-muted"
                        onclick="togglePwdVisibility('currentPassword', this)">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                      </button>
                    </div>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="form-label text-xs fw-bold text-muted">كلمة السر الجديدة</label>
                    <div class="position-relative">
                      <input type="password" id="newPassword" class="form-control form-input-custom pe-5"
                        placeholder="●●●●●●●●" autocomplete="new-password">
                      <button type="button" class="btn btn-link position-absolute top-50 end-0 translate-middle-y px-3 text-muted"
                        onclick="togglePwdVisibility('newPassword', this)">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                      </button>
                    </div>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="form-label text-xs fw-bold text-muted">تأكيد كلمة السر</label>
                    <div class="position-relative">
                      <input type="password" id="confirmPassword" class="form-control form-input-custom pe-5"
                        placeholder="●●●●●●●●" autocomplete="new-password">
                      <button type="button" class="btn btn-link position-absolute top-50 end-0 translate-middle-y px-3 text-muted"
                        onclick="togglePwdVisibility('confirmPassword', this)">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Password strength indicator -->
                <div id="pwdStrengthBar" class="mt-2 d-none">
                  <div class="progress rounded-pill" style="height:4px;">
                    <div id="pwdStrengthFill" class="progress-bar rounded-pill transition-all" role="progressbar"></div>
                  </div>
                  <p id="pwdStrengthLabel" class="text-xs mt-1 mb-0 text-muted"></p>
                </div>

                <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                  <button type="submit" id="passwordSaveBtn"
                    class="btn btn-warning btn-sm fw-bold px-4 py-2 rounded-3 scale-active d-flex align-items-center gap-2 text-dark">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    تحديث كلمة السر
                  </button>
                </div>
              </form>
            </div>
          </div>

        </div>
      </section>
