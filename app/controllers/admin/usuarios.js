// app/controllers/admin/usuarios.js
$(function() {
  const $tablaUsuarios = $('#tbl-usuarios tbody');
  const $modal       = $('#modalUsuario');
  const $formModal   = $('#form-usuario-modal');
  const $inputId     = $('#usuario-id');
  const $inputNombre = $('#usuario-nombre');
  const $inputApe    = $('#usuario-apellido');
  const $inputEmail  = $('#usuario-email');
  const $inputTel    = $('#usuario-telefono');
  const $selRol      = $('#usuario-rol');
  const $selEstado   = $('#usuario-estado');
  const $divPwd      = $('#div-password-fields');
  const $inputPwd    = $('#usuario-password');
  const $inputPwdConf= $('#usuario-password-confirm');
  const $btnGuardar  = $('#btnGuardarUsuario');

  // 1) Mostrar alerta simple
  function showAlert(type, message) {
    Swal.fire({ icon: type, text: message });
  }

  // 2) Cargar lista de roles
  function cargarRoles(selectedRolId = '') {
    $.getJSON('app/models/admin/get_roles.php')
      .done(function(lista) {
        $selRol.empty().append('<option value="">Selecciona rol</option>');
        lista.forEach(r => {
          const sel = (r.id == selectedRolId) ? 'selected' : '';
          $selRol.append(`<option value="${r.id}" ${sel}>${r.nombre_rol}</option>`);
        });
      })
      .fail(() => {
        showAlert('error', 'No se pudieron cargar los roles.');
      });
  }

  // 3) Cargar todos los usuarios
  function cargarUsuarios() {
    $.getJSON('app/models/admin/get_usuarios.php')
      .done(function(lista) {
        $tablaUsuarios.empty();
        lista.forEach(u => {
          const fila = `
            <tr>
              <td>${u.id_usuario}</td>
              <td>${u.nombre}</td>
              <td>${u.apellido || ''}</td>
              <td>${u.email}</td>
              <td>${u.telefono || ''}</td>
              <td>${u.nombre_rol || ''}</td>
              <td>${u.estado}</td>
              <td>
                <button class="btn btn-sm btn-info btn-edit" data-id="${u.id_usuario}">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger btn-delete" data-id="${u.id_usuario}">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </td>
            </tr>`;
          $tablaUsuarios.append(fila);
        });
      })
      .fail(() => {
        showAlert('error', 'No se pudieron cargar los usuarios.');
      });
  }

  // 4) “Añadir Usuario”
  $('#btnAddUsuario').on('click', function() {
    $inputId.val('0');
    $formModal[0].reset();
    $divPwd.removeClass('d-none'); // muestro contraseña
    $('#modalUsuarioLabel').text('Añadir Usuario');
    cargarRoles();
    $modal.modal('show');
  });

  // 5) “Editar Usuario”
  $tablaUsuarios.on('click', '.btn-edit', function() {
    const id = $(this).data('id');
    $.getJSON('app/models/admin/get_usuarios.php', { id: id })
      .done(function(res) {
        if (!res.ok) {
          showAlert('error', res.message || 'No se pudo obtener el usuario.');
          return;
        }
        const u = res.usuario;
        $inputId.val(u.id_usuario);
        $inputNombre.val(u.nombre);
        $inputApe.val(u.apellido);
        $inputEmail.val(u.email);
        $inputTel.val(u.telefono);
        $selEstado.val(u.estado);
        $divPwd.addClass('d-none'); // oculto contraseña en edición
        cargarRoles(u.rol_id);
        $('#modalUsuarioLabel').text('Editar Usuario');
        $modal.modal('show');
      })
      .fail(() => {
        showAlert('error', 'Error al conectar para editar usuario.');
      });
  });

  // 6) “Eliminar Usuario”
  $tablaUsuarios.on('click', '.btn-delete', function() {
    const id = $(this).data('id');
    Swal.fire({
      title: '¿Eliminar usuario?',
      text: 'Esta acción no se puede deshacer.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: 'app/models/admin/delete_usuario.php',
          method: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({ id: id }),
          dataType: 'json'
        })
        .done(function(res) {
          if (res.ok) {
            showAlert('success', res.message);
            cargarUsuarios();
          } else {
            showAlert('error', res.message || 'No se pudo eliminar el usuario.');
          }
        })
        .fail(() => {
          showAlert('error', 'Error en la conexión al servidor.');
        });
      }
    });
  });

  // 7) Guardar (Añadir/Editar)
  $formModal.on('submit', function(e) {
    e.preventDefault();

    const id       = parseInt($inputId.val(), 10);
    const nombre   = $inputNombre.val().trim();
    const apellido = $inputApe.val().trim();
    const email    = $inputEmail.val().trim();
    const telefono = $inputTel.val().trim();
    const rol_id   = parseInt($selRol.val(), 10);
    const estado   = $selEstado.val();
    const pwd      = $inputPwd.val().trim();
    const pwdConf  = $inputPwdConf.val().trim();

    if (!nombre || !email || !rol_id || !estado) {
      showAlert('warning', 'Los campos con * son obligatorios.');
      return;
    }

    // Si es “Añadir” (id=0), se exige contraseña
    if (id === 0) {
      if (!pwd || !pwdConf) {
        showAlert('warning', 'Debes establecer una contraseña.');
        return;
      }
      if (pwd !== pwdConf) {
        showAlert('warning', 'Las contraseñas no coinciden.');
        return;
      }
    }

    // Preparo payload
    const payload = {
      id: id,
      nombre: nombre,
      apellido: apellido,
      email: email,
      telefono: telefono,
      rol_id: rol_id,
      estado: estado
    };
    if (id === 0) {
      payload.password = pwd;
    }

    const url = (id === 0)
      ? 'app/models/admin/add_usuario.php'
      : 'app/models/admin/update_usuario.php';

    $.ajax({
      url: url,
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(payload),
      dataType: 'json'
    })
    .done(function(res) {
      if (res.ok) {
        $modal.modal('hide');
        showAlert('success', res.message);
        cargarUsuarios();
      } else {
        showAlert('error', res.message || 'Error al guardar el usuario.');
      }
    })
    .fail(() => {
      showAlert('error', 'Error en la conexión al servidor.');
    });
  });

  // 8) Inicializar
  cargarUsuarios();
});