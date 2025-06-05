// app/controllers/cart/checkout.js
function initCheckout() {
    let pasoActual = 1;
    const totalPasos = 4;

<<<<<<< HEAD
    function validarPaso(n) {
if (n === 1) {
    const nombres = $('#nombres').val().trim();
    const apellidos = $('#apellidos').val().trim();
    const email = $('#email').val().trim();
    const dui = $('#dui').val().trim();
    const telefono = $('#telefono').val().trim();

    const duiRegex = /^\d{7}-\d$/; // Formato de DUI: 1234567-8
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const telefonoRegex = /^\d{8}$/;

    if (!nombres) {
        Swal.fire('Campo requerido', 'Por favor ingresa tus nombres', 'warning');
        return false;
    }
    if (!apellidos) {
        Swal.fire('Campo requerido', 'Por favor ingresa tus apellidos', 'warning'); 
        return false;
    }
    if (!email || !emailRegex.test(email)) {
        Swal.fire('Correo inválido', 'Ingresa un correo válido (ej. ejemplo@dominio.com)', 'warning');
        return false;
    }
    if (!dui || !duiRegex.test(dui)) {
        Swal.fire('DUI inválido', 'Ingresa el DUI en formato correcto (ej. 1234567-8)', 'warning');
        return false;
    }
    if (!telefono || !telefonoRegex.test(telefono)) {
        Swal.fire('Teléfono inválido', 'El número debe tener 8 dígitos', 'warning');
        return false;
    }
}
=======
    // ---------------------------------------------------
    // 1) Definición DEL OBJETO “departamentos” ANTES DE USARLO
    // ---------------------------------------------------
    const departamentos = {
        'Ahuachapán': ['Ahuachapán','Atiquizaya','Turín','El Refugio','Guaymango','Jujutla','San Francisco Menéndez','San Lorenzo','San Pedro Puxtla','Tacuba'],
        'Cabañas': ['Sensuntepeque','Ilobasco','Cinquera','Guacotecti','Jutiapa','San Isidro','Tejutepeque','Victoria'],
        'Chalatenango': ['Chalatenango','La Palma','Arcatao','Azacualpa','Cancasque','Citalá','Comalapa','Concepción Quezaltepeque','Dulce Nombre de María','El Carrizal','El Paraíso','La Laguna','Las Vueltas','Nombre de Jesús','Nueva Concepción','Ojos de Agua','Potonico','San Antonio de la Cruz','San Antonio Los Ranchos','San Fernando','San Francisco Lempa','San Ignacio','San Isidro Labrador','San Luis del Carmen','San Miguel de Mercedes','San Rafael','Santa Rita','Tejutla'],
        'Cuscatlán': ['Cojutepeque','Suchitoto','Candelaria','El Carmen','El Rosario','Monte San Juan','Oratorio de Concepción','San Bartolomé Perulapía','San Cristóbal','San José Guayabal','San Pedro Perulapán','San Rafael Cedros','San Ramón','Santa Cruz Analquito','Santa Cruz Michapa','Tenancingo'],
        'La Libertad': ['Santa Tecla','Antiguo Cuscatlán','Colón','Quezaltepeque','Zaragoza','Ciudad Arce','Huizúcar','Jayaque','Jicalapa','La Libertad','Nuevo Cuscatlán','San Juan Opico','San Matías','San Pablo Tacachico','Tamanique','Talnique','Teotepeque','Tepecoyo','Sacacoyo','Chiltiupán','Comasagua'],
        'La Paz': ['Zacatecoluca','Olocuilta','San Juan Nonualco','San Luis Talpa','San Pedro Masahuat','San Rafael Obrajuelo','Santiago Nonualco','Tapalhuaca','Cuyultitán','Jerusalén','Mercedes La Ceiba','San Antonio Masahuat','San Emigdio','San Francisco Chinameca','San Juan Talpa','San Juan Tepezontes','San Miguel Tepezontes','San Pedro Nonualco','Santa María Ostuma'],
        'La Unión': ['La Unión','Santa Rosa de Lima','Anamorós','Bolívar','Concepción de Oriente','Conchagua','El Carmen','Estanzuelas','Intipucá','Jucuarán','Lolotique','Meanguera del Golfo','Moncagua','Nueva Esparta','Pasaquina','Polorós','San Alejo','San Carlos','San Fernando','San Jorge','San José','San Juan','San Lorenzo','Santa Rosa','Yayantique','Yucuaiquín'],
        'Morazán': ['San Francisco Gotera','Sociedad','Arambala','Cacaopera','Chilanga','Corinto','Delicias de Concepción','El Divisadero','El Rosario','Gualococti','Guatajiagua','Joateca','Jocoaitique','Jocoro','Lolotiquillo','Meanguera','Osicala','Perquín','San Carlos','San Fernando','San Isidro','San Simón','Sensembra','Yamabal','Yoloaiquín'],
        'San Miguel': ['San Miguel','Chinameca','Ciudad Barrios','Comacarán','Chapeltique','El Carmen','Guatajiagua','Jocoro','Lolotique','Moncagua','Nueva Guadalupe','Quelepa','San Antonio del Mosco','San Gerardo','San Jorge','San Luis de la Reina','San Rafael Oriente','Sesori','Uluazapa'],
        'San Salvador': ['San Salvador','Soyapango','Mejicanos','Ilopango','Apopa','Ayutuxtepeque','Cuscatancingo','Delgado','El Paisnal','Guazapa','Nejapa','Panchimalco','Rosario de Mora','San Marcos','San Martín','Santiago Texacuangos','Santo Tomás','Tonacatepeque','Aguilares'],
        'San Vicente': ['San Vicente','Apastepeque','San Cayetano Istepeque','San Esteban Catarina','San Ildefonso','San Lorenzo','San Sebastián','Santa Clara','Santo Domingo','Tecoluca','Verapaz','Guadalupe'],
        'Santa Ana': ['Santa Ana','Metapán','Chalchuapa','Coatepeque','El Congo','Candelaria de la Frontera','Texistepeque','Masahuat','San Antonio Pajonal','Santa Rosa Guachipilín','Santiago de la Frontera','El Porvenir','San Sebastián Salitrillo'],
        'Sonsonate': ['Sonsonate','Izalco','Acajutla','Armenia','Caluco','Cuyultitán','Juayúa','Nahuizalco','Salcoatitán','San Antonio del Monte','San Julián','Santa Catarina Masahuat','Santa Isabel Ishuatán','Santo Domingo de Guzmán','Sonzacate'],
        'Usulután': ['Usulután','Jiquilisco','Alegría','Berlín','Concepción Batres','Ereguayquín','Estanzuelas','Jucuapa','Mercedes Umaña','Nueva Granada','Ozatlán','Puerto El Triunfo','San Agustín','San Buenaventura','San Dionisio','San Francisco Javier','Santa Elena','Santa María','Santiago de María','Tecapán']
    };

    // ---------------------------------------------------
    // 2) Funciones “cargarDepartamentos” y “cargarMunicipios”
    // ---------------------------------------------------
    function cargarDepartamentos() {
        const $sel = $('#departamento')
          .empty()
          .append('<option value="">Seleccione departamento</option>');
        $.each(departamentos, (dpto, lista) => {
          $sel.append(`<option value="${dpto}">${dpto}</option>`);
        });
    }

    function cargarMunicipios() {
        const dpto = $('#departamento').val();
        const $sel = $('#municipio')
          .empty()
          .append('<option value="">Seleccione municipio</option>');
        (departamentos[dpto] || []).forEach(muni => {
          $sel.append(`<option value="${muni}">${muni}</option>`);
        });
    }

    // ---------------------------------------------------
    // 3) Nueva función: prellenarDatosUsuario
    // ---------------------------------------------------
    function prellenarDatosUsuario() {
        $.ajax({
            url: 'app/models/usuarios/get_perfil.php',
            method: 'GET',
            dataType: 'json'
        }).done(res => {
            if (!res.ok) {
                return; // Si no está logueado o hay error, no hacemos nada
            }

            // 3.1) Paso 1: Información personal
            $('#nombres').val(res.usuario.nombre || '');
            $('#apellidos').val(res.usuario.apellido || '');
            $('#email').val(res.usuario.email || '');
            $('#telefono').val(res.usuario.telefono || '');
            // Si en tu tabla usuarios guardas habitualmente también el DUI, podrías hacer:
            // $('#dui').val(res.usuario.dui || '');

            // 3.2) Paso 2: Dirección
            cargarDepartamentos(); // Primero llenamos el <select id="departamento">
            if (res.direccion) {
                const depto = res.direccion.departamento || '';
                $('#departamento').val(depto);

                // Luego cargamos municipios para ese departamento
                cargarMunicipios();
                const muni = res.direccion.ciudad || '';
                $('#municipio').val(muni);

                // Resto de la dirección (texto)
                $('#direccionCompleta').val(res.direccion.direccion || '');
            }
        }).fail(() => {
            // Si falla, simplemente no rellenamos nada
        });
    }

    // ---------------------------------------------------
    // 4) Resto de tu código original (validaciones, avanzar paso, finalizarCompra, etc.)
    //    ¡No cambiar nada de aquí hacia abajo!
    // ---------------------------------------------------
    function validarPaso(n) {
        if (n === 1) {
            const nombres = $('#nombres').val().trim();
            const apellidos = $('#apellidos').val().trim();
            const email = $('#email').val().trim();
            const dui = $('#dui').val().trim();
            const telefono = $('#telefono').val().trim();

            const duiRegex = /^\d{8}-\d$/; // Formato de DUI: 1234567-8
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const telefonoRegex = /^\d{8}$/;

            if (!nombres) {
                Swal.fire('Campo requerido', 'Por favor ingresa tus nombres', 'warning');
                return false;
            }
            if (!apellidos) {
                Swal.fire('Campo requerido', 'Por favor ingresa tus apellidos', 'warning'); 
                return false;
            }
            if (!email || !emailRegex.test(email)) {
                Swal.fire('Correo inválido', 'Ingresa un correo válido (ej. ejemplo@dominio.com)', 'warning');
                return false;
            }
            if (!dui || !duiRegex.test(dui)) {
                Swal.fire('DUI inválido', 'Ingresa el DUI en formato correcto (ej. 1234567-8)', 'warning');
                return false;
            }
            if (!telefono || !telefonoRegex.test(telefono)) {
                Swal.fire('Teléfono inválido', 'El número debe tener 8 dígitos', 'warning');
                return false;
            } 
        }
>>>>>>> 10c551a (Actualizacion)

        if (n === 2) {
            if (!$('#departamento').val() || !$('#municipio').val() || !$('#direccionCompleta').val().trim()) {
                Swal.fire('Completa todos los campos de dirección', '', 'warning');
                return false;
            }
        }
        if (n === 3) {
            const numeroTarjeta = $('#tarjeta').val().trim();
            const nombreTarjeta = $('#nombre-tarjeta').val().trim();
            const fechaVencimiento = $('#fecha-vencimiento').val().trim();
            const cvv = $('#cvv').val().trim();

            // Expresiones regulares
            const regexTarjeta = /^\d{16}$/; // 16 dígitos
            const regexCVV = /^\d{3,4}$/;    // 3 o 4 dígitos

            // Validar campos vacíos
            if (!numeroTarjeta || !nombreTarjeta || !fechaVencimiento || !cvv) {
                Swal.fire('Completa todos los campos de pago', '', 'warning');
                return false;
            }

            // Validar número de tarjeta
            if (!regexTarjeta.test(numeroTarjeta)) {
                Swal.fire('El número de tarjeta debe tener 16 dígitos numéricos', '', 'warning');
                return false;
            }

            // Validar nombre en la tarjeta
            if (nombreTarjeta.length < 4) {
                Swal.fire('El nombre en la tarjeta es muy corto', '', 'warning');
                return false;
            }
        }
        return true;
    }

    window.irAlPaso = function (n) {
        if (n > pasoActual && !validarPaso(pasoActual)) return;
        $('#paso-' + pasoActual).addClass('d-none');
        $('#paso-' + n).removeClass('d-none');
        pasoActual = n;
        const pct = Math.round((pasoActual - 1) / (totalPasos - 1) * 100);
        $('#barra-progreso').css('width', pct + '%').text(`Paso ${pasoActual} de ${totalPasos}`);
        if (pasoActual === 3) cargarResumenCarrito();
    };

    function cargarResumenCarrito() {
        $.getJSON('app/models/cart/obtener_carrito.php').done(data => {
            const $cont = $('#resumen-carrito');
            if (data.status !== 'success' || !data.data || data.data.length === 0) {
                $cont.html('<p>No hay productos en tu carrito.</p>');
                return;
            }
            let total = 0;
            const rows = data.data.map(p => {
                const precio = parseFloat(p.precio) || 0;
                const cantidad = parseInt(p.cantidad, 10) || 1;
                // Asumiendo que p.subtotal ya viene calculado del backend
                const subtotal = parseFloat(p.subtotal) || (precio * cantidad);
                total += subtotal;
                return `<tr>
                    <td>${p.nombre}</td>
                    <td>${cantidad}</td>
                    <td>$${precio.toFixed(2)}</td>
                    <td>$${subtotal.toFixed(2)}</td>
                </tr>`;
            }).join('');
            $cont.html(`
                <table class="table table-bordered">
                    <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr></thead>
                    <tbody>${rows}</tbody>
                </table>
                <h5 class="text-end">Total: $${total.toFixed(2)}</h5>
            `);
        }).fail(() => $('#resumen-carrito').html('<p>Error de conexión al cargar carrito.</p>'));
    }

    window.finalizarCompra = function () {
        if (!validarPaso(3)) return; // Asegúrate de que el último paso (pago) sea válido

        const cliente = {
            nombres: $('#nombres').val().trim(),
            apellidos: $('#apellidos').val().trim(),
            email: $('#email').val().trim(),
            dui: $('#dui').val().trim(),
            telefono: $('#telefono').val().trim(),
            departamento: $('#departamento').val(),
            municipio: $('#municipio').val(),
            direccionCompleta: $('#direccionCompleta').val().trim(),
            // Aquí añade los datos de la tarjeta si quieres que se envíen al backend
            // Aunque para una integración real de pagos, esto sería más complejo y seguro.
            tarjeta: $('#tarjeta').val().trim(),
            nombreTarjeta: $('#nombre-tarjeta').val().trim(),
            vencimiento: $('#fecha-vencimiento').val().trim(),
            cvv: $('#cvv').val().trim()
        };

        Swal.fire({
            title: '¿Confirmar pedido?',
            text: '¿Estás seguro de que todos los datos son correctos?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Revisar nuevamente'
        }).then(res => {
            if (!res.isConfirmed) return; // Si el usuario cancela, se detiene aquí

            // Muestra un indicador de carga mientras se procesa el pedido
            Swal.fire({
                title: 'Procesando pedido...',
                text: 'Por favor espera mientras confirmamos tu compra.',
                allowOutsideClick: false, // No permite cerrar haciendo clic fuera
                didOpen: () => {
                    Swal.showLoading(); // Muestra el spinner de carga
                }
            });

            $.ajax({
                url: 'app/models/cart/checkout.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ cliente }),
                dataType: 'json',
                xhrFields: { withCredentials: true } // Importante para el manejo de sesiones y cookies
            }).done(resp => {
                Swal.close(); // Cierra el indicador de carga una vez que la respuesta llega

                if (resp.status === 'success') {
                    irAlPaso(4); // Avanza al paso final de confirmación
                    let confirmacionHtml = `
                        <h6>¡Compra completada!</h6>
                        <p>Gracias por tu compra, <strong>${cliente.nombres} ${cliente.apellidos}</strong>.</p>
                        <p>Hemos recibido tu pedido correctamente. Tu número de pedido es: <strong>#${resp.pedido_id}</strong></p>
                    `;
                    // Añade un enlace para descargar la factura si la URL está disponible en la respuesta
                    if (resp.invoice_url) {
                        confirmacionHtml += `<p><a href="${resp.invoice_url}" target="_blank" class="btn btn-primary mt-3" id="descargar-factura-btn"><i class="fas fa-file-pdf"></i> Descargar Factura PDF</a></p>`;
                    } else if (resp.status === 'warning') {
                        // Si el pedido se hizo pero el PDF falló, muestra una advertencia
                        confirmacionHtml += `<p class="text-warning mt-3"><i class="fas fa-exclamation-triangle"></i> Advertencia: Pedido realizado, pero no se pudo generar la factura PDF. Por favor, contacta a soporte si la necesitas.</p>`;
                    }
                    $('#confirmacion-final').html(confirmacionHtml);

                    // >>>>> NUEVO: Disparar el evento 'carritoVaciado' al documento <<<<<
                    $(document).trigger('carritoVaciado');
                    console.log("Evento 'carritoVaciado' disparado desde checkout.js");


                } else {
                    // Si el estado no es 'success', muestra el mensaje de error del servidor
                    Swal.fire('Error', resp.message || 'No se pudo completar el pedido. Por favor, inténtalo de nuevo.', 'error');
                }
            }).fail((jqXHR, textStatus, errorThrown) => {
                Swal.close(); // Cierra el indicador de carga en caso de error AJAX
                let errorMessage = 'Ocurrió un problema al procesar tu pedido. Inténtalo más tarde.';
                // Intenta obtener un mensaje de error más específico de la respuesta del servidor
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                } else if (errorThrown) {
                    errorMessage = errorThrown;
                }
                Swal.fire('Error', errorMessage, 'error');
                console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
            });
        });
    };

<<<<<<< HEAD
    // Carga de departamentos/municipios (sin cambios, solo por completitud)
    const departamentos = {
        'Ahuachapán': ['Ahuachapán', 'Atiquizaya', 'Turín', 'El Refugio', 'Guaymango', 'Jujutla', 'San Francisco Menéndez', 'San Lorenzo', 'San Pedro Puxtla', 'Tacuba'],
        'Cabañas': ['Sensuntepeque', 'Ilobasco', 'Cinquera', 'Guacotecti', 'Jutiapa', 'San Isidro', 'Tejutepeque', 'Victoria'],
        'Chalatenango': ['Chalatenango', 'La Palma', 'Arcatao', 'Azacualpa', 'Cancasque', 'Citalá', 'Comalapa', 'Concepción Quezaltepeque', 'Dulce Nombre de María', 'El Carrizal', 'El Paraíso', 'La Laguna', 'Las Vueltas', 'Nombre de Jesús', 'Nueva Concepción', 'Ojos de Agua', 'Potonico', 'San Antonio de la Cruz', 'San Antonio Los Ranchos', 'San Fernando', 'San Francisco Lempa', 'San Ignacio', 'San Isidro Labrador', 'San Luis del Carmen', 'San Miguel de Mercedes', 'San Rafael', 'Santa Rita', 'Tejutla'],
        'Cuscatlán': ['Cojutepeque', 'Suchitoto', 'Candelaria', 'El Carmen', 'El Rosario', 'Monte San Juan', 'Oratorio de Concepción', 'San Bartolomé Perulapía', 'San Cristóbal', 'San José Guayabal', 'San Pedro Perulapán', 'San Rafael Cedros', 'San Ramón', 'Santa Cruz Analquito', 'Santa Cruz Michapa', 'Tenancingo'],
        'La Libertad': ['Santa Tecla', 'Antiguo Cuscatlán', 'Colón', 'Quezaltepeque', 'Zaragoza', 'Ciudad Arce', 'Huizúcar', 'Jayaque', 'Jicalapa', 'La Libertad', 'Nuevo Cuscatlán', 'San Juan Opico', 'San Matías', 'San Pablo Tacachico', 'Tamanique', 'Talnique', 'Teotepeque', 'Tepecoyo', 'Sacacoyo', 'Chiltiupán', 'Comasagua'],
        'La Paz': ['Zacatecoluca', 'Olocuilta', 'San Juan Nonualco', 'San Luis Talpa', 'San Pedro Masahuat', 'San Rafael Obrajuelo', 'Santiago Nonualco', 'Tapalhuaca', 'Cuyultitán', 'Jerusalén', 'Mercedes La Ceiba', 'San Antonio Masahuat', 'San Emigdio', 'San Francisco Chinameca', 'San Juan Talpa', 'San Juan Tepezontes', 'San Miguel Tepezontes', 'San Pedro Nonualco', 'Santa María Ostuma'],
        'La Unión': ['La Unión', 'Santa Rosa de Lima', 'Anamorós', 'Bolívar', 'Concepción de Oriente', 'Conchagua', 'El Carmen', 'Estanzuelas', 'Intipucá', 'Jucuarán', 'Lolotique', 'Meanguera del Golfo', 'Moncagua', 'Nueva Esparta', 'Pasaquina', 'Polorós', 'San Alejo', 'San Carlos', 'San Fernando', 'San Jorge', 'San José', 'San Juan', 'San Lorenzo', 'Santa Rosa', 'Yayantique', 'Yucuaiquín'],
        'Morazán': ['San Francisco Gotera', 'Sociedad', 'Arambala', 'Cacaopera', 'Chilanga', 'Corinto', 'Delicias de Concepción', 'El Divisadero', 'El Rosario', 'Gualococti', 'Guatajiagua', 'Joateca', 'Jocoaitique', 'Jocoro', 'Lolotiquillo', 'Meanguera', 'Osicala', 'Perquín', 'San Carlos', 'San Fernando', 'San Isidro', 'San Simón', 'Sensembra', 'Yamabal', 'Yoloaiquín'],
        'San Miguel': ['San Miguel', 'Chinameca', 'Ciudad Barrios', 'Comacarán', 'Chapeltique', 'El Carmen', 'Guatajiagua', 'Jocoro', 'Lolotique', 'Moncagua', 'Nueva Guadalupe', 'Quelepa', 'San Antonio del Mosco', 'San Gerardo', 'San Jorge', 'San Luis de la Reina', 'San Rafael Oriente', 'Sesori', 'Uluazapa'],
        'San Salvador': ['San Salvador', 'Soyapango', 'Mejicanos', 'Ilopango', 'Apopa', 'Ayutuxtepeque', 'Cuscatancingo', 'Delgado', 'El Paisnal', 'Guazapa', 'Nejapa', 'Panchimalco', 'Rosario de Mora', 'San Marcos', 'San Martín', 'Santiago Texacuangos', 'Santo Tomás', 'Tonacatepeque', 'Aguilares'],
        'San Vicente': ['San Vicente', 'Apastepeque', 'San Cayetano Istepeque', 'San Esteban Catarina', 'San Ildefonso', 'San Lorenzo', 'San Sebastián', 'Santa Clara', 'Santo Domingo', 'Tecoluca', 'Verapaz', 'Guadalupe'],
        'Santa Ana': ['Santa Ana', 'Metapán', 'Chalchuapa', 'Coatepeque', 'El Congo', 'Candelaria de la Frontera', 'Texistepeque', 'Masahuat', 'San Antonio Pajonal', 'Santa Rosa Guachipilín', 'Santiago de la Frontera', 'El Porvenir', 'San Sebastián Salitrillo'],
        'Sonsonate': ['Sonsonate', 'Izalco', 'Acajutla', 'Armenia', 'Caluco', 'Cuyultitán', 'Juayúa', 'Nahuizalco', 'Salcoatitán', 'San Antonio del Monte', 'San Julián', 'Santa Catarina Masahuat', 'Santa Isabel Ishuatán', 'Santo Domingo de Guzmán', 'Sonzacate'],
        'Usulután': ['Usulután', 'Jiquilisco', 'Alegría', 'Berlín', 'Concepción Batres', 'Ereguayquín', 'Estanzuelas', 'Jucuapa', 'Mercedes Umaña', 'Nueva Granada', 'Ozatlán', 'Puerto El Triunfo', 'San Agustín', 'San Buenaventura', 'San Dionisio', 'San Francisco Javier', 'Santa Elena', 'Santa María', 'Santiago de María', 'Tecapán']
    };

    function cargarDepartamentos() {
        const $sel = $('#departamento').empty().append('<option value="">Seleccione departamento</option>');
        $.each(departamentos, (dpto, list) => $sel.append(`<option value="${dpto}">${dpto}</option>`));
    }

    function cargarMunicipios() {
        const dpto = $('#departamento').val();
        const $sel = $('#municipio').empty().append('<option value="">Seleccione municipio</option>');
        (departamentos[dpto] || []).forEach(m => $sel.append(`<option value="${m}">${m}</option>`));
    }

    // Inicialización del checkout al cargar la página
    cargarDepartamentos();
    $('#departamento').on('change', cargarMunicipios);
    $('#barra-progreso').css('width', '0%').text(`Paso 1 de ${totalPasos}`);
}
=======
    // ---------------------------------------------------
    // 5) Inicialización al cargar el checkout
    // ---------------------------------------------------
    cargarDepartamentos();
    $('#departamento').on('change', cargarMunicipios);
    $('#barra-progreso').css('width', '0%').text(`Paso 1 de ${totalPasos}`);

    // 6) AÑADIDO: Prellenar con los datos del usuario guardados en BD
    prellenarDatosUsuario();
}
>>>>>>> 10c551a (Actualizacion)
