$(function() {
  const $tbody = $('#tbl-inventario-empleado tbody');
  const $sku   = $('#inv_sku');

  // Función para cargar inventario
  function cargarInventario(filtroSKU = '') {
    $.ajax({
      url: 'app/models/empleados/inventario_modelo.php',
      method: 'GET',
      data: { sku: filtroSKU },
      dataType: 'json',
      success: function(data) {
        $tbody.empty();
        if (!Array.isArray(data) || data.length === 0) {
          $tbody.append('<tr><td colspan="5" class="text-center">No se encontraron registros.</td></tr>');
          return;
        }
        data.forEach(r => {
          $tbody.append(`
            <tr>
              <td>${r.id_inventario}</td>
              <td>${r.nombre_producto}</td>
              <td>${r.sku}</td>
              <td>${r.stock}</td>
              <td>${r.stock_minimo}</td>
            </tr>
          `);
        });
      },
      error: function() {
        $tbody.empty();
        $tbody.append('<tr><td colspan="5" class="text-center text-danger">Error al cargar inventario.</td></tr>');
      }
    });
  }

  $('#btn-buscar-inv').on('click', function() {
    cargarInventario($sku.val().trim());
  });
  $('#btn-reset-inv').on('click', function() {
    $sku.val('');
    cargarInventario('');
  });

  // Carga inicial
  cargarInventario();
});
