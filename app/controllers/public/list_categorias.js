//Controlador de categorias en el index
$(document).ready(function() {
    const baseUrl = 'app/models/public';

    function fetchCategories() {
        $.ajax({
            url: `${baseUrl}/list_categorias.php`,
            method: 'GET',
            dataType: 'json',
        })
        .done(function(response) {
            if (response.success) {
                const container = $('#category-section');
                container.empty().css({
                    'overflow': 'hidden',
                    'white-space': 'nowrap',
                    'position': 'relative'
                });
        
                // Duplicamos las categorías para efecto infinito
                const categories = response.data.concat(response.data);
                
                // Contenedor interno
                const innerContainer = $('<div class="inner-categories"></div>').css({
                    'display': 'inline-block'
                });
                container.append(innerContainer);
        
                // Agregamos categorías al DOM
                categories.forEach(cat => {
                    innerContainer.append(`
                        <div class="category-item" style="display: inline-block; width: 200px; margin: 0 10px;">
                            <div class="card text-center p-3">
                                <h6 style="margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${cat.nombre_categoria}</h6>
                            </div>
                        </div>
                    `);
                });
                // Animación infinita
                let animationId;
                function animateCategories() {
                    const innerWidth = innerContainer.width();
                    
                    // Reinicia posición cuando llega al final
                    if (innerContainer.position().left <= -innerWidth / 2) {
                        innerContainer.css('left', '0');
                    }
                    // Mueve 1px por frame (ajusta velocidad con el valor)
                    innerContainer.css('left', '-=1px');
                    animationId = requestAnimationFrame(animateCategories);
                }
                // Inicia animación
                animateCategories();
                // Opcional: Pausa al pasar el mouse
                container.hover(
                    () => cancelAnimationFrame(animationId),
                    () => animationId = requestAnimationFrame(animateCategories)
                );
            } else {
                console.error('Error en respuesta:', response.message);
                Swal.fire('Error', 'No se pudieron cargar las categorías: ' + response.message, 'error');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('AJAX Error:', status, error, xhr.responseText);
            Swal.fire('Error', 'Error al cargar categorías. Revisa la consola.', 'error');
        });
    }

    fetchCategories();
});
