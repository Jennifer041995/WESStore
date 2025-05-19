// Controlador de la página de productos
$(document).ready(function() {
    const baseUrl1 = '/Listo/WESStore/app/models/public';
    const baseUrl2 = '/Listo/WESStore/app/models/productos';

    let products = [];
    let categories = [];
    let brands = [];

    // Carga categorías, marcas y productos en paralelo
    function loadData() {
        return $.when(
            $.getJSON(`${baseUrl1}/list_categorias.php`),
            $.getJSON(`${baseUrl1}/list_marcas.php`),
            $.getJSON(`${baseUrl2}/list_all_productos.php`)
        );
    }

     // Llena los <select> de filtros
     function populateFilters() {
        const catSel   = $('#filterCategory');
        const brandSel = $('#filterBrand');

        categories.forEach(c => {
            catSel.append(`<option value="${c.id}">${c.nombre_categoria}</option>`);
        });
        brands.forEach(b => {
            brandSel.append(`<option value="${b.marca_id}">${b.nombre_marca}</option>`);
        });
    }

    // Renderiza productos agrupados por categoría y aplica filtros
    function renderProducts(filterCat, filterBrand) {
        const container = $('#categoriesList');
        container.empty();
    
        // Agrupa por categoría
        const grouped = {};
        products.forEach(p => {
            if ((filterCat && p.categoria_id != filterCat) ||
                (filterBrand && p.marca_id != filterBrand)) return;
    
            if (!grouped[p.categoria_id]) {
                grouped[p.categoria_id] = {
                    id: p.categoria_id,
                    name: p.nombre_categoria,
                    items: []
                };
            }
            grouped[p.categoria_id].items.push(p);
        });
    
        // Genera HTML con categorías clickeables
        Object.values(grouped).forEach(group => {
            const safeId = 'cat-' + group.name.replace(/\W+/g,'');
            container.append(`
                <h3 class="mt-4 category-header" 
                    data-category-id="${group.id}" 
                    style="cursor: pointer; color: #007bff;">
                    ${group.name}
                </h3>
                <div class="row" id="${safeId}"></div>
            `);
            
            const row = container.find(`#${safeId}`);
            group.items.forEach(p => {
                row.append(`
                    <div class="col-md-3 mb-3">
                        <div class="card h-100">
                            <img src="${p.url_imagen}" class="card-img-top" alt="${p.nombre}">
                            <div class="card-body">
                                <h5 class="card-title">${p.nombre}</h5>
                                <p class="card-text">$${p.precio}</p>
                            </div>
                        </div>
                    </div>
                `);
            });
        });
    
        // Agrega evento de clic a los encabezados de categoría
        $('.category-header').on('click', function() {
            const categoryId = $(this).data('category-id');
            $('#filterCategory').val(categoryId).trigger('change');
        });
    }

    // Eventos de filtro y reset
    $('#filterCategory, #filterBrand').on('change', function() {
        const fc = $('#filterCategory').val();
        const fb = $('#filterBrand').val();
        renderProducts(fc, fb);
    });
    $('#resetFilters').on('click', function() {
        $('#filterCategory, #filterBrand').val('');
        renderProducts();
    });

    // Inicia todo
    loadData()
        .done((catsRes, brandsRes, prodsRes) => {
            categories = catsRes[0].data;
            brands     = brandsRes[0].data;
            products   = prodsRes[0].data;
            populateFilters();
            renderProducts();
        })
        .fail(() => {
            Swal.fire('Error', 'No se pudo cargar la información.', 'error');
        });
});
