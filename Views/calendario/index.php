<?php include_once 'Views/template/header.php'; ?>

<!-- Dashboard de Estadísticas -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stat-card bg-warning-subtle p-3 rounded">
                            <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                            <h3 class="mb-0"><?php echo $data['stats']['pendientes']; ?></h3>
                            <p class="text-muted mb-0 small">Pendientes</p>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stat-card bg-info-subtle p-3 rounded">
                            <i class="fas fa-spinner fa-2x text-info mb-2"></i>
                            <h3 class="mb-0"><?php echo $data['stats']['en_proceso']; ?></h3>
                            <p class="text-muted mb-0 small">En Proceso</p>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stat-card bg-success-subtle p-3 rounded">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h3 class="mb-0"><?php echo $data['stats']['completados']; ?></h3>
                            <p class="text-muted mb-0 small">Completados</p>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stat-card bg-secondary-subtle p-3 rounded">
                            <i class="fas fa-exclamation-triangle fa-2x text-secondary mb-2"></i>
                            <h3 class="mb-0"><?php echo $data['stats']['vencidos']; ?></h3>
                            <p class="text-muted mb-0 small">Vencidos</p>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stat-card bg-danger-subtle p-3 rounded">
                            <i class="fas fa-fire fa-2x text-danger mb-2"></i>
                            <h3 class="mb-0"><?php echo $data['stats']['urgentes']; ?></h3>
                            <p class="text-muted mb-0 small">Urgentes Hoy</p>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stat-card bg-primary-subtle p-3 rounded">
                            <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                            <h3 class="mb-0"><?php echo $data['stats']['proximos_vencer']; ?></h3>
                            <p class="text-muted mb-0 small">Próximos a Vencer</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Barra de Herramientas del Calendario -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-2">
                        <button class="btn btn-primary" id="btnNuevoDocumento">
                            <i class="fas fa-plus"></i> Nuevo Documento
                        </button>
                        <button class="btn btn-secondary" id="btnHoy">
                            <i class="fas fa-calendar-day"></i> Hoy
                        </button>
                        <button class="btn btn-info" id="btnReportes">
                            <i class="fas fa-chart-bar"></i> Reportes
                        </button>
                    </div>
                    <div class="btn-group mb-2" role="group">
                        <button type="button" class="btn btn-outline-secondary" id="btnVistaMonth">
                            <i class="fas fa-calendar"></i> Mes
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnVistaWeek">
                            <i class="fas fa-calendar-week"></i> Semana
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnVistaList">
                            <i class="fas fa-list"></i> Lista
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Calendario Principal -->
<div class="row">
    <div class="col-md-9">
        <div class="card shadow-sm">
            <div class="card-body">
                <div id="calendario"></div>
            </div>
        </div>
    </div>
    
    <!-- Panel Lateral -->
    <div class="col-md-3">
        <!-- Documentos del Día -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-calendar-day"></i> Documentos de Hoy
            </div>
            <div class="card-body p-2" style="max-height: 300px; overflow-y: auto;" id="documentosHoy">
                <p class="text-center text-muted">Cargando...</p>
            </div>
        </div>
        
        <!-- Notificaciones -->
        <div class="card shadow-sm">
            <div class="card-header bg-warning">
                <i class="fas fa-bell"></i> Notificaciones
                <span class="badge bg-danger float-end"><?php echo $data['total_notificaciones']; ?></span>
            </div>
            <div class="card-body p-2" style="max-height: 300px; overflow-y: auto;" id="notificaciones">
                <?php if (empty($data['notificaciones'])): ?>
                    <p class="text-center text-muted">No hay notificaciones</p>
                <?php else: ?>
                    <?php foreach ($data['notificaciones'] as $notif): ?>
                        <div class="notificacion-item p-2 border-bottom" data-id="<?php echo $notif['id']; ?>">
                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($notif['fecha'])); ?></small>
                            <p class="mb-0 small"><?php echo $notif['mensaje']; ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo/Editar Documento -->
<div class="modal fade" id="modalDocumento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalDocumentoTitle">Nuevo Documento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formDocumento">
                    <input type="hidden" id="doc_id" name="doc_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número de Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="numero_documento" name="numero_documento" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                <option value="">Seleccionar</option>
                                <option value="MEMORANDUM">Memorándum</option>
                                <option value="OFICIO">Oficio</option>
                                <option value="INFORME">Informe</option>
                                <option value="CARTA">Carta</option>
                                <option value="RESOLUCION">Resolución</option>
                                <option value="DECRETO">Decreto</option>
                                <option value="CIRCULAR">Circular</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Asunto <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="asunto" name="asunto" rows="2" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Remitente <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="remitente" name="remitente" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Destinatario</label>
                            <input type="text" class="form-control" id="destinatario" name="destinatario">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Recepción <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="fecha_recepcion" name="fecha_recepcion" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Límite <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="fecha_limite" name="fecha_limite" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Prioridad <span class="text-danger">*</span></label>
                            <select class="form-select" id="prioridad" name="prioridad" required>
                                <option value="BAJA">Baja</option>
                                <option value="MEDIA" selected>Media</option>
                                <option value="ALTA">Alta</option>
                                <option value="URGENTE">Urgente</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Carpeta <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_carpeta" name="id_carpeta" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($data['carpetas'] as $carpeta): ?>
                                    <option value="<?php echo $carpeta['id']; ?>"><?php echo $carpeta['nombre']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Asignar a <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_usuario_asignado" name="id_usuario_asignado" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($data['usuarios'] as $usuario): ?>
                                    <option value="<?php echo $usuario['id']; ?>"><?php echo $usuario['nombre_completo']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarDocumento">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Documento -->
<div class="modal fade" id="modalVerDocumento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detalles del Documento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detallesDocumento"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" id="btnDerivar">
                    <i class="fas fa-share"></i> Derivar
                </button>
                <button type="button" class="btn btn-success" id="btnCompletarDoc">
                    <i class="fas fa-check"></i> Completar
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Derivar Documento -->
<div class="modal fade" id="modalDerivar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Derivar Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formDerivar">
                    <input type="hidden" id="derivar_doc_id" name="id_documento">
                    
                    <div class="mb-3">
                        <label class="form-label">Derivar a <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_usuario_destino" name="id_usuario_destino" required>
                            <option value="">Seleccionar usuario</option>
                            <?php foreach ($data['usuarios'] as $usuario): ?>
                                <option value="<?php echo $usuario['id']; ?>"><?php echo $usuario['nombre_completo']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Comentario</label>
                        <textarea class="form-control" name="comentario" rows="3" placeholder="Instrucciones o comentarios adicionales"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnConfirmarDerivar">
                    <i class="fas fa-share"></i> Derivar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    transition: transform 0.2s;
    cursor: pointer;
}
.stat-card:hover {
    transform: translateY(-5px);
}
.notificacion-item {
    cursor: pointer;
    transition: background-color 0.2s;
}
.notificacion-item:hover {
    background-color: #f8f9fa;
}
.fc-event {
    cursor: pointer;
}
</style>

<?php include_once 'Views/template/footer.php'; ?>