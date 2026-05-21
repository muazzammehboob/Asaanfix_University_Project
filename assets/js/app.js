/**
 * AsaanFix Pakistan - Main JavaScript
 * Enhanced with SweetAlert2 and modern interactions
 */
document.addEventListener('DOMContentLoaded', function() {

    // ─── AOS Init ───
    if (typeof AOS !== 'undefined') {
        AOS.init({ duration: 600, once: true, offset: 80 });
    }

    // ─── Navbar Scroll Effect ───
    const navbar = document.getElementById('mainNavbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        });
    }

    // ─── Back to Top ───
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            backToTop.classList.toggle('show', window.scrollY > 400);
        });
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ─── Auto-dismiss alerts ───
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    // ─── Password Toggle ───
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = document.querySelector(this.dataset.target);
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            this.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
        });
    });

    // ─── Form Validation ───
    document.querySelectorAll('.needs-validation').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // ─── Counter Animation ───
    const counters = document.querySelectorAll('[data-counter]');
    if (counters.length) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(el => observer.observe(el));
    }

    function animateCounter(el) {
        const target = parseInt(el.dataset.counter);
        const suffix = el.dataset.suffix || '';
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) { current = target; clearInterval(timer); }
            el.textContent = Math.floor(current).toLocaleString() + suffix;
        }, 30);
    }

    // ─── Sidebar Toggle (Mobile) ───
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.dashboard-sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('show'));
        document.addEventListener('click', (e) => {
            if (sidebar.classList.contains('show') && !sidebar.contains(e.target) && e.target !== sidebarToggle) {
                sidebar.classList.remove('show');
            }
        });
    }

    // ─── Lazy Loading Images ───
    if ('loading' in HTMLImageElement.prototype) {
        document.querySelectorAll('img:not([loading])').forEach(img => {
            img.setAttribute('loading', 'lazy');
            img.setAttribute('decoding', 'async');
        });
    }

    // ─── SweetAlert2 Confirm for Dangerous Actions ───
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            const msg = this.dataset.confirm || 'Are you sure?';
            const href = this.href || '';
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Confirm Action',
                    text: msg,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2563EB',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Yes, proceed!',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed && href) {
                        window.location.href = href;
                    }
                });
            } else if (confirm(msg) && href) {
                window.location.href = href;
            }
        });
    });

    // ─── SweetAlert2 Delete Confirmations ───
    document.querySelectorAll('[data-delete]').forEach(el => {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.href || this.dataset.delete;
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete this item?',
                    text: 'This action cannot be undone.',
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#DC2626',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: '<i class="bi bi-trash me-1"></i>Delete',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed && href) {
                        window.location.href = href;
                    }
                });
            } else if (confirm('Delete this item?') && href) {
                window.location.href = href;
            }
        });
    });

    // ─── Status change with SweetAlert2 ───
    document.querySelectorAll('[data-status-change]').forEach(el => {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.href || '';
            const status = this.dataset.statusChange;
            const labels = {
                'accepted': 'Accept this booking?',
                'technician_on_way': 'Mark technician as on the way?',
                'in_progress': 'Start working on this booking?',
                'completed': 'Mark this booking as completed?',
                'cancelled': 'Cancel this booking?',
                'refunded': 'Process refund for this booking?',
            };
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: labels[status] || 'Change status?',
                    icon: status === 'cancelled' ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#2563EB',
                    confirmButtonText: 'Yes, confirm',
                }).then((result) => {
                    if (result.isConfirmed && href) window.location.href = href;
                });
            } else if (confirm(labels[status] || 'Change status?') && href) {
                window.location.href = href;
            }
        });
    });
});

// ─── AJAX Utility ───
const Ajax = {
    baseUrl: document.querySelector('meta[name="app-url"]')?.content || '',

    async request(url, options = {}) {
        const defaults = {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        };
        if (options.method === 'POST' && !(options.body instanceof FormData)) {
            defaults.headers['Content-Type'] = 'application/json';
        }
        // Add CSRF token for POST
        if (options.method === 'POST') {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) defaults.headers['X-CSRF-TOKEN'] = csrfMeta.content;
        }

        try {
            const response = await fetch(url, { ...defaults, ...options });
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('AJAX Error:', error);
            return { success: false, message: 'Network error. Please try again.' };
        }
    },

    get(url) { return this.request(url); },

    post(url, data) {
        const body = data instanceof FormData ? data : JSON.stringify(data);
        return this.request(url, { method: 'POST', body });
    }
};

// ─── Toast Notification (SweetAlert2 enhanced) ───
function showToast(message, type = 'info') {
    if (typeof Swal !== 'undefined') {
        const iconMap = { success: 'success', danger: 'error', warning: 'warning', info: 'info' };
        Swal.fire({
            icon: iconMap[type] || 'info',
            title: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
        });
        return;
    }

    // Fallback to Bootstrap toast
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '1090';
        document.body.appendChild(container);
    }
    const icons = { success: 'check-circle', danger: 'exclamation-triangle', warning: 'exclamation-circle', info: 'info-circle' };
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body"><i class="bi bi-${icons[type] || 'info-circle'} me-2"></i>${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>`;
    container.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 4000 });
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

// ─── Confirm Dialog (SweetAlert2 enhanced) ───
function confirmAction(message, callback) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Are you sure?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2563EB',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Yes, continue',
        }).then((result) => {
            if (result.isConfirmed) callback();
        });
    } else {
        if (confirm(message)) callback();
    }
}
