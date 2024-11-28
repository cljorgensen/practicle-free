<?php
	include ('PdfToText.php');

class Filetotext {

	private $filename;
	var $multibyte = 4; // Use setUnicode(TRUE|FALSE)
	var $convertquotes = ENT_QUOTES; // ENT_COMPAT (double-quotes), ENT_QUOTES (Both), ENT_NOQUOTES (None)
	var $showprogress = true; // TRUE if you have problems with time-out	
	var $decodedtext = '';

	public function __construct($filePath) {
		$this->filename = $filePath;
	}
  
  	public function convertToText() {
	
		if(isset($this->filename) && !file_exists($this->filename)) {
			return "File Not exists";
		}
		
		$fileArray = pathinfo($this->filename);
		$file_ext  = $fileArray['extension'];
		if($file_ext == "docx" || $file_ext == "odt" || $file_ext == "txt" || $file_ext == "pdf" || $file_ext == "pptx")
		{
			if($file_ext == "odt") {
				return $this->read_odt();
			}
			elseif($file_ext == "txt") {
				return $this->read_txt();
			}
			elseif($file_ext == "docx")  {
				return $this->read_docx();
			}
			elseif($file_ext == "pptx")  {
				return $this->read_pptx();
			}
			elseif($file_ext == "pdf" ){
				$pdf = new PdfToText($this->filename);
				return $pdf;

			} 
			else {
				return "Invalid File Type";
			}
		}
	}	

	private function read_txt()	{
		$fileHandle = fopen($this->filename, "r");
		$line = @fread($fileHandle, filesize($this->filename));   
		$lines = explode(chr(0x0D),$line);
		$outtext = "";
		foreach($lines as $thisline)
		  {
			$array[] = "UTF-8";
			$array[] = "ASCII";
			$array[] = "WINDOWS-1252";
			$array[] = "iso-8859-1";
			$encoding = mb_detect_encoding($thisline, $array);
			$convertedString = mb_convert_encoding($thisline, "UTF-8", "$encoding");

			$pos = strpos($convertedString, chr(0x00));
			if (($pos !== FALSE)||(strlen($convertedString)==0))
			  {
			  } else {
				$outtext .= $convertedString." ";
			  }
			}

		$outtext = preg_replace("/[^a-åA-Å0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);
		return $outtext;
	}

	private function read_docx(){

		$striped_content = '';
		$content = '';
		$zip = zip_open($this->filename);
		if (!$zip || is_numeric($zip)) return false;
		while ($zip_entry = zip_read($zip)) {
			if (zip_entry_open($zip, $zip_entry) == FALSE) continue;
			if (zip_entry_name($zip_entry) != "word/document.xml") continue;
			$content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
			zip_entry_close($zip_entry);
		}// end while
		zip_close($zip);
		
		$content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
		$content = str_replace('</w:r></w:p>', "\r\n", $content);
		$striped_content = strip_tags($content);

		return $striped_content;
	
	}

	private function read_pptx(){

		$zip_handle = new ZipArchive;
		$output_text = "";
		if (true === $zip_handle->open($this->filename)) {
			$slide_number = 1; //loop through slide files
			while (($xml_index = $zip_handle->locateName("ppt/slides/slide" . $slide_number . ".xml")) !== false) {
				$xml_datas = $zip_handle->getFromIndex($xml_index);
				$xml = new DOMDocument;
				$xml->loadXML($xml_datas, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
				// Return XML
				$xml_handle = $xml->saveXML();

				$output_text .= strip_tags($xml_handle);
				$slide_number++;
			}
			if ($slide_number == 1) {
				$output_text .= "";
			}
			$zip_handle->close();
		} else {
			$output_text .= "";
		}
		return $output_text;
	}

	private function read_odt(){

		$Content = '';

		$zip = zip_open($this->filename);

		if (!$zip || is_numeric($zip)){
			return false;
		}

		while ($zip_entry = zip_read($zip)) {

			if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

			if (zip_entry_name($zip_entry) != "content.xml") continue;

			$Content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

			zip_entry_close($zip_entry);
		}

		zip_close($zip);
		$xml = new DOMDocument;
		$xml->loadXML($Content, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
		// Return XML
		$Content = $xml->saveXML();

		return strip_tags($Content);
	}
}