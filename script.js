// State Management
let currentUser = null;
let requests = [];
let archiveRequests = [];
let announcements = [];
let systemSettings = {
    isListOpen: true,
    councils: [],
    courts: [],
    sections: [],
    chambers: [],
    mapping: {}
};
let allUsers = [];

// Filtering & View State
let currentTab = 'requests'; // requests, announcements, archive, calendar, stats, users, profile, settings
let currentJurisdiction = {
    type: 'court',
    name: 'محكمة البليدة',
    subEntity: 'قسم الجنح'
};
let selectedCouncil = 'مجلس قضاء البليدة';
let selectedDate = getNextWorkingDay();
let searchQuery = '';
let isAddingRequest = false;
let isForColleague = false;
let editingRequest = null;
let viewingCardUrl = null;
let isDarkMode = localStorage.getItem('darkMode') === 'true';
let showNotes = localStorage.getItem('showNotes') !== 'false';
let notificationsEnabled = localStorage.getItem('notificationsEnabled') === 'true';

// Auto-determine next working day (skips Friday/Saturday)
function getNextWorkingDay() {
    const d = new Date();
    const day = d.getDay(); // w: 0 (Sunday) to 6 (Saturday)
    if (day === 4) { // Thursday -> next is Sunday (+3)
        d.setDate(d.getDate() + 3);
    } else if (day === 5) { // Friday -> next is Sunday (+2)
        d.setDate(d.getDate() + 2);
    } else if (day === 6) { // Saturday -> next is Sunday (+1)
        d.setDate(d.getDate() + 1);
    } else { // Mon, Tue, Wed, Sun -> next day (+1)
        d.setDate(d.getDate() + 1);
    }
    return d.toISOString().split('T')[0];
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

    // PWA Install Prompt
    initPwaInstall();
});

// Theme Control
function applyTheme() {
    if (isDarkMode) {
        document.documentElement.classList.add('dark');
        const darkToggle = document.getElementById('darkToggle');
        if (darkToggle) darkToggle.innerHTML = '<i data-lucide="sun" class="w-5 h-5"></i>';
    } else {
        document.documentElement.classList.remove('dark');
        const darkToggle = document.getElementById('darkToggle');
        if (darkToggle) darkToggle.innerHTML = '<i data-lucide="moon" class="w-5 h-5"></i>';
    }
    if (window.lucide) lucide.createIcons();
}

function toggleDarkMode() {
    isDarkMode = !isDarkMode;
    localStorage.setItem('darkMode', isDarkMode);
    applyTheme();
}

// Check auth state
async function checkAuth() {
    if (window.preinjectedUser) {
        currentUser = window.preinjectedUser;
        return;
    }
    try {
        const res = await fetch('../api.php?action=auth_check');
        const data = await res.json();
        if (data.authenticated) {
            currentUser = data.user;
        } else {
            currentUser = null;
            window.location.href = '../index.php';
        }
    } catch (e) {
        console.error('Auth check error', e);
        window.location.href = '../index.php';
    }
}

// Fetch all settings, lists, and active status
async function fetchSettings() {
    try {
        const res = await fetch(`../api.php?action=get_settings&t=${Date.now()}`);
        systemSettings = await res.json();
        
        // Synchronize selected council and names
        if (systemSettings.councils.length > 0) {
            if (!systemSettings.councils.includes(selectedCouncil)) {
                selectedCouncil = systemSettings.councils[0];
            }
        }
        
        updateJurisdictionSelects();
    } catch (e) {
        console.error('Error fetching settings', e);
    }
}

// Fetch Active Requests or Archives
async function fetchRequests(isHistory = false) {
    try {
        const res = await fetch(`../api.php?action=get_requests&history=${isHistory}&t=${Date.now()}`);
        const data = await res.json();
        if (isHistory) {
            archiveRequests = data;
        } else {
            requests = data;
        }
        renderRequestsTable();
    } catch (e) {
        console.error('Error fetching requests', e);
    }
}

// Fetch Announcements
async function fetchAnnouncements() {
    try {
        const res = await fetch(`../api.php?action=get_announcements&t=${Date.now()}`);
        announcements = await res.json();
        renderAnnouncements();
        updateNotificationBadge();
    } catch (e) {
        console.error('Error fetching announcements', e);
    }
}

