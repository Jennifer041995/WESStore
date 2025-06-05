$(document).ready(function() {
    // Elementos principales
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

    // Validación de existencia de elementos
    if (!($categoria.length && $subcategoria.length && $marca.length &&
          $buscador.length && $limpiarBtn.length && $contenedorProductos.length &&
          $detalleProductoDiv.length && $comentariosProductoDiv.length && $formComentario.length)) {
        console.warn("Faltan elementos del DOM para inicializar productos.");
        return;
    }

    // Muestra lista de productos con datos extendidos
    function mostrarProductos(productos) {
        $contenedorProductos.empty();

        if (!$.isArray(productos)) {
            Swal.fire("Error", "Respuesta inválida del servidor al mostrar productos", "error");
            return;
        }
        if (!productos.length) {
            $contenedorProductos.html('<p>No se encontraron productos.</p>');
            return;
        }

        $.each(productos, function(_, p) {
            var etiqueta = p.destacado ? '<span class="badge bg-info me-1">Destacado</span>' : '';
            if (p.oferta) etiqueta += '<span class="badge bg-danger">Oferta</span>';

            var precioHtml = p.precio_anterior
                ? '<small class="text-muted"><del>$'+p.precio_anterior+'</del></small> $'+p.precio
                : '$'+p.precio;

            var stars = '';
            for (var i = 1; i <= 5; i++) stars += i <= p.valoracion_promedio ? '⭐' : '☆';

            var $card = $(
                '<div class="col-md-4 mb-4">' +
                  '<div class="card h-100">' +
                    '<img src="'+ (p.imagen_principal||'img/default.png') +'" class="card-img-top" alt="'+p.nombre+'">' +
                    '<div class="card-body d-flex flex-column">' +
                      etiqueta +
                      '<h5 class="card-title mt-2">'+p.nombre+'</h5>' +
                      '<p class="card-text flex-grow-1">'+p.descripcion_corta+'</p>' +
                      '<p>'+precioHtml+' <small>('+stars+')</small></p>' +
                      '<div class="input-group mb-2">' +
                        '<input type="number" min="1" max="'+p.stock+'" value="1" class="form-control form-control-sm cantidad-input">' +
                        '<span class="input-group-text">Stock: '+p.stock+'</span>' +
                      '</div>' +
                      '<div class="mt-auto d-flex justify-content-between">' +
                        '<button class="btn btn-primary btn-ver-detalle" data-id="'+p.id_producto+'">Ver</button>' +
                        '<button class="btn btn-success btn-add-carrito" data-id="'+p.id_producto+'">Añadir</button>' +
                      '</div>' +
                    '</div>' +
                  '</div>' +
                '</div>'
            );
            $contenedorProductos.append($card);
        });
    }

    // Carga productos desde backend
    function cargarProductos() {
        var params = {
            categoria:    $categoria.val(),
            subcategoria: $subcategoria.val(),
            marca:        $marca.val(),
            buscar:       $buscador.val()
        };
        $.getJSON('app/models/productos/productos.php', params)
         .done(mostrarProductos)
         .fail(function() {
             Swal.fire("Error", "No se pudieron cargar los productos", "error");
         });
    }

    // Carga selects para filtros
    function cargarSelect(selectId, url) {
        $.getJSON(url)
         .done(function(data) {
             var $sel = $('#'+selectId);
             if (!$sel.length) return;
             $sel.empty().append('<option value="">Todos</option>');
             if (!$.isArray(data)) {
                 Swal.fire("Error", "Error en los datos de " + selectId, "error");
                 return;
             }
             $.each(data, function(_, o) {
                 $sel.append('<option value="'+o.id+'">'+o.nombre+'</option>');
             });
         })
         .fail(function() {
             Swal.fire("Error", "No se pudo cargar " + selectId, "error");
         });
    }

    // Muestra detalle de un producto en modal
    function cargarDetalleProducto(id) {
        $detalleProductoDiv.html('Cargando...');
        $comentariosProductoDiv.html('Cargando...');
        $formComentario[0].reset();

        $.getJSON('app/models/productos/obtener_detalle_producto.php', { id: id })
         .done(function(res) {
             if (res.status !== 'success') {
                 $detalleProductoDiv.html('<p>Error al cargar el producto.</p>');
                 $comentariosProductoDiv.empty();
                 return;
             }
             var d = res.data;
             // Carrusel de imágenes
             var slides = d.imagenes.map(function(img,i){
                 return '<div class="carousel-item '+(i===0?'active':'')+'">'
                        +'<img src="'+img.imagen_url+'" class="d-block w-100">'
                        +'</div>';
             }).join('');
             var carrusel =
               '<div id="carouselProd" class="carousel slide mb-3" data-ride="carousel">'
               +'  <div class="carousel-inner">'+slides+'</div>'
               +'  <a class="carousel-control-prev" href="#carouselProd" role="button" data-slide="prev">'
               +'    <span class="carousel-control-prev-icon"></span>'
               +'  </a>'
               +'  <a class="carousel-control-next" href="#carouselProd" role="button" data-slide="next">'
               +'    <span class="carousel-control-next-icon"></span>'
               +'  </a>'
               +'</div>';

             // Atributos
             var tablaAttrs = '<table class="table table-sm">'
                            + d.atributos.map(function(a){
                                return '<tr><th>'+a.nombre+'</th><td>'+a.valor+'</td></tr>';
                              }).join('')
                            + '</table>';

             // Oferta y precio
             var panelOferta = '';
             if (d.oferta) {
                 panelOferta = '<div class="alert alert-warning">Descuento '+d.oferta.valor+
                               (d.oferta.tipo_descuento==='porcentaje'? '%':' USD')+'</div>';
             }
             var precioDetalle = d.oferta
                 ? '<small><del>$'+d.producto.precio_anterior+'</del></small> $'+d.producto.precio
                 : '$'+d.producto.precio;

             // Input cantidad en modal
             var qtyInput =
               '<div class="input-group mb-2">'
               +'  <input type="number" min="1" max="'+d.stock+'" value="1" id="modal-cantidad" class="form-control">'
               +'  <span class="input-group-text">Stock: '+d.stock+'</span>'
               +'</div>';

             // Render detalle
             $detalleProductoDiv.html(
               carrusel+
               '<h4>'+d.producto.nombre+'</h4>'+panelOferta+
               '<p>'+d.producto.descripcion_larga+'</p>'+tablaAttrs+
               '<p><strong>Precio: '+precioDetalle+'</strong></p>'+qtyInput+
               '<button id="btn-add-carrito-modal" class="btn btn-success">Añadir al carrito</button>'
             );

             // Botón añadir en modal
             $('#btn-add-carrito-modal').off('click').on('click', function(){
                 var cantidad = parseInt($('#modal-cantidad').val(),10) || 1;
                 $.ajax({
                     url: 'app/models/productos/agregar_carrito.php',
                     method: 'POST',
                     contentType: 'application/json',
                     data: JSON.stringify({ producto_id: id, cantidad: cantidad })
                 })
                 .done(function(r){
                     Swal.fire(r.status==='success'? '¡Agregado!' : 'Error', r.message, r.status);
                 });
             });

             // Comentarios
             if (!d.comentarios.length) {
                 $comentariosProductoDiv.html('<p>No hay comentarios aún.</p>');
             } else {
                 var htmlC = d.comentarios.map(function(c){
                     var estrellas = '⭐'.repeat(c.calificacion);
                     return '<div class="border rounded p-2 mb-2">'
                          + '<strong>'+c.nombre_usuario+'</strong> - <span>'+estrellas+'</span>'
                          + '<p>'+c.comentario+'</p>'
                          + '</div>';
                 }).join('');
                 $comentariosProductoDiv.html(htmlC);
             }
         })
         .fail(function() {
             $detalleProductoDiv.html('<p>Error al cargar el producto.</p>');
             $comentariosProductoDiv.empty();
         });
    }

    // Envío de comentarios
    $('#form-comentario').submit(function (e) {
        e.preventDefault();

        // Tomar el texto del textarea y la calificación del select, usando los IDs correctos:
        const texto       = $('#comentario-texto').val().trim();
        const calificacion = $('#comentario-calificacion').val();

        if (!texto || !calificacion) {
            Swal.fire({
                icon: 'warning',
                title: 'Faltan campos',
                text: 'Por favor, completa tanto el comentario como la calificación.'
            });
            return;
        }

        $.ajax({
            url: 'app/models/productos/agregar_comentario.php',
            method: 'POST',
            data: {
                producto_id: productoActualId,
                comentario: texto,
                calificacion: calificacion
            },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Comentario agregado',
                        text: 'Tu comentario se guardó con éxito',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    $('#form-comentario')[0].reset();
                    // Recarga TODO el detalle (incluyendo comentarios) una vez insertado:
                    cargarDetalleProducto(productoActualId);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message || 'No se pudo agregar el comentario.'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al conectar con el servidor.'
                });
            }
        });
    });

    // Delegación de eventos de botones después de cargar productos
    $contenedorProductos
      .on('click', '.btn-ver-detalle', function(){
          productoActualId = $(this).data('id');
          cargarDetalleProducto(productoActualId);
          $modalVistaPrevia.modal('show');
      })
      .on('click', '.btn-add-carrito', function(){
          var $card = $(this).closest('.card');
          var id    = $(this).data('id');
          var qty   = parseInt($card.find('.cantidad-input').val(),10) || 1;
          $.ajax({
              url: 'app/models/productos/agregar_carrito.php',
              method: 'POST',
              contentType: 'application/json',
              data: JSON.stringify({ producto_id: id, cantidad: qty })
          })
          .done(function(r){ Swal.fire(r.status==='success'?'¡Listo!':'Error', r.message, r.status); });
      });

    // Filtros y limpieza
    $categoria.add($subcategoria).add($marca).on('change', cargarProductos);
    $buscador.on('input', cargarProductos);
    $limpiarBtn.on('click', function(){
        $categoria.val(''); $subcategoria.val(''); $marca.val(''); $buscador.val('');
        cargarProductos();
    });

    // Carga inicial
    cargarSelect('filtro-categoria',    'app/models/productos/get_categorias.php');
    cargarSelect('filtro-subcategoria', 'app/models/productos/get_subcategorias.php');
    cargarSelect('filtro-marca',        'app/models/productos/get_marcas.php');
    cargarProductos();
});