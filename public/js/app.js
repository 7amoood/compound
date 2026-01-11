/**
 * خدمات المجمع - Community Services App
 * Core JavaScript Helper Functions
 */

// API Base URL
const API_BASE = '/api';

// Get auth token from localStorage
function getToken() {
    return localStorage.getItem('token');
}

// Get CSRF token from meta tag
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

/**
 * API Call Wrapper
 * @param {string} endpoint - API endpoint
 * @param {string} method - HTTP method
 * @param {object} data - Request body data
 * @returns {Promise<object>} - Response data
 */
async function apiCall(endpoint, method = 'GET', data = null) {
    const url = endpoint.startsWith('/api') ? endpoint : API_BASE + endpoint;

    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        }
    };

    // Add auth token if available
    const token = getToken();
    if (token) {
        options.headers['Authorization'] = `Bearer ${token}`;
    }

    // Add body for non-GET requests
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, options);
        const result = await response.json();

        // Handle 401 Unauthorized
        if (response.status === 401) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/login';
            return { success: false, message: 'انتهت صلاحية الجلسة' };
        }

        return result;
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, message: 'حدث خطأ في الاتصال بالخادم' };
    }
}

/**
 * Show Toast Notification
 * @param {string} message - Toast message
 * @param {string} type - 'success', 'error', 'info', 'warning'
 */
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const colors = {
        success: 'bg-emerald-500',
        error: 'bg-red-500',
        warning: 'bg-amber-500',
        info: 'bg-primary'
    };

    const icons = {
        success: 'check_circle',
        error: 'error',
        warning: 'warning',
        info: 'info'
    };

    const toast = document.createElement('div');
    toast.className = `toast-enter pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl ${colors[type] || colors.info} text-white shadow-lg max-w-sm`;
    toast.innerHTML = `
        <span class="material-symbols-outlined text-[20px]">${icons[type] || icons.info}</span>
        <span class="text-sm font-medium">${message}</span>
    `;

    container.appendChild(toast);

    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('toast-enter');
        toast.classList.add('toast-exit');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Show Modal
 * @param {string} modalId - Modal element ID
 * @param {object} data - Optional data to pass
 */
function showModal(modalId, data = null, replace = false) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        // Manage history state
        if (!history.state || history.state.modalId !== modalId) {
            if (replace) {
                history.replaceState({ modalId: modalId }, '');
            } else {
                history.pushState({ modalId: modalId }, '');
            }
        }

        // Trigger custom event with data
        if (data) {
            modal.dispatchEvent(new CustomEvent('modalOpen', { detail: data }));
        }
    }
}

/**
 * Close Modal
 * @param {string} modalId - Modal element ID
 * @param {boolean} syncHistory - Whether to call history.back()
 */
function closeModal(modalId, syncHistory = true) {
    const modal = document.getElementById(modalId);
    if (modal && !modal.classList.contains('hidden')) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');

        // If we closed it manually, and it matches current history, pop it
        if (syncHistory && history.state && history.state.modalId === modalId) {
            history.back();
        }

        // Trigger custom event
        modal.dispatchEvent(new CustomEvent('modalClose'));
    }
}

// Global popstate listener to handle back button for modals
window.addEventListener('popstate', function (event) {
    const stateModalId = (event.state && event.state.modalId) ? event.state.modalId : null;

    // Close any modal that isn't the one we just navigated to
    document.querySelectorAll('[id$="-modal"]:not(.hidden)').forEach(modal => {
        if (modal.id !== stateModalId) {
            modal.classList.add('hidden');
            modal.dispatchEvent(new CustomEvent('modalClose'));
        }
    });

    // Show the modal we navigated to, if any
    if (stateModalId) {
        const targetModal = document.getElementById(stateModalId);
        if (targetModal) {
            targetModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    } else {
        document.body.classList.remove('overflow-hidden');
    }
});

/**
 * Logout User
 */
async function logout() {
    try {
        await apiCall('/api/logout', 'POST');
    } catch (e) {
        // Ignore errors
    }

    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = '/login';
}

/**
 * Format Date in Arabic
 * @param {string} dateString - Date string
 * @returns {string} - Formatted date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * Format Currency
 * @param {number} amount - Amount
 * @returns {string} - Formatted currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('ar-EG', {
        style: 'currency',
        currency: 'EGP',
        minimumFractionDigits: 0
    }).format(amount);
}

/**
 * Notification System
 */

// Load unread notifications count
async function loadUnreadNotificationsCount() {
    try {
        const response = await apiCall('/api/notifications/unread-count', 'GET');
        if (response.success) {
            updateNotificationBadge(response.count);
        }
    } catch (e) {
        console.log('Could not load notifications count');
    }
}

// Update notification badge in UI and trigger system notification
function updateNotificationBadge(count) {
    const badges = document.querySelectorAll('.notification-badge');
    badges.forEach(badge => {
        if (count > 0) {
            badge.textContent = count > 9 ? '9+' : count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    });

    // Check if we have new notifications
    const lastCount = parseInt(localStorage.getItem('notification_count') || '0');
    if (count > lastCount && count > 0) {
        // Try to fetch latest notification details
        fetchLatestNotification();
    }
    localStorage.setItem('notification_count', count);
}

// Fetch latest notification for system alert
async function fetchLatestNotification() {
    try {
        const response = await apiCall('/api/notifications', 'GET');
        if (response.success && response.notifications.data.length > 0) {
            const latest = response.notifications.data[0];
            // Only show if it's very recent (less than 1 min ago) and unread
            const notifTime = new Date(latest.created_at).getTime();
            const now = new Date().getTime();

            if (latest.is_read === false && (now - notifTime) < 60000) {
                showSystemNotification(latest.title, latest.body);
            }
        }
    } catch (e) {
        console.log('Error fetching notification for alert');
    }
}

// Show system notification
function showSystemNotification(title, body) {
    if (!('Notification' in window)) return;

    if (Notification.permission === 'granted') {
        new Notification(title, {
            body: body,
            icon: '/icons/icon-192x192.png',
            dir: 'rtl'
        });
    } else if (Notification.permission !== 'denied') {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                new Notification(title, {
                    body: body,
                    icon: '/icons/icon-192x192.png',
                    dir: 'rtl'
                });
            }
        });
    }
}