// Fetch Users List (Admin only)
async function fetchUsers() {
    if (currentUser.role !== 'admin') return;
    try {
        const res = await fetch(`../api.php?action=get_users&t=${Date.now()}`);
        allUsers = await res.json();
        renderUsersList();
    } catch (e) {
        console.error('Error fetching users', e);
    }
}

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
    flatpickr("#calendarPicker", {
        inline: true,
        locale: "ar",
        defaultDate: selectedDate,
        onChange: (selectedDates, dateStr) => {
            selectedDate = dateStr;
            renderRequestsTable();
            showTab('requests');
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

// Show/Hide specific views
function showTab(tabId) {
    currentTab = tabId;
    document.querySelectorAll('.tab-content-panel').forEach(el => el.classList.add('d-none'));
    document.querySelectorAll('.nav-tab-btn').forEach(btn => btn.classList.remove('active'));

    const activeBtn = document.querySelector(`.nav-tab-btn[data-tab="${tabId}"]`);
    if (activeBtn) activeBtn.classList.add('active');

    if (tabId === 'requests') {
        document.getElementById('requestsView').classList.remove('d-none');
        document.getElementById('searchBarContainer').classList.remove('d-none');
        document.getElementById('jurisdictionSelectorCard').classList.remove('d-none');
        document.getElementById('notesContainer').classList.remove('d-none');
        document.getElementById('sessionDateHeaderPanel').classList.remove('d-none');
        renderRequestsTable();
    } else if (tabId === 'announcements') {
        document.getElementById('announcementsView').classList.remove('d-none');
        renderAnnouncementsDetails();
    } else if (tabId === 'archive') {
        document.getElementById('requestsView').classList.remove('d-none');
        document.getElementById('searchBarContainer').classList.remove('d-none');
        document.getElementById('jurisdictionSelectorCard').classList.remove('d-none');
        document.getElementById('notesContainer').classList.add('d-none');
        document.getElementById('sessionDateHeaderPanel').classList.add('d-none');
        renderRequestsTable(); // will render from archive requests if tab is archive
    } else if (tabId === 'calendar') {
        document.getElementById('calendarView').classList.remove('d-none');
    } else if (tabId === 'stats') {
        document.getElementById('statsView').classList.remove('d-none');
        renderStats();
    } else if (tabId === 'users') {
        document.getElementById('usersView').classList.remove('d-none');
        renderUsersList();
    } else if (tabId === 'profile') {
        document.getElementById('profileView').classList.remove('d-none');
        renderProfile();
    } else if (tabId === 'settings') {
        document.getElementById('settingsView').classList.remove('d-none');
        renderSettings();
    }
}

// Populate drop downs and selects mapping
function updateJurisdictionSelects() {
    const jurTypeSelect = document.getElementById('jurTypeSelect');
    const jurCouncilSelect = document.getElementById('jurCouncilSelect');
    const jurCourtSelect = document.getElementById('jurCourtSelect');
    const jurSubSelect = document.getElementById('jurSubSelect');

    if (!jurTypeSelect) return;

    const jurType = jurTypeSelect.value;
    
    // Fill councils
    jurCouncilSelect.innerHTML = '';
    systemSettings.councils.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c;
        opt.innerText = c;
        jurCouncilSelect.appendChild(opt);
    });
    jurCouncilSelect.value = selectedCouncil;

    // Show/Hide court select
    if (jurType === 'court') {
        document.getElementById('courtSelectGroup').classList.remove('d-none');
        jurCourtSelect.innerHTML = '';
        const mappedCourts = systemSettings.mapping[selectedCouncil] || [];
        mappedCourts.forEach(ct => {
            const opt = document.createElement('option');
            opt.value = ct;
            opt.innerText = ct;
            jurCourtSelect.appendChild(opt);
        });
        if (mappedCourts.length > 0 && !mappedCourts.includes(currentJurisdiction.name)) {
            currentJurisdiction.name = mappedCourts[0];
        }
        jurCourtSelect.value = currentJurisdiction.name;
    } else {
        document.getElementById('courtSelectGroup').classList.add('d-none');
        currentJurisdiction.name = selectedCouncil;
    }

    // Fill sub-entities (chambers for council, sections for court)
    jurSubSelect.innerHTML = '';
    const subList = jurType === 'council' ? systemSettings.chambers : systemSettings.sections;
    subList.forEach(sub => {
        const opt = document.createElement('option');
        opt.value = sub;
        opt.innerText = sub;
        jurSubSelect.appendChild(opt);
    });
    
    if (subList.length > 0 && !subList.includes(currentJurisdiction.subEntity)) {
        currentJurisdiction.subEntity = subList[0];
    }
    jurSubSelect.value = currentJurisdiction.subEntity;
}

// Sorting and Unique constraints logic matching React
function processRequests(sourceList) {
    // 1. Filter by current selection
    const filtered = sourceList.filter(r => {
        const matchesJurisdiction = 
            r.jurisdiction.type === currentJurisdiction.type &&
            r.jurisdiction.name === currentJurisdiction.name &&
            r.jurisdiction.subEntity === currentJurisdiction.subEntity;
        
        const matchesSearch = 
            r.lawyerName.toLowerCase().includes(searchQuery.toLowerCase()) ||
            r.caseNumber.toLowerCase().includes(searchQuery.toLowerCase()) ||
            r.parties.toLowerCase().includes(searchQuery.toLowerCase());

        const matchesDate = r.sessionDate === selectedDate;

        if (currentTab === 'archive') {
            return matchesJurisdiction && matchesSearch; // History shows all dates by default
        }
        return matchesJurisdiction && matchesDate && matchesSearch;
    });

    // 2. Sort by purpose (delay first) and seniority score
    const sorted = [...filtered].sort((a, b) => {
        if (a.purpose !== b.purpose) {
            return a.purpose === 'delay' ? -1 : 1;
        }

        const getSeniorityScore = (req) => {
            if (req.isSyndicateMember || req.oathDate === 'عضو نقابة') return 0;
            const year = parseInt(req.oathDate);
            return isNaN(year) ? 9999 : year;
        };

        const scoreA = getSeniorityScore(a);
        const scoreB = getSeniorityScore(b);

        if (scoreA !== scoreB) return scoreA - scoreB;
        return a.createdAt - b.createdAt;
    });

    // 3. Deduplicate (only show the most senior lawyer per case number and purpose)
    const unique = [];
    const seen = new Set();
    sorted.forEach(req => {
        const key = `${req.caseNumber}_${req.purpose}`;
        if (!seen.has(key)) {
            unique.push(req);
            seen.add(key);
        }
    });

    return unique;
}

// Render Table
function renderRequestsTable() {
    const listToProcess = currentTab === 'archive' ? archiveRequests : requests;
    const finalRequests = processRequests(listToProcess);
    
    // Render list description text in header
    document.getElementById('listTitleHeader').innerText = `${currentJurisdiction.name} - ${currentJurisdiction.subEntity}`;
    document.getElementById('sessionTargetDateText').innerText = new Date(selectedDate).toLocaleDateString('ar-DZ', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

    // Table render (desktop)
    const tbody = document.getElementById('requestsTableBody');
    tbody.innerHTML = '';

    // Mobile render cards
    const mobileContainer = document.getElementById('requestsMobileContainer');
    mobileContainer.innerHTML = '';

    if (finalRequests.length === 0) {
        // Empty state
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                    <i data-lucide="file-text" class="w-12 h-12 opacity-25 d-block mx-auto mb-2"></i>
                    ${currentTab === 'archive' ? 'الأرشيف فارغ' : 'لا توجد طلبات مسجلة حالياً'}
                </td>
            </tr>
        `;
        mobileContainer.innerHTML = `
            <div class="premium-card p-5 text-center text-muted">
                <i data-lucide="file-text" class="w-12 h-12 opacity-25 d-block mx-auto mb-2"></i>
                ${currentTab === 'archive' ? 'الأرشيف فارغ' : 'لا توجد طلبات مسجلة'}
            </div>
        `;
        if (window.lucide) lucide.createIcons();
        return;
    }

    let seenPurpose = null;

    finalRequests.forEach((req, idx) => {
        // If purpose changed, show section break
        if (req.purpose !== seenPurpose && currentTab !== 'archive') {
            seenPurpose = req.purpose;
            const breakTr = document.createElement('tr');
            breakTr.className = 'print-bg-slate bg-light text-center font-weight-bold';
            breakTr.innerHTML = `
                <td colspan="7" class="py-2 text-xs font-bold text-muted border-top border-bottom">
                    ${req.purpose === 'delay' ? '--- قسم التأجيلات ---' : '--- قسم التسبيقات ---'}
                </td>
            `;
            tbody.appendChild(breakTr);
        }

        // Action controls
        let actionButtons = '';
        if (currentTab !== 'archive' && currentUser.role !== 'guest') {
            const canModify = (currentUser.role === 'admin' || currentUser.role === 'delegate' || req.creatorId === currentUser.id);
            if (canModify) {
                actionButtons = `
                    <button onclick="editRequestPrompt('${req.id}')" class="btn btn-sm btn-outline-success border-0 me-1" title="تعديل">
                        <i data-lucide="edit" class="w-4 h-4"></i>
                    </button>
                    <button onclick="deleteRequestPrompt('${req.id}')" class="btn btn-sm btn-outline-danger border-0" title="حذف">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                `;
            }
        } else if (currentTab === 'archive' && currentUser.role === 'admin') {
            actionButtons = `
                <button onclick="deleteRequestPrompt('${req.id}', true)" class="btn btn-sm btn-outline-danger border-0" title="حذف نهائي">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            `;
        }

        const badgeClass = req.purpose === 'delay' ? 'bg-warning text-dark' : 'bg-primary text-white';
        const purposeText = req.purpose === 'delay' ? 'تأجيل' : 'تسبيق';

        const tr = document.createElement('tr');
        tr.className = req.purpose === 'delay' ? 'bg-warning-subtle' : 'bg-primary-subtle';
        tr.innerHTML = `
            <td class="px-3 py-3 text-muted print-border-black">${idx + 1}</td>
            <td class="px-3 py-3 fw-bold text-dark print-border-black">${req.lawyerName}</td>
            <td class="px-3 py-3 text-muted print-border-black">${req.parties}</td>
            <td class="px-3 py-3 text-muted font-monospace print-border-black">${req.caseNumber}</td>
            <td class="px-3 py-3 text-muted print-border-black">
                ${req.oathDate === 'عضو نقابة' ? '<span class="badge bg-success">عضو نقابة</span>' : req.oathDate}
            </td>
            <td class="px-3 py-3 print-border-black">
                <span class="badge ${badgeClass}">${purposeText}</span>
            </td>
            <td class="px-3 py-3 print-hidden">${actionButtons}</td>
        `;
        tbody.appendChild(tr);

        // Mobile Cards rendering
        const card = document.createElement('div');
        card.className = `p-4 mb-3 premium-card border ${req.purpose === 'delay' ? 'border-warning' : 'border-primary'}`;
        card.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-dark">#${idx + 1}</span>
                <span class="badge ${badgeClass}">${purposeText}</span>
            </div>
            <h5 class="fw-bold mb-1">${req.lawyerName}</h5>
            <div class="row pt-2 mt-2 border-top">
                <div class="col-6">
                    <small class="text-muted d-block text-uppercase">رقم القضية</small>
                    <span class="font-monospace text-dark">${req.caseNumber}</span>
                </div>
                <div class="col-6">
                    <small class="text-muted d-block text-uppercase">الأطراف</small>
                    <span class="text-dark text-truncate d-block">${req.parties}</span>
                </div>
            </div>
            ${actionButtons ? `
                <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                    ${actionButtons}
                </div>
            ` : ''}
        `;
        mobileContainer.appendChild(card);
    });

    // Populate Official Print Template
    const printSessionDateEl = document.getElementById('printSessionDate');
    const printCenterSubTitleEl = document.getElementById('printCenterSubTitle');
    const printJurisdictionNameEl = document.getElementById('printJurisdictionName');
    const printJurisdictionSubEntityEl = document.getElementById('printJurisdictionSubEntity');
    const printTableBodyEl = document.getElementById('printTableBody');

    if (printSessionDateEl) {
        printSessionDateEl.innerText = selectedDate.replace(/-/g, '/');
    }
    if (printCenterSubTitleEl) {
        printCenterSubTitleEl.innerText = `*** مندوبية ${currentJurisdiction.name} ***`;
    }
    if (printJurisdictionNameEl) {
        printJurisdictionNameEl.innerText = currentJurisdiction.name;
    }
    if (printJurisdictionSubEntityEl) {
        printJurisdictionSubEntityEl.innerText = currentJurisdiction.subEntity;
    }
    if (printTableBodyEl) {
        printTableBodyEl.innerHTML = '';
        if (finalRequests.length === 0) {
            printTableBodyEl.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; padding: 25px; font-weight: bold; color: #555555;">لا توجد طلبات مسجلة لهذه الجلسة</td>
                </tr>
            `;
        } else {
            finalRequests.forEach((req, idx) => {
                const indexText = String(idx + 1).padStart(2, '0');
                const oathText = req.purpose === 'delay' ? 'تأجيل' : (req.oathDate || '—');
                
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="width: 7%;">${indexText}</td>
                    <td class="font-monospace" style="width: 15%;">${req.caseNumber}</td>
                    <td style="width: 38%;">${req.parties}</td>
                    <td style="width: 25%;">${req.lawyerName}</td>
                    <td style="width: 15%;">${oathText}</td>
                `;
                printTableBodyEl.appendChild(tr);
            });
        }
    }

    if (window.lucide) lucide.createIcons();
}

