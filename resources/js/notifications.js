/**
 * Realtime Notification Manager
 * Integrates Laravel Echo (Reverb) with Header Dropdown, Toasts, and Notification Center.
 */

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

export function initNotifications() {
    const userMeta = document.querySelector('meta[name="user-id"]');
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!userMeta) return;

    const userId = userMeta.getAttribute('content');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
    if (!userId) return;

    // 1. Setup AJAX handlers for Mark as Read & Mark All Read
    setupReadHandlers(csrfToken);

    // 2. Setup audio alarm & browser push notification preferences
    initNotificationPreferences();

    // 3. Initialize Laravel Echo Listener
    if (!window.Echo) {
        console.info('Laravel Echo is not available. Realtime notifications inactive.');
        return;
    }

    try {
        window.Echo.private(`App.Models.User.${userId}`)
            .notification((notification) => {
                handleIncomingNotification(notification, csrfToken);
            });
    } catch (err) {
        console.warn('Could not subscribe to private notification channel:', err);
    }
}

function handleIncomingNotification(notification, csrfToken) {
    // 1. Increment and update all unread badges
    incrementUnreadCount();

    // 2. Prepend item to Header Dropdown
    addNotificationToDropdown(notification, csrfToken);

    // 3. Show small toast popup
    showNotificationToast(notification);

    // 4. If user is currently on /notifications, prepend to the page list
    addNotificationToPage(notification, csrfToken);

    // 5. Browser push notification (if permission granted)
    triggerBrowserNotification(notification);

    // 6. Play audio alarm chime (if enabled by user)
    playReminderSound(notification);
}

function getUnreadCount() {
    const textEl = document.getElementById('header-unread-count-text');
    if (!textEl) return 0;
    const val = parseInt(textEl.textContent.replace(/\D/g, ''), 10);
    return isNaN(val) ? 0 : val;
}

