<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$rol = isset($_SESSION['usuario']['rol_id']) ? (int)$_SESSION['usuario']['rol_id'] : null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WES Store | Inicio</title>
    <link rel="icon" href="media/img/icono.png" type="image/x-icon">
    
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rajdhani:wght@300;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="resources/sweetalert/sweetalert2.min.css">
    <link rel="stylesheet" href="resources/bootstrap/css/adminlte.min.css">
    <style>
        :root {
            --neon-blue: #0ff0fc;
            --neon-purple: #9600ff;
            --cyber-yellow: #f5d300;
            --matrix-green: #00ff41;
            --dark-space: #0a0a1a;
            --deep-space: #12122e;
        }

        /* === Estructura General === */
        body {
            background-color: var(--dark-space);
            color: #e0e0e0;
            font-family: 'Rajdhani', sans-serif;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5 {
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1px;
        }

        /* === Preloader Holográfico === */
        .preloader {
            background: var(--dark-space);
        }
        .animation__shake {
            filter: drop-shadow(0 0 10px var(--neon-blue));
            animation: hologram 2s infinite alternate;
        }
        @keyframes hologram {
            0% { opacity: 0.8; }
            100% { opacity: 1; filter: drop-shadow(0 0 15px var(--neon-purple)); }
        }

        /* === Navbar Cyberpunk === */
        .main-header {
            background: linear-gradient(90deg, #1a1a2e 0%, #16213e 100%) !important;
            border-bottom: 1px solid var(--neon-blue) !important;
            box-shadow: 0 0 20px rgba(15, 240, 252, 0.3);
        }
        .navbar-light .navbar-nav .nav-link {
            color: white !important;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
            margin: 0 5px;
            position: relative;
        }
        .navbar-light .navbar-nav .nav-link:hover {
            color: var(--neon-blue) !important;
        }
        .navbar-light .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--neon-blue);
            transition: width 0.3s;
        }
        .navbar-light .navbar-nav .nav-link:hover::after {
            width: 100%;
        }

        /* === Sidebar Terminal === */
        .main-sidebar {
            background: linear-gradient(180deg, #0f0f23 0%, #1a1a2e 100%) !important;
            border-right: 1px solid var(--neon-purple) !important;
        }
        .brand-link {
            border-bottom: 1px dashed rgba(15, 240, 252, 0.3) !important;
        }
        .brand-text {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            text-shadow: 0 0 10px var(--neon-blue);
        }
        .nav-sidebar .nav-link {
            color: #b8b8b8 !important;
            margin: 5px 0;
            border-radius: 0 !important;
            transition: all 0.3s;
        }
        .nav-sidebar .nav-link:hover {
            color: white !important;
            background: rgba(15, 240, 252, 0.1) !important;
            border-left: 3px solid var(--neon-blue) !important;
        }
        .nav-sidebar .nav-link.active {
            background: rgba(15, 240, 252, 0.15) !important;
            color: white !important;
            border-left: 3px solid var(--neon-blue) !important;
        }
        .nav-icon {
            margin-right: 10px;
            color: var(--neon-blue);
        }

        /* === Panel de Usuario === */
        .user-panel {
            border-bottom: 1px dashed rgba(15, 240, 252, 0.3);
        }
        .user-panel .info a {
            font-family: 'Orbitron', sans-serif;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        /* === Contenido Principal === */
        .content-wrapper {
            background: linear-gradient(135deg, var(--dark-space) 0%, var(--deep-space) 100%) !important;
            background-attachment: fixed !important;
        }

        /* === Footer Neón === */
        .main-footer {
            background: linear-gradient(90deg, #1a1a2e 0%, #16213e 100%) !important;
            border-top: 1px solid var(--neon-purple) !important;
            color: white !important;
            font-family: 'Orbitron', sans-serif;
            padding: 15px !important;
        }
        .main-footer a {
            color: var(--neon-blue) !important;
            text-decoration: none;
            transition: all 0.3s;
        }
        .main-footer a:hover {
            text-shadow: 0 0 10px var(--neon-blue);
        }

        /* === Modal de Autenticación === */
        #authModal .modal-content {
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 100%);
            border: 1px solid var(--neon-blue);
            color: white;
        }
        #authModal .modal-header {
            border-bottom: 1px solid var(--neon-purple);
        }
        #authModal .modal-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--neon-blue);
            text-shadow: 0 0 5px var(--neon-blue);
        }
        #authModal .close {
            color: var(--neon-blue);
            text-shadow: none;
        }

        /* === Efectos Especiales === */
        .tech-glow {
            animation: glow 2s infinite alternate;
        }
        @keyframes glow {
            from { text-shadow: 0 0 5px var(--neon-blue); }
            to { text-shadow: 0 0 10px var(--neon-purple), 0 0 20px var(--neon-blue); }
        }

        .cyber-border {
            position: relative;
        }
        .cyber-border::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border: 2px solid var(--neon-blue);
            border-radius: 5px;
            z-index: -1;
            opacity: 0.5;
        }

        /* === Responsive === */
        @media (max-width: 768px) {
            .main-header {
                box-shadow: 0 0 10px rgba(15, 240, 252, 0.3);
            }
            .brand-text {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Preloader Holográfico -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="media/img/logo.png" alt="WES Store Logo" height="80" width="80">
            <div class="mt-3" style="font-family: 'Orbitron', sans-serif; color: var(--neon-blue);">CARGANDO SISTEMA...</div>
        </div>

        <!-- Navbar Cyberpunk -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="?mod=home" class="nav-link"><i class="fas fa-home"></i> INICIO</a>
                </li>
                <?php if (empty($_SESSION['usuario'])): ?>
                <li class="nav-item">
                    <a href="#" class="nav-link tech-glow" data-toggle="modal" data-target="#authModal" data-auth-view="login">
                        <i class="fas fa-sign-in-alt"></i> ACCESO
                    </a>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a href="#" id="btnLogout" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> SALIR
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Sidebar Terminal -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="?mod=home" class="brand-link cyber-border">
                <img src="media/img/logo.png" alt="WES Store Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">WES STORE</span>
            </a>

            <div class="sidebar">
                <?php if (!empty($_SESSION['usuario'])): ?>
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="media/img/avatares/avatar.png" class="img-circle" alt="Avatar">
                    </div>
                    <div class="info">
                        <a href="?mod=perfil" class="d-block"><?php echo strtoupper(htmlspecialchars($_SESSION['usuario']['nombre'])); ?></a>
                    </div>
                </div>
                <?php endif; ?>

                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="?mod=inicio" class="nav-link">
                                <i class="nav-icon fas fa-terminal"></i>
                                <p>INICIO</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="?mod=productos" class="nav-link">
                                <i class="nav-icon fas fa-cubes"></i>
                                <p>PRODUCTOS</p>
                            </a>
                        </li>

                        <?php if (!empty($_SESSION['usuario'])): ?>
                        <li class="nav-item">
                            <a href="?mod=carrito" class="nav-link">
                                <i class="nav-icon fas fa-shopping-basket"></i>
                                <p>CARRITO</p>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if (in_array($rol, [1,3], true)): ?>
                        <li class="nav-item">
                            <a href="?mod=empleado" class="nav-link">
                                <i class="nav-icon fas fa-user-shield"></i>
                                <p>PANEL EMPLEADOS</p>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if (in_array($rol, [1], true)): ?>
                        <li class="nav-item">
                            <a href="?mod=admin" class="nav-link">
                                <i class="nav-icon fas fa-user-cog"></i>
                                <p>PANEL ADMIN</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Contenido Principal -->
        <div class="content-wrapper">
            <?php @include(MODULO_PATH . "/" . $conf[$modulo]['archivo']); ?>
        </div>

        <!-- Footer Futurista -->
        <footer class="main-footer">
            <strong>&copy; 2025 <a href="#">WES STORE</a>.</strong> TODOS LOS DERECHOS RESERVADOS.
            <div class="float-right d-none d-sm-inline-block">
                <b>VERSIÓN</b> 3.0.0
            </div>
        </footer>
    </div>

    <!-- Scripts (sin cambios) -->
    <script src="resources/jquery/jquery.min.js"></script>
    <script src="resources/popper/popper.min.js"></script>
    <script src="resources/bootstrap/js/bootstrap.min.js"></script>
    <script src="resources/sweetalert/sweetalert2.all.min.js"></script>
    <script src="resources/bootstrap/js/adminlte.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.4/min/tiny-slider.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="app/controllers/public/main.js"></script>
    <script src="app/controllers/auth/authController.js"></script>

    <!-- Modal de Autenticación -->
    <div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-key"></i> AUTENTICACIÓN</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>
</body>
</html>