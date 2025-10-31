<?php include_once 'Views/template/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Header diferente con gradiente -->
            <div class="card shadow-sm">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-0"><i class="fas fa-folder-open"></i> Gestión de Hojas de Ruta</h3>
                            <small>Sistema de seguimiento de documentación oficial</small>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-light" id="btnNuevaHoja">
                                <i class="fas fa-plus-circle"></i> Nueva Hoja de Ruta
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filtros rápidos -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm active" onclick="filtrarHojasRuta('todas')">Todas</button>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="filtrarHojasRuta('en_proceso')">En Proceso</button>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="filtrarHojasRuta('completado')">Completadas</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="filtrarHojasRuta('archivado')">Archivadas</button>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla con diseño Bootstrap moderno -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="10%">N° Registro</th>
                                    <th width="12%">Fecha Recepción</th>
                                    <th width="30%">Asunto</th>
                                    <th width="18%">Oficina Destino</th>
                                    <th width="10%">Prioridad</th>
                                    <th width="10%">Estado</th>
                                    <th width="10%" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaHojasRuta">
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        No hay hojas de ruta registradas
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva Hoja de Ruta -->
    <div class="modal fade" id="modalNuevaHojaRuta" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title">Nueva Hoja de Ruta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="row">
                        <!-- Columna Izquierda: Formulario -->
                        <div class="col-md-5">
                            <form id="formNuevaHojaRuta" enctype="multipart/form-data">
                                <div class="mb-2">
                                    <label class="form-label">N° de Registro *</label>
                                    <input type="text" class="form-control form-control-sm" id="numero_registro" readonly>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Fecha Recepción *</label>
                                        <input type="date" class="form-control form-control-sm" id="fecha_recepcion" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Fecha Límite *</label>
                                        <input type="date" class="form-control form-control-sm" id="fecha_limite" required>
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" id="sin_limite">
                                            <label class="form-check-label" for="sin_limite">
                                                Sin límite (Para Conocimiento)
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Remitente</label>
                                    <input type="text" class="form-control form-control-sm" id="remitente" placeholder="Nombre de quien envía">
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Asunto *</label>
                                    <textarea class="form-control form-control-sm" id="asunto" rows="2" required></textarea>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Oficina Destino *</label>
                                    <select class="form-control form-control-sm" id="oficina_destino" required>
                                        <option value="">Seleccione oficina...</option>
                                        <option value="0">Todas las Oficinas (Para Conocimiento)</option>
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Prioridad *</label>
                                    <select class="form-control form-control-sm" id="prioridad" required>
                                        <option value="media">Media</option>
                                        <option value="baja">Baja</option>
                                        <option value="alta">Alta</option>
                                        <option value="urgente">Urgente</option>
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Documento Adjunto (PDF) *</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control form-control-sm" id="archivo_adjunto" accept=".pdf" required onchange="previsualizarPDF()">
                                        <button type="button" class="btn btn-outline-danger btn-sm" id="btnQuitarArchivo" style="display: none;" onclick="quitarArchivo()" title="Quitar archivo">
                                            <span class="material-icons" style="font-size: 18px; vertical-align: middle;">close</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Observaciones</label>
                                    <textarea class="form-control form-control-sm" id="observaciones" rows="2"></textarea>
                                </div>
                            </form>
                        </div>

                        <!-- Columna Derecha: Previsualizador -->
                        <div class="col-md-7">
                            <div class="border rounded p-2 bg-light h-100">
                                <h6 class="text-center mb-2">Vista Previa del Documento</h6>
                                <iframe id="previsualizadorPDF" style="width: 100%; height: 550px; border: 1px solid #ddd; border-radius: 4px; background: white;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-info" id="btnPublicarPC" onclick="publicarParaConocimiento()" disabled>
                        <i class="fas fa-book"></i> Publicar P.C.
                    </button>
                    <button type="button" class="btn btn-primary" id="btnGuardarDelegar" onclick="guardarHojaRuta()" disabled>
                        <i class="fas fa-save"></i> Guardar y Delegar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Detalles (Solo Lectura) -->
