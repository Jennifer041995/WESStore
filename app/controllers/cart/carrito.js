$(function() {
  function renderCarrito(items) {
    const $contenedor = $('#contenido-carrito');
    if (!items.length) {
      $contenedor.html('<p>No hay productos en el carrito.</p>');
      return;
    }
    let total = 0;
    const rows = items.map(p => {
      const precio   = parseFloat(p.precio)   || 0;
      const cantidad = parseInt(p.cantidad,10) || 1;
      const subtotal = parseFloat(p.subtotal) || precio * cantidad;
      total += subtotal;
      return `
        <tr>
          <td>
            <img src="${p.imagen_principal||'img/default.png'}" width="50" class="mr-2">
            ${p.nombre}
          </td>
          <td>$${precio.toFixed(2)}</td>
          <td>
            <input type="number" min="1" max="${p.stock}" value="${cantidad}"
              class="form-control form-control-sm cantidad-input" data-id="${p.id_producto}">
          </td>
          <td>$${subtotal.toFixed(2)}</td>
          <td>
            <button class="btn btn-danger btn-sm btn-eliminar" data-id="${p.id_producto}">
              Eliminar
            </button>
          </td>
        </tr>`;
    }).join('');

    $contenedor.html(`
      <table class="table">
        <thead>
          <tr>
            <th>Producto</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
            <th></th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
      <h4 class="text-end">Total: $${total.toFixed(2)}</h4>
      <div class="text-end">
        <button id="finalizar-compra" class="btn btn-success">Finalizar Compra</button>
      </div>
    `);
  }

  function cargarCarrito() {
    $.getJSON('app/models/cart/obtener_carrito.php')
      .done(resp => {
        if (resp.status === 'success') renderCarrito(resp.data);
        else if (resp.status === 'empty') renderCarrito([]);
        else $('#contenido-carrito').html('<p>Error al cargar el carrito.</p>');
      })
      .fail(() => $('#contenido-carrito').html('<p>Error de conexión.</p>'));
  }

  // Eventos delegados
  $('#contenido-carrito')
    .on('change', '.cantidad-input', function() {
      const id  = $(this).data('id');
      const qty = $(this).val();
      $.post('app/models/cart/actualizar_cantidad.php',
        { id: id, cantidad: qty }, resp => {
          if (resp.status === 'success') cargarCarrito();
          else Swal.fire('Error', resp.message, 'error');
        }, 'json');
    })
    .on('click', '.btn-eliminar', function() {
      const id = $(this).data('id');
      $.ajax({
        url: 'app/models/cart/eliminar_carrito.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ producto_id: id }),
        success(resp) {
          if (resp.status === 'success') cargarCarrito();
          else Swal.fire('Error', resp.message, 'error');
        }
      });
    });

  // Finalizar compra abre el modal y carga el fragmento
  $(document).on('click', '#finalizar-compra', function() {
    $('#checkout-container').load('app/views/checkout.html', function(resp, status) {
      if (status === 'error') {
        Swal.fire('Error', 'No se pudo cargar el checkout.', 'error');
        return;
      }
      initCheckout();
      $('#checkoutModal').modal('show');
    });
  });

  // Inicializar
  cargarCarrito();
});