<nav class="topbar">

    <div class="left-menu">
        <h1 class="topbar-title">Sistem Informasi Perijinan</h1>
    </div>

    <div class="right-menu">
        <div class="dropdown">        
            <button class="border-0 bg-transparent p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">

                <span class="position-relative d-inline-block">
                    <img src="{{ asset('images/user.png') }}"
                        class="avatar"
                        style="cursor: pointer;" alt="User">

                    @if(($jumlahNotifikasi ?? 0) > 0)
                        <span class="badge bg-danger rounded-pill notification-badge"> {{ $jumlahNotifikasi }} </span> 
                    @endif
                </span>
                
            </button>
   
    <ul class="dropdown-menu dropdown-menu-end shadow message-dropdown">
        @can('notifikasi-list')
        <li>
            <a href="{{ route('notifikasi-ijin.index') }}"
            class="dropdown-item">
                <i class="fas fa-bell me-2"></i>
                Notifikasi Ijin
            </a>
        </li>
        @endcan
        <li>
            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit"
                        class="dropdown-item"
                        onclick="confirmLogout(event)">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Logout
                </button>
            </form>
        </li>
    </ul>
</div>
</div>
</nav>