/* Toko Adam - Main JS */
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle (mobile)
    const menuBtn = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (menuBtn) {
        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        });
    }
    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }
    
    // Auto-dismiss alerts after 5s
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    
    // Alert close button
    document.querySelectorAll('.alert-close').forEach(btn => {
        btn.addEventListener('click', () => {
            const alert = btn.closest('.alert');
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 200);
        });
    });
    
    // Confirm delete
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            if (!confirm(el.dataset.confirm || 'Yakin ingin menghapus?')) {
                e.preventDefault();
            }
        });
    });
    
    // Format rupiah on input
    document.querySelectorAll('.input-rupiah').forEach(input => {
        input.addEventListener('input', function() {
            let val = this.value.replace(/\D/g, '');
            this.value = val;
        });
    });
});

// Utility: Format number as rupiah string
function formatRupiah(num) {
    return 'Rp ' + parseInt(num || 0).toLocaleString('id-ID');
}