// Notice Board Display
function renderAnnouncements() {
    const listContainer = document.getElementById('announcementsCompactList');
    if (!listContainer) return;
    listContainer.innerHTML = '';

    const activeList = announcements.filter(a => a.isActive);

    if (activeList.length === 0) {
        document.getElementById('announcementsAlertCard').classList.add('d-none');
        return;
    }

    document.getElementById('announcementsAlertCard').classList.remove('d-none');
    
    activeList.slice(0, 2).forEach(a => {
        const item = document.createElement('div');
        item.className = 'p-3 mb-2 rounded bg-light border-start border-success border-3';
        item.innerHTML = `
            <div class="d-flex gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-success mt-0.5"></i>
                <p class="mb-0 text-dark fw-bold text-sm">${a.text}</p>
            </div>
        `;
        listContainer.appendChild(item);
    });

    if (window.lucide) lucide.createIcons();
}

// Renders the dedicated Announcements view
function renderAnnouncementsDetails() {
    const container = document.getElementById('announcementsFullGrid');
    container.innerHTML = '';

    const activeList = announcements.filter(a => a.isActive);

    if (activeList.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i data-lucide="bell-off" class="w-16 h-16 text-muted opacity-25 d-block mx-auto mb-3"></i>
                <p class="text-muted fw-bold">لا توجد إعلانات نشطة حالياً</p>
            </div>
        `;
        if (window.lucide) lucide.createIcons();
        return;
    }

    activeList.forEach((a, idx) => {
        const div = document.createElement('div');
        div.className = 'p-4 mb-3 premium-card border-start border-success border-4';
        div.innerHTML = `
            <div class="d-flex align-items-start gap-3">
                <div class="p-2 rounded bg-success-subtle text-success">
                    <i data-lucide="alert-circle" class="w-6 h-6"></i>
                </div>
                <div class="flex-grow-1">
                    <p class="fs-5 fw-bold text-dark mb-3 leading-relaxed">${a.text}</p>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <small class="text-muted">
                            <i data-lucide="user" class="w-3.5 h-3.5 inline-block me-1"></i>
                            بواسطة: ${a.authorName}
                        </small>
                        <small class="text-muted">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 inline-block me-1"></i>
                            ${new Date(a.createdAt).toLocaleDateString('ar-DZ')}
                        </small>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(div);
    });

    if (window.lucide) lucide.createIcons();
}

