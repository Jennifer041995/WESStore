$(document).ready(function () {
    // Helper para peticiones Ajax
    function ajaxPost(url, payload, onSuccess, onError) {
        $.ajax({
            url: url,
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            dataType: 'json'
        })
        .done(onSuccess)
        .fail(function (jqXHR) {
            let msg = 'Error en la petición';
            if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                msg = jqXHR.responseJSON.error;
            } else if (jqXHR.responseText) {
                try {
                    const r = JSON.parse(jqXHR.responseText);
                    if (r.error) msg = r.error;
                } catch (e) {
                    // Response no es JSON
                }
            }
            onError({ message: msg });
        });
    }

    // Registro (delegado)
    $(document)
    .off('submit', '#formRegister')
    .on('submit', '#formRegister', function (e) {
        e.preventDefault();
        const password = $('#regPassword').val();
        const hashedPasswordClient = sha256(password);

        const data = {
            nombre: $('#regNombre').val(),
            apellido: $('#regApellido').val(),
            email: $('#regEmail').val(),
            password: hashedPasswordClient
        };
        ajaxPost('app/models/auth/register.php', data,
            function (res) {
                // Primero ocultamos el modal para no dejar foco en un elemento con aria-hidden
                $('#authModal').modal('hide');

                // Luego disparamos el SweetAlert
                Swal.fire({
                    icon: 'success',
                    title: '¡Registrado!',
                    text: 'Te hemos dado la bienvenida.',
                    confirmButtonText: 'Aceptar'
                }).then(() => location.reload());
            },
            function (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message,
                    confirmButtonText: 'Cerrar'
                });
            }
        );
    })

    // Login
    .off('submit', '#formLogin')
    .on('submit', '#formLogin', function (e) {
        e.preventDefault();
        const password = $('#logPassword').val();
        const hashedPasswordClient = sha256(password);

        const data = {
            email: $('#logEmail').val(),
            password: hashedPasswordClient
        };
        ajaxPost('app/models/auth/login.php', data,
            function (res) {
                // Ocultamos el modal antes de lanzar el SweetAlert
                $('#authModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: '¡Bienvenido!',
                    text: 'Has iniciado sesión.',
                    confirmButtonText: 'Aceptar'
                }).then(() => location.reload());
            },
            function (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message,
                    confirmButtonText: 'Cerrar'
                });
            }
        );
    });

    // Logout (fuera del modal)
    $('#btnLogout').off('click').on('click', function () {
        Swal.fire({
            icon: 'warning',
            title: '¿Cerrar sesión?',
            text: '¿Estás seguro de que deseas salir?',
            showCancelButton: true,
            confirmButtonText: 'Sí, salir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Si confirma, hacemos la llamada AJAX para cerrar sesión
                ajaxPost('app/models/auth/logout.php', {},
                    function () {
                        // Redirigir al home después de cerrar sesión
                        location.href = BASE_URL_APP + '/home';
                    },
                    function (err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: err.message,
                            confirmButtonText: 'Cerrar'
                        });
                    }
                );
            }
            // Si cancela, no hace nada
        });
    });
});