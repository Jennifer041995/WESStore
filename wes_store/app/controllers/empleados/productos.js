$(function() {
  const $tablaBody = $('#tbl-productos-empleado tbody');
  const $filtro      = $('#filtro-productos');

  // Función para cargar todos los productos
  function cargarProductos(nombre = '') {
    $.ajax({
      url: 'app/models/empleados/productos_modelo.php',
      method: 'GET',
      data: { buscar: nombre },
      dataType: 'json',
      success: function(data) {
        $tablaBody.empty();
        if (!Array.isArray(data) || data.length === 0) {
          $tablaBody.append('<tr><td colspan="6" class="text-center">No se encontraron productos.</td></tr>');
          return;
        }
        data.forEach(p => {
          $tablaBody.append(`
            <tr>
              <td>${p.id_producto}</td>
              <td>${p.sku}</td>
              <td>${p.nombre}</td>
              <td>${p.categoria}</td>
              <td>$${parseFloat(p.precio).toFixed(2)}</td>
              <td>${p.stock}</td>
            </tr>
          `);
        });
      },
      error: function() {
        $tablaBody.empty();
        $tablaBody.append('<tr><td colspan="6" class="text-center text-danger">Error al cargar productos.</td></tr>');
      }
    });
  }

  // Evento “Buscar”
  $('#btn-buscar-productos').on('click', function() {
    cargarProductos($filtro.val().trim());
  });

  // Evento “Limpiar”
  $('#btn-reset-productos').on('click', function() {
    $filtro.val('');
    cargarProductos('');
  });

  // Carga inicial sin filtro
  cargarProductos();
});
