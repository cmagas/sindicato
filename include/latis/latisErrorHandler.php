<?php

function latisErrorHandler($errno, $errstr, $errfile, $errline) 
{
   throw new Exception("Error: ".$errstr.". Archivo: ".$errfile.". Linea: ".$errline);
}

set_error_handler("latisErrorHandler");
?>