$(document).ready(function () {
    cargarCategorias();
    cargarMarcas();
});

// Cargar categorías en tarjetas con slider
function cargarCategorias() {
    $.ajax({
        url: 'app/models/public/list_categorias.php',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                const contenedor = $('#categorias-list');
                contenedor.empty();

                response.data.forEach(categoria => {
                    contenedor.append(`
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-tag fa-2x text-primary mb-2"></i>
                                    <h5 class="card-title">${categoria.nombre_categoria}</h5>
                                    <p class="card-text">${categoria.descripcion}</p>
                                </div>
                            </div>
                        </div>
                    `);
                });

                // Inicializar Tiny Slider
                tns({
                    container: '#categorias-list',
                    items: 3,
                    slideBy: 1,
                    autoplay: true,
                    autoplayButtonOutput: false,
                    controls: false,
                    nav: false,
                    mouseDrag: true,
                    gutter: 10,
                    loop: true,
                    autoplayTimeout: 3000
                });
            } else {
                console.warn('No se pudieron cargar las categorías');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar categorías:', error);
        }
    });
}

// Cargar marcas en tarjetas con slider
function cargarMarcas() {
    $.ajax({
        url: 'app/models/public/list_marcas.php',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                const contenedor = $('#marcas-list');
                contenedor.empty();

                response.data.forEach(marca => {
                    contenedor.append(`
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card h-100 text-center border-0">
                                <div class="card-body">
                                    <img src="media/img/marcas/${marca.logo}" alt="${marca.nombre_marca}" class="img-fluid" style="max-height: 60px;">
                                    <p class="mt-2">${marca.nombre_marca}</p>
                                </div>
                            </div>
                        </div>
                    `);
                });

                tns({
                    container: '#marcas-list',
                    items: 4,
                    slideBy: 1,
                    autoplay: true,
                    autoplayButtonOutput: false,
                    controls: false,
                    nav: false,
                    mouseDrag: true,
                    gutter: 10,
                    loop: true,
                    autoplayTimeout: 2500
                });
            } else {
                console.warn('No se pudieron cargar las marcas');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar marcas:', error);
        }
    });
}
