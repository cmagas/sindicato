<?php
	include_once("latis/conexionBD.php");

	$fechaA=date("Y-m-d");

	$consulta="SELECT idEvento,titulo,descripcion,fechaEvento,hora,lugar FROM 202_eventos 
		WHERE situacion=1 ORDER BY fechaEvento DESC";
	$res=$con->obtenerFilas($consulta);
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">	
	<title>SIDEPEV</title>
	<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
	<link rel="stylesheet" type="text/css" href="css/all.css">
	<link rel="stylesheet" type="text/css" href="css/all.min.css">
	<link rel="stylesheet" type="text/css" href="css/lightbox.css">
	<link rel="stylesheet" type="text/css" href="css/flexslider.css">
	<link rel="stylesheet" type="text/css" href="css/owl.carousel.css">
	<link rel="stylesheet" type="text/css" href="css/owl.theme.default.css">
	<link rel="stylesheet" type="text/css" href="css/jquery.rateyo.css"/>
	<link rel="stylesheet" type="text/css" href="css/jquery.mmenu.all.css" />
	<link rel="stylesheet" type="text/css" href="css/inner-page-style.css">
	<link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
	<div id="page" class="site">
		<header class="site-header">
			<div class="top-header">
				<div class="container">
					<div class="top-header-left">
						<div class="top-header-block">
							<a href="mailto:sidepev.nvo@hotmail.com"><i class="fas fa-envelope"></i>sidepev.nvo@hotmail.com</a>
						</div>
						<div class="top-header-block">
							<a href="tel:+2288146496"><i class="fas fa-phone"></i> +228 814 6496</a>
						</div>
					</div>
					<div class="top-header-right">
						<div class="social-block">
							<ul class="social-list">
								<li><a href=""><i class="fab fa-viber"></i></a></li>
								<li><a href=""><i class="fab fa-google-plus-g"></i></a></li>
								<li><a href=""><i class="fab fa-facebook-square"></i></a></li>
								<li><a href=""><i class="fab fa-facebook-messenger"></i></a></li>
								<li><a href=""><i class="fab fa-twitter"></i></a></li>
								<li><a href=""><i class="fab fa-skype"></i></a></li>
							</ul>
						</div>
						<div class="login-block">
							<a href="login/index.php">Ingresar</a>
							<!--
							<a href="">Registro</a>
							-->
						</div>
					</div>
				</div>
			</div>
			<!-- Top header Close -->
			<div class="main-header">
				<div class="container">
					<div class="logo-wrap">
						<img src="images/logoSindicato.jpeg" alt="Logo Image" width="70">
					</div>
					<div class="nav-wrap">
						<nav class="nav-desktop">
							<ul class="menu-list">
								<li><a href="index.php">Inicio</a></li>
								<li><a href="about.html">Historia</a></li>
								<li><a href="avisos.php">Avisos</a></li>
								<li><a href="galeria.php">Galeria</a></li>
								<li><a href="contacto.html">Contacto</a></li>
							</ul>
						</nav>
						<div id="bar">
							<i class="fas fa-bars"></i>
						</div>
						<div id="close">
							<i class="fas fa-times"></i>
						</div>
					</div>
				</div>
			</div>
		</header>
		<!-- Header Close -->
		<div class="banner">
			<div class="owl-four owl-carousel">
				<img src="images/page-banner.jpg" alt="Image of Bannner">
				<img src="images/page-banner2.jpg" alt="Image of Bannner">
				<img src="images/page-banner3.jpg" alt="Image of Bannner">
			</div>
			<div class="container">
				<h1>Proximos Eventos</h1>
				<h3>Sindicato Democratico de Empleados del Poder Ejecutivo de Veracruz</h3>
			</div>
			 <div id="owl-four-nav" class="owl-nav"></div>
		</div>
		
		<!-- Banner Close -->
		<section class="page-heading">
			<div class="container">
				<h2>Proximos Eventos</h2>
			</div>
		</section>
		<section class="upcomming events-section">
			<div class="container">
				<?php
					if($res)
					{
						while($fila=mysql_fetch_row($res))
						{
							//idEvento,titulo,descripcion,fechaEvento,hora,lugar
							$titulo=$fila[1];
							$descripcion=$fila[2];
							$fechaEvento=$fila[3];
							$horaEvento=$fila[4];
							$lugar=$fila[5];
							
				?>
				<div class="event-wrap">
					<div class="img-wrap">
						<img src="images/eventos3.jpg" alt="event images">
					</div>
					<div class="details">
						<a href=""><h3><?php echo $titulo?></h3></a>
						<p><?php echo $descripcion?></p>

						<h5><i class="far fa-clock"></i> <?php echo $fechaEvento?> | <?php echo $horaEvento?></h5>
						<h5><i class="fas fa-map-marker-alt"></i> <?php echo $lugar?></h5>
					</div>
				</div>
				<?php
					}
						}
					else
					{
				?>
				<div class="event-wrap">
					<div class="img-wrap">
						<img src="images/events.jpg" alt="event images">
					</div>
					<div class="details">
						<a href=""><h3>Orientation Programme for new Students.</h3></a>
						<p>Orientation Programme for new sffs Students. Orientation Programme for new sffs Students. Orientation Programme for new sffs Students.</p>

						<h5><i class="far fa-clock"></i> Dec 30,2018 | 11am</h5>
						<h5><i class="fas fa-map-marker-alt"></i> Hotel Malla, Lainchaur</h5>
					</div>
				</div>

				<div class="event-wrap">
					<div class="img-wrap">
						<img src="images/events.jpg" alt="event images">
					</div>
					<div class="details">
						<a href=""><h3>Orientation Programme for new Students.</h3></a>
						<p>Orientation Programme for new sffs Students. Orientation Programme for new sffs Students. Orientation Programme for new sffs Students.</p>

						<h5><i class="far fa-clock"></i> Dec 30,2018 | 11am</h5>
						<h5><i class="fas fa-map-marker-alt"></i> Hotel Malla, Lainchaur</h5>
					</div>
				</div>
				<?php
					}
				?>
	

			</div>
		</section>
		<!--
		<section class="button-section">
			<div class="container">
				<a href="#" class="button">Mas Eventos</a>
			</div>
		</section>
		-->
		<!-- Upcomming Events Closed -->
		<section class="query-section">
			<div class="container">
				<p>¿Alguna pregunta? Contactanos por Telefono:<a href="tel:+2288146496"><i class="fas fa-phone"></i> +228 814 6496</a></p>
			</div>
		</section>
		<!-- End of Query Section -->
		<footer class="page-footer" itemprop="footer" itemscope itemtype="http://schema.org/WPFooter">
			<div class="footer-first-section">
				<div class="container">
					<div class="box-wrap" itemprop="about">
						<header>
							<h1>Conocenos</h1>
						</header>
						<p>Sindicato Democrático de Empleados del Poder Ejecutivo de Veracruz <br>( 2019 - 2025)</p>

						<h4><a href="tel:+9779813639131"><i class="fas fa-phone"></i> +228 814 6496</a></h4>
						<h4><a href="mailto:sidepev.nvo@hotmail.com"><i class="fas fa-envelope"></i> sidepev.nvo@hotmail.com</a></h4>
						<h4><a href=""><i class="fas fa-map-marker-alt"></i>Jalisco #68 Col. Progreso, Xalapa,Ver.</a></h4>
					</div>

					<div class="box-wrap">
						<img src="images/logoSindicato.jpeg" alt="logo">
						<!--
							<header>
								<h1>Contactanos por Email</h1>
							</header>
							<section class="quick-contact">
								<input type="email" name="email" placeholder="Tu Email*">
								<textarea placeholder="Mensaje*"></textarea>
								<button>Enviar mensaje</button>
							</section>
						-->
					</div>

				</div>
			</div>
			<!-- End of box-Wrap -->
			<div class="footer-second-section">
				<div class="container">
					<hr class="footer-line">
					<ul class="social-list">
						<li><a href=""><i class="fab fa-facebook-square"></i></a></li>
						<li><a href=""><i class="fab fa-twitter"></i></a></li>
						<li><a href=""><i class="fab fa-skype"></i></a></li>
						<li><a href=""><i class="fab fa-youtube"></i></a></li>
						<li><a href=""><i class="fab fa-instagram"></i></a></li>
					</ul>
					<hr class="footer-line">
				</div>
			</div>
			<div class="footer-last-section">
				<div class="container">
					<p>Copyright 2022 &copy; sgtecno.com <span> | </span> Diseñada y desarrollada por <a href="https://sgtecno.com">SGTecno</a></p>
				</div>
			</div>
		</footer>
	</div>
	<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
	<script type="text/javascript" src="js/lightbox.js"></script>
	<script type="text/javascript" src="js/all.js"></script>
	<script type="text/javascript" src="js/owl.carousel.js"></script>
	<script type="text/javascript" src="js/jquery.flexslider.js"></script>
	<script type="text/javascript" src="js/jquery.rateyo.js"></script>
	<script type="text/javascript" src="js/jquery.mmenu.all.js"></script>
	<script type="text/javascript" src="js/custom.js"></script>
</body>
</html>
