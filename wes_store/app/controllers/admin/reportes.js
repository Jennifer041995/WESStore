$(function() {
  // 1) Inicial: cargar Inventario
  $('#tabs-reportes .nav-link[data-reporte="inventario"]').addClass('active');
  cargarReporteInventario();

  // 2) Cambio de pestaña
  $('#tabs-reportes .nav-link').on('click', function(e) {
    e.preventDefault();
    $('#tabs-reportes .nav-link').removeClass('active');
    $(this).addClass('active');

    const reporte = $(this).data('reporte');
    // Ocultar todas las secciones
    $('#contenido-reportes > div').addClass('d-none');
    // Mostrar la sección correspondiente
    $('#reporte-' + reporte).removeClass('d-none');

    // Llamar a la función de carga correspondiente
    switch (reporte) {
      case 'inventario': cargarReporteInventario(); break;
      case 'ordenes': cargarReporteOrdenes(); break;
      case 'ventas_categoria': cargarReporteVentasCategoria(); break;
      case 'productos_menos_vendidos': cargarReporteProductosMenosVendidos(); break;
      case 'clientes_top': cargarReporteClientesTop(); break;
      case 'movimientos_inventario': cargarReporteMovimientosInventario(); break;
      case 'ordenes_pendientes': cargarReporteOrdenesPendientes(); break;
      case 'margen': cargarReporteMargen(); break;
    }
  });

  // === 3) FUNCIONES DE CARGA ===

  // a) Inventario
  function cargarReporteInventario() {
    $.ajax({
      url: 'app/models/admin/reportes_modelo.php',
      method: 'POST',
      data: { tipo: 'inventario' },
      dataType: 'json',
      success: function(data) {
        const $tbody = $('#tbl-reporte-inventario tbody').empty();
        data.forEach(p => {
          $tbody.append(`
            <tr>
              <td>${p.id_producto}</td>
              <td>${p.sku}</td>
              <td>${p.nombre}</td>
              <td>${p.stock}</td>
              <td>${p.stock_minimo}</td>
            </tr>
          `);
        });
      },
      error: function() {
        Swal.fire('Error', 'No se pudo cargar inventario.', 'error');
      }
    });
  }

  // b) Órdenes (muestra sin filtro al inicio)
  function cargarReporteOrdenes(fecha_ini = '', fecha_fin = '') {
    $.ajax({
      url: 'app/models/admin/reportes_modelo.php',
      method: 'POST',
      data: { tipo: 'ordenes', fecha_ini: fecha_ini, fecha_fin: fecha_fin },
      dataType: 'json',
      success: function(data) {
        const $tbody = $('#tbl-reporte-ordenes tbody').empty();
        data.forEach(o => {
          $tbody.append(`
            <tr>
              <td>${o.id_orden_compra}</td>
              <td>${o.numero_orden}</td>
              <td>${o.nombre_proveedor}</td>
              <td>${o.fecha_orden}</td>
              <td>${o.estado}</td>
              <td>$${parseFloat(o.total).toFixed(2)}</td>
            </tr>
          `);
        });
      },
      error: function() {
        Swal.fire('Error', 'No se pudo cargar órdenes.', 'error');
      }
    });
  }

  // c) Ventas por Categoría (necesita gráfico)
  function cargarReporteVentasCategoria(fecha_ini = '', fecha_fin = '') {
    $.ajax({
      url: 'app/models/admin/reportes_modelo.php',
      method: 'POST',
      data: { tipo: 'ventas_categoria', fecha_ini: fecha_ini, fecha_fin: fecha_fin },
      dataType: 'json',
      success: function(data) {
        const $tbody = $('#tbl-reporte-ventas_categoria tbody').empty();
        const categorias = [], totales = [];
        data.forEach(c => {
          $tbody.append(`
            <tr>
              <td>${c.nombre_categoria}</td>
              <td>$${parseFloat(c.total_vendido).toFixed(2)}</td>
            </tr>
          `);
          categorias.push(c.nombre_categoria);
          totales.push(parseFloat(c.total_vendido));
        });

        // Inicializar (o actualizar) gráfico de barras
        if (window.chartVentasCategoria) {
          window.chartVentasCategoria.data.labels = categorias;
          window.chartVentasCategoria.data.datasets[0].data = totales;
          window.chartVentasCategoria.update();
        } else {
          const ctx = document.getElementById('grafico-ventas_categoria').getContext('2d');
          window.chartVentasCategoria = new Chart(ctx, {
            type: 'bar',
            data: {
              labels: categorias,
              datasets: [{
                label: 'Ventas ($)',
                data: totales,
                backgroundColor: 'rgba(75, 192, 192, 0.7)'
              }]
            },
            options: {
              responsive: true,
              scales: { y: { beginAtZero: true } }
            }
          });
        }
      },
      error: function() {
        Swal.fire('Error', 'No se pudo cargar ventas por categoría.', 'error');
      }
    });
  }

  // d) Productos Menos Vendidos
  function cargarReporteProductosMenosVendidos() {
    $.ajax({
      url: 'app/models/admin/reportes_modelo.php',
      method: 'POST',
      data: { tipo: 'productos_menos_vendidos' },
      dataType: 'json',
      success: function(data) {
        const $tbody = $('#tbl-reporte-productos_menos_vendidos tbody').empty();
        data.forEach(p => {
          $tbody.append(`
            <tr>
              <td>${p.id_producto}</td>
              <td>${p.sku}</td>
              <td>${p.nombre}</td>
              <td>${p.unidades_vendidas}</td>
            </tr>
          `);
        });
      },
      error: function() {
        Swal.fire('Error', 'No se pudo cargar productos menos vendidos.', 'error');
      }
    });
  }

  // e) Clientes Top
  function cargarReporteClientesTop(fecha_ini = '', fecha_fin = '') {
    $.ajax({
      url: 'app/models/admin/reportes_modelo.php',
      method: 'POST',
      data: { tipo: 'clientes_top', fecha_ini: fecha_ini, fecha_fin: fecha_fin },
      dataType: 'json',
      success: function(data) {
        const $tbody = $('#tbl-reporte-clientes_top tbody').empty();
        data.forEach(c => {
          $tbody.append(`
            <tr>
              <td>${c.nombre_cliente}</td>
              <td>${c.cantidad_pedidos}</td>
              <td>$${parseFloat(c.total_comprado).toFixed(2)}</td>
            </tr>
          `);
        });
      },
      error: function() {
        Swal.fire('Error', 'No se pudo cargar clientes top.', 'error');
      }
    });
  }

  // f) Movimientos de Inventario
  function cargarReporteMovimientosInventario(fecha_ini = '', fecha_fin = '') {
    $.ajax({
      url: 'app/models/admin/reportes_modelo.php',
      method: 'POST',
      data: { tipo: 'movimientos_inventario', fecha_ini: fecha_ini, fecha_fin: fecha_fin },
      dataType: 'json',
      success: function(data) {
        const $tbody = $('#tbl-reporte-movimientos_inventario tbody').empty();
        data.forEach(m => {
          $tbody.append(`
            <tr>
              <td>${m.id_movimiento_inventario}</td>
              <td>${m.nombre_producto}</td>
              <td>${m.tipo_movimiento}</td>
              <td>${m.cantidad}</td>
              <td>${m.referencia ?? ''}</td>
              <td>${m.usuario ?? 'N/A'}</td>
              <td>${m.fecha}</td>
            </tr>
          `);
        });
      },
      error: function() {
        Swal.fire('Error', 'No se pudo cargar movimientos de inventario.', 'error');
      }
    });
  }

  // g) Órdenes Pendientes
  function cargarReporteOrdenesPendientes() {
    $.ajax({
      url: 'app/models/admin/reportes_modelo.php',
      method: 'POST',
      data: { tipo: 'ordenes_pendientes' },
      dataType: 'json',
      success: function(data) {
        const $tbody = $('#tbl-reporte-ordenes_pendientes tbody').empty();
        data.forEach(o => {
          $tbody.append(`
            <tr>
              <td>${o.id_orden_compra}</td>
              <td>${o.numero_orden}</td>
              <td>${o.nombre_proveedor}</td>
              <td>${o.fecha_orden}</td>
              <td>${o.fecha_esperada}</td>
              <td>${o.estado}</td>
              <td>$${parseFloat(o.total).toFixed(2)}</td>
            </tr>
          `);
        });
      },
      error: function() {
        Swal.fire('Error', 'No se pudo cargar órdenes pendientes.', 'error');
      }
    });
  }

  // h) Margen de Ganancia
  function cargarReporteMargen(fecha_ini = '', fecha_fin = '') {
    $.ajax({
      url: 'app/models/admin/reportes_modelo.php',
      method: 'POST',
      data: { tipo: 'margen', fecha_ini: fecha_ini, fecha_fin: fecha_fin },
      dataType: 'json',
      success: function(data) {
        const $tbody = $('#tbl-reporte-margen tbody').empty();
        data.forEach(p => {
          $tbody.append(`
            <tr>
              <td>${p.id_producto}</td>
              <td>${p.nombre}</td>
              <td>$${parseFloat(p.precio).toFixed(2)}</td>
              <td>$${parseFloat(p.costo).toFixed(2)}</td>
              <td>$${parseFloat(p.margen_unitario).toFixed(2)}</td>
              <td>${p.unidades_vendidas}</td>
              <td>$${parseFloat(p.margen_total).toFixed(2)}</td>
            </tr>
          `);
        });
      },
      error: function() {
        Swal.fire('Error', 'No se pudo cargar margen de ganancia.', 'error');
      }
    });
  }

  // === 4) EVENTOS “Filtrar” ===

  $('#btn-filtar-ordenes').on('click', function() {
    const fi = $('#fecha_ini').val();
    const ff = $('#fecha_fin').val();
    if (!fi || !ff) {
      Swal.fire('Aviso', 'Seleccione rango de fechas.', 'warning');
      return;
    }
    cargarReporteOrdenes(fi, ff);
  });

  $('#btn-filtar-ventas_categoria').on('click', function() {
    const fi = $('#vc_fecha_ini').val();
    const ff = $('#vc_fecha_fin').val();
    if (!fi || !ff) {
      Swal.fire('Aviso', 'Seleccione rango de fechas.', 'warning');
      return;
    }
    cargarReporteVentasCategoria(fi, ff);
  });

  $('#btn-filtar-clientes_top').on('click', function() {
    const fi = $('#ct_fecha_ini').val();
    const ff = $('#ct_fecha_fin').val();
    if (!fi || !ff) {
      Swal.fire('Aviso', 'Seleccione rango de fechas.', 'warning');
      return;
    }
    cargarReporteClientesTop(fi, ff);
  });

  $('#btn-filtar-movimientos_inventario').on('click', function() {
    const fi = $('#mi_fecha_ini').val();
    const ff = $('#mi_fecha_fin').val();
    if (!fi || !ff) {
      Swal.fire('Aviso', 'Seleccione rango de fechas.', 'warning');
      return;
    }
    cargarReporteMovimientosInventario(fi, ff);
  });

  $('#btn-filtar-margen').on('click', function() {
    const fi = $('#mg_fecha_ini').val();
    const ff = $('#mg_fecha_fin').val();
    if (!fi || !ff) {
      Swal.fire('Aviso', 'Seleccione rango de fechas.', 'warning');
      return;
    }
    cargarReporteMargen(fi, ff);
  });

  // === 5) EVENTOS “Generar Reporte” ===

  $('#btn-generar-inventario').on('click', function() {
    Swal.fire({ title: 'Generando Reporte...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    $.ajax({
      url: 'app/models/admin/reportes_export_all.php',
      method: 'POST',
      data: { tipo: 'inventario' },
      dataType: 'json',
      success: function(res) {
        Swal.close();
        if (res.ok) {
          window.open(res.pdf_url, '_blank');
          const a = document.createElement('a');
          a.href = res.json_url;
          a.download = '';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
        } else {
          Swal.fire('Error', res.message, 'error');
        }
      },
      error: function() {
        Swal.close();
        Swal.fire('Error', 'No se pudo generar el reporte de inventario.', 'error');
      }
    });
  });

  $('#btn-generar-ordenes').on('click', function() {
    const fi = $('#fecha_ini').val();
    const ff = $('#fecha_fin').val();
    if (!fi || !ff) {
      Swal.fire('Aviso', 'Seleccione rango de fechas para exportar.', 'warning');
      return;
    }
    Swal.fire({ title: 'Generando Reporte...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    $.ajax({
      url: 'app/models/admin/reportes_export_all.php',
      method: 'POST',
      data: { tipo: 'ordenes', fecha_ini: fi, fecha_fin: ff },
      dataType: 'json',
      success: function(res) {
        Swal.close();
        if (res.ok) {
          window.open(res.pdf_url, '_blank');
          const a = document.createElement('a');
          a.href = res.json_url;
          a.download = '';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
        } else {
          Swal.fire('Error', res.message, 'error');
        }
      },
      error: function() {
        Swal.close();
        Swal.fire('Error', 'No se pudo generar el reporte de órdenes.', 'error');
      }
    });
  });

  $('#btn-generar-ventas_categoria').on('click', function() {
    const fi = $('#vc_fecha_ini').val();
    const ff = $('#vc_fecha_fin').val();
    if (!fi || !ff) {
      Swal.fire('Aviso', 'Seleccione rango de fechas para exportar.', 'warning');
      return;
    }
    Swal.fire({ title: 'Generando Reporte...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    $.ajax({
      url: 'app/models/admin/reportes_export_all.php',
      method: 'POST',
      data: { tipo: 'ventas_categoria', fecha_ini: fi, fecha_fin: ff },
      dataType: 'json',
      success: function(res) {
        Swal.close();
        if (res.ok) {
          window.open(res.pdf_url, '_blank');
          const a = document.createElement('a');
          a.href = res.json_url;
          a.download = '';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
        } else {
          Swal.fire('Error', res.message, 'error');
        }
      },
      error: function() {
        Swal.close();
        Swal.fire('Error', 'No se pudo generar el reporte de ventas por categoría.', 'error');
      }
    });
  });

  $('#btn-generar-productos_menos_vendidos').on('click', function() {
    Swal.fire({ title: 'Generando Reporte...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    $.ajax({
      url: 'app/models/admin/reportes_export_all.php',
      method: 'POST',
      data: { tipo: 'productos_menos_vendidos' },
      dataType: 'json',
      success: function(res) {
        Swal.close();
        if (res.ok) {
          window.open(res.pdf_url, '_blank');
          const a = document.createElement('a');
          a.href = res.json_url;
          a.download = '';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
        } else {
          Swal.fire('Error', res.message, 'error');
        }
      },
      error: function() {
        Swal.close();
        Swal.fire('Error', 'No se pudo generar el reporte de productos menos vendidos.', 'error');
      }
    });
  });

  $('#btn-generar-clientes_top').on('click', function() {
    const fi = $('#ct_fecha_ini').val();
    const ff = $('#ct_fecha_fin').val();
    if (!fi || !ff) {
      Swal.fire('Aviso', 'Seleccione rango de fechas para exportar.', 'warning');
      return;
    }
    Swal.fire({ title: 'Generando Reporte...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    $.ajax({
      url: 'app/models/admin/reportes_export_all.php',
      method: 'POST',
      data: { tipo: 'clientes_top', fecha_ini: fi, fecha_fin: ff },
      dataType: 'json',
      success: function(res) {
        Swal.close();
        if (res.ok) {
          window.open(res.pdf_url, '_blank');
          const a = document.createElement('a');
          a.href = res.json_url;
          a.download = '';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
        } else {
          Swal.fire('Error', res.message, 'error');
        }
      },
      error: function() {
        Swal.close();
        Swal.fire('Error', 'No se pudo generar el reporte de clientes top.', 'error');
      }
    });
  });

  $('#btn-generar-movimientos_inventario').on('click', function() {
    const fi = $('#mi_fecha_ini').val();
    const ff = $('#mi_fecha_fin').val();
    if (!fi || !ff) {
      Swal.fire('Aviso', 'Seleccione rango de fechas para exportar.', 'warning');
      return;
    }
    Swal.fire({ title: 'Generando Reporte...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    $.ajax({
      url: 'app/models/admin/reportes_export_all.php',
      method: 'POST',
      data: { tipo: 'movimientos_inventario', fecha_ini: fi, fecha_fin: ff },
      dataType: 'json',
      success: function(res) {
        Swal.close();
        if (res.ok) {
          window.open(res.pdf_url, '_blank');
          const a = document.createElement('a');
          a.href = res.json_url;
          a.download = '';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
        } else {
          Swal.fire('Error', res.message, 'error');
        }
      },
      error: function() {
        Swal.close();
        Swal.fire('Error', 'No se pudo generar el reporte de movimientos de inventario.', 'error');
      }
    });
  });

  $('#btn-generar-ordenes_pendientes').on('click', function() {
    Swal.fire({ title: 'Generando Reporte...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    $.ajax({
      url: 'app/models/admin/reportes_export_all.php',
      method: 'POST',
      data: { tipo: 'ordenes_pendientes' },
      dataType: 'json',
      success: function(res) {
        Swal.close();
        if (res.ok) {
          window.open(res.pdf_url, '_blank');
          const a = document.createElement('a');
          a.href = res.json_url;
          a.download = '';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
        } else {
          Swal.fire('Error', res.message, 'error');
        }
      },
      error: function() {
        Swal.close();
        Swal.fire('Error', 'No se pudo generar el reporte de órdenes pendientes.', 'error');
      }
    });
  });

  $('#btn-generar-margen').on('click', function() {
    const fi = $('#mg_fecha_ini').val();
    const ff = $('#mg_fecha_fin').val();
    if (!fi || !ff) {
      Swal.fire('Aviso', 'Seleccione rango de fechas para exportar.', 'warning');
      return;
    }
    Swal.fire({ title: 'Generando Reporte...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    $.ajax({
      url: 'app/models/admin/reportes_export_all.php',
      method: 'POST',
      data: { tipo: 'margen', fecha_ini: fi, fecha_fin: ff },
      dataType: 'json',
      success: function(res) {
        Swal.close();
        if (res.ok) {
          window.open(res.pdf_url, '_blank');
          const a = document.createElement('a');
          a.href = res.json_url;
          a.download = '';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
        } else {
          Swal.fire('Error', res.message, 'error');
        }
      },
      error: function() {
        Swal.close();
        Swal.fire('Error', 'No se pudo generar el reporte de margen de ganancia.', 'error');
      }
    });
  });
});
