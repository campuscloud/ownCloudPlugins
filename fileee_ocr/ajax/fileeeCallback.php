<?php

OCP\JSON::checkAppEnabled('fileee_ocr');

$key = '5MSubD0WW8Dcaajd0dKDvNUWKZQQwZFF';

OC_Log::write('fileee_ocr', 'Got fileee callback. Trying to save...', \OC_log::INFO);
$entityBody = file_get_contents('php://input');
$inputStream = file_get_contents('php://input');
$result = json_decode($inputStream, true);

switch(json_last_error())
 {
  case JSON_ERROR_DEPTH:
  OC_Log::write('fileee_ocr', 'JSON: Maximale Stacktiefe überschritten', \OC_log::ERROR);
  break;
  case JSON_ERROR_CTRL_CHAR:
   OC_Log::write('fileee_ocr', 'JSON: Unerwartetes Steuerzeichen gefunden', \OC_log::ERROR);
  break;
  case JSON_ERROR_SYNTAX:
   OC_Log::write('fileee_ocr', 'JSON: Syntaxfehler, ungültiges JSON', \OC_log::ERROR);
  break;
  case JSON_ERROR_NONE:
   OC_Log::write('fileee_ocr', 'JSON: Ok -->'.$inputStream, \OC_log::INFO);
  break;
 }

if($result['key'] == $key){
	OC_Log::write('fileee_ocr', 'Fileee callback is valid.', \OC_log::INFO);
	
	//TODO: Check if entry exists --> update | else drop request.
	$entry = OCA\Fileee_ocr\OCR::get_database_entry($result['username'], $result['data-id']);
	if($entry){
		$status = null;
		while($row = $entry->fetchRow()) {
        	$status = $row['status'];
		}
		if($status == 0){
			OCA\Fileee_ocr\OCR::insert_text_content($result['text'], $result['username'], $result['data-id']);
		}else{
			OC_Log::write('fileee_ocr', 'Got wrong fileee callback. Status is already set to \'1\'', \OC_log::INFO);
		}
	}else{
		OC_Log::write('fileee_ocr', 'Got wrong fileee callback. Error during database query.', \OC_log::INFO);
	}
}else{
	OC_Log::write('fileee_ocr', 'Got wrong fileee callback. Wrong access key', \OC_log::INFO);
}

?>