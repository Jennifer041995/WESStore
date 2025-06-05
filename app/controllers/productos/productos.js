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

    // 1) Mostrar listado de productos
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

        $.each(productos, function(_, p) {
            var etiqueta = p.destacado ? '<span class="badge bg-info me-1">Destacado</span>' : '';
            if (p.oferta) etiqueta += '<span class="badge bg-danger">Oferta</span>';

            var precioHtml = p.precio_anterior
                ? '<small class="text-muted"><del>$'+p.precio_anterior+'</del></small> $'+p.precio
                : '$'+p.precio;

            var stars = '';
            for (var i = 1; i <= 5; i++) {
                stars += (i <= p.valoracion_promedio) ? '⭐' : '☆';
            }

            var $card = $(
                '<div class="col-md-4 mb-4">' +
                  '<div class="card h-100 product-card">' +
                    '<div class="product-img-container">' +
                    '  <img src="'+ (p.imagen_principal || 'media/img/default.png') +'" class="card-img-top product-img" alt="'+p.nombre+'">' +
                    '</div>' +
                    '<div class="card-body d-flex flex-column product-body">' +
                      etiqueta +
                      '<h5 class="card-title mt-2 product-title">'+p.nombre+'</h5>' +
                      '<p class="card-text flex-grow-1">'+p.descripcion_corta+'</p>' +
                      '<p><span class="product-price">$'+p.precio+'</span>' +
                      (p.precio_anterior ? '<span class="product-old-price">$'+p.precio_anterior+'</span>' : '') +
                      ' <small>('+stars+')</small></p>' +
                      '<div class="input-group mb-2">' +
                        '<input type="number" min="1" max="'+p.stock+'" value="1" class="form-control form-control-sm cantidad-input">' +
                        '<span class="input-group-text">Stock: '+p.stock+'</span>' +
                      '</div>' +
                      '<div class="mt-auto d-flex justify-content-between product-actions">' +
                        '<button class="btn btn-primary btn-ver-detalle" data-id="'+p.id_producto+'">Ver</button>' +
                        '<button class="btn btn-success btn-add-cart btn-add-carrito" data-id="'+p.id_producto+'">Añadir</button>' +
                      '</div>' +
                    '</div>' +
                  '</div>' +
                '</div>'
            );
            $contenedorProductos.append($card);
        });
    }

    // 2) Petición para traer productos según filtros
    function cargarProductos() {
        var params = {
            categoria:    $categoria.val(),
            subcategoria: $subcategoria.val(),
            marca:        $marca.val(),
            buscar:       $buscador.val()
        };
        $.getJSON('app/models/productos/productos.php', params)
         .done(mostrarProductos)
         .fail(function(jqXHR, txtStatus, err) {
             console.error("Error AJAX al cargar productos:", txtStatus, err);
             Swal.fire("Error", "No se pudieron cargar los productos", "error");
         });
    }

    // 3) Cargar “Categorías” (todas) → usa tu endpoint real
    function cargarSelectCategorias() {
        $.getJSON('app/models/productos/get_categorias.php')
         .done(function(lista) {
             $categoria.empty().append('<option value="">Todas las categorías</option>');
             lista.forEach(function(c) {
                 $categoria.append(
                   '<option value="'+c.id_categoria+'">'+c.nombre_categoria+'</option>'
                 );
             });
         })
         .fail(function() {
             console.error("Error AJAX al cargar categorías.");
             Swal.fire("Error", "No se pudieron cargar las categorías.", "error");
         });
    }

    // 4) Cargar “Subcategorías”, opcionalmente filtradas por categoría
    function cargarSelectSubcategorias(categoriaId) {
        var data = {};
        if (categoriaId) {
            data.categoria = categoriaId;
        }
        $.getJSON('app/models/productos/get_subcategorias.php', data)
         .done(function(lista) {
             $subcategoria.empty().append('<option value="">Todas las subcategorías</option>');
             lista.forEach(function(sc) {
                 // Agrego data-categoria para saber cuál es la categoría padre
                 $subcategoria.append(
                   '<option value="'+sc.id_subcategoria+'" data-categoria="'+sc.categoria_id+'">'+sc.nombre_subcategoria+'</option>'
                 );
             });
         })
         .fail(function() {
             console.error("Error AJAX al cargar subcategorías.");
             Swal.fire("Error", "No se pudieron cargar las subcategorías.", "error");
         });
    }

    // 5) Cargar “Marcas” (todas)
    function cargarSelectMarcas() {
        $.getJSON('app/models/productos/get_marcas.php')
         .done(function(lista) {
             $marca.empty().append('<option value="">Todas las marcas</option>');
             lista.forEach(function(m) {
                 $marca.append(
                   '<option value="'+m.id_marca+'">'+m.nombre_marca+'</option>'
                 );
             });
         })
         .fail(function() {
             console.error("Error AJAX al cargar marcas.");
             Swal.fire("Error", "No se pudieron cargar las marcas.", "error");
         });
    }

    // 6) Al cambiar categoría → recargar subcategorías filtradas + recargar productos
    $categoria.on('change', function() {
        var catId = $(this).val();
        cargarSelectSubcategorias(catId);
        cargarProductos();
    });

    // 7) Al cambiar subcategoría → ajustar categoría padre y recargar productos
    $subcategoria.on('change', function() {
        var subId = $(this).val();
        if (!subId) {
            // Si regresa a “Todas”, limpiamos la categoría (para permitir ver todos)
            cargarProductos();
            return;
        }
        // Leemos la categoría padre desde el atributo data-categoria
        var categoriaPadre = $('#filtro-subcategoria option:selected').data('categoria');
        if (categoriaPadre && $categoria.val() !== String(categoriaPadre)) {
            // Ajustamos el select de categoría al padre correspondiente
            $categoria.val(categoriaPadre);
            // Recargamos subcategorías para mostrar sólo las de esa categoría
            cargarSelectSubcategorias(categoriaPadre);
            // Reestablecemos la subcategoría seleccionada
            $subcategoria.val(subId);
        }
        cargarProductos();
    });

    // 8) Limpiar filtros
    $limpiarBtn.on('click', function() {
        $categoria.val('');
        cargarSelectSubcategorias(null);
        $subcategoria.val('');
        $marca.val('');
        $buscador.val('');
        cargarProductos();
    });

    // 9) Buscar al teclear
    $buscador.on('input', function() {
        cargarProductos();
    });

    // 10) Función para cargar detalle de producto en el modal
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
             var slides = d.imagenes.map(function(img, i) {
                 return '<div class="carousel-item '+(i===0?'active':'')+'">' +
                          '<img src="'+img.imagen_url+'" class="d-block w-100" alt="Imagen '+(i+1)+'">' +
                        '</div>';
             }).join('');
             var carrusel =
               '<div id="carouselProd" class="carousel slide mb-3" data-ride="carousel">' +
               '  <div class="carousel-inner">'+slides+'</div>' +
               '  <a class="carousel-control-prev" href="#carouselProd" role="button" data-slide="prev">' +
               '    <span class="carousel-control-prev-icon" aria-hidden="true"></span>' +
               '  </a>' +
               '  <a class="carousel-control-next" href="#carouselProd" role="button" data-slide="next">' +
               '    <span class="carousel-control-next-icon" aria-hidden="true"></span>' +
               '  </a>' +
               '</div>';

             // Tabla de atributos
             var tablaAttrs = '<table class="table table-sm">';
             d.atributos.forEach(function(a) {
                 tablaAttrs += '<tr><th>'+a.nombre+'</th><td>'+a.valor+'</td></tr>';
             });
             tablaAttrs += '</table>';

             // Panel de oferta / precio
             var panelOferta = '';
             if (d.oferta) {
                 panelOferta = '<div class="alert alert-warning">Descuento '+
                               d.oferta.valor +
                               (d.oferta.tipo_descuento==='porcentaje'? '%':' USD') +
                               '</div>';
             }
             var precioDetalle = d.oferta
                 ? '<small><del>$'+d.producto.precio_anterior+'</del></small> $'+d.producto.precio
                 : '$'+d.producto.precio;

             // Input cantidad en modal
             var qtyInput =
               '<div class="input-group mb-2">' +
               '  <input type="number" min="1" max="'+d.stock+'" value="1" id="modal-cantidad" class="form-control">' +
               '  <span class="input-group-text">Stock: '+d.stock+'</span>' +
               '</div>';

             // Renderizamos todo dentro del div #detalle-producto
             $detalleProductoDiv.html(
               carrusel +
               '<h4>'+d.producto.nombre+'</h4>' +
               panelOferta +
               '<p>'+d.producto.descripcion_larga+'</p>' +
               tablaAttrs +
               '<p><strong>Precio: '+precioDetalle+'</strong></p>' +
               qtyInput +
               '<button id="btn-add-carrito-modal" class="btn btn-success">Añadir al carrito</button>'
             );

             // Botón “Añadir al carrito” dentro del modal
             $('#btn-add-carrito-modal').off('click').on('click', function() {
                 var cantidad = parseInt($('#modal-cantidad').val(), 10) || 1;
                 $.ajax({
                     url: 'app/models/productos/agregar_carrito.php',
                     method: 'POST',
                     contentType: 'application/json',
                     data: JSON.stringify({ producto_id: id, cantidad: cantidad })
                 })
                 .done(function(resp) {
                     var icon = (resp.status === 'success') ? 'success' : 'error';
                     Swal.fire(resp.status === 'success' ? '¡Agregado!' : 'Error',
                               resp.message,
                               icon);
                 })
                 .fail(function() {
                     Swal.fire('Error', 'No se pudo añadir al carrito', 'error');
                 });
             });

             // Comentarios
             if (!Array.isArray(d.comentarios) || d.comentarios.length === 0) {
                 $comentariosProductoDiv.html('<p>No hay comentarios aún.</p>');
             } else {
                 var htmlC = '';
                 d.comentarios.forEach(function(c) {
                     var estrellas = '⭐'.repeat(c.calificacion);
                     htmlC += '<div class="border rounded p-2 mb-2">' +
                                '<strong>'+c.nombre_usuario+'</strong> - <span>'+estrellas+'</span>' +
                                '<p>'+c.comentario+'</p>' +
                              '</div>';
                 });
                 $comentariosProductoDiv.html(htmlC);
             }
         })
         .fail(function() {
             console.error("Error AJAX al obtener detalle de producto.");
             $detalleProductoDiv.html('<p>Error al cargar el producto.</p>');
             $comentariosProductoDiv.empty();
         });
    }

    // 11) Envío de comentarios desde el modal
    $formComentario.on('submit', function(e) {
        e.preventDefault();
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
            contentType: 'application/json',
            data: JSON.stringify({
                producto_id: productoActualId,
                comentario: texto,
                calificacion: calificacion
            }),
            dataType: 'json'
        })
        .done(function(res) {
            if (res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Comentario agregado',
                    text: 'Tu comentario se guardó con éxito',
                    timer: 1500,
                    showConfirmButton: false
                });
                $('#comentario-texto').val('');
                $('#comentario-calificacion').val('');
                // Recarga TODO el detalle (incluyendo comentarios)
                cargarDetalleProducto(productoActualId);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: res.message || 'No se pudo agregar el comentario.'
                });
            }
        })
        .fail(function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al conectar con el servidor.'
            });
        });
    });

    // 12) Delegación de eventos para los botones “Ver” y “Añadir al carrito”
    $contenedorProductos
      .on('click', '.btn-ver-detalle', function() {
          productoActualId = $(this).data('id');
          cargarDetalleProducto(productoActualId);
          $modalVistaPrevia.modal('show');
      })
      .on('click', '.btn-add-cart', function() {
          var $card = $(this).closest('.card');
          var id    = $(this).data('id');
          var qty   = parseInt($card.find('.cantidad-input').val(), 10) || 1;
          $.ajax({
              url: 'app/models/productos/agregar_carrito.php',
              method: 'POST',
              contentType: 'application/json',
              data: JSON.stringify({ producto_id: id, cantidad: qty })
          })
          .done(function(r) {
              var icon = (r.status === 'success') ? 'success' : 'error';
              Swal.fire(r.status === 'success' ? '¡Listo!' : 'Error', r.message, icon);
          })
          .fail(function() {
              Swal.fire('Error', 'Error al agregar al carrito', 'error');
          });
      });

    // 13) Vincular marca → recargar productos
    $marca.on('change', cargarProductos);

    // 14) Inicialización al cargar la página
    cargarSelectCategorias();
    cargarSelectSubcategorias(null); // trae TODAS las subcategorías la primera vez
    cargarSelectMarcas();
    cargarProductos();
});
