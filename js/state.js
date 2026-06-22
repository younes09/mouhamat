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

// State Management
let currentUser = null;
let requests = [];
let archiveRequests = [];
let announcements = [];
let systemSettings = {
    isListOpen: true,
    restrictedDays: '5,6',
    startTime: '06:00',
    endTime: '14:30',
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
