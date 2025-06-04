$(document).ready(function() {
  const $tblBody = $('#tbl-proveedores tbody');

  // 1. Cargar lista de proveedores
  function cargarProveedores() {
    $.getJSON('app/models/admin/proveedores_modelo.php?action=listar')
      .done(function(data) {
        $tblBody.empty();
        data.forEach(p => {
          $tblBody.append(`
            <tr>
              <td>${p.id_proveedor}</td>
              <td>${p.nombre_proveedor}</td>
              <td>${p.contacto || ''}</td>
              <td>${p.email || ''}</td>
              <td>${p.telefono || ''}</td>
              <td>${p.ciudad || ''}</td>
              <td>${p.pais || ''}</td>
              <td>${p.estado}</td>
              <td>
                <button class="btn btn-sm btn-info btn-editar" data-id="${p.id_proveedor}">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger btn-eliminar" data-id="${p.id_proveedor}">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          `);
        });
      })
      .fail(function() {
        Swal.fire('Error', 'No se pudieron cargar los proveedores.', 'error');
      });
  }

  // 2. Abrir modal para “Nuevo Proveedor”
  $('#btn-nuevo-proveedor').on('click', function() {
    $('#proveedorModalLabel').text('Nuevo Proveedor');
    $('#form-proveedor')[0].reset();
    $('#id_proveedor').val('');
    $('#pais_proveedor').val('El Salvador');
    $('#proveedorModal').modal('show');
  });

  // 3. Guardar / Editar proveedor
  $('#form-proveedor').on('submit', function(e) {
    e.preventDefault();
    const id = $('#id_proveedor').val().trim();
    const data = {
      nombre_proveedor: $('#nombre_proveedor').val().trim(),
      contacto: $('#contacto').val().trim(),
      email: $('#email_proveedor').val().trim(),
      telefono: $('#telefono_proveedor').val().trim(),
      direccion: $('#direccion_proveedor').val().trim(),
      ciudad: $('#ciudad_proveedor').val().trim(),
      pais: $('#pais_proveedor').val().trim(),
      estado: $('#estado_proveedor').val()
    };
    let action = 'agregar';
    if (id) {
      data.id_proveedor = id;
      action = 'editar';
    }

    $.ajax({
      url: 'app/models/admin/proveedores_modelo.php?action=' + action,
      method: 'POST',
      data: data,
      dataType: 'json'
    }).done(function(resp) {
      if (resp.status === 'success') {
        $('#proveedorModal').modal('hide');
        Swal.fire('Éxito', resp.message, 'success');
        cargarProveedores();
      } else {
        Swal.fire('Error', resp.message, 'error');
      }
    }).fail(function() {
      Swal.fire('Error', 'Error de conexión.', 'error');
    });
  });

  // 4. Click “Editar”
  $tblBody.on('click', '.btn-editar', function() {
    const id = $(this).data('id');
    $.getJSON('app/models/admin/proveedores_modelo.php?action=obtener&id=' + id)
      .done(function(p) {
        $('#proveedorModalLabel').text('Editar Proveedor');
        $('#id_proveedor').val(p.id_proveedor);
        $('#nombre_proveedor').val(p.nombre_proveedor);
        $('#contacto').val(p.contacto);
        $('#email_proveedor').val(p.email);
        $('#telefono_proveedor').val(p.telefono);
        $('#direccion_proveedor').val(p.direccion);
        $('#ciudad_proveedor').val(p.ciudad);
        $('#pais_proveedor').val(p.pais);
        $('#estado_proveedor').val(p.estado);
        $('#proveedorModal').modal('show');
      })
      .fail(function() {
        Swal.fire('Error', 'No se pudo obtener los datos del proveedor.', 'error');
      });
  });

  // 5. Click “Eliminar”
  $tblBody.on('click', '.btn-eliminar', function() {
    const id = $(this).data('id');
    Swal.fire({
      title: '¿Eliminar proveedor?',
      text: 'Esta acción no se puede revertir.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then(function(res) {
      if (!res.isConfirmed) return;
      $.ajax({
        url: 'app/models/admin/proveedores_modelo.php?action=eliminar',
        method: 'POST',
        data: { id_proveedor: id },
        dataType: 'json'
      }).done(function(resp) {
        if (resp.status === 'success') {
          Swal.fire('Eliminado', resp.message, 'success');
          cargarProveedores();
        } else {
          Swal.fire('Error', resp.message, 'error');
        }
      }).fail(function() {
        Swal.fire('Error', 'No se pudo eliminar.', 'error');
      });
    });
  });

  // Inicializar tabla al cargar la página
  cargarProveedores();
});