// Renders Dashboard stats
function renderStats() {
    document.getElementById('statTotalRequests').innerText = requests.length;
    document.getElementById('statDelayRequests').innerText = requests.filter(r => r.purpose === 'delay').length;
    document.getElementById('statAdvanceRequests').innerText = requests.filter(r => r.purpose === 'advance').length;
}

// Renders Profile Tab
function renderProfile() {
    document.getElementById('profileLastName').value = currentUser.last_name;
    document.getElementById('profileFirstName').value = currentUser.first_name;
    document.getElementById('profileOathDate').value = currentUser.oath_date || '—';
    if (document.getElementById('profileEmail')) document.getElementById('profileEmail').value = currentUser.email || '—';
    if (document.getElementById('profilePhone')) document.getElementById('profilePhone').value = currentUser.phone || '—';
    
    let roleText = 'محامي';
    if (currentUser.role === 'admin') roleText = 'مسؤول';
    else if (currentUser.role === 'delegate') roleText = 'مندوب';
    document.getElementById('profileRole').value = roleText;
}

// Renders System Admin Settings
function renderSettings() {
    const listState = document.getElementById('settingListStateText');
    const toggleBtn = document.getElementById('settingToggleListBtn');
    
    if (systemSettings.isListOpen) {
        listState.innerText = 'حالة القائمة: مفتوحة';
        listState.className = 'fw-bold text-success';
        toggleBtn.innerText = 'غلق القائمة';
        toggleBtn.className = 'btn btn-danger';
    } else {
        listState.innerText = 'حالة القائمة: مغلقة';
        listState.className = 'fw-bold text-danger';
        toggleBtn.innerText = 'فتح القائمة';
        toggleBtn.className = 'btn btn-success';
    }

    // Render list elements for lists configs
    renderAdminSettingList('councilsListContainer', systemSettings.councils, 'council');
    renderAdminSettingList('courtsListContainer', systemSettings.courts, 'court');
    renderAdminSettingList('sectionsListContainer', systemSettings.sections, 'section');
    renderAdminSettingList('chambersListContainer', systemSettings.chambers, 'chamber');

    // Admin Announcements Manager
    renderAdminAnnouncements();
}

