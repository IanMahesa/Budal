<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

    <title>@yield('title')</title>

    <style>
        .sidebar {
            width: 250px;
            min-width: 250px;
            height: 100vh;

            position: fixed;
            left: 0;
            top: 0;

            z-index: 1000;

            display: flex;
            flex-direction: column;

            transition: width 0.3s ease;

            overflow: visible;
        }

        .logo {
            flex-shrink: 0;
            text-align: center;
            padding: 20px 10px;
        }

        .logo-image {
            max-width: 150px;
            transition: all 0.3s ease;
        }

        .sidebar-menu {
            flex: 1;
            min-height: 0;
            overflow: visible;
            margin: 0;
            padding: 10px 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .sidebar-menu li a i {
            min-width: 35px;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar-toggle-wrapper {
            flex: 0 0 auto;
            margin-top: auto;
            flex-shrink: 0;
            padding: 12px;
            border-top: 1px solid rgba(255,255,255,0.10);
        }

        .sidebar-toggle {
            width: 100%;
            height: 42px;

            border: none;
            border-radius: 8px;

            background: rgba(255,255,255,0.08);
            color: #fff;

            cursor: pointer;

            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;

            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background: rgba(255,255,255,0.15);
        }

        .sidebar.collapsed {
            width: 75px;
            min-width: 75px;
            overflow: visible;
        }

        .sidebar.collapsed .logo {
            padding: 20px 5px;
        }

        .sidebar.collapsed .logo-image {
            width: 45px;
            height: 45px;
            object-fit: contain;
        }

        .sidebar.collapsed .sidebar-menu li a span {
            display: none !important;
        }

        .sidebar.collapsed .sidebar-menu li > a {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        .sidebar.collapsed .sidebar-menu li > a i {
            min-width: auto;
            margin: 0;
        }

        .sidebar.collapsed .sidebar-menu {
            overflow: visible;
        }

        .sidebar.collapsed .sidebar-toggle-wrapper {
            padding: 12px 10px;
        }

        .sidebar.collapsed .sidebar-toggle {
            width: 100%;
        }

        .sidebar.collapsed .sidebar-toggle span {
            display: none;
        }

        .sidebar.collapsed .sidebar-toggle {
            gap: 0;
        }

        .sidebar.collapsed .sidebar-toggle i {
            margin: 0;
        }

        .main {
            min-width: 0;
            margin-left: 250px;
            transition: margin-left 0.3s ease;
        }

        .sidebar.collapsed ~ .main {
            margin-left: 75px;
        }

        body{
            margin:0;
            background:#f4f5f7;
            font-family:'Segoe UI',sans-serif;
        }

        .wrapper{
            display:flex;
        }

        .sidebar{
            width:150px;
            min-height:100vh;
            background:#2f3136;
            color:#fff;
        }

        .logo{
            height:80px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:20px;
            font-weight:bold;
        }

        .sidebar-menu{
            list-style:none;
            padding:0;
            margin:0;
        }

        .sidebar-menu li{
            position:relative;
            border-bottom: 1px solid rgba(255,255,255,.15);
        }

        .sidebar-menu a{
            display:block;
            padding:12px 20px;
            color:#fff;
            text-decoration:none;
            background:#2f3136;
        }

        .sidebar-menu a:hover{
            background:#40444b;
        }
      
        .has-submenu > a{
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;

            padding: 15px 10px;
            text-align: center;
        }

        .has-submenu > a i{
            font-size: 24px;
        }

        .has-submenu > a span{
            font-size: 13px;
            font-weight: 600;
        }

        .has-submenu{
            position: relative;
        }

        .has-submenu.active{
            background:#40444b;
        }

        .has-submenu.active>a{
            background:#40444b;
            color:#fff;
        }

        .has-submenu.active::before{
            content:"";
            position:absolute;
            left:0;
            top:10%;
            height:80%;
            width:4px;
            background:#ff4d4d;
            border-radius:0 4px 4px 0;
        }

        .has-submenu:hover .submenu{
            display:block;
        }

        .has-submenu.submenu-open > .submenu{
            display:block;
        }

        .submenu{
            position:absolute;
            top:0;
            left:100%;          
            min-width:180px;
            background:#313a4a;
            list-style:none;
            padding:0;
            margin:0;
            display:none;
            border-radius:0px;
            overflow:hidden;
            box-shadow:0 5px 15px rgba(0,0,0,.2);
            z-index:999;
        }

        .submenu a{
            padding:12px 15px;
            white-space:nowrap;
            background:#313a4a;
        }

        .submenu a:hover{
            background:#21293a;
        }

        .submenu li{
            border-bottom: 1px solid rgba(255,255,255,.15);
        }

        .submenu li:last-child{
            border-bottom: none;
        }

        .main{
            flex:1;
        }

        .topbar{
            height:80px;
            background:#ef6666;
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:0 30px;
            color:white;
        }

        .left-menu{
            display:flex;
            gap:30px;
        }

        .left-menu a{
            color:white;
            text-decoration:none;
        }

        .right-menu{
            display:flex;
            gap:25px;
            align-items:center;
        }

        .icon-btn{
            position: relative;
            color:rgb(255, 255, 255);
            font-size:20px;
            text-decoration:none;
        }

        .icon-btn .badge{
            position:absolute;
            top:-4px;
            right:-5px;
            font-size:10px;
        }

        .message-dropdown{
            position: absolute;
            top: 55px;
            right: 0;
            width: 280px;
            max-height: 300px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,.15);
            clip-path: unset;
            overflow: visible;
        }

        .message-dropdown::before{
            content:"";
            position:absolute;
            top:-12px;
            right:28px;
            border-left:12px solid transparent;
            border-right:12px solid transparent;
            border-bottom:12px solid #ddd;
        }

        .message-dropdown::after{
            content:"";
            position:absolute;
            top:-11px;
            right:29px;
            border-left:11px solid transparent;
            border-right:11px solid transparent;
            border-bottom:11px solid #fff;
        }

        .message-item{
            display:flex;
            align-items:flex-start;
            gap:12px;
            padding:15px;
            white-space:normal;
        }

        .message-item:hover{
            background:#f8f9fa;
        }

        .unread{
            background:#fff4f4;
        }

        .avatar-message{
            width:50px;
            height:50px;
            border-radius:50%;
            object-fit:cover;
        }

        .message-content{
            flex:1;
        }

        .message-content strong{
            color:#ff5b5b;
            font-size:16px;
            font-weight:600;
        }

        .message-content small{
            color:#999;
            font-size:12px;
        }

        .message-content p{
            margin:5px 0 0;
            color:#777;
            font-size:12px;
        }

        .avatar{
            width:40px;
            height:40px;
            border-radius:50%;
        }

        .content{
            padding:30px;
        }

        .dashboard-card{
            border:none;
            border-radius:10px;
            box-shadow:0 4px 10px rgba(0,0,0,.08);
        }

        .chart-placeholder{
            height:250px;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#aaa;
        }

        .tabel-card {
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-tambah{
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            width: 100px;
            height: 40px;
            background-color: #38b000;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 0 #007200;
            transition:
                background-color .2s ease,
                transform .2s ease,
                box-shadow .2s ease;
        }

        .btn-tambah:hover{
            background-color: #70e000;
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #007200;
            color: #fff;
        }

        .btn-tambah:active{
            background-color: #008000;
            transform: translateY(2px);
            box-shadow: 0 2px 0 #007200;
        }

        .btn-tambah:focus{
            outline: none;
            box-shadow:
                0 4px 0 #007200,
                0 0 0 3px rgba(13,110,253,.25);
        }

        .btn-edit,
        .btn-delete,
        .btn-show,
        .btn-recap{    
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            width: 40px;
            height: 40px;    
            font-weight: 600;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            color: #fff;
            transition: 
                background-color .2s ease,
                transform .2s ease,
                box-shadow .2s ease;
        }

        .btn-edit{
            background-color: #fad643;
            box-shadow: 0 4px 0 #dbb42c;
        }

        .btn-edit:hover{
            background-color: #ffe169;
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #dbb42c;
            color: #fff;
        }

        .btn-edit:active{
            background-color: #edc531;
            transform: translateY(2px);
            box-shadow: 0 2px 0 #dbb42c;
        }

        .btn-edit:focus{
            outline: none;
            box-shadow:
                0 4px 0 #dbb42c,
                0 0 0 3px rgba(253, 249, 13, 0.25);
        }

        .btn-delete{
            background-color: #bd1f36;
            box-shadow: 0 4px 0 #a71e34;
        }

        .btn-delete:hover{
            background-color: #c71f37;
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #a71e34;
            color: #fff;
        }

        .btn-delete:active{
            background-color: #b21e35;
            transform: translateY(2px);
            box-shadow: 0 2px 0 #a71e34;
        }

        .btn-delete:focus{
            outline: none;
            box-shadow:
                0 4px 0 #a71e34,
                0 0 0 3px rgba(253, 13, 33, 0.25);
        }

        .btn-show{
            background-color: #2196f3;
            box-shadow: 0 4px 0 #1e88e5;
        }

        .btn-show:hover{
            background-color: #42a5f5;
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #1e88e5;
            color: #fff;
        }
    
        .btn-show:active{
            background-color: #64b5f6;
            transform: translateY(2px);
            box-shadow: 0 2px 0 #1e88e5;
        }

        .btn-show:focus{
            outline: none;
            box-shadow:
                0 4px 0 #1e88e5,
                0 0 0 3px rgba(33, 150, 243, 0.25);
        }

        .btn-recap{
            background-color: #3dccc7;
            box-shadow: 0 4px 0 #07beb8;
        }

        .btn-recap:hover{
            background-color: #68d8d6;
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #07beb8;
            color: #fff;
        }

        .btn-recap:active{
            background-color: #0fa3b1;
            transform: translateY(2px);
            box-shadow: 0 2px 0 #07beb8;
        }

        .btn-recap:focus{
            outline: none;
            box-shadow:
                0 4px 0 #07beb8,
                0 0 0 3px rgba(64, 129, 117, 0.25);
        }

        .table-divider{
                    width: 100%;
                    height: 2px;
                    background-color: #a0a0a0;
                    border-radius: 10px;
                    margin-bottom:15px;
                }

        #tabelUser thead th{
            background: #4382DF; 
            color: white;
            text-align: center;
            vertical-align: middle;
            font-weight: 600;
            border-color: #2C5EAD;
        }

        #tabelRekap thead th{
            background: #070F2B; 
            color: white;
            text-align: center;
            vertical-align: middle;
            font-weight: 600;
            border-color: #070F2B;
        }

        #tabelRole thead th{
            background: #4382DF; 
            color: white;
            text-align: center;
            vertical-align: middle;
            font-weight: 600;
            border-color: #2C5EAD;
        }

        .user-card{
            width: fit-content;
            min-width: 900px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .btn-back{
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            width: 100px;
            height: 40px;
            background-color: #495057;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 0 #343a40;
            transition:
                background-color .2s ease,
                transform .2s ease,
                box-shadow .2s ease;
        }

        .btn-back:hover{
            background-color: #6c757d;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #343a40;
        }

        .btn-back:active{
            background-color: #adb5bd;
            transform: translateY(2px);
            box-shadow: 0 2px 0 #343a40;
        }

        .btn-back:focus{
            outline: none;
            box-shadow:
                0 4px 0 #343a40,
                0 0 0 3px rgba(108,117,125,.25);
        }

       .btn-save{
        display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            width: 100px;
            height: 40px;
            background-color: #56c3e7;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 0 #2b7da0;
            transition: 
                background-color .2s ease,
                transform .2s ease,
                box-shadow .2s ease;
        }

        .btn-save:hover{
            background-color: #6dcbea;
            transform: translateY(-2px);
            box-shadow: 0 7px 0 #2b7da0;
            color: #fff;
        }

        .btn-save:active{
            background-color: #40bbe4;
            transform: translateY(3px);
            box-shadow: 0 2px 0 #2b7da0;
        }

        .btn-save:focus{
            outline: none;
            box-shadow:
                0 4px 0 #0A043C,
                0 0 0 3px rgba(122,152,191,.35);
        }

        #tabelAccor thead th{
            background: #295F98; 
            color: white;
            text-align: center;
            vertical-align: middle;
            font-weight: 600;
            border-color: #295F98;
        }

        .btn-tambah1 {
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;            
            font-size: 14px;
            margin-right: 15px;
            margin-left: 15px;
            cursor: pointer;
            box-shadow: 0 4px 0 #089838;
            transition:
                background-color .2s ease,
                transform .2s ease,
                box-shadow .2s ease;
        }
    
        .btn-tambah1:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #089838;
            color: #fff;
        }   
        
        .btn-tambah1:active{
            background-color: #70b769;
            transform: translateY(2px);
            box-shadow: 0 2px 0 #089838;
        }
        .btn-tambah1:focus{
            outline: none;
            box-shadow:
                0 4px 0 #08980f,
                0 0 0 3px rgba(33, 253, 13, 0.25);
        }

        .btn-cetak{
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            width: 100px;
            height: 40px;
            background-color: #e77f56;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 0 #a0502b;
            transition: 
                background-color .2s ease,
                transform .2s ease,
                box-shadow .2s ease;
        }

        .btn-cetak:hover{
            background-color: #ea9b6d;
            transform: translateY(-2px);
            box-shadow: 0 7px 0 #a05a2b;
            color: #fff;
        }

        .btn-cetak:active{
            background-color: #e47440;
            transform: translateY(3px);
            box-shadow: 0 2px 0 #a05a2b;
        }

        .btn-cetak:focus{
            outline: none;
            box-shadow:
                0 4px 0 #983608,
                0 0 0 3px rgba(191, 144, 122, 0.35);
        }

        .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
            opacity: 1;
            margin-left: auto;
            margin-right: 10px;
        }

        .bg-pink{
            background:#e83e8c !important;
            color:#fff;
        }

        .btn-download{
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            width: 100px;
            height: 40px;
            background-color: #158467;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 0 #065446;
            transition: 
                background-color .2s ease,
                transform .2s ease,
                box-shadow .2s ease;
        }

        .btn-download:hover{
            background-color: #00917C;
            transform: translateY(-2px);
            box-shadow: 0 7px 0 #065446;
            color: #fff;
        }

        .btn-download:active{
            background-color: #00AD7C;
            transform: translateY(3px);
            box-shadow: 0 2px 0 #065446;
        }

        .btn-download:focus{
            outline: none;
            box-shadow:
                0 4px 0 #3A9679,
                0 0 0 3px rgba(191, 144, 122, 0.35);
        }

        .btn-scanqr{
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            width: 200px;
            height: 40px;
            background-color: #0A5EB0;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 0 #0A1D56;
            transition: 
                background-color .2s ease,
                transform .2s ease,
                box-shadow .2s ease;
        }

        .btn-scanqr:hover{
            background-color: #1C6DD0;
            transform: translateY(-2px);
            box-shadow: 0 7px 0 #0A1D56;
            color: #fff;
        }

        .btn-scanqr:active{
            background-color: #0E46A3;
            transform: translateY(3px);
            box-shadow: 0 2px 0 #0A1D56;
        }

        .btn-scanqr:focus{
            outline: none;
            box-shadow:
                0 4px 0 #091353,
                0 0 0 3px rgba(191, 144, 122, 0.35);
        }

        .btn-filter{
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            width: 170px;
            height: 36px;
            background-color: #1e6091;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 0 #184e77;
            transition: 
                background-color .2s ease,
                transform .2s ease,
                box-shadow .2s ease;
        }

        .btn-filter:hover{
            background-color: #1a759f;
            transform: translateY(-2px);
            box-shadow: 0 7px 0 #184e77;
            color: #fff;
        }

        .btn-filter:active{
            background-color: #003566;
            transform: translateY(3px);
            box-shadow: 0 2px 0 #184e77;
        }

        .btn-filter:focus{
            outline: none;
            box-shadow:
                0 4px 0 #001d3d,
                0 0 0 3px rgba(191, 144, 122, 0.35);
        }

        .btn-reset{
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            width: 170px;
            height: 36px;
            background-color: #6c757d;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 0 #343a40;
            transition: 
                background-color .2s ease,
                transform .2s ease,
                box-shadow .2s ease;
        }

        .btn-reset:hover{
            background-color: #adb5bd;
            transform: translateY(-2px);
            box-shadow: 0 7px 0 #343a40;
            color: #fff;
        }

        .btn-reset:active{
            background-color: #495057;
            transform: translateY(3px);
            box-shadow: 0 2px 0 #343a40;
        }

        .btn-reset:focus{
            outline: none;
            box-shadow:
                0 4px 0 #343a40,
                0 0 0 3px rgba(191, 144, 122, 0.35);
        }

        #btnIjin .btn{
            min-height:90px;
            border-radius:12px;
            font-weight:600;
            transition:.2s;
        }

        #btnIjin .btn:hover{
            transform:translateY(-2px);
        }

        .btn-laporan{
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            width: 180px;
            height: 40px;
            background-color: #1B3A57;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 0 #102A43;
            transition:
                background-color .2s ease,
                transform .2s ease,
                box-shadow .2s ease;
        }

        .btn-laporan:hover{
            background-color: #244E70;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #102A43;
        }

        .btn-laporan:active{
            background-color: #2F6690;
            transform: translateY(2px);
            box-shadow: 0 2px 0 #102A43;
        }

        .btn-laporan:focus{
            outline: none;
            box-shadow:
                0 4px 0 #102A43,
                0 0 0 3px rgba(27, 58, 87, .25);
        }

        .custom-header {
            background: linear-gradient(135deg, #3a0ca3, #4361ee);
            color: #fff;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            border-bottom: 2px solid #03045e;
            box-shadow: 0 2px 6px rgba(0,0,0,.15);
        }

        .custom-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 1.3rem;
            letter-spacing: .5px;
        }

        .custom-header i {
            font-size: 1.2rem;
        }

         /* ACCORDION */

        /* Header saat masih tertutup */
        .accordion-button.collapsed{
            background: linear-gradient(135deg, #0d47a1, #2196f3);
            color: #fff;
            font-weight: 600;
            transition: .3s;
        }

        /* Badge saat tertutup */
        .accordion-button.collapsed .badge{
            background: #ffffff !important;
            color: #0353a4;
        }

        /* Header saat terbuka */
        .accordion-button:not(.collapsed){
            background: linear-gradient(135deg, #1976d2, #90caf9);
            color: #fff;
            font-weight: 600;
            box-shadow: none;
        }

        /* Badge saat terbuka */
        .accordion-button:not(.collapsed) .badge{
            background: #fff !important;
            color: #1976d2;
        }

        /* Hilangkan outline bootstrap */
        .accordion-button:focus{
            box-shadow: none;
        }

        /* Warna icon panah */
        .accordion-button::after{
            filter: brightness(0) invert(1);
        }

        /* Hover */
        .accordion-button:hover{
            opacity: .95;
        }

        /* Body accordion */
        .accordion-body{
            background: #f8f9fa;
        }

        .role-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #eaf2ff;
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
        }

        .badge {
            font-weight: 500;
            padding: 6px 9px;
            border-radius: 6px;
        }

        .alert {
            border-radius: 8px;
            font-size: 14px;
        }

        .role-form {
        max-width: 550px;
        margin-bottom: 30px;
    }

    .role-form label {
        font-size: 13px;
        font-weight: 500;
        color: #555863;
        margin-bottom: 7px;
    }

    .role-form .form-control {
        height: 40px;
        border: 1px solid #cfd1d8;
        border-radius: 3px;
        font-size: 13px;
        box-shadow: none;
    }

    .role-form .form-control:focus {
        border-color: #6252c7;
        box-shadow: 0 0 0 2px rgba(98, 82, 199, .08);
    }

    .permission-checkbox {
        width: 14px;
        height: 14px;
        appearance: none;
        -webkit-appearance: none;
        border: 1px solid #4b3cc4;
        border-radius: 3px;
        cursor: pointer;
        position: relative;
        vertical-align: middle;
        background: #fff;
    }

    .permission-checkbox:checked {
        background: #4b3cc4;
        border-color: #4b3cc4;
    }

    .permission-checkbox:checked::after {
        content: "";
        position: absolute;
        left: 3px;
        top: 0px;
        width: 5px;
        height: 9px;
        border: solid #fff;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    .manage-checkbox {
        border-color: #4b3cc4;
    }

   .right-menu {
        display: flex;
        align-items: center;
        margin-right: 80px;
    }

    .right-menu > .dropdown {
        margin-right: 12px;
    }

    .avatar {
        margin-left: 0;
    }

    .logout-btn {
        margin-right: 0;
    }

    .logo-image {
        width: 55px;
        height: auto;
    }

    .notification-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        font-size: 10px;
        min-width: 18px;
        height: 18px;
        padding: 2px 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid white;
    }

    .message-dropdown {
        margin-top: 8px !important;
        margin-right: -20px !important;
    }
    </style>    

    @stack('styles')

