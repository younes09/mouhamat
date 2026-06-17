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
