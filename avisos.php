<?php
	include_once("latis/conexionBD.php");

	$fechaA=date("Y-m-d");

	$consulta="SELECT idAviso,titulo,descripcion_larga,url_doc,idArea FROM 201_avisos WHERE '".$fechaA."' BETWEEN fechaPublicacion 
			AND fechaFin AND situacion='1' ORDER BY fechaPublicacion,idAviso";
	$resA=$con->obtenerFilas($consulta);

    //$bar = ucfirst(strtolower($bar)); // Hello world!
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
    <link rel="stylesheet" type="text/css" href="css/jquery.rateyo.css" />
    <link rel="stylesheet" type="text/css" href="css/jquery.mmenu.all.css" />
    <link rel="stylesheet" type="text/css" href="css/inner-page-style.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/estiloPersonal.css">
    <link rel="stylesheet" type="text/css" href="css/estiloModalPersonal.css">

    <script type="text/javascript" src="js/controles.js?rev=<?php echo time();?>"></script>

</head>

<body>
    <div id="page" class="site">
        <header class="site-header">
            <div class="top-header">
                <div class="container">
                    <div class="top-header-left">
                        <div class="top-header-block">
                            <a href="mailto:sidepev.nvo@hotmail.com"><i
                                    class="fas fa-envelope"></i>sidepev.nvo@hotmail.com</a>
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
            <div class="owl-four owl-carousel">
                <img src="images/page-banner.jpg" alt="Image of Bannner">
                <img src="images/page-banner2.jpg" alt="Image of Bannner">
                <img src="images/page-banner3.jpg" alt="Image of Bannner">
            </div>
            <div class="container">
                <h1>Avisos Importantes</h1>
                <h3>Sindicato Democratico de Empleados del Poder Ejecutivo de Veracruz</h3>
            </div>
            <div id="owl-four-nav" class="owl-nav"></div>
        </div>

        <!-- Banner Close -->
        <section class="testimonial-page">
            <div class="container">
                <main class="customer-review">
					<?php
						if($resA)
						{
							$existe=true;
							while($fila=mysql_fetch_row($resA))
							{
								$titulo=cv(ucfirst(strtolower($fila[1])));
								$descripcion=$fila[2];
								$urlDoc=$fila[3];
								$idArea=$fila[4];
								$nombreArea=obtenerNombreAreas($idArea);

								if($urlDoc=='' || $urlDoc==null)
								{
									$existe=false;
								}
					?>
                    <div class="row">
                        <div class="img">
                            <img src="images/logoSindicato.jpeg" alt="Customer Picture">
                        </div>
                        <div class="rewiew-content">
                            <header>
                                <h3><?php echo $titulo?></h3>
                                <p><?php echo $descripcion?></p>
                            </header>
                            <footer>
                                <span><h4>Area:</h4><p><?php echo $nombreArea?></p></span>
                                <?php
                                    if($existe)
                                    {
                                ?>
                                <div class="btn_doc">
                                    <a href="#" onclick="abrirDocumentoPDFPag('<?php echo $urlDoc;?>')">Visualizar Oficio</a>
                                </div>
                                <?php
                                    }
                                ?>
                            </footer>
                        </div>
                    </div>
					<?php
							}
						}
						else{
					?>
                    <div class="row">
                        <div class="img">
                            <img src="images/testimonial-customer.jpg" alt="Customer Picture">
                        </div>
                        <div class="rewiew-content">
                            <header>
                                <h3>Very happy to find this institute2</h3>
                                <p>I am very happy with the service provided by this institute. Now i have got the job
                                    as web developer in one of the reputed company of Nepal. Especially trainers are the
                                    Professionals from the field of information technology. Thankyou lab theme.
                                    Professionals from the field of information technology. Thankyou lab theme.</p>
                            </header>
                            <footer>
                                <span>
                                    <h4>Bibek Basnet</h4>
                                    <p>Web Developer</p>
                                </span>
                                <div class="rateYo"></div>
                            </footer>
                        </div>
                    </div>
					<?php
						}
					?>

                </main>
 
            </div>
        </section>

        <section class="query-section">
            <div class="container">
                <p>¿Alguna pregunta? Contactanos por Telefono:<a href="tel:+2288146496"><i class="fas fa-phone"></i>
                        +228 814 6496</a></p>
            </div>
        </section>
        <!-- End of Query Section -->
        <footer class="page-footer" itemprop="footer">
            <div class="footer-first-section">
                <div class="container">
                    <div class="box-wrap" itemprop="about">
                        <header>
                            <h1>Conocenos</h1>
                        </header>
                        <p>Sindicato Democrático de Empleados del Poder Ejecutivo de Veracruz <br>( 2019 - 2025)</p>

                        <h4><a href="tel:+9779813639131"><i class="fas fa-phone"></i> +228 814 6496</a></h4>
                        <h4><a href="mailto:sidepev.nvo@hotmail.com"><i class="fas fa-envelope"></i>
                                sidepev.nvo@hotmail.com</a></h4>
                        <h4><a href=""><i class="fas fa-map-marker-alt"></i>Jalisco #68 Col. Progreso, Xalapa,Ver.</a>
                        </h4>
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
                    <p>Copyright 2022 &copy; sgtecno.com <span> | </span> Diseñada y desarrollada por <a
                            href="https://sgtecno.com">SGTecno</a></p>
                </div>
            </div>
        </footer>

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

    <script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="js/lightbox.js"></script>
    <script type="text/javascript" src="js/all.js"></script>
    <script type="text/javascript" src="js/owl.carousel.js"></script>
    <script type="text/javascript" src="js/jquery.flexslider.js"></script>
    <script type="text/javascript" src="js/jquery.rateyo.js"></script>
    <script type="text/javascript" src="js/jquery.mmenu.all.js"></script>
    <script type="text/javascript" src="js/custom.js"></script>

    <script src="Plantilla/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    
</body>

</html>