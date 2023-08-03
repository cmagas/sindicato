<?php
//phpinfo();
	error_reporting(E_ALL);
	include_once("latis/conexionBD.php");

	$fechaA=date("Y-m-d");

	$consulta="SELECT idEvento,titulo,descripcion,fechaEvento,hora,lugar FROM 202_eventos 
				WHERE situacion=1 ORDER BY fechaEvento DESC LIMIT 0,2";
	$res=$con->obtenerFilas($consulta);

	$consultaN="SELECT idNoticia,titulo,descripcion,fechaPublicacion,n.idArea,c.nombreArea 
			FROM 203_noticias n, 11_cat_areas c WHERE n.idArea=c.idArea AND '".$fechaA."' BETWEEN fechaPublicacion 
			AND fechaFin AND n.situacion='1' ORDER BY fechaPublicacion DESC";
	$resN=$con->obtenerFilas($consultaN);

	$consultaG="SELECT * FROM 204_galeria WHERE situacion='1' ORDER BY fechaRegistro,idGaleria DESC";
	$resG=$con->obtenerFilas($consultaG);

	
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">	
	<title>SIDEPEV</title>
	<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700&display=swap" rel="stylesheet">
	
	<link rel="stylesheet" type="text/css" href="css/lightbox.css">
	<link rel="stylesheet" type="text/css" href="css/flexslider.css">
	<link rel="stylesheet" type="text/css" href="css/owl.carousel.css">
	<link rel="stylesheet" type="text/css" href="css/owl.theme.default.css">
	<link rel="stylesheet" type="text/css" href="css/jquery.rateyo.css"/>
	
	<!-- <link rel="stylesheet" type="text/css" href="css/jquery.mmenu.all.css" /> -->
	<!-- <link rel="stylesheet" type="text/css" href="css/meanmenu.min.css"> -->
	<link rel="stylesheet" type="text/css" href="css/inner-page-style.css">
	<link rel="stylesheet" type="text/css" href="css/style.css">

	<!-- Style of the plugin -->
	
	<link rel="stylesheet" href="pluginWhats/components/Font Awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="pluginWhats/whatsapp-chat-support.css">

	<link rel="stylesheet" type="text/css" href="css/estiloPersonal.css">
