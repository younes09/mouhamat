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
        toggleBtn.className = 'btn btn-danger btn-sm fw-bold px-3';
    } else {
        listState.innerText = 'حالة القائمة: مغلقة';
        listState.className = 'fw-bold text-danger';
        toggleBtn.innerText = 'فتح القائمة';
        toggleBtn.className = 'btn btn-success btn-sm fw-bold px-3';
    }

    // Populate constraints values
    const startTimeInput = document.getElementById('constraintStartTime');
    const endTimeInput = document.getElementById('constraintEndTime');
    if (startTimeInput && endTimeInput) {
        startTimeInput.value = systemSettings.startTime || '06:00';
        endTimeInput.value = systemSettings.endTime || '14:30';
    }

    const restrictedDays = (systemSettings.restrictedDays !== undefined ? systemSettings.restrictedDays : '5,6').split(',');
    for (let i = 0; i <= 6; i++) {
        const checkbox = document.getElementById(`day_${i}`);
        if (checkbox) {
            checkbox.checked = restrictedDays.includes(String(i));
        }
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

// Open standalone print page in a new tab with current jurisdiction + date
function openPrintPage() {
    const params = new URLSearchParams({
        date:     selectedDate,
        jur_type: currentJurisdiction.type,
        jur_name: currentJurisdiction.name,
        jur_sub:  currentJurisdiction.subEntity
    });
    window.open(`../print.php?${params.toString()}`, '_blank');
}

