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

async function saveAdminConstraints(event) {
    event.preventDefault();
    const startTime = document.getElementById('constraintStartTime').value;
    const endTime = document.getElementById('constraintEndTime').value;
    
    const restrictedDays = [];
    for (let i = 0; i <= 6; i++) {
        const checkbox = document.getElementById(`day_${i}`);
        if (checkbox && checkbox.checked) {
            restrictedDays.push(i);
        }
    }

    try {
        const res = await fetch('../api.php?action=update_constraints', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ startTime, endTime, restrictedDays })
        });
        if (res.ok) {
            showToast('تم حفظ القيود بنجاح', 'success');
            await fetchSettings();
            renderSettings();
        } else {
            const data = await res.json();
            showToast(data.error || 'فشل حفظ القيود', 'error');
        }
    } catch (e) {
        console.error(e);
        showToast('خطأ في الاتصال بالخادم', 'error');
    }
}
