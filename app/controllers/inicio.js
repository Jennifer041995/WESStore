$(document).ready(function () {
    cargarCategorias();
    cargarMarcas();
});

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
                        <div class="tech-category-card">
                            <img src="${categoria.imagen}"
                                alt="${categoria.nombre_categoria}"
                                class="tech-category-img"
                                onerror="this.src='https://via.placeholder.com/150';"
                            >
                            <h3 class="tech-category-title">${categoria.nombre_categoria}</h3>
                            <p class="tech-category-desc">
                                ${categoria.descripcion || 'Productos de alta tecnología'}
                            </p>
                        </div>
                    `);
                });

                tns({
                    container: '#categorias-list',
                    items: 3,
                    slideBy: 1,
                    autoplay: true,
                    autoplayButtonOutput: false,
                    controls: false,
                    nav: false,
                    mouseDrag: true,
                    gutter: 15,
                    loop: true,
                    autoplayTimeout: 3000,
                    responsive: {
                        0: { items: 1 },
                        576: { items: 2 },
                        992: { items: 3 }
                    }
                });
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar categorías:', error);
        }
    });
}

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
                        <div class="tech-brand-card">
                            <img src="${marca.logo}"
                                alt="${marca.nombre_marca}"
                                class="tech-brand-logo"
                                onerror="this.src='https://via.placeholder.com/100';"
                            >
                            <p class="tech-brand-name">${marca.nombre_marca}</p>
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
                    gutter: 15,
                    loop: true,
                    autoplayTimeout: 2500,
                    responsive: {
                        0: { items: 2 },
                        768: { items: 3 },
                        992: { items: 4 }
                    }
                });
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar marcas:', error);
        }
    });
}