<div class="modal fade" id="modalDetallesHoja" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt me-2"></i>Detalle de Hoja de Ruta 
                    <span class="badge bg-success ms-2">DELEGADO</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="row">
                    <!-- Columna Izquierda: Información -->
                    <div class="col-md-4">
                        <table class="table table-sm table-borderless" style="font-size: 0.9rem;">
                            <tr>
                                <td width="40%"><strong>N° Registro:</strong></td>
                                <td id="detNumero"></td>
                            </tr>
                            <tr>
                                <td><strong>Recepción:</strong></td>
                                <td id="detFechaRecepcion"></td>
                            </tr>
                            <tr>
                                <td><strong>Límite:</strong></td>
                                <td id="detFechaLimite"></td>
                            </tr>
                            <tr>
                                <td><strong>Remitente:</strong></td>
                                <td id="detRemitente"></td>
                            </tr>
                            <tr>
                                <td><strong>Prioridad:</strong></td>
                                <td><span id="detPrioridad" class="badge"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Estado:</strong></td>
                                <td><span id="detEstado" class="badge"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Oficina:</strong></td>
                                <td id="detOficina" style="font-size: 0.8rem;"></td>
                            </tr>
                        </table>
                        <hr class="my-2">
                        <div style="font-size: 0.9rem;">
                            <strong>Asunto:</strong>
                            <div id="detAsunto" class="p-2 bg-light rounded mb-2" style="max-height: 80px; overflow-y: auto;"></div>
                            <strong>Observaciones:</strong>
                            <div id="detObservaciones" class="p-2 bg-light rounded text-muted" style="max-height: 60px; overflow-y: auto;"></div>
                        </div>
                    </div>
                    
                    <!-- Columna Derecha: Visor PDF -->
                    <div class="col-md-8">
                        <h6 class="text-center mb-2">Documento Adjunto</h6>
                        <iframe id="detVisorPDF" style="width: 100%; height: 550px; border: 1px solid #ddd; border-radius: 4px;"></iframe>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    var oficinasYaCargadas = false;

    // Función que controla el estado del modal según la selección
    function actualizarEstadoModal() {
        var oficinaSeleccionada = document.getElementById('oficina_destino').value;
        var sinLimiteCheckbox = document.getElementById('sin_limite');
        var fechaLimiteInput = document.getElementById('fecha_limite');
        var btnPublicar = document.getElementById('btnPublicarPC');
        var btnDelegar = document.getElementById('btnGuardarDelegar');

        // Lógica principal: La Oficina de Destino decide qué botón se activa
        if (oficinaSeleccionada === '0') {
            // Es "Para Conocimiento"
            btnPublicar.disabled = false;
            btnDelegar.disabled = true;

            // Forzar "Sin Límite"
            sinLimiteCheckbox.checked = true;
            sinLimiteCheckbox.disabled = true;
            fechaLimiteInput.disabled = true;
            fechaLimiteInput.value = '';
            fechaLimiteInput.required = false;

        } else if (oficinaSeleccionada === '') {
            // No se ha seleccionado nada
            btnPublicar.disabled = true;
            btnDelegar.disabled = true;
            sinLimiteCheckbox.disabled = false;

        } else {
            // Es una oficina específica
            btnPublicar.disabled = true;
            btnDelegar.disabled = false;
            sinLimiteCheckbox.disabled = false;
            fechaLimiteInput.required = true;
        }

        // Lógica secundaria: El checkbox "Sin Límite" controla la fecha
        if (oficinaSeleccionada !== '0') {
             fechaLimiteInput.disabled = sinLimiteCheckbox.checked;
             if (sinLimiteCheckbox.checked) {
                fechaLimiteInput.value = '';
                fechaLimiteInput.required = false;
             } else {
                fechaLimiteInput.required = true;
             }
        }
    }

    // Función para quitar el archivo seleccionado
    function quitarArchivo() {
        var inputArchivo = document.getElementById('archivo_adjunto');
        inputArchivo.value = '';
        document.getElementById('previsualizadorPDF').src = '';
        document.getElementById('btnQuitarArchivo').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        cargarHojasRuta();

        // Abrir modal
        document.getElementById('btnNuevaHoja').addEventListener('click', function() {
            if (!oficinasYaCargadas) {
                cargarOficinas();
                oficinasYaCargadas = true;
            }
            // Limpiar y resetear formulario
            document.getElementById('formNuevaHojaRuta').reset();
            quitarArchivo();

            cargarNumeroRegistro();
            document.getElementById('fecha_recepcion').valueAsDate = new Date();

            // Resetear la lógica del modal
            actualizarEstadoModal();

            var modal = new bootstrap.Modal(document.getElementById('modalNuevaHojaRuta'));
            modal.show();
        });

        // Event listeners para las validaciones
        document.getElementById('oficina_destino').addEventListener('change', actualizarEstadoModal);
        document.getElementById('sin_limite').addEventListener('change', actualizarEstadoModal);
    });
    // Cargar oficinas
    function cargarOficinas() {
        fetch('<?php echo BASE_URL; ?>hojaruta/listarOficinas')
            .then(response => response.json())
            .then(data => {
                var select = document.getElementById('oficina_destino');
                // Mantener las primeras dos opciones y limpiar el resto
                select.options.length = 2; // Mantiene "Seleccione oficina..." y "Todas las Oficinas"
                data.forEach(oficina => {
                    var option = document.createElement('option');
                    option.value = oficina.id;
                    option.textContent = oficina.nombre;
                    select.appendChild(option);
                });
            });
    }

    // Cargar número de registro automático
    function cargarNumeroRegistro() {
        fetch('<?php echo BASE_URL; ?>hojaruta/obtenerNumeroRegistro')
            .then(response => response.json())
            .then(data => {
                document.getElementById('numero_registro').value = data.numero;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('numero_registro').value = '0001';
            });
    }

