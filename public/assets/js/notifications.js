document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-site-notification]').forEach(function(notification) {
        let removalTimer = 0;

        const closeNotification = function() {
            window.clearTimeout(removalTimer);
            notification.classList.add('is-hiding');
            window.setTimeout(function() {
                notification.closest('.site-notification-layer')?.remove();
            }, 200);
        };

        notification.querySelector('[data-site-notification-close]')?.addEventListener('click', closeNotification);

        if (notification.dataset.autoDismiss === 'true') {
            removalTimer = window.setTimeout(closeNotification, 5000);
        }
    });
});
