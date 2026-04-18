</div><!-- end .admin-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const sidebar       = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebarClose  = document.getElementById('sidebarClose');

// Hamburger button : opens sidebar
if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.add('show');
    });
}

// X button : closes sidebar
if (sidebarClose) {
    sidebarClose.addEventListener('click', function() {
        sidebar.classList.remove('show');
    });
}

// Also close when clicking a nav link on mobile
document.querySelectorAll('.sidebar .nav-link').forEach(function(link) {
    link.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('show');
        }
    });
});

// Close when clicking outside
document.addEventListener('click', function(e) {
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
            sidebar.classList.remove('show');
        }
    }
});
</script>
</body>
</html>