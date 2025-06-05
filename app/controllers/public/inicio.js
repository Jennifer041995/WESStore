$(document).ready(function () {
    cargarMarcas();
    cargarCategorias();
    cargarSubcategorias();
    cargarDestacados();
});

function cargarMarcas() {
    $.ajax({
        url: 'app/models/public/list_marcas.php',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
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
                                 onerror="this.onerror=null;this.src='media/img/logo.png';">
                            <p class="tech-brand-name">${m.nombre_marca}</p>
                        </div>`;
                    cont.append(card);
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
            } else {
                cont.append(`<p class="text-center w-100">Error: ${response.message}</p>`);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar marcas:', error);
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
          // Ahora usamos tech-brand-card/tech-brand-logo en lugar de tech-category-card
          const card = `
            <div class="tech-brand-card">
              <img
                src="${c.imagen}"
                alt="${c.nombre_categoria}"
                class="tech-brand-logo"
                onerror="this.onerror=null;this.src='media/img/logo.png';">
              <div class="pt-2">
                <h6 class="tech-brand-name mb-1">${c.nombre_categoria}</h6>
                <p class="tech-category-desc">${c.descripcion || 'Productos de alta tecnología'}</p>
              </div>
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
    url: 'app/models/public/list_subcategorias.php',
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
            <div class="tech-brand-card">
              <img
                src="${sc.imagen}"
                alt="${sc.nombre_subcategoria}"
                class="tech-brand-logo"
                onerror="this.onerror=null;this.src='media/img/logo.png';">
              <div class="pt-2">
                <h6 class="tech-brand-name mb-1">${sc.nombre_subcategoria}</h6>
                <p class="tech-category-desc">Perteneciente a: ${sc.nombre_categoria}</p>
              </div>
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
        url: 'app/models/public/list_destacados.php',
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
                                 onerror="this.onerror=null;this.src='media/img/logo.png';">
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
        }
    });
}
