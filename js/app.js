// App initial loading after login
async function initApp() {
    const loginContainer = document.getElementById('loginContainer');
    if (loginContainer) loginContainer.classList.add('d-none');
    
    const appContainer = document.getElementById('appContainer');
    if (appContainer) appContainer.classList.remove('d-none');
    
    // Display user top bar details
    updateHeaderUserProfile();
    
    // Pull resources
    await fetchSettings();
    await fetchRequests(false);
    await fetchRequests(true);
    await fetchAnnouncements();
    
    if (currentUser.role === 'admin' || currentUser.role === 'delegate') {
        document.querySelectorAll('.delegate-admin-only').forEach(el => el.classList.remove('d-none'));        
        if (currentUser.role === 'admin') {
            await fetchUsers();
            document.querySelectorAll('.admin-only').forEach(el => el.classList.remove('d-none'));
        } else {
            document.querySelectorAll('.admin-only').forEach(el => el.classList.add('d-none'));
        }
    } else {
        document.querySelectorAll('.admin-only, .delegate-admin-only').forEach(el => el.classList.add('d-none'));
    }

    if (currentUser.role === 'delegate') {
        document.getElementById('delegateReminder').classList.remove('d-none');
    } else {
        document.getElementById('delegateReminder').classList.add('d-none');
    }

    // Guest limits
    if (currentUser.role === 'guest') {
        document.getElementById('addNewCaseBtn').classList.add('d-none');
    } else {
        document.getElementById('addNewCaseBtn').classList.remove('d-none');
    }
    
    // Init flatpickr calendar
    calendarInstance = flatpickr("#calendarPicker", {
        inline: true,
        locale: "ar",
        defaultDate: selectedDate,
        onChange: (selectedDates, dateStr) => {
            if (selectedDates.length > 0) {
                const d = selectedDates[0];
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                selectedDate = `${y}-${m}-${day}`;
            }
            renderRequestsTable();
            renderCalendarRequests();
        },
        onDayCreate: (dObj, dStr, fp, dayElem) => {
            const y = dayElem.dateObj.getFullYear();
            const m = String(dayElem.dateObj.getMonth() + 1).padStart(2, '0');
            const d = String(dayElem.dateObj.getDate()).padStart(2, '0');
            const dateStr = `${y}-${m}-${d}`;
            
            const count = requests.filter(r => r.sessionDate === dateStr).length;
            if (count > 0) {
                if (!dayElem.querySelector('.flatpickr-day-badge')) {
                    const badge = document.createElement('span');
                    badge.className = 'flatpickr-day-badge';
                    badge.innerText = count;
                    dayElem.appendChild(badge);
                }
            }
        }
    });

    showTab('requests');
    startAutoRefresh();
}

// Update DOM elements for profile header
function updateHeaderUserProfile() {
    document.getElementById('headerUserFullName').innerText = `الأستاذ ${currentUser.last_name} ${currentUser.first_name}`;
    let roleText = 'محامي';
    if (currentUser.role === 'admin') roleText = 'مسؤول';
    else if (currentUser.role === 'delegate') roleText = 'مندوب';
    
    let subDetail = roleText;
    if (currentUser.role === 'lawyer') {
        subDetail += ` | سنة اليمين: ${currentUser.oath_date}`;
    }
    document.getElementById('headerUserRole').innerText = subDetail;
}

// Auto Refresh lists every 30 seconds
let refreshInterval = null;
function startAutoRefresh() {
    if (refreshInterval) clearInterval(refreshInterval);
    refreshInterval = setInterval(() => {
        if (currentUser) {
            fetchRequests(false);
            fetchAnnouncements();
            if (currentUser.role === 'admin') {
                fetchUsers();
            }
        }
    }, 30000);
}