// Render Admin listing columns
function renderAdminSettingList(elementId, itemsList, type) {
    const container = document.getElementById(elementId);
    container.innerHTML = '';
    
    if (itemsList.length === 0) {
        container.innerHTML = '<p class="text-muted text-xs italic py-2">لا توجد عناصر مضافة</p>';
        return;
    }

    itemsList.forEach(item => {
        const div = document.createElement('div');
        div.className = 'd-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded border';
        div.innerHTML = `
            <span class="text-sm">${item}</span>
            <button onclick="deleteAdminSettingItem('${type}', '${item}')" class="btn btn-sm text-danger p-1 border-0">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        `;
        container.appendChild(div);
    });

    if (window.lucide) lucide.createIcons();
}

// Render Admin Announcements Manager
function renderAdminAnnouncements() {
    const container = document.getElementById('adminAnnouncementsList');
    container.innerHTML = '';

    if (announcements.length === 0) {
        container.innerHTML = '<p class="text-muted text-xs text-center py-4 italic">لا توجد إعلانات سابقة</p>';
        return;
    }

    announcements.forEach(a => {
        const div = document.createElement('div');
        div.className = `p-3 mb-2 rounded border d-flex justify-content-between align-items-center ${a.isActive ? 'bg-success-subtle border-success' : 'bg-light border-secondary opacity-75'}`;
        div.innerHTML = `
            <div>
                <p class="mb-1 fw-bold text-sm text-dark">${a.text}</p>
                <small class="text-muted text-xs">
                    ${new Date(a.createdAt).toLocaleString('ar-DZ')} • ${a.authorName}
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button onclick="toggleAnnouncementStatus('${a.id}', ${!a.isActive})" class="btn btn-xs ${a.isActive ? 'btn-warning' : 'btn-success'} py-1 px-2 text-xs">
                    ${a.isActive ? 'إيقاف' : 'تفعيل'}
                </button>
                <button onclick="deleteAnnouncement('${a.id}')" class="btn btn-sm text-danger border-0 p-1">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
    });

    if (window.lucide) lucide.createIcons();
}

// Render Users List (Admin only)
function renderUsersList() {
    const tbody = document.getElementById('usersTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (allUsers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted">لا يوجد مستخدمين مسجلين</td></tr>';
        return;
    }

    allUsers.forEach(u => {
        const roleText = u.role === 'admin' ? 'مسؤول' : u.role === 'delegate' ? 'مندوب' : 'محامي';
        const badgeClass = u.status === 'approved' ? 'bg-success' : u.status === 'pending' ? 'bg-warning text-dark' : 'bg-danger';
        const statusText = u.status === 'approved' ? 'نشط' : u.status === 'pending' ? 'قيد المراجعة' : 'مرفوض';
        const oathDisplay = u.isSyndicateMember ? '<span class="badge bg-success">عضو نقابة</span>' : (u.oathDate || '—');
        const isCurrentUser = (currentUser && u.id === currentUser.id);

        const tr = document.createElement('tr');
        tr.className = 'border-bottom';
        tr.innerHTML = `
            <td class="py-3 px-4">
                <div class="fw-bold text-dark">${u.lastName} ${u.firstName}</div>
                ${u.idCardUrl ? `
                    <button onclick="viewIDCard('${u.idCardUrl}')" class="btn btn-link text-success p-0 m-0 text-xs border-0 d-flex align-items-center gap-1">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        عرض البطاقة
                    </button>
                ` : ''}
            </td>
            <td class="py-3 px-4 text-muted text-sm">${oathDisplay}</td>
            <td class="py-3 px-4 text-muted text-sm">${roleText}</td>
            <td class="py-3 px-4">
                <span class="badge ${badgeClass}">${statusText}</span>
            </td>
            <td class="py-3 px-4">
                <div class="d-flex align-items-center gap-1">
                    <button onclick="editUserPrompt('${u.id}')" class="btn btn-sm btn-outline-success border-0 p-1" title="تعديل">
                        <i data-lucide="edit" class="w-4 h-4"></i>
                    </button>
                    ${!isCurrentUser ? `
                        <button onclick="deleteUserPrompt('${u.id}', '${u.lastName} ${u.firstName}')" class="btn btn-sm btn-outline-danger border-0 p-1" title="حذف">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    ` : '<span class="text-muted text-xs">أنت</span>'}
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });

    if (window.lucide) lucide.createIcons();
}

