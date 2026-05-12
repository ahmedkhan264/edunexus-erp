<!-- Top Navbar -->
<nav class="navbar">
    <div class="navbar-brand">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title">@yield('title', 'Dashboard')</h1>
    </div>
    
    <div class="navbar-actions">
        <!-- Notification Bell -->
        <button class="notification-bell position-relative" id="notificationBell">
            <i class="fas fa-bell"></i>
            @php
                try {
                    $unreadCount = auth()->user()->unreadNotifications->count();
                } catch (\Exception $e) {
                    $unreadCount = 0;
                }
            @endphp
            @if($unreadCount > 0)
                <span class="notification-badge">
                    {{ $unreadCount }}
                </span>
            @endif
        </button>
        
        <!-- User Dropdown -->
        <div class="user-dropdown">
            <div class="user-avatar" id="userAvatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            
            <div class="dropdown-menu" id="dropdownMenu">
                <div class="px-4 py-3 border-bottom">
                    <div class="fw-bold">{{ auth()->user()->name }}</div>
                    <div class="text-muted small">{{ auth()->user()->email }}</div>
                    <div class="badge bg-primary mt-1">{{ auth()->user()->role->name }}</div>
                </div>
                
                <a href="#" class="dropdown-item">
                    <i class="fas fa-user"></i>
                    Profile
                </a>
                
                <a href="#" class="dropdown-item">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
                
                <a href="#" class="dropdown-item">
                    <i class="fas fa-question-circle"></i>
                    Help
                </a>
                
                <div class="border-top mt-2 pt-2">
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Notification Dropdown -->
<div class="dropdown-menu" id="notificationDropdown" style="width: 350px; right: 100px; top: 80px;">
    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Notifications</h6>
        @if($unreadCount > 0)
            <button class="btn btn-sm btn-outline-primary" onclick="markAllAsRead()">Mark all as read</button>
        @endif
    </div>
    
    <div style="max-height: 400px; overflow-y: auto;">
        @if($unreadCount > 0)
            @php
                try {
                    $notifications = auth()->user()->unreadNotifications->take(5);
                } catch (\Exception $e) {
                    $notifications = collect([]);
                }
            @endphp
            @forelse($notifications as $notification)
                <div class="dropdown-item notification-item" @if($notification->data['url']) onclick="window.location.href='{{ $notification->data['url'] }}'" @endif>
                    <div class="d-flex">
                        <div class="me-3">
                            @php
                                $iconClass = 'fas fa-bell text-secondary';
                                $notificationType = $notification->data['type'] ?? 'general';
                                
                                switch($notificationType) {
                                    case 'student_admission':
                                        $iconClass = 'fas fa-user-plus text-primary';
                                        break;
                                    case 'fee_reminder':
                                        $iconClass = 'fas fa-money-bill text-warning';
                                        break;
                                    case 'meeting':
                                        $iconClass = 'fas fa-calendar text-info';
                                        break;
                                    case 'system':
                                        $iconClass = 'fas fa-cog text-success';
                                        break;
                                    case 'assignment':
                                        $iconClass = 'fas fa-tasks text-primary';
                                        break;
                                    case 'attendance':
                                        $iconClass = 'fas fa-calendar-check text-info';
                                        break;
                                    case 'result':
                                        $iconClass = 'fas fa-chart-line text-success';
                                        break;
                                }
                            @endphp
                            <i class="{{ $iconClass }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small">{{ $notification->data['message'] ?? 'New notification' }}</div>
                            <div class="text-muted small">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="ms-2">
                            <span class="badge bg-primary">New</span>
                        </div>
                    </div>
                </div>
                <hr class="dropdown-divider">
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-bell fa-2x mb-3"></i>
                    <div>No unread notifications</div>
                </div>
            @endforelse
        @else
            <div class="text-center py-4 text-muted">
                <i class="fas fa-bell fa-2x mb-3"></i>
                <div>No new notifications</div>
            </div>
        @endif
    </div>
    
    <div class="text-center p-2 border-top">
        <a href="#" class="text-primary text-decoration-none">View all notifications</a>
    </div>
</div>
