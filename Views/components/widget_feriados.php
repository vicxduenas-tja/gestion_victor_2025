<!-- Widget compacto de Feriados para integrar en cualquier vista -->
<div class="card widget-feriados-compacto">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="material-icons-outlined align-middle">event</i>
            Próximos Feriados
        </h6>
        <a href="<?php echo BASE_URL; ?>feriados/index" class="btn btn-sm btn-link">Ver todos</a>
    </div>
    <div class="card-body">
        <div id="widgetFeriadosLoader" class="text-center p-3">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
        <div id="widgetFeriadosContent" style="display: none;">
            <!-- Próximo feriado destacado -->
            <div class="proximo-feriado-destacado text-center mb-3 p-3" style="background-color: #f8f9fa; border-radius: 8px;">
                <div class="display-6 fw-bold text-primary" id="widgetDiasRestantes">--</div>
                <small class="text-muted">días hasta</small>
                <h6 class="mt-2 mb-1" id="widgetNombreFeriado">--</h6>
                <small class="text-muted" id="widgetFechaFeriado">--</small>
            </div>

            <!-- Lista de próximos feriados -->
            <div id="widgetListaFeriados" class="list-group list-group-flush small">
                <!-- Se llenará con JavaScript -->
            </div>
        </div>
        <div id="widgetFeriadosError" class="alert alert-warning small p-2" style="display: none;">
            <i class="material-icons-outlined align-middle" style="font-size: 16px;">info</i>
            <small>No se pudieron cargar los feriados</small>
        </div>
    </div>
</div>

<script>
// Cargar feriados en el widget
(function() {
    cargarWidgetFeriados();
    // Actualizar cada hora
    setInterval(cargarWidgetFeriados, 3600000);
})();

function cargarWidgetFeriados() {
    fetch('<?php echo BASE_URL; ?>feriados/obtenerFeriados')
        .then(response => response.json())
        .then(data => {
            if (data.tipo === 'success') {
                mostrarWidgetFeriados(data.data);
            } else {
                mostrarWidgetFeriadosError();
            }
        })
        .catch(error => {
            mostrarWidgetFeriadosError();
            console.error('Error:', error);
        });
}

function mostrarWidgetFeriados(feriados) {
    document.getElementById('widgetFeriadosLoader').style.display = 'none';
    document.getElementById('widgetFeriadosContent').style.display = 'block';
    document.getElementById('widgetFeriadosError').style.display = 'none';

    if (feriados.length === 0) {
        document.getElementById('widgetFeriadosContent').innerHTML =
            '<p class="text-muted text-center small">No hay próximos feriados</p>';
        return;
    }

    // Mostrar el próximo feriado destacado
    const proximoFeriado = feriados[0];
    document.getElementById('widgetDiasRestantes').textContent = proximoFeriado.dias_restantes;
    document.getElementById('widgetNombreFeriado').textContent = proximoFeriado.nombre;

    const fecha = new Date(proximoFeriado.fecha);
    const fechaFormateada = fecha.toLocaleDateString('es-BO', {
        weekday: 'long',
        day: 'numeric',
        month: 'long'
    });
    document.getElementById('widgetFechaFeriado').textContent = fechaFormateada;

    // Mostrar lista de próximos feriados (máximo 3 más)
    let html = '';
    const feriadosParaMostrar = feriados.slice(1, 4); // Los siguientes 3 feriados

    feriadosParaMostrar.forEach(feriado => {
        const fechaFer = new Date(feriado.fecha);
        const fechaCorta = fechaFer.toLocaleDateString('es-BO', {
            day: 'numeric',
            month: 'short'
        });

        html += `
            <div class="list-group-item px-0 py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">${feriado.nombre}</div>
                        <small class="text-muted">${fechaCorta}</small>
                    </div>
                    <span class="badge bg-primary rounded-pill">${feriado.dias_restantes}d</span>
                </div>
            </div>
        `;
    });

    document.getElementById('widgetListaFeriados').innerHTML = html;
}

function mostrarWidgetFeriadosError() {
    document.getElementById('widgetFeriadosLoader').style.display = 'none';
    document.getElementById('widgetFeriadosContent').style.display = 'none';
    document.getElementById('widgetFeriadosError').style.display = 'block';
}
</script>

<style>
.widget-feriados-compacto {
    min-height: 250px;
}

.widget-feriados-compacto .material-icons-outlined {
    vertical-align: middle;
}

.proximo-feriado-destacado {
    border-left: 4px solid #007bff;
}
</style>
