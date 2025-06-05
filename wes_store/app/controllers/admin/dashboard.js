$(document).ready(function() {
  cargarResumen();
  cargarGraficoOrdenes();
  cargarGraficoPendientesEntregadas();
  cargarProductoMasVendido();
  cargarClienteMasFrecuente();
  cargarUltimasOrdenes();
});

// 1. Resumen de totales
function cargarResumen() {
  $.ajax({
    url: 'app/models/admin/dashboard_model.php',
    method: 'POST',
    data: { accion: 'resumen' },
    dataType: 'json',
    success: function(data) {
      $('#total-productos').text(data.productos);
      $('#total-proveedores').text(data.proveedores);
      $('#total-ordenes').text(data.ordenes);
      $('#total-ingresos').text(parseFloat(data.ingresos).toFixed(2));
      $('#total-clientes').text(data.clientes);
      $('#total-admin').text(data.admins);
    },
    error: function() {
      Swal.fire('Error', 'No se pudo cargar el resumen.', 'error');
    }
  });
}

function cargarGraficoOrdenes() {
  $.ajax({
    url: 'app/models/admin/dashboard_model.php',
    method: 'POST',
    data: { accion: 'grafico_ordenes' },
    dataType: 'json',
    success: function(data) {
      const ctx = document.getElementById('grafico-ordenes').getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: data.meses,
          datasets: [{
            label: 'Órdenes',
            data: data.valores,
            backgroundColor: 'rgba(54, 162, 235, 0.7)'
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {               // En Chart.js v3+, se usa “y” en lugar de “yAxes”
              beginAtZero: true
            }
          }
        }
      });
    },
    error: function() {
      Swal.fire('Error', 'No se pudo cargar el gráfico de órdenes.', 'error');
    }
  });
}

function cargarGraficoPendientesEntregadas() {
  $.ajax({
    url: 'app/models/admin/dashboard_model.php',
    method: 'POST',
    data: { accion: 'grafico_pendientes' },
    dataType: 'json',
    success: function(data) {
      const ctx = document.getElementById('grafico-pendientes-entregadas').getContext('2d');
      new Chart(ctx, {
        type: 'pie',
        data: {
          labels: ['Pendientes', 'Entregadas'],
          datasets: [{
            data: [data.pendientes, data.entregadas],
            backgroundColor: ['#ffc107', '#28a745']
          }]
        },
        options: {
          responsive: true
          // No necesita escalas para gráfico de torta
        }
      });
    },
    error: function() {
      Swal.fire('Error', 'No se pudo cargar el gráfico de estado de órdenes.', 'error');
    }
  });
}

// 4. Producto más vendido
function cargarProductoMasVendido() {
  $.ajax({
    url: 'app/models/admin/dashboard_model.php',
    method: 'POST',
    data: { accion: 'producto_top' },
    dataType: 'json',
    success: function(p) {
      const cont = $('#producto-mas-vendido');
      let imgUrl = p.imagen_principal ? p.imagen_principal : 'img/default.png';
      let html = `
        <img src="${imgUrl}" class="img-fluid mb-2" style="max-height:120px">
        <h6>${p.nombre}</h6>
        <p>Vendidos: <strong>${p.cantidad}</strong></p>
      `;
      cont.html(html);
    },
    error: function() {
      Swal.fire('Error', 'No se pudo cargar el producto más vendido.', 'error');
    }
  });
}

// 5. Cliente más frecuente
function cargarClienteMasFrecuente() {
  $.ajax({
    url: 'app/models/admin/dashboard_model.php',
    method: 'POST',
    data: { accion: 'cliente_top' },
    dataType: 'json',
    success: function(c) {
      const cont = $('#cliente-mas-frecuente');
      let html = `
        <h6>${c.nombre_completo}</h6>
        <p>Pedidos: <strong>${c.cantidad}</strong></p>
      `;
      cont.html(html);
    },
    error: function() {
      Swal.fire('Error', 'No se pudo cargar el cliente más frecuente.', 'error');
    }
  });
}

// 6. Últimas 5 órdenes
function cargarUltimasOrdenes() {
  $.ajax({
    url: 'app/models/admin/dashboard_model.php',
    method: 'POST',
    data: { accion: 'ultimas_ordenes' },
    dataType: 'json',
    success: function(ord) {
      let html = '';
      ord.forEach(function(o) {
        html += `
          <tr>
            <td>${o.id_pedido}</td>
            <td>${o.nombre_cliente}</td>
            <td>${o.fecha}</td>
            <td>$${parseFloat(o.total).toFixed(2)}</td>
            <td>${o.estado}</td>
          </tr>
        `;
      });
      $('#tabla-ultimas-ordenes').html(html);
    },
    error: function() {
      Swal.fire('Error', 'No se pudieron cargar las últimas órdenes.', 'error');
    }
  });
}