// User Profile logic details
function initEventListeners() {
    // Nav bar tab switching
    document.querySelectorAll('.nav-tab-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            showTab(e.currentTarget.getAttribute('data-tab'));
        });
    });

    // Theme toggling
    const darkToggle = document.getElementById('darkToggle');
    if (darkToggle) {
        darkToggle.addEventListener('click', toggleDarkMode);
    }

    // Toggle notes visibility
    const toggleNotesBtn = document.getElementById('toggleNotesBtn');
    if (toggleNotesBtn) {
        toggleNotesBtn.addEventListener('click', () => {
            showNotes = !showNotes;
            localStorage.setItem('showNotes', showNotes);
            const notesContent = document.getElementById('notesContent');
            if (showNotes) {
                notesContent.classList.remove('d-none');
                toggleNotesBtn.innerHTML = '<i data-lucide="eye-off" class="w-4 h-4"></i> إخفاء الملاحظات';
            } else {
                notesContent.classList.add('d-none');
                toggleNotesBtn.innerHTML = '<i data-lucide="eye" class="w-4 h-4"></i> إظهار الملاحظات';
            }
            if (window.lucide) lucide.createIcons();
        });
        
        // Initial setup
        const notesContent = document.getElementById('notesContent');
        if (showNotes) {
            notesContent.classList.remove('d-none');
            toggleNotesBtn.innerHTML = '<i data-lucide="eye-off" class="w-4 h-4"></i> إخفاء الملاحظات';
        } else {
            notesContent.classList.add('d-none');
            toggleNotesBtn.innerHTML = '<i data-lucide="eye" class="w-4 h-4"></i> إظهار الملاحظات';
        }
    }

    // Search query binding
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchQuery = e.target.value;
            renderRequestsTable();
        });
    }

    // User management search & filters binding
    const userSearchEl = document.getElementById('userSearchInput');
    if (userSearchEl) {
        userSearchEl.addEventListener('input', (e) => {
            userSearchQuery = e.target.value;
            renderUsersList();
        });
    }

    const userRoleEl = document.getElementById('userRoleFilter');
    if (userRoleEl) {
        userRoleEl.addEventListener('change', (e) => {
            userRoleFilter = e.target.value;
            renderUsersList();
        });
    }

    const userStatusEl = document.getElementById('userStatusFilter');
    if (userStatusEl) {
        userStatusEl.addEventListener('change', (e) => {
            userStatusFilter = e.target.value;
            renderUsersList();
        });
    }

    // Announcement search binding
    const annSearchEl = document.getElementById('announcementSearchInput');
    if (annSearchEl) {
        annSearchEl.addEventListener('input', (e) => {
            announcementSearchQuery = e.target.value;
            renderAnnouncementsDetails();
        });
    }

    // Jurisdiction Select handlers
    const jurTypeSelect = document.getElementById('jurTypeSelect');
    if (jurTypeSelect) {
        jurTypeSelect.addEventListener('change', () => {
            const isCouncil = jurTypeSelect.value === 'council';
            currentJurisdiction.type = jurTypeSelect.value;
            currentJurisdiction.name = isCouncil ? selectedCouncil : (systemSettings.mapping[selectedCouncil]?.[0] || '');
            currentJurisdiction.subEntity = isCouncil ? systemSettings.chambers[0] : systemSettings.sections[0];
            updateJurisdictionSelects();
            renderRequestsTable();
        });
    }

    const jurCouncilSelect = document.getElementById('jurCouncilSelect');
    if (jurCouncilSelect) {
        jurCouncilSelect.addEventListener('change', () => {
            selectedCouncil = jurCouncilSelect.value;
            if (currentJurisdiction.type === 'council') {
                currentJurisdiction.name = selectedCouncil;
            } else {
                currentJurisdiction.name = systemSettings.mapping[selectedCouncil]?.[0] || '';
            }
            updateJurisdictionSelects();
            renderRequestsTable();
        });
    }

    const jurCourtSelect = document.getElementById('jurCourtSelect');
    if (jurCourtSelect) {
        jurCourtSelect.addEventListener('change', () => {
            currentJurisdiction.name = jurCourtSelect.value;
            renderRequestsTable();
        });
    }

    const jurSubSelect = document.getElementById('jurSubSelect');
    if (jurSubSelect) {
        jurSubSelect.addEventListener('change', () => {
            currentJurisdiction.subEntity = jurSubSelect.value;
            renderRequestsTable();
        });
    }

    // Login / Register Tab Switcher
    const toggleLoginTab = document.getElementById('toggleLoginTab');
    const toggleRegisterTab = document.getElementById('toggleRegisterTab');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    if (toggleLoginTab && toggleRegisterTab && loginForm && registerForm) {
        toggleLoginTab.addEventListener('click', () => {
            toggleLoginTab.classList.add('border-bottom', 'border-3', 'border-success', 'text-success', 'fw-black');
            toggleLoginTab.classList.remove('text-muted', 'fw-bold');
            toggleRegisterTab.classList.remove('border-bottom', 'border-3', 'border-success', 'text-success', 'fw-black');
            toggleRegisterTab.classList.add('text-muted', 'fw-bold');
            loginForm.classList.remove('d-none');
            registerForm.classList.add('d-none');
        });

        toggleRegisterTab.addEventListener('click', () => {
            toggleRegisterTab.classList.add('border-bottom', 'border-3', 'border-success', 'text-success', 'fw-black');
            toggleRegisterTab.classList.remove('text-muted', 'fw-bold');
            toggleLoginTab.classList.remove('border-bottom', 'border-3', 'border-success', 'text-success', 'fw-black');
            toggleLoginTab.classList.add('text-muted', 'fw-bold');
            registerForm.classList.remove('d-none');
            loginForm.classList.add('d-none');
        });
    }

    // Login Form Submit handler
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(loginForm);
            const errDiv = document.getElementById('loginErrorAlert');
            errDiv.classList.add('d-none');

            try {
                const res = await fetch('../api/api.php?action=login', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                
                if (res.ok && data.success) {
                    currentUser = data.user;
                    initApp();
                } else {
                    errDiv.innerText = data.error || 'خطأ غير معروف';
                    errDiv.classList.remove('d-none');
                }
            } catch (err) {
                console.error(err);
                errDiv.innerText = 'حدث خطأ في الاتصال بالخادم';
                errDiv.classList.remove('d-none');
            }
        });
    }

    // Register Form Submit handler
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(registerForm);
            const errDiv = document.getElementById('registerErrorAlert');
            errDiv.classList.add('d-none');

            try {
                const res = await fetch('../api/api.php?action=register', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                
                if (res.ok) {
                    if (data.pending) {
                        showToast(data.message, 'success');
                        registerForm.reset();
                        // Reset syndicate checkbox state
                        const oathInput = document.getElementById('oathDateRegisterInput');
                        oathInput.disabled = false;
                        oathInput.required = true;
                        oathInput.placeholder = "مثال: 1995";
                        oathInput.classList.remove('opacity-50');
                        // Switch to login tab
                        if (toggleLoginTab) toggleLoginTab.click();
                    }
                } else {
                    errDiv.innerText = data.error || 'خطأ غير معروف';
                    errDiv.classList.remove('d-none');
                }
            } catch (err) {
                console.error(err);
                errDiv.innerText = 'حدث خطأ في الاتصال بالخادم';
                errDiv.classList.remove('d-none');
            }
        });
    }

    // Role switcher during login screen
    const roleButtons = document.querySelectorAll('.role-select-btn');
    roleButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            roleButtons.forEach(b => b.classList.remove('bg-success-subtle', 'border-success', 'text-success', 'ring-1', 'ring-success'));
            roleButtons.forEach(b => b.classList.add('bg-light', 'text-muted'));

            const selectedRole = e.currentTarget.getAttribute('data-role');
            document.getElementById('loginRoleInput').value = selectedRole;

            e.currentTarget.classList.remove('bg-light', 'text-muted');
            e.currentTarget.classList.add('bg-success-subtle', 'border-success', 'text-success');
        });
    });

    // Register syndicate checkbox
    const isSyndicateRegister = document.getElementById('isSyndicateRegister');
    if (isSyndicateRegister) {
        isSyndicateRegister.addEventListener('change', (e) => {
            const checked = e.target.checked;
            const oathInput = document.getElementById('oathDateRegisterInput');
            if (checked) {
                oathInput.disabled = true;
                oathInput.required = false;
                oathInput.placeholder = "عضو نقابة";
                oathInput.classList.add('opacity-50');
                oathInput.value = '';
            } else {
                oathInput.disabled = false;
                oathInput.required = true;
                oathInput.placeholder = "مثال: 1995";
                oathInput.classList.remove('opacity-50');
            }
        });
    }

    // Guest login trigger
    const guestLoginBtn = document.getElementById('guestLoginBtn');
    if (guestLoginBtn) {
        guestLoginBtn.addEventListener('click', () => {
            currentUser = {
                id: 'guest',
                first_name: 'زائر',
                last_name: '',
                oath_date: '',
                role: 'guest',
                status: 'approved'
            };
            initApp();
        });
    }

    // Modal forms toggles
    const addColleagueCheck = document.getElementById('forColleagueCheckbox');
    if (addColleagueCheck) {
        addColleagueCheck.addEventListener('change', (e) => {
            const colleagueFields = document.getElementById('colleagueFieldsContainer');
            if (e.target.checked) {
                colleagueFields.classList.remove('d-none');
                document.getElementById('colleagueFirstName').required = true;
                document.getElementById('colleagueLastName').required = true;
                document.getElementById('colleagueOathDate').required = !document.getElementById('colleagueIsSyndicateMember').checked;
            } else {
                colleagueFields.classList.add('d-none');
                document.getElementById('colleagueFirstName').required = false;
                document.getElementById('colleagueLastName').required = false;
                document.getElementById('colleagueOathDate').required = false;
            }
        });
    }

    const colleagueIsSyndicate = document.getElementById('colleagueIsSyndicateMember');
    if (colleagueIsSyndicate) {
        colleagueIsSyndicate.addEventListener('change', (e) => {
            const oath = document.getElementById('colleagueOathDate');
            if (e.target.checked) {
                oath.disabled = true;
                oath.required = false;
                oath.classList.add('opacity-50');
            } else {
                oath.disabled = false;
                oath.required = true;
                oath.classList.remove('opacity-50');
            }
        });
    }

    // Case adding modal form submission
    const caseForm = document.getElementById('caseForm');
    if (caseForm) {
        caseForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const caseNumber = document.getElementById('caseNumberInput').value;
            const parties = document.getElementById('casePartiesInput').value;
            const purpose = document.querySelector('.purpose-select-btn.active').getAttribute('data-purpose');
            const isColleague = document.getElementById('forColleagueCheckbox').checked;

            const payload = {
                caseNumber,
                parties,
                purpose,
                sessionDate: selectedDate,
                jurisdiction: currentJurisdiction,
                isColleague
            };

            if (isColleague) {
                payload.colleagueFirstName = document.getElementById('colleagueFirstName').value;
                payload.colleagueLastName = document.getElementById('colleagueLastName').value;
                payload.colleagueIsSyndicateMember = document.getElementById('colleagueIsSyndicateMember').checked;
                payload.colleagueOathDate = document.getElementById('colleagueOathDate').value;
            }

            const url = editingRequest ? '../api/api.php?action=edit_request' : '../api/api.php?action=add_request';
            if (editingRequest) {
                payload.id = editingRequest.id;
            }

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (res.ok) {
                    showToast(editingRequest ? 'تم تحديث القضية بنجاح' : 'تم إضافة القضية بنجاح', 'success');
                    closeAddCaseModal();
                    await fetchRequests(false);
                } else {
                    showToast(data.error, 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('خطأ في إرسال البيانات', 'error');
            }
        });
    }

    // Modal cancellation/close triggers
    document.querySelectorAll('.close-modal-trigger').forEach(btn => {
        btn.addEventListener('click', () => {
            closeAddCaseModal();
        });
    });

    // Purpose selection switcher buttons in modal
    const purposeButtons = document.querySelectorAll('.purpose-select-btn');
    purposeButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            purposeButtons.forEach(b => b.classList.remove('active', 'bg-warning-subtle', 'border-warning', 'text-warning', 'bg-primary-subtle', 'border-primary', 'text-primary', 'ring-1', 'ring-warning', 'ring-primary'));
            
            const purpose = e.currentTarget.getAttribute('data-purpose');
            e.currentTarget.classList.add('active');

            if (purpose === 'delay') {
                e.currentTarget.classList.add('bg-warning-subtle', 'border-warning', 'text-warning');
            } else {
                e.currentTarget.classList.add('bg-primary-subtle', 'border-primary', 'text-primary');
            }
        });
    });

    // Logout
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            await fetch('../api/api.php?action=logout');
            currentUser = null;
            window.location.href = '../index.php';
        });
    }
}

