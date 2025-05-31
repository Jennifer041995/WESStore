class AdminPanel {
    constructor() {
        this.currentSection = 'usuarios';
        this.init();
    }

    init() {
        this.loadSection(this.currentSection);
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Cambio de sección
        $('.nav-link').click(e => {
            e.preventDefault();
            this.currentSection = $(e.target).closest('.nav-link').data('target');
            this.loadSection(this.currentSection);
        });

        // Botón agregar nuevo
        $('#btn-add-new').click(() => this.showModal());
    }

    async loadSection(section) {
        try {
            const response = await $.ajax({
                url: `app/models/admin/${section}.php?action=getAll`,
                type: 'GET'
            });
            
            this.renderTable(section, response.data);
            $('#section-title').text(section.charAt(0).toUpperCase() + section.slice(1));
        } catch (error) {
            Swal.fire('Error', 'Error al cargar datos', 'error');
        }
    }

    renderTable(section, data) {
        let tableHtml = `
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            ${this.getTableHeaders(section)}
                        </tr>
                    </thead>
                    <tbody>`;
        
        data.forEach(item => {
            tableHtml += `
                <tr>
                    ${this.getTableRow(section, item)}
                    <td>
                        <button class="btn btn-sm btn-warning edit-item" 
                                data-id="${item.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-item" 
                                data-id="${item.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
        });
        
        tableHtml += `</tbody></table></div>`;
        $('#admin-content').html(tableHtml);
        
        // Eventos para editar/eliminar
        $('.edit-item').click(e => this.showModal($(e.target).data('id')));
        $('.delete-item').click(e => this.deleteItem($(e.target).data('id')));
    }

    async showModal(id = null) {
        const modal = $('#adminModal');
        const isEdit = !!id;
        
        try {
            if (isEdit) {
                const response = await $.get(
                    `app/models/admin/${this.currentSection}.php?action=get&id=${id}`
                );
                this.fillForm(response.data);
            } else {
                this.fillForm({});
            }
            
            modal.find('.modal-title').text(
                `${isEdit ? 'Editar' : 'Nuevo'} ${this.currentSection}`
            );
            modal.modal('show');
        } catch (error) {
            Swal.fire('Error', 'Error al cargar datos', 'error');
        }
    }

    async deleteItem(id) {
        const { value: password } = await Swal.fire({
            title: 'Confirmar eliminación',
            input: 'password',
            inputLabel: 'Ingrese su contraseña de administrador',
            inputAttributes: { required: true }
        });
        
        if (password) {
            try {
                await $.ajax({
                    url: `app/models/admin/${this.currentSection}.php?action=delete`,
                    type: 'POST',
                    data: { id, password }
                });
                this.loadSection(this.currentSection);
                Swal.fire('Éxito', 'Registro eliminado', 'success');
            } catch (error) {
                Swal.fire('Error', error.responseJSON.error, 'error');
            }
        }
    }

    // Métodos auxiliares...
}

new AdminPanel();