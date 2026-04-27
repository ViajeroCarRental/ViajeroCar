<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Menú Flotilla</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <style>
        *{
            box-sizing:border-box;
        }

        body{
            margin:0;
            font-family:'Poppins', system-ui, Arial, sans-serif;
            background:#ffffff;
        }

        /* CONTENEDOR GENERAL */
        .main-content{
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:40px;
            position:relative;
        }

        .flotilla-home{
            width:100%;
            max-width:1200px;
        }

        /* 🔙 BOTÓN DASHBOARD */
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

        /* ================= HEADER ================= */
        .flotilla-menu-header{
            position:relative;
            width:100%;
            margin-bottom:50px;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        /* LOGO IZQUIERDA */
        .logo-menu{
            position:absolute;
            left:0;
            top:50%;
            transform:translateY(-50%);
            width:150px;
        }

        /* TÍTULO */
        .flotilla-menu-header h1{
            margin:0;
            font-size:34px;
            font-weight:900;
            color:#111827;
            text-align:center;
        }

        /* LINEA DECORATIVA */
        .flotilla-menu-header::after{
            content:"";
            position:absolute;
            bottom:-15px;
            left:0;
            width:100%;
            height:1px;
            background:#e5e7eb;
        }

        /* GRID */
        .flotilla-home-grid{
            display:grid;
            grid-template-columns:repeat(3,1fr);
            gap:30px;
        }

        /* CARDS */
        .flotilla-home-card{
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

        .flotilla-home-card i{
            font-size:36px;
            color:#b22222;
        }

        .flotilla-home-card:hover{
            border-color:#b22222;
            box-shadow:0 10px 30px rgba(178,34,34,.15);
            transform:translateY(-3px);
        }

        /* RESPONSIVE */
        @media(max-width:900px){
            .flotilla-home-grid{
                grid-template-columns:repeat(2,1fr);
            }
        }

        @media(max-width:500px){
            .flotilla-home-grid{
                grid-template-columns:1fr;
            }

            .flotilla-home-card{
                height:150px;
            }

            .logo-menu{
                width:120px;
            }

            .btn-dashboard{
                right:20px;
                top:20px;
            }
        }
    </style>
</head>

<body>

<div class="main-content">

    <!-- BOTÓN DASHBOARD -->
    <a href="{{ route('rutaDashboard') }}" class="btn-dashboard">
        <i class="fas fa-arrow-left"></i>
        Dashboard
    </a>

    <div class="flotilla-home">

        <!-- HEADER -->
        <div class="flotilla-menu-header">
            <img src="{{ asset('img/Logo5.png') }}" class="logo-menu" alt="Viajero Car Rental">
            <h1>Menú de flotilla</h1>
        </div>

        <!-- GRID -->
        <div class="flotilla-home-grid">

            <a href="{{ route('rutaFlotilla') }}" class="flotilla-home-card">
                <i class="fas fa-truck"></i>
                <span>Flotilla</span>
            </a>

            <a href="{{ route('rutaMantenimiento') }}" class="flotilla-home-card">
                <i class="fas fa-wrench"></i>
                <span>Mantenimiento</span>
            </a>

            <a href="{{ route('rutaPolizas') }}" class="flotilla-home-card">
                <i class="fas fa-file-alt"></i>
                <span>Pólizas</span>
            </a>

            <a href="{{ route('rutaCarroceria') }}" class="flotilla-home-card">
                <i class="fas fa-car-side"></i>
                <span>Carrocería</span>
            </a>

            <a href="{{ route('rutaSeguros') }}" class="flotilla-home-card">
                <i class="fas fa-shield-alt"></i>
                <span>Seguros</span>
            </a>

            <a href="{{ route('rutaGastos') }}" class="flotilla-home-card">
                <i class="fas fa-coins"></i>
                <span>Gastos</span>
            </a>

            <a href="{{ route('vehiculos.documentos') }}" class="flotilla-home-card">
                <i class="fas fa-folder-open"></i>
                <span>Documentación</span>
            </a>

        </div>

    </div>

</div>

</body>
</html>