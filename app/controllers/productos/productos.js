$(document).ready(function() {
    var $modalVistaPrevia       = $('#vistaPreviaModal');
    var productoActualId        = null;
    var $categoria              = $('#filtro-categoria');
    var $subcategoria           = $('#filtro-subcategoria');
    var $marca                  = $('#filtro-marca');
    var $buscador               = $('#buscador-productos');
    var $limpiarBtn             = $('#limpiar-filtros');
    var $contenedorProductos    = $('#lista-productos');
    var $detalleProductoDiv     = $('#detalle-producto');
    var $comentariosProductoDiv = $('#comentarios-producto');
    var $formComentario         = $('#form-comentario');

    // Verificar existencia de elementos
    if (
        !$categoria.length || !$subcategoria.length || !$marca.length ||
        !$buscador.length  || !$limpiarBtn.length  || !$contenedorProductos.length ||
        !$detalleProductoDiv.length || !$comentariosProductoDiv.length || !$formComentario.length
    ) {
        console.warn("Faltan elementos del DOM para inicializar productos.");
        return;
    }

    function mostrarProductos(productos) {
        $contenedorProductos.empty();

        if (!$.isArray(productos)) {
            Swal.fire("Error", "Respuesta inválida del servidor al mostrar productos", "error");
            return;
        }

        if (productos.length === 0) {
            $contenedorProductos.html('<p>No se encontraron productos.</p>');
            return;
        }

        $.each(productos, function(_, producto) {
            var $card = $( 
                '<div class="col-md-4 mb-3">' +
                    '<div class="card h-100">' +
                    '<img src="' + producto.imagen_principal + '" class="card-img-top" alt="' + producto.nombre + '">'+
                    '<div class="card-body d-flex flex-column">' +
                        '<h5 class="card-title">' + producto.nombre + '</h5>' +
                        '<p class="card-text flex-grow-1">' + producto.descripcion_corta + '</p>' +
                        '<p class="card-text"><strong>$' + producto.precio + '</strong></p>' +
                        '<div class="mt-auto d-flex justify-content-between">' +
                        '<button class="btn btn-primary btn-ver-detalle" data-id="' + producto.id_producto + '">Vista previa</button>' +
                        '<button class="btn btn-success btn-add-carrito" data-id="' + producto.id_producto + '">Añadir al carrito</button>' +
                        '</div>' +
                    '</div>' +
                    '</div>' +
                '</div>'
            );
            $contenedorProductos.append($card);
        });

        // Eventos sobre botones
        $contenedorProductos
            .off('click', '.btn-ver-detalle')
            .on('click', '.btn-ver-detalle', function() {
                productoActualId = $(this).data('id');
                cargarDetalleProducto(productoActualId);
                $modalVistaPrevia.modal('show');
            });

        $contenedorProductos
            .off('click', '.btn-add-carrito')
            .on('click', '.btn-add-carrito', function() {
                agregarAlCarrito($(this).data('id'));
            });
    }

    function cargarProductos() {
        var params = {
            categoria:   $categoria.val(),
            subcategoria:$subcategoria.val(),
            marca:       $marca.val(),
            buscar:      $buscador.val()
        };

        $.getJSON('app/models/productos/productos.php', params)
         .done(mostrarProductos)
         .fail(function() {
             Swal.fire("Error", "No se pudieron cargar los productos", "error");
         });
    }

    function cargarSelect(selectId, url) {
        $.getJSON(url)
         .done(function(data) {
             var $sel = $('#' + selectId);
             if (!$sel.length) return;

             $sel.empty().append('<option value="">Todos</option>');

             if (!$.isArray(data)) {
                 console.error('Datos inválidos para ' + selectId, data);
                 Swal.fire("Error", "Error en los datos de " + selectId, "error");
                 return;
             }

             $.each(data, function(_, opcion) {
                 $sel.append('<option value="'+ opcion.id +'">'+ opcion.nombre +'</option>');
             });
         })
         .fail(function() {
             Swal.fire("Error", "No se pudo cargar " + selectId, "error");
         });
    }

    function cargarDetalleProducto(id) {
        $detalleProductoDiv.html('Cargando...');
        $comentariosProductoDiv.html('Cargando...');
        $formComentario[0].reset();

        $.getJSON('app/models/productos/obtener_detalle_producto.php', { id: id })
         .done(function(data) {
             if (data.status !== 'success') {
                 $detalleProductoDiv.html('<p>Error al cargar el producto.</p>');
                 $comentariosProductoDiv.empty();
                 return;
             }

             var p = data.data.producto;
             $detalleProductoDiv.html(
                 '<h4>'+ p.nombre +'</h4>' +
                 '<p>'+ p.descripcion_corta +'</p>' +
                 '<p><strong>Precio: $'+ p.precio +'</strong></p>' +
                 '<button id="btn-add-carrito-modal" class="btn btn-success">Añadir al carrito</button>'
             );

             $('#btn-add-carrito-modal')
               .off('click')
               .on('click', function() { agregarAlCarrito(p.id_producto); });

             var comentarios = data.data.comentarios;
             if (!comentarios.length) {
                 $comentariosProductoDiv.html('<p>No hay comentarios aún.</p>');
             } else {
                 var html = '';
                 $.each(comentarios, function(_, c) {
                     html +=
                       '<div class="border rounded p-2 mb-2">' +
                         '<strong>'+ c.nombre_usuario +'</strong> - <span>'+ '⭐'.repeat(c.calificacion) +'</span>' +
                         '<p>'+ c.comentario +'</p>' +
                       '</div>';
                 });
                 $comentariosProductoDiv.html(html);
             }
         })
         .fail(function() {
             $detalleProductoDiv.html('<p>Error al cargar el producto.</p>');
             $comentariosProductoDiv.empty();
         });
    }

    $formComentario.on('submit', function(e) {
        e.preventDefault();
        if (!productoActualId) {
            Swal.fire('Error', 'No hay producto seleccionado', 'error');
            return;
        }

        var texto      = $('#comentario-texto').val().trim();
        var calificacion = $('#comentario-calificacion').val();

        if (!texto || !calificacion) {
            Swal.fire('Error', 'Completa el comentario y la calificación', 'warning');
            return;
        }

        $.ajax({
            url: 'app/models/productos/agregar_comentario.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                producto_id: productoActualId,
                comentario:  texto,
                calificacion: calificacion
            })
        })
        .done(function(resp) {
            if (resp.status === 'success') {
                Swal.fire('Gracias', 'Comentario enviado y en espera de aprobación', 'success');
                $formComentario[0].reset();
                cargarDetalleProducto(productoActualId);
            } else {
                Swal.fire('Error', resp.message || 'No se pudo enviar el comentario', 'error');
            }
        })
        .fail(function() {
            Swal.fire('Error', 'Error en la conexión', 'error');
        });
    });

    function agregarAlCarrito(productoId) {
        $.post('app/models/productos/agregar_carrito.php', { producto_id: productoId })
         .done(function(resp) {
             if (resp.status === 'success') {
                 Swal.fire('Agregado', 'Producto añadido al carrito', 'success');
             } else {
                 Swal.fire('Error', resp.message || 'No se pudo añadir al carrito', 'error');
             }
         })
         .fail(function() {
             Swal.fire('Error', 'Error al agregar al carrito', 'error');
         });
    }

    // Filtros y botones
    $categoria
      .add($subcategoria)
      .add($marca)
      .on('change', cargarProductos);

    $buscador.on('input', cargarProductos);

    $limpiarBtn.on('click', function() {
        $categoria.val('');
        $subcategoria.val('');
        $marca.val('');
        $buscador.val('');
        cargarProductos();
    });

    // Carga inicial de selects y productos
    cargarSelect('filtro-categoria',    'app/models/productos/get_categorias.php');
    cargarSelect('filtro-subcategoria', 'app/models/productos/get_subcategorias.php');
    cargarSelect('filtro-marca',        'app/models/productos/get_marcas.php');
    cargarProductos();
});