function setUnreadCount(count) {
    const safeCount = Math.max(0, count);

    // Header Bell Badge
    const headerBadge = document.getElementById('header-notification-badge');
    const headerText = document.getElementById('header-unread-count-text');
    if (headerText) {
        headerText.textContent = safeCount > 99 ? '99+' : safeCount;
    }
    if (headerBadge) {
        if (safeCount > 0) {
            headerBadge.classList.remove('d-none');
        } else {
            headerBadge.classList.add('d-none');
        }
    }

    // Dropdown Pill
    const dropdownPill = document.getElementById('dropdown-unread-pill');
    const dropdownText = document.getElementById('dropdown-unread-count-text');
    if (dropdownText) dropdownText.textContent = safeCount;
    if (dropdownPill) {
        if (safeCount > 0) {
            dropdownPill.classList.remove('d-none');
        } else {
            dropdownPill.classList.add('d-none');
        }
    }

    // Sidebar Badges
    document.querySelectorAll('.sidebar-unread-badge').forEach((badge) => {
        const textEl = badge.querySelector('.sidebar-unread-count-text') || badge;
        textEl.textContent = safeCount;
        if (safeCount > 0) {
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    });

    // Profile Dropdown Badges
    document.querySelectorAll('.profile-unread-badge').forEach((badge) => {
        const textEl = badge.querySelector('.profile-unread-count-text') || badge;
        textEl.textContent = safeCount;
        if (safeCount > 0) {
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    });

    // Notification Center Page Elements
    const ncPill = document.getElementById('nc-unread-pill');
    if (ncPill) {
        ncPill.textContent = `${safeCount} belum dibaca`;
        if (safeCount > 0) {
            ncPill.classList.remove('d-none');
        } else {
            ncPill.classList.add('d-none');
        }
    }
    const ncFilterBadge = document.getElementById('nc-filter-unread-badge');
    if (ncFilterBadge) {
        ncFilterBadge.textContent = safeCount;
        if (safeCount > 0) {
            ncFilterBadge.classList.remove('d-none');
        } else {
            ncFilterBadge.classList.add('d-none');
        }
    }
}

function incrementUnreadCount() {
    setUnreadCount(getUnreadCount() + 1);
}

function decrementUnreadCount() {
    setUnreadCount(getUnreadCount() - 1);
}

function getTypeColor(type) {
    if (type === 'overdue' || (type && type.includes('Overdue'))) {
        return '#EF233C';
    }
    if (type === 'reminder_10m' || (type && type.includes('TenMinutes'))) {
        return '#E11D48';
    }
    if (type === 'reminder_1h' || (type && type.includes('OneHour'))) {
        return '#4F46E5';
    }
    if (type === 'due_today' || (type && type.includes('DueToday'))) {
        return '#F59E0B';
    }
    return '#4361EE';
}

function getTypeLabel(type) {
    if (type === 'overdue' || (type && type.includes('Overdue'))) {
        return 'Overdue';
    }
    if (type === 'reminder_10m' || (type && type.includes('TenMinutes'))) {
        return '10 Menit (Mendesak)';
    }
    if (type === 'reminder_1h' || (type && type.includes('OneHour'))) {
        return '1 Jam Menuju Deadline';
    }
    if (type === 'due_today' || (type && type.includes('DueToday'))) {
        return 'Hari Ini';
    }
    return 'Mendatang';
}

function addNotificationToDropdown(notification, csrfToken) {
    const list = document.getElementById('header-notifications-list');
    if (!list) return;

    const emptyState = document.getElementById('header-notifications-empty');
    if (emptyState) emptyState.remove();

    const notifId = notification.id;
    const type = notification.notification_type || notification.type || 'due_soon';
    const title = escapeHtml(notification.title || 'Notifikasi Task');
    const message = escapeHtml(notification.message || '');
    const url = notification.url || (notification.task_id ? `/tasks/${notification.task_id}` : '#');
    const color = getTypeColor(type);

    const itemEl = document.createElement('div');
    itemEl.className = 'notification-item p-2.5 border-bottom d-flex align-items-start gap-2.5 bg-primary-subtle bg-opacity-25';
    itemEl.style.cssText = 'border-color: #F1F5F9 !important; transition: background-color 0.3s ease;';
    itemEl.id = `dropdown-notif-${notifId}`;

    itemEl.innerHTML = `
        <div class="mt-1 flex-shrink-0">
            <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: ${color};"></span>
        </div>
        <div class="flex-grow-1 min-w-0">
            <a href="${escapeHtml(url)}" class="text-decoration-none d-block">
                <div class="fw-semibold text-truncate small" style="color: #2B2D42;">${title}</div>
                <div class="text-secondary small text-truncate" style="font-size: 12px;">${message}</div>
            </a>
            <div class="d-flex align-items-center justify-content-between mt-1" style="font-size: 11px; color: #8D99AE;">
                <span>Baru saja</span>
                <form method="POST" action="/notifications/${notifId}/read" class="mark-single-read-form">
                    <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                    <button type="submit" class="btn btn-link p-0 text-decoration-none text-muted" style="font-size: 11px;">
                        Tandai dibaca
                    </button>
                </form>
            </div>
        </div>
    `;

    list.prepend(itemEl);
    attachSingleReadHandler(itemEl.querySelector('.mark-single-read-form'), csrfToken);
}

function showNotificationToast(notification) {
    let container = document.getElementById('realtime-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'realtime-toast-container';
        container.className = 'position-fixed top-0 end-0 p-3';
        container.style.cssText = 'z-index: 1090; max-width: 360px; width: 100%; pointer-events: none;';
        document.body.appendChild(container);
    }

    const type = notification.notification_type || notification.type || 'due_soon';
    const title = escapeHtml(notification.title || 'Notifikasi Task');
    const message = escapeHtml(notification.message || '');
    const url = notification.url || (notification.task_id ? `/tasks/${notification.task_id}` : '#');
    const color = getTypeColor(type);
    const label = getTypeLabel(type);
    const isUrgent = type === 'reminder_10m' || type === 'overdue' || (type && (type.includes('TenMinutes') || type.includes('Overdue')));

    const toast = document.createElement('div');
    toast.className = 'toast show border mb-2';
    toast.style.cssText = `
        pointer-events: auto;
        background-color: ${isUrgent ? '#FFF5F5' : '#FFFFFF'};
        border-left: 4px solid ${color} !important;
        border-color: ${isUrgent ? '#FED7D7' : '#DFE5EC'};
        border-radius: 10px;
        box-shadow: 0 4px 16px rgba(43, 45, 66, ${isUrgent ? '0.15' : '0.08'});
        animation: slideInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    `;

    toast.innerHTML = `
        <div class="p-3">
            <div class="d-flex align-items-center justify-content-between mb-1.5">
                <div class="d-flex align-items-center gap-1.5">
                    <span class="rounded-circle d-inline-block" style="width: 7px; height: 7px; background-color: ${color};"></span>
                    <span class="fw-bold tracking-tight" style="font-size: 11px; text-transform: uppercase; color: ${color}; letter-spacing: 0.04em;">${label}</span>
                </div>
                <button type="button" class="btn-close" style="font-size: 10px;" aria-label="Close"></button>
            </div>
            <div class="fw-semibold text-dark small mb-1">${title}</div>
            <div class="text-secondary small mb-2.5 leading-snug" style="font-size: 12px;">${message}</div>
            <div class="d-flex align-items-center justify-content-between">
                <a href="${escapeHtml(url)}" class="btn btn-sm ${isUrgent ? 'btn-danger' : 'btn-primary'} px-2.5 py-1 rounded-2 fw-medium text-decoration-none" style="font-size: 11px;">
                    Buka Task &rarr;
                </a>
                <span class="text-muted" style="font-size: 11px;">Baru saja</span>
            </div>
        </div>
    `;

    const closeBtn = toast.querySelector('.btn-close');
    closeBtn.addEventListener('click', () => {
        toast.remove();
    });

    container.appendChild(toast);

    // Auto remove after 6 seconds (8s for urgent)
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }
    }, isUrgent ? 8000 : 6000);
}

