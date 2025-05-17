// Controlador de Marcas en el index
$(document).ready(function() {
    const baseUrl = 'app/models/public';

    function fetchBrands() {
        $.ajax({
            url: `${baseUrl}/list_marcas.php`,
            method: 'GET',
            dataType: 'json',
        })
        .done(function(response) {
            if (response.success) {
                const container = $('#brands-section');
                container.empty().css({
                    'overflow': 'hidden',
                    'white-space': 'nowrap',
                    'position': 'relative',
                    'padding': '10px 0' // Espacio vertical
                });
                // Duplicamos las marcas para efecto infinito (3 copias para fluidez)
                const brands = [...response.data, ...response.data, ...response.data];
                // Contenedor interno
                const innerContainer = $('<div class="inner-brands"></div>').css({
                    'display': 'inline-block'
                });
                container.append(innerContainer);
                // Agregamos marcas al DOM
                brands.forEach(brand => {
                    innerContainer.append(`
                        <div class="brand-item" style="display: inline-block; width: 150px; margin: 0 15px; vertical-align: middle;">
                            <div class="card p-3 h-100" style="border: none; background:rgb(101, 231, 151); border-radius: 8px;">
                                <h6 class="m-0" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${brand.nombre_marca}</h6>
                            </div>
                        </div>
                    `);
                });
                // Animación infinita
                let animationId;
                let scrollSpeed = 1; // Pixeles por frame (ajustar velocidad)
                function animateBrands() {
                    const containerWidth = container.width();
                    const innerWidth = innerContainer.width();
                    // Reinicia posición cuando llega al final
                    if (innerContainer.position().left <= -innerWidth/3) {
                        innerContainer.css('left', '0');
                    }
                    innerContainer.css('left', `-=${scrollSpeed}px`);
                    animationId = requestAnimationFrame(animateBrands);
                }
                // Inicia animación
                animateBrands();
                // Control opcional: Pausa al hacer hover
                container.hover(
                    () => cancelAnimationFrame(animationId),
                    () => animationId = requestAnimationFrame(animateBrands)
                );
            } else {
                console.error('Error en respuesta:', response.message);
                Swal.fire('Error', 'No se pudieron cargar las marcas: ' + response.message, 'error');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('AJAX Error:', status, error, xhr.responseText);
            Swal.fire('Error', 'Error al cargar marcas. Revisa la consola.', 'error');
        });
    }

    fetchBrands();
});