// App Initialization
document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.dir = 'rtl';
    document.documentElement.lang = 'ar';
    
    // Apply theme
    applyTheme();
    
    // Initialize standard event handlers
    initEventListeners();
    
    // Check authentication
    checkAuth().then(() => {
        if (currentUser) {
            initApp();
        } else {
            window.location.href = '../index.php';
        }
    });

    // Register Service Worker
    if ('serviceWorker' in navigator) {
        if (document.readyState === 'complete') {
            navigator.serviceWorker.register('../sw.js')
                .then((reg) => console.log('Service Worker registered successfully:', reg.scope))
                .catch((err) => console.error('Service Worker registration failed:', err));
        } else {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('../sw.js')
                    .then((reg) => console.log('Service Worker registered successfully:', reg.scope))
                    .catch((err) => console.error('Service Worker registration failed:', err));
            });
        }
    }

    // PWA Install Prompt
    initPwaInstall();
});

// 13:00 Notification Alarm Alarm
setInterval(() => {
    if (!notificationsEnabled) return;

    const now = new Date();
    if (now.getHours() === 13 && now.getMinutes() === 0) {
        const lastNotified = localStorage.getItem('lastNotificationDate');
        const today = now.toDateString();

        if (lastNotified !== today) {
            if ("Notification" in window) {
                if (Notification.permission === "granted") {
                    new Notification("تنبيه منظمة محامي البليدة", {
                        body: "ضرورة استخراج القضايا لجلسة الغد قبل غلق القائمة.",
                        icon: "https://storage.googleapis.com/static.ai.studio/build/Rachid.Mca.Chido%40gmail.com/Rachid.Mca.Chido%40gmail.com_1775555917000_0.png",
                        dir: 'rtl'
                    });
                    localStorage.setItem('lastNotificationDate', today);
                } else if (Notification.permission !== "denied") {
                    Notification.requestPermission().then(permission => {
                        if (permission === "granted") {
                            new Notification("تنبيه منظمة محامي البليدة", {
                                body: "ضرورة استخراج القضايا لجلسة الغد قبل غلق القائمة.",
                                icon: "https://storage.googleapis.com/static.ai.studio/build/Rachid.Mca.Chido%40gmail.com/Rachid.Mca.Chido%40gmail.com_1775555917000_0.png",
                                dir: 'rtl'
                            });
                            localStorage.setItem('lastNotificationDate', today);
                        }
                    });
                }
            }
        }
    }
}, 30000);

