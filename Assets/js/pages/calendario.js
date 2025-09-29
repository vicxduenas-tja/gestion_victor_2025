let calendario;
let documentoActual = null;

document.addEventListener('DOMContentLoaded', function() {
    inicializarCalendario();
    inicializarEventos();
    cargarDocumentosHoy();
    actualizarNotificaciones();
    
    // Actualizar cada 5 minutos
    setInterval(function() {
        calendario.refetchEvents();
        actualizarNotificaciones();
        cargarDocumentosHoy();
    }, 300000);
});

// ============ INICIALIZACIÓN DEL CALENDARIO ============

function inicializarCalendario() {
    const calendarEl = document.getElementById('calendario');
    
    calendario = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: ''
        },
        height: 'auto',
        editable: false,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekNumbers: true,
        weekText: 'Sem',
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día',
            list: 'Lista'
        },
        
        // Cargar eventos desde el servidor
        events: {
            url: base_url + 'calendario/getEventos',
            method: 'GET',
            failure: function() {
                Swal.fire('Error', 'No se pudieron cargar los documentos', 'error');
            }
        },
        
        // Click en un evento
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            verDocumento(info.event.id);
        },
        
        // Click en un día
        dateClick: function(info) {
            const fecha = info.dateStr + 'T12:00';
            limpiarFormulario();
            $('#fecha_limite').val(fecha);
            $('#modalDocumento').modal('show');
        },
        
        // Personalizar la visualización de eventos
        eventDidMount: function(info) {
            const props = info.event.extendedProps;
            const tooltipContent = `
                <strong>${props.numero}</strong><br>
                ${props.asunto}<br>
                <small>Remitente: ${props.remitente}</small><br>
                <small>Prioridad: ${props.prioridad}</small><br>
                <small>Estado: ${props.estado}</small>
            `;
            
            // Agregar tooltip
            $(info.el).tooltip({
                title: tooltipContent,
                html: true,
                placement: 'top',
                container: 'body'
            });
            
            // Agregar icono según prioridad
            if (props.prioridad === 'URGENTE') {
                info.el.innerHTML = '🔴 ' + info.el.innerHTML;
            } else if (props.prioridad === 'ALTA') {
                info.el.innerHTML = '🟠 ' + info.el.innerHTML;
            }
            
            // Agregar clase CSS según estado
            if (props.estado === 'COMPLETADO') {
                info.el.classList.add('estado-completado');
            } else if (props.estado === 'VENCIDO') {
                info.el.classList.add('estado-vencido');
            }
        },
        
        // Personalizar día actual
        dayCellDidMount: function(arg) {
            const fecha = arg.date.toISOString().split('T')[0];
            verificarDocumentosVencidos(fecha, arg.el);
        }
    });
    
    calendario.render();
}

// ============ INICIALIZACIÓN DE EVENTOS ============

function inicializarEventos() {
    // Botón nuevo documento
    $('#btnNuevoDocumento').on('click', function() {
        limpiarFormulario();
        $('#modalDocumentoTitle').text('Nuevo Documento');
        const ahora = new Date();
        const fechaHora = ahora.toISOString().slice(0, 16);
        $('#fecha_recepcion').val(fechaHora);
        $('#modalDocumento').modal('show');
    });
    
    // Botón guardar documento
    $('#btnGuardarDocumento').on('click', guardarDocumento);
    
    // Botón hoy
    $('#btnHoy').on('click', function() {
        calendario.today();
    });
    
    // Botones de vista
    $('#btnVistaMonth').on('click', function() {
        calendario.changeView('dayGridMonth');
        actualizarBotonesVista(this);
    });
    
    $('#btnVistaWeek').on('click', function() {
        calendario.changeView('timeGridWeek');
        actualizarBotonesVista(this);
    });
    
    $('#btnVistaList').on('click', function() {
        calendario.changeView('listWeek');
        actualizarBotonesVista(this);
    });
    
    // Botón reportes
    $('#btnReportes').on('click', mostrarReportes);
    
    // Botón derivar
    $('#btnDerivar').on('click', function() {
        $('#modalVerDocumento').modal('hide');
        $('#derivar_doc_id').val(documentoActual);
        $('#modalDerivar').modal('show');
    });
    
    // Confirmar derivación
    $('#btnConfirmarDerivar').on('click', derivarDocumento);
    
    // Botón completar documento
    $('#btnCompletarDoc').on('click', completarDocumento);
    
    // Cambio de prioridad - actualizar color
    $('#prioridad').on('change', function() {
        const prioridad = $(this).val();
        $(this).removeClass('bg-danger bg-warning bg-info bg-secondary text-white');
        
        if (prioridad === 'URGENTE') {
            $(this).addClass('bg-danger text-white');
        } else if (prioridad === 'ALTA') {
            $(this).addClass('bg-warning');
        } else if (prioridad === 'MEDIA') {
            $(this).addClass('bg-info text-white');
        } else {
            $(this).addClass('bg-secondary text-white');
        }
    });
    
    // Click en notificaciones
    $(document).on('click', '.notificacion-item', function() {
        const id = $(this).data('id');
        marcarNotificacionLeida(id);
    });
}

