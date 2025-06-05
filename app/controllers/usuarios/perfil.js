$(function() {
  const $alertBox = $('#alert-perfil');

  function showAlert(type, message) {
    $alertBox
      .removeClass('alert-success alert-danger alert-warning')
      .addClass(`alert alert-${type}`)
      .text(message)
      .fadeIn(200)
      .delay(3000)
      .fadeOut(200);
  }

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

  function cargarDepartamentos() {
    const $dep = $('#modal-departamento')
      .empty()
      .append('<option value="">Seleccione departamento</option>');
    $.each(departamentos, (dpto, lista) => {
      $dep.append(`<option value="${dpto}">${dpto}</option>`);
    });
  }

  function cargarMunicipios() {
    const dpto = $('#modal-departamento').val();
    const $mun = $('#modal-municipio')
      .empty()
      .append('<option value="">Seleccione municipio</option>');
    (departamentos[dpto] || []).forEach(muni => {
      $mun.append(`<option value="${muni}">${muni}</option>`);
    });
  }

  // Cuando se abra el modal “Editar Ubicación”
  $('#modalEditAddress').on('show.bs.modal', function() {
    cargarDepartamentos();

    const alias         = $('#display-alias').text().trim();
    const direccion     = $('#display-direccion').text().trim();
    const ciudad        = $('#display-ciudad').text().trim();
    const departamento  = $('#display-departamento').text().trim();
    const codigo_postal = $('#display-codigo_postal').text().trim();
    const pais          = $('#display-pais').text().trim();

    $('#modal-alias').val(alias === '-' ? '' : alias);
    $('#modal-direccion').val(direccion === '-' ? '' : direccion);
    $('#modal-codigo_postal').val(codigo_postal === '-' ? '' : codigo_postal);
    $('#modal-pais').val(pais === '-' ? 'El Salvador' : pais);

    if (departamento && departamentos[departamento]) {
      $('#modal-departamento').val(departamento);
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

  $('#modal-departamento').on('change', cargarMunicipios);

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

        // Datos de lectura
        $('#display-nombre'       ).text(res.usuario.nombre);
        $('#display-apellido'     ).text(res.usuario.apellido);
        $('#display-email'        ).text(res.usuario.email);
        $('#display-telefono'     ).text(res.usuario.telefono || '-');

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

        // Rellenar modal de editar info personal
        $('#modal-nombre'  ).val(res.usuario.nombre);
        $('#modal-apellido').val(res.usuario.apellido);
        $('#modal-email'   ).val(res.usuario.email);
        $('#modal-telefono').val(res.usuario.telefono || '');

        if (res.direccion) {
          $('#modal-alias'        ).val(res.direccion.alias);
          $('#modal-direccion'    ).val(res.direccion.direccion);
          $('#modal-codigo_postal').val(res.direccion.codigo_postal);
          $('#modal-pais'         ).val(res.direccion.pais);
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

  // ————————————
  // Actualizar INFO PERSONAL
  // ————————————
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

          // 2) Eliminar cualquier aria-hidden que Bootstrap haya dejado
          $('[aria-hidden="true"]').removeAttr('aria-hidden');

          // 3) Eliminar backdrop y clases de Bootstrap del <body>
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open');
          $('body').css('padding-right', '');

          // 4) Devolver el foco al botón “Editar Info”
          $('#btnEditarInfo').focus();

          // 5) Mostrar SweetAlert de éxito
          Swal.fire({
            icon: 'success',
            title: '¡Listo!',
            text: res.message || 'Información personal actualizada con éxito.',
            confirmButtonText: 'Aceptar'
          });

          loadProfile();
        } else {
          Swal.fire({
            icon: 'error',
            title: '¡Ups!',
            text: res.message || 'Error al actualizar la información.',
            confirmButtonText: 'Cerrar'
          });
        }
      },
      error: function() {
        Swal.fire({
          icon: 'error',
          title: '¡Error!',
          text: 'Error en la conexión al servidor.',
          confirmButtonText: 'Cerrar'
        });
      }
    });
  });

  // ————————————
  // Cambiar CONTRASEÑA (pedir confirmación antes)
  // ————————————
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

    if (payload.new_password !== payload.confirm_password) {
      showAlert('warning', 'La nueva contraseña y su confirmación no coinciden.');
      return;
    }

    // Antes de ejecutar, pedimos confirmación con SweetAlert
    Swal.fire({
      icon: 'warning',
      title: '¿Cambiar contraseña?',
      text: '¿Estás seguro de que deseas actualizar tu contraseña?',
      showCancelButton: true,
      confirmButtonText: 'Sí, cambiar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        // Si el usuario confirma, enviamos la petición AJAX
        $.ajax({
          url: 'app/models/usuarios/update_password.php',
          method: 'POST',
          contentType: 'application/json',
          data: JSON.stringify(payload),
          dataType: 'json',
          success: function(res) {
            if (res.ok) {
              // 1) Cerrar el modal
              $('#modalChangePassword').modal('hide');

              // 2) Eliminar cualquier aria-hidden que haya quedado
              $('[aria-hidden="true"]').removeAttr('aria-hidden');

              // 3) Eliminar backdrop y clases de Bootstrap
              $('.modal-backdrop').remove();
              $('body').removeClass('modal-open');
              $('body').css('padding-right', '');

              // 4) Devolver el foco al botón “Cambiar Contraseña”
              $('#btnCambiarPassword').focus();

              // 5) SweetAlert de éxito
              Swal.fire({
                icon: 'success',
                title: '¡Contraseña cambiada!',
                text: res.message || 'Tu contraseña se ha actualizado con éxito.',
                confirmButtonText: 'Aceptar'
              });

              // Limpiar campos
              $('#modal-old-password').val('');
              $('#modal-new-password').val('');
              $('#modal-confirm-password').val('');
            } else {
              Swal.fire({
                icon: 'error',
                title: '¡Error!',
                text: res.message || 'No se pudo cambiar la contraseña.',
                confirmButtonText: 'Cerrar'
              });
            }
          },
          error: function() {
            Swal.fire({
              icon: 'error',
              title: '¡Error!',
              text: 'Error en la conexión al servidor.',
              confirmButtonText: 'Cerrar'
            });
          }
        });
      }
      // Si el usuario canceló, no hacemos nada
    });
  });

  // ————————————
  // Actualizar DIRECCIÓN
  // ————————————
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
          // 1) Cerrar el modal
          $('#modalEditAddress').modal('hide');

          // 2) Eliminar cualquier aria-hidden que haya quedado
          $('[aria-hidden="true"]').removeAttr('aria-hidden');

          // 3) Eliminar backdrop y clases de Bootstrap
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open');
          $('body').css('padding-right', '');

          // 4) Devolver el foco al botón “Editar Ubicación”
          $('#btnEditarUbicacion').focus();

          // 5) SweetAlert de éxito
          Swal.fire({
            icon: 'success',
            title: '¡Ubicación actualizada!',
            text: res.message || 'Tu dirección se ha actualizado con éxito.',
            confirmButtonText: 'Aceptar'
          });

          loadProfile();
        } else {
          Swal.fire({
            icon: 'error',
            title: '¡Error!',
            text: res.message || 'No se pudo actualizar la ubicación.',
            confirmButtonText: 'Cerrar'
          });
        }
      },
      error: function() {
        Swal.fire({
          icon: 'error',
          title: '¡Error!',
          text: 'Error en la conexión al servidor.',
          confirmButtonText: 'Cerrar'
        });
      }
    });
  });

  loadProfile();
});
