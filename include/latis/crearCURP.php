<?php
class B_CURP{
    var $CURP="";
    var $Nombre="";
    var $Ape1="";
    var $Ape2="";
    var $Sexo="";
    var $Dia=0;
    var $Mes=0;
    var $Anio=0;
    var $DatosExtras=array();
    var $errores=array();
    private $url0="http://curp.emexico.gob.mx:8080/CurpSP/curp2.do";
    private $url1="http://curp.emexico.gob.mx:8080/CurpSP/curp1.do";
    private $url="";
    private $Modo="GET";
    private $Entidades=array("AS", "BC", "BS", "CC", "CL", "CM", "CS", "CH", "DF", "DG", "GT", "GR", "HG", "JC", "MC", "MN", "MS", "NT", "NL", "OC", "PL", "QT", "QR", "SP", "SL", "SR", "TC", "TS", "TL", "VZ", "YN", "ZS", "SM", "NE");
    private $EntidadNom=array("AGUASCALIENTES","BAJA CALIFORNIA NTE.","BAJA CALIFORNIA SUR","CAMPECHE","COAHUILA","COLIMA","CHIAPAS","CHIHUAHUA","DISTRITO FEDERAL","DURANGO","GUANAJUATO","GUERRERO","HIDALGO","JALISCO","MEXICO","MICHOACAN","MORELOS","NAYARIT","NUEVO LEON","OAXACA","PUEBLA","QUERETARO","QUINTANA ROO","SAN LUIS POTOSI","SINALOA","SONORA","TABASCO","TAMAULIPAS","TLAXCALA","VERACRUZ","YUCATAN","ZACATECAS","SERV. EXTERIOR MEXICANO","NACIDO EN EL EXTRANJERO");
    private $Especiales="ÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÄËÏÖÜ";
    private $Lectura_Error="no se encuentra en la Base de Datos Nacional de la CURP";//key to find if it no exist don't to traslate
    function B_CURP(){
        $this->url=$this->url0;
    }
    function GetModo(){
        return $this->Modo;
    }
    function SetModo(){
        if($this->Modo=="POST")
            $this->Modo="GET";
        else 
            $this->Modo="POST";
        return $this->Modo;
    }
    public function SetDatos($Nombre="", $Ape1="", $Ape2="", $Sexo="", $Dia=0, $Mes=0, $Anio=0, $Entidad=0){
        $this->url=$this->url1;
        
        $Nombre=strtoupper(trim($Nombre));$Ape1=strtoupper(trim($Ape1));$Ape2=strtoupper(trim($Ape2));
        $Sexo=strtoupper(trim($Sexo));
        
        $Dia=(int)$Dia;$Mes=(int)$Mes;$Anio=(int)$Anio;
        
        if($Nombre=="") $this->errores[]="Error(8): El nombre no esta definido.";// the name is not defined
        if($Ape1=="") $this->errores[]="Error(9): El apellido no esta definido. ";//the first second name is not defined
        if($Ape2=="") $this->errores[]="Error(10): El apellido 2 no esta definido. ";//the second second name is not defined
        if($Sexo=="") $this->errores[]="Error(11): El sexo no esta definido. ";//the gender is not defined
        if($Dia==0) $this->errores[]="Error(12): El dia no esta definido. ";//the day is not defined
        if($Mes==0) $this->errores[]="Error(13): El mes no esta definido. ";//the month y not defined
        if($Anio==0) $this->errores[]="Error(14): El a&ntilde;o no esta definido. ";//the year is not defined
        
        $fecha=mktime(0,0,0,$Mes,$Dia,$Anio);if($fecha===false)$this->errores[]="Error(15): la fecha no concuerda. ";
        
        $Nombre=trim(strtoupper($Nombre));if(ereg("^[A-ZÑ{$this->Especiales}][A-Z Ñ{$this->Especiales}]+$",$Nombre)===false) $this->errores[]="Error(16): El nombre tiene caracteres no reconocibles. ";//the name have not valid chars 
        $Ape1=trim(strtoupper($Ape1));if(ereg("^[A-ZÑ{$this->Especiales}][A-Z Ñ{$this->Especiales}]+$",$Ape1)===false) $this->errores[]="Error(17): El apellido tiene caracteres no reconocibles. ";//the first second name have not valid chars 
        $Ape2=trim(strtoupper($Ape2));if(ereg("^[A-ZÑ{$this->Especiales}][A-Z Ñ{$this->Especiales}]+$",$Ape2)===false) $this->errores[]="Error(18): El apellido 2 tiene caracteres no reconocibles. ";//the second second name have no valid chars 
        if(ereg("^[HM]$",$Sexo)===false) $this->errores[]="Error(19): el sexo deve ser H/M siendo H para Hombre. ";
        
        $Dia=date("d",$fecha); $Mes=date("m",$fecha); $Anio=date("Y",$fecha);
        //$Entidad=(int)$Entidad;if($Entidad>=count($this->Entidades)) $this->errores[]="Error(19): La entidad esta fuera del rango permitido. ";// the state is not valid(out of range)
        //$Entidad=$this->Entidades[$Entidad];
        if(count($this->errores)) return false;
        $vocales=array("A","E","I","O","U");
        $consonantes =array("B","C","D","F","G","H","I","J","K","L","M","N","Ñ","P","Q","R","S","T","V","W","X","Y","Z");
        
        $curp=substr($Ape1,0,1);//primera letra
        
        for($i=1;$i<strlen($Ape1);$i++)
            if(array_search(substr($Ape1,$i,1),$vocales)!==false){
                $curp.=substr($Ape1,$i,1);//primera vocal que no sea la primera
                break;
            }
        $curp.=substr($Ape2,0,1).substr($Nombre,0,1).substr($Anio,2,2).$Mes.$Dia.$Sexo.$Entidad;
        for($i=1;$i<strlen($Ape1);$i++)
            if(array_search(substr($Ape1,$i,1),$consonantes)!==false){
                $curp.=substr($Ape1,$i,1);//primera vocal que no sea la primera
                break;
            }
        for($i=1;$i<strlen($Ape2);$i++)
            if(array_search(substr($Ape2,$i,1),$consonantes)!==false){
                $curp.=substr($Ape2,$i,1);//primera vocal que no sea la primera
                break;
            }
        for($i=1;$i<strlen($Nombre);$i++)
            if(array_search(substr($Nombre,$i,1),$consonantes)!==false){
                $curp.=substr($Nombre,$i,1);//primera vocal que no sea la primera
                break;
            }
        $curp.="0";    $curp.=$this->calculaDigito($curp);
        $this->CURP=$curp;//curp pseudocalculada, con la esperanza de que este correcta, ya luego se verificara // we get the curp calculated
        $this->vars=
        "strPrimerApellido=".urlencode("{$Ape1}")."; ".
        "strSegundoAplido=".urlencode("{$Ape2}")."; ".
        "strNombre=".urlencode("{$Nombre}")."; ".
        "sSexoA=".urlencode("{$Sexo}")."; ".
        "strdia=".urlencode("{$Dia}")."; ".
        "strmes=".urlencode("{$Mes}")."; ".
        "stranio=".urlencode("{$Anio}")."; ".
        "sEntidadA=".urlencode("{$Entidad}")."; strTipo=A; entfija=DF; depfija=05 \r\n";
        return true;
    }
    public function SetCURP($xcurp=""){
        $this->url=$this->url0;
        $xcurp=trim(strtoupper($xcurp));
        {
            switch (strlen($xcurp)){
            case 10 :
                $reg = "^([A-Z]{2})([A-Z]{2})([0-9]{2})([0-9]{2})([0-9]{2})$";
                break;
            case 11 :
                $reg = "^([A-Z]{2})([A-Z]{2})([0-9]{2})([0-9]{2})([0-9]{2})([HM])$";
                break;
            case 12 :
                $reg = "^([A-Z]{2})([A-Z]{2})([0-9]{2})([0-9]{2})([0-9]{2})([HM])([A-Z]{1})$";
                break;
            case 13 :
                $reg = "^([A-Z]{2})([A-Z]{2})([0-9]{2})([0-9]{2})([0-9]{2})([HM])([A-Z]{2})$";
                break;
            case 14 :
                $reg = "^([A-Z]{2})([A-Z]{2})([0-9]{2})([0-9]{2})([0-9]{2})([HM])([A-Z]{2})([A-Z]{1})$";
                break;
            case 15 :
                $reg = "^([A-Z]{2})([A-Z]{2})([0-9]{2})([0-9]{2})([0-9]{2})([HM])([A-Z]{2})([A-Z]{2})$";
                break;
            case 16 :
                $reg = "^([A-Z]{2})([A-Z]{2})([0-9]{2})([0-9]{2})([0-9]{2})([HM])([A-Z]{2})([A-Z]{3})$";
                break;
            case 17 :
                $reg = "^([A-Z]{2})([A-Z]{2})([0-9]{2})([0-9]{2})([0-9]{2})([HM])([A-Z]{2})([A-Z]{3})([A-Z0-9]{1})$";
                break;
            case 18:
                $reg = "^([A-Z]{2})([A-Z]{2})([0-9]{2})([0-9]{2})([0-9]{2})([HM])([A-Z]{2})([A-Z]{3})([A-Z0-9]{2})$";
          }
            if(ereg($reg,$xcurp, $regs)===false)$this->errores[]="Error(20): La entidad curp no esta bien estructurada. ";//the state in the curp is not structurated
            if(isset($regs[7]))    if((array_search($regs[7],$this->Entidades))===false)
                $this->errores[]="Error(21): La entidad en la curp no esta dentro del rango permitido. ";//the state is not in the rage 
            
            if(isset($regs[9]))if(((int)$this->calculaDigito($xcurp))!=((int)$regs[9])) $this->errores[]="Error(21): parece que el digito verificador no corresponde con el de la curp. ";//the verific char not correspond
        }
        $this->CURP=$xcurp;
        $this->vars="strCurp=".urlencode("{$xcurp}")."; strTipo=B; entfija=DF; depfija=05"."\r\n";
        if(count($this->errores)) return false;
        return true;
    }
    
