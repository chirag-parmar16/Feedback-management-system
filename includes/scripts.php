<!-- External Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ─── Toast Notification System ──────────────────────────────────────────────
(function() {
    function createToastStack() {
        let stack = document.getElementById('toast-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'toast-stack';
            stack.className = 'toast-stack';
            document.body.appendChild(stack);
        }
        return stack;
    }

    const icons = {
        success: { icon: 'fa-check-circle', color: 'var(--accent)' },
        error:   { icon: 'fa-times-circle',  color: 'var(--danger)' },
        warning: { icon: 'fa-exclamation-triangle', color: 'var(--warning)' },
        info:    { icon: 'fa-info-circle',   color: 'var(--info)' },
    };

    window.showToast = function(message, type = 'success', duration = 4000) {
        const stack = createToastStack();
        const cfg = icons[type] || icons.success;

        const item = document.createElement('div');
        item.className = `toast-item toast-${type}`;
        item.innerHTML = `
            <i class="fas ${cfg.icon} toast-icon" style="color:${cfg.color}"></i>
            <span class="toast-text">${message}</span>
            <button class="toast-close" aria-label="Close">&times;</button>
        `;

        stack.appendChild(item);

        item.querySelector('.toast-close').addEventListener('click', () => removeToast(item));

        const timer = setTimeout(() => removeToast(item), duration);
        item._timer = timer;
    };

    function removeToast(item) {
        clearTimeout(item._timer);
        item.classList.add('removing');
        item.addEventListener('animationend', () => item.remove(), { once: true });
    }

    // Auto-fire server-side toast from PHP session
    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('server-toast-data');
        if (el) {
            const msg  = el.getAttribute('data-message');
            const type = el.getAttribute('data-type') || 'success';
            if (msg) setTimeout(() => showToast(msg, type), 150);
        }
    });
})();

// ─── Password Toggle ─────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.pw-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const input = document.querySelector(btn.getAttribute('data-target'));
            if (!input) return;
            const isText = input.type === 'text';
            input.type = isText ? 'password' : 'text';
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
            }
        });
    });
});

// ─── Submit button loading state ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            const btn = form.querySelector('button[type="submit"]');
            if (btn && !btn.hasAttribute('data-no-loading')) {
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i>Please wait...';
                btn.disabled = true;
                // Re-enable after 8s as fallback
                setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; }, 8000);
            }
        });
    });
});
</script>