</head>
<body>
	<div id="page" class="site">
		<!-- Button Whatsapp Structure -->
		<div class="whatsapp_chat_support wcs_fixed_right" id="button-w">
			<div class="wcs_button_label">
					Contáctanos
			</div> 

			<div class="wcs_button wcs_button_circle">
				<span class="fa fa-whatsapp"></span>
			</div>  

			<div class="wcs_popup">
				<div class="wcs_popup_close">
					<span class="fa fa-close"></span>
				</div>
				<div class="wcs_popup_header">
					<span class="fa fa-whatsapp"></span>
					<strong>Servicio de Atención</strong>
					
					<div class="wcs_popup_header_description">¿Necesidad de ayuda? Chatea con nosotros en Whatsapp, de Lunes a Viernes de 9:00 a 15:00 hrs.</div>

				</div>  
				<div class="wcs_popup_input" 
					data-number="522281189000"
					data-availability='{ "monday":"09:00-15:00", "tuesday":"09:00-15:00", "wednesday":"09:00-15:00", "thursday":"09:00-15:00", "friday":"09:00-15:00" }'>
					<input type="text" placeholder="Escribir su pregunta!" />
					<i class="fa fa-play"></i>
				</div>
				<div class="wcs_popup_avatar">
					<img src="images/testi_03.png" alt="img-avatar">
				</div>
			</div>
		</div>		
			
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
					<div class="logo-wrap" itemprop="logo">
						<img src="images/logoSindicato.jpeg" alt="Logo Image" width="70">
					</div>
					<div class="nav-wrap">
						<nav class="nav-desktop">
							<ul class="menu-list">
								<li><a href="index.php">Inicio</a></li>
								<li><a href="about.html">Historia</a></li>
								<li><a href="avisos.php">Avisos</a></li>
								<li><a href="eventos.php">Eventos</a></li>
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
			<div class="owl-four owl-carousel" itemprop="image">
				<img src="images/ComiteEjecutivo2.jpg" alt="Img_banner">
				<!--
				<img src="images/page-banner2.jpg" alt="Img_banner">
				<img src="images/page-banner3.jpg" alt="Img_banner">
				-->
			</div>
			<div class="container tituloPrin" itemprop="description">
				<h1>Sindicato Democratico de Empleados del Poder Ejecutivo de Veracruz</h1>
				<h3>SIDEPEV</h3>
			</div>
			 <div id="owl-four-nav" class="owl-nav"></div>
		</div>

		<!--Galeria-->
		<section class="page-heading">
			<div class="container">
				<h2>Galeria</h2>
			</div>
		</section>

		<section class="gallery-images-section">
			<?php
					while($filag=mysql_fetch_row($resG))
					{
						$imagen="vista/contenido/galeria/image/".$filag[6];
						$titulo=$filag[3];
			?>
					<div class="gallery-img-wrap">
						<a href="<?php echo $imagen?>" data-lightbox="example-set" data-title="Click a la derech de la Imagen para cambiar.">
							<img src="<?php echo $imagen?>" alt="gallery-images" class="contenido_img_princ">
						</a>
					</div>
			<?php
					}
			?>
		</section>
		<!-- End of gallery Images -->

		<!--Proximos ventos-->
		<section class="page-heading">
			<div class="container">
				<h2>Proximos eventos</h2>
			</div>
		</section>

		<section class="events-section">
			<div class="container">
				<?php
					if($res)
					{
						while($fila=mysql_fetch_row($res))
						{
							$titulo=ucfirst(strtolower($fila[1]));
							$descripcion=ucfirst(strtolower($fila[2]));
							$descripcion2=htmlspecialchars ($descripcion);
							$fechaE=cambiarFormatoFecha($fila[3]);
							$horaE=$fila[4];
							$lugarE=ucfirst(strtolower($fila[5]));
				?>
				<div class="event-wrap">
					<div class="img-wrap" itemprop="image">
						<img src="images/eventos3.jpg" alt="event images" height="40">
					</div>
					<div class="details">
						<a href=""><h3 itemprop="name"><?php echo $titulo?></h3></a>
						<p itemprop="description"><?php echo $descripcion2?></p>
						<h5 itemprop="startDate"><i class="far fa-clock"></i> <?php echo $fechaE ?> | <?php echo $horaE?></h5>
						<h5 itemprop="location"><i class="fas fa-map-marker-alt"></i> <?php echo $lugarE ?></h5>
					</div>
				</div>

				<?php
						}
					}else{
				?>

				<div class="event-wrap">
					<div class="img-wrap" itemprop="image">
						<img src="images/events.jpg" alt="event images">
					</div>
					<div class="details">
						<a href=""><h3 itemprop="name">Orientation Programme for new Students.</h3></a>
						<p itemprop="description">Orientation Programme for new sffs Students. Orientation Programme for new sffs Students. Orientation Programme for new sffs Students.</p>
						<h5 itemprop="startDate"><i class="far fa-clock"></i> Dec 30,2018 | 11am</h5>
						<h5 itemprop="location"><i class="fas fa-map-marker-alt"></i> Hotel Malla, Lainchaur</h5>
					</div>
				</div>

				<div class="event-wrap">
					<div class="img-wrap" itemprop="image">
						<img src="images/events.jpg" alt="event images">
					</div>
					<div class="details">
						<a href=""><h3 itemprop="name">Orientation Programme for new Students.</h3></a>
						<p itemprop="description">Orientation Programme for new sffs Students. Orientation Programme for new sffs Students. Orientation Programme for new sffs Students.</p>
						<h5 itemprop="startDate"><i class="far fa-clock"></i> Dec 30,2018 | 11am</h5>
						<h5 itemprop="location"><i class="fas fa-map-marker-alt"></i> Hotel Malla, Lainchaur</h5>
					</div>
				</div>
				<?php
					}
				?>

			</div>
		</section>
		
		<!--Comite Ejecutivo-->
		<section class="what-other-say">
			<div class="container">
				<h4 class="article-subtitle">Conoce nuestro</h4>
				<h2 class="head">Comite Ejecutivo</h2>
				<div class="three owl-carousel owl-theme">
					<div class="customer-item">
						<div class="border">
							<div class="customer">
								<figure>
									<img class="customer-img" src="images/comite/1_MariaDoraRosalesMoreno.jpeg" alt="Person image">
									<figcaption>
										<span itemprop="author">LIC. MARIA DORA ROSALES MORENO</span><br>
										<span itemprop="author">Secretaría General</span><br>
										<span itemprop="author">Contacto: 2281 205032</span>
										<div class="rateYo" itemprop="ratingValue"></div>
									</figcaption>
								</figure>
							</div>

						</div>
					</div>
					<div class="customer-item">
						<div class="border">
							<div class="customer">
								<figure>
									<img class="customer-img" src="images/comite/2_GamalielLascanoMoreno.jpeg" alt="Person image">
									<figcaption>
										<span itemprop="author">LIC. GAMALIEL LASCANO MORENO</span><br>
										<span itemprop="author">Secretaría de Trabajos y Conflictos</span><br>
										<span itemprop="author">Contacto: 2283 462736</span>
										<div class="rateYo" itemprop="ratingValue"></div>
									</figcaption>
								</figure>
							</div>

						</div>
					</div>
					<div class="customer-item">
						<div class="border">
							<div class="customer">
								<figure>
									<img class="customer-img" src="images/comite/3_CarlosGustavoRodriguezCado.jpeg" alt="Person image">
									<figcaption>
										<span itemprop="author">C. CARLOS GUSTAVO RODRIGUEZ CADO</span><br>
										<span itemprop="author">Secretaría de Organización</span><br>
										<span itemprop="author">Contacto: 2281 083302</span>
										<div class="rateYo" itemprop="ratingValue"></div>
									</figcaption>
								</figure>
							</div>

						</div>
					</div>
					<div class="customer-item">
						<div class="border">
							<div class="customer">
								<figure>
									<img class="customer-img" src="images/comite/8JoseFelipeLopezGonzalez.jpeg" alt="Person image">
									<figcaption>
										<span itemprop="author">C. JOSE FELIPE LOPEZ GONZALEZ</span><br>
										<span itemprop="author">Secretaría de Previsión Social</span><br>
										<span itemprop="author">Contacto: 2281 043639</span>
										<div class="rateYo" itemprop="ratingValue"></div>
									</figcaption>
								</figure>
							</div>

						</div>
					</div>
					<div class="customer-item">
						<div class="border">
							<div class="customer">
								<figure>
									<img class="customer-img" src="images/comite/11_RosalbaLopezSesena.jpeg" alt="Person image">
									<figcaption>
										<span itemprop="author">LIC. ROSALBA LOPEZ SESEÑA</span><br>
										<span itemprop="author">Secretaría de Afiliación</span><br>
										<span itemprop="author">Contacto: 2282 122973</span>
										<div class="rateYo" itemprop="ratingValue"></div>
									</figcaption>
								</figure>
							</div>

						</div>
					</div>
					<div class="customer-item">
						<div class="border">
							<div class="customer">
								<figure>
									<img class="customer-img" src="images/comite/4_VictorNovasHernandez.jpg" alt="Person image">
									<figcaption>
										<span itemprop="author">MTRO. VICTOR NOVAS HERNANDEZ</span><br>
										<span itemprop="author">Secretaría de Finanzas</span><br>
										<span itemprop="author">Contacto: 2281 261445</span>
										<div class="rateYo" itemprop="ratingValue"></div>
									</figcaption>
								</figure>
							</div>

						</div>
					</div>
					<div class="customer-item">
						<div class="border">
							<div class="customer">
								<figure>
									<img class="customer-img" src="images/comite/5_JosedeJesusCendonMejorada.jpeg" alt="Person image">
									<figcaption>
										<span itemprop="author">JOSE DE JESUS CENDON MEJORADA</span><br>
										<span itemprop="author">Secretaría de Acción Política</span>
										<div class="rateYo" itemprop="ratingValue"></div>
									</figcaption>
								</figure>
							</div>

						</div>
					</div>
					<div class="customer-item">
						<div class="border">
							<div class="customer">
								<figure>
									<img class="customer-img" src="images/comite/6_CarolVianeyLaraSosa.jpeg" alt="Person image">
									<figcaption>
										<span itemprop="author">CAROL VIANEY LARA SOSA</span><br>
										<span itemprop="author">Secretaría de Acción Cultural</span>
										<div class="rateYo" itemprop="ratingValue"></div>
									</figcaption>
								</figure>
							</div>

						</div>
					</div>
					<div class="customer-item">
						<div class="border">
							<div class="customer">
								<figure>
									<img class="customer-img" src="images/comite/7_LidiaFernandezPalmeros.jpeg" alt="Person image">
									<figcaption>
										<span itemprop="author">LIDIA FERNANDEZ PALMEROS</span><br>
										<span itemprop="author">Secretaría de Acción Femenil</span>
										<div class="rateYo" itemprop="ratingValue"></div>
									</figcaption>
								</figure>
							</div>

						</div>
					</div>	
					<div class="customer-item">
						<div class="border">
							<div class="customer">
								<figure>
									<img class="customer-img" src="images/comite/9_RaulMoralesFlandes.jpeg" alt="Person image">
									<figcaption>
										<span itemprop="author">RAUL MORALES FLANDES</span><br>
										<span itemprop="author">Secretaría de Prensa y Propaganda</span>
										<div class="rateYo" itemprop="ratingValue"></div>
									</figcaption>
								</figure>
							</div>

						</div>
					</div>
					<div class="customer-item">
						<div class="border">
							<div class="customer">
								<figure>
									<img class="customer-img" src="images/comite/10_RebecaGutierrezOtero.jpeg" alt="Person image">
									<figcaption>
										<span itemprop="author">REBECA GUTIERREZ OTERO</span><br>
										<span itemprop="author">Secretaría de Actas y Acuerdos</span>
										<div class="rateYo" itemprop="ratingValue"></div>
									</figcaption>
								</figure>
							</div>

						</div>
					</div>
					
					<div class="customer-item">
						<div class="border">
							<div class="customer">
								<figure>
									<img class="customer-img" src="images/comite/12MarioRodriguezAlcantara.jpeg" alt="Person image">
									<figcaption>
										<span itemprop="author">MARIO RODRIGUEZ ALCANTARA</span><br>
										<span itemprop="author">Secretaría de Deportes</span>
										<div class="rateYo" itemprop="ratingValue"></div>
									</figcaption>
								</figure>
							</div>

						</div>
					</div>
					<div class="customer-item">
						<div class="border">
							<div class="customer">
								<figure>
									<img class="customer-img" src="images/comite/13_JoseLuisLazcanoMoreno.jpeg" alt="Person image">
									<figcaption>
										<span itemprop="author">JOSE LUIS LAZCANO MORENO</span><br>
										<span itemprop="author">Secretario Asesor</span>
										<div class="rateYo" itemprop="ratingValue"></div>
									</figcaption>
								</figure>
							</div>

						</div>
					</div>																																													
				</div>
			</div>
		</section>
		<!-- Fin Comite Ejecutivo -->

		<!--Noticias-->
		<section class="page-heading">
			<div class="container">
				<h2>Noticias</h2>
			</div>
		</section>

		<section class="latest-news">
			<div class="container" itemprop="event" >
				<div class="owl-two owl-carousel">
					<?php
						if($resN)
						{
							while($row=mysql_fetch_row($resN))
							{
								$titulo=ucfirst(strtolower($row[1]));
								$descripcion=ucfirst(strtolower($row[2]));
								$fechaPub=cambiarFormatoFecha($row[3]);
								$area=$row[5];
								//$nomArea=obtenerNombreAreas($area);
					?>
								<div class="news-wrap" itemprop="event">
									<div class="news-img-wrap" itemprop="image">
										<img src="images/fondoNoticia.jpg" alt="Latest News Images">
									</div>
									<div class="news-detail" itemprop="description">
										<a href=""><h1><?php echo $titulo?></h1></a>
										<h2 itemprop="startDate">Por: <?php echo $area?>  | <?php echo $fechaPub?></h2>

										<p><?php echo $descripcion?></p>
									</div>
								</div>
					<?php
							}


						}else{
						
					
					?>
					<div class="news-wrap" itemprop="event">
						<div class="news-img-wrap" itemprop="image">
							<img src="images/latest-new-img.jpg" alt="Latest News Images">
						</div>
						<div class="news-detail" itemprop="description">
							<a href=""><h1>Orientation Programme for new Students.</h1></a>
							<h2 itemprop="startDate">By Admin | 20 Dec. 2018</h2>

							<p>Orientation Programme for new sffs Students. Orientatin Programmes for new Students.. Orientatin Programmes for new Students</p>
						</div>
					</div>
					<div class="news-wrap" itemprop="event">
						<div class="news-img-wrap" itemprop="image">
							<img src="images/latest-new-img.jpg" alt="Latest News Images">
						</div>
						<div class="news-detail" itemprop="description">
							<a href=""><h1>Orientation Programme for new Students.</h1></a>
							<h2 itemprop="startDate">By Admin | 20 Dec. 2018</h2>

							<p>Orientation Programme for new sffs Students. Orientatin Programmes for new Students.. Orientatin Programmes for new Students</p>
						</div>
					</div>
					<?php
						}
					?>
					
				</div>
			</div>
		</section>
		<!-- Latest News CLosed -->

		<!--Seccion contacto-->
		<section class="query-section">
			<div class="container">
				<p>¿Alguna pregunta? Contactanos por Telefono:<a href="tel:+2288146496"><i class="fas fa-phone"></i> +228 814 6496</a></p>
			</div>
		</section>
		<!-- Fin Seccion contacto -->

		<footer class="page-footer" itemprop="footer">
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
	<script type="text/javascript" src="js/isotope.pkgd.min.js"></script>
	<script type="text/javascript" src="js/owl.carousel.js"></script>
	<script type="text/javascript" src="js/jquery.flexslider.js"></script>
	<script type="text/javascript" src="js/jquery.rateyo.js"></script>

	<!-- <script type="text/javascript" src="js/jquery.mmenu.all.js"></script> -->
	<!-- <script type="text/javascript" src="js/jquery.meanmenu.min.js"></script> -->
	<script type="text/javascript" src="js/custom.js"></script>

	<!-- jQuery 1.8+ 
	<script src="pluginWhats/components/jQuery/jquery-1.11.3.min.js"></script>
	-->
	<!-- Plugin JS file -->
	<script src="pluginWhats/components/moment/moment.min.js"></script>
	<script src="pluginWhats/components/moment/moment-timezone-with-data.min.js"></script> <!-- spanish language (es) -->
	<script src="pluginWhats/whatsapp-chat-support.js"></script>

	<script>
		$('#button-w').whatsappChatSupport({
			defaultMsg : '',
		});
	</script>
</body>
</html>