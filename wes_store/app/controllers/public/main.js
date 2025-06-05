$(document).ready(function() {
  // Referencias a elementos
  const $main     = $('#main-content');
  const $modal    = $(`
    <div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="authModalLabel">Autenticación</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body"></div>
        </div>
      </div>
    </div>
  `);
  // Inyecta el modal en el body si no existe
  if (!$('#authModal').length) {
    $('body').append($modal);
  }
  const $modalBody  = $('#authModal .modal-body');
  const $modalTitle = $('#authModalLabel');

  // Función para cargar en el content principal
  function loadContent(viewPath) {
    $main.fadeOut(200, function() {
      $.ajax({
        url: viewPath,
        method: 'GET',
        dataType: 'html'
      })
      .done(function(html) {
        $main.html(html).fadeIn(200);
      })
      .fail(function() {
        Swal.fire('Error', 'No se pudo cargar la vista. Intenta de nuevo.', 'error');
      });
    });
  }

  // Función para cargar en el modal
  function loadModal(viewPath, title) {
    $modalBody.html(''); // limpia contenido anterior
    $modalTitle.text(title);
    $modalBody.load(viewPath, function(response, status) {
      if (status === 'error') {
        Swal.fire('Error', 'No se pudo cargar la vista.', 'error');
      } else {
        $('#authModal').modal('show');
      }
    });
  }

  // Capturar clics en enlaces con data-view
  $('body').on('click', 'a[data-view]', function(e) {
    e.preventDefault();
    const view = $(this).data('view');

    // Si es login o register, abrimos modal, si no, content normal
    if (view.endsWith('auth/login.html')) {
      loadModal(view, 'Iniciar Sesión');
    }
    else if (view.endsWith('auth/register.html')) {
      loadModal(view, 'Regístrate');
    }
    else if (view && view !== '#') {
      loadContent(view);
      // Manejo de clases activas
      $('a[data-view]').removeClass('active');
      $(this).addClass('active');
    }
  });

  // Intercambiar login ↔ register dentro del modal
  $('body').on('click', '[data-auth-view]', function(e) {
    e.preventDefault();
    const target = $(this).data('auth-view');
    if (target === 'login') {
      loadModal('app/views/auth/login.html', 'Iniciar Sesión');
    } else if (target === 'register') {
      loadModal('app/views/auth/register.html', 'Regístrate');
    }
  });

  // Carga inicial: home
  const initial = $('a[data-view="app/views/public/home.html"]').data('view');
  if (initial) loadContent(initial);
});
