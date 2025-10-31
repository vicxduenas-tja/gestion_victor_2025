let tblReporteDetallado;

document.addEventListener('DOMContentLoaded', function () {
    // Inicialización de DataTables
    tblReporteDetallado = $('#tblReporteDetallado').DataTable({
        ajax: {
            url: base_url + 'Reportes/generarReporteDetallado',
            type: 'POST',
            data: function (d) {
                // Envía los filtros en la petición inicial
                d.fecha_desde = document.getElementById('fecha_desde').value;
                d.fecha_hasta = document.getElementById('fecha_hasta').value;
                d.filtro_estado = document.getElementById('filtro_estado').value;
            },
            dataSrc: ''
        },
        columns: [
            { data: 'numero_documento' },
            { data: 'asunto' },
            { data: 'oficina_destino' },
            { data: 'fecha_recepcion' },
            {
                data: 'fecha_limite',
                render: function (data, type, row) {
                    return row.sin_limite == 1 ? 'N/A' : data;
                }
            },
            {
                data: 'fecha_completado',
                render: function (data) {
                    return data ? data : 'Pendiente';
                }
            },
            {
                data: 'estado',
                render: function (data) {
                    if (data == 'en_progreso') {
                        return '<span class="badge bg-warning text-dark">En Progreso</span>';
                    } else if (data == 'completado') {
                        return '<span class="badge bg-success">Completado</span>';
                    } else if (data == 'respondido_retraso') {
                        return '<span class="badge bg-danger">Retraso</span>';
                    } else if (data == 'conocimiento') {
                        return '<span class="badge bg-info">Conocimiento</span>';
                    }
                    return data;
                }
            }
        ],
        language: {
            url: base_url + 'Assets/js/es-ES.json'
        },
        order: [
            [3, 'desc']
        ]
    });

    // Evento para el formulario de filtros
    document.getElementById('frmFiltros').addEventListener('submit', function (e) {
        e.preventDefault();
        // Recarga DataTables con los nuevos parámetros del formulario
        tblReporteDetallado.ajax.reload(null, false);
    });
});