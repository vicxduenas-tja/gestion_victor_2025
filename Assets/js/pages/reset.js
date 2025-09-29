

const formulario = document.querySelector('#formulario');
const clave_nueva = document.querySelector('#clave_nueva');
const clave_confirmar = document.querySelector('#clave_confirmar');

formulario.addEventListener('DOMContentLoaded', function () {
    frmPass.addEventListener('submit', function (e) {
        e.preventDefault();
        if (clave_nueva.value == '' || clave_confirmar.value == '') {
            alertaPersonalizada('warning', 'Todos los campos son requeridos');
        } else {
            if (clave_nueva.value != clave_confirmar.value) {
                alertaPersonalizada('warning', 'Las contraseñas no coinciden');
            } else {
                const data = new FormData(formulario);
                const http = new XMLHttpRequest();
                const url = base_url + 'principal/cambiarPass';
                http.open("POST", url, true);
                http.send(data);
                http.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        const res = JSON.parse(this.responseText);
                        alertaPersonalizada(res.tipo, res.mensaje);
                        if (res.tipo == 'success') {
                            setTimeout(() => {
                                window.location = base_url;
                            }, 1500);
                        }
                    }
                };
            }
        }
    })

})    