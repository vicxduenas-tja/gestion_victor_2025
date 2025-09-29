
const id_carpeta = document.querySelector('#id_carpeta');
let tbl;
document.addEventListener('DOMContentLoaded', function () {
    tbl = $('#tblDetalle').DataTable({
        ajax: {
            url: base_url + 'admin/listardetalle/' + id_carpeta.value,
            dataSrc: ''
        },
        columns: [
            { data: 'acciones' },
            { data: 'correo' },
            { data: 'nombre' },
            { data: 'estado' }
        ],
        language : {
            url: 'https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json'
        },
        responsive: true,
        destroy: true,
        order: [[1, 'desc']],
    });
})

function eliminarDetalle(id) {  
    const url = base_url + 'archivos/eliminarCompartido/' + id;
    eliminarRegistro('¿Está seguro de eliminar el archivo?', 'El archivo compartido se eliminara de forma permanente en 30 dias', 'si eliminar',url, tbl);
}