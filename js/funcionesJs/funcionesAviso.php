<?php
session_start();
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");

$fechaAct=date("Y-m-d");
$new_name_file="";
$subNombre=date("Ymdhis");

global $con;
$idUsuarioSesion=$_SESSION['idUsr'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $titulo=$_POST['titulo'];
        $desc_corta=$_POST['desc_corta'];
        $desc_larga=$_POST['desc_larga'];
        $fecha_aplica=$_POST['fecha_aplica'];
        $fecha_fin=$_POST['fecha_finaliza'];
        $idArea=$_POST['cmb_areas'];

       
        $file_name=$_FILES['id_file']['name'];
        list($nombreArch,$extension)=explode('.',$file_name);

        
        $nombreArchivo=$nombreArch."_".$subNombre.".".$extension;
        //echo "nombre arhvio".$nombreArchivo;
        if($file_name!=''||$file_name!=null)
        {
            
            $file_type=$_FILES['id_file']['type'];
            list($type, $extension) = explode('/', $file_type);
            if($extension=='pdf')
            {
                $dir='../../vista/contenido/archivoVistas/';
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }

                $file_tmp_name=$_FILES['id_file']['tmp_name'];
                //$new_name_file = $dir.$file_name.'.'.$extension;
                $new_name_file = $dir.$nombreArchivo;
                if (copy($file_tmp_name, $new_name_file)) {
                    
                }
                
            }
        }
        $consulta="INSERT INTO 201_avisos(fechaRegistro,idResponsable,titulo,descripcion_corta,descripcion_larga,
                    url_doc,fechaPublicacion,fechaFin,idArea,situacion)VALUES('".$fechaAct."','".$_SESSION['idUsr']."','".$titulo."',
                    '".$desc_corta."','".$desc_larga."','".$nombreArchivo."','".$fecha_aplica."','".$fecha_fin."','".$idArea."','1')";
              //echo $consulta;     
        $res=$con->ejecutarConsulta($consulta);
        if($res)
        {
            echo "1";
        }else{
            echo "2";
        }
    
    }else{
        echo "2";
    }

    
?>