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
          <div class="table-responsive">
            <table class="table custom-table align-middle">
              <thead>
                <tr>
                  <th>الأستاذ</th>
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
        </div>
      </section>
