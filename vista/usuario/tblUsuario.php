<?php
   include("latis/conexionBD.php");

?>

<link rel="stylesheet" type="text/css" href="../Scripts/ext/resources/css/ext-all.css.cgz" />
<link rel="stylesheet" type="text/css" href="../login/vendor/select2/select2.min.css">
<link rel="stylesheet" type="text/css" href="../css/estiloPersonal.css">

<script type="text/javascript" src="../Scripts/ext/adapter/ext/ext-base.js.jgz"></script>
<script type="text/javascript" src="../Scripts/ext/ext-all.js.jgz"></script>
<script type="text/javascript" src="../Scripts/ext/idioma/ext-lang-es.js"></script>
<script type="text/javascript" src="../Scripts/funcionesUtiles.js.php"></script>
<script type="text/javascript" src="../Scripts/base64.js"></script>
<script type="text/javascript" src="../Scripts/funcionesAjax.js.jgz"></script>

<!--===============================================================================================-->
<script src="../login/vendor/sweetalert2/sweetalert2.js"></script>
<!--===============================================================================================-->
<!--
<script type="text/javascript" src="../js/controles.js?rev=<?php echo time(); ?>"></script>
-->
<script type="text/javascript" src="usuario/js/usuario.js?rev=<?php echo time(); ?>"></script>

<div class="col-md-12">
  <div class="box box-warning box-solid">
    <div class="box-header with-border">
      <h3 class="box-title"><b>CATALOGO DE USUARIOS</b></h3>
      <div class="box-tools pull-right">
        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i> </button>
      </div>
    </div>
   
    <div class="box-body">
      <div class="form-group"></div>
      <div class="col-lg-10">
        <div class="input-group">
          <input type="text" class="global_filter form-control" id="global_filter" placeholder="Ingresar dato a buscar">
          <span class="input-group-addon"><i class="fa fa-search"></i></span>
        </div>
      </div>
      <div class="col-lg-2">
         <button class="btn btn-danger" style="width:100%" onclick="abrirModalRegistro()"><i class="glyphicon glyphicon-plus"></i>Nuevo Usuario</button>
      </div>
    </div>
    <table id="tabla_usuario" class="display responsive nowrap" style="width:100%">
      <thead>
        <tr>
          <th>id</th>
          <th>Nombre</th>
          <th>Apellido paterno</th>
          <th>Apellido materno</th>
          <th>Sexo</th>
          <th>Email</th>
          <th>Estatus</th>
          <th>Acción</th>
        </tr>
      </thead>

    </table>
  </div>
  <!-- /.box-body -->
</div>
<!-- /.box -->
</div>

<!--VENTA MODAL REGISTRO NUEVO-->
<form action="">
  <div class="modal fade" id="modal_registro_usuario" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content altura_usuario">
        <div class="modal-header">
          <h3 class="modal-title titulo_registro" id="exampleModalLabel"><b>Registro de Usuario</b></h3>
        </div>
        <div class="modal-body contenido-modal">
          <div class="col-lg-4 div_etiqueta">
            <label for="">Nombre *</label>
            <input type="text" class="form-control" id="txt_nombre" placeholder="Ingrese su Nombre">
          </div>
          <div class="col-lg-4 div_etiqueta">
            <label for="">Apellido Paterno *</label>
            <input type="text" class="form-control" id="txt_apPaterno" placeholder="Ingrese su Apellido paterno">
          </div>
          <div class="col-lg-4 div_etiqueta">
            <label for="">Apellido Materno *</label>
            <input type="text" class="form-control" id="txt_apMaterno" placeholder="Ingrese su Apellido Materno">
          </div>
          <div class="col-lg-4 div_etiqueta">
            <label for="">Genero *</label>
            <select name="" id="txt_genero" style="width: 100%;">
              <option value="1">Femenino</option>
              <option value="2">Masculino</option>
            </select>
          </div>
          <div class="col-lg-8 div_etiqueta">
            <label for="">Email *</label>
            <input type="email" class="form-control" id="txt_email" placeholder="Ingrese su Email">
          </div>
          <div class="col-lg-4 div_etiqueta">
            <label for="">Usuario *</label>
            <input type="text" class="form-control" id="txt_usuario" placeholder="Ingrese el Usuario">
          </div>
          <div class="col-lg-4 div_etiqueta">
            <label for="">Contrase&ntilde;a *</label>
            <input type="password" class="form-control" id="txt_cont1" placeholder="Ingrese su contrase&ntilde;a">
          </div>
          <div class="col-lg-4 div_etiqueta marg_especi">
            <label for="">Repetir la Contrase&ntilde;a *</label>
            <input type="password" class="form-control" id="txt_cont2" placeholder="Repita la contrase&ntilde;a">
          </div>
        </div>

        <div class="modal-footer contenido_footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary" onclick="registrar_usuario()">Guardar</button>
        </div>

      </div>
    </div>
  </div>
