<?php include_once 'Views/template/header.php'; ?>


<div class="container">
    <div class="row">
        <div class="col">
            <div class="page-description">
                <h1><?php echo $data['title']; ?></h1>
            </div>
        </div>
        <div class="col-md-12">
            <button class="btn btn-outline-primary mb-3" type="button" id="btnNuevo">Nuevo Usuario</button>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover display nowrap" id="tblUsuarios" style="width: 100%">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Id</th>
                                    <th>Nombres</th>
                                    <th>Correo</th>
                                    <th>Telefono</th>
                                    <th>Direccion</th>
                                    <th>Oficina</th>
                                    <th>Cargo</th>
                                    <th>Perfil</th>
                                    <th>F. registro</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalRegistro" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="title"></h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <form id="formulario" autocomplete="off">
                    <input type="hidden" id="id_usuario" name="id_usuario">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="nombre">Nombre</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="material-icons">
                                            list
                                        </i></span>
                                    <input class="form-control" type="text" id="nombre" name="nombre" placeholder="Nombre">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="apellido">Apellido</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="material-icons">
                                            list
                                        </i>
                                    </span>
                                    <input class="form-control" type="text" id="apellido" name="apellido" placeholder="Apellido" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="correo">Correo</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="material-icons">
                                            email
                                        </i>
                                    </span>
                                    <input class="form-control" type="email" id="correo" name="correo" placeholder="ejemplo@correo.com" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required title="Ingrese un correo válido">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="telefono">Telefono</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="material-icons">
                                            phone
                                        </i>
                                    </span>
                                    <input class="form-control" type="text" id="telefono" name="telefono" placeholder="Ej: 71234567" pattern="[67][0-9]{7}" maxlength="8" required title="Ingrese numero telefonico valido">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label for="direccion">Direccion</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="material-icons">
                                            location_on
                                        </i>
                                    </span>
                                    <input class="form-control" type="text" id="direccion" name="direccion" placeholder="Direccion" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="clave">Clave</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="material-icons">
                                            lock
                                        </i>
                                    </span>
                                    <input class="form-control" type="password" id="clave" name="clave" placeholder="Contraseña" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="rol">Rol</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="material-icons">
                                            person
                                        </i>
                                    </span>
                                    <select name="rol" id="rol" class="form-control" required>
                                        <option value="1">ADMINISTRADOR</option>
                                        <option value="2">USUARIO</option>
                                    </select> 
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="id_oficina">Oficina Asignada</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="material-icons">
                                            business
                                        </i>
                                    </span>
                                    <select name="id_oficina" id="id_oficina" class="form-control" required>
                                        <option value="">Sin oficina asignada</option>
                                    </select> 
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="cargo">Cargo</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="material-icons">
                                            badge
                                        </i>
                                    </span>
                                    <select name="cargo" id="cargo" class="form-control" required>
                                        <option value="">Seleccionar cargo</option>
                                        <option value="Encargado de Oficina">Encargado de Oficina</option>
                                        <option value="Auxiliar">Auxiliar</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="material-icons">
                                save
                            </i>Guardar
                        </button>
                        <button class="btn btn-outline-danger" type="button" data-bs-dismiss="modal">
                            <i class="material-icons">
                                cancel
                            </i>Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script src="<?php echo BASE_URL; ?>Assets/pages/usuarios.js?v=<?php echo time(); ?>"></script>

<?php include_once 'Views/template/footer.php'; ?>