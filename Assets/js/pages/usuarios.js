const frm = document.querySelector('#formulario');
const btnNuevo = document.querySelector('#btnNuevo');
const title = document.querySelector('#title');
const modalRegistro = document.querySelector('#modalRegistro');
const myModal = new bootstrap.Modal(modalRegistro);

let tblUsuarios;
let oficinasYaCargadas = false;

document.addEventListener('DOMContentLoaded', function () {
    if ($.fn.DataTable.isDataTable('#tblUsuarios')) {
        $('#tblUsuarios').DataTable().destroy();
    }

    tblUsuarios = $('#tblUsuarios').DataTable({
        ajax: {
            url: base_url + 'usuarios/listar',
            dataSrc: ''
        },
        columns: [
            { data: 'acciones' },
            { data: 'id' },
            { data: 'nombres' },
            { data: 'correo' },
            { data: 'telefono' },
            { data: 'direccion' },
            { data: 'nombre_oficina', defaultContent: 'Sin asignar' },
            { data: 'cargo', defaultContent: '-' },
            {
                data: 'perfil',
                render: function (data, type, row) {
                    if (row.rol == 1) return '<span class="badge bg-danger">SUPER ADMIN</span>';
                    if (row.rol == 2) return '<span class="badge bg-primary">USUARIO</span>';
                    return data || '-';
                }
            },
            { data: 'fecha' }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json'
        },
        responsive: false,
        scrollX: true,
        order: [[1, 'desc']],
        destroy: true
    });

    btnNuevo.addEventListener('click', function () {
        title.textContent = 'NUEVO USUARIO';
        frm.id_usuario.value = '';
        frm.reset();
        frm.clave.removeAttribute('readonly');

        if (!oficinasYaCargadas) {
            cargarOficinas();
            oficinasYaCargadas = true;
        }

        myModal.show();
    });

    frm.addEventListener('submit', function (e) {
        e.preventDefault();

        // Validar campos vacíos
        if (frm.nombre.value === '' || frm.apellido.value === '' || frm.correo.value === '' ||
            frm.telefono.value === '' || frm.direccion.value === '' || frm.clave.value === '' ||
            frm.rol.value === '' || frm.id_oficina.value === '' || frm.cargo.value === '') {
            alertaPersonalizada('warning', 'TODOS LOS CAMPOS SON REQUERIDOS');
            return;
        }

        // Validar teléfono (8 dígitos, empieza con 6 o 7)
        const telRegex = /^[67]\d{7}$/;
        if (!telRegex.test(frm.telefono.value)) {
            alertaPersonalizada('warning', 'El teléfono debe tener 8 dígitos y empezar con 6 o 7');
            return;
        }

        // Validar correo
        const emailRegex = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
        if (!emailRegex.test(frm.correo.value)) {
            alertaPersonalizada('warning', 'Ingrese un correo electrónico válido');
            return;
        }

        // Si todo está bien, enviar formulario
        const data = new FormData(frm);
        const http = new XMLHttpRequest();
        const url = base_url + 'usuarios/guardar';
        http.open("POST", url, true);
        http.send(data);
        http.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                const res = JSON.parse(this.responseText);
                alertaPersonalizada(res.tipo, res.mensaje);
                if (res.tipo == 'success') {
                    frm.reset();
                    myModal.hide();
                    tblUsuarios.ajax.reload();
                }
            }
        };
    });
});

function eliminar(id) {
    const url = base_url + 'usuarios/delete/' + id;
    eliminarRegistro('¿Está seguro de eliminar este usuario?', 'El usuario no se eliminara de forma permanente', 'si eliminar', url, tblUsuarios);
}

function editar(id) {
    const http = new XMLHttpRequest();
    const url = base_url + 'usuarios/editar/' + id;
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            title.textContent = 'EDITAR USUARIO';
            frm.id_usuario.value = res.id;
            frm.nombre.value = res.nombre;
            frm.apellido.value = res.apellido;
            frm.correo.value = res.correo;
            frm.telefono.value = res.telefono;
            frm.direccion.value = res.direccion;
            frm.clave.value = '000000000';
            frm.clave.setAttribute('readonly', 'readonly');
            frm.rol.value = res.rol;
            frm.id_oficina.value = res.id_oficina || '';
            frm.cargo.value = res.cargo || '';

            if (!oficinasYaCargadas) {
                cargarOficinas();
                oficinasYaCargadas = true;
            }

            myModal.show();
        }
    };
}

function cargarOficinas() {
    const http = new XMLHttpRequest();
    const url = base_url + 'usuarios/listarOficinas';
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const oficinas = JSON.parse(this.responseText);
            const select = document.getElementById('id_oficina');
            select.innerHTML = '<option value="">Sin oficina asignada</option>';
            oficinas.forEach(oficina => {
                const option = document.createElement('option');
                option.value = oficina.id;
                option.textContent = oficina.nombre;
                select.appendChild(option);
            });
        }
    };
}