// ============ GESTIÓN DE DOCUMENTOS ============

function guardarDocumento() {
    const formData = new FormData(document.getElementById('formDocumento'));
    
    // Validar campos requeridos
    if (!formData.get('numero_documento') || !formData.get('asunto') || 
        !formData.get('remitente') || !formData.get('fecha_recepcion') || 
        !formData.get('fecha_limite') || !formData.get('id_carpeta') || 
        !formData.get('id_usuario_asignado')) {
        Swal.fire('Advertencia', 'Por favor complete todos los campos obligatorios', 'warning');
        return;
    }
    
    // Mostrar loading
    Swal.fire({
        title: 'Guardando...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(base_url + 'calendario/guardar', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        
        if (data.tipo === 'success') {
            Swal.fire('Éxito', data.msg, 'success');
            $('#modalDocumento').modal('hide');
            calendario.refetchEvents();
            cargarDocumentosHoy();
        } else {
            Swal.fire('Error', data.msg, data.tipo);
        }
    })
    .catch(error => {
        Swal.close();
        Swal.fire('Error', 'Error al guardar el documento', 'error');
        console.error('Error:', error);
    });
}

function verDocumento(id) {
    documentoActual = id;
    
    Swal.fire({
        title: 'Cargando...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(base_url + 'calendario/getDocumento/' + id)
    .then(response => response.json())
    .then(doc => {
        Swal.close();
        
        if (doc.error) {
            Swal.fire('Error', doc.error, 'error');
            return;
        }
        
        // Construir HTML de detalles
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Número:</strong> ${doc.numero_documento}</p>
                    <p><strong>Tipo:</strong> ${doc.tipo_documento}</p>
                    <p><strong>Remitente:</strong> ${doc.remitente}</p>
                    <p><strong>Destinatario:</strong> ${doc.destinatario || 'N/A'}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Estado:</strong> <span class="badge bg-${getEstadoBadgeClass(doc.estado)}">${doc.estado}</span></p>
                    <p><strong>Prioridad:</strong> <span class="badge bg-${getPrioridadBadgeClass(doc.prioridad)}">${doc.prioridad}</span></p>
                    <p><strong>Carpeta:</strong> ${doc.carpeta_nombre}</p>
                    <p><strong>Asignado a:</strong> ${doc.usuario_asignado}</p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12">
                    <p><strong>Asunto:</strong></p>
                    <p>${doc.asunto}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Fecha Recepción:</strong> ${formatearFecha(doc.fecha_recepcion)}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Fecha Límite:</strong> ${formatearFecha(doc.fecha_limite)}</p>
                </div>
            </div>
            ${doc.observaciones ? `
            <div class="row">
                <div class="col-12">
                    <p><strong>Observaciones:</strong></p>
                    <p>${doc.observaciones}</p>
                </div>
            </div>` : ''}
            <hr>
            <h6>Línea de Tiempo</h6>
            <div class="timeline">
        `;
        
        // Agregar seguimiento
        if (doc.seguimiento && doc.seguimiento.length > 0) {
            doc.seguimiento.forEach(seg => {
                html += `
                    <div class="timeline-item">
                        <div class="timeline-badge">
                            <i class="fas fa-${getAccionIcon(seg.accion)}"></i>
                        </div>
                        <div class="timeline-content">
                            <strong>${seg.accion}</strong> - ${seg.usuario_nombre}
                            <br><small class="text-muted">${formatearFecha(seg.fecha)}</small>
                            ${seg.comentario ? `<br><em>${seg.comentario}</em>` : ''}
                        </div>
                    </div>
                `;
            });
        } else {
            html += '<p class="text-muted">No hay seguimiento registrado</p>';
        }
        
        html += '</div>';
        
        $('#detallesDocumento').html(html);
        $('#modalVerDocumento').modal('show');
        
        // Ocultar botones según estado
        if (doc.estado === 'COMPLETADO' || doc.estado === 'VENCIDO') {
            $('#btnCompletarDoc').hide();
        } else {
            $('#btnCompletarDoc').show();
        }
    })
    .catch(error => {
        Swal.close();
        Swal.fire('Error', 'Error al cargar el documento', 'error');
        console.error('Error:', error);
    });
}

function derivarDocumento() {
    const formData = new FormData(document.getElementById('formDerivar'));
    
    if (!formData.get('id_usuario_destino')) {
        Swal.fire('Advertencia', 'Debe seleccionar un usuario', 'warning');
        return;
    }
    
    Swal.fire({
        title: '¿Derivar documento?',
        text: "El documento será asignado al usuario seleccionado",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, derivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Derivando...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch(base_url + 'calendario/derivar', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                
                if (data.tipo === 'success') {
                    Swal.fire('Éxito', data.msg, 'success');
                    $('#modalDerivar').modal('hide');
                    $('#modalVerDocumento').modal('hide');
                    calendario.refetchEvents();
                    cargarDocumentosHoy();
                } else {
                    Swal.fire('Error', data.msg, data.tipo);
                }
            })
            .catch(error => {
                Swal.close();
                Swal.fire('Error', 'Error al derivar el documento', 'error');
                console.error('Error:', error);
            });
        }
    });
}

function completarDocumento() {
    Swal.fire({
        title: '¿Marcar como completado?',
        input: 'textarea',
        inputLabel: 'Comentario final (opcional)',
        inputPlaceholder: 'Escriba un comentario sobre la conclusión del documento...',
        showCancelButton: true,
        confirmButtonText: 'Sí, completar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', documentoActual);
            formData.append('estado', 'COMPLETADO');
            formData.append('comentario', result.value || 'Documento completado');
            
            Swal.fire({
                title: 'Completando...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch(base_url + 'calendario/cambiarEstado', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                
                if (data.tipo === 'success') {
                    Swal.fire('Éxito', data.msg, 'success');
                    $('#modalVerDocumento').modal('hide');
                    calendario.refetchEvents();
                    cargarDocumentosHoy();
                } else {
                    Swal.fire('Error', data.msg, data.tipo);
                }
            })
            .catch(error => {
                Swal.close();
                Swal.fire('Error', 'Error al completar el documento', 'error');
                console.error('Error:', error);
            });
        }
    });
}

// ============ DOCUMENTOS DEL DÍA ============

function cargarDocumentosHoy() {
    const hoy = new Date().toISOString().split('T')[0];
    
    fetch(base_url + 'calendario/getDocumentosDelDia?fecha=' + hoy)
    .then(response => response.json())
    .then(documentos => {
        let html = '';
        
        if (documentos.length === 0) {
            html = '<p class="text-center text-muted">No hay documentos para hoy</p>';
        } else {
            documentos.forEach(doc => {
                const prioridadClass = getPrioridadBadgeClass(doc.prioridad);
                html += `
                    <div class="card mb-2 cursor-pointer" onclick="verDocumento(${doc.id})">
                        <div class="card-body p-2">
                            <h6 class="mb-1">${doc.numero_documento}</h6>
                            <p class="mb-1 small">${doc.asunto.substring(0, 50)}...</p>
                            <span class="badge bg-${prioridadClass}">${doc.prioridad}</span>
                        </div>
                    </div>
                `;
            });
        }
        
        $('#documentosHoy').html(html);
    })
    .catch(error => {
        console.error('Error:', error);
        $('#documentosHoy').html('<p class="text-center text-danger">Error al cargar</p>');
    });
}

// ============ NOTIFICACIONES ============

function actualizarNotificaciones() {
    fetch(base_url + 'calendario/getNotificaciones')
    .then(response => response.json())
    .then(notificaciones => {
        let html = '';
        
        if (notificaciones.length === 0) {
            html = '<p class="text-center text-muted">No hay notificaciones</p>';
        } else {
            notificaciones.forEach(notif => {
                html += `
                    <div class="notificacion-item p-2 border-bottom" data-id="${notif.id}">
                        <small class="text-muted">${formatearFecha(notif.fecha)}</small>
                        <p class="mb-0 small">${notif.mensaje}</p>
                    </div>
                `;
            });
        }
        
        $('#notificaciones').html(html);
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function marcarNotificacionLeida(id) {
    fetch(base_url + 'calendario/marcarLeida/' + id)
    .then(response => response.json())
    .then(data => {
        actualizarNotificaciones();
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// ============ REPORTES ============

function mostrarReportes() {
    const mes = new Date().getMonth() + 1;
    const anio = new Date().getFullYear();
    
    Swal.fire({
        title: 'Cargando reportes...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(base_url + `calendario/reportes?mes=${mes}&anio=${anio}`)
    .then(response => response.json())
    .then(data => {
        Swal.close();
        
        let html = '<h5>Reportes del Mes</h5>';
        html += '<h6>Documentos por Estado</h6>';
        html += '<ul>';
        
        if (data.estadisticas.length > 0) {
            data.estadisticas.forEach(item => {
                html += `<li>${item.estado}: ${item.total}</li>`;
            });
        } else {
            html += '<li>No hay datos</li>';
        }
        
        html += '</ul>';
        html += '<h6>Documentos por Prioridad</h6>';
        html += '<ul>';
        
        if (data.porPrioridad.length > 0) {
            data.porPrioridad.forEach(item => {
                html += `<li>${item.prioridad}: ${item.total}</li>`;
            });
        } else {
            html += '<li>No hay datos</li>';
        }
        
        html += '</ul>';
        
        Swal.fire({
            title: 'Reportes',
            html: html,
            width: '600px',
            showCloseButton: true
        });
    })
    .catch(error => {
        Swal.close();
        Swal.fire('Error', 'Error al cargar reportes', 'error');
        console.error('Error:', error);
    });
}

// ============ FUNCIONES AUXILIARES ============

function limpiarFormulario() {
    $('#formDocumento')[0].reset();
    $('#doc_id').val('');
    $('#prioridad').removeClass('bg-danger bg-warning bg-info bg-secondary text-white');
}

function actualizarBotonesVista(botonActivo) {
    $('.btn-group button').removeClass('btn-secondary').addClass('btn-outline-secondary');
    $(botonActivo).removeClass('btn-outline-secondary').addClass('btn-secondary');
}

function formatearFecha(fecha) {
    if (!fecha) return 'No especificada';
    const opciones = { 
        year: 'numeric', 
        month: '2-digit', 
        day: '2-digit', 
        hour: '2-digit', 
        minute: '2-digit' 
    };
    return new Date(fecha).toLocaleDateString('es-ES', opciones);
}

function getEstadoBadgeClass(estado) {
    const clases = {
        'PENDIENTE': 'warning',
        'EN_PROCESO': 'info',
        'COMPLETADO': 'success',
        'VENCIDO': 'secondary'
    };
    return clases[estado] || 'secondary';
}

function getPrioridadBadgeClass(prioridad) {
    const clases = {
        'URGENTE': 'danger',
        'ALTA': 'warning',
        'MEDIA': 'info',
        'BAJA': 'secondary'
    };
    return clases[prioridad] || 'secondary';
}

function getAccionIcon(accion) {
    const iconos = {
        'CREADO': 'plus-circle',
        'ACTUALIZADO': 'edit',
        'DERIVADO': 'share',
        'INICIADO': 'play',
        'COMPLETADO': 'check-circle',
        'VENCIDO': 'exclamation-triangle',
        'ELIMINADO': 'trash'
    };
    return iconos[accion] || 'circle';
}

function verificarDocumentosVencidos(fecha, elemento) {
    // Esta función puede expandirse para resaltar días con documentos vencidos
    // Por ahora es un placeholder para futuras mejoras
}

// Estilos CSS adicionales
const style = document.createElement('style');
style.textContent = `
    .timeline {
        position: relative;
        padding-left: 30px;
        margin-top: 20px;
    }
    
    .timeline:before {
        content: '';
        position: absolute;
        left: 9px;
        top: 0;
        height: 100%;
        width: 2px;
        background: #dee2e6;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    
    .timeline-badge {
        position: absolute;
        left: -21px;
        top: 0;
        width: 20px;
        height: 20px;
        background: #fff;
        border: 2px solid #3498db;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }
    
    .timeline-badge i {
        font-size: 10px;
        color: #3498db;
    }
    
    .timeline-content {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        margin-left: 10px;
    }
    
    .estado-vencido {
        opacity: 0.7;
        text-decoration: line-through;
    }
    
    .estado-completado {
        opacity: 0.8;
    }
    
    .cursor-pointer {
        cursor: pointer;
    }
    
    .cursor-pointer:hover {
        background-color: #f8f9fa;
    }
`;
document.head.appendChild(style);