</form>

<!--VENTA MODAL MODIFICAR REGISTRO-->
<form action="">
  <div class="modal fade" id="modal_editar_usuario" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content altura_usuario">
        <div class="modal-header">
          <h3 class="modal-title titulo_registro" id="exampleModalLabel"><b>Editar Usuario</b></h3>
        </div>
        <div class="modal-body contenido-modal">
          <div class="col-lg-4 div_etiqueta">
            
            <label for="">Nombre</label>
            <input type="text" class="form-control" id="txt_nombre_editar" placeholder="Ingrese su Nombre" disabled>
          </div>
          <div class="col-lg-4 div_etiqueta">
            <label for="">Apellido Paterno</label>
            <input type="text" class="form-control" id="txt_apPaterno_editar" placeholder="Ingrese su Apellido paterno" disabled>
          </div>
          <div class="col-lg-4 div_etiqueta">
            <label for="">Apellido Materno</label>
            <input type="text" class="form-control" id="txt_apMaterno_editar" placeholder="Ingrese su Apellido Materno" disabled>
          </div>
          <div class="col-lg-4 div_etiqueta">
            <label for="">Genero</label>
            <select name="" id="txt_genero_editar" style="width: 100%;" disabled>
              <option value="1">Femenino</option>
              <option value="2">Masculino</option>
            </select>
          </div>
          <div class="col-lg-8 div_etiqueta">
            <label for="">Email</label>
            <input type="email" class="form-control" id="txt_email_editar" placeholder="Ingrese su Email">
          </div>

          <div class="col-lg-4 div_etiqueta">
            <label for="">Usuario </label>
            <input type="text" class="form-control" id="txt_usuario_editar" placeholder="Ingrese el Usuario">
          </div>
          <div class="col-lg-4 div_etiqueta">
            <label for="">Contrase&ntilde;a</label>
            <input type="password" class="form-control" id="txt_cont1_editar" placeholder="Ingrese su contrase&ntilde;a">
          </div>
          <div class="col-lg-4 div_etiqueta marg_especi">
            <label for="">Repetir la Contrase&ntilde;a</label>
            <input type="password" class="form-control" id="txt_cont2_editar" placeholder="Repita la contrase&ntilde;a">
          </div>
          <br>
          <!--
            <h2 class="titulo_roles">Permisos permitidos</h2>

          <div class="col-lg-4 div_etiqueta">
            <input type="checkbox" id="rol1_editar" name="rol1">
            <label for="rol1">Catalogo</label><br>
          </div>
          <div class="col-lg-4 div_etiqueta">
            <input type="checkbox" id="rol2_editar" name="rol2" value="2">
            <label for="rol2">Operación</label><br>
          </div>
          -->
          <div class="col-lg-12">
            <input type="text" id="txtIdUsuario" hidden>
          </div>
        </div>

        <div class="modal-footer contenido_footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary" onclick="modificar_usuario()">Guardar</button>
        </div>
      </div>
    </div>
  </div>
</form>


<script>
  $(document).ready(function() {
    listar_usuario();
  });
</script>