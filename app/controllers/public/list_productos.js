// controlador que muestra productos en el index
$(document).ready(function() {
    const baseUrl = 'app/models/public';

    function fetchProducts() {
        $.ajax({
            url: `${baseUrl}/list_productos.php`,
            method: 'GET',
            dataType: 'json',
        })
        .done(function(response) {
            if (response.success) {
                const container = $('#featured-products');
                container.empty().css({
                    'overflow': 'hidden',
                    'white-space': 'nowrap' // Evita saltos de línea
                });
                // Duplicamos los productos para efecto infinito
                const products = response.data.concat(response.data);
                // Contenedor interno
                const innerContainer = $('<div class="inner-products"></div>').css({
                    'display': 'inline-block'
                });
                container.append(innerContainer);
                // Agregamos productos al DOM
                products.forEach(product => {
                    innerContainer.append(`
                        <div class="product-item" style="display: inline-block; width: 200px; margin: 0 10px;">
                            <div class="card h-100">
                                <img src="${product.url_imagen}" class="card-img-top" alt="${product.nombre}">
                                <div class="card-body">
                                    <h5 class="card-title">${product.nombre}</h5>
                                    <p class="card-text">$${product.precio}</p>
                                </div>
                            </div>
                        </div>
                    `);
                });
                // Animación infinita
                let animationId;
                function animateProducts() {
                    const containerWidth = container.width();
                    const innerWidth = innerContainer.width();
                    // Reinicia posición cuando llega al final
                    if (innerContainer.position().left <= -innerWidth / 2) {
                        innerContainer.css('left', '0');
                    }
                    // Mueve 1px por frame (ajusta velocidad)
                    innerContainer.css('left', '-=1px');
                    animationId = requestAnimationFrame(animateProducts);
                }
                // Inicia animación
                animateProducts();
                // Detiene animación al pasar el mouse (opcional)
                container.hover(
                    () => cancelAnimationFrame(animationId),
                    () => animationId = requestAnimationFrame(animateProducts)
                );
            } else {
                console.error('Error en respuesta:', response.message);
                Swal.fire('Error', 'No se pudieron cargar los productos: ' + response.message, 'error');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('AJAX Error:', status, error, xhr.responseText);
            Swal.fire('Error', 'Error al cargar productos. Revisa la consola.', 'error');
        });
    }

    fetchProducts();
});