<?php include_once 'Views/template/header.php'; ?>

<!-- CSS de FullCalendar -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

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
    <!-- Modal para detalles del documento -->
    <div class="modal fade" id="modalDetalleDoc" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Detalle del Documento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Número:</strong>
                        <p id="modalNumero"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Asunto:</strong>
                        <p id="modalAsunto"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Fecha Límite:</strong>
                        <p id="modalFecha"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Prioridad:</strong>
                        <span id="modalPrioridad" class="badge"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Estado:</strong>
                        <span id="modalEstado" class="badge bg-secondary"></span>
                    </div>
                    <div class="mb-3" id="divFechaCompletado" style="display: none;">
                        <strong>Fecha Completado:</strong>
                        <p id="modalFechaCompletado" class="text-success fw-bold"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Carpeta:</strong>
                        <p id="modalCarpeta"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="btnCompletarTarea" onclick="completarTarea()">
                        <i class="fas fa-check"></i> Tarea Completada
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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

    <!-- Modal de confirmación al completar tarea -->
    <div class="modal fade" id="modalConfirmarArchivo" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Tarea Completada</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                    <h5 class="mt-3">¿Desea archivar este documento en Documentos Personales?</h5>
                    <p class="text-muted">El documento se moverá a Adm. de Archivos</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-success" onclick="confirmarArchivo(true)">
                        <i class="fas fa-check"></i> Sí, Archivar
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="confirmarArchivo(false)">
                        <i class="fas fa-times"></i> No, Solo Completar
                    </button>
                </div>
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
    });

    // Función para color según prioridad
    function obtenerColor(prioridad, estado) {
        if (estado === 'completado') return '#dfe1e4ff'; // Gris para completados
        if (prioridad === 'alta') return '#dc3545';
        if (prioridad === 'media') return '#ffc107';
        if (prioridad === 'baja') return '#28a745';
        return '#007bff';
    }

    // Mostrar detalle del documento
    function mostrarDetalleDocumento(evento) {
        // Guardar ID del documento para usarlo en los botones
        window.documentoActual = evento.id;
        // Extraer número y asunto del título
        var partes = evento.title.split(': ');
        var numero = partes[0];
        var asunto = partes.slice(1).join(': ');

        // Llenar el modal
        document.getElementById('modalNumero').textContent = numero;
        document.getElementById('modalAsunto').textContent = asunto;
        document.getElementById('modalFecha').textContent = evento.start.toLocaleDateString('es-ES');
        document.getElementById('modalCarpeta').textContent = evento.extendedProps.carpeta;

        // Prioridad con badge de color
        var badgePrioridad = document.getElementById('modalPrioridad');
        badgePrioridad.textContent = evento.extendedProps.prioridad.toUpperCase();
        badgePrioridad.className = 'badge';
        if (evento.extendedProps.prioridad === 'alta') {
            badgePrioridad.classList.add('bg-danger');
        } else if (evento.extendedProps.prioridad === 'media') {
            badgePrioridad.classList.add('bg-warning');
        } else {
            badgePrioridad.classList.add('bg-success');
        }

        // Estado
        document.getElementById('modalEstado').textContent = evento.extendedProps.estado.toUpperCase();

        // Si está completado, mostrar fecha y aplicar estilo
        var divFechaCompletado = document.getElementById('divFechaCompletado');
        var btnCompletar = document.getElementById('btnCompletarTarea');
        
        if (evento.extendedProps.estado === 'completado') {
            // Mostrar fecha completado
            if (evento.extendedProps.fecha_completado) {
                var fecha = new Date(evento.extendedProps.fecha_completado);
                document.getElementById('modalFechaCompletado').textContent = 
                    fecha.toLocaleDateString('es-ES') + ' ' + fecha.toLocaleTimeString('es-ES');
                divFechaCompletado.style.display = 'block';
            }
            
            // Aplicar estilo opaco al modal
            document.querySelector('#modalDetalleDoc .modal-body').style.opacity = '0.6';
            
            // Ocultar botón de completar tarea
            btnCompletar.style.display = 'none';
        } else {
            // Resetear estilos para documentos pendientes
            divFechaCompletado.style.display = 'none';
            document.querySelector('#modalDetalleDoc .modal-body').style.opacity = '1';
            btnCompletar.style.display = 'inline-block';
        }
        // Mostrar el modal
        var modal = new bootstrap.Modal(document.getElementById('modalDetalleDoc'));
        modal.show();
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
    
    // Completar tarea
    function completarTarea() {
        if (!window.documentoActual) {
            alertaPersonalizada('error', 'No se pudo identificar el documento');
            return;
        }
        
        // Cerrar modal de detalles
        var modalDetalles = bootstrap.Modal.getInstance(document.getElementById('modalDetalleDoc'));
        modalDetalles.hide();
        
        // Mostrar modal de confirmación
        var modalConfirmar = new bootstrap.Modal(document.getElementById('modalConfirmarArchivo'));
        modalConfirmar.show();
    }

    // Confirmar si archivar o no
    function confirmarArchivo(archivar) {
        var modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarArchivo'));
        modal.hide();
        
        // Enviar al servidor para cambiar estado
        fetch('<?php echo BASE_URL; ?>calendario/completar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + window.documentoActual
        })
        .then(response => response.json())
        .then(data => {
            if (archivar) {
                alertaPersonalizada('success', 'Tarea completada y archivada');
            } else {
                alertaPersonalizada('success', 'Tarea completada');
            }
            
            // Recargar calendario para ver el cambio de color
            window.calendar.refetchEvents();
        })
        .catch(error => {
            console.error('Error:', error);
            alertaPersonalizada('error', 'Error al completar tarea');
        });
    }

    // Archivar documento
    function archivarDocumento() {
        if (!window.documentoActual) {
            alertaPersonalizada('error', 'No se pudo identificar el documento');
            return;
        }
        
        // Solo notificación por ahora
        alertaPersonalizada('info', 'Documento enviado a Adm. de Archivos (funcionalidad pendiente)');
    }

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
</script>

<?php include_once 'Views/template/footer.php'; ?>