// Check notification badge count
function updateNotificationBadge() {
    const badge = document.getElementById('announcementBadge');
    if (!badge) return;
    
    const count = announcements.filter(a => a.isActive).length;
    if (count > 0) {
        badge.classList.remove('d-none');
        badge.innerText = count;
    } else {
        badge.classList.add('d-none');
    }
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
                const res = await fetch('../api.php?action=login', {
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
                const res = await fetch('../api.php?action=register', {
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

            const url = editingRequest ? '../api.php?action=edit_request' : '../api.php?action=add_request';
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
            await fetch('../api.php?action=logout');
            currentUser = null;
            window.location.href = '../index.php';
        });
    }
}

// Open Form Modal to Create/Edit requests
function openAddCaseModal(requestObj = null) {
    const modal = document.getElementById('addCaseModal');
    const title = document.getElementById('caseModalTitle');
    const form = document.getElementById('caseForm');

    form.reset();
    document.getElementById('forColleagueCheckbox').checked = false;
    document.getElementById('colleagueFieldsContainer').classList.add('d-none');
    document.getElementById('colleagueOathDate').disabled = false;

    // Reset purpose select
    const delayBtn = document.querySelector('.purpose-select-btn[data-purpose="delay"]');
    if (delayBtn) delayBtn.click();

    if (requestObj) {
        editingRequest = requestObj;
        title.innerText = 'تعديل القضية';
        document.getElementById('caseNumberInput').value = requestObj.caseNumber;
        document.getElementById('casePartiesInput').value = requestObj.parties;
        
        // Hide colleague check during editing
        document.getElementById('forColleagueCheckboxGroup').classList.add('d-none');

        const activePurposeBtn = document.querySelector(`.purpose-select-btn[data-purpose="${requestObj.purpose}"]`);
        if (activePurposeBtn) activePurposeBtn.click();
    } else {
        editingRequest = null;
        title.innerText = 'إضافة قضية جديدة';
        document.getElementById('forColleagueCheckboxGroup').classList.remove('d-none');
    }

    modal.classList.remove('d-none');
    modal.classList.add('d-flex');
}

function closeAddCaseModal() {
    const modal = document.getElementById('addCaseModal');
    modal.classList.add('d-none');
    modal.classList.remove('d-flex');
    editingRequest = null;
}

// Dialog trigger helper: custom confirm
function showConfirm(message, onConfirm) {
    const modal = document.getElementById('confirmModal');
    document.getElementById('confirmModalMessage').innerText = message;
    
    const confirmBtn = document.getElementById('confirmModalBtn');
    
    // Clear listeners from confirmBtn
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

    newConfirmBtn.addEventListener('click', () => {
        onConfirm();
        closeConfirm();
    });

    const cancelBtn = document.getElementById('confirmCancelBtn');
    cancelBtn.addEventListener('click', closeConfirm);

    modal.classList.remove('d-none');
    modal.classList.add('d-flex');
}

function closeConfirm() {
    const modal = document.getElementById('confirmModal');
    modal.classList.add('d-none');
    modal.classList.remove('d-flex');
}

// Edit / Delete buttons triggers from lists
function editRequestPrompt(id) {
    const req = requests.find(r => r.id === id);
    if (req) {
        openAddCaseModal(req);
    }
}

function deleteRequestPrompt(id, isHistory = false) {
    const msg = isHistory ? 'هل تريد حذف هذا السجل نهائياً من الأرشيف؟' : 'هل أنت متأكد من الحذف؟';
    showConfirm(msg, async () => {
        try {
            const res = await fetch(`../api.php?action=delete_request&id=${id}`);
            const data = await res.json();
            if (res.ok) {
                showToast('تم الحذف بنجاح', 'success');
                if (isHistory) {
                    await fetchRequests(true);
                } else {
                    await fetchRequests(false);
                }
            } else {
                showToast(data.error, 'error');
            }
        } catch (e) {
            console.error(e);
            showToast('خطأ في الحذف', 'error');
        }
    });
}

// Toast Notifications helper
function showToast(message, type = 'success') {
    const toast = document.getElementById('toastNotification');
    const textSpan = document.getElementById('toastMessageText');
    const iconDiv = document.getElementById('toastIcon');

    textSpan.innerText = message;
    toast.className = `toast-toast fixed-bottom mb-4 start-50 translate-middle-x z-3 px-4 py-3 rounded border shadow-lg d-flex align-items-center gap-2 text-white`;

    if (type === 'error') {
        toast.classList.add('bg-danger', 'border-danger');
        iconDiv.innerHTML = '<i data-lucide="alert-circle" class="w-5 h-5"></i>';
    } else if (type === 'info') {
        toast.classList.add('bg-primary', 'border-primary');
        iconDiv.innerHTML = '<i data-lucide="bell" class="w-5 h-5"></i>';
    } else {
        toast.classList.add('bg-success', 'border-success');
        iconDiv.innerHTML = '<i data-lucide="check-circle" class="w-5 h-5"></i>';
    }

    if (window.lucide) lucide.createIcons();
    
    toast.classList.remove('d-none');
    setTimeout(() => {
        toast.classList.add('d-none');
    }, 4000);
}

// User Approvals actions
async function updateUserStatus(id, status) {
    try {
        const res = await fetch('../api.php?action=update_user_status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, status })
        });
        if (res.ok) {
            showToast('تم تحديث حالة المستخدم بنجاح', 'success');
            await fetchUsers();
        } else {
            const data = await res.json();
            showToast(data.error, 'error');
        }
    } catch (e) {
        console.error(e);
    }
}

// ==================== USER ACCOUNT CRUD (Admin) ====================

// State: tracking which user is being edited
let editingUser = null;

