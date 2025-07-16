<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Custom Script untuk Sidebar Toggle dan Tooltips -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('active');
                content.classList.toggle('active');
                // Update localStorage saat toggle sidebar
                localStorage.setItem('sidebarActive', sidebar.classList.contains('active'));
            });
        }

        // DataTable initialization if exists
        if ($.fn.DataTable && document.getElementById('dataTable')) {
            $('#dataTable').DataTable();
        }

        // Inisialisasi tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(t => new bootstrap.Tooltip(t));
    });
</script>
