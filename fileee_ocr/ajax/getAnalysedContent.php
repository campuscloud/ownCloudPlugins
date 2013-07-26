<?php

OCP\JSON::checkAppEnabled('fileee_ocr');
OCP\JSON::checkLoggedIn();

$username = OCP\User::getUser();

$path = $_POST['path'];

if(trim($path) != ""){
	$view = new \OC\Files\View('/' . OCP\User::getUser() . '/files/');
	$meta = $view->getFileInfo(\OC\Files\Filesystem::normalizePath($path));	

	$entry = OCA\Fileee_ocr\OCR::get_database_entry($username, $meta['fileid']);
	if($entry){
		$result = array();
		while($row = $entry->fetchRow()) {
        	$result = $row;
		}
		if(count($result) > 0){
			OCP\JSON::success(array("result" => $result));
			//OCP\JSON::encodedPrint(array($result));
		}else{
			OCP\JSON::error(array("result"=>"null", "message" => "No content to view."));
			//OCP\JSON::encodedPrint(array('result' => 'null'));
		}
	}else{
		OCP\JSON::error(array('message' => 'Error during query.'));
	}
}else{
	OCP\JSON::error(array('message' => 'Empty path.'));
}

?>