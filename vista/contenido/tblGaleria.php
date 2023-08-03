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



<script type="text/javascript" src="contenido/js/galeria.js?rev=<?php echo time(); ?>"></script>







<!--Tabla-->

<div class="col-md-12">

    <div class="box box-warning box-solid">

        <div class="box-header with-border">

            <h3 class="box-title"><b>MODULO DE GALERIA</b></h3>

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

                <button class="btn btn-danger" style="width:100%" onclick="abrirModalNuevaImagen()"><i

                        class="glyphicon glyphicon-plus"></i>Nueva Imagen</button>

            </div>

        </div>

        <table id="tabla_galeria" class="display responsive nowrap" style="width:100%">

            <thead>

                <tr>

                    <th>id</th>

                    <th>Titulo</th>

                    <th>Fecha public.</th>

                    <th>Situación</th>

                    <th>Acción</th>

                </tr>

            </thead>

        </table>

    </div>

</div>





<!--VENTA MODAL NUEVO Galeria-->

<div class="modal fade" id="modal_registro_galeria" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"

    aria-hidden="true">

    <div class="modal-dialog" role="document">

        <div class="modal-content">



            <div class="modal-header">

                <h5 class="modal-title titulo_registro" id="exampleModalLabel">Subir Imagen</h5>

            </div>



            <div class="modal-body tamano_modal_galeria_alto">

                <form class="form-horizontal" id="form_subir" enctype="multipart/form-data">

                    <div class="col-lg-12 div_etiqueta">

                        <label for="txt_titulo">Titulo de la Imagen*</label>

                        <input type="text" class="form-control" id="txt_titulo" name="txt_titulo" placeholder="Ingrese el Titulo" required>

                    </div>

                    <div class="col-lg-12 div_etiqueta">

                        <label for="id_file">Adjuntar imagen * (.jpg, .png, .gif) (Tamaño recomendado 900 x 900 px)</label>

                        <input type="file" class="form-control" id="id_file" name="id_file" accept="image/*">

                    </div>

                </form>

            </div>

 

            <div class="modal-footer">

                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>

                <button class="btn btn-primary" onclick="registrarImagenGaleria()">Guardar</button>

            </div>



        </div>

    </div>

</div>









<script>

$(document).ready(function() {

    listar_imagenes();

});

</script>