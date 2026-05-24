<!-- Mobile menu button -->
<button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Menu">
    <i class="ri-menu-line"></i>
</button>

<!-- Sidebar overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>🏨 Luxury Hotel</h2>
    </div>

    <nav class="sidebar-nav">
        <a href="index.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="ri-dashboard-line"></i> Dashboard
        </a>
        <a href="occupancy.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'occupancy.php' ? 'active' : ''; ?>">
            <i class="ri-building-2-line"></i> Occupancy
        </a>
        <a href="bookings.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>">
            <i class="ri-file-list-line"></i> Booking
        </a>
        <a href="room-management.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'room-management.php' ? 'active' : ''; ?>">
            <i class="ri-settings-line"></i> Kelola Kamar
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="admin-info">
            <p>Admin: <strong><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></strong></p>
        </div>
        <a href="logout.php" class="btn-logout">
            <i class="ri-logout-box-line"></i> Logout
        </a>
    </div>
</div>

<script>
(function() {
    const btn = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('open');
        btn.querySelector('i').className = 'ri-close-line';
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
        btn.querySelector('i').className = 'ri-menu-line';
    }

    btn.addEventListener('click', function() {
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });

    overlay.addEventListener('click', closeSidebar);
})();
</script>
