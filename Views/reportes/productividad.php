<?php include_once 'Views/template/header.php'; ?>

<style>
    /* Estilos de impresión para forzar orientación horizontal */
    .report-container { padding: 40px; }
    @page { size: A4 landscape; margin: 1cm; }
    
    @media print {
        body>.app>.app-container>.app-header, body>.app>.app-sidebar,
        .btn-imprimir, .btn-volver, .card-header, .card-title, .alert-info { display: none !important; }
        .app-content, .content-wrapper, .card {
            width: 100% !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; float: none;
        }
        .table { font-size: 10px !important; margin-top: 20px; table-layout: fixed; overflow-x: hidden !important; overflow: visible !important; }
        .table td { font-size: 9px !important; }
        .report-header, .firmas { display: block !important; }
        .firmas { margin-top: 50px; page-break-inside: avoid; }
        h3 { text-align: center; margin-bottom: 20px; }
        thead { display: table-header-group; }
        
        /* Colores Azules para la segunda tabla */
        .table-blue thead { background-color: #0d6efd !important; color: white !important; }
        .table-blue tbody tr:nth-of-type(odd) { background-color: #f0f8ff !important; }
        .text-pc { color: #0d6efd !important; font-weight: bold !important; }
    }

    /* Estilos para pantalla (oculta firmas) */
    .firmas { display: none; }
    .table-blue thead { background-color: #0d6efd; color: white; }
    .table-blue tbody tr:nth-of-type(odd) { background-color: #f0f8ff; }
    .table-blue tbody tr:hover { background-color: #e0f0ff; }
    .text-pc { color: #0d6efd; font-weight: bold; }
</style>

<div class="container-fluid mt-4 report-container">
    <div class="card shadow-sm">
        <div class="card-header text-white bg-secondary">
            <h3 class="mb-0"><i class="fas fa-users-cog"></i> Reporte de Productividad por Usuario</h3>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-sm btn-light btn-imprimir" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir Reporte
                </button>
            </div>
        </div>

        <div class="card-body">
            
            <div class="report-header mb-5" style="display: none;">
                <div style="text-align: center; margin-bottom: 25px;">
                    <img src="<?php echo BASE_URL . 'Assets/images/favicon.ico'; ?>" style="height: 60px; margin-bottom: 10px;">
                    <h4 style="margin: 0; font-weight: 600;">DIRECCIÓN DEPARTAMENTAL FELCV</h4>
                    <h5 style="margin: 0; font-weight: 400;">REPORTE DE RELEVO DE CARGOS</h5>
                    <p style="margin: 5px 0 0 0; font-size: 12px;">Generado el: <?php echo date('d/m/Y H:i:s'); ?></p>
                </div>
                <h5 class="card-title mb-2">Detalle de Actividad y Archivos por Personal Activo</h5>
            </div>
            <h5 class="card-title mb-4 btn-volver">Detalle de Actividad y Archivos por Personal Activo</h5>

            <div class="alert alert-info btn-volver">
                <i class="fas fa-info-circle"></i>
                Este reporte muestra la cantidad de trabajo respondido por cada usuario y la cantidad de archivos que ha guardado en su carpeta fija "Documentos Respondidos".
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Usuario</th>
                            <th>Oficina</th>
                            <th class="text-center">Total Delegado (Oficina)</th>
                            <th class="text-center">Respondido por Usuario (Histórico)</th>
                            <th class="text-center">Archivos Guardados (Respuesta)</th>
                            <th class="text-center">Diferencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['productividad'])): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No se encontraron usuarios o datos de actividad.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['productividad'] as $usuario): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($usuario['nombre_usuario']); ?></strong>
                                        <small class="d-block text-muted"><?php echo htmlspecialchars($usuario['cargo']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($usuario['oficina_usuario']); ?></td>

                                    <td class="text-center">
                                        <span class="badge bg-primary"><?php echo $usuario['total_delegado_oficina']; ?></span>
                                    </td>

                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-success"
                                                onclick="mostrarDetalleRespuestas(<?php echo $usuario['id']; ?>, '<?php echo $usuario['nombre_usuario']; ?>')"
                                                <?php echo ($usuario['total_respondidos'] == 0) ? 'disabled' : ''; ?>>
                                                <?php echo $usuario['total_respondidos']; ?>
                                        </button>
                                    </td>

                                    <td class="text-center">
                                        <?php
                                        $respondidos_guardados = $usuario['archivos_respondidos_guardados'];
                                        $diferencia = $usuario['total_respondidos'] - $respondidos_guardados;
                                        $badge_class = ($diferencia > 0) ? 'bg-danger' : 'bg-secondary';
                                        ?>
                                        <span class="badge bg-info"><?php echo $respondidos_guardados; ?></span>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo $diferencia; ?>
                                        </span>
                                        <small class="d-block text-muted">(Faltan por guardar)</small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <hr class="my-5" />

            <h5 style="font-weight: bold; margin-bottom: 20px;">
                <i class="fas fa-list-ol"></i> Inventario de Documentación Activa para Relevo
            </h5>
            <div class="table-responsive">
                <table class="table table-bordered table-blue align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%" class="text-center">N°</th>
                            <th width="15%" class="text-center">N° Registro</th>
                            <th width="40%">Asunto y Descripción</th>
                            <th width="15%">Tipo de Documento</th>
                            <th width="10%" class="text-center">Fecha Límite</th>
                            <th width="15%" class="text-center">Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['inventario'])): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No hay documentos pendientes o de conocimiento para incluir en el inventario.</td>
                            </tr>
                        <?php else: ?>
                            <?php $contador = 1; ?>
                            <?php foreach ($data['inventario'] as $doc): ?>
                                <tr>
                                    <td class="text-center"><?php echo $contador++; ?>.</td>
                                    <td class="text-center"><?php echo htmlspecialchars($doc['numero_documento']); ?></td>
                                    <td><?php echo htmlspecialchars($doc['asunto']); ?></td>

                                    <td>
                                        <?php if ($doc['estado'] == 'conocimiento'): ?>
                                            <span class="text-pc">Para Conocimiento</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Tarea Delegada</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <?php echo ($doc['sin_limite'] == 1 || empty($doc['fecha_limite'])) ? 'SIN LÍMITE' : htmlspecialchars($doc['fecha_limite']); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo ($doc['estado'] == 'conocimiento') ? 'Archivar/Referencia' : 'PENDIENTE DE RESPUESTA'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <a href="<?php echo BASE_URL; ?>reportes" class="btn btn-secondary mt-3 btn-volver">
                <i class="fas fa-arrow-left"></i> Volver a Estadísticas
            </a>


            <div class="firmas mt-5 pt-3" style="text-align: center;">
                <h5 style="margin-bottom: 60px; font-size: 16px;">Acta de Conformidad y Relevo</h5>
                <div style="display: flex; justify-content: space-around; width: 100%;">
                    <div style="width: 40%;">
                        <p style="border-top: 1px solid black; padding-top: 5px; margin: 0; font-size: 14px; font-weight: 500;">Funcionario Saliente</p>
                        <p style="margin: 0; font-size: 12px; font-weight: bold;">(Firma y Sello)</p>
                    </div>
                    <div style="width: 40%;">
                        <p style="border-top: 1px solid black; padding-top: 5px; margin: 0; font-size: 14px; font-weight: 500;">Funcionario Entrante</p>
                        <p style="margin: 0; font-size: 12px; font-weight: bold;">(Firma y Sello)</p>
                    </div>
                </div>
            </div>
            </div>
    </div>
</div>

<?php include_once 'Views/template/footer.php'; ?>