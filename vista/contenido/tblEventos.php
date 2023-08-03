<?php
session_start();
include("latis/conexionBD.php");

$imagen_demo = "demo.png";
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

<script type="text/javascript" src="contenido/js/eventos.js?rev=<?php echo time(); ?>"></script>



<!--Tabla-->
<div class="col-md-12">
    <div class="box box-warning box-solid">
        <div class="box-header with-border">
            <h3 class="box-title"><b>MODULO DE EVENTOS</b></h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
            </div>
            <!-- /.box-tools -->
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="form-group"></div>
            <div class="col-lg-10">
                <div class="input-group">
                    <input type="text" class="global_filter form-control" id="global_filter"
                        placeholder="Ingresar dato a buscar">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                </div>
            </div>
            <div class="col-lg-2">
                <!--
          <button class="btn btn-danger" style="width:100%" onclick="abrirModalRegistro()"><i class="glyphicon glyphicon-plus"></i>Nuevo Registro</button>
          -->
                <button class="btn btn-danger" style="width:100%" onclick="abrirModalNuevoEvento()"><i
                        class="glyphicon glyphicon-plus"></i>Nuevo Evento</button>
            </div>
        </div>
        <table id="tabla_eventos" class="display responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Titulo del Evento</th>
                    <th>Descripción</th>
                    <th>Fecha public.</th>
                    <th>Fecha fin</th>
                    <th>Situación</th>
                    <th>Acción</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!--VENTA MODAL NUEVO AVISO-->
<div class="modal fade" id="modal_registro_evento" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog tamano-modal-dialog" role="document">
        <div class="modal-content tamano_modal">

            <div class="modal-header">
                <h5 class="modal-title titulo_registro" id="exampleModalLabel">Registro de Eventos</h5>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form class="form-horizontal" id="form_subir_evento" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="titulo" class="col-sm-3 control-label">Titulo *</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="txt_titulo" required name="txt_titulo"
                                        maxlength="50" placeholder="Maximo 50 caract.">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="descr_corta" class="col-sm-3 control-label">Descripción *</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="desc_corta" required name="desc_corta"
                                        maxlength="150" placeholder="Maximo 150 caract.">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="fecha_evento" class="col-sm-3 control-label">Fecha del evento *</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" id="fecha_evento" name="fecha_evento">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="hora_evento" class="col-sm-3 control-label">Hora del evento</label>
                                <div class="col-sm-9">
                                    <input type="time" class="form-control" id="hora_evento" name="hora_evento">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="lugar_evento" class="col-sm-3 control-label">Lugar del Evento *</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="lugar_evento" required name="lugar_evento"
                                        maxlength="100" placeholder="Maximo 100 caract.">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="fecha_aplica" class="col-sm-3 control-label">Publicar apartir del *</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" id="fecha_aplica" name="fecha_aplica">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="fecha_finaliza" class="col-sm-3 control-label">Finaliza publicación
                                    *</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" id="fecha_finaliza" name="fecha_finaliza">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" onclick="registrarEventos()">Guardar</button>
            </div>

        </div>
    </div>
</div>

<!--VENTA MODAL MODIFICAR AVISO-->
<div class="modal fade" id="modal_editar_evento" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog tamano-modal-dialog" role="document">
        <div class="modal-content tamano_modal">

            <div class="modal-header">
                <h5 class="modal-title titulo_registro" id="exampleModalLabel">Modificar Eventos</h5>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form class="form-horizontal" id="form_subir_evento" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="titulo" class="col-sm-3 control-label">Titulo *</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="txt_modificar_titulo" required name="txt_modificar_titulo"
                                        maxlength="50" placeholder="Maximo 50 caract.">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="descr_corta" class="col-sm-3 control-label">Descripción *</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="desc_corta_modificar" required name="desc_corta_modificar"
                                        maxlength="150" placeholder="Maximo 150 caract.">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="fecha_evento" class="col-sm-3 control-label">Fecha del evento *</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" id="fecha_evento_modificar" name="fecha_evento_modificar">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="hora_evento" class="col-sm-3 control-label">Hora del evento</label>
                                <div class="col-sm-9">
                                    <input type="time" class="form-control" id="hora_evento_modificar" name="hora_evento_modificar">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="lugar_evento" class="col-sm-3 control-label">Lugar del Evento *</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="lugar_evento_modificar" required name="lugar_evento_modificar"
                                        maxlength="100" placeholder="Maximo 100 caract.">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="fecha_aplica" class="col-sm-3 control-label">Publicar apartir del *</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" id="fecha_aplica_modificar" name="fecha_aplica_modificar">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="fecha_finaliza" class="col-sm-3 control-label">Finaliza publicación *</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" id="fecha_finaliza_modificar" name="fecha_finaliza_modificar">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <input type="text" id="txtIdEvento" name="txtIdEvento" hidden>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" onclick="modificarEventos()">Guardar</button>
            </div>

        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    listar_eventos();
});
</script>