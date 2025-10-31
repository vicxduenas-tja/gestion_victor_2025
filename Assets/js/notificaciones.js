/**
 * Sistema de Notificaciones
 * Gestiona la visualización de notificaciones pendientes (tareas y documentos de conocimiento)
 * Autor: Sistema FELCV Tarija
 */

let notificacionesPendientes = [];
let notificacionActual = 0;
let modalNotificacion;

document.addEventListener('DOMContentLoaded', function () {
    // Inicializar modal de notificaciones
    const modalElement = document.getElementById('modalNotificacion');
    if (modalElement) {
        modalNotificacion = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });

        // Cargar notificaciones al cargar la página
        cargarNotificaciones();

        // Event listener para el botón Aceptar
        document.getElementById('btnAceptarNotificacion').addEventListener('click', function () {
            marcarNotificacionVista();
        });
    }
});

/**
 * Carga todas las notificaciones pendientes del servidor
 */
function cargarNotificaciones() {
    const url = base_url + 'calendario/obtenerNotificaciones';

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                notificacionesPendientes = data;
                notificacionActual = 0;
                mostrarSiguienteNotificacion();
            }
        })
        .catch(error => {
            console.error('Error al cargar notificaciones:', error);
        });
}

/**
 * Muestra la siguiente notificación en el modal
 */
function mostrarSiguienteNotificacion() {
    if (notificacionActual < notificacionesPendientes.length) {
        const notif = notificacionesPendientes[notificacionActual];
        renderizarNotificacion(notif);
        modalNotificacion.show();
    } else {
        // No hay más notificaciones
        modalNotificacion.hide();
    }
}

/**
 * Renderiza el contenido de una notificación en el modal
 */
function renderizarNotificacion(notif) {
    const header = document.getElementById('notificacionHeader');
    const icono = document.getElementById('notificacionIcono');
    const tipoTexto = document.getElementById('notificacionTipoTexto');
    const contenido = document.getElementById('notificacionContenido');

    if (notif.tipo === 'tarea') {
        // Notificación de Tarea Nueva
        header.style.backgroundColor = '#0d6efd'; // Azul
        header.style.color = '#fff';
        icono.textContent = 'assignment';
        tipoTexto.textContent = 'Nueva Tarea Asignada';

        contenido.innerHTML = `
            <div class="alert alert-info">
                <h6 class="mb-2"><strong>Número de Documento:</strong></h6>
                <p class="mb-3">${notif.numero_documento}</p>

                <h6 class="mb-2"><strong>Asunto:</strong></h6>
                <p class="mb-3">${notif.asunto}</p>

                <h6 class="mb-2"><strong>Fecha Límite:</strong></h6>
                <p class="mb-3">${formatearFecha(notif.fecha_limite)}</p>

                <h6 class="mb-2"><strong>Prioridad:</strong></h6>
                <p class="mb-0">
                    <span class="badge ${obtenerBadgePrioridad(notif.prioridad)}">
                        ${notif.prioridad.toUpperCase()}
                    </span>
                </p>
            </div>
            <p class="text-muted small mt-2">
                <i class="material-icons-outlined" style="font-size: 14px;">info</i>
                Por favor, revise esta tarea en el Calendario
            </p>
        `;
    } else if (notif.tipo === 'conocimiento') {
        // Notificación de Documento de Conocimiento
        header.style.backgroundColor = '#198754'; // Verde
        header.style.color = '#fff';
        icono.textContent = 'school';
        tipoTexto.textContent = 'Nuevo Documento de Conocimiento';

        contenido.innerHTML = `
            <div class="alert alert-success">
                <h6 class="mb-2"><strong>Título:</strong></h6>
                <p class="mb-3">${notif.titulo}</p>

                <h6 class="mb-2"><strong>Descripción:</strong></h6>
                <p class="mb-3">${notif.descripcion || 'Sin descripción'}</p>

                <h6 class="mb-2"><strong>Fecha de Publicación:</strong></h6>
                <p class="mb-0">${formatearFecha(notif.fecha_publicacion)}</p>
            </div>
            <p class="text-muted small mt-2">
                <i class="material-icons-outlined" style="font-size: 14px;">info</i>
                Documento disponible para su consulta
            </p>
        `;
    }
}

/**
 * Marca la notificación actual como vista y muestra la siguiente
 */
function marcarNotificacionVista() {
    const notif = notificacionesPendientes[notificacionActual];
    const url = notif.tipo === 'tarea'
        ? base_url + 'calendario/marcarTareaVista'
        : base_url + 'calendario/marcarConocimientoVisto';

    const formData = new FormData();
    formData.append('id_documento', notif.id);

    fetch(url, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.tipo === 'success') {
                // Pasar a la siguiente notificación
                notificacionActual++;
                mostrarSiguienteNotificacion();
            } else {
                console.error('Error al marcar notificación:', data.mensaje);
                // Aún así avanzar a la siguiente
                notificacionActual++;
                mostrarSiguienteNotificacion();
            }
        })
        .catch(error => {
            console.error('Error en la petición:', error);
            // Avanzar de todas formas
            notificacionActual++;
            mostrarSiguienteNotificacion();
        });
}

/**
 * Formatea una fecha en formato legible
 */
function formatearFecha(fecha) {
    if (!fecha) return 'No definida';

    const date = new Date(fecha);
    const opciones = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };

    return date.toLocaleDateString('es-ES', opciones);
}

/**
 * Retorna la clase CSS del badge según la prioridad
 */
function obtenerBadgePrioridad(prioridad) {
    switch (prioridad.toLowerCase()) {
        case 'alta':
            return 'bg-danger';
        case 'media':
            return 'bg-warning text-dark';
        case 'baja':
            return 'bg-secondary';
        default:
            return 'bg-info';
    }
}
