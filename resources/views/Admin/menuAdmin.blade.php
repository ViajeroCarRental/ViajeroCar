<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Admin</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <style>
        *{ box-sizing:border-box; }

        body{
            margin:0;
            font-family:'Poppins', system-ui, Arial, sans-serif;
            background:#ffffff;
        }

        .main-content{
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:40px;
            position:relative;
        }

        .admin-home{
            width:100%;
            max-width:1200px;
        }

        .btn-dashboard{
            position:absolute;
            top:30px;
            right:40px;
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:10px 16px;
            border-radius:12px;
            background:#fff;
            color:#b22222;
            border:1px solid rgba(178,34,34,.25);
            text-decoration:none;
            font-weight:800;
            box-shadow:0 10px 25px rgba(0,0,0,.06);
            transition:.2s ease;
        }

        .btn-dashboard:hover{
            background:#b22222;
            color:#fff;
        }

        .admin-menu-header{
            position:relative;
            width:100%;
            margin-bottom:50px;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .logo-menu{
            position:absolute;
            left:0;
            top:50%;
            transform:translateY(-50%);
            width:150px;
        }

        .admin-menu-header h1{
            margin:0;
            font-size:34px;
            font-weight:900;
            color:#111827;
            text-align:center;
        }

        .admin-menu-header::after{
            content:"";
            position:absolute;
            bottom:-15px;
            left:0;
            width:100%;
            height:1px;
            background:#e5e7eb;
        }

        .admin-home-grid{
            display:grid;
            grid-template-columns:repeat(3,1fr);
            gap:30px;
        }

        .admin-home-card{
            height:180px;
            background:#fff;
            border-radius:14px;
            border:1px solid #e5e7eb;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            gap:18px;
            text-decoration:none;
            color:#111;
            font-weight:800;
            font-size:18px;
            text-align:center;
            transition:.2s ease;
        }

        .admin-home-card i{
            font-size:36px;
            color:#b22222;
        }

        .admin-home-card:hover{
            border-color:#b22222;
            box-shadow:0 10px 30px rgba(178,34,34,.15);
            transform:translateY(-3px);
        }

        @media(max-width:900px){
            .admin-home-grid{
                grid-template-columns:repeat(2,1fr);
            }
        }

        @media(max-width:500px){
            .main-content{
                padding:24px;
                align-items:flex-start;
            }

            .admin-menu-header{
                padding-top:55px;
                flex-direction:column;
                gap:10px;
            }

            .logo-menu{
                position:static;
                transform:none;
                width:120px;
            }

            .btn-dashboard{
                right:20px;
                top:20px;
            }

            .admin-home-grid{
                grid-template-columns:1fr;
            }

            .admin-home-card{
                height:150px;
            }
        }
        /* ========================================
        RESPONSIVE (SIN MODIFICAR TU DISEÑO)
        ======================================== */

        /* 💻 Tablet grande */
        @media (max-width: 1024px){

            .dashboard-grid,
            .cards-dashboard,
            .grid-dashboard {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 22px !important;
            }

            .card,
            .dashboard-card {
                height: auto;
                min-height: 150px;
            }

            .main-content {
                padding: 90px 28px 40px;
            }

        }

        /* 📱 Tablet vertical */
        @media (max-width: 768px){

            .dashboard-grid,
            .cards-dashboard,
            .grid-dashboard {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 18px !important;
            }

            .card,
            .dashboard-card {
                font-size: 15px;
                padding: 16px;
            }

            .card i,
            .dashboard-card i {
                font-size: 28px;
            }

            .header-dashboard,
            .dashboard-header {
                flex-direction: column;
                align-items: center;
                gap: 10px;
                text-align: center;
            }

            .header-dashboard h1,
            .dashboard-header h1 {
                font-size: 26px;
            }

        }

        /* 📱 Celulares */
        @media (max-width: 560px){

            .dashboard-grid,
            .cards-dashboard,
            .grid-dashboard {
                grid-template-columns: 1fr !important;
                gap: 14px !important;
            }

            .card,
            .dashboard-card {
                width: 100%;
                min-height: 120px;
                font-size: 14px;
                border-radius: 14px;
            }

            .card i,
            .dashboard-card i {
                font-size: 24px;
            }

            .main-content {
                padding: 80px 16px 28px;
            }

        }

        /* 📱 iPhone chico */
        @media (max-width: 390px){

            .card,
            .dashboard-card {
                min-height: 105px;
            }

            .header-dashboard h1,
            .dashboard-header h1 {
                font-size: 22px;
            }

            .main-content {
                padding-left: 12px;
                padding-right: 12px;
            }

        }
    </style>
</head>

<body>

<div class="main-content">

    <a href="{{ route('rutaDashboard') }}" class="btn-dashboard">
        <i class="fas fa-arrow-left"></i>
        Dashboard
    </a>

    <div class="admin-home">

        <div class="admin-menu-header">
            <img src="{{ asset('img/Logo5.png') }}" class="logo-menu" alt="Viajero Car Rental">
            <h1>Menú Admin</h1>
        </div>

        <div class="admin-home-grid">

            <a href="{{ route('admin.usuarios.index') }}" class="admin-home-card">
                <i class="fas fa-users"></i>
                <span>Usuarios</span>
            </a>

            <a href="{{ route('roles.index') }}" class="admin-home-card">
                <i class="fas fa-user-shield"></i>
                <span>Roles y permisos</span>
            </a>

            <a href="{{ route('categorias.index') }}" class="admin-home-card">
                <i class="fas fa-tags"></i>
                <span>Categorías</span>
            </a>

            <a href="{{ route('paqueteseguros.index') }}" class="admin-home-card">
                <i class="fas fa-shield-alt"></i>
                <span>Seguros</span>
            </a>

            <a href="{{ route('paquetesindividuales.index') }}" class="admin-home-card">
                <i class="fas fa-layer-group"></i>
                <span>Seguros Individuales</span>
            </a>

            <a href="{{ route('servicios.index') }}" class="admin-home-card">
                <i class="fas fa-layer-group"></i>
                <span>Adicionales</span>
            </a>

        </div>

    </div>

</div>

</body>
</html>
