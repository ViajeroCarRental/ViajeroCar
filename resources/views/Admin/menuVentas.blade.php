<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Ventas</title>

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

        .ventas-home{
            width:100%;
            max-width:1200px;
        }

        /* BOTÓN DASHBOARD */
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

        /* HEADER */
        .ventas-menu-header{
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

        .ventas-menu-header h1{
            margin:0;
            font-size:34px;
            font-weight:900;
            color:#111827;
            text-align:center;
        }

        .ventas-menu-header::after{
            content:"";
            position:absolute;
            bottom:-15px;
            left:0;
            width:100%;
            height:1px;
            background:#e5e7eb;
        }

        /* GRID */
        .ventas-home-grid{
            display:grid;
            grid-template-columns:repeat(3,1fr);
            gap:30px;
        }

        /* CARDS */
        .ventas-home-card{
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
            font-size:17px;
            text-align:center;

            transition:.2s ease;
        }

        .ventas-home-card i{
            font-size:34px;
            color:#b22222;
        }

        .ventas-home-card:hover{
            border-color:#b22222;
            box-shadow:0 10px 30px rgba(178,34,34,.15);
            transform:translateY(-3px);
        }

        /* RESPONSIVE */
        @media(max-width:900px){
            .ventas-home-grid{
                grid-template-columns:repeat(2,1fr);
            }
        }

        @media(max-width:500px){
            .main-content{
                padding:24px;
                align-items:flex-start;
            }

            .ventas-menu-header{
                padding-top:55px;
                flex-direction:column;
                gap:10px;
                margin-bottom:35px;
            }

            .logo-menu{
                position:static;
                transform:none;
                width:120px;
            }

            .ventas-menu-header h1{
                font-size:28px;
            }

            .btn-dashboard{
                right:20px;
                top:20px;
                padding:9px 14px;
                font-size:14px;
            }

            .ventas-home-grid{
                grid-template-columns:1fr;
                gap:18px;
            }

            .ventas-home-card{
                height:150px;
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

    <div class="ventas-home">

        <!-- HEADER -->
        <div class="ventas-menu-header">
            <img src="{{ asset('img/Logo5.png') }}" class="logo-menu" alt="Viajero Car Rental">
            <h1>Menú Ventas</h1>
        </div>

        <!-- GRID -->
        <div class="ventas-home-grid">

            <a href="{{ route('rutaReservacionesAdmin') }}" class="ventas-home-card">
                <i class="fas fa-file-invoice"></i>
                <span>Reservaciones</span>
            </a>

            <a href="{{ route('rutaCotizar') }}" class="ventas-home-card">
                <i class="fas fa-briefcase"></i>
                <span>Cotizaciones</span>
            </a>

            <a href="{{ route('rutaReservacionesActivas') }}" class="ventas-home-card">
                <i class="fas fa-check-square"></i>
                <span>Bookings</span>
            </a>

            <a href="{{ route('rutaVisorReservaciones') }}" class="ventas-home-card">
                <i class="fas fa-eye"></i>
                <span>Visor</span>
            </a>

            <a href="{{ route('rutaAdministracionReservaciones') }}" class="ventas-home-card">
                <i class="fas fa-cogs"></i>
                <span>Contratos</span>
            </a>

            <a href="{{ route('ventas.historial') }}" class="ventas-home-card">
                <i class="fas fa-folder-open"></i>
                <span>Historial</span>
            </a>

            <a href="{{ route('rutaAltaCliente') }}" class="ventas-home-card">
                <i class="fas fa-user-plus"></i>
                <span>Alta Cliente</span>
            </a>

            <a href="{{ route('rutaFacturar') }}" class="ventas-home-card">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Facturar</span>
            </a>

            <a href="{{ route('rutaflotillastatus') }}" class="ventas-home-card">
                <i class="fas fa-warehouse"></i>
                <span>Flotilla</span>
            </a>

        </div>

    </div>

</div>

</body>
</html>
