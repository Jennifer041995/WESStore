$(function() {
  const $tbody = $('#tbl-ordenes-empleado tbody');

  // Cargar órdenes (filtradas)
  function cargarOrdenes(fecha_ini = '', fecha_fin = '') {
    $.ajax({
      url: 'app/models/empleados/ordenes_modelo.php',
      method: 'POST',
      data: { fecha_ini: fecha_ini, fecha_fin: fecha_fin },
      dataType: 'json',
      success: function(data) {
        $tbody.empty();
        if (!Array.isArray(data) || data.length === 0) {
          $tbody.append('<tr><td colspan="5" class="text-center">No se encontraron órdenes.</td></tr>');
          return;
        }
        data.forEach(o => {
          $tbody.append(`
            <tr>
              <td>${o.id_pedido}</td>
              <td>${o.nombre_cliente}</td>
              <td>${o.fecha_pedido}</td>
              <td>${o.estado}</td>
              <td>$${parseFloat(o.total).toFixed(2)}</td>
            </tr>
          `);
        });
      },
      error: function() {
        $tbody.empty();
        $tbody.append('<tr><td colspan="5" class="text-center text-danger">Error al cargar órdenes.</td></tr>');
      }
    });
  }

  // Filtro “Filtrar”
  $('#btn-filtar-ordenes-emp').on('click', function() {
    const fi = $('#ord_fecha_ini').val();
    const ff = $('#ord_fecha_fin').val();
    if (!fi || !ff) {
      Swal.fire('Aviso', 'Seleccione rango de fechas.', 'warning');
      return;
    }
    cargarOrdenes(fi, ff);
  });

  // Generar informe (JSON+PDF) de órdenes abiertas (mismo rango)
  $('#btn-generar-ordenes-emp').on('click', function() {
    const fi = $('#ord_fecha_ini').val();
    const ff = $('#ord_fecha_fin').val();
    if (!fi || !ff) {
      Swal.fire('Aviso', 'Seleccione rango de fechas.', 'warning');
      return;
    }
    Swal.fire({
      title: 'Generando Informe...',
      allowOutsideClick: false,
      didOpen: () => { Swal.showLoading(); }
    });
    $.ajax({
      url: 'app/models/empleados/ordenes_export_emp.php',
      method: 'POST',
      data: { tipo: 'ordenes_abiertas', fecha_ini: fi, fecha_fin: ff },
      dataType: 'json',
      success: function(res) {
        Swal.close();
        if (res.ok) {
          window.open(res.pdf_url, '_blank');
          const linkJson = document.createElement('a');
          linkJson.href = res.json_url;
          linkJson.download = '';
          document.body.appendChild(linkJson);
          linkJson.click();
          document.body.removeChild(linkJson);
        } else {
          Swal.fire('Error', res.message, 'error');
        }
      },
      error: function() {
        Swal.close();
        Swal.fire('Error', 'No se pudo generar informe.', 'error');
      }
    });
  });

  // Carga inicial (sin filtro): últimas órdenes del mes actual
  const hoy = new Date().toISOString().slice(0,10);
  const primerDia = hoy.slice(0,8) + '01';
  $('#ord_fecha_ini').val(primerDia);
  $('#ord_fecha_fin').val(hoy);
  cargarOrdenes(primerDia, hoy);
});
