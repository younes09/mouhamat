// Authentication view logic (index.php)

// Theme Control
let isDarkMode = localStorage.getItem('darkMode') === 'true';

function applyTheme() {
    const darkToggle = document.getElementById('darkToggle');
    if (isDarkMode) {
        document.documentElement.classList.add('dark');
        if (darkToggle) darkToggle.innerHTML = '<i data-lucide="sun" class="w-5 h-5"></i>';
    } else {
        document.documentElement.classList.remove('dark');
        if (darkToggle) darkToggle.innerHTML = '<i data-lucide="moon" class="w-5 h-5"></i>';
    }
    if (window.lucide) lucide.createIcons();
}

function toggleDarkMode() {
    isDarkMode = !isDarkMode;
    localStorage.setItem('darkMode', isDarkMode);
    applyTheme();
}

document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.dir = 'rtl';
    document.documentElement.lang = 'ar';
    
    // Apply theme
    applyTheme();

    // Theme toggling
    const darkToggle = document.getElementById('darkToggle');
    if (darkToggle) {
        darkToggle.addEventListener('click', toggleDarkMode);
    }
    
    // Lucide Icons initialization
    if (window.lucide) lucide.createIcons();

    // Tab switcher between Login & Register
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

    // Role Selector Buttons in Login
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

    // Login Form Submit handler
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(loginForm);
            const errDiv = document.getElementById('loginErrorAlert');
            errDiv.classList.add('d-none');

            try {
                const res = await fetch('api/api.php?action=login', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                
                if (res.ok && data.success) {
                    window.location.href = 'dashboard/';
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
                const res = await fetch('api/api.php?action=register', {
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

    // Register syndicate checkbox behavior
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
        guestLoginBtn.addEventListener('click', async () => {
            // Set session for guest
            try {
                const res = await fetch('api/api.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        firstName: 'زائر',
                        lastName: '',
                        role: 'guest'
                    })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    window.location.href = 'dashboard/';
                } else {
                    showToast('خطأ في دخول الزائر', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('خطأ في الاتصال بالخادم', 'error');
            }
        });
    }

    // PWA Install Prompt
    initPwaInstall();
});

// Toast Notifications Helper
function showToast(message, type = 'success') {
    const toast = document.getElementById('toastNotification');
    const textSpan = document.getElementById('toastMessageText');
    const iconDiv = document.getElementById('toastIcon');

    if (!toast || !textSpan || !iconDiv) return;

    textSpan.innerText = message;
    toast.className = `toast-toast fixed-bottom mb-4 start-50 translate-middle-x z-3 px-4 py-3 rounded border shadow-lg d-flex align-items-center gap-2 text-white`;

    if (type === 'error') {
        toast.classList.add('bg-danger', 'border-danger');
        iconDiv.innerHTML = '<i data-lucide="alert-circle" class="w-5 h-5"></i>';
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

// PWA Install Helper
let deferredPrompt = null;
function initPwaInstall() {
    const installBtn = document.getElementById('installAppBtn');
    const iframeMsg = document.getElementById('iframeInstallMsg');
    
    // Check if running inside iframe
    const isIframe = window.self !== window.top;

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        
        if (isIframe) {
            if (iframeMsg) iframeMsg.classList.remove('d-none');
        } else {
            if (installBtn) installBtn.classList.remove('d-none');
        }
    });

    if (installBtn) {
        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                deferredPrompt = null;
                installBtn.classList.add('d-none');
            }
        });
    }

    window.addEventListener('appinstalled', () => {
        deferredPrompt = null;
        if (installBtn) installBtn.classList.add('d-none');
        showToast('تم تثبيت التطبيق بنجاح!', 'success');
    });
}
