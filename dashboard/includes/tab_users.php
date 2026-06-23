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

          <!-- Search & Filter Controls -->
          <div class="row g-3 mb-4">
            <!-- Search Bar -->
            <div class="col-12 col-lg-6">
              <div class="position-relative">
                <input type="text" id="userSearchInput"
                  class="form-control form-input-custom ps-5 py-2.5 text-sm fw-bold shadow-sm"
                  placeholder="البحث باسم الأستاذ، البريد الإلكتروني، أو رقم الهاتف...">
                <span class="position-absolute end-0 top-50 translate-middle-y pe-3.5 text-muted">
                  <i data-lucide="search" class="w-4 h-4"></i>
                </span>
              </div>
            </div>
            <!-- Role Filter -->
            <div class="col-6 col-lg-3">
              <select id="userRoleFilter" class="form-select form-input-custom text-sm fw-bold shadow-sm">
                <option value="all">كل الصفات</option>
                <option value="admin">مسؤول</option>
                <option value="delegate">مندوب</option>
                <option value="lawyer">محامي</option>
              </select>
            </div>
            <!-- Status Filter -->
            <div class="col-6 col-lg-3">
              <select id="userStatusFilter" class="form-select form-input-custom text-sm fw-bold shadow-sm">
                <option value="all">كل الحالات</option>
                <option value="approved">نشط</option>
                <option value="pending">قيد المراجعة</option>
                <option value="rejected">مرفوض</option>
              </select>
            </div>
          </div>

          <div class="table-responsive d-none d-md-block">
            <table class="table custom-table align-middle">
              <thead>
                <tr>
                  <th>الأستاذ</th>
                  <th>البريد الإلكتروني</th>
                  <th>رقم الهاتف</th>
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

          <!-- Mobile view cards -->
          <div id="usersMobileContainer" class="d-md-none d-flex flex-column gap-3">
            <!-- dynamic -->
          </div>
        </div>
      </section>
