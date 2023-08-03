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

<script type="text/javascript" src="contenido/js/noticias.js?rev=<?php echo time(); ?>"></script>



<!--Tabla-->
<div class="col-md-12">
    <div class="box box-warning box-solid">
        <div class="box-header with-border">
            <h3 class="box-title"><b>MODULO DE NOTICIAS</b></h3>
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
                <button class="btn btn-danger" style="width:100%" onclick="abrirModalNuevaNoticia()"><i
                        class="glyphicon glyphicon-plus"></i>Nueva Noticia</button>
            </div>
        </div>
        <table id="tabla_noticias" class="display responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Titulo de la noticia</th>
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

<!--VENTA MODAL NUEVA NOTICIA-->
<div class="modal fade" id="modal_registro_noticia" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog tamano-modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title titulo_registro" id="exampleModalLabel">Registro de Noticias</h5>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form class="form-horizontal" id="form_subir_noticia" enctype="multipart/form-data">
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
                            <div class="form-group">
                                <label for="cmb_area" class="col-sm-3 control-label">Area Publicación *</label>
                                <div class="col-sm-9">
                                    <select class="js-example-basic-single" name="cmb_areas" id="cmb_areas"
                                        style="width: 100%;">
                                        <?php
                                            $Consulta="SELECT idArea,nombreArea FROM 11_cat_areas WHERE situacion='1'";
                                            $con->generarOpcionesSelect($Consulta);
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" onclick="registrarNuevaNoticia()">Guardar</button>
            </div>

        </div>
    </div>
</div>

<!--VENTA MODAL MODIFICAR AVISO-->
<div class="modal fade" id="modal_modificar_noticia" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog tamano-modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title titulo_registro" id="exampleModalLabel">Modificar Noticias</h5>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form class="form-horizontal" id="form_subir_noticia" enctype="multipart/form-data">
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
                                <label for="fecha_aplica" class="col-sm-3 control-label">Publicar apartir del *</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" id="fecha_aplica_modificar" name="fecha_aplica_modificar">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="fecha_finaliza" class="col-sm-3 control-label">Finaliza publicación
                                    *</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" id="fecha_finaliza_modificar" name="fecha_finaliza_modificar">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="cmb_area" class="col-sm-3 control-label">Area Publicación *</label>
                                <div class="col-sm-9">
                                    <select class="js-example-basic-single" name="cmb_areas_modificar" id="cmb_areas_modificar"
                                        style="width: 100%;">
                                        <?php
                                            $Consulta="SELECT idArea,nombreArea FROM 11_cat_areas WHERE situacion='1'";
                                            $con->generarOpcionesSelect($Consulta);
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <input type="text" id="txtIdNoticias" name="txtIdNoticias" hidden>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" onclick="registrarModificacionNoticia()">Guardar</button>
            </div>

        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    listar_noticias();
});
</script>