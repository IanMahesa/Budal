<div class="sidebar">

    <div class="logo">
        <a href="{{ route('dashboard.index') }}">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-image">
            
        </a>
    </div>

   <ul class="sidebar-menu">
        <li class="has-submenu"></li>

        <li class="has-submenu">
            <a href="{{ route('dashboard.index') }}">
                <i class="fa-solid fa-desktop"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="has-submenu {{ Request::is('ui-elements*') ? 'active' : '' }}">
            <a href="#">
                <i class="fa-regular fa-gem"></i>
                <span>Master Data</span>
            </a>

            <ul class="submenu">
                @can('user-list')
                <li><a href="{{ route('user.index') }}">User</a></li>
                @endcan
                @can('role-list')
                    <li><a href="{{ route('role.index') }}">Role</a></li>
                @endcan
                @can('bagian-list')
                <li><a href="{{ route('bagian.index') }}">Bagian</a></li>
                @endcan
                @can('subag-list')
                <li><a href="{{ route('subag.index') }}">SubBagian</a></li>
                @endcan
                @can('pegawai-list')
                <li><a href="{{ route('pegawai.index') }}">Pegawai</a></li>
                @endcan
                @can('ijin-list')
                <li><a href="{{ route('ijin.index') }}">Perijinan</a></li>
                @endcan
            </ul>
        </li>

        <li class="has-submenu {{ Request::is('ui-elements*') ? 'active' : '' }}">
            <a href="#">
                <i class="fa-regular fa-gem"></i>
                <span>Master Generate QR Code</span>
            </a>

            <ul class="submenu">
                @can('geneqr-list')
                <li class="has-submenu">
                    <a href="{{ route('geneqr.index') }}">                        
                        Generate Pribadi
                    </a>
                </li>
                @endcan
                @can('geneqr-list')
                <li class="has-submenu">
                    <a href="{{ route('geneqrdin.index') }}">                        
                        Generate Dinas
                    </a>
                </li>
                @endcan               
            </ul>
        </li>
       
        
        <li class="has-submenu">
            <a href="{{ route('scan.index') }}">
                <i class="fas fa-search"></i>
                <span>Scan QR Code</span>
            </a>
        </li>

        <li class="has-submenu">
            <a href="{{ route('keluar.index') }}">
                <i class="fas fa-walking"></i>
                <span>Ijin Keluar</span>
            </a>
        </li>

        @can('rekap-list')
        <li class="has-submenu">
            <a href="{{ route('rekap.index') }}">
                <i class="fas fa-clipboard-list"></i>
                <span>Rekapitulasi</span>
            </a>
        </li>
        @endcan

        <li class="has-submenu">
            <a href="{{ route('qrcode.index') }}">
                <i class="fas fa-qrcode"></i>
                <span>QrCode</span>
            </a>
        </li>

    </ul>

    <div class="sidebar-toggle-wrapper">
        <button type="button" id="sidebarToggle" class="sidebar-toggle">
            <i class="fas fa-angles-left"></i>
            <span>Lipat Menu</span>
        </button>
    </div>

</div>