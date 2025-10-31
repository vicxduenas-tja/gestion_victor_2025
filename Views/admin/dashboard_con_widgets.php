<?php include_once 'Views/template/header.php'; ?>

<div class="app-content">
    <?php include_once 'Views/components/menus.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <div class="page-description d-flex align-items-center">
                        <div class="page-description-content flex-grow-1">
                            <h1>Dashboard con Widgets</h1>
                            <p class="text-muted">Panel principal con información del clima y días feriados</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WIDGETS DE CLIMA Y FERIADOS -->
            <div class="row mt-3">
                <!-- Widget de Clima -->
                <div class="col-lg-6 col-md-12 mb-3">
                    <?php include_once 'Views/components/widget_clima.php'; ?>
                </div>

                <!-- Widget de Feriados -->
                <div class="col-lg-6 col-md-12 mb-3">
                    <?php include_once 'Views/components/widget_feriados.php'; ?>
                </div>
            </div>

            <!-- CONTENIDO ORIGINAL DEL SISTEMA -->
            <div class="section-description mt-4">
                <h3>Carpetas</h3>
            </div>
            <div class="row">
                <?php if(isset($data['carpetas']) && is_array($data['carpetas'])): ?>
                    <?php foreach ($data['carpetas'] as $carpeta) { ?>
                        <div class="col-md-4">
                            <div class="card file-manager-group">
                                <div class="card-body d-flex align-items-center">
                                    <i class="material-icons" style="color: #<?php echo $carpeta['color']; ?>;">folder</i>
                                    <div class="file-manager-group-info flex-fill">
                                        <a href="#" id="<?php echo $carpeta['id']; ?>" class="file-manager-group-title carpetas"><?php echo $carpeta['nombre']; ?></a>
                                        <span class="file-manager-group-about"><?php echo $carpeta['fecha']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            No hay carpetas disponibles. Esta es una página de ejemplo para mostrar los widgets.
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="section-description mt-4">
                <h3>Archivos Recientes</h3>
            </div>
            <div class="row">
                <?php if(isset($data['archivos']) && is_array($data['archivos'])): ?>
                    <?php foreach ($data['archivos'] as $archivo) { ?>
                        <div class="col-md-6">
                            <div class="card file-manager-recent-item">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="material-icons-outlined text-danger align-middle m-r-sm">description</i>
                                        <a href="#" class="file-manager-recent-item-title flex-fill"><?php echo $archivo['nombre']; ?></a>
                                        <span class="p-h-sm">167kb</span>
                                        <span class="p-h-sm text-muted">09.14.21</span>
                                        <a href="#" class="dropdown-toggle file-manager-recent-file-actions" id="file-manager-recent-<?php echo $archivo['id']?>" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="file-manager-recent-<?php echo $archivo['id']?>">
                                            <li><a class="dropdown-item compartir" href="#" id="<?php echo $archivo['id']?>">Compartir</a></li>
                                            <li><a class="dropdown-item" href="<?php echo BASE_URL . 'Assets/archivos/' . $archivo ['id_carpeta'] . '/' . $archivo['nombre']; ?>" download="<?php echo $archivo['nombre'] ?>">Download</a></li>
                                            <li><a class="dropdown-item eliminar" href="#" data-id="<?php echo $archivo['id']?>">Eliminar</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            No hay archivos recientes. Esta es una página de ejemplo para mostrar los widgets.
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- NOTA INFORMATIVA -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <i class="material-icons-outlined align-middle">info</i>
                            Información sobre los Widgets
                        </div>
                        <div class="card-body">
                            <h5>Widgets Implementados</h5>
                            <p>Esta página muestra un ejemplo de cómo integrar los widgets de Clima y Días Feriados en el dashboard.</p>

                            <h6>Para usar los widgets en otras páginas:</h6>
                            <ol>
                                <li>Incluye el widget de clima: <code>&lt;?php include_once 'Views/components/widget_clima.php'; ?&gt;</code></li>
                                <li>Incluye el widget de feriados: <code>&lt;?php include_once 'Views/components/widget_feriados.php'; ?&gt;</code></li>
                            </ol>

                            <h6>Configuración requerida:</h6>
                            <p>Para que el widget de clima funcione correctamente, debes obtener una API key gratuita de OpenWeatherMap y configurarla en <code>Models/ClimaModel.php</code></p>

                            <p class="mb-0">
                                <strong>Lee las instrucciones completas en:</strong>
                                <code>WIDGETS_APIs_INSTRUCCIONES.txt</code>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
include_once 'Views/components/modal.php';
include_once 'Views/template/footer.php';
?>