function addNotificationToPage(notification, csrfToken) {
    let ncList = document.getElementById('nc-notifications-list-group');
    const emptyCard = document.getElementById('nc-empty-card');
    const ncCard = document.getElementById('nc-card');

    if (emptyCard) {
        emptyCard.classList.add('d-none');
    }
    if (ncCard) {
        ncCard.classList.remove('d-none');
    }

    if (!ncList) {
        const container = document.getElementById('nc-container') || document.querySelector('.container-fluid');
        if (!container) return;

        const card = document.createElement('div');
        card.id = 'nc-card';
        card.className = 'card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4';
        card.innerHTML = '<div id="nc-notifications-list-group" class="list-group list-group-flush"></div>';
        if (emptyCard && emptyCard.parentNode) {
            emptyCard.parentNode.insertBefore(card, emptyCard);
        } else {
            container.prepend(card);
        }
        ncList = document.getElementById('nc-notifications-list-group');
    }

    if (!ncList) return;

    const notifId = notification.id;
    const type = notification.notification_type || notification.type || 'due_soon';
    const title = escapeHtml(notification.title || 'Notifikasi Task');
    const message = escapeHtml(notification.message || '');
    const url = notification.url || (notification.task_id ? `/tasks/${notification.task_id}` : '#');
    const dueDate = notification.due_date ? escapeHtml(notification.due_date) : null;

    let badgeHtml = '';
    if (type === 'overdue' || (type && type.includes('Overdue'))) {
        badgeHtml = `<span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-0.5 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
            <span class="rounded-circle bg-danger d-inline-block" style="width: 5px; height: 5px;"></span>
            <span>Overdue</span>
        </span>`;
    } else if (type === 'reminder_10m' || (type && type.includes('TenMinutes'))) {
        badgeHtml = `<span class="badge bg-danger text-white rounded-pill px-2 py-0.5 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" style="font-size: 0.72rem;">
            <span class="rounded-circle bg-white d-inline-block" style="width: 5px; height: 5px;"></span>
            <span>10 Menit</span>
        </span>`;
    } else if (type === 'reminder_1h' || (type && type.includes('OneHour'))) {
        badgeHtml = `<span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
            <span class="rounded-circle bg-primary d-inline-block" style="width: 5px; height: 5px;"></span>
            <span>1 Jam</span>
        </span>`;
    } else if (type === 'due_today' || (type && type.includes('DueToday'))) {
        badgeHtml = `<span class="badge bg-warning-subtle text-warning rounded-pill px-2 py-0.5 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
            <span class="rounded-circle bg-warning d-inline-block" style="width: 5px; height: 5px;"></span>
            <span>Hari Ini</span>
        </span>`;
    } else {
        badgeHtml = `<span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
            <span class="rounded-circle bg-primary d-inline-block" style="width: 5px; height: 5px;"></span>
            <span>Mendatang</span>
        </span>`;
    }

    const itemEl = document.createElement('div');
    itemEl.id = `nc-notification-${notifId}`;
    itemEl.className = 'list-group-item p-3 p-md-4 border-bottom d-flex flex-column flex-md-row md:align-items-center justify-content-between gap-3 bg-primary-subtle bg-opacity-25';

    itemEl.innerHTML = `
        <div class="d-flex align-items-start gap-3 min-w-0">
            <div class="mt-1 shrink-0">
                <span class="d-inline-block rounded-circle bg-primary" style="width: 10px; height: 10px;" title="Belum dibaca"></span>
            </div>
            <div class="min-w-0">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    ${badgeHtml}
                    <h4 class="h6 fw-bold text-dark mb-0 text-truncate">${title}</h4>
                </div>
                <p class="text-secondary small mb-1.5 leading-relaxed">${message}</p>
                <div class="d-flex align-items-center gap-3 text-secondary" style="font-size: 0.75rem;">
                    <span>Baru saja</span>
                    ${dueDate ? `<span>•</span><span>Deadline: ${dueDate}</span>` : ''}
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 shrink-0 justify-content-end">
            <a href="${escapeHtml(url)}" class="btn btn-sm btn-light border bg-white text-primary fw-semibold rounded-3 px-3 py-1.5 d-inline-flex align-items-center gap-1 hover-lift">
                <span class="small">Buka Task</span>
            </a>
            <form method="POST" action="/notifications/${notifId}/read" class="mark-single-read-form">
                <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                <button type="submit" class="btn btn-sm btn-light border bg-white text-secondary fw-semibold rounded-3 px-3 py-1.5 hover-lift">
                    <span class="small">Tandai dibaca</span>
                </button>
            </form>
        </div>
    `;

    ncList.prepend(itemEl);
    attachSingleReadHandler(itemEl.querySelector('.mark-single-read-form'), csrfToken);
}

