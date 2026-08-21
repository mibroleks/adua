/**
 * Ygrace Tech
 * University Admissions Portal
 *
 * Notifications Module
 * Handles badge count updates and read/unread state.
 *
 * Status: ✅ Production Ready
 * Version: 1.0 (dynamic badge integration)
 */

const badge = document.querySelector('[data-notification-badge]');
const notificationList = document.querySelector('[data-notification-list]');

if (badge && notificationList) {
    const updateBadge = () => {
        const unreadItems = notificationList.querySelectorAll('[data-notification][data-unread="true"]');
        badge.textContent = unreadItems.length > 0 ? unreadItems.length : '';
        badge.hidden = unreadItems.length === 0;
    };

    // Initial badge update
    updateBadge();

    // Mark notification as read when clicked
    notificationList.querySelectorAll('[data-notification]').forEach((item) => {
        item.addEventListener('click', () => {
            item.setAttribute('data-unread', 'false');
            updateBadge();
        });
    });

    // Optional: simulate live updates (polling/WebSocket)
    // Example: poll every 30s for new notifications
    // setInterval(fetchNewNotifications, 30000);
}
