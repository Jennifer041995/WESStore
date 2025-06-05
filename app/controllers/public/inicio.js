$(document).ready(function () {
<<<<<<< HEAD
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
=======
    cargarMarcas();
    cargarCategorias();
    cargarSubcategorias();
    cargarDestacados();
});

>>>>>>> 10c551a (Actualizacion)
function cargarMarcas() {
    $.ajax({
        url: 'app/models/public/list_marcas.php',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
<<<<<<< HEAD
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
=======
            console.log('[AJAX marcas] response:', response);
            let cont = $('#marcas-list');
            cont.empty();

            if (response.status === 'success') {
                let marcas = response.data;
                if (!Array.isArray(marcas) || marcas.length === 0) {
                    cont.append('<p class="text-center w-100">No hay marcas disponibles.</p>');
                    return;
                }
                marcas.forEach(m => {
                    const card = `
                        <div class="tech-brand-card">
                            <img src="${m.logo}" alt="${m.nombre_marca}" class="tech-brand-logo"
                                 onerror="this.onerror=null;this.src='img/default-logo.png';">
                            <p class="tech-brand-name">${m.nombre_marca}</p>
                        </div>`;
                    cont.append(card);
>>>>>>> 10c551a (Actualizacion)
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
<<<<<<< HEAD
                    gutter: 10,
                    loop: true,
                    autoplayTimeout: 2500
                });
            } else {
                console.warn('No se pudieron cargar las marcas');
=======
                    gutter: 15,
                    loop: true,
                    autoplayTimeout: 2500,
                    responsive: {
                        0: { items: 2 },
                        768: { items: 3 },
                        992: { items: 4 }
                    }
                });
            } else {
                cont.append(`<p class="text-center w-100">Error: ${response.message}</p>`);
>>>>>>> 10c551a (Actualizacion)
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar marcas:', error);
<<<<<<< HEAD
=======
            $('#marcas-list').html('<p class="text-center w-100">Error al cargar las marcas.</p>');
        }
    });
}

function cargarCategorias() {
    $.ajax({
        url: 'app/models/public/list_categorias.php',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            console.log('[AJAX categorías] response:', response);
            let cont = $('#categorias-list');
            cont.empty();

            if (response.status === 'success') {
                let cats = response.data;
                if (!Array.isArray(cats) || cats.length === 0) {
                    cont.append('<p class="text-center w-100">No hay categorías disponibles.</p>');
                    return;
                }
                cats.forEach(c => {
                    const card = `
                        <div class="tech-category-card">
                            <img src="${c.imagen}" alt="${c.nombre_categoria}"
                                 class="tech-category-img"
                                 onerror="this.onerror=null;this.src='img/default-cat.jpg';">
                            <h3 class="tech-category-title">${c.nombre_categoria}</h3>
                            <p class="tech-category-desc">${c.descripcion || 'Productos de alta tecnología'}</p>
                        </div>`;
                    cont.append(card);
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
            } else {
                cont.append(`<p class="text-center w-100">Error: ${response.message}</p>`);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar categorías:', error);
            $('#categorias-list').html('<p class="text-center w-100">Error al cargar las categorías.</p>');
        }
    });
}

function cargarSubcategorias() {
    $.ajax({
        url: 'app/models/public/list_subcategorias.php', // tu endpoint de subcategorías
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            console.log('[AJAX subcategorías] response:', response);
            let cont = $('#subcategorias-list');
            cont.empty();

            if (response.status === 'success') {
                let subcats = response.data;
                if (!Array.isArray(subcats) || subcats.length === 0) {
                    cont.append('<p class="text-center w-100">No hay subcategorías disponibles.</p>');
                    return;
                }
                subcats.forEach(sc => {
                    const card = `
                        <div class="tech-category-card">
                            <img src="${sc.imagen}" alt="${sc.nombre_subcategoria}"
                                 class="tech-category-img"
                                 onerror="this.onerror=null;this.src='img/default-subcat.jpg';">
                            <h3 class="tech-category-title">${sc.nombre_subcategoria}</h3>
                            <p class="tech-category-desc">Perteneciente a: ${sc.nombre_categoria}</p>
                        </div>`;
                    cont.append(card);
                });

                tns({
                    container: '#subcategorias-list',
                    items: 3,
                    slideBy: 1,
                    autoplay: true,
                    autoplayButtonOutput: false,
                    controls: false,
                    nav: false,
                    mouseDrag: true,
                    gutter: 15,
                    loop: true,
                    autoplayTimeout: 3500,
                    responsive: {
                        0: { items: 1 },
                        576: { items: 2 },
                        992: { items: 3 }
                    }
                });
            } else {
                cont.append(`<p class="text-center w-100">Error: ${response.message}</p>`);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar subcategorías:', error);
            $('#subcategorias-list').html('<p class="text-center w-100">Error al cargar las subcategorías.</p>');
        }
    });
}

function cargarDestacados() {
    $.ajax({
        url: 'app/models/public/list_destacados.php', // tu endpoint de productos destacados
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            console.log('[AJAX destacados] response:', response);
            let cont = $('#productos-destacados-list');
            cont.empty();

            if (response.status === 'success') {
                let prods = response.data;
                if (!Array.isArray(prods) || prods.length === 0) {
                    cont.append('<p class="text-center w-100">No hay productos destacados.</p>');
                    return;
                }
                prods.forEach(p => {
                    const card = `
                        <div class="tech-brand-card">
                            <img src="${p.imagen_principal}"
                                 alt="${p.nombre_producto}"
                                 class="tech-brand-logo"
                                 onerror="this.onerror=null;this.src='img/default-producto.png';">
                            <div class="pt-2">
                                <h6 class="font-weight-bold mb-1">${p.nombre_producto}</h6>
                                <p class="mb-1">${p.nombre_marca} / ${p.nombre_categoria}</p>
                                <p class="text-neon-cyber">$${p.precio}</p>
                            </div>
                        </div>`;
                    cont.append(card);
                });

                tns({
                    container: '#productos-destacados-list',
                    items: 4,
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
                        768: { items: 3 },
                        992: { items: 4 }
                    }
                });
            } else {
                cont.append(`<p class="text-center w-100">Error: ${response.message}</p>`);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar destacados:', error);
            $('#productos-destacados-list').html('<p class="text-center w-100">Error al cargar productos destacados.</p>');
>>>>>>> 10c551a (Actualizacion)
        }
    });
}