// PWA installation triggers
let deferredPrompt = null;
function initPwaInstall() {
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        const installBtn = document.getElementById('installAppBtn');
        if (installBtn) installBtn.classList.remove('d-none');
    });

    window.addEventListener('appinstalled', () => {
        const installBtn = document.getElementById('installAppBtn');
        if (installBtn) installBtn.classList.add('d-none');
        deferredPrompt = null;
    });

    const installBtn = document.getElementById('installAppBtn');
    if (installBtn) {
        installBtn.addEventListener('click', async () => {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                installBtn.classList.add('d-none');
            }
            deferredPrompt = null;
        });
    }

    // Check iframe environment
    if (window.self !== window.top) {
        const iframeMsg = document.getElementById('iframeInstallMsg');
        if (iframeMsg) iframeMsg.classList.remove('d-none');
    }
}

// ==================== PROFILE EDITING ====================

function showProfileAlert(containerId, msgId, type, message) {
    const box = document.getElementById(containerId);
    const msg = document.getElementById(msgId);
    if (!box || !msg) return;
    box.className = `alert alert-${type} alert-dismissible rounded-3 mb-3 text-sm fw-semibold`;
    msg.innerText = message;
    box.classList.remove('d-none');
    setTimeout(() => box.classList.add('d-none'), 5000);
}

