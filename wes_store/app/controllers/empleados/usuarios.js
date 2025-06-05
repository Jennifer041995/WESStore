$(function() {
  const $tablaBody = $('#tbl-usuarios-empleado tbody');

  function showAlert(type, message) {
    Swal.fire({ icon: type, text: message });
  }

  // Carga la lista de usuarios (solo lectura)
  function cargarUsuariosEmpleado() {
    $.ajax({
      url: 'app/models/empleados/get_usuarios.php',
      method: 'GET',
      data: { rol_id: 2 }, // Aquí se envía el filtro por rol_id
      dataType: 'json'
    })
    .done(function(res) {
      // Si el resultado es un arreglo, mostramos todos
      if (Array.isArray(res)) {
        $tablaBody.empty();
        res.forEach(u => {
          const fila = `
            <tr>
              <td>${u.id_usuario}</td>
              <td>${u.nombre}</td>
              <td>${u.apellido || ''}</td>
              <td>${u.email}</td>
              <td>${u.telefono || ''}</td>
              <td>${u.nombre_rol || ''}</td>
              <td>${u.estado}</td>
            </tr>`;
          $tablaBody.append(fila);
        });
      } else if (res.ok === false) {
        // Si vino { ok: false, message: ... }
        showAlert('error', res.message || 'No se pudo cargar la lista de usuarios.');
      } else {
        // Cualquier otro formato inesperado
        showAlert('warning', 'Respuesta inesperada del servidor.');
      }
    })
    .fail(function() {
      showAlert('error', 'Error de conexión al servidor al cargar la lista de usuarios.');
    });
  }

  // Inicial
  cargarUsuariosEmpleado();
});
