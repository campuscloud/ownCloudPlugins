<?php
//Delete und Change Hook!?

namespace OCA\Fileee_ocr;

class OCR{

	function http_post($host, $file = '', $username, $dataId, $callbackurl) {
		//echo "http_post called";
		$boundary = md5(uniqid());
		//Wenn Datei vorhanden
		//if ($file) {
		$binary = $file;//file_get_contents($file);

		//Typen passend setzen
		$content_type = "multipart/form-data; boundary=$boundary";

		$items = array();

		define('CRLF',"\r\n");

		$json = "{\"username\":\"$username\", \"data-id\":\"$dataId\", \"url\":\"$callbackurl\"}";
		//echo $json;
		
		$items[] = '--' . $boundary . CRLF . 'Content-Disposition: form-data; name="file.pdf"; filename="myFile.pdf"' . CRLF . 'Content-Length: ' . strlen($file) . CRLF . 'Content-Type: application/octet-stream' . CRLF . CRLF . $file;
		$items[] = '--' . $boundary . CRLF . 'Content-Disposition: form-data; name="param"' . CRLF . 'Content-Type: application/json' . CRLF . 'Content-Length: ' . strlen($json) . CRLF . CRLF . $json;
		$items[] = '--' . $boundary . '--';
		
		$data = implode(CRLF, $items);
		//echo $data;
		$content_length = strlen($data);
		
		$fp = fsockopen('h1.fileee.com', 8008, $errNo, $errString);
		//path eintragen
		fputs($fp, "POST /externocr/upload HTTP/1.1\r\n");
		fputs($fp, "Host: h1.fileee.com\r\n");
		fputs($fp, "Content-Type: $content_type\r\n");
		fputs($fp, "Content-Length: $content_length\r\n");
		fputs($fp, "Authorization: bearer 1d6f22ece1d0ce0083351779eeb73160397dafc737ad46137d4fb13c4290c988\r\n");
		fputs($fp, "Connection: close\r\n\r\n");
		fputs($fp, $data, $content_length);

		$http_response = stream_get_contents($fp);
		fclose($fp);

		list($headers, $body) = explode("\r\n\r\n", $http_response, 2);

		//print_r($headers);
		//print_r($body);

		return $http_response;
	}

	static function create_database_entry($username, $dataId){
		$timestamp = time();

		if(OCR::get_database_entry($username, $dataId)->rowCount() == 0){
			$query = \OC_DB::prepare("INSERT INTO `*PREFIX*files_fileee` (`timestamp`,`user`, `content`, `dataid`) VALUES (?,?,'null', ?)");
			\OC_Log::write('fileee_ocr', 'New entry', \OC_log::INFO);
			$result = $query->execute(array($timestamp, $username, $dataId));
		}else{
			$query = \OC_DB::prepare("UPDATE `*PREFIX*files_fileee` SET content='null', status=0 WHERE user=? AND dataid=?");
			\OC_Log::write('fileee_ocr', 'Update entry', \OC_log::INFO);
			$result = $query->execute(array($username, $dataId));
		}

		if(!$result){
			\OC_Log::write('fileee_ocr', 'Could not create entry', \OC_log::ERROR);
			return false;
		}else{
			\OC_Log::write('fileee_ocr', 'Entry saved.', \OC_log::INFO);
			return true;
		}
	}

	static function get_database_entry($username, $dataId){
		$query = \OC_DB::prepare("SELECT * FROM `*PREFIX*files_fileee` WHERE user=? AND dataid=?");
		$result = $query->execute(array($username, $dataId));

		return $result;
	}

	static function insert_text_content($content, $username, $dataId){
		$query = \OC_DB::prepare("UPDATE `*PREFIX*files_fileee` SET content=?, status=1 WHERE user=? AND dataid=? AND status=0");
		$result = $query->execute(array($content,$username, $dataId));

		if(!$result){
			\OC_Log::write('fileee_ocr', 'Could not create entry', \OC_log::ERROR);
			return false;
		}else{
			\OC_Log::write('fileee_ocr', 'Entry saved.', \OC_log::INFO);
			return true;
		}
	}
}

?>