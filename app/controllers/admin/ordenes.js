$(document).ready(function() {
  const $tblOrdenesBody = $('#tbl-ordenes tbody');
  const $tblDetallesBody = $('#tbl-detalles tbody');
  const IVA_RATE = 0.15;

  // 1) Cargar datos iniciales
  cargarProveedorSelect();
  cargarProductosParaLineas();
  cargarOrdenes();

  // Función para cargar select de proveedores
  function cargarProveedorSelect() {
    return $.getJSON('app/models/admin/proveedores_modelo.php?action=listar')
      .done(function(data) {
        const $sel = $('#proveedor_id').empty().append('<option value="">Seleccione...</option>');
        data.forEach(p => {
          $sel.append(`<option value="${p.id_proveedor}">${p.nombre_proveedor}</option>`);
        });
      });
  }

  // Función para cargar array global de productos para líneas de detalle
  let productosGlobal = [];
  function cargarProductosParaLineas() {
    return $.getJSON('app/models/admin/productos_modelo.php?action=listar')
      .done(function(data) {
        productosGlobal = data; // cada objeto: { id_producto, nombre, precio, ... }
      });
  }

  // 2) Listar todas las órdenes
  function cargarOrdenes() {
    $.getJSON('app/models/admin/ordenes_modelo.php?action=listar')
      .done(function(data) {
        $tblOrdenesBody.empty();
        data.forEach(o => {
          $tblOrdenesBody.append(`
            <tr>
              <td>${o.id_orden_compra}</td>
              <td>${o.nombre_proveedor}</td>
              <td>${o.fecha_orden}</td>
              <td>${o.fecha_esperada || ''}</td>
              <td>$${parseFloat(o.subtotal).toFixed(2)}</td>
              <td>$${parseFloat(o.iva).toFixed(2)}</td>
              <td>$${parseFloat(o.total).toFixed(2)}</td>
              <td>${o.estado}</td>
              <td>
                <button class="btn btn-sm btn-info btn-ver" data-id="${o.id_orden_compra}">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-danger btn-eliminar" data-id="${o.id_orden_compra}">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          `);
        });
      })
      .fail(function() {
        Swal.fire('Error', 'No se pudieron cargar las órdenes.', 'error');
      });
  }

  // 3) Abrir modal “Nueva Orden”
  $('#btn-nueva-orden').on('click', function () {
    $('#ordenModalLabel').text('Nueva Orden de Compra');
    $('#form-orden')[0].reset();
    $('#id_orden').val('');
    $tblDetallesBody.empty();
    actualizarTotales();

    // Establecer fecha actual en fecha_orden
    const hoy = new Date();
    const fechaActual = hoy.toISOString().split('T')[0];
    $('#fecha_orden').val(fechaActual);

    // Establecer fecha esperada mínima = hoy + 5 días
    const fechaMinima = new Date();
    fechaMinima.setDate(hoy.getDate() + 5);
    const fechaEsperadaStr = fechaMinima.toISOString().split('T')[0];

    // Asignar valor y restricción mínima
    $('#fecha_esperada').val(fechaEsperadaStr);
    $('#fecha_esperada').attr('min', fechaEsperadaStr);

    // Cargar proveedores y productos
    $.when(cargarProveedorSelect(), cargarProductosParaLineas()).done(function () {
      $('#ordenModal').modal('show');
    });
  });

  // 4) Agregar línea en la orden
  $('#btn-agregar-linea').on('click', function() {
    const $fila = $('<tr>');
    // Columna: Producto
    const $tdProd = $('<td>');
    const $selProd = $('<select class="form-control form-control-sm select-producto"><option value="">Seleccione...</option></select>');
    productosGlobal.forEach(prod => {
      $selProd.append(`<option value="${prod.id_producto}" data-precio="${prod.precio}">${prod.nombre}</option>`);
    });
    $tdProd.append($selProd);
    // Cantidad
    const $tdCant = $('<td><input type="number" min="1" value="1" class="form-control form-control-sm input-cantidad"></td>');
    // Precio Unitario
    const $tdPrecio = $('<td><input type="number" step="0.01" min="0" class="form-control form-control-sm input-precio"></td>');
    // Subtotal
    const $tdSub = $('<td class="text-right">0.00</td>');
    // Botón eliminar
    const $tdAcc = $('<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-quitar-linea">X</button></td>');

    $fila.append($tdProd, $tdCant, $tdPrecio, $tdSub, $tdAcc);
    $tblDetallesBody.append($fila);

    // Cuando el usuario seleccione un producto, autopoblar precio unitario
    $selProd.on('change', function() {
      const precioDef = parseFloat($(this).find('option:selected').data('precio')) || 0;
      $fila.find('.input-precio').val(precioDef.toFixed(2));
      recalcularFila($fila);
      actualizarTotales();
    });

    // Cuando cambie cantidad o precio manualmente
    $fila.on('input', '.input-cantidad, .input-precio', function() {
      recalcularFila($fila);
      actualizarTotales();
    });

    // Quitar línea
    $fila.on('click', '.btn-quitar-linea', function() {
      $fila.remove();
      actualizarTotales();
    });
  });

  // Recalcula el subtotal de una fila
  function recalcularFila($fila) {
    const qty = parseInt($fila.find('.input-cantidad').val()) || 0;
    const precio = parseFloat($fila.find('.input-precio').val()) || 0;
    const subt = qty * precio;
    $fila.find('td').eq(3).text(subt.toFixed(2));
  }

  // 5) Actualizar los totales (subtotal, IVA y total)
  function actualizarTotales() {
    let subtotal = 0;
    $tblDetallesBody.find('tr').each(function() {
      const fila = $(this);
      const value = parseFloat(fila.find('td').eq(3).text()) || 0;
      subtotal += value;
    });
    const iva = subtotal * IVA_RATE;
    const total = subtotal + iva;
    $('#subtotal').val(subtotal.toFixed(2));
    $('#iva').val(iva.toFixed(2));
    $('#total').val(total.toFixed(2));
  }

  // 6) Guardar / Editar Orden
  $('#form-orden').on('submit', function(e) {
    e.preventDefault();
    const idOrden = $('#id_orden').val().trim();
    const proveedor_id   = $('#proveedor_id').val();
    const fecha_orden    = $('#fecha_orden').val();
    const fecha_esperada = $('#fecha_esperada').val();
    const notas          = $('#notas').val().trim();
    const subt           = parseFloat($('#subtotal').val()) || 0;
    const iva            = parseFloat($('#iva').val()) || 0;
    const total          = parseFloat($('#total').val()) || 0;

    // Validación mínima
    if (!proveedor_id) {
      Swal.fire('Error', 'Seleccione un proveedor.', 'warning');
      return;
    }
    if ($tblDetallesBody.find('tr').length === 0) {
      Swal.fire('Error', 'Agregue al menos una línea de detalle.', 'warning');
      return;
    }

    // Construir array de líneas
    const lineas = [];
    let valido = true;
    $tblDetallesBody.find('tr').each(function() {
      const fila = $(this);
      const prodId = fila.find('.select-producto').val();
      const cantidad = parseInt(fila.find('.input-cantidad').val()) || 0;
      const precioUnit = parseFloat(fila.find('.input-precio').val()) || 0;
      if (!prodId || cantidad <= 0 || precioUnit <= 0) {
        valido = false;
        return false; // romper each
      }
      lineas.push({
        producto_id: prodId,
        cantidad: cantidad,
        precio_unitario: precioUnit
      });
    });
    if (!valido) {
      Swal.fire('Error', 'Revise que cada línea tenga producto, cantidad y precio válidos.', 'warning');
      return;
    }

    const payload = {
      proveedor_id,
      fecha_orden,
      fecha_esperada,
      subtotal: subt,
      iva: iva,
      total: total,
      notas: notas,
      detalles: lineas
    };
    let action = 'agregar';
    if (idOrden) {
      action = 'editar';
      payload.id_orden = idOrden;
    }

    $.ajax({
      url: 'app/models/admin/ordenes_modelo.php?action=' + action,
      method: 'POST',
      data: { payload: JSON.stringify(payload) },
      dataType: 'json'
    }).done(function(resp) {
      if (resp.status === 'success') {
        $('#ordenModal').modal('hide');
        Swal.fire('Éxito', resp.message, 'success');
        cargarOrdenes();
      } else {
        Swal.fire('Error', resp.message, 'error');
      }
    }).fail(function() {
      Swal.fire('Error', 'Error de conexión.', 'error');
    });
  });

  // 7) Ver/Eliminar Orden
  $tblOrdenesBody.on('click', '.btn-ver', function() {
    const id = $(this).data('id');
    // Para simplificar, solo mostramos un alert con los datos
    $.getJSON('app/models/admin/ordenes_modelo.php?action=obtener&id=' + id)
      .done(function(o) {
        let detalles = '';
        o.detalles.forEach(d => {
          detalles += `\n• ${d.nombre_producto} | Cant: ${d.cantidad} | P.Unit: $${parseFloat(d.precio_unitario).toFixed(2)} | Sub: $${(d.cantidad * d.precio_unitario).toFixed(2)}`;
        });
        Swal.fire({
          title: `Orden #${o.id_orden_compra}`,
          html: `
            <strong>Proveedor:</strong> ${o.nombre_proveedor}<br>
            <strong>Fecha Orden:</strong> ${o.fecha_orden}<br>
            <strong>Fecha Esperada:</strong> ${o.fecha_esperada || '—'}<br>
            <strong>Subtotal:</strong> $${parseFloat(o.subtotal).toFixed(2)}<br>
            <strong>IVA:</strong> $${parseFloat(o.iva).toFixed(2)}<br>
            <strong>Total:</strong> $${parseFloat(o.total).toFixed(2)}<br>
            <strong>Estado:</strong> ${o.estado}<br>
            <strong>Notas:</strong> ${o.notas || '—'}<br>
            <hr>
            <pre style="text-align:left;">${detalles}</pre>
          `
        });
      })
      .fail(function() {
        Swal.fire('Error', 'No se pudo obtener la orden.', 'error');
      });
  });

  $tblOrdenesBody.on('click', '.btn-eliminar', function() {
    const id = $(this).data('id');
    Swal.fire({
      title: '¿Eliminar orden?',
      text: 'Esta acción eliminará la orden y todos sus detalles.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then(function(res) {
      if (!res.isConfirmed) return;
      $.ajax({
        url: 'app/models/admin/ordenes_modelo.php?action=eliminar',
        method: 'POST',
        data: { id_orden: id },
        dataType: 'json'
      }).done(function(resp) {
        if (resp.status === 'success') {
          Swal.fire('Eliminada', resp.message, 'success');
          cargarOrdenes();
        } else {
          Swal.fire('Error', resp.message, 'error');
        }
      }).fail(function() {
        Swal.fire('Error', 'No se pudo eliminar.', 'error');
      });
    });
  });
});