function setupReadHandlers(csrfToken) {
    // 1. Mark all as read forms
    document.querySelectorAll('#mark-all-read-form, form[action*="/notifications/read-all"], form[action*="/notifications/mark-all-read"]').forEach((form) => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (res.ok) {
                    setUnreadCount(0);
                    // Remove unread indicators in dropdown
                    document.querySelectorAll('#header-notifications-list .notification-item').forEach((item) => {
                        item.classList.remove('bg-primary-subtle', 'bg-opacity-25');
                        const markForm = item.querySelector('.mark-single-read-form');
                        if (markForm) markForm.remove();
                    });
                    // Remove unread indicators in Notification Center
                    document.querySelectorAll('#nc-notifications-list-group .list-group-item').forEach((item) => {
                        item.classList.remove('bg-primary-subtle', 'bg-opacity-25');
                        const dot = item.querySelector('.rounded-circle.bg-primary');
                        if (dot) {
                            dot.classList.remove('bg-primary');
                            dot.classList.add('bg-secondary', 'bg-opacity-25');
                        }
                        const markForm = item.querySelector('.mark-single-read-form');
                        if (markForm) markForm.remove();
                    });
                } else {
                    form.submit();
                }
            } catch {
                form.submit();
            }
        });
    });

    // 2. Mark single notification as read forms
    document.querySelectorAll('.mark-single-read-form').forEach((form) => {
        attachSingleReadHandler(form, csrfToken);
    });
}

function attachSingleReadHandler(form, csrfToken) {
    if (!form || form.dataset.bound) return;
    form.dataset.bound = 'true';

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (res.ok) {
                const data = await res.json().catch(() => null);
                if (data && typeof data.unread_count === 'number') {
                    setUnreadCount(data.unread_count);
                } else {
                    decrementUnreadCount();
                }

                const item = form.closest('.notification-item, .list-group-item');
                if (item) {
                    item.classList.remove('bg-primary-subtle', 'bg-opacity-25');
                    const dot = item.querySelector('.rounded-circle.bg-primary');
                    if (dot) {
                        dot.classList.remove('bg-primary');
                        dot.classList.add('bg-secondary', 'bg-opacity-25');
                    }
                    form.remove();
                }
            } else {
                form.submit();
            }
        } catch {
            form.submit();
        }
    });
}

