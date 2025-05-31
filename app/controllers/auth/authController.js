$(document).ready(function() {
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
        .fail(function(jqXHR) {
            // Manejo de errores robusto
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
      .on('submit', '#formRegister', function(e) {
        e.preventDefault();
        const data = {
            nombre:   $('#regNombre').val(),
            apellido: $('#regApellido').val(),
            email:    $('#regEmail').val(),
            password: $('#regPassword').val()
        };
        ajaxPost('app/models/auth/register.php', data,
            function(res) {
                Swal.fire('¡Registrado!','Te hemos dado la bienvenida.','success')
                  .then(() => $('#authModal').modal('hide'));
            },
            function(err) {
                Swal.fire('Error', err.message, 'error');
            }
        );
    })

    // Login (delegado)
    .off('submit', '#formLogin')
    .on('submit', '#formLogin', function(e) {
        e.preventDefault();
        const data = {
            email:    $('#logEmail').val(),
            password: $('#logPassword').val()
        };
        ajaxPost('app/models/auth/login.php', data,
            function(res) {
                Swal.fire('¡Bienvenido!','Has iniciado sesión.', 'success')
                  .then(() => location.reload());
            },
            function(err) {
                Swal.fire('Error', err.message, 'error');
            }
        );
    });

    // Logout (fuera del modal)
    $('#btnLogout').off('click').on('click', function() {
        ajaxPost('app/models/auth/logout.php', {},
            function() {
                location.href = '?mod=home';
            },
            function(err) {
                Swal.fire('Error', err.message, 'error');
            }
        );
    });
});
