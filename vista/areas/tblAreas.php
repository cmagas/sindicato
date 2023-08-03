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
<script type="text/javascript" src="areas/js/areas.js?rev=<?php echo time(); ?>"></script>

<div class="col-md-12">
    <div class="box box-warning box-solid">
        <div class="box-header with-border">
            <h3 class="box-title"><b>CATALOGO DE AREAS</b></h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="form-group"></div>
            <div class="col-lg-10">
                <div class="input-group">
                    <input type="text" class="global_filter form-control" id="global_filter" placeholder="Ingresar dato a buscar">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                </div>
            </div>
            <div class="col-lg-2">
                <button class="btn btn-danger" style="width:100%" onclick="abrirModalRegistro()"><i class="glyphicon glyphicon-plus"></i>Nueva Area</button>
            </div>
        </div>
        <table id="tabla_areas" class="display responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Nombre area</th>
                    <th>Correo principal</th>
                    <th>Estatus</th>
                    <th>Acción</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!--VENTA MODAL REGISTRO NUEVO-->
<form action="">
    <div class="modal fade" id="modal_registro_areas" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title titulo_registro" id="exampleModalLabel"><b>Registro de Areas</b></h3>
                </div>
                <div class="modal-body tamano_modal_area">
                    <div class="col-lg-12 div_etiqueta">
                        <label for="">Nombre del Area *</label>
                        <input type="text" class="form-control" id="txt_nombreArea" placeholder="Ingrese el nombre del Area">
                    </div>
                    <div class="col-lg-12 div_etiqueta">
                        <label for="">Email principal</label>
                        <input type="email" class="form-control" id="txt_emailArea" placeholder="Ingrese su Email">
                    </div>
                </div>
                <div class="modal-footer contenido_footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" onclick="registrar_areas()">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</form>

<!--VENTA MODAL MODIFICAR REGISTRO-->
<form action="">
  <div class="modal fade" id="modal_editar_area" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title titulo_registro" id="exampleModalLabel"><b>Editar Usuario</b></h3>
        </div>
        <div class="modal-body tamano_modal_area">
          <div class="col-lg-12 div_etiqueta">
            
            <label for="">Nombre del Area *</label>
            <input type="text" class="form-control" id="txt_nombre_editar_area" placeholder="Ingrese el Nombre del Area" disabled>
          </div>
          <div class="col-lg-12 div_etiqueta">
            <label for="">Email principal</label>
            <input type="email" class="form-control" id="txt_email_editar_area" placeholder="Ingrese su Email">
          </div>
          <div class="col-lg-12">
            <input type="text" id="txtIdArea" hidden>
          </div>
        </div>

        <div class="modal-footer contenido_footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary" onclick="modificar_areas()">Guardar</button>
        </div>
      </div>
    </div>
  </div>
</form>


<script>
    $(document).ready(function() {
        listar_areas();
    });
</script>