// Open the user modal (for creating new or editing existing)
function openUserModal(userObj = null) {
    const modal = document.getElementById('userModal');
    const oldForm = document.getElementById('userForm');
    
    // Clone to strip listeners
    const newForm = oldForm.cloneNode(true);
    oldForm.parentNode.replaceChild(newForm, oldForm);
    newForm.id = 'userForm';

    const title = document.getElementById('userModalTitle');
    const errDiv = document.getElementById('userModalError');
    const pwdRequired = document.getElementById('userPasswordRequired');
    const pwdHint = document.getElementById('userPasswordHint');

    newForm.reset();
    errDiv.classList.add('d-none');
    document.getElementById('userIsSyndicateMember').checked = false;
    document.getElementById('userOathDateInput').disabled = false;
    document.getElementById('userOathDateInput').required = true;

    if (userObj) {
        editingUser = userObj;
        title.innerText = 'تعديل بيانات الحساب';
        document.getElementById('userIdInput').value = userObj.id;
        document.getElementById('userLastNameInput').value = userObj.lastName;
        document.getElementById('userFirstNameInput').value = userObj.firstName;
        document.getElementById('userEmailInput').value = userObj.email || '';
        document.getElementById('userPhoneInput').value = userObj.phone || '';
        document.getElementById('userRoleInput').value = userObj.role;
        document.getElementById('userStatusInput').value = userObj.status;

        if (userObj.isSyndicateMember) {
            document.getElementById('userIsSyndicateMember').checked = true;
            document.getElementById('userOathDateInput').disabled = true;
            document.getElementById('userOathDateInput').required = false;
            document.getElementById('userOathDateInput').value = '';
        } else {
            document.getElementById('userOathDateInput').value = userObj.oathDate || '';
        }

        pwdRequired.classList.add('d-none');
        pwdHint.innerText = '(اتركها فارغة للإبقاء على كلمة السر الحالية)';
        document.getElementById('userPasswordInput').required = false;
    } else {
        editingUser = null;
        title.innerText = 'إضافة حساب جديد';
        document.getElementById('userIdInput').value = '';
        pwdRequired.classList.remove('d-none');
        pwdHint.innerText = '';
        document.getElementById('userPasswordInput').required = true;
    }

    modal.classList.remove('d-none');
    modal.classList.add('d-flex');
    if (window.lucide) lucide.createIcons();

    const syndicateCheck = document.getElementById('userIsSyndicateMember');
    const oathInput = document.getElementById('userOathDateInput');
    syndicateCheck.onchange = (e) => {
        if (e.target.checked) {
            oathInput.disabled = true;
            oathInput.required = false;
            oathInput.value = '';
        } else {
            oathInput.disabled = false;
            oathInput.required = true;
        }
    };

    newForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const errDiv2 = document.getElementById('userModalError');
        errDiv2.classList.add('d-none');

        const payload = {
            lastName: document.getElementById('userLastNameInput').value.trim(),
            firstName: document.getElementById('userFirstNameInput').value.trim(),
            email: document.getElementById('userEmailInput')?.value.trim() || '',
            phone: document.getElementById('userPhoneInput')?.value.trim() || '',
            isSyndicateMember: document.getElementById('userIsSyndicateMember').checked,
            oathDate: document.getElementById('userOathDateInput').value.trim(),
            role: document.getElementById('userRoleInput').value,
            status: document.getElementById('userStatusInput').value,
            password: document.getElementById('userPasswordInput').value
        };

        const isEditing = !!editingUser;
        if (isEditing) payload.id = editingUser.id;

        const url = isEditing ? '../api.php?action=edit_user' : '../api.php?action=add_user';

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (res.ok && data.success) {
                showToast(isEditing ? 'تم تحديث بيانات الحساب بنجاح' : 'تم إنشاء الحساب بنجاح', 'success');
                closeUserModal();
                await fetchUsers();
            } else {
                errDiv2.innerText = data.error || 'حدث خطأ غير معروف';
                errDiv2.classList.remove('d-none');
            }
        } catch (err) {
            console.error(err);
            errDiv2.innerText = 'خطأ في الاتصال بالخادم';
            errDiv2.classList.remove('d-none');
        }
    });
}

function closeUserModal() {
    const modal = document.getElementById('userModal');
    modal.classList.add('d-none');
    modal.classList.remove('d-flex');
    editingUser = null;
}

// Trigger edit for a user by ID
function editUserPrompt(id) {
    const user = allUsers.find(u => u.id === id);
    if (user) openUserModal(user);
}

// Trigger delete for a user by ID
function deleteUserPrompt(id, name) {
    showConfirm(`هل أنت متأكد من حذف حساب الأستاذ ${name} نهائياً؟`, async () => {
        try {
            const res = await fetch('../api.php?action=delete_user', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                showToast('تم حذف الحساب بنجاح', 'success');
                await fetchUsers();
            } else {
                showToast(data.error || 'حدث خطأ أثناء الحذف', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('خطأ في الاتصال بالخادم', 'error');
        }
    });
}

// View lawyer's ID Card uploaded
function viewIDCard(url) {
    const modal = document.getElementById('idCardViewerModal');
    document.getElementById('idCardImage').src = url;
    modal.classList.remove('d-none');
    modal.classList.add('d-flex');
}

function closeIDCardViewer() {
    const modal = document.getElementById('idCardViewerModal');
    modal.classList.add('d-none');
    modal.classList.remove('d-flex');
}

// Settings changes handlers
async function updateAdminListStatus(isOpen) {
    try {
        const res = await fetch('../api.php?action=update_list_status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ isOpen })
        });
        if (res.ok) {
            showToast('تم تغيير حالة القائمة بنجاح', 'success');
            await fetchSettings();
            renderSettings();
        }
    } catch (e) {
        console.error(e);
    }
}

async function addAdminSettingItem(type) {
    let inputEl, url;
    let payload = {};

    if (type === 'council') {
        inputEl = document.getElementById('newCouncilInput');
        url = '../api.php?action=add_council';
        payload.name = inputEl.value;
    } else if (type === 'court') {
        inputEl = document.getElementById('newCourtInput');
        const councilSelect = document.getElementById('newCourtCouncilSelect');
        url = '../api.php?action=add_court';
        payload.name = inputEl.value;
        payload.council = councilSelect.value;
    } else if (type === 'section') {
        inputEl = document.getElementById('newSectionInput');
        url = '../api.php?action=add_section';
        payload.name = inputEl.value;
    } else if (type === 'chamber') {
        inputEl = document.getElementById('newChamberInput');
        url = '../api.php?action=add_chamber';
        payload.name = inputEl.value;
    }

    if (!inputEl || !inputEl.value.trim()) return;

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (res.ok) {
            showToast('تم الإضافة بنجاح', 'success');
            inputEl.value = '';
            
            // Reload settings & render UI lists
            await fetchSettings();
            renderSettings();
        } else {
            showToast(data.error, 'error');
        }
    } catch (e) {
        console.error(e);
    }
}