    private function calculaDigito($curp){
        $segRaiz      = substr($curp,0,17);
        $chrCaracter  = "0123456789ABCDEFGHIJKLMNÑOPQRSTUVWXYZ";
        $intFactor    = array();
        $lngSuma      = 0.0;
        $lngDigito    = 0.0;
        for($i=0; $i<17; $i++){
            for($j=0; $j<37; $j++){
                if(substr($segRaiz,$i,1)==substr($chrCaracter,$j,1)){
                    $intFactor[$i]=$j;
                }
            }
        }
        for($k = 0; $k < 17; $k++){
            $lngSuma= $lngSuma + (($intFactor[$k]) * (18 - $k));
        }
        $lngDigito= (10 - ($lngSuma % 10));
        if($lngDigito==10){
            $lngDigito=0;
        }
        return $lngDigito;
    }
    public function LeerDatos(){
        unset($this->errores);
        $this->errores=array();
        /*$host = Host al que se establecerá la conexión.
        $uri = Archivo que se intentará abrir.
        urlencode($vars) = Variables que se enviarán.
        $puerto = Puerto por el que se establecerá la conexión.*/
        if(strlen($this->vars)==0){
            if(strlen($this->CURP)>0) 
                $this->vars =urlencode("txtCurp=".$this->CURP); 
            else{
                $this->errores[]="Error(1): No hay datos que leer. ";
            }
        }
        
        $url=$this->url;
        $url=str_replace("http://","",$url);
        $_GET['host']=substr($url,0,strpos($url,"/"));
        $_GET['uri']=substr($url,strpos($url,"/"));
        if($this->Modo=="POST") 
            $_GET['vars']=$this->vars; 
        else{
            $vars=explode(";",$this->vars);$variables="";$amp="";
            foreach($vars as $Var){
                $variables.=$amp.trim($Var);
                $amp="&";
            }
            $_GET['uri'].="?".$variables;
            unset($_GET['vars']);
        }
        $_GET['puerto']=80;
        /* El siguiente fragmento fue tomado del siguiente autor(se modifico poco por eso hago referencia)
        Autor: Victor De la Rocha Sitio: http://www.mis-algoritmos.com blog: http://vyk1.spaces.live.com */ 
        if(isset($_GET['host'])&&!ereg("^ *$",$_GET['host'])){ 
            $host=$_GET['host']; 
        }else{ 
            $this->errores[]="Error(2): No ha definido el host. ";//the host is not defined
        } 
        if(isset($_GET['uri'])&&!ereg("^ *$",$_GET['uri'])){ 
            $uri=$_GET['uri']; 
        }else{ 
            $this->errores[]="Error(3): No ha definido el uri al cual se enviarán los datos. ";//the uri is not defined
        } 
        if(isset($_GET['vars'])&&!ereg("^ *$",$_GET['vars'])){ 
            $vars=$_GET['vars']; 
        }/*else{ 
            $this->errores[]="Error(4): No ha definido vars. ";
        } */
        if(isset($_GET['puerto'])&&!ereg("^ *$",$_GET['puerto'])){ 
            $puerto=$_GET['puerto']; 
        }else{ 
            $this->errores[]="Error(5): Es necesario definir el puerto por el cual se ejecutará la conexión. ";//the port is not defined
        }
        if(count($this->errores))return false;
        $header = "Host: $host\r\n"; 
        $header .= "User-Agent: PHP Script\r\n"; 
        if($this->Modo=="POST"){
            $header .= "Content-Type: application/x-www-form-urlencoded\r\n"; 
            $header .= "Content-Length: ".strlen($vars)."\r\n";
        }
        $header .= "Connection: close\r\n\r\n";
        $fp = pfsockopen("tcp://".$host, $puerto, $errno, $errstr, 30); 
        if($fp){
            fputs($fp, $this->Modo." {$uri} HTTP/1.1\r\n");
            fputs($fp, $header.$vars);
            fwrite($fp, $out); 
            //if(isset($_GET[view]))
            {
             $Archivo="";
                while (!feof($fp)) {
                    $Archivo.= fgets($fp, 128);
                }$Archivo.=" ";
            } fclose($fp);
        }else{ 
            $this->errores[]="Error(6):No se pudo establecer la conexion.";return false;//the conection can not be established
        }/*Fin del codigo que se implemento*/
        if(strpos($Archivo,$this->Lectura_Error)){
            $this->errores[]="Error(7):".$this->Lectura_Error;
            return false;
        }else{
            
            $Archivo=strstr($Archivo,'<input');//delimitamos hasta el primer input
            $Archivo=substr($Archivo,0,strpos($Archivo,">",strpos($Archivo,"errores"))+1);//buscamos la ultima etiqueta
            $tags=explode("\n",$Archivo);//separamos por etiquetas por medio de "\n"
            if(count($tags)==0) $tags=explode("\r",$Archivo);//si no paso nada entonces por otro criterio "\r"            
            if(count($tags)==0){
                $tags=explode(">",$Archivo); 
                for($i=0;$i<count($tags);$i++)
                    $tags[$i]=$tags[$i].">";
            }//si no entonces por medio de ">"
            for($i=0;$i<count($tags);$i++){//por cada tag encontrado
                $Nombre=$this->GetTagProp($tags[$i],"name");//buscamos el nombre y
                $Valor=$this->GetTagProp($tags[$i],"value");//el valor
                //$this->GetTagProp($tags[i],"type"); //tambien se puede el tipo
                $this->DatosExtras[$Nombre]=$Valor;//Guardamos en extras, nomas por referencia
                
                if(strtolower($Nombre)=="strcurp"){//curp
                    $this->CURP=$Valor;
                }else
                if(strtolower($Nombre)=="strnombre"){//nombre
                    $this->Nombre=$Valor;
                }else
                if(strpos(strtolower($Nombre),"primerap")!==false){//primer apellido
                    $this->Ape1=$Valor;
                }else
                if(strpos(strtolower($Nombre),"segundo")!==false){//segundo apellido
                    $this->Ape2=$Valor;
                }else
                if(strpos(strtolower($Nombre),"sexo")!==false){//sexo segun genero
                    if(strpos($Valor,"HOM")!==FALSE||strpos($Valor,"MAS")!==FALSE) $this->Sexo="H";else
                    if(strpos($Valor,"MUJ")!==FALSE||strpos($Valor,"FEM")!==FALSE)$this->Sexo="M";
                }else
                if(strpos(strtolower($Nombre),"chanacim")!==false){//fecha de nacimiento
                    list( $this->Dia, $this->Mes, $this->Anio) = split( '[/.-]', $Valor );//el formato es dia mes año
                    //pasamos cada uno a un entero
                    $this->Dia=(int)$this->Dia;    $this->Mes=(int)$this->Mes;    $this->Anio=(int)$this->Anio;
                }
            }
        }
        return true;
    }
    private function GetTagProp($Etiqueta,$Tag){
        $Etiqueta=trim($Etiqueta,"\n\t\r\0\b ");//quitamos caracteres que nos estorvan
        $Campo=substr($Etiqueta,strpos($Etiqueta,'<')+1,strpos($Etiqueta,'>')-1)." ";//quitamos las marcas de etiquetas y agregamos un token separador
        $Valor =trim(substr($Campo,strpos($Campo,'"',strpos($Campo,'=',strpos($Campo,$Tag)))));
        return trim(substr($Valor,0, strpos($Valor,'"',strpos($Valor,'"')+1)+1),"\" ");
    }
    public function GetNumberErrors(){//obtiene un arreglo del numero de cada error sin su descripcion
        foreach($this->errores as $ErrorNumber){
            $ErrorNum[]=substr($ErrorNumber,strpos($ErrorNumber,"("),strpos($ErrorNumber,")"));
        }return $ErrorNum;
    }    
}
?>