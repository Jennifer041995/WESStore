// Este archivo JavaScript se encarga de manejar la lógica del carrito de compras

async function cargarCarrito() {
    const res = await fetch('app/models/cart/obtener_carrito.php');
    const json = await res.json();
    const contenedor = document.getElementById('contenido-carrito');
    contenedor.innerHTML = '';

    if (json.status === 'empty') {
        contenedor.innerHTML = '<p>No hay productos en el carrito.</p>';
        return;
    }

    if (json.status === 'error') {
        contenedor.innerHTML = '<p>Error al cargar el carrito.</p>';
        return;
    }

    let total = 0;
    let html = `
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
            <tbody>
    `;
    
    json.data.forEach(p => {
        html += `
            <tr>
                <td>${p.nombre}</td>
                <td>$${p.precio}</td>
                <td>
                    <input type="number" min="1" value="${p.cantidad}" class="form-control form-control-sm cantidad-input" data-id="${p.id_producto}">
                </td>
                <td>$${p.subtotal.toFixed(2)}</td>
                <td><button class="btn btn-danger btn-sm" onclick="eliminarDelCarrito(${p.id_producto})">Eliminar</button></td>
            </tr>
        `;
        total += p.subtotal;
    });

    html += `
            </tbody>
        </table>
        <h4 class="text-end">Total: $${total.toFixed(2)}</h4>
        <div class="text-end">
            <button class="btn btn-success" id="finalizar-compra">Finalizar Compra</button>
        </div>
    `;

    contenedor.innerHTML = html;

    // Re-asociar eventos a inputs de cantidad
    document.querySelectorAll('.cantidad-input').forEach(input => {
        input.addEventListener('change', function () {
            const id = this.dataset.id;
            const cantidad = this.value;

            fetch('app/models/actualizar_cantidad.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&cantidad=${cantidad}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    cargarCarrito(); // recargar carrito actualizado
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        });
    });

    // Botón finalizar compra
    const btnFinalizar = document.getElementById('finalizar-compra');
    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', () => {
            // Aquí enlazamos a checkout.html (puedes usar navegación o abrir modal)
            window.location.href = 'checkout.html';
        });
    }
}

async function eliminarDelCarrito(id) {
    const res = await fetch('app/models/eliminar_carrito.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ producto_id: id })
    });
    const json = await res.json();
    if (json.status === 'success') {
        cargarCarrito();
    } else {
        alert(json.message);
    }
}

// Ejecutamos la función inicial
cargarCarrito();