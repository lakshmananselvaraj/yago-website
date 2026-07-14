import { apiGet } from './api.js';

const shell = document.querySelector('.dash-shell');

if (shell) {
    initSidebarCollapse();
    initMobileToggle();
    initDropdowns();
    initSidebarSearch();
    loadNotifications();
}

function initSidebarCollapse() {
    const btn = shell.querySelector('[data-sidebar-collapse]');
    if (!btn) return;

    if (localStorage.getItem('vipasa-sidebar-collapsed') === '1') {
        shell.classList.add('is-collapsed');
    }

    btn.addEventListener('click', () => {
        shell.classList.toggle('is-collapsed');
        localStorage.setItem('vipasa-sidebar-collapsed', shell.classList.contains('is-collapsed') ? '1' : '0');
    });
}

function initMobileToggle() {
    const toggleBtn = document.querySelector('[data-sidebar-toggle]');
    const overlay = document.querySelector('[data-sidebar-overlay]');

    toggleBtn?.addEventListener('click', () => {
        shell.classList.toggle('is-mobile-open');
    });

    overlay?.addEventListener('click', () => {
        shell.classList.remove('is-mobile-open');
    });
}

function initDropdowns() {
    const dropdowns = document.querySelectorAll('[data-dropdown]');

    dropdowns.forEach((dropdown) => {
        const toggle = dropdown.querySelector('[data-dropdown-toggle]');
        const panel = dropdown.querySelector('[data-dropdown-panel]');
        if (!toggle || !panel) return;

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            const isOpen = !panel.hidden;
            dropdowns.forEach((other) => {
                const otherPanel = other.querySelector('[data-dropdown-panel]');
                if (otherPanel) otherPanel.hidden = true;
            });
            panel.hidden = isOpen;
        });
    });

    document.addEventListener('click', () => {
        dropdowns.forEach((dropdown) => {
            const panel = dropdown.querySelector('[data-dropdown-panel]');
            if (panel) panel.hidden = true;
        });
    });
}

function initSidebarSearch() {
    const input = document.querySelector('[data-sidebar-search]');
    if (!input) return;

    const links = Array.from(document.querySelectorAll('.dash-sidebar__link'));

    input.addEventListener('input', () => {
        const query = input.value.trim().toLowerCase();
        links.forEach((link) => {
            const label = link.querySelector('.dash-sidebar__label')?.textContent.toLowerCase() ?? '';
            link.style.display = query === '' || label.includes(query) ? '' : 'none';
        });
    });
}

async function loadNotifications() {
    const badge = document.querySelector('[data-notif-badge]');
    const list = document.querySelector('[data-notif-list]');
    if (!badge || !list) return;

    try {
        const result = await apiGet('/api/notifications');
        const { notifications, unread_count: unreadCount } = result.data;

        if (unreadCount > 0) {
            badge.hidden = false;
            badge.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
        }

        if (!notifications.length) {
            list.innerHTML = '<p class="text-muted" style="padding:var(--space-4);font-size:var(--font-size-sm)">No notifications yet.</p>';
            return;
        }

        list.innerHTML = notifications.map((n) => `
            <div class="dash-topbar__notif-item${n.is_read == 0 ? ' is-unread' : ''}">
                <strong>${escapeHtml(n.title)}</strong>
                ${n.body ? `<div class="text-muted" style="margin-top:2px">${escapeHtml(n.body)}</div>` : ''}
            </div>
        `).join('');
    } catch {
        list.innerHTML = '<p class="text-muted" style="padding:var(--space-4);font-size:var(--font-size-sm)">Could not load notifications.</p>';
    }
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}
