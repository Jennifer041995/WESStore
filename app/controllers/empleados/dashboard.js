$(function() {
  // 1) Cargar todas las métricas y sección “Últimas Órdenes”
  function cargarDashboard() {
    $.ajax({
      url: 'app/models/empleados/dashboard_modelo.php',
      method: 'GET',
      dataType: 'json',
      success: function(res) {
        if (!res.ok) {
          Swal.fire('Error', 'No se pudieron obtener los datos del dashboard.', 'error');
          return;
        }

        // Métricas simples
        $('#count-productos').text(res.total_productos);
        $('#count-ordenes').text(res.ordenes_abiertas);
        $('#count-stock-bajo').text(res.stock_bajo);
        $('#count-ventas-hoy').text(parseFloat(res.ventas_hoy).toFixed(2));
        $('#count-clientes-nuevos').text(res.clientes_nuevos);

        // Cargar gráfico de Órdenes Semanales
        generarGraficoOrdenesSemana(res.ordenes_semana);

        // Cargar tabla de últimas órdenes
        const $tbody = $('#tbl-ultimas-ordenes tbody').empty();
        if (Array.isArray(res.ultimas_ordenes) && res.ultimas_ordenes.length) {
          res.ultimas_ordenes.forEach(o => {
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
        } else {
          $tbody.append('<tr><td colspan="5" class="text-center">Sin órdenes recientes.</td></tr>');
        }
      },
      error: function() {
        Swal.fire('Error', 'Falla al conectar con el servidor.', 'error');
      }
    });
  }

  // 2) Función para generar el gráfico de barras semanal
  function generarGraficoOrdenesSemana(dataSemana) {
    // dataSemana esperado: [{ dia: '2025-05-25', total: 3 }, … ] 7 objetos
    const etiquetas = dataSemana.map(item => item.dia_label);
    const valores   = dataSemana.map(item => item.cantidad);

    const ctx = document.getElementById('grafico-ordenes-semana').getContext('2d');
    // Si ya existe, destruye antes de crear uno nuevo
    if (window.graficoOrdenes) {
      window.graficoOrdenes.destroy();
    }
    window.graficoOrdenes = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: etiquetas,
        datasets: [{
          label: 'Órdenes',
          data: valores,
          backgroundColor: 'rgba(54, 162, 235, 0.7)'
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1 }
          }
        }
      }
    });
  }

  // 3) Ejecutar carga inicial
  cargarDashboard();
});
