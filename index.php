<?php
    date_default_timezone_set('America/El_Salvador');
    setlocale(LC_TIME, 'spanish');

    session_start(); // ¡Fundamental! Siempre al inicio.

    require_once('conf.php'); // Carga tu archivo de configuración.

    // 1. Determinar el módulo solicitado
    $modulo = $_GET['mod'] ?? MODULO_DEFECTO; // Por defecto es 'home'

    // 2. Definir módulos completamente públicos
    // Estos módulos NO requieren que el usuario esté logeado.
    // 'home', 'error', 'productos', 'inicio' son los que se ven sin sesión.
    // 'login' es el contenido del modal, no una página completa a la que redirigir.
    $modulos_publicos_completos = ['home', 'error', 'productos', 'inicio'];

    // 3. Lógica de Seguridad Principal: Proteger módulos privados
    // Si el módulo solicitado NO es uno de los públicos completos,
    // Y NO hay una sesión de usuario activa (es decir, $_SESSION['usuario'] está vacío o no existe)
    if (!in_array($modulo, $modulos_publicos_completos) && empty($_SESSION['usuario'])) {
        // Redirige al usuario a la página de inicio (donde se muestra el modal de login).
        header('Location: ' . BASE_URL_PROJECT . '/home');
        exit(); // Detiene el script para evitar el bucle.
    }

    // 4. Control de Acceso Basado en Roles (RBAC) para usuarios logeados
    // Este bloque solo se ejecuta si hay una sesión de usuario activa.
    if (!empty($_SESSION['usuario'])) {
        // Obtiene el ID de rol del usuario de la sesión.
        $id_rol_usuario = $_SESSION['usuario']['rol_id'] ?? null;

        // Regla para el módulo 'admin': Solo accesible por roles 1 (Admin) y 3 (Empleado).
        if ($modulo === 'admin') {
            if (!in_array($id_rol_usuario, [1, 3])) {
                // Si el rol no es autorizado, redirige a 'home'.
                header('Location: ' . BASE_URL_PROJECT . '/home');
                exit();
            }
        }

        // Regla para el módulo 'pedidos': Solo accesible por el rol 2 (Cliente).
        if ($modulo === 'pedidos') {
            if ($id_rol_usuario !== 2) {
                // Si el rol no es cliente, redirige a 'home'.
                header('Location: ' . BASE_URL_PROJECT . '/home');
                exit();
            }
        }

        // Reglas para 'carrito' y 'perfil':
        // Estos módulos no tienen una verificación de rol específica aquí.
        // Si quieres que solo roles específicos accedan, añade una lógica similar a 'admin'/'pedidos'.
        // Si cualquier usuario logeado puede acceder, la lógica de seguridad del punto 3 ya los protege.
    }


    // --- 5. Lógica para cargar el módulo y su layout ---

    // Verifica si el módulo solicitado existe en el array de configuración ($conf).
    if (isset($conf[$modulo])) {
        $archivo_modulo = MODULO_PATH . "/" . $conf[$modulo]['archivo'];
        $layout_modulo = LAYOUT_PAHT . "/" . $conf[$modulo]['layout'];

        // Comprueba que los archivos físicos del módulo y del layout existan.
        if (file_exists($archivo_modulo) && file_exists($layout_modulo)) {

            // Caso especial para el módulo 'login' (que es el contenido del modal)
            if ($modulo === 'login') {
                // El contenido de login.html se carga, pero siempre se utiliza el LAYOUT_DESKTOP
                // como el layout principal de la página.
                $layout_final_para_contenido = LAYOUT_PAHT . "/" . LAYOUT_DESKTOP;

                // Captura el contenido de login.html.
                ob_start();
                include $archivo_modulo;
                $contenido_modulo = ob_get_clean();

                // Incluye el layout principal (desktop.php), que ya tiene el modal.
                include $layout_final_para_contenido;
            } else {
                // Para todos los demás módulos, se captura su contenido normal.
                ob_start();
                include $archivo_modulo;
                $contenido_modulo = ob_get_clean();

                // Y se incluye el layout correspondiente a ese módulo según la configuración.
                include $layout_modulo;
            }
        } else {
            // Si el archivo del módulo o el layout no existe físicamente, redirige a error 404.
            header('Location: ' . BASE_URL_PROJECT . '/error');
            exit();
        }
    } else {
        // Si el módulo solicitado no está definido en 'conf.php', redirige a error 404.
        header('Location: ' . BASE_URL_PROJECT . '/error');
        exit();
    }
?>