// Guardar y delegar hoja de ruta automáticamente
    function guardarHojaRuta() {
        var formData = new FormData();
        formData.append('numero_registro', document.getElementById('numero_registro').value);
        formData.append('fecha_recepcion', document.getElementById('fecha_recepcion').value);
        formData.append('remitente', document.getElementById('remitente').value);
        formData.append('asunto', document.getElementById('asunto').value);
        formData.append('oficina_destino', document.getElementById('oficina_destino').value);
        formData.append('fecha_limite', document.getElementById('fecha_limite').value);
        formData.append('prioridad', document.getElementById('prioridad').value);
        formData.append('observaciones', document.getElementById('observaciones').value);
        formData.append('archivo', document.getElementById('archivo_adjunto').files[0]);
        
        if (!document.getElementById('archivo_adjunto').files[0]) {
            alertaPersonalizada('warning', 'Debe adjuntar un archivo PDF');
            return;
        }
        
        fetch('<?php echo BASE_URL; ?>hojaruta/guardarYDelegarDirecto', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alertaPersonalizada(data.tipo, data.mensaje);
            
            if (data.tipo === 'success') {
                var modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaHojaRuta'));
                modal.hide();
                document.getElementById('formNuevaHojaRuta').reset();
                document.getElementById('previsualizadorPDF').src = '';
                cargarHojasRuta();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alertaPersonalizada('error', 'Error al guardar');
        });
    }

    // Cargar lista de hojas de ruta
    function cargarHojasRuta() {
        fetch('<?php echo BASE_URL; ?>hojaruta/listar/' + filtroActual)
            .then(response => response.json())
            .then(data => {
                var tbody = document.getElementById('tablaHojasRuta');
                tbody.innerHTML = '';

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox fa-3x mb-3 d-block"></i>No hay hojas de ruta registradas</td></tr>';
                    return;
                }

                data.forEach(hr => {
                    var badgePrioridad = hr.prioridad === 'urgente' ? 'danger' :
                        hr.prioridad === 'alta' ? 'warning' :
                        hr.prioridad === 'media' ? 'info' : 'secondary';

                    var badgeEstado = hr.estado === 'completado' ? 'success' :
                        hr.estado === 'en_proceso' ? 'primary' : 'warning';

                    var tr = `
                    <tr>
                        <td>${hr.numero_registro}</td>
                        <td>${hr.fecha_recepcion}</td>
                        <td>${hr.asunto}</td>
                        <td>${hr.oficina_destino}</td>
                        <td><span class="badge bg-${badgePrioridad}">${hr.prioridad.toUpperCase()}</span></td>
                        <td><span class="badge bg-${badgeEstado}">${hr.estado}</span></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-primary" onclick="verDetalleHoja(${hr.id})" title="Ver detalles">
                                <span class="material-icons" style="font-size: 18px; vertical-align: middle;">visibility</span>
                            </button>
                        </td>
                    </tr>
                `;
                    tbody.innerHTML += tr;
                });
            });

    }

    // Previsualizar PDF antes de subir
    function previsualizarPDF() {
        var archivo = document.getElementById('archivo_adjunto').files[0];

        if (archivo && archivo.type === 'application/pdf') {
            var url = URL.createObjectURL(archivo);
            document.getElementById('previsualizadorPDF').src = url;
            // Mostrar botón para quitar archivo
            document.getElementById('btnQuitarArchivo').style.display = 'inline-block';
        } else {
            document.getElementById('previsualizadorPDF').src = '';
            document.getElementById('btnQuitarArchivo').style.display = 'none';
            if (archivo) {
                alertaPersonalizada('warning', 'Solo se permiten archivos PDF');
                document.getElementById('archivo_adjunto').value = '';
            }
        }
    }

    // Variable global para el filtro actual
