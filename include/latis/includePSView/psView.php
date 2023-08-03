<?php
	$dir_bin = $baseDir."/psview/bin";
	$dir_rsc = $baseDir."/archivosTmpPDF/";
	$dir_destino = $baseDir."/archivosTemporales/";
	$cmd_convert = "/usr/bin/convert";
	$cmd_ghostscript = "/usr/bin/ghostscript";
	$cmd_wget = "/usr/local/bin/wget";
	$cmd_file = "/usr/bin/file";
	
	$hst = "localhost"; // Host
	$usr = "root";          // User
	$pwd = "123456";          // Password
	$db  = "psview";          // Database name

	@mysql_connect($hst,$usr,$pwd,true)	or exit("Cannot connect to database");
	mysql_select_db($db) or exit("Cannot open database!");
	
	
	class Rsc 
	{
		var $RscID = "";
		var $RscMD5 = "";
		var $RscTypeID = "";
		var $RscName = "";
		var $IsURL = -1;
		var $InCache = -1;
		var $Size = 0;
	}  	
	
	function new_id() 
	{
		// TBD: While not taken, get md5 id
		return date("YmdHis"); /* Unique enough */
	}

	function fetch_wget($fname, $url) 
	{
		global $cmd_wget;
		$cmd = "/bin/bash -c \"$cmd_wget -O $fname ".escapeshellcmd($url)." 2>&1\"";
//		$cmd = "/usr/local/bin/wget -O $fname ".escapeshellcmd($url);
#DEB		echo $cmd."<br>";
		system($cmd); // Can decide from wget if dl succeeded...
		return true;
	}

	function fetch_try_unzip($fname) 
	{
		global $dir_bin;
 		$cmd = "$dir_bin/try_unzip.sh $fname";
#DEB		echo $cmd."<br>";
                system($cmd);
		return true;
	}

	function rsc_type($fname) 
	{
		global $cmd_file;
		$cmd = "$cmd_file -b $fname";
		exec($cmd, $output);
		if(count($output) == 1) 
		{ # One line of output
			if(ereg("^Microsoft Office Document", $output[0])) 
			{
				$tmpret = 4;
			} 
			else
			if(ereg("^PDF", $output[0])) 
			{
				$tmpret = 3;
			} 
			else
			if(ereg("^PostScript", $output[0])) 
			{
				$tmpret = 2;
			} 
			else 
			{
				$tmpret = 1; # Unknown
			}
		} 
		else 
		{
			$tmpret = 0; # No output
		}
		return $tmpret;
	}
	
	
	function elapsedtime($starttime, $stoptime) 
	{
	        $startsep = strpos($starttime, " ");
       		$startsec = substr($starttime, $startsep);
        	$startmicro = substr($starttime, 0, $startsep - 1);

        	$stopsep = strpos($starttime, " ");
        	$stopsec = substr($stoptime, $stopsep);
        	$stopmicro = substr($stoptime, 0, $stopsep - 1);

        	$elapsed = ($stopsec - $startsec) + ($stopmicro - $startmicro);

        	return $elapsed;
	}
	
	function render($dir_rsc, $rsc_id, $rsc_type,$nombreArchivo,$dir_destino) 
	{
		global $dir_bin;
		global $cmd_convert;
		global $cmd_ghostscript;
		$cmd = $dir_bin."/render.sh ".$dir_rsc." ".$rsc_id." ".$rsc_type." ".$dir_destino." ".$nombreArchivo." ".$cmd_convert." ".$cmd_ghostscript;
		$resultado=array();
		exec($cmd,$resultado);
		
		return true;
	}

	function discover_gif_comps($dir_rsc, $rsc_md5, $rsc_id, $rsc_type) 
	{
		/* Fetch directory. See how many files there are */
		$my_dir = array();
		global $dir_destino;
		$d = dir($dir_destino."".$rsc_md5);
		while($entry=$d->read()) {
			if(ereg(".gif", $entry)) {
				$my_dir[] = (int) substr($entry,3,strlen($entry)-7);
			}
		}
		$d->close();
		sort($my_dir);

		$sql = "INSERT INTO RscComp (RscID, RscCompTypeID, Position, Size, Filename)";
		$sql .= " VALUES ";

		$arr = array();
		foreach($my_dir as $key=>$val) {
			$arr[] = sprintf("('%s', 1, %d, %d, '%s')",
							$rsc_id,
							$val,
							filesize("$dir_destino/$rsc_md5/tmp$val.gif"),
							"tmp$val.gif"
							);
		}
		$sql .= join(",", $arr);
#DEB		echo $sql."<br>";
		$q = mysql_query($sql);
		return true;
	}

	function discover_html_comps($dir_rsc, $rsc_md5, $rsc_id, $rsc_type) 
	{
		/* Fetch directory. See how many files there are */
		$my_dir = array();
		global $dir_destino;
		$d = dir($dir_destino."".$rsc_md5);
		while($entry=$d->read()) {
			if($entry == "index.html") {
				$my_dir[] = $entry;
			}
		}
		$d->close();
		sort($my_dir);

		$sql = "INSERT INTO RscComp (RscID, RscCompTypeID, Position, Size, Filename)";
		$sql .= " VALUES ";

		$arr = array();
		foreach($my_dir as $key=>$val) {
			$arr[] = sprintf("('%s', 3, %d, %d, '%s')",
							$rsc_id,
							$val,
							filesize("$dir_rsc/$rsc_md5/index.html"),
							"tmp$val.gif"
							);
		}
		$sql .= join(",", $arr);
#DEB		echo $sql."<br>";
		$q = mysql_query($sql);
		return true;
	}
	
	function convertirArchivoPDFVisor($nombreArchivo)
	{
		global $baseDir;
		global $dir_rsc;
		global $dir_destino;
		$starttime = microtime();
		$upd_rsc=false;
		$download=false;
		
		$tmpRsc = new Rsc;
		$tmpRsc->RscMD5 = new_id();
		$tmpRsc->RscName =$nombreArchivo;
		$tmpRsc->IsURL = 0;

		$tmpFname = $baseDir."/archivosTmpPDF/".$nombreArchivo;
		fetch_try_unzip($tmpFname);

		$tmpRsc->RscTypeID = rsc_type($tmpFname);
		$tmpRsc->InCache = -1; // If not failed ...
		$tmpRsc->Size = filesize($tmpFname);
			
		$add_rsc = true;
		
		
	
		if($add_rsc) 
		{
			/* Add $tmpRsc to Rsc */
			$sql = "INSERT INTO Rsc";
			$sql .= " (RscMD5, RscTypeID, RscName, IsURL, InCache, Size)";
			$sql .= sprintf(" VALUES ('%s', '%s', '%s', %d, %d, %d)",
							$tmpRsc->RscMD5,
							$tmpRsc->RscTypeID,
							mysql_escape_string($tmpRsc->RscName),
							$tmpRsc->IsURL,
							$tmpRsc->InCache,
							$tmpRsc->Size
							);
	#DEB		echo $sql."<BR>";
			$q = mysql_query($sql);
			$tmpRsc->RscID = mysql_insert_id();
		}
	
		if($upd_rsc) 
		{
			$sql = "UPDATE Rsc SET RscTypeID=".$tmpRsc->RscTypeID;
			$sql .= ", InCache=".$tmpRsc->InCache;
			$sql .= ", Size=".$tmpRsc->Size;
			$sql .= " WHERE RscID=".$tmpRsc->RscID;
	#DEB		echo $sql."<BR>";
			$q = mysql_query($sql);
		}
		
		$stoptime = microtime();
	

		$host = mysql_escape_string(substr(GetEnv("REMOTE_HOST"), 0, 254));
		$ip = mysql_escape_string(substr(GetEnv("REMOTE_ADDR"), 0, 15));
		$req_status = "200";	
		$req_method = mysql_escape_string(substr(GetEnv("REQUEST_METHOD"), 0, 254));
		$request = mysql_escape_string(substr(GetEnv("QUERY_STRING"), 0, 254));
		$referer = mysql_escape_string(substr(GetEnv("HTTP_REFERER"), 0, 254));
		$user_agent = mysql_escape_string(substr(GetEnv("HTTP_USER_AGENT"), 0, 254));
	
		$sql .= "'$req_status', '$req_method', '$request', '$referer', '$user_agent', '$host', '$ip')";
	
		$sql = "INSERT INTO RscFetch";
		$sql .= " (RscID, FetchDate, FromCache, FetchSeconds, Size, req_status, req_method, request, referer, user_agent, host, ip) ";
		$sql .= sprintf("VALUES ('%s', NOW(), %d, '%s', %d, '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
							$tmpRsc->RscID,
							($download || isset($file)) ? 0 : -1,
							elapsedtime($starttime, $stoptime),
							$tmpRsc->Size,
							$req_status,
							$req_method,
							$request,
							$referer,
							$user_agent,
							$host,
							$ip
						);
	
	
		$q = mysql_query($sql);
		$sql = "SELECT RscCompID FROM RscComp WHERE Position=1 AND RscID=".$tmpRsc->RscID;
		$q = mysql_query($sql);
		if(mysql_num_rows($q) == 0) 
		{ 
			mkdir($dir_destino."/".$tmpRsc->RscMD5, 0777);
			render($dir_rsc, $tmpRsc->RscMD5, $tmpRsc->RscTypeID,$nombreArchivo,$dir_destino);
			if($tmpRsc->RscTypeID == 4)
				discover_html_comps($dir_rsc, $tmpRsc->RscMD5, $tmpRsc->RscID, $tmpRsc->RscTypeID);
			else
				discover_gif_comps($dir_rsc, $tmpRsc->RscMD5, $tmpRsc->RscID, $tmpRsc->RscTypeID);
			return $tmpRsc->RscMD5;
		}
		 
	}
?>