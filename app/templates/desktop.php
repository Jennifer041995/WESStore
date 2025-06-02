<?php
// Evitar notice si ya hay sesión activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Obtener rol actual (1=admin, 2=cliente, 3=empleado)
$rol = $_SESSION['usuario']['rol_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>WES Store | Inicio</title>
<link rel="icon" href="media/img/icono.png" type="image/x-icon">
<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link rel="stylesheet" href="resources/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="resources/sweetalert/sweetalert2.min.css">
<link rel="stylesheet" href="resources/bootstrap/css/adminlte.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.4/tiny-slider.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="media/img/logo.png" alt="WES Store Logo" height="60" width="60">
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a  href="<?php echo BASE_URL_PROJECT; ?>/home" class="nav-link">Inicio</a>
                </li>
                <?php if (empty($_SESSION['usuario'])): ?>
                <!-- Botón login si no hay sesión -->
                <li class="nav-item">
                    <a href="#" class="nav-link" data-toggle="modal" data-target="#authModal" data-auth-view="login">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </a>
                </li>
                <?php else: ?>
                <!-- Botón logout si hay sesión -->
                <li class="nav-item">
                    <a href="#" id="btnLogout" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Salir
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Sidebar -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a  href="<?php echo BASE_URL_PROJECT; ?>/home" class="brand-link">
                <img src="media/img/logo.png" alt="WES Store Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">WES Store</span>
            </a>

            <div class="sidebar">
                <?php if (!empty($_SESSION['usuario'])): ?>
                <!-- Panel usuario -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="media/img/avatares/avatar.png" class="img-circle" alt="Avatar">
                    </div>
                    <div class="info">
                        <a href="?mod=perfil" class="d-block"><?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></a>
                    </div>
                </div>
                <?php endif; ?>

                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                        <li class="nav-item">
                            <a href="<?php echo BASE_URL_PROJECT; ?>/inicio" class="nav-link">
                                <i class="nav-icon fas fa-home"></i>
                                <p>Inicio</p>
                            </a>
                        </li>


                        <li class="nav-item">
                            <a href="<?php echo BASE_URL_PROJECT; ?>/productos" class="nav-link">
                                <i class="nav-icon fas fa-boxes"></i>
                                <p>Productos</p>
                            </a>
                        </li>

                        <?php if (!empty($_SESSION['usuario'])): ?>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL_PROJECT; ?>/carrito" class="nav-link">
                                <i class="nav-icon fas fa-shopping-basket"></i>
                                <p>Carrito</p>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if (!empty($_SESSION['usuario'])): ?>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL_PROJECT; ?>/perfil" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Perfil</p>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Solo Admin (1) y Empleado (3) -->
                        <?php if (in_array($rol, [1,3], true)): ?>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL_PROJECT; ?>/admin" class="nav-link">
                                <i class="nav-icon fas fa-user-cog"></i>
                                <p>Administrador</p>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Solo Cliente (2) -->
                        <?php if ($rol === 2): ?>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL_PROJECT; ?>/pedidos" class="nav-link">
                                <i class="nav-icon fas fa-shopping-cart"></i>
                                <p>Mis Pedidos</p>
                            </a>
                        </li>
                        <?php endif; ?>

                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <?php @include(MODULO_PATH . "/" . $conf[$modulo]['archivo']); ?>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <strong>&copy; 2025 <a href="#">WES Store</a>.</strong> Todos los derechos reservados.
        </footer>
    </div>

    <!-- JS -->
    <script src="resources/jquery/jquery.min.js"></script>
    <script src="resources/popper/popper.min.js"></script>
    <script src="resources/bootstrap/js/bootstrap.min.js"></script>
    <script src="resources/sweetalert/sweetalert2.all.min.js"></script>
    <script src="resources/bootstrap/js/adminlte.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.4/min/tiny-slider.js"></script>
    <script src="app/controllers/public/main.js"></script>
    <script src="app/controllers/auth/authController.js"></script>

    <!-- Modal Auth -->
    <div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Autenticación</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>
</body>
</html>