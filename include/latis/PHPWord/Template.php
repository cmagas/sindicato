<?php
/**
 * PHPWord
 *
 * Copyright (c) 2011 PHPWord
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPWord
 * @package    PHPWord
 * @copyright  Copyright (c) 010 PHPWord
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    Beta 0.6.3, 08.07.2011
 */


/**
 * PHPWord_DocumentProperties
 *
 * @category   PHPWord
 * @package    PHPWord
 * @copyright  Copyright (c) 2009 - 2011 PHPWord (http://www.codeplex.com/PHPWord)
 */
class PHPWord_Template {
    
    /**
     * ZipArchive
     * 
     * @var ZipArchive
     */
    private $_objZip;
    
    /**
     * Temporary Filename
     * 
     * @var string
     */
    private $_tempFileName;
    
    /**
     * Document XML
     * 
     * @var string
     */
    public $_documentXML;
    
	private $_header1XML;
	private $_header2XML;
	private $_header3XML;
	private $_footer1XML;
    private $_footer2XML;
	private $_footer3XML;
    /**
     * Create a new Template Object
     * 
     * @param string $strFilename
     */
    public function __construct($strFilename) 
	{
        $path = dirname($strFilename);
        $this->_tempFileName = $path.DIRECTORY_SEPARATOR.time().'.docx';
        $this->_tempFileName=str_replace("\\","/",$this->_tempFileName);
		
		
        copy($strFilename, $this->_tempFileName); // Copy the source File to the temp File
		
        $this->_objZip = new ZipArchive();
        $this->_objZip->open($this->_tempFileName);
        
        $this->_documentXML = $this->_objZip->getFromName('word/document.xml');
		
		$this->_header1XML = $this->_objZip->getFromName('word/header1.xml');
		//echo $this->_header1XML ;
		$this->_header2XML = $this->_objZip->getFromName('word/header2.xml'); 
		//echo $this->_header2XML ;
		$this->_header3XML = $this->_objZip->getFromName('word/header3.xml'); 
		//echo $this->_header3XML ;
		$this->_footer1XML = $this->_objZip->getFromName('word/footer1.xml'); 
		$this->_footer2XML = $this->_objZip->getFromName('word/footer2.xml'); 
		$this->_footer3XML = $this->_objZip->getFromName('word/footer3.xml');
		
		
    }
    
    /**
     * Set a Template value
     * 
     * @param mixed $search
     * @param mixed $replace
     */
    public function setValue($search, $replace) 
	{
       /* if((substr($search, 0, 2) !== '${') && (substr($search, -1) !== '}')) 
		{
//            $search = $search;
        }*/
		
        if(!is_array($replace)) 
		{
            $replace = utf8_encode($replace);
        }
       // echo $this->_documentXML."<br>";
        $this->_documentXML = str_replace($search, $replace, $this->_documentXML);
		$this->_header1XML = str_replace($search, $replace, $this->_header1XML); // Custom code by Matt Bowden (blenderstyle) 04/12/2011
		$this->_header2XML = str_replace($search, $replace, $this->_header2XML); // Custom code by Matt Bowden (blenderstyle) 04/12/2011
		$this->_header3XML = str_replace($search, $replace, $this->_header3XML); // Custom code by Matt Bowden (blenderstyle) 04/12/2011
		$this->_footer1XML = str_replace($search, $replace, $this->_footer1XML); // Custom code by Matt Bowden (blenderstyle) 04/12/2011
		$this->_footer2XML = str_replace($search, $replace, $this->_footer2XML); // Custom code by Matt Bowden (blenderstyle) 04/12/2011
		$this->_footer3XML = str_replace($search, $replace, $this->_footer3XML); // Custom code by Matt Bowden (blenderstyle) 04/12/2011
		//echo $this->_documentXML."<br>";
		
    }
    
