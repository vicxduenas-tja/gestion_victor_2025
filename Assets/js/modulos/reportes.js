document.addEventListener('DOMContentLoaded', function () {
    // Escucha clics en cualquier botón con la clase .btn-detalle-respuesta
    document.body.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-detalle-respuesta')) {
            const userId = e.target.getAttribute('data-id');
            const userName = e.target.getAttribute('data-usuario');
            
            // 1. Mostrar nombre del usuario en el modal
            document.getElementById('modal-usuario-nombre').textContent = `Detalle de Documentos Respondidos por: ${userName}`;

            // 2. Limpiar contenido anterior
            const tbody = document.getElementById('detalleRespuestasBody');
            tbody.innerHTML = '';
            
            // 3. Cargar datos vía AJAX
            const url = base_url + 'Reportes/getDetalleRespuestas';
            
            const formData = new FormData();
            formData.append('id_usuario', userId);

            fetch(url, {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                let contador = 1;
                if (data.length > 0) {
                    data.forEach(doc => {
                        const cumplimientoClass = (doc.cumplimiento == 'RESPONDIDO CON RETRASO') ? 'text-danger fw-bold' : 'text-success';
                        const cumplimientoText = (doc.cumplimiento == 'RESPONDIDO CON RETRASO') ? 'RETRASO' : 'A TIEMPO';
                        
                        const row = `
                            <tr>
                                <td class="text-center">${contador++}.</td>
                                <td class="text-center">${doc.numero_documento}</td>
                                <td>${doc.asunto}</td>
                                <td class="text-center">${doc.fecha_respuesta}</td>
                                <td class="text-center">${doc.archivo_respuesta || 'N/A'}</td>
                                <td class="text-center ${cumplimientoClass}">${cumplimientoText}</td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Este usuario no tiene respuestas registradas.</td></tr>';
                }

                // 4. Mostrar el modal
                const modal = new bootstrap.Modal(document.getElementById('detalleRespuestasModal'));
                modal.show();

            })
            .catch(error => console.error('Error al cargar detalle de respuestas:', error));
        }
    });
});