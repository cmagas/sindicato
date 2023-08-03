<?php
class cActiveDirectory
{
	
	var $conexion;
	var $ipServidor;
	var $dominio;
	var $usuario;
	var $password;
	var $puerto;
	
	function cActiveDirectory($iServidor,$port)
	{
		$this->ipServidor=$iServidor;
		$this->puerto=$port;
	}
	
	
	function conectar()
	{
		$this->conexion = ldap_connect("ldap://". $this->ipServidor .":".$this->puerto."/");
		
		if(!$this->conexion)
			return false;
		else
			return true;
	}
    
	
	function autenticarUsuario($usr,$passwd,$domain)
	{
		
		$arrResultado=array();
		$arrResultado["sAMAccountName"]="";
		$arrResultado["autenticado"]=false;
		$arrResultado["mensageError"]= "";
		$arrResultado["codigoAccion"]="0000";
		$arrResultado["nombre"]="";
		$arrResultado["apellidos"]="";
		$arrResultado["email"]="";
		$arrResultado["roles"]=array();
		try
		{
			if(!$this->conexion)
				$this->conectar();
				
			$this->dominio=$domain;
			$this->usuario=$usr;
			$this->password=$passwd;
			
			$extended_error="";
			ldap_set_option($this->conexion, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($this->conexion, LDAP_OPT_REFERRALS, 0);
			$bind = @ldap_bind($this->conexion, ($this->usuario."@".$this->dominio), $this->password);
			ldap_get_option($this->conexion, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error);
			
			if (!empty($extended_error))
			{
				$errno = explode(',', $extended_error);
				$errno = $errno[2];
				$errno = explode(' ', $errno);
				$errno = $errno[2];
				$errno = intval($errno);
				
				$arrResultado["autenticado"]=false;	
				$arrResultado["codigoAccion"]=$errno;
				if ($errno == 532)
				{
					$arrResultado["mensageError"] = 'Fallo de Logueo: Password Expirado';
					
				}
			}
			else
			{
				if ($bind)
				{
			 		$ldap_base_dn ="CN=Users,DC=". join(',DC=', explode('.', $this->dominio));
					$search_filter = "(&(sAMAccountName=".$this->usuario."))";
					$atributos=array();
					$atributos[0]="givenname";
					$atributos[1]="memberof";
					$atributos[2]="sn";
					$atributos[3]="sAMAccountName";
					$atributos[4]="mail";

					$result = ldap_search($this->conexion, $ldap_base_dn, $search_filter,$atributos);
					$info = ldap_get_entries($this->conexion,$result);
					if (!count($result))
					{
						$arrResultado["codigoAccion"]=1010;
						$arrResultado["autenticado"]=false;
						$arrResultado["mensageError"]= ldap_error($this->conexion);
						
					}						
					else
					{
						$arrResultado["codigoAccion"]=1011;
						$arrResultado["autenticado"]=true;
						$arrResultado["mensageError"]= "";
			   			$arrResultado["nombre"]=isset($info[0]["givenname"])?$info[0]["givenname"][0]:$info[0]["sAMAccountName"][0];
						$arrResultado["apellidos"]=isset($info[0]["sn"])?$info[0]["sn"][0]:"";
						$arrResultado["sAMAccountName"]=isset($info[0]["samaccountname"])?$info[0]["samaccountname"][0]:"";
						$arrResultado["email"]=isset($info[0]["mail"])?$info[0]["mail"][0]:"";
						foreach($info[0]["memberof"] as $id=>$infoGrupo)
						{
							if($id!=="count")
							{
								$arrInfoGrupo=explode(",",$infoGrupo);
								$nombreGrupo="[AD] ".str_replace('CN=',"",$arrInfoGrupo[0]);
								array_push($arrResultado["roles"],$nombreGrupo);
							}
							
						}
						
					}
				}
			}
		
			return $arrResultado;
		}
		catch(Exception $e)
		{
			$arrResultado["autenticado"]=false;
			$arrResultado["mensageError"]= $e->getMessage();
			$arrResultado["codigoAccion"]=999;
			if(strpos($arrResultado["mensageError"],"Invalid credentials")!==false)
			{
				$arrResultado["mensageError"]="Usuario / Contraseña incorrecta";
			}
			
			return $arrResultado;
		}
		
		
	}
	
	
	
   
	
}    


?>