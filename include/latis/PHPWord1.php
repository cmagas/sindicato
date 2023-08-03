<?php
	function glp($iS=false)
	{
		if(!$iS)
			return "KFNFTEVDVCBNRDUoQ09OQ0FUKCdCdWVuIGludGVudG8gJywoaWRfXzQxMF90YWJsYURpbmFtaWNhK2ZlY2hhQ3JlYWNpb24rMTcwMDArYS5jb21wbGVtZW50YXJpbzcpKSkgRlJPTSBfNDEwX3RhYmxhRGluYW1pY2EgV0hFUkUgaWRfXzQxMF90YWJsYURpbmFtaWNhPWEuY29tcGxlbWVudGFyaW8yKQ==";	
		return "TUQ1KENPTkNBVCgnQnVlbiBpbnRlbnRvICcsKGlkX180MTBfdGFibGFEaW5hbWljYStmZWNoYUNyZWFjaW9uKzE3MDAwK2EuY29tcGxlbWVudGFyaW83KSkp";
	}
	
	function ghr()
	{
		return "KGNvbXBsZW1lbnRhcmlvNCtpZFVzdWFyaW9WU1Byb3llY3RvKQ==";	
		
	}
	
	function gdhr()
	{
		return "KGNvbXBsZW1lbnRhcmlvNC1pZFVzdWFyaW9WU1Byb3llY3RvKQ==";	
		
	}
	
	function ghc()
	{
		return "KGNvbXBsZW1lbnRhcmlvNCtpZFVzdWFyaW9WU1Byb3llY3RvKQ==";
	}
	
	function ghc2()
	{
		return "U1VCU1RSSU5HKE1ENShyYW5kKDIwMDAwKSksMSwxNik=";
	}
	
	function ghst()
	{
		return "U1VCU1RSSU5HKGNvbXBsZW1lbnRhcmlvNSwxNyk=";
	}
	
	function ghst2()
	{
		return "U1VCU1RSSU5HKGNvbXBsZW1lbnRhcmlvNSwxLDE2KQ==";
	}
	
	function cUsr()
	{
		global $con;

		$x=0;
		$query[$x]="begin";
		$x++;
		$consulta="SELECT * FROM _225_tablaDinamica";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$iUsr=$fila[6];
			$llave=md5($iUsr);
			$p1=substr($llave,0,16);
			$p2=substr($llave,17);
			$p1=rand(0,9).$fila[0].$p1;
			$p2=rand(0,9).$fila[0].$p2;
			$query[$x]="UPDATE _225_tablaDinamica SET complementario5='".$p2."',complementario6='c4ca4238a0b92382',complementario7='".$p1."' WHERE idHash=".$fila[0];
			$x++;

		}
		$query[$x]="commit";
		$x++;
		$con->ejecutarBloque($query);
	}
	
	
?>