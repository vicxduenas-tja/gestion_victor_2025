<?php include_once 'Views/template/header.php'; ?>

<div class="app-content">
    <?php include_once 'Views/components/menus.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <div class="page-description d-flex align-items-center">
                        <div class="page-description-content flex-grow-1">
                            <h1>Clima en Tarija</h1>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <!-- Card principal del clima -->
                    <div class="card widget-clima">
                        <div class="card-body">
                            <div id="climaLoader" class="text-center p-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2">Obteniendo información del clima...</p>
                            </div>
                            <div id="climaContent" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="clima-principal text-center">
                                            <img id="climaIcono" src="" alt="Clima" style="width: 100px;">
                                            <h1 id="climaTemp" class="display-1 fw-bold">--°</h1>
                                            <p id="climaDescripcion" class="text-capitalize fs-4"></p>
                                            <p id="climaCiudad" class="text-muted"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="clima-detalles">
                                            <h5 class="mb-3">Detalles del clima</h5>
                                            <div class="row mb-2">
                                                <div class="col-6">
                                                    <i class="material-icons-outlined align-middle">thermostat</i>
                                                    <span>Sensación térmica:</span>
                                                </div>
                                                <div class="col-6 text-end fw-bold">
                                                    <span id="climaSensacion">--</span>°C
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6">
                                                    <i class="material-icons-outlined align-middle">arrow_upward</i>
                                                    <span>Temp. Máxima:</span>
                                                </div>
                                                <div class="col-6 text-end fw-bold text-danger">
                                                    <span id="climaTempMax">--</span>°C
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6">
                                                    <i class="material-icons-outlined align-middle">arrow_downward</i>
                                                    <span>Temp. Mínima:</span>
                                                </div>
                                                <div class="col-6 text-end fw-bold text-primary">
                                                    <span id="climaTempMin">--</span>°C
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6">
                                                    <i class="material-icons-outlined align-middle">water_drop</i>
                                                    <span>Humedad:</span>
                                                </div>
                                                <div class="col-6 text-end fw-bold">
                                                    <span id="climaHumedad">--</span>%
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6">
                                                    <i class="material-icons-outlined align-middle">air</i>
                                                    <span>Viento:</span>
                                                </div>
                                                <div class="col-6 text-end fw-bold">
                                                    <span id="climaViento">--</span> km/h
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6">
                                                    <i class="material-icons-outlined align-middle">speed</i>
                                                    <span>Presión:</span>
                                                </div>
                                                <div class="col-6 text-end fw-bold">
                                                    <span id="climaPresion">--</span> hPa
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-muted text-center mt-3 small">
                                    <i class="material-icons-outlined" style="font-size: 14px;">info</i>
                                    Última actualización: <span id="climaActualizacion"></span>
                                </div>
                            </div>
                            <div id="climaError" class="alert alert-danger" style="display: none;">
                                <i class="material-icons-outlined align-middle">error_outline</i>
                                <span id="climaErrorMsg"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <!-- Información adicional -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Información</h5>
                            <p class="small text-muted">
                                Los datos del clima son proporcionados por OpenWeatherMap y se actualizan automáticamente.
                            </p>
                            <hr>
                            <h6>Ubicación</h6>
                            <p class="small">
                                <i class="material-icons-outlined align-middle">location_on</i>
                                Tarija, Bolivia<br>
                                <small class="text-muted">Lat: -21.5355, Lon: -64.7295</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Cargar clima al iniciar la página
document.addEventListener('DOMContentLoaded', function() {
    cargarClima();
    // Actualizar cada 10 minutos
    setInterval(cargarClima, 600000);
});

function cargarClima() {
    fetch('<?php echo BASE_URL; ?>clima/obtenerClima')
        .then(response => response.json())
        .then(data => {
            if (data.tipo === 'success') {
                mostrarClima(data.data);
            } else {
                mostrarError(data.mensaje);
            }
        })
        .catch(error => {
            mostrarError('Error al conectar con el servidor');
            console.error('Error:', error);
        });
}

function mostrarClima(clima) {
    document.getElementById('climaLoader').style.display = 'none';
    document.getElementById('climaContent').style.display = 'block';
    document.getElementById('climaError').style.display = 'none';

    // Actualizar datos
    document.getElementById('climaTemp').textContent = clima.temperatura + '°';
    document.getElementById('climaDescripcion').textContent = clima.descripcion;
    document.getElementById('climaCiudad').textContent = clima.ciudad + ', ' + clima.pais;
    document.getElementById('climaSensacion').textContent = clima.sensacion;
    document.getElementById('climaTempMax').textContent = clima.temp_max;
    document.getElementById('climaTempMin').textContent = clima.temp_min;
    document.getElementById('climaHumedad').textContent = clima.humedad;
    document.getElementById('climaViento').textContent = clima.viento;
    document.getElementById('climaPresion').textContent = clima.presion;

    // Icono del clima
    const iconUrl = `https://openweathermap.org/img/wn/${clima.icono}@2x.png`;
    document.getElementById('climaIcono').src = iconUrl;

    // Hora de actualización
    const ahora = new Date();
    document.getElementById('climaActualizacion').textContent = ahora.toLocaleTimeString('es-BO');
}

function mostrarError(mensaje) {
    document.getElementById('climaLoader').style.display = 'none';
    document.getElementById('climaContent').style.display = 'none';
    document.getElementById('climaError').style.display = 'block';
    document.getElementById('climaErrorMsg').textContent = mensaje;
}
</script>

<style>
.widget-clima {
    min-height: 400px;
}

.clima-principal {
    padding: 20px;
}

.clima-detalles {
    padding: 20px;
}

.clima-detalles .row {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.clima-detalles .row:last-child {
    border-bottom: none;
}

.material-icons-outlined {
    vertical-align: middle;
    font-size: 20px;
    margin-right: 5px;
}
</style>

<?php include_once 'Views/template/footer.php'; ?>