// Load notifications list
async function loadNotifications(page = 1) {
    try {
        const response = await apiCall(`/api/notifications?page=${page}`, 'GET');
        if (response.success) {
            return response.notifications;
        }
    } catch (e) {
        console.log('Could not load notifications');
    }
    return { data: [], current_page: 1, last_page: 1 };
}

// Mark notification as read
async function markNotificationRead(notificationId) {
    try {
        await apiCall(`/api/notifications/${notificationId}/read`, 'POST');
        loadUnreadNotificationsCount();
    } catch (e) {
        console.log('Could not mark notification as read');
    }
}

// Mark all notifications as read
async function markAllNotificationsRead() {
    try {
        await apiCall('/api/notifications/read-all', 'POST');
        updateNotificationBadge(0);
        showToast('تم تحديد جميع الإشعارات كمقروءة', 'success');
    } catch (e) {
        console.log('Could not mark all as read');
    }
}

// Get notification icon by type
function getNotificationIcon(type) {
    const icons = {
        'new_request': 'add_circle',
        'new_proposal': 'local_offer',
        'proposal_accepted': 'check_circle',
        'work_started': 'play_circle',
        'work_completed': 'task_alt',
        'new_review': 'star'
    };
    return icons[type] || 'notifications';
}

// Get notification color by type
function getNotificationColor(type) {
    const colors = {
        'new_request': 'text-blue-500',
        'new_proposal': 'text-green-500',
        'proposal_accepted': 'text-emerald-500',
        'work_started': 'text-amber-500',
        'work_completed': 'text-green-600',
        'new_review': 'text-yellow-500'
    };
    return colors[type] || 'text-primary';
}

// Close modals on escape key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('[id$="-modal"]:not(.hidden)').forEach(modal => {
            closeModal(modal.id);
        });
    }
});

// Check authentication on page load
document.addEventListener('DOMContentLoaded', function () {
    const publicPages = ['/login', '/'];
    const currentPath = window.location.pathname;

    if (!publicPages.includes(currentPath) && !getToken()) {
        window.location.href = '/login';
        return;
    }

    // Check if running as PWA
    if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
        document.body.classList.add('is-pwa');
    }

    // Request Notification Permission if logged in
    if (getToken()) {
        requestNotificationPermission();
        // Poll for notifications every 30 seconds
        setInterval(loadUnreadNotificationsCount, 30000);
    }
});

/**
 * Handle Notification Permissions
 */
async function requestNotificationPermission() {
    if (!('Notification' in window)) return;

    if (Notification.permission === 'default') {
        // We can't auto-prompt anymore in most browsers without user gesture
        // but for a PWA it's sometimes okay or we can add a button in settings
        console.log('Notification permission is default');
    } else if (Notification.permission === 'granted') {
        setupPush();
    }
}

async function setupPush() {
    if (!('serviceWorker' in navigator)) return;

    try {
        const registration = await navigator.serviceWorker.ready;
        // In a real app with FCM, you would use firebase.messaging()
        // but for now we'll just simulate getting a token if using a mock 
        // OR just rely on the browser's pushManager

        // This is a placeholder for getting a real token from FCM or VAPID
        console.log('Push messaging is ready');
    } catch (e) {
        console.error('Push setup failed:', e);
    }
}

window.enableNotifications = async function () {
    const permission = await Notification.requestPermission();
    if (permission === 'granted') {
        showToast('تم تفعيل الإشعارات بنجاح!', 'success');
        setupPush();
    }
};


/**
 * PWA Installation Logic
 */
let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent Chrome 67 and earlier from automatically showing the prompt
    e.preventDefault();
    // Stash the event so it can be triggered later.
    deferredPrompt = e;

    // Show header install buttons if not already in PWA mode
    if (!(window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone)) {
        const headerBtns = document.querySelectorAll('#header-install-btn');
        headerBtns.forEach(btn => btn.classList.remove('hidden'));
    }
});

window.triggerPwaInstall = async function () {
    if (!deferredPrompt) {
        showToast('التطبيق مثبت بالفعل أو غير متاح للتثبيت', 'info');
        return;
    }

    // Show the prompt
    deferredPrompt.prompt();

    // Wait for the user to respond to the prompt
    const { outcome } = await deferredPrompt.userChoice;
    console.log(`User response to install prompt: ${outcome}`);

    if (outcome === 'accepted') {
        showToast('جاري تثبيت التطبيق...', 'success');
    }

    deferredPrompt = null;

    // Hide all install buttons
    const btns = document.querySelectorAll('#header-install-btn');
    btns.forEach(btn => btn.classList.add('hidden'));
};

window.addEventListener('appinstalled', (evt) => {
    console.log('PWA was installed');
    const btns = document.querySelectorAll('#header-install-btn');
    btns.forEach(btn => btn.classList.add('hidden'));
    showToast('تم تثبيت التطبيق بنجاح! 🎉', 'success');
});