async function deleteAdminSettingItem(type, name) {
    let url;
    if (type === 'council') url = '../api.php?action=delete_council';
    else if (type === 'court') url = '../api.php?action=delete_court';
    else if (type === 'section') url = '../api.php?action=delete_section';
    else if (type === 'chamber') url = '../api.php?action=delete_chamber';

    showConfirm(`هل أنت متأكد من حذف ${name}؟`, async () => {
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name })
            });
            if (res.ok) {
                showToast('تم الحذف بنجاح', 'success');
                await fetchSettings();
                renderSettings();
            }
        } catch (e) {
            console.error(e);
        }
    });
}

// Announcements Posting
async function postAnnouncement() {
    const text = document.getElementById('newAnnouncementText').value;
    if (!text.trim()) return;

    try {
        const res = await fetch('../api.php?action=add_announcement', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ text })
        });
        if (res.ok) {
            showToast('تم نشر الإعلان بنجاح', 'success');
            document.getElementById('newAnnouncementText').value = '';
            
            // Toggle form view collapse
            document.getElementById('addAnnouncementContainer').classList.add('d-none');
            
            await fetchAnnouncements();
            renderSettings();
        } else {
            const data = await res.json();
            showToast(data.error, 'error');
        }
    } catch (e) {
        console.error(e);
    }
}

async function toggleAnnouncementStatus(id, isActive) {
    try {
        const res = await fetch('../api.php?action=toggle_announcement', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, isActive })
        });
        if (res.ok) {
            showToast('تم تغيير حالة الإعلان بنجاح', 'success');
            await fetchAnnouncements();
            renderSettings();
        }
    } catch (e) {
        console.error(e);
    }
}

async function deleteAnnouncement(id) {
    showConfirm('هل أنت متأكد من حذف هذا الإعلان؟', async () => {
        try {
            const res = await fetch(`../api.php?action=delete_announcement&id=${id}`);
            if (res.ok) {
                showToast('تم حذف الإعلان بنجاح', 'success');
                await fetchAnnouncements();
                renderSettings();
            }
        } catch (e) {
            console.error(e);
        }
    });
}

// Archive and Clear current active list
function archiveCurrentList() {
    showConfirm('هل تريد نقل القائمة الحالية إلى الأرشيف؟', async () => {
        try {
            const res = await fetch('../api.php?action=archive_requests');
            if (res.ok) {
                showToast('تمت أرشفة القائمة بنجاح', 'success');
                await fetchRequests(false);
                await fetchRequests(true);
            }
        } catch (e) {
            console.error(e);
        }
    });
}

function clearCurrentList() {
    showConfirm('هل أنت متأكد من مسح القائمة بالكامل؟', async () => {
        try {
            const res = await fetch('../api.php?action=clear_requests');
            if (res.ok) {
                showToast('تم مسح القائمة بنجاح', 'success');
                await fetchRequests(false);
            }
        } catch (e) {
            console.error(e);
        }
    });
}

// PDF Download export using html2pdf - generates from officialPrintTemplate
function handleDownloadPDF() {
    const listToProcess = currentTab === 'archive' ? archiveRequests : requests;
    const finalRequests = processRequests(listToProcess);

    if (finalRequests.length === 0) {
        showToast('لا توجد طلبات لتنزيلها', 'error');
        return;
    }

    // Ensure the print template is populated with latest data
    // (renderRequestsTable already does this, but force a refresh here)
    const printSessionDateEl = document.getElementById('printSessionDate');
    const printCenterSubTitleEl = document.getElementById('printCenterSubTitle');
    const printJurisdictionNameEl = document.getElementById('printJurisdictionName');
    const printJurisdictionSubEntityEl = document.getElementById('printJurisdictionSubEntity');
    const printTableBodyEl = document.getElementById('printTableBody');

    if (printSessionDateEl) printSessionDateEl.innerText = selectedDate.replace(/-/g, '/');
    if (printCenterSubTitleEl) printCenterSubTitleEl.innerText = `*** مندوبية ${currentJurisdiction.name} ***`;
    if (printJurisdictionNameEl) printJurisdictionNameEl.innerText = currentJurisdiction.name;
    if (printJurisdictionSubEntityEl) printJurisdictionSubEntityEl.innerText = currentJurisdiction.subEntity;

    if (printTableBodyEl) {
        printTableBodyEl.innerHTML = '';
        finalRequests.forEach((req, idx) => {
            const indexText = String(idx + 1).padStart(2, '0');
            const oathText = req.purpose === 'delay' ? 'تأجيل' : (req.oathDate || '—');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="width: 7%;">${indexText}</td>
                <td style="width: 15%; font-family: monospace;">${req.caseNumber}</td>
                <td style="width: 38%;">${req.parties}</td>
                <td style="width: 25%; font-weight: 900;">${req.lawyerName}</td>
                <td style="width: 15%;">${oathText}</td>
            `;
            printTableBodyEl.appendChild(tr);
        });
    }

    const pdfTarget = document.getElementById('officialPrintTemplate');

    // html2canvas cannot capture off-screen elements.
    // Temporarily bring the element on-screen at top-left, above everything.
    const savedStyle = pdfTarget.getAttribute('style') || '';
    pdfTarget.setAttribute('style',
        'position: fixed; left: 0; top: 0; width: 794px; z-index: 99999; background: #ffffff;'
    );

    const filename = `قائمة_${currentJurisdiction.name}_${currentJurisdiction.subEntity}_${selectedDate}.pdf`;

    const opt = {
        margin: 0,
        filename,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: {
            scale: 2,
            useCORS: true,
            logging: false,
            allowTaint: true,
            windowWidth: 794
        },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
    };

    html2pdf().set(opt).from(pdfTarget).save().then(() => {
        // Restore off-screen positioning
        pdfTarget.setAttribute('style', savedStyle);
        showToast('تم تحميل الملف بنجاح', 'success');
    });
}

// Print Handler
function handlePrint() {
    window.focus();
    setTimeout(() => {
        window.print();
    }, 100);
}

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
