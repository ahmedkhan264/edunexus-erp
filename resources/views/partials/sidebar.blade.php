<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('login') }}" class="sidebar-logo">
            <i class="fas fa-graduation-cap"></i>
            <span>EduNexus</span>
        </a>
    </div>
    
    <nav class="sidebar-menu">
        @php
            $navigationItems = \App\Services\RoleRedirectService::getNavigationItems();
        @endphp
        
        @foreach($navigationItems as $item)
            <div class="sidebar-item">
                @if(isset($item['submenu']) && count($item['submenu']) > 0)
                    <a href="#" 
                       class="sidebar-link {{ $item['active'] ? 'active' : '' }}" 
                       data-bs-toggle="collapse" 
                       data-bs-target="#submenu-{{ md5($item['title']) }}"
                       aria-expanded="false">
                        <i class="{{ $item['icon'] }}"></i>
                        <span>{{ $item['title'] }}</span>
                        <i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                    
                    <div class="sidebar-submenu collapse {{ $item['active'] ? 'show' : '' }}" id="submenu-{{ md5($item['title']) }}">
                        @foreach($item['submenu'] as $subitem)
                            @php
                                $routeExists = isset($subitem['route']) && Route::has($subitem['route']);
                            @endphp
                            @if($routeExists)
                                <a href="{{ route($subitem['route']) }}" class="sidebar-submenu-item">
                                    {{ $subitem['title'] }}
                                </a>
                            @else
                                <span class="sidebar-submenu-item disabled" style="opacity:0.6; pointer-events:none;">
                                    {{ $subitem['title'] }} (coming soon)
                                </span>
                            @endif
                        @endforeach
                    </div>
                @else
                    @php
                        $routeExists = isset($item['route']) && Route::has($item['route']);
                    @endphp
                    @if($routeExists)
                        <a href="{{ route($item['route']) }}" 
                           class="sidebar-link {{ $item['active'] ? 'active' : '' }}">
                            <i class="{{ $item['icon'] }}"></i>
                            <span>{{ $item['title'] }}</span>
                        </a>
                    @else
                        <span class="sidebar-link disabled" style="opacity:0.6; pointer-events:none;">
                            <i class="{{ $item['icon'] }}"></i>
                            <span>{{ $item['title'] }} (coming soon)</span>
                        </span>
                    @endif
                @endif
            </div>
        @endforeach
        
        <!-- User Info Section -->
        <div class="sidebar-item mt-4">
            <div class="px-3">
                <div class="text-white-50 small mb-2">Logged in as:</div>
                <div class="text-white fw-bold">{{ auth()->user()->name }}</div>
                <div class="text-white-75 small">{{ auth()->user()->role->name }}</div>
            </div>
        </div>
        
        <!-- Logout Button -->
        <div class="sidebar-item">
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="sidebar-link text-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </nav>
</aside>