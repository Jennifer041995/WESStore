$(document).ready(function() {
    cargarDatosUsuario();
    cargarDirecciones();
    cargarPedidos();

    $('#form-info-personal').submit(function(e) {
        e.preventDefault();
        guardarInformacionPersonal();
    });

    $('#form-direccion').submit(function(e) {
        e.preventDefault();
        guardarDireccion();
    });
});

$(document).ajaxError(function(event, jqxhr) {
    Swal.fire('Error', 'Error de conexión con el servidor', 'error');
});

function cargarDatosUsuario() {
    $.ajax({
        url: 'app/models/usuario.php?action=getUser',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                const user = response.data;
                $('#nombre-usuario').text(user.nombre + ' ' + (user.apellido || ''));
                $('#email-usuario').text(user.email);
                $('input[name="nombre"]').val(user.nombre);
                $('input[name="apellido"]').val(user.apellido || '');
                $('input[name="telefono"]').val(user.telefono || '');
            }
        },
        error: function() {
            Swal.fire('Error', 'No se pudieron cargar los datos del usuario', 'error');
        }
    });
}

function guardarInformacionPersonal() {
    const $form = $('#form-info-personal');
    const telefono = $form.find('input[name="telefono"]').val();
    
    if(telefono && !/^[0-9]{8,}$/.test(telefono)) {
        Swal.fire('Error', 'Formato de teléfono inválido', 'error');
        return;
    }
    
    const formData = $form.serialize();
    
    $.ajax({
        url: 'app/models/usuario.php?action=updateUser',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                Swal.fire('¡Éxito!', 'Datos actualizados correctamente', 'success');
                cargarDatosUsuario();
            } else {
                Swal.fire('Error', response.error || 'Error al actualizar', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al guardar los cambios', 'error');
        }
    });
}

function mostrarModalDireccion(direccion = null) {
    const $modal = $('#modalDireccion');
    const $form = $modal.find('form');
    
    if(direccion) {
        $form.find('input[name="id_direccion"]').val(direccion.id_direccion_usuario);
        $form.find('input[name="alias"]').val(direccion.alias);
        $form.find('textarea[name="direccion"]').val(direccion.direccion);
        $form.find('input[name="ciudad"]').val(direccion.ciudad);
        $form.find('input[name="departamento"]').val(direccion.departamento);
        $form.find('select[name="principal"]').val(direccion.principal);
    } else {
        $form[0].reset();
        $form.find('input[name="id_direccion"]').val('');
    }
    
    $modal.modal('show');
}

function cargarDirecciones() {
    $.ajax({
        url: 'app/models/direcciones.php?action=getAll',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            const $container = $('#lista-direcciones');
            $container.empty();
            
            if(response.success && response.data.length > 0) {
                response.data.forEach(direccion => {
                    const card = `
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5>${direccion.alias} ${direccion.principal ? '<span class="badge badge-primary">Principal</span>' : ''}</h5>
                                    <p>${direccion.direccion}<br>
                                    ${direccion.ciudad}, ${direccion.departamento}</p>
                                    <button class="btn btn-sm btn-warning" onclick="mostrarModalDireccion(${JSON.stringify(direccion)})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="eliminarDireccion(${direccion.id_direccion_usuario})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>`;
                    $container.append(card);
                });
            } else {
                $container.html('<p class="text-muted">No hay direcciones registradas</p>');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al cargar direcciones', 'error');
        }
    });
}

function guardarDireccion() {
    const $form = $('#form-direccion');
    const ciudad = $form.find('input[name="ciudad"]').val();
    
    if(!ciudad || !/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,}$/.test(ciudad)) {
        Swal.fire('Error', 'Ciudad inválida', 'error');
        return;
    }

    const formData = $form.serialize();
    
    $.ajax({
        url: 'app/models/direcciones.php?action=save',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                $('#modalDireccion').modal('hide');
                Swal.fire('¡Éxito!', 'Dirección guardada correctamente', 'success');
                cargarDirecciones();
            } else {
                Swal.fire('Error', response.error || 'Error al guardar', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al guardar la dirección', 'error');
        }
    });
}

function eliminarDireccion(id) {
    Swal.fire({
        title: '¿Eliminar dirección?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `app/models/direcciones.php?action=delete&id=${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        Swal.fire('¡Éxito!', 'Dirección eliminada', 'success');
                        cargarDirecciones();
                    } else {
                        Swal.fire('Error', response.error || 'Error al eliminar', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al eliminar la dirección', 'error');
                }
            });
        }
    });
}

function cargarPedidos() {
    $.ajax({
        url: 'app/models/pedidos.php?action=getByUser',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            const $tbody = $('#lista-pedidos');
            $tbody.empty();
            
            if(response.success && response.data.length > 0) {
                response.data.forEach(pedido => {
                    const estadoClass = {
                        'Pendiente': 'badge-warning',
                        'Procesando': 'badge-info',
                        'Enviado': 'badge-primary',
                        'Entregado': 'badge-success',
                        'Cancelado': 'badge-danger'
                    }[pedido.estado] || 'badge-secondary';
                    
                    const fila = `
                        <tr>
                            <td>#${pedido.id_pedido}</td>
                            <td>${new Date(pedido.fecha_pedido).toLocaleDateString()}</td>
                            <td><span class="badge ${estadoClass}">${pedido.estado}</span></td>
                            <td>$${pedido.total.toFixed(2)}</td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="verDetallePedido(${pedido.id_pedido})">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>`;
                    $tbody.append(fila);
                });
            } else {
                $tbody.html('<tr><td colspan="5" class="text-center">No hay pedidos registrados</td></tr>');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al cargar pedidos', 'error');
        }
    });
}

function verDetallePedido(idPedido) {
    $.ajax({
        url: `app/models/pedidos.php?action=getDetalle&id=${idPedido}`,
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            Swal.showLoading();
        },
        success: function(response) {
            Swal.close();
            if(response.success) {
                mostrarModalPedido(response.data);
            } else {
                Swal.fire('Error', response.error, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al cargar detalle del pedido', 'error');
        }
    });
}

function mostrarModalPedido(data) {
    const pedido = data.pedido;
    const detalles = data.detalles;
    
    let html = `
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h5>Pedido #${pedido.id_pedido}</h5>
                    <p><strong>Fecha:</strong> ${new Date(pedido.fecha_pedido).toLocaleString()}</p>
                    <p><strong>Estado:</strong> <span class="badge ${getEstadoClass(pedido.estado)}">${pedido.estado}</span></p>
                    <p><strong>Total:</strong> $${pedido.total.toFixed(2)}</p>
                    <hr>
                    <h6>Productos:</h6>
                </div>
                <div class="col-12">
                    <table class="table table-sm table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>P. Unitario</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>`;
    
    detalles.forEach(detalle => {
        html += `
            <tr>
                <td>${detalle.nombre} (${detalle.sku})</td>
                <td>${detalle.cantidad}</td>
                <td>$${detalle.precio_unitario.toFixed(2)}</td>
                <td>$${(detalle.cantidad * detalle.precio_unitario).toFixed(2)}</td>
            </tr>`;
    });
    
    html += `</tbody></table></div></div></div>`;
    
    Swal.fire({
        title: 'Detalle del Pedido',
        html: html,
        width: '800px',
        showConfirmButton: false,
        showCloseButton: true
    });
}

function getEstadoClass(estado) {
    const clases = {
        'Pendiente': 'badge-warning',
        'Procesando': 'badge-info',
        'Enviado': 'badge-primary',
        'Entregado': 'badge-success',
        'Cancelado': 'badge-danger'
    };
    return clases[estado] || 'badge-secondary';
}