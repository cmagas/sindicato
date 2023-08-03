<?php
	session_start();
	
	include("conexionBD.php");
?>

arrMediosFirmaPermitidos=[];




	<?php
	if(isset($tipoFirmaPermitida[1]))
	{
	?>
		arrMediosFirmaPermitidos.push('1');
	<?php
	}
	
	if(isset($tipoFirmaPermitida[2]))
	{
	?>
		arrMediosFirmaPermitidos.push('6');
	<?php
	}
	
	if(isset($tipoFirmaPermitida[4]))
	{
	?>
		arrMediosFirmaPermitidos.push('4');
	<?php
	}
	?>