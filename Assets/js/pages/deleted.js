
let tblArchivos; 

document.addEventListener('DOMContentLoaded', function () {
    //cargar datos con datatables
    tblArchivos = $('#tblArchivos').DataTable({
        ajax: {
            url: base_url + 'archivos/listarHistorial',
            dataSrc: ''
        },
        columns: [
            { data: 'accion' },
            { data: 'id' },
            { data: 'nombre' },
            { data: 'tipo' },
            { data: 'fecha_create' },
            { data: 'elimina' }
        ],
        language : {
            url: 'https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json'
        },
        responsive: true,
        order: [[1, 'desc']],
    });

}); 

function restaurar(id){
    const url = base_url + 'archivos/delete/' + id;
    eliminarRegistro('¿Está seguro de restaurar archivo?', 'El archivo se restaurara en el mismo directorio', 'si restaurar',url, tblArchivos);
}