    /**
     * Save Template
     * 
     * @param string $strFilename
     */
    public function save($strFilename) 
	{
		//echo ($this->_documentXML);
		//return;
        if(file_exists($strFilename)) {
            unlink($strFilename);
        }
        
        $this->_objZip->addFromString('word/document.xml', $this->_documentXML);
        $this->_objZip->addFromString('word/header1.xml', $this->_header1XML); // Custom code by Matt Bowden (blenderstyle) 04/12/2011
		$this->_objZip->addFromString('word/header2.xml', $this->_header2XML); // Custom code by Matt Bowden (blenderstyle) 04/12/2011
		$this->_objZip->addFromString('word/header3.xml', $this->_header3XML); // Custom code by Matt Bowden (blenderstyle) 04/12/2011
		$this->_objZip->addFromString('word/footer1.xml', $this->_footer1XML); // Custom code by Matt Bowden (blenderstyle) 04/12/2011
		$this->_objZip->addFromString('word/footer2.xml', $this->_footer2XML); // Custom code by Matt Bowden (blenderstyle) 04/12/2011
		$this->_objZip->addFromString('word/footer3.xml', $this->_footer3XML); 
        // Close zip file
        if($this->_objZip->close() === false) {
            throw new Exception('Could not close zip file.');
        }

        rename($this->_tempFileName, $strFilename);
    }
	
	public function generarArchivo($formato="Word2007",$nomArchivo)
	{
		global $baseDir;
		global $tipoServidor;
		
		$separador="/";
		if($tipoServidor!="1")
			$separador="\\";
		
		
			
		switch($formato)
		{
			
			case "PDF":
			break;
			default:
			
				$aArchivo=explode($separador,str_replace("..","",$nomArchivo));
				$nomArchivoAux=$aArchivo[sizeof($aArchivo)-1];
				
				
				header('Content-Type: application/msword');
				header('Content-Disposition: attachment; filename='.$nomArchivoAux);
				header('Cache-Control: max-age=0');
			break;
		}	
			
		
		if($formato!="PDF")
		{
			$this->save($nomArchivo);
			readfile($nomArchivo); 	
			unlink($nomArchivo);
			
		}
		else
		{
			
			$baseArchivoDestino=$baseDir."/archivosTemporales/".date("YmdHis_".rand(10,1000)).".docx";
			
			$arrArchivo=explode($separador,str_replace("..","",$nomArchivo));
			
			$nombreFinalArchivo=$arrArchivo[sizeof($arrArchivo)-1];
			$arrArchivo=explode(".",$nombreFinalArchivo);
			
			$this->save($baseArchivoDestino);

			generarDocumentoPDF($baseArchivoDestino,true,true,true,$arrArchivo[0].".pdf");
		}
	}
	
	public function generarArchivoServidor($formato="Word2007",$nomArchivo)
	{
		global $baseDir;
		global $tipoServidor;

		$nomArchivo="";
		if($nArchivo=="")
			$nomArchivo=$this->nArchivo;
		else
			$nomArchivo=$nArchivo;
		if($formato!="PDF")
		{
			$this->save($nomArchivo);
		}
		else
		{

			$separador="/";
			if($tipoServidor!="1")
				$separador="\\";
				
			$nombreAleatorio=date("YmdHis_".rand(10,1000));	
			$baseArchivoDestino=$baseDir."/archivosTemporales/".$nombreAleatorio.".docx";
			
			$arrArchivo=explode($separador,$nomArchivo);
			
			$nombreFinalArchivo=$arrArchivo[sizeof($arrArchivo)-1];

			$arrArchivo=explode(".",$nombreFinalArchivo);

			$rutaFinal=str_replace($nombreFinalArchivo,$arrArchivo[0].".pdf",$nomArchivo);


			
			$this->save($baseArchivoDestino);

			generarDocumentoPDF($baseArchivoDestino,false,false,true,$arrArchivo[0].".pdf");

			$archivoPDF=$nombreAleatorio.".pdf";

			
			if(file_exists($baseDir."/archivosTmpPDF/".$archivoPDF))
				copy($baseDir."/archivosTmpPDF/".$archivoPDF,$rutaFinal);
			unlink($baseDir."/archivosTmpPDF/".$archivoPDF);
		}
	}
}
?>