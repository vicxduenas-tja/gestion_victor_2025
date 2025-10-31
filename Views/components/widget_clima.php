<!-- Widget compacto de Clima para integrar en cualquier vista -->
<div class="card widget-clima-compacto">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="material-icons-outlined align-middle">wb_sunny</i>
            Clima en Tarija
        </h6>
        <a href="<?php echo BASE_URL; ?>clima/index" class="btn btn-sm btn-link">Ver más</a>
    </div>
    <div class="card-body">
        <div id="widgetClimaLoader" class="text-center p-3">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
        <div id="widgetClimaContent" style="display: none;">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img id="widgetClimaIcono" src="" alt="Clima" style="width: 60px;">
                </div>
                <div class="col">
                    <h2 class="mb-0"><span id="widgetClimaTemp">--</span>°C</h2>
                    <p class="text-capitalize mb-0" id="widgetClimaDesc">--</p>
                </div>
            </div>
            <div class="row mt-3 small">
                <div class="col-4 text-center">
                    <i class="material-icons-outlined" style="font-size: 18px;">thermostat</i>
                    <div><span id="widgetClimaMax">--</span>°</div>
                    <small class="text-muted">Máx</small>
                </div>
                <div class="col-4 text-center">
                    <i class="material-icons-outlined" style="font-size: 18px;">water_drop</i>
                    <div><span id="widgetClimaHum">--</span>%</div>
                    <small class="text-muted">Humedad</small>
                </div>
                <div class="col-4 text-center">
                    <i class="material-icons-outlined" style="font-size: 18px;">air</i>
                    <div><span id="widgetClimaViento">--</span></div>
                    <small class="text-muted">km/h</small>
                </div>
            </div>
        </div>
        <div id="widgetClimaError" class="alert alert-warning small p-2" style="display: none;">
            <i class="material-icons-outlined align-middle" style="font-size: 16px;">info</i>
            <small>Configure su API key de OpenWeatherMap en Models/ClimaModel.php</small>
        </div>
    </div>
</div>

<script>
// Cargar clima en el widget
(function() {
    cargarWidgetClima();
    // Actualizar cada 15 minutos
    setInterval(cargarWidgetClima, 900000);
})();

function cargarWidgetClima() {
    fetch('<?php echo BASE_URL; ?>clima/obtenerClima')
        .then(response => response.json())
        .then(data => {
            if (data.tipo === 'success') {
                mostrarWidgetClima(data.data);
            } else {
                mostrarWidgetClimaError();
            }
        })
        .catch(error => {
            mostrarWidgetClimaError();
            console.error('Error:', error);
        });
}

function mostrarWidgetClima(clima) {
    document.getElementById('widgetClimaLoader').style.display = 'none';
    document.getElementById('widgetClimaContent').style.display = 'block';
    document.getElementById('widgetClimaError').style.display = 'none';

    document.getElementById('widgetClimaTemp').textContent = clima.temperatura;
    document.getElementById('widgetClimaDesc').textContent = clima.descripcion;
    document.getElementById('widgetClimaMax').textContent = clima.temp_max;
    document.getElementById('widgetClimaHum').textContent = clima.humedad;
    document.getElementById('widgetClimaViento').textContent = clima.viento;

    const iconUrl = `https://openweathermap.org/img/wn/${clima.icono}@2x.png`;
    document.getElementById('widgetClimaIcono').src = iconUrl;
}

function mostrarWidgetClimaError() {
    document.getElementById('widgetClimaLoader').style.display = 'none';
    document.getElementById('widgetClimaContent').style.display = 'none';
    document.getElementById('widgetClimaError').style.display = 'block';
}
</script>

<style>
.widget-clima-compacto {
    min-height: 180px;
}

.widget-clima-compacto .material-icons-outlined {
    vertical-align: middle;
}
</style>