function initNotificationPreferences() {
    // Sound alarm toggle
    const soundBtn = document.getElementById('tm-toggle-sound-btn');
    const soundText = document.getElementById('tm-sound-status-text');
    const soundIcon = document.getElementById('tm-sound-icon');

    const updateSoundUI = (enabled) => {
        if (soundText) soundText.textContent = enabled ? 'Suara: On' : 'Suara: Off';
        if (soundIcon) soundIcon.textContent = enabled ? '🔔' : '🔕';
        if (soundBtn) {
            soundBtn.title = enabled ? 'Suara alarm aktif (Klik untuk matikan)' : 'Suara alarm nonaktif (Klik untuk aktifkan)';
            if (enabled) {
                soundBtn.classList.remove('text-muted');
                soundBtn.classList.add('text-primary');
            } else {
                soundBtn.classList.remove('text-primary');
                soundBtn.classList.add('text-muted');
            }
        }
    };

    const isSoundEnabled = localStorage.getItem('tm_audio_alarm_enabled') === 'true';
    updateSoundUI(isSoundEnabled);

    if (soundBtn && !soundBtn.dataset.bound) {
        soundBtn.dataset.bound = 'true';
        soundBtn.addEventListener('click', () => {
            const current = localStorage.getItem('tm_audio_alarm_enabled') === 'true';
            const next = !current;
            localStorage.setItem('tm_audio_alarm_enabled', next ? 'true' : 'false');
            updateSoundUI(next);
            if (next) {
                playTone(659.25, 0.15, 'sine');
            }
        });
    }

    // Browser push notification permission button
    const notifBtn = document.getElementById('tm-request-browser-notif-btn');
    const notifWrapper = document.getElementById('tm-browser-notif-wrapper');

    const updateBrowserNotifUI = () => {
        if (!('Notification' in window)) {
            if (notifWrapper) notifWrapper.style.display = 'none';
            return;
        }

        if (Notification.permission === 'granted') {
            if (notifBtn) {
                notifBtn.textContent = '✓ Notif Browser';
                notifBtn.classList.remove('text-primary');
                notifBtn.classList.add('text-success');
                notifBtn.disabled = true;
                notifBtn.style.cursor = 'default';
                notifBtn.title = 'Notifikasi browser sudah aktif.';
            }
        } else if (Notification.permission === 'denied') {
            if (notifBtn) {
                notifBtn.textContent = 'Notif Diblokir';
                notifBtn.classList.remove('text-primary');
                notifBtn.classList.add('text-muted');
                notifBtn.disabled = true;
                notifBtn.title = 'Izin notifikasi diblokir di pengaturan browser Anda.';
            }
        } else {
            if (notifBtn) {
                notifBtn.textContent = 'Aktifkan Notif Browser';
                notifBtn.classList.add('text-primary');
                notifBtn.classList.remove('text-success', 'text-muted');
                notifBtn.disabled = false;
            }
        }
    };

    updateBrowserNotifUI();

    if (notifBtn && !notifBtn.dataset.bound) {
        notifBtn.dataset.bound = 'true';
        notifBtn.addEventListener('click', async () => {
            if (!('Notification' in window)) return;
            try {
                const permission = await Notification.requestPermission();
                updateBrowserNotifUI();
                if (permission === 'granted') {
                    new Notification('Task Manager', {
                        body: 'Notifikasi browser berhasil diaktifkan!',
                        icon: '/images/task-manager-logo.png',
                    });
                }
            } catch (err) {
                console.warn('Error requesting notification permission:', err);
            }
        });
    }
}

function playTone(freq, duration, type = 'sine') {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;

        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = type;
        osc.frequency.setValueAtTime(freq, ctx.currentTime);

        gain.gain.setValueAtTime(0.15, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);

        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.start();
        osc.stop(ctx.currentTime + duration);
    } catch {
        // Ignored if sound cannot play
    }
}

function playReminderSound(notification) {
    if (localStorage.getItem('tm_audio_alarm_enabled') !== 'true') {
        return;
    }

    const type = notification.notification_type || notification.type || '';
    if (type === 'reminder_10m' || type === 'overdue' || (type && (type.includes('TenMinutes') || type.includes('Overdue')))) {
        // Urgent alert chime: two distinct pulses
        playTone(880, 0.22, 'sawtooth');
        setTimeout(() => playTone(1046.5, 0.3, 'sawtooth'), 180);
    } else {
        // Standard pleasant chime
        playTone(523.25, 0.2, 'sine');
        setTimeout(() => playTone(659.25, 0.25, 'sine'), 150);
    }
}

function triggerBrowserNotification(notification) {
    if (!('Notification' in window)) return;
    if (Notification.permission !== 'granted') return;

    try {
        const title = notification.title || 'Pengingat Deadline Task';
        const body = notification.message || 'Tenggat waktu task Anda mendekati deadline.';
        const url = notification.url || (notification.task_id ? `/tasks/${notification.task_id}` : '/notifications');

        const browserNotif = new Notification(title, {
            body: body,
            icon: '/images/task-manager-logo.png',
            tag: `tm-notif-${notification.id || Date.now()}`,
        });

        browserNotif.onclick = function () {
            window.focus();
            if (url && url !== '#') {
                window.location.href = url;
            }
            browserNotif.close();
        };
    } catch (err) {
        console.warn('Browser Notification error:', err);
    }
}