async function saveProfileInfo(event) {
    event.preventDefault();
    const btn = document.getElementById('profileSaveBtn');
    const origHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري الحفظ...';

    try {
        const payload = {
            action:    'update_profile',
            lastName:  document.getElementById('profileLastName').value.trim(),
            firstName: document.getElementById('profileFirstName').value.trim(),
            email:     document.getElementById('profileEmail').value.trim(),
            phone:     document.getElementById('profilePhone').value.trim(),
        };
        const res  = await fetch('../api/api.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        const data = await res.json();
        if (data.success) {
            currentUser = data.user;
            updateHeaderUserProfile();
            renderProfile();
            showProfileAlert('profileInfoAlert', 'profileInfoAlertMsg', 'success', '✓ تم حفظ المعلومات بنجاح');
        } else {
            showProfileAlert('profileInfoAlert', 'profileInfoAlertMsg', 'danger', data.error || 'حدث خطأ');
        }
    } catch (e) {
        showProfileAlert('profileInfoAlert', 'profileInfoAlertMsg', 'danger', 'تعذر الاتصال بالخادم');
    } finally {
        btn.disabled  = false;
        btn.innerHTML = origHTML;
        lucide.createIcons();
    }
}

async function saveNewPassword(event) {
    event.preventDefault();
    const current  = document.getElementById('currentPassword').value;
    const newPwd   = document.getElementById('newPassword').value;
    const confirm  = document.getElementById('confirmPassword').value;

    if (!current || !newPwd) {
        return showProfileAlert('profilePasswordAlert', 'profilePasswordAlertMsg', 'warning', 'يرجى ملء جميع حقول كلمة السر');
    }
    if (newPwd !== confirm) {
        return showProfileAlert('profilePasswordAlert', 'profilePasswordAlertMsg', 'warning', 'كلمة السر الجديدة وتأكيدها غير متطابقتين');
    }

    const btn = document.getElementById('passwordSaveBtn');
    const origHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري التحديث...';

    try {
        const payload = {
            action:          'update_profile',
            lastName:        currentUser.last_name,
            firstName:       currentUser.first_name,
            email:           currentUser.email  || '',
            phone:           currentUser.phone  || '',
            currentPassword: current,
            newPassword:     newPwd,
        };
        const res  = await fetch('../api/api.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        const data = await res.json();
        if (data.success) {
            showProfileAlert('profilePasswordAlert', 'profilePasswordAlertMsg', 'success', '✓ تم تحديث كلمة السر بنجاح');
            document.getElementById('profilePasswordForm').reset();
            document.getElementById('pwdStrengthBar').classList.add('d-none');
        } else {
            showProfileAlert('profilePasswordAlert', 'profilePasswordAlertMsg', 'danger', data.error || 'حدث خطأ');
        }
    } catch (e) {
        showProfileAlert('profilePasswordAlert', 'profilePasswordAlertMsg', 'danger', 'تعذر الاتصال بالخادم');
    } finally {
        btn.disabled  = false;
        btn.innerHTML = origHTML;
        lucide.createIcons();
    }
}

function togglePasswordSection() {
    const section = document.getElementById('passwordSection');
    const icon    = document.getElementById('passwordToggleIcon');
    if (!section) return;
    section.classList.toggle('d-none');
    if (icon) {
        icon.setAttribute('data-lucide', section.classList.contains('d-none') ? 'chevron-down' : 'chevron-up');
        lucide.createIcons();
    }
}

function togglePwdVisibility(fieldId, btn) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    const isText = field.type === 'text';
    field.type = isText ? 'password' : 'text';
    const icon = btn.querySelector('i');
    if (icon) {
        icon.setAttribute('data-lucide', isText ? 'eye' : 'eye-off');
        lucide.createIcons();
    }
}

// Password strength meter — wire up after DOM ready
document.addEventListener('DOMContentLoaded', () => {
    const pwdInput = document.getElementById('newPassword');
    if (!pwdInput) return;
    pwdInput.addEventListener('input', () => {
        const val   = pwdInput.value;
        const bar   = document.getElementById('pwdStrengthBar');
        const fill  = document.getElementById('pwdStrengthFill');
        const label = document.getElementById('pwdStrengthLabel');
        if (!val) { bar.classList.add('d-none'); return; }
        bar.classList.remove('d-none');
        let score = 0;
        if (val.length >= 6)           score++;
        if (val.length >= 10)          score++;
        if (/[A-Z]/.test(val))         score++;
        if (/[0-9]/.test(val))         score++;
        if (/[^A-Za-z0-9]/.test(val))  score++;
        const levels = [
            { pct: 20,  cls: 'bg-danger',  txt: 'ضعيفة جداً' },
            { pct: 40,  cls: 'bg-danger',  txt: 'ضعيفة' },
            { pct: 60,  cls: 'bg-warning', txt: 'متوسطة' },
            { pct: 80,  cls: 'bg-info',    txt: 'جيدة' },
            { pct: 100, cls: 'bg-success', txt: 'قوية جداً' },
        ];
        const lvl = levels[Math.min(score, 4)];
        fill.style.width = lvl.pct + '%';
        fill.className   = `progress-bar rounded-pill transition-all ${lvl.cls}`;
        label.innerText  = `قوة كلمة السر: ${lvl.txt}`;
        label.className  = `text-xs mt-1 mb-0 ${lvl.cls.replace('bg-', 'text-')}`;
    });
});