</head>

<body>

    <div class="wrapper">

    @include('partials.sidebar')

    <div class="main">

        @include('partials.header')

        <div class="content">

            @yield('content')

        </div>

    </div>

</div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    
    @stack('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        const sidebar = document.querySelector('.sidebar');
        const toggle = document.getElementById('sidebarToggle');
        const collapsedStorageKey = 'sidebar-collapsed';

        function updateToggleIcon() {
            const icon = toggle.querySelector('i');

            if (sidebar.classList.contains('collapsed')) {
                icon.classList.remove('fa-angles-left');
                icon.classList.add('fa-angles-right');
            } else {
                icon.classList.remove('fa-angles-right');
                icon.classList.add('fa-angles-left');
            }
        }

        if (localStorage.getItem(collapsedStorageKey) === 'true') {
            sidebar.classList.add('collapsed');
        }
        updateToggleIcon();

        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem(
                collapsedStorageKey,
                sidebar.classList.contains('collapsed') ? 'true' : 'false'
            );
            updateToggleIcon();

        });

        sidebar.querySelectorAll('.has-submenu > a[href="#"]').forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                link.parentElement.classList.toggle('submenu-open');
            });
        });

    });
    </script>

    <script>
        window.addEventListener('pageshow', function (event) {
            const navigation = performance.getEntriesByType('navigation')[0];
            const isBackForward = navigation && navigation.type === 'back_forward';

            if (event.persisted || isBackForward) {
                window.location.reload();
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.penilaian-carousel').owlCarousel({
                loop: true,
                margin: 20,
                nav: false,
                dots: true,
                autoplay: true,
                autoplayTimeout: 3000,
                responsive: {
                    0: { items: 1 },
                    768: { items: 2 },
                    992: { items: 3 }
                }
            });
        });
    </script>

    <script>
    function confirmSaveUser(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Simpan',
            text: 'Apakah Anda yakin ingin menyimpan user baru?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) {
                    // trigger jQuery submit handlers first (populate fields), then submit form
                    try {
                        $('#formSaveUser').trigger('submit');
                    } catch (e) {}
                    document.getElementById('formSaveUser').submit();
                }
            });
        }
    </script>

    <script>
    function confirmUpdateUser(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Perubahan',
            text: 'Apakah Anda yakin ingin menyimpan perubahan user?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('formUpdateUser').submit();
                }
            });
        }
    </script>

    <script>
    function confirmHapusUser(button) {
        let form = button.closest('form');

        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus user ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
    </script>

    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: "{{ session('success') }}",
            confirmButtonText: 'OK'
        });
    </script>
    @endif

    <script>
        function penilaian() {
            Swal.fire({
                title: 'Konfirmasi Penilaian',
                text: 'Apakah Anda yakin ingin mengirim penilaian ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) {

                    // Simpan / submit form
                    document.getElementById('formPenilaian').submit();

                }
            });
        }
    </script>

    <script>
        function confirmLogout(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: 'Apakah Anda yakin ingin keluar?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = e.target.closest('form');
                    if (form) form.submit();
                }
            });
        }
    </script>

    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: "{{ session('success') }}",
            confirmButtonText: 'OK'
        });
    </script>
    @endif

    <script>
        function penilaianX() {
            Swal.fire({
                title: 'Batalkan Penilaian?',
                text: 'Apakah Anda yakin tidak jadi memberikan penilaian?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak'
            }).then((result) => {

                if (result.isConfirmed) {
                    $('#modalPenilaian').modal('hide');

                    $('#formPenilaian')[0].reset();

                    $('#formAwal').show();

                    Swal.fire({
                        icon: 'error',
                        title: 'Penilaian Dibatalkan',
                        text: 'Anda telah membatalkan pemberian penilaian.',
                        confirmButtonText: 'OK'
                    });

                } else {
                    $('#modalPenilaian').modal('show');
                }

            });

        }
    </script>

    <script>
        $(document).ready(function() {
            $('#tabelPenilaian').DataTable({
                pageLength: 10,
                lengthMenu: [
                    [5, 10, 25, 50, 100, -1],
                    [5, 10, 25, 50, 100, "Semua"]
                ],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data",
                    zeroRecords: "Data tidak ditemukan",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                }
            });
        });
    </script>

    <script>
        document.querySelectorAll('[id="togglePassword"]').forEach(function(btn) {
            btn.addEventListener('click', function () {
                const input = btn.closest('.input-group')?.querySelector('input');
                const icon = btn.querySelector('i');
                if (!input) return;

                if (input.type === 'password') {
                    input.type = 'text';
                    icon?.classList.remove('fa-eye');
                    icon?.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon?.classList.remove('fa-eye-slash');
                    icon?.classList.add('fa-eye');
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            if ($('#tabelUser').length) {
                const $tbody = $('#tabelUser tbody');
                const $placeholder = $tbody.children('tr');

                if ($placeholder.length === 1 && $placeholder.children('td').length === 1) {
                    $placeholder.remove();
                }

                $('#tabelUser').DataTable({
                    pageLength: 10,
                    order: [],
                    lengthMenu: [
                        [5, 10, 25, 50, 100, -1],
                        [5, 10, 25, 50, 100, "Semua"]
                    ],
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        infoEmpty: "Tidak ada data",
                        emptyTable: "Tidak ada data",
                        zeroRecords: "Data tidak ditemukan",
                        paginate: {
                            first: "Awal",
                            last: "Akhir",
                            next: "Berikutnya",
                            previous: "Sebelumnya"
                        }
                    }
                });
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            const $table = $('#tabelRekap');

            if ($table.length && !$.fn.DataTable.isDataTable($table[0])) {
                const $tbody = $table.find('tbody');
                const $placeholder = $tbody.children('tr');

                if ($placeholder.length === 1 && $placeholder.children('td').length === 1) {
                    $placeholder.remove();
                }

                $table.DataTable({
                    pageLength: 10,
                    lengthMenu: [
                        [5, 10, 25, 50, 100, -1],
                        [5, 10, 25, 50, 100, "Semua"]
                    ],
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        infoEmpty: "Tidak ada data",
                        emptyTable: "Tidak ada data",
                        zeroRecords: "Data tidak ditemukan",
                        paginate: {
                            first: "Awal",
                            last: "Akhir",
                            next: "Berikutnya",
                            previous: "Sebelumnya"
                        }
                    }
                });
            }
        });
    </script>

<script>
    function submitSelection(actionUrl) {

    const checked = document.querySelectorAll('.checkItem:checked');

    if (checked.length === 0) {

        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silakan pilih minimal satu QR Code yang akan dicetak.',
            confirmButtonText: 'OK'
        });

        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = actionUrl;

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);

    checked.forEach(function(item) {

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'id_qrcode[]';
        input.value = item.value;
        form.appendChild(input);

    });

    document.body.appendChild(form);
    form.submit();
}
</script>
</body>

</html>