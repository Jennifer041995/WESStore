$(function() { 
  const $alertBox = $('#alert-perfil');

  // 1) Función: mostrar alerta temporal
  function showAlert(type, message) {
    $alertBox
      .removeClass('alert-success alert-danger alert-warning')
      .addClass(`alert alert-${type}`)
      .text(message)
      .fadeIn(200)
      .delay(3000)
      .fadeOut(200);
  }

  // ======================================================================
  // 2) Configuración de departamentos y municipios (misma lógica del checkout)
  // ======================================================================
  const departamentos = {
    'Ahuachapán': ['Ahuachapán','Atiquizaya','Turín','El Refugio','Guaymango','Jujutla','San Francisco Menéndez','San Lorenzo','San Pedro Puxtla','Tacuba'],
    'Cabañas':    ['Sensuntepeque','Ilobasco','Cinquera','Guacotecti','Jutiapa','San Isidro','Tejutepeque','Victoria'],
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

  // 2.1) Funcion: cargar la lista de departamentos en el <select id="modal-departamento">
  function cargarDepartamentos() {
    const $dep = $('#modal-departamento')
      .empty()
      .append('<option value="">Seleccione departamento</option>');
    $.each(departamentos, (dpto, lista) => {
      $dep.append(`<option value="${dpto}">${dpto}</option>`);
    });
  }

  // 2.2) Funcion: cargar municipios según el departamento seleccionado
  function cargarMunicipios() {
    const dpto = $('#modal-departamento').val();
    const $mun = $('#modal-municipio')
      .empty()
      .append('<option value="">Seleccione municipio</option>');
    (departamentos[dpto] || []).forEach(muni => {
      $mun.append(`<option value="${muni}">${muni}</option>`);
    });
  }

  // 2.3) Cuando se abra el modal “Editar Ubicación”, inicializamos selects
  $('#modalEditAddress').on('show.bs.modal', function() {
    // Cargar todos los departamentos
    cargarDepartamentos();

    // Preseleccionar los valores existentes en la vista de solo lectura
    const alias         = $('#display-alias').text().trim();
    const direccion     = $('#display-direccion').text().trim();
    const ciudad        = $('#display-ciudad').text().trim();
    const departamento  = $('#display-departamento').text().trim();
    const codigo_postal = $('#display-codigo_postal').text().trim();
    const pais          = $('#display-pais').text().trim();

    // 2.3.1) Prellenar alias y dirección completa
    $('#modal-alias').val(alias === '-' ? '' : alias);
    $('#modal-direccion').val(direccion === '-' ? '' : direccion);
    $('#modal-codigo_postal').val(codigo_postal === '-' ? '' : codigo_postal);
    $('#modal-pais').val(pais === '-' ? 'El Salvador' : pais);

    // 2.3.2) Preseleccionar departamento y municipio (si existen)
    if (departamento && departamentos[departamento]) {
      $('#modal-departamento').val(departamento);
      // Cargar municipios para ese departamento
      cargarMunicipios();
      if (departamentos[departamento].includes(ciudad)) {
        $('#modal-municipio').val(ciudad);
      }
    } else {
      $('#modal-departamento').val('');
      $('#modal-municipio')
        .empty()
        .append('<option value="">Seleccione municipio</option>');
    }
  });

  // 2.4) Vincular el evento change del <select> departamento → cargar municipios
  $('#modal-departamento').on('change', cargarMunicipios);

  // ======================================================================
  // 3) Cargar y mostrar datos en la vista “solo lectura”
  // ======================================================================
  function loadProfile() {
    $.ajax({
      url: 'app/models/usuarios/get_perfil.php',
      method: 'GET',
      dataType: 'json',
      success: function(res) {
        if (!res.ok) {
          showAlert('danger', res.message || 'No se pudo cargar el perfil.');
          return;
        }

        // Poner valores en la sección de solo lectura
        $('#display-nombre'       ).text(res.usuario.nombre);
        $('#display-apellido'     ).text(res.usuario.apellido);
        $('#display-email'        ).text(res.usuario.email);
        $('#display-telefono'     ).text(res.usuario.telefono || '-');
        $('#display-rol'          ).text(res.usuario.nombre_rol || '-');
        $('#display-ultimo-login' ).text(res.usuario.ultimo_login || '-');

        // Dirección
        if (res.direccion) {
          $('#display-alias'        ).text(res.direccion.alias);
          $('#display-direccion'    ).text(res.direccion.direccion);
          $('#display-ciudad'       ).text(res.direccion.ciudad);
          $('#display-departamento' ).text(res.direccion.departamento);
          $('#display-codigo_postal').text(res.direccion.codigo_postal || '-');
          $('#display-pais'         ).text(res.direccion.pais || '-');
        } else {
          $('#display-alias'        ).text('-');
          $('#display-direccion'    ).text('-');
          $('#display-ciudad'       ).text('-');
          $('#display-departamento' ).text('-');
          $('#display-codigo_postal').text('-');
          $('#display-pais'         ).text('-');
        }

        // 3.1) Personal info para rellenar el modal de editar info
        $('#modal-nombre'  ).val(res.usuario.nombre);
        $('#modal-apellido').val(res.usuario.apellido);
        $('#modal-email'   ).val(res.usuario.email);
        $('#modal-telefono').val(res.usuario.telefono);

        // 3.2) Dirección info para rellenar el modal de editar ubicación
        if (res.direccion) {
          $('#modal-alias'        ).val(res.direccion.alias);
          $('#modal-direccion'    ).val(res.direccion.direccion);
          $('#modal-codigo_postal').val(res.direccion.codigo_postal);
          $('#modal-pais'         ).val(res.direccion.pais);
          // El departamento/municipio se cargará al abrir el modal (show.bs.modal)
        } else {
          $('#modal-alias'        ).val('');
          $('#modal-direccion'    ).val('');
          $('#modal-codigo_postal').val('');
          $('#modal-pais'         ).val('El Salvador');
          $('#modal-departamento').val('');
          $('#modal-municipio'   ).empty().append('<option value="">Seleccione municipio</option>');
        }
      },
      error: function() {
        showAlert('danger', 'Error al conectar al servidor.');
      }
    });
  }

  // ======================================================================
  // 4) Enviar formulario “Editar Info Personal”
  // ======================================================================
  $('#form-perfil-modal').on('submit', function(e) {
    e.preventDefault();

    const payload = {
      nombre:   $('#modal-nombre').val().trim(),
      apellido: $('#modal-apellido').val().trim(),
      email:    $('#modal-email').val().trim(),
      telefono: $('#modal-telefono').val().trim()
    };

    if (!payload.nombre || !payload.email) {
      showAlert('warning', 'Nombre y correo son obligatorios.');
      return;
    }

    $.ajax({
      url: 'app/models/usuarios/update_perfil.php',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(payload),
      dataType: 'json',
      success: function(res) {
        if (res.ok) {
          // 1) Cerrar el modal
          $('#modalEditPersonal').modal('hide');
          // 2) Quitar aria-hidden y style residual
          $('#modalEditPersonal').removeAttr('aria-hidden').removeAttr('style');
          // 3) Eliminar backdrop y desbloquear body
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open');
          $('body').css('padding-right', '');
          // 4) Mover foco al botón “Editar Info”
          $('#btnEditarInfo').focus();
          // 5) Mostrar alerta y recargar perfil
          showAlert('success', res.message || 'Información actualizada');
          loadProfile();
        } else {
          showAlert('danger', res.message || 'Error al actualizar');
        }
      },
      error: function() {
        showAlert('danger', 'Error en la conexión al servidor.');
      }
    });
  });

  // ======================================================================
  // 5) Enviar formulario “Cambiar Contraseña”
  // ======================================================================
  $('#form-password-modal').on('submit', function(e) {
    e.preventDefault();

    const payload = {
      old_password:     $('#modal-old-password').val(),
      new_password:     $('#modal-new-password').val(),
      confirm_password: $('#modal-confirm-password').val()
    };

    if (!payload.old_password || !payload.new_password || !payload.confirm_password) {
      showAlert('warning', 'Complete todos los campos.');
      return;
    }

    $.ajax({
      url: 'app/models/usuarios/update_password.php',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(payload),
      dataType: 'json',
      success: function(res) {
        if (res.ok) {
          // 1) Cerrar modal
          $('#modalChangePassword').modal('hide');
          // 2) Quitar aria-hidden y style residual
          $('#modalChangePassword').removeAttr('aria-hidden').removeAttr('style');
          // 3) Eliminar backdrop y desbloquear body
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open');
          $('body').css('padding-right', '');
          // 4) Mover foco al botón “Cambiar Contraseña”
          $('#btnCambiarPassword').focus();
          // 5) Mostrar alerta y limpiar campos
          showAlert('success', res.message || 'Contraseña actualizada');
          $('#modal-old-password').val('');
          $('#modal-new-password').val('');
          $('#modal-confirm-password').val('');
        } else {
          showAlert('danger', res.message || 'Error al cambiar contraseña');
        }
      },
      error: function() {
        showAlert('danger', 'Error en la conexión al servidor.');
      }
    });
  });

  // ======================================================================
  // 6) Enviar formulario “Editar Ubicación”
  // ======================================================================
  $('#form-address-modal').on('submit', function(e) {
    e.preventDefault();

    const payload = {
      alias:         $('#modal-alias').val().trim(),
      direccion:     $('#modal-direccion').val().trim(),
      ciudad:        $('#modal-municipio').val().trim(),
      departamento:  $('#modal-departamento').val().trim(),
      codigo_postal: $('#modal-codigo_postal').val().trim(),
      pais:          $('#modal-pais').val().trim()
    };

    if (!payload.alias || !payload.direccion || !payload.ciudad || !payload.departamento) {
      showAlert('warning', 'Complete todos los campos obligatorios de dirección.');
      return;
    }

    $.ajax({
      url: 'app/models/usuarios/update_direccion.php',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(payload),
      dataType: 'json',
      success: function(res) {
        if (res.ok) {
          // 1) Mover primero el foco al botón “Editar Ubicación”
          $('#btnEditarUbicacion').focus();
          // 2) Cerrar el modal
          $('#modalEditAddress').modal('hide');
          // 3) Quitar aria-hidden y style residual
          $('#modalEditAddress').removeAttr('aria-hidden').removeAttr('style');
          // 4) Eliminar backdrop y desbloquear body
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open');
          $('body').css('padding-right', '');
          // 5) Mostrar alerta y recargar perfil
          showAlert('success', res.message || 'Ubicación actualizada');
          loadProfile();
        } else {
          showAlert('danger', res.message || 'Error al actualizar ubicación');
        }
      },
      error: function() {
        showAlert('danger', 'Error en la conexión al servidor.');
      }
    });
  });

  // ======================================================================
  // 7) Inicializar al cargar la página
  // ======================================================================
  loadProfile();
});
