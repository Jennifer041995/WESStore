$(document).ready(function() {
  // Referencias
  const $tblBody = $('#tbl-productos tbody');

  // 1. Cargar datos de select: Categorías, Subcategorías, Marcas
  function cargarCategorias() {
    return $.getJSON('app/models/productos/get_categorias.php')
      .done(function(data) {
        const $sel = $('#categoria').empty().append('<option value="">Seleccione...</option>');
        data.forEach(c => $sel.append(`<option value="${c.id}">${c.nombre}</option>`));
      });
  }
  function cargarSubcategorias() {
    return $.getJSON('app/models/productos/get_subcategorias.php')
      .done(function(data) {
        const $sel = $('#subcategoria').empty().append('<option value="">Seleccione...</option>');
        data.forEach(s => $sel.append(`<option value="${s.id}">${s.nombre}</option>`));
      });
  }
  function cargarMarcas() {
    return $.getJSON('app/models/productos/get_marcas.php')
      .done(function(data) {
        const $sel = $('#marca').empty().append('<option value="">Seleccione...</option>');
        data.forEach(m => $sel.append(`<option value="${m.id}">${m.nombre}</option>`));
      });
  }

  // 2. Cargar lista de productos
  function cargarProductos() {
    $.getJSON('app/models/admin/productos_modelo.php?action=listar')
      .done(function(data) {
        $tblBody.empty();
        data.forEach(p => {
          $tblBody.append(`
            <tr>
              <td>${p.id_producto}</td>
              <td>${p.sku}</td>
              <td>${p.nombre}</td>
              <td>${p.nombre_categoria || ''}</td>
              <td>${p.nombre_marca || ''}</td>
              <td>$${parseFloat(p.precio).toFixed(2)}</td>
              <td>$${parseFloat(p.costo).toFixed(2)}</td>
              <td>${p.stock}</td>
              <td>${p.estado}</td>
              <td>
                <button class="btn btn-sm btn-info btn-editar" data-id="${p.id_producto}">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger btn-eliminar" data-id="${p.id_producto}">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          `);
        });
      })
      .fail(function() {
        Swal.fire('Error', 'No se pudieron cargar los productos.', 'error');
      });
  }

  // 3. Abrir modal para “Nuevo Producto”
  $('#btn-nuevo-producto').on('click', function() {
    $('#productoModalLabel').text('Nuevo Producto');
    $('#form-producto')[0].reset();
    $('#id_producto').val('');
    $.when(cargarCategorias(), cargarSubcategorias(), cargarMarcas()).done(function() {
      $('#productoModal').modal('show');
    });
  });

  // 4. Guardar / Editar producto
  $('#form-producto').on('submit', function(e) {
    e.preventDefault();
    const id = $('#id_producto').val().trim();
    const data = {
      sku: $('#sku').val().trim(),
      nombre: $('#nombre').val().trim(),
      categoria_id: $('#categoria').val(),
      subcategoria_id: $('#subcategoria').val(),
      marca_id: $('#marca').val(),
      precio: $('#precio').val(),
      costo: $('#costo').val(),
      stock: $('#stock').val(),
      descripcion_corta: $('#descripcion_corta').val().trim(),
      descripcion_larga: $('#descripcion_larga').val().trim(),
      estado: $('#estado').val(),
      destacado: $('#destacado').val()
    };
    let action = 'agregar';
    if (id) {
      data.id_producto = id;
      action = 'editar';
    }

    $.ajax({
      url: 'app/models/admin/productos_modelo.php?action=' + action,
      method: 'POST',
      data: data,
      dataType: 'json'
    }).done(function(resp) {
      if (resp.status === 'success') {
        $('#productoModal').modal('hide');
        Swal.fire('Éxito', resp.message, 'success');
        cargarProductos();
      } else {
        Swal.fire('Error', resp.message, 'error');
      }
    }).fail(function() {
      Swal.fire('Error', 'Error de conexión.', 'error');
    });
  });

  // 5. Click “Editar”
  $tblBody.on('click', '.btn-editar', function() {
    const id = $(this).data('id');
    $.getJSON('app/models/admin/productos_modelo.php?action=obtener&id=' + id)
      .done(function(p) {
        $('#productoModalLabel').text('Editar Producto');
        $('#id_producto').val(p.id_producto);
        $('#sku').val(p.sku);
        $('#nombre').val(p.nombre);

        // Cargar selects y luego asignar valores
        $.when(cargarCategorias(), cargarSubcategorias(), cargarMarcas()).done(function() {
          $('#categoria').val(p.categoria_id);
          $('#subcategoria').val(p.subcategoria_id);
          $('#marca').val(p.marca_id);
        });

        $('#precio').val(p.precio);
        $('#costo').val(p.costo);
        $('#stock').val(p.stock);
        $('#descripcion_corta').val(p.descripcion_corta);
        $('#descripcion_larga').val(p.descripcion_larga);
        $('#estado').val(p.estado);
        $('#destacado').val(p.destacado);
        $('#productoModal').modal('show');
      })
      .fail(function() {
        Swal.fire('Error', 'No se pudo obtener el producto.', 'error');
      });
  });

  // 6. Click “Eliminar”
  $tblBody.on('click', '.btn-eliminar', function() {
    const id = $(this).data('id');
    Swal.fire({
      title: '¿Eliminar producto?',
      text: 'Esta acción no se puede revertir.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then(function(res) {
      if (!res.isConfirmed) return;
      $.ajax({
        url: 'app/models/admin/productos_modelo.php?action=eliminar',
        method: 'POST',
        data: { id_producto: id },
        dataType: 'json'
      }).done(function(resp) {
        if (resp.status === 'success') {
          Swal.fire('Eliminado', resp.message, 'success');
          cargarProductos();
        } else {
          Swal.fire('Error', resp.message, 'error');
        }
      }).fail(function() {
        Swal.fire('Error', 'No se pudo eliminar.', 'error');
      });
    });
  });

  // Inicializar tabla
  cargarProductos();
});
