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



<script type="text/javascript" src="contenido/js/avisos.js?rev=<?php echo time(); ?>"></script>

<script type="text/javascript" src="contenido/js/avisosNuevos.js?rev=<?php echo time(); ?>"></script>





<!--Tabla-->

<div class="col-md-12">

    <div class="box box-warning box-solid">

        <div class="box-header with-border">

            <h3 class="box-title"><b>MODULO DE AVISOS</b></h3>

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

                <button class="btn btn-danger" style="width:100%" onclick="abrirModalNuevoAviso()"><i

                        class="glyphicon glyphicon-plus"></i>Nuevo Aviso</button>

            </div>

        </div>

        <table id="tabla_aviso" class="display responsive nowrap" style="width:100%">

            <thead>

                <tr>

                    <th>id</th>

                    <th>Titulo de aviso</th>

                    <th>Area public.</th>

                    <th>Fecha public.</th>

                    <th>Fecha fin</th>

                    <th>Tiene Doc.</th>

                    <th>Situación</th>

                    <th>Acción</th>

                </tr>

            </thead>

        </table>

    </div>

</div>





<!--VENTA MODAL NUEVO AVISO-->

<div class="modal fade" id="modal_registro_aviso" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"

    aria-hidden="true">

    <div class="modal-dialog tamano-modal-dialog" role="document">

        <div class="modal-content tamano_modal">



            <div class="modal-header">

                <h5 class="modal-title titulo_registro" id="exampleModalLabel">Registro de Aviso</h5>

            </div>



            <div class="modal-body">

                <div class="row">

                    <div class="col-md-12">



                        <form class="form-horizontal" id="form_subir" enctype="multipart/form-data">

                            <div class="form-group">

                                <label for="titulo" class="col-sm-3 control-label">Titulo *</label>

                                <div class="col-sm-9">

                                    <input type="text" class="form-control" id="titulo" required name="titulo"

                                        maxlength="50" placeholder="Maximo 50 caract.">

                                </div>

                            </div>

                            <div class="form-group">

                                <label for="descr_corta" class="col-sm-3 control-label">Descripción corta *</label>

                                <div class="col-sm-9">

                                    <input type="text" class="form-control" id="desc_corta" required name="desc_corta"

                                        maxlength="100" placeholder="Maximo 100 caract.">

                                </div>

                            </div>

                            <div class="form-group">

                                <label for="descripcion" class="col-sm-3 control-label">Descripción larga *</label>

                                <div class="col-sm-9">

                                    <textarea class="form-control " rows="5" id="desc_larga" required

                                        name="desc_larga"></textarea>

                                </div>

                            </div>

                            <div class="form-group">

                                <label for="descr_corta" class="col-sm-3 control-label">Adjuntar documento *

                                    (.PDF)</label>

                                <div class="col-sm-9">

                                    <input type="file" class="form-control" id="id_file" name="id_file">

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

                <button class="btn btn-primary" onclick="registrarAviso()">Guardar</button>

            </div>



        </div>

    </div>

</div>



<!--VISUALZIAR DOCUMENTO-->

<div class="modal fade" id="modalPdf" tabindex="-1" aria-labelledby="modalPdf" aria-hidden="true">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title titulo_registro" id="exampleModalLabel">Visualización de Documento</h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                    <span aria-hidden="true">&times;</span>

                </button>

            </div>

            <div class="modal-body">

                <iframe id="iframePDF" frameborder="0" scrolling="no" width="100%" height="500px"></iframe>

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>

            </div>

        </div>

    </div>

</div>





<script>

$(document).ready(function() {

    listar_avisos();

});

</script>