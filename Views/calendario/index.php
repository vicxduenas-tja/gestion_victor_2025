<?php include_once 'Views/template/header.php'; ?>

<!-- CSS de FullCalendar -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<!-- CSS de Animate.css para animaciones -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<style>
    /* Estilos mínimos para el stepper de Bootstrap */
    .stepper-item {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
    }

    .stepper-item::before {
        position: absolute;
        content: "";
        border-bottom: 2px solid #dee2e6;
        width: 100%;
        top: 20px;
        left: -50%;
        z-index: 0;
    }

    .stepper-item::after {
        position: absolute;
        content: "";
        border-bottom: 2px solid #dee2e6;
        width: 100%;
        top: 20px;
        left: 50%;
        z-index: 0;
    }

    .stepper-item .step-counter {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #dee2e6;
        margin-bottom: 6px;
    }

    .stepper-item.active .step-counter {
        background-color: #0d6efd;
        color: white;
        font-weight: bold;
    }

    .stepper-item.completed .step-counter {
        background-color: #198754;
        color: white;
    }

    .stepper-item:first-child::before {
        content: none;
    }

    .stepper-item:last-child::after {
        content: none;
    }
</style>





<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-calendar"></i> Calendario de Documentos</h4>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="buscarDocumento" placeholder="Buscar por número o asunto..." onkeyup="buscarDocumento()">
                                    <button class="btn btn-outline-secondary" type="button" onclick="limpiarBusqueda()">Limpiar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Botón para crear documento -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevoDoc">
                            <i class="fas fa-plus"></i> Nuevo Documento
                        </button>
                    </div>
                    <!-- Filtros -->
                    <div class="mb-3">
                        <strong>Filtrar por prioridad:</strong>
                        <div class="btn-group ms-2" role="group">
                            <button type="button" class="btn btn-sm btn-outline-secondary active" onclick="filtrarPrioridad('todas')">Todas</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="filtrarPrioridad('alta')">Alta</button>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="filtrarPrioridad('media')">Media</button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="filtrarPrioridad('baja')">Baja</button>
                        </div>

                        <strong class="ms-4">Filtrar por estado:</strong>
                        <div class="btn-group ms-2" role="group">
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="filtrarEstado('completados')">Completados</button>
                        </div>
                    </div>

                    <!-- Aquí se mostrará el calendario -->
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>



    <!-- Modal para crear nuevo documento -->
    <div class="modal fade" id="modalNuevoDoc" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Nuevo Documento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoDoc">
                        <div class="mb-3">
                            <label>Número de Documento *</label>
                            <input type="text" class="form-control" id="numero_documento" required>
                        </div>
                        <div class="mb-3">
                            <label>Asunto *</label>
                            <input type="text" class="form-control" id="asunto" required>
                        </div>
                        <div class="mb-3">
                            <label>Fecha Límite *</label>
                            <input type="date" class="form-control" id="fecha_limite" required>
                        </div>
                        <div class="mb-3">
                            <label>Prioridad *</label>
                            <select class="form-control" id="prioridad">
                                <option value="baja">Baja</option>
                                <option value="media" selected>Media</option>
                                <option value="alta">Alta</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="guardarDocumento()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 1: Ver Detalles del Documento -->
    <div class="modal fade" id="modalVerDetalles" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-file-alt"></i> Detalle de Hoja de Ruta
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Indicador de progreso -->
                    <div class="d-flex justify-content-between mb-4" id="stepperDetalles">
                        <div class="stepper-item completed">
                            <div class="step-counter">1</div>
                            <div class="step-name">Delegado</div>
                        </div>
                        <div class="stepper-item active">
                            <div class="step-counter">2</div>
                            <div class="step-name">En Progreso</div>
                        </div>
                        <div class="stepper-item">
                            <div class="step-counter">3</div>
                            <div class="step-name">Respuesta</div>
                        </div>
                        <div class="stepper-item">
                            <div class="step-counter">4</div>
                            <div class="step-name">Completado</div>
                        </div>
                    </div>

                    <hr>

                    <!-- Información del documento -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">N° Documento</small>
                            <strong id="detNumDoc"></strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Estado</small>
                            <span id="detEstado" class="badge"></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Fecha de Recepción</small>
                            <span id="detFechaRecep"></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Fecha Límite</small>
                            <span id="detFechaLim" class="text-danger fw-bold"></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Prioridad</small>
                            <span id="detPrioridad" class="badge"></span>
                        </div>
                        <div class="col-md-6 mb-3" id="divRemitente">
                            <small class="text-muted d-block">Remitente</small>
                            <span id="detRemitente"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Asunto</small>
                        <div id="detAsunto" class="p-3 bg-light rounded"></div>
                    </div>

                    <div class="mb-3" id="divObservaciones" style="display: none;">
                        <small class="text-muted d-block">Observaciones</small>
                        <div id="detObservaciones" class="p-3 bg-light rounded text-muted"></div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <small class="text-muted d-block mb-2">Documento Original</small>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="abrirPDFNuevaPestana()">
                                <i class="fas fa-external-link-alt"></i> Abrir PDF
                            </button>
                            <a id="btnDescargar" href="#" download class="btn btn-sm btn-outline-success">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                        </div>
                    </div>

                    <!-- Preview del documento original (siempre visible) -->
                    <div class="mt-3">
                        <small class="text-muted d-block mb-2"><strong>Vista Previa del Documento:</strong></small>
                        <iframe id="iframePreviewOriginal" style="width: 100%; height: 450px; border: 1px solid #dee2e6; border-radius: 4px;"></iframe>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-success" id="btnResponder" onclick="abrirModalResponder()">
                        <i class="fas fa-reply"></i> Responder
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Responder Tarea -->
    <div class="modal fade" id="modalResponder" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-reply"></i> Responder Hoja de Ruta
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formResponder" enctype="multipart/form-data">
                    <input type="hidden" id="idDocResp" name="id_documento">
                    <div class="modal-body">
                        <!-- Indicador de progreso -->
                        <div class="d-flex justify-content-between mb-4">
                            <div class="stepper-item completed">
                                <div class="step-counter">1</div>
                                <div class="step-name">Delegado</div>
                            </div>
                            <div class="stepper-item completed">
                                <div class="step-counter">2</div>
                                <div class="step-name">En Progreso</div>
                            </div>
                            <div class="stepper-item active">
                                <div class="step-counter">3</div>
                                <div class="step-name">Respondiendo</div>
                            </div>
                            <div class="stepper-item">
                                <div class="step-counter">4</div>
                                <div class="step-name">Completado</div>
                            </div>
                        </div>

                        <hr>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>N° Documento:</strong> <span id="respNumDoc"></span>
                        </div>

                        <!-- Subir archivo de respuesta -->
                        <div class="mb-3">
                            <label class="form-label">
                                <strong>Archivo de Respuesta (PDF) *</strong>
                            </label>
                            <div class="d-flex gap-2">
                                <input type="file" class="form-control" id="archivoRespuesta" name="archivo_respuesta"
                                    accept=".pdf" required onchange="mostrarInfoArchivo()">
                                <button type="button" class="btn btn-outline-danger btn-sm" id="btnQuitarArchivo"
                                    onclick="quitarArchivo()" style="display: none;" title="Quitar archivo">
                                    <span class="material-icons" style="font-size: 20px;">close</span>
                                </button>
                            </div>
                            <small class="text-muted">Solo archivos PDF. Tamaño máximo: 10MB</small>
                        </div>

                        <!-- Info del archivo cargado -->
                        <div id="infoArchivo" class="alert alert-success" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-file-pdf text-danger"></i>
                                    <strong id="nombreArchivo"></strong>
                                    <span id="tamañoArchivo" class="text-muted ms-2"></span>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">Fecha y hora de respuesta:</small>
                                    <strong id="fechaHoraRespuesta"></strong>
                                </div>
                            </div>
                        </div>

                        <!-- Preview del PDF -->
                        <div id="previewPDF" style="display: none;">
                            <label class="form-label"><strong>Vista previa:</strong></label>
                            <iframe id="iframePreview" style="width: 100%; height: 400px; border: 1px solid #dee2e6; border-radius: 4px;"></iframe>
                        </div>

                        <!-- Comentarios opcionales -->
                        <div class="mb-3 mt-3">
                            <label class="form-label">
                                <strong>Comentarios</strong> (Opcional)
                            </label>
                            <textarea class="form-control" id="comentariosResp" name="comentarios"
                                rows="3" placeholder="Agregue comentarios sobre su respuesta..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-primary" onclick="confirmarCompletado(false)">
                            <i class="fas fa-check"></i> Solo Completar
                        </button>
                        <button type="button" class="btn btn-success" onclick="confirmarCompletado(true)">
                            <i class="fas fa-archive"></i> Archivar y Completar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JS de FullCalendar -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js'></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.todosLosDocumentos = []; // Guardar todos los documentos
        var filtroActual = 'todas'; // Filtro actual
        var calendarEl = document.getElementById('calendar');

        window.calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            height: 'auto',
            contentHeight: 600,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },

            eventClick: function(info) {
                mostrarDetalleDocumento(info.event);
            },

            events: function(info, successCallback, failureCallback) {
                // Cargar documentos
                fetch('<?php echo BASE_URL; ?>calendario/listarPendientes')
                    .then(response => response.json())
                    .then(data => {
                        console.log('Documentos recibidos:', data);

                        // Convertir documentos a formato de eventos
                        var eventos = data.map(doc => {
                            return {
                                id: doc.id,
                                title: doc.numero_documento + ': ' + doc.asunto,
                                start: doc.fecha_limite,
                                color: obtenerColor(doc.prioridad, doc.estado),
                                extendedProps: {
                                    prioridad: doc.prioridad,
                                    estado: doc.estado,
                                    carpeta: doc.carpeta,
                                    fecha_completado: doc.fecha_completado,
                                    fecha_recepcion: doc.fecha_recepcion,
                                    archivo_adjunto: doc.archivo_adjunto
                                }
                            };
                        });
                        window.todosLosDocumentos = eventos;
                        successCallback(eventos);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        failureCallback(error);
                    });
            }
        });

        window.calendar.render();
        console.log('Calendario listo');

        // Cargar notificaciones al iniciar
        cargarNotificaciones();
    });

    function obtenerColor(prioridad, estado) {
        if (estado === 'completado' || estado === 'archivado') return '#dfe1e4ff'; // Gris para completados
        if (prioridad === 'alta') return '#dc3545';
        if (prioridad === 'media') return '#ffc107';
        if (prioridad === 'baja') return '#28a745';
        return '#007bff';
    }

    // Filtrar por prioridad
    function filtrarPrioridad(prioridad) {
        filtroActual = prioridad;

        // Actualizar botones activos
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');

        // Aplicar filtro
        var eventosFiltrados = window.todosLosDocumentos;
        if (prioridad !== 'todas') {
            eventosFiltrados = window.todosLosDocumentos.filter(doc =>
                doc.extendedProps.prioridad === prioridad
            );
        }

        // Actualizar calendario
        window.calendar.removeAllEvents();
        window.calendar.addEventSource(eventosFiltrados);
    }

    // Buscar documentos
    function buscarDocumento() {
        var texto = document.getElementById('buscarDocumento').value.toLowerCase();

        var eventosFiltrados = window.todosLosDocumentos.filter(doc => {
            var numero = doc.title.toLowerCase();
            return numero.includes(texto);
        });

        // Actualizar calendario
        window.calendar.removeAllEvents();
        window.calendar.addEventSource(eventosFiltrados);
    }

    // Limpiar búsqueda
    function limpiarBusqueda() {
        document.getElementById('buscarDocumento').value = '';
        window.calendar.removeAllEvents();
        window.calendar.addEventSource(window.todosLosDocumentos);
    }

    // Guardar nuevo documento
    function guardarDocumento() {
        var numero = document.getElementById('numero_documento').value;
        var asunto = document.getElementById('asunto').value;
        var fecha_limite = document.getElementById('fecha_limite').value;
        var prioridad = document.getElementById('prioridad').value;

        if (!numero || !asunto || !fecha_limite) {
            alertaPersonalizada('warning', 'Por favor complete todos los campos obligatorios');
            return;
        }

        // Enviar al servidor
        fetch('<?php echo BASE_URL; ?>calendario/guardar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'numero=' + encodeURIComponent(numero) +
                    '&asunto=' + encodeURIComponent(asunto) +
                    '&fecha_limite=' + encodeURIComponent(fecha_limite) +
                    '&prioridad=' + encodeURIComponent(prioridad)
            })
            .then(response => response.json())
            .then(data => {
                alertaPersonalizada(data.tipo, data.mensaje);

                if (data.tipo === 'success') {
                    // Cerrar modal
                    var modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoDoc'));
                    modal.hide();
                    // Limpiar formulario
                    document.getElementById('formNuevoDoc').reset();
                    // Recargar calendario
                    window.calendar.refetchEvents();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alertaPersonalizada('error', 'Error al guardar documento');
            });
    } // Este cierre es del guardarDocumento

    // Filtrar por estado
    function filtrarEstado(estado) {
        // Actualizar botones activos del grupo de estado
        document.querySelectorAll('.btn-group .btn-outline-info').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');

        // Filtrar documentos completados
        var eventosFiltrados = window.todosLosDocumentos.filter(doc =>
            doc.extendedProps.estado === 'completado'
        );

        // Actualizar calendario
        window.calendar.removeAllEvents();
        window.calendar.addEventSource(eventosFiltrados);
    }



    // Variable global para la ruta del PDF actual
    window.rutaPDFActual = '';
    window.idDocumentoActual = 0;

    // Mostrar detalle del documento (MODAL 1)
    // Mostrar detalle del documento (MODAL 1)
    function mostrarDetalleDocumento(evento) {
        window.idDocumentoActual = evento.id;

        // Extraer número y asunto
        var partes = evento.title.split(': ');
        var numero = partes[0];
        var asunto = partes.slice(1).join(': ');

        // Llenar información básica primero
        document.getElementById('detNumDoc').textContent = numero;
        document.getElementById('detAsunto').textContent = asunto;
        document.getElementById('detFechaLim').textContent = evento.start.toLocaleDateString('es-ES');

        // Llenar fecha de recepción
        if (evento.extendedProps.fecha_recepcion) {
            var fechaRecep = new Date(evento.extendedProps.fecha_recepcion);
            document.getElementById('detFechaRecep').textContent = fechaRecep.toLocaleDateString('es-ES');
        } else {
            document.getElementById('detFechaRecep').textContent = 'No registrada';
        }

        // Estado
        var badgeEstado = document.getElementById('detEstado');
        var estadoTexto = evento.extendedProps.estado.replace('_', ' ').toUpperCase();
        badgeEstado.textContent = estadoTexto;
        badgeEstado.className = 'badge';
        if (evento.extendedProps.estado === 'completado') {
            badgeEstado.classList.add('bg-success');
        } else if (evento.extendedProps.estado === 'en_progreso') {
            badgeEstado.classList.add('bg-primary');
        } else if (evento.extendedProps.estado === 'respondiendo') {
            badgeEstado.classList.add('bg-info');
        } else {
            badgeEstado.classList.add('bg-warning');
        }

        // Prioridad
        var badgePrioridad = document.getElementById('detPrioridad');
        badgePrioridad.textContent = evento.extendedProps.prioridad.toUpperCase();
        badgePrioridad.className = 'badge';
        if (evento.extendedProps.prioridad === 'alta') {
            badgePrioridad.classList.add('bg-danger');
        } else if (evento.extendedProps.prioridad === 'media') {
            badgePrioridad.classList.add('bg-warning');
        } else {
            badgePrioridad.classList.add('bg-success');
        }

        // Mostrar/ocultar botón Responder según estado
        var btnResponder = document.getElementById('btnResponder');
        if (evento.extendedProps.estado === 'completado' || evento.extendedProps.estado === 'archivado') {
            btnResponder.style.display = 'none';
        } else {
            btnResponder.style.display = 'inline-block';
        }

        // Actualizar indicador de progreso según estado actual
        actualizarStepper('stepperDetalles', evento.extendedProps.estado);

        // Determinar carpeta
        var carpeta = 'en_proceso';
        if (evento.extendedProps.estado === 'completado' || evento.extendedProps.estado === 'archivado') {
            carpeta = 'completados';
        }

        // Buscar y cargar el PDF
        fetch('<?php echo BASE_URL; ?>calendario/buscarArchivoPDF/' + numero + '/' + carpeta)
            .then(response => response.json())
            .then(data => {
                if (data.archivo) {
                    var rutaPDF = '<?php echo BASE_URL; ?>Assets/documentos_oficiales/' + carpeta + '/' + data.archivo;
                    window.rutaPDFActual = rutaPDF;

                    // Actualizar iframe y botón de descarga
                    document.getElementById('iframePreviewOriginal').src = rutaPDF;
                    document.getElementById('btnDescargar').href = rutaPDF;
                    document.getElementById('btnDescargar').download = data.archivo;
                }
            })
            .catch(error => {
                console.error('Error al buscar PDF:', error);
            });

        // Registrar visualización si es primera vez
        if (evento.extendedProps.estado === 'delegado') {
            registrarVisualizacion(evento.id);
        }

        // Aplicar efectos visuales si está completado
        if (evento.extendedProps.estado === 'completado' || evento.extendedProps.estado === 'archivado') {
            document.querySelector('#modalVerDetalles .modal-body').style.filter = 'grayscale(50%)';
            document.querySelector('#modalVerDetalles .modal-body').style.opacity = '0.85';
        } else {
            document.querySelector('#modalVerDetalles .modal-body').style.filter = 'none';
            document.querySelector('#modalVerDetalles .modal-body').style.opacity = '1';
        }

        // Mostrar modal
        var modal = new bootstrap.Modal(document.getElementById('modalVerDetalles'));
        modal.show();
    }

    // Actualizar indicador de progreso
    function actualizarStepper(stepperId, estado) {
        var stepper = document.getElementById(stepperId);
        var steps = stepper.querySelectorAll('.stepper-item');

        // Resetear todos
        steps.forEach(step => {
            step.classList.remove('active', 'completed');
        });

        // Marcar según estado
        if (estado === 'delegado') {
            steps[0].classList.add('active');
        } else if (estado === 'en_progreso') {
            steps[0].classList.add('completed');
            steps[1].classList.add('active');
        } else if (estado === 'respondiendo') {
            steps[0].classList.add('completed');
            steps[1].classList.add('completed');
            steps[2].classList.add('active');
        } else if (estado === 'completado' || estado === 'archivado') {
            steps[0].classList.add('completed');
            steps[1].classList.add('completed');
            steps[2].classList.add('completed');
            steps[3].classList.add('completed');
            steps[3].classList.add('active');
        }
    }

    // Registrar que el usuario visualizó el documento
    function registrarVisualizacion(idDoc) {
        fetch('<?php echo BASE_URL; ?>calendario/registrarVisualizacion', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + idDoc
            })
            .then(response => response.json())
            .then(data => {
                if (data.tipo === 'success') {
                    // Recargar calendario para reflejar el cambio de estado
                    window.calendar.refetchEvents();
                }
            });
    }

    // Abrir PDF en nueva pestaña
    function abrirPDFNuevaPestana() {
        window.open(window.rutaPDFActual, '_blank');
    }

    // Abrir modal para responder (MODAL 2)
    function abrirModalResponder() {
        // Cerrar modal de detalles
        var modalDetalles = bootstrap.Modal.getInstance(document.getElementById('modalVerDetalles'));
        modalDetalles.hide();

        // Llenar info en modal de responder
        var numero = document.getElementById('detNumDoc').textContent;
        document.getElementById('respNumDoc').textContent = numero;
        document.getElementById('idDocResp').value = window.idDocumentoActual;

        // Limpiar formulario
        document.getElementById('formResponder').reset();
        document.getElementById('infoArchivo').style.display = 'none';
        document.getElementById('previewPDF').style.display = 'none';

        // Mostrar modal de responder
        var modalResponder = new bootstrap.Modal(document.getElementById('modalResponder'));
        modalResponder.show();
    }

    // Mostrar info del archivo cuando se selecciona
    function mostrarInfoArchivo() {
        var input = document.getElementById('archivoRespuesta');
        var archivo = input.files[0];

        if (!archivo) {
            document.getElementById('infoArchivo').style.display = 'none';
            document.getElementById('previewPDF').style.display = 'none';
            return;
        }

        // Validar tipo
        if (archivo.type !== 'application/pdf') {
            alertaPersonalizada('warning', 'El archivo debe ser PDF');
            input.value = '';
            return;
        }

        // Validar tamaño (10MB)
        if (archivo.size > 10 * 1024 * 1024) {
            alertaPersonalizada('warning', 'El archivo no debe superar 10MB');
            input.value = '';
            return;
        }

        // Mostrar información del archivo
        document.getElementById('nombreArchivo').textContent = archivo.name;
        var tamañoMB = (archivo.size / (1024 * 1024)).toFixed(2);
        document.getElementById('tamañoArchivo').textContent = '(' + tamañoMB + ' MB)';

        // Fecha y hora actual
        var ahora = new Date();
        var fechaHora = ahora.toLocaleDateString('es-ES') + ' ' + ahora.toLocaleTimeString('es-ES');
        document.getElementById('fechaHoraRespuesta').textContent = fechaHora;

        document.getElementById('infoArchivo').style.display = 'block';

        // Preview del PDF
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('iframePreview').src = e.target.result;
            document.getElementById('previewPDF').style.display = 'block';
        };
        reader.readAsDataURL(archivo);

        // Al final de mostrarInfoArchivo(), AGREGAR:
        document.getElementById('btnQuitarArchivo').style.display = 'inline-block';
    }

    // Confirmar completado (con o sin archivar)
    function confirmarCompletado(archivar) {
        var form = document.getElementById('formResponder');
        var archivoInput = document.getElementById('archivoRespuesta');

        // Validar que se haya subido archivo
        if (!archivoInput.files || archivoInput.files.length === 0) {
            alertaPersonalizada('warning', 'Debe adjuntar el archivo de respuesta');
            return;
        }

        // Preparar FormData
        var formData = new FormData(form);
        formData.append('archivar', archivar ? '1' : '0');

        // Enviar
        fetch('<?php echo BASE_URL; ?>calendario/completarTarea', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.tipo === 'success') {
                    alertaPersonalizada('success', data.mensaje);

                    // Cerrar modal
                    var modal = bootstrap.Modal.getInstance(document.getElementById('modalResponder'));
                    modal.hide();

                    // Recargar calendario
                    window.calendar.refetchEvents();
                } else {
                    alertaPersonalizada(data.tipo, data.mensaje);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alertaPersonalizada('error', 'Error al completar la tarea');
            });
    }

    // Quitar archivo seleccionado
    function quitarArchivo() {
        document.getElementById('archivoRespuesta').value = '';
        document.getElementById('infoArchivo').style.display = 'none';
        document.getElementById('previewPDF').style.display = 'none';
        document.getElementById('btnQuitarArchivo').style.display = 'none';
    }

    /**
     * Busca y muestra notificaciones pendientes una por una con SweetAlert2
     * Incluye animaciones de Animate.css e iconos animados
     */
    async function cargarNotificaciones() {
        try {
            const response = await fetch('<?php echo BASE_URL; ?>calendario/listarNotificaciones');
            const notificaciones = await response.json();

            if (notificaciones.length === 0) {
                return; // No hay nada que mostrar
            }

            // Mostrar cada notificación una por una
            for (const doc of notificaciones) {

                let titulo = (doc.estado === 'conocimiento') ? 'Nuevo Documento (P.Conocimiento)' : 'Nueva Tarea Delegada';

                // Iconos animados con FontAwesome y Animate.css
                let htmlIcono = (doc.estado === 'conocimiento')
                    ? `<i class="fas fa-envelope fa-2x animate__animated animate__tada" style="color: #0dcaf0; --animate-duration: 2s;"></i>`
                    : `<i class="fas fa-file-alt fa-2x animate__animated animate__tada" style="color: #ffc107; --animate-duration: 2s;"></i>`;

                let html = `<b>Asunto:</b> ${doc.asunto}<br>`;
                if (doc.estado === 'conocimiento') {
                    html += `<small>Publicado: ${doc.fecha_recepcion}</small>`;
                } else {
                    if (doc.sin_limite == '0' && doc.fecha_limite) {
                         html += `<b style="color: #dc3545;">Fecha Límite: ${doc.fecha_limite}</b>`;
                    }
                }

                await Swal.fire({
                    title: titulo,
                    html: html,
                    iconHtml: htmlIcono,
                    confirmButtonText: 'Aceptar',
                    showClass: { popup: 'animate__animated animate__fadeInUp animate__faster' },
                    hideClass: { popup: 'animate__animated animate__fadeOutDown animate__faster' },

                    preConfirm: () => {
                        return fetch('<?php echo BASE_URL; ?>calendario/registrarVisualizacion', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                            body: 'id=' + doc.id
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.tipo !== 'success' && data.tipo !== 'info') {
                                Swal.showValidationMessage('Error al marcar como visto');
                            }
                            return data;
                        })
                        .catch(error => Swal.showValidationMessage('Error de red'));
                    }
                });
            }

            // Al terminar todas las notificaciones
            Swal.fire({
                title: '¡Todo listo!',
                text: 'Viste todas las notificaciones.',
                icon: 'success',
                showConfirmButton: false,
                timer: 1500,
                showClass: { popup: 'animate__animated animate__fadeIn animate__faster' },
                hideClass: { popup: 'animate__animated animate__fadeOut animate__faster' }
            });

            // Recargar eventos del calendario
            window.calendar.refetchEvents();

        } catch (error) {
            console.error("Error al cargar notificaciones:", error);
        }
    }
</script>

<?php include_once 'Views/template/footer.php'; ?>