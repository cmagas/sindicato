<?php
    //include_once("latis/conexionBD.php");
    //include_once("latis/utiles.php");

    $fechaRegistro=date("Y-m-d");
    $fechaCorreo=date("d/m/Y");

    $nombreContacto="";
    $email="";
    $nombreArea="";
    $asunto="";
    $mensaje="";
    $destinatario="carlosmariomaganaramirez@gmail.com";
    $idDespacho="";

	echo "Destinatario ".$destinatario;

    if(isset($_POST["nombre"]))
	    $nombreContacto=$_POST["nombre"];

    if(isset($_POST["email"]))
        $email=$_POST["email"];

    if(isset($_POST["despacho"]))
        $idDespacho=$_POST["despacho"];		

    if(isset($_POST["asunto"]))
        $asunto=$_POST["asunto"];	

    if(isset($_POST["mensaje"]))
        $mensaje=$_POST["mensaje"];	

    if($idDespacho!="")
    {
        $nombreArea=obtenerNombreAreas($idDespacho);
        $destinatario=$nombreArea;
    }

    $nombreEmisor=$nombreContacto;
    $emisor=$email;

    $mensaje='
			<html>
				<head>
					<style type="text/css">
					.encabezado {
						text-align: center;
						font-weight: bold;
						font-family: Verdana, Geneva, sans-serif;
						font-size: 16px;
					}
					.texto {
						font-weight: bold;
						font-family: Arial, Helvetica, sans-serif;
						font-size: 12px;
					}
					.datos {
						font-weight: bold;
						font-style: italic;
						font-family: Tahoma, Geneva, sans-serif;
						font-size: 12px;
					}
					</style>
					
				</head>
				<body>
					<table width="90%" border="0">
					  <tr>
						<td class="encabezado">INFORMACION RECIBIDA</td>
					  </tr>
					</table>
					<table width="90%" border="0">
					  <tr>
						<td width="14%">&nbsp;</td>
						<td width="53%">&nbsp;</td>
						<td width="33%">&nbsp;</td>
					  </tr>
					  <tr>
						<td width="14%" class="texto">Fecha recepción::</td>
						<td width="53%" class="datos">'.$fechaCorreo.'</td>
						<td width="33%">&nbsp;</td>
					  </tr>
					  
					  <tr>
						<td class="texto">Nombre del Contacto:</td>
						<td class="datos">'.$nombreContacto.'</td>
						<td>&nbsp;</td>
					  </tr>
					  <tr>
						<td class="texto">Correo contacto:</td>
						<td class="datos">'.$email.'</td>
						<td>&nbsp;</td>
					  </tr>
					  <tr>
						<td class="texto">Despacho de Atención:</td>
						<td class="datos">'.$nombreArea.'</td>
						<td>&nbsp;</td>
					  </tr>                      
					  <tr>
						<td class="texto">Asunto:</td>
						<td class="datos">'.$asunto.'</td>
						<td>&nbsp;</td>
					  </tr>
					  <tr>
						<td class="texto">Asunto:</td>
						<td class="datos">'.$mensaje.'</td>
						<td>&nbsp;</td>
					  </tr>
					  <tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					  </tr>
					</table>
					
				</body>
			</html>

';

//enviarMail($destinatario,$asunto,$mensaje,$emisor,$nombreEmisor="",$arrArchivos=null,$arrCopia=null,$arrCopiaOculta=null);
/*
    $consulta="INSERT INTO 12_correosRecibidos(fechaCreacion,nombre,emailSocio,idAreaAtencion,asunto,
            mensaje,situacion)VALUE('".$fechaRegistro."','".$nombreContacto."','".$email."','".$idDespacho."',
            '".$asunto."','".$mensaje."','1')";
    if($con->ejecutarConsulta($consulta))
    {
        enviarMail($destinatario,$asunto,$mensaje,$emisor,$nombreEmisor="",$arrArchivos=null,$arrCopia=null,$arrCopiaOculta=null);
	}

	*/
 
?>

<script>
    //window.location.href='http://demosindicato.sgtecno.com/';
</script>