var filtroActual = 'todas';

// Filtrar hojas de ruta por estado
function filtrarHojasRuta(filtro) {
    filtroActual = filtro;
    
    // Actualizar botones activos
    document.querySelectorAll('.btn-group button').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Recargar con filtro
    cargarHojasRuta();
}

// Ver detalle de hoja de ruta (solo lectura)
function verDetalleHoja(id) {
    fetch('<?php echo BASE_URL; ?>hojaruta/obtenerDetalle/' + id)
        .then(response => response.json())
        .then(data => {
            // Llenar información
            document.getElementById('detNumero').textContent = data.numero_registro;
            document.getElementById('detFechaRecepcion').textContent = data.fecha_recepcion;
            document.getElementById('detFechaLimite').textContent = data.fecha_limite;
            document.getElementById('detRemitente').textContent = data.remitente || 'No especificado';
            document.getElementById('detOficina').textContent = data.oficina_destino;
            document.getElementById('detAsunto').textContent = data.asunto;
            document.getElementById('detObservaciones').textContent = data.observaciones || 'Sin observaciones';
            
            // Badge prioridad
            var badgePrioridad = document.getElementById('detPrioridad');
            badgePrioridad.textContent = data.prioridad.toUpperCase();
            badgePrioridad.className = 'badge';
            if (data.prioridad === 'urgente') badgePrioridad.classList.add('bg-danger');
            else if (data.prioridad === 'alta') badgePrioridad.classList.add('bg-warning');
            else if (data.prioridad === 'media') badgePrioridad.classList.add('bg-info');
            else badgePrioridad.classList.add('bg-secondary');
            
            // Badge estado
            var badgeEstado = document.getElementById('detEstado');
            badgeEstado.textContent = data.estado.replace('_', ' ').toUpperCase();
            badgeEstado.className = 'badge';
            if (data.estado === 'completado') badgeEstado.classList.add('bg-success');
            else if (data.estado === 'en_proceso') badgeEstado.classList.add('bg-primary');
            else badgeEstado.classList.add('bg-secondary');
            
            // Cargar PDF (buscar en carpeta según estado)
            var carpeta = data.estado === 'en_proceso' ? 'en_proceso' : 
                         data.estado === 'completado' ? 'completados' : 'archivado';
            document.getElementById('detVisorPDF').src = '<?php echo BASE_URL; ?>Assets/documentos_oficiales/' + carpeta + '/' + data.archivo_adjunto;
            
            // Mostrar modal
            var modal = new bootstrap.Modal(document.getElementById('modalDetallesHoja'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alertaPersonalizada('error', 'Error al cargar detalles');
        });
}
</script>

<?php include_once 'Views/template/footer.php'; ?>