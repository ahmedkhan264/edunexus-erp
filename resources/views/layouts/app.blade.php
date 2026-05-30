<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EduNexus ERP + LMS') - EduNexus</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        :root {
            --edunexus-blue: #1e3a8a;
            --edunexus-light-blue: #3b82f6;
            --edunexus-green: #10b981;
            --edunexus-light-green: #34d399;
            --edunexus-white: #ffffff;
            --edunexus-gray: #6b7280;
            --edunexus-light-gray: #f3f4f6;
            --edunexus-dark-gray: #374151;
            --sidebar-width: 280px;
            --navbar-height: 70px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--edunexus-light-gray);
            color: var(--edunexus-dark-gray);
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, var(--edunexus-blue) 0%, var(--edunexus-light-blue) 100%);
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar.collapsed {
            transform: translateX(-100%);
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
        }
        
        .sidebar-logo {
            color: var(--edunexus-white);
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .sidebar-logo i {
            font-size: 1.8rem;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-item {
            margin-bottom: 5px;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--edunexus-white);
        }
        
        .sidebar-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: var(--edunexus-white);
            border-left-color: var(--edunexus-green);
        }
        
        .sidebar-link i {
            width: 20px;
            margin-right: 12px;
            text-align: center;
        }
        
        .sidebar-submenu {
            background: rgba(0, 0, 0, 0.1);
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .sidebar-submenu.show {
            max-height: 500px;
        }
        
        .sidebar-submenu-item {
            display: block;
            padding: 10px 20px 10px 52px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .sidebar-submenu-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.9);
        }
        
        /* Main Content Area */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        
        .main-wrapper.full-width {
            margin-left: 0;
        }
        
        /* Navbar Styles */
        .navbar {
            background: var(--edunexus-white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 0 30px;
            height: var(--navbar-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--edunexus-gray);
            cursor: pointer;
            padding: 8px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .sidebar-toggle:hover {
            background: var(--edunexus-light-gray);
            color: var(--edunexus-blue);
        }
        
        .page-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--edunexus-dark-gray);
            margin: 0;
        }
        
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .notification-bell {
            position: relative;
            background: none;
            border: none;
            font-size: 1.1rem;
            color: var(--edunexus-gray);
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .notification-bell:hover {
            background: var(--edunexus-light-gray);
            color: var(--edunexus-blue);
        }
        
        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: var(--edunexus-green);
            color: var(--edunexus-white);
            font-size: 0.7rem;
            padding: 2px 5px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }
        
        .user-dropdown {
            position: relative;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--edunexus-green) 0%, var(--edunexus-light-green) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--edunexus-white);
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .user-avatar:hover {
            transform: scale(1.05);
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--edunexus-white);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            min-width: 200px;
            margin-top: 10px;
            display: none;
            z-index: 1001;
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-item {
            display: block;
            padding: 12px 20px;
            color: var(--edunexus-dark-gray);
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 1px solid var(--edunexus-light-gray);
        }
        
        .dropdown-item:first-child {
            border-radius: 10px 10px 0 0;
        }
        
        .dropdown-item:last-child {
            border-radius: 0 0 10px 10px;
            border-bottom: none;
        }
        
        .dropdown-item:hover {
            background: var(--edunexus-light-gray);
            color: var(--edunexus-blue);
        }
        
        .dropdown-item i {
            width: 20px;
            margin-right: 10px;
        }
        
        /* Content Area */
        .content {
            padding: 30px;
            min-height: calc(100vh - var(--navbar-height));
        }
        
        /* KPI Cards */
        .kpi-card {
            background: var(--edunexus-white);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .kpi-card.blue {
            border-left-color: var(--edunexus-light-blue);
        }
        
        .kpi-card.green {
            border-left-color: var(--edunexus-green);
        }
        
        .kpi-card.orange {
            border-left-color: #f59e0b;
        }
        
        .kpi-card.red {
            border-left-color: #ef4444;
        }
        
        .kpi-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        
        .kpi-icon.blue {
            background: rgba(59, 130, 246, 0.1);
            color: var(--edunexus-light-blue);
        }
        
        .kpi-icon.green {
            background: rgba(16, 185, 129, 0.1);
            color: var(--edunexus-green);
        }
        
        .kpi-icon.orange {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }
        
        .kpi-icon.red {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        
        .kpi-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--edunexus-dark-gray);
            margin-bottom: 5px;
        }
        
        .kpi-label {
            color: var(--edunexus-gray);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .kpi-change {
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .kpi-change.positive {
            color: var(--edunexus-green);
        }
        
        .kpi-change.negative {
            color: #ef4444;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-wrapper {
                margin-left: 0;
            }
            
            .navbar {
                padding: 0 20px;
            }
            
            .content {
                padding: 20px;
            }
            
            .page-title {
                font-size: 1rem;
            }
        }
        
        /* Loading Spinner */
        .spinner-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 3px;
        }
        
        /* Alert Styles */
        .alert {
            border-radius: 10px;
            border: none;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border-left: 4px solid var(--edunexus-green);
        }
        
        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
            border-left: 4px solid #ef4444;
        }
        
        .alert-warning {
            background: #fffbeb;
            color: #d97706;
            border-left: 4px solid #f59e0b;
        }
        
        .alert-info {
            background: #eff6ff;
            color: #2563eb;
            border-left: 4px solid var(--edunexus-light-blue);
        }
    </style>
</head>
<body>
    @include('partials.sidebar')
    
    <div class="main-wrapper" id="mainWrapper">
        @include('partials.navbar')
        
        <main class="content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @yield('content')
        </main>
    </div>
    
    <!-- Loading Spinner -->
    <div class="spinner-wrapper d-none" id="loadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainWrapper = document.getElementById('mainWrapper');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                sidebar.classList.toggle('mobile-open');
                mainWrapper.classList.toggle('full-width');
            });
        }
        
        // User Dropdown
        const userAvatar = document.getElementById('userAvatar');
        const dropdownMenu = document.getElementById('dropdownMenu');
        
        if (userAvatar && dropdownMenu) {
            userAvatar.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            });
            
            document.addEventListener('click', function() {
                dropdownMenu.classList.remove('show');
            });
            
            dropdownMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        // Sidebar Submenu Toggle - Bootstrap 5
        document.querySelectorAll('.sidebar-link[data-bs-toggle="collapse"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-bs-target');
                const submenu = document.querySelector(targetId);
                
                if (submenu) {
                    const bsCollapse = new bootstrap.Collapse(submenu, {
                        toggle: true
                    });
                    
                    // Toggle icon rotation
                    const icon = this.querySelector('.fa-chevron-down, .fa-chevron-right');
                    if (icon) {
                        icon.classList.toggle('fa-chevron-down');
                        icon.classList.toggle('fa-chevron-right');
                    }
                }
            });
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Show/hide loading spinner
        function showLoading() {
            document.getElementById('loadingSpinner').classList.remove('d-none');
        }
        
        function hideLoading() {
            document.getElementById('loadingSpinner').classList.add('d-none');
        }
        
        // Global AJAX setup
        if (typeof $ !== 'undefined') {
            $(document).ajaxStart(function() {
                showLoading();
            });
            
            $(document).ajaxStop(function() {
                hideLoading();
            });
        }
        
        // Close sidebar on mobile when clicking outside
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('mobile-open');
                }
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('mobile-open');
                mainWrapper.classList.remove('full-width');
            }
        });
        
        // Notification System
        const notificationBell = document.getElementById('notificationBell');
        const notificationDropdown = document.getElementById('notificationDropdown');
        let notificationDropdownOpen = false;
        
        // Toggle notification dropdown
        if (notificationBell) {
            notificationBell.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdownOpen = !notificationDropdownOpen;
                notificationDropdown.style.display = notificationDropdownOpen ? 'block' : 'none';
            });
        }
        
        // Close notification dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (notificationDropdownOpen && 
                !notificationBell.contains(e.target) && 
                !notificationDropdown.contains(e.target)) {
                notificationDropdownOpen = false;
                notificationDropdown.style.display = 'none';
            }
        });
        
        // Mark all notifications as read
        window.markAllAsRead = function() {
            fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error marking notifications as read:', error);
            });
        };
        
        // Auto-refresh notifications every 30 seconds
        setInterval(function() {
            fetch('/notifications/count', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.count !== undefined) {
                    const badge = notificationBell.querySelector('.notification-badge');
                    if (data.count > 0) {
                        if (badge) {
                            badge.textContent = data.count;
                        } else {
                            const newBadge = document.createElement('span');
                            newBadge.className = 'notification-badge';
                            newBadge.textContent = data.count;
                            notificationBell.appendChild(newBadge);
                        }
                    } else if (badge) {
                        badge.remove();
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching notification count:', error);
            });
        }, 30000);
    </script>
    
    @yield('scripts')
</body>
</html>
