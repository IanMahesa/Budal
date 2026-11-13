<div class="sidebar">

    <div class="logo">
        @can('dashboard-list')
        <a href="{{ route('dashboard.index') }}">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-image">
            
        </a>
        @else
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-image">
        @endcan
    </div>

   <ul class="sidebar-menu">
        <li class="has-submenu"></li>

        @can('dashboard-list')
            <li class="has-submenu">
                <a href="{{ route('dashboard.index') }}">
                    <i class="fa-solid fa-desktop"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        @endcan

        @canany(['user-list', 'role-list', 'bagian-list', 'subag-list', 'pegawai-list', 'perijinan-list'])
        <li class="has-submenu {{ Request::is('ui-elements*') ? 'active' : '' }}">
            <a href="#">
                <i class="fas fa-folder-open"></i>
                <span>Master Data</span>
            </a>

            <ul class="submenu">
                @can('bagian-list')
                <li><a href="{{ route('bagian.index') }}">Bagian</a></li>
                @endcan
                @can('subag-list')
                <li><a href="{{ route('subag.index') }}">SubBagian</a></li>
                @endcan
                @can('pegawai-list')
                <li><a href="{{ route('pegawai.index') }}">Pegawai</a></li>
                @endcan                
                @can('user-list')
                <li><a href="{{ route('user.index') }}">User</a></li>
                @endcan
                @can('role-list')
                    <li><a href="{{ route('role.index') }}">Role</a></li>
                @endcan
            </ul>
        </li>
        @endcanany

        @can('geneqr-list')
        <li class="has-submenu {{ Request::is('ui-elements*') ? 'active' : '' }}">
            <a href="#">
                <i class="fas fa-barcode"></i>
                <span>Generate QR Code</span>
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
                <li class="has-submenu">
                    <a href="{{ route('genlembur.index') }}">                        
                        Generate Lembur
                    </a>
                </li>              
            </ul>
        </li>
        @endcan
       
        
        @can('scan-list')
            <li class="has-submenu">
                <a href="{{ route('scan.index') }}">
                    <i class="fas fa-search"></i>
                    <span>Scan QR Code</span>
                </a>
            </li>
        @endcan

         <li class="has-submenu {{ Request::is('ui-elements*') ? 'active' : '' }}">
            <a href="#">
                <i class="fas fa-clipboard-list"></i>
                <span>Laporan</span>
            </a>

            <ul class="submenu">
                
                <li class="has-submenu">
                    <a href="{{ route('keluar.index') }}">
                        <span>Ijin</span>
                    </a>
                </li>
               
                <li class="has-submenu">
                    <a href="{{ route('lembur.index') }}">
                        <span>Lembur</span>
                    </a>
                </li>               
                        
            </ul>
        </li>

        @can('rekap-list')
        <li class="has-submenu">
            <a href="{{ route('rekap.index') }}">
                <i class="fas fa-clipboard-list"></i>
                <span>Rekapitulasi</span>
            </a>
        </li>
        @endcan

        @can('qrcode-list')
            <li class="has-submenu">
                <a href="{{ route('qrcode.index') }}">
                    <i class="fas fa-qrcode"></i>
                    <span>QrCode</span>
                </a>
            </li>
        @endcan

       
        <li class="has-submenu {{ Request::is('ui-elements*') ? 'active' : '' }}">
            <a href="#">
                <i class="fas fa-fingerprint"></i>
                <span>Rekap Saya</span>
            </a>

            <ul class="submenu">
                
                <li class="has-submenu">
                    <a href="{{ route('rekappeg.index') }}">
                        <span>Ijin</span>
                    </a>
                </li>
               
                <li class="has-submenu">
                    <a href="{{ route('rekaplem.index') }}">
                        <span>Lembur</span>
                    </a>
                </li>               
                        
            </ul>
        </li>

    <div class="sidebar-toggle-wrapper">
        <button type="button" id="sidebarToggle" class="sidebar-toggle">
            <i class="fas fa-angles-left"></i>
            <span>Lipat Menu</span>
        </button>
    </div>

</div>