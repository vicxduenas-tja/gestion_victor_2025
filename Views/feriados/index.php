<?php include_once 'Views/template/header.php'; ?>

<div class="app-content">
    <?php include_once 'Views/components/menus.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <div class="page-description d-flex align-items-center">
                        <div class="page-description-content flex-grow-1">
                            <h1>Días Feriados en Bolivia</h1>
                        </div>
                        <div class="page-description-actions">
                            <select id="selectYear" class="form-select" style="width: auto;">
                                <option value="2024">2024</option>
                                <option value="2025" selected>2025</option>
                                <option value="2026">2026</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <!-- Próximos feriados -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Próximos Feriados</h5>
                        </div>
                        <div class="card-body">
                            <div id="feriadosLoader" class="text-center p-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2">Obteniendo días feriados...</p>
                            </div>
                            <div id="feriadosProximos" style="display: none;">
                                <!-- Se llenará con JavaScript -->
                            </div>
                            <div id="feriadosError" class="alert alert-danger" style="display: none;">
                                <i class="material-icons-outlined align-middle">error_outline</i>
                                <span id="feriadosErrorMsg"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Todos los feriados -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title">Todos los Feriados <span id="yearDisplay"></span></h5>
                        </div>
                        <div class="card-body">
                            <div id="feriadosTodos" class="table-responsive">
                                <!-- Se llenará con JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <!-- Información -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="material-icons-outlined align-middle">info</i>
                                Información
                            </h5>
                            <p class="small text-muted">
                                Los días feriados mostrados son los días festivos oficiales de Bolivia.
                            </p>
                            <hr>
                            <h6>Tipos de feriados:</h6>
                            <div class="small">
                                <span class="badge bg-primary mb-1">Nacional</span>
                                <p class="text-muted ms-2">Feriados en todo el país</p>

                                <span class="badge bg-info mb-1">Regional</span>
                                <p class="text-muted ms-2">Feriados en regiones específicas</p>
                            </div>
                            <hr>
                            <p class="small text-muted">
                                <i class="material-icons-outlined align-middle" style="font-size: 16px;">update</i>
                                Datos actualizados de la API Nager.Date
                            </p>
                        </div>
                    </div>

                    <!-- Contador próximo feriado -->
                    <div class="card mt-3">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Próximo Feriado</h6>
                            <div id="contadorFeriado" class="my-3">
                                <h1 class="display-4 fw-bold text-primary" id="diasRestantes">--</h1>
                                <p class="text-muted">días restantes</p>
                                <p class="fw-bold" id="nombreProximoFeriado">--</p>
                                <p class="small text-muted" id="fechaProximoFeriado">--</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Cargar feriados al iniciar la página
document.addEventListener('DOMContentLoaded', function() {
    cargarFeriados();

    // Evento cambio de año
    document.getElementById('selectYear').addEventListener('change', function() {
        cargarTodosFeriados(this.value);
    });
});

function cargarFeriados() {
    fetch('<?php echo BASE_URL; ?>feriados/obtenerFeriados')
        .then(response => response.json())
        .then(data => {
            if (data.tipo === 'success') {
                mostrarProximosFeriados(data.data);
            } else {
                mostrarError(data.mensaje);
            }
        })
        .catch(error => {
            mostrarError('Error al conectar con el servidor');
            console.error('Error:', error);
        });

    // Cargar todos los feriados del año actual
    const yearActual = document.getElementById('selectYear').value;
    cargarTodosFeriados(yearActual);
}

function cargarTodosFeriados(year) {
    fetch('<?php echo BASE_URL; ?>feriados/listarTodos?year=' + year)
        .then(response => response.json())
        .then(data => {
            if (data.tipo === 'success') {
                mostrarTodosFeriados(data.data, year);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function mostrarProximosFeriados(feriados) {
    document.getElementById('feriadosLoader').style.display = 'none';
    document.getElementById('feriadosProximos').style.display = 'block';
    document.getElementById('feriadosError').style.display = 'none';

    if (feriados.length === 0) {
        document.getElementById('feriadosProximos').innerHTML =
            '<p class="text-muted text-center">No hay próximos feriados este año</p>';
        return;
    }

    // Actualizar contador del próximo feriado
    if (feriados.length > 0) {
        document.getElementById('diasRestantes').textContent = feriados[0].dias_restantes;
        document.getElementById('nombreProximoFeriado').textContent = feriados[0].nombre;
        document.getElementById('fechaProximoFeriado').textContent =
            new Date(feriados[0].fecha).toLocaleDateString('es-BO', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
    }

    let html = '<div class="list-group">';
    feriados.forEach(feriado => {
        const fecha = new Date(feriado.fecha);
        const fechaFormateada = fecha.toLocaleDateString('es-BO', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        const badgeColor = feriado.es_global ? 'bg-primary' : 'bg-info';
        const tipoTexto = feriado.es_global ? 'Nacional' : 'Regional';

        html += `
            <div class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">${feriado.nombre}</h6>
                        <p class="mb-1 small text-capitalize">${fechaFormateada}</p>
                        <span class="badge ${badgeColor} small">${tipoTexto}</span>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-secondary rounded-pill">${feriado.dias_restantes} días</span>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';

    document.getElementById('feriadosProximos').innerHTML = html;
}

function mostrarTodosFeriados(feriados, year) {
    document.getElementById('yearDisplay').textContent = year;

    if (feriados.length === 0) {
        document.getElementById('feriadosTodos').innerHTML =
            '<p class="text-muted text-center">No hay feriados registrados para este año</p>';
        return;
    }

    let html = `
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody>
    `;

    feriados.forEach(feriado => {
        const fecha = new Date(feriado.date);
        const fechaFormateada = fecha.toLocaleDateString('es-BO', {
            weekday: 'short',
            month: 'short',
            day: 'numeric'
        });

        const badgeColor = feriado.global ? 'bg-primary' : 'bg-info';
        const tipoTexto = feriado.global ? 'Nacional' : 'Regional';

        html += `
            <tr>
                <td class="text-nowrap">${fechaFormateada}</td>
                <td>${feriado.localName}</td>
                <td><span class="badge ${badgeColor}">${tipoTexto}</span></td>
            </tr>
        `;
    });

    html += `
            </tbody>
        </table>
    `;

    document.getElementById('feriadosTodos').innerHTML = html;
}

function mostrarError(mensaje) {
    document.getElementById('feriadosLoader').style.display = 'none';
    document.getElementById('feriadosProximos').style.display = 'none';
    document.getElementById('feriadosError').style.display = 'block';
    document.getElementById('feriadosErrorMsg').textContent = mensaje;
}
</script>

<?php include_once 'Views/template/footer.php'; ?>
