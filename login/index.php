<!DOCTYPE html>
<html lang="en">

<head>
	<title>Ingreso | Administración</title>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">	
	
	<!--===============================================================================================-->
	<link rel="icon" type="image/png" href="images/icons/favicon.ico" />
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/iconic/css/material-design-iconic-font.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/animsition/css/animsition.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/daterangepicker/daterangepicker.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" type="text/css" href="css/main.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="../css/estiloPersonal.css">

	<script type="text/javascript" src="../Scripts/funcionesUtiles.js.php"></script>
	<script type="text/javascript" src="../Scripts/base64.js"></script>
	<script type="text/javascript" src="../Scripts/funcionesAjax.js.jgz"></script>

</head>

<body>

	<div class="limiter">
		<div class="container-login100" style="background-image: url('images/bg-01.jpg');">
			<div class="wrap-login100 p-l-55 p-r-55 p-t-65 p-b-20">
				<span class="login100-form-title p-b-5">
					INICIAR SESI&Oacute;N
				</span>
				<span class="login100-form-title p-b-30">
					<a href="../index.php">Ir a la Pagina principal del Sindicato</a>
				</span>


				<div class="wrap-input100 validate-input m-b-23" data-validate="El Usuario es requerido">
					<span class="label-input100">Usuario</span>
					<input class="input100" type="text" name="username" placeholder="Escriba el Usuario" id="txtUsuario">
					<span class="focus-input100" data-symbol="&#xf206;"></span>
				</div>

				<div class="wrap-input100 validate-input" data-validate="La Contraseña es requerida">
					<span class="label-input100">Contrase&ntilde;a</span>
					<input class="input100" type="password" name="pass" placeholder="Escriba la Contrase&ntilde;a" id="txtPasswd">
					<span class="focus-input100" data-symbol="&#xf190;"></span>
				</div>
				<!--
				<div class="text-right p-t-8 p-b-31">
					<a href="#" onclick="abrirModalRestablecer()">
						¿Olvidaste la Contrase&ntilde;a?
					</a>
				</div>
				-->
				<div class="container-login100-form-btn mt-5">
					<div class="wrap-login100-form-btn">
						<div class="login100-form-bgbtn"></div>
						<button class="login100-form-btn" onclick="autentificar()">
							INGRESAR
						</button>
					</div>
				</div><br>
				<!--
				<div class="flex-c-m">
					<a href="#" class="login100-social-item bg1">
						<i class="fa fa-facebook"></i>
					</a>

					<a href="#" class="login100-social-item bg2">
						<i class="fa fa-twitter"></i>
					</a>

					<a href="#" class="login100-social-item bg3">
						<i class="fa fa-google"></i>
					</a>
				</div>
				-->
			</div>
		</div>
	</div>


	<div id="dropDownSelect1"></div>

	<!--MODAL RESTABLECER CONTRASEÑA-->
	<div class="modal fade" id="modal_restablecer_contra" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h3 class="modal-title titulo_registro" id="exampleModalLabel"><b>Restablecer contrase&ntilde;a</b></h3>
				</div>
				<div class="modal-body">
					<div class="col-lg-12 div_etiqueta">
						<label for=""><b>Ingrese el Email registrado en el usuario para enviarle su contrase&ntilde;a restablecida</b></label>
						<input type="text" class="form-control" id="txt_email" placeholder="Ingrese su Email">
					</div>

				</div>

				<div class="modal-footer contenido_footer">
					<button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
					<button class="btn btn-primary" onclick="restablecer_contra()">Enviar</button>
				</div>
			</div>
		</div>
	</div>

	<!--===============================================================================================-->
	<script src="vendor/sweetalert2/sweetalert2.js"></script>
	<!--===============================================================================================-->

	<!--===============================================================================================-->
	<script src="vendor/jquery/jquery-3.2.1.min.js"></script>
	<!--===============================================================================================-->
	<script src="vendor/animsition/js/animsition.min.js"></script>
	<!--===============================================================================================-->
	<script src="vendor/bootstrap/js/popper.js"></script>
	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
	<!--===============================================================================================-->
	<script src="vendor/select2/select2.min.js"></script>
	<!--===============================================================================================-->
	<script src="vendor/daterangepicker/moment.min.js"></script>
	<script src="vendor/daterangepicker/daterangepicker.js"></script>
	<!--===============================================================================================-->
	<script src="vendor/countdowntime/countdowntime.js"></script>
	<!--===============================================================================================-->
	<script src="js/main.js"></script>

	<script type="text/javascript" src="../js/funcionAjax2.js"></script>
	<script type="text/javascript" src="../js/acceso.js.php"></script>
	<script type="text/javascript" src="../js/controles.js"></script>
</body>
<script>
	txtUsuario.focus();
</script>

</html>