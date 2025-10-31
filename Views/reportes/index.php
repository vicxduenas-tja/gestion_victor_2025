<?php include_once 'Views/template/header.php'; ?>

<div class="row">
    <div class="col-12">
        <h1 class="page-title text-primary"><i class="fas fa-chart-bar"></i> <?php echo $data['title']; ?></h1>
    </div>
</div>

<hr class="my-4" />
<div class="row g-4">
    <div class="col-md-6 col-lg-3">
        <div class="card bg-warning text-white shadow">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-hourglass-half fa-3x"></i>
                    </div>
                    <div class="col text-end">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">En Progreso</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $data['en_progreso']; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card bg-danger text-white shadow">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-3x"></i>
                    </div>
                    <div class="col text-end">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Vencidos</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $data['vencidos']; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card bg-success text-white shadow">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-3x"></i>
                    </div>
                    <div class="col text-end">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Completados (Mes)</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $data['completados_mes']; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card bg-info text-white shadow">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-bullhorn fa-3x"></i>
                    </div>
                    <div class="col text-end">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Conocimiento (Mes)</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $data['conocimiento_mes']; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr class="my-5" />
<h4 style="font-weight: bold; margin-bottom: 20px;"><i class="fas fa-search"></i> Reporte Detallado de Documentos</h4>

<div class="card shadow mb-4">
    <div class="card-header py-3 bg-light">
        <form id="frmFiltros" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="fecha_desde" class="form-label">Fecha Desde</label>
                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-3">
                <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-3">
                <label for="filtro_estado" class="form-label">Estado</label>
                <select id="filtro_estado" name="filtro_estado" class="form-select">
                    <option value="todos">Todos</option>
                    <option value="en_progreso">En Progreso</option>
                    <option value="completados_todos">Completados (A Tiempo y Retraso)</option>
                    <option value="completado">Completado (A Tiempo)</option>
                    <option value="respondido_retraso">Respondido con Retraso</option>
                    <option value="conocimiento">Para Conocimiento</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filtrar</button>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle" id="tblReporteDetallado" style="width: 100%;">
                <thead class="table-dark">
                    <tr>
                        <th width="10%">N° Registro</th>
                        <th width="35%">Asunto</th>
                        <th width="15%">Oficina Destino</th>
                        <th width="10%">F. Recepción</th>
                        <th width="10%">F. Límite</th>
                        <th width="10%">F. Completado</th>
                        <th width="10%">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once 'Views/template/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>Assets/js/modulos/reportes_general.js"></script>