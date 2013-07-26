<?php

OCP\JSON::checkAppEnabled('fileee_ocr');
OCP\JSON::checkLoggedIn();

//request an fileee schicken
//phpinfo();

$path = $_POST['path'];

if (trim($path) != ""){
	$view = new \OC\Files\View('/' . OCP\User::getUser() . '/files/');
	$meta = $view->getFileInfo(\OC\Files\Filesystem::normalizePath($path));
	$file = $view->file_get_contents(\OC\Files\Filesystem::normalizePath($path));
	$username = OCP\User::getUser();
	$dataId = $meta['fileid'];

	$callbackurl = 'http://pscloud.uni-muenster.de/owncloud/index.php/apps/fileee_ocr/ajax/fileeeCallback.php'; //Zu füllen
	$host = "h1.fileee.com";
	$host = 'http://officeapi.fileee.com';
	//$path = '/v2/test/logRequest';
	$path = "/externOcr/upload";
	//echo $host.$path;

	OCA\Fileee_ocr\OCR::create_database_entry($username, $dataId);

	OCP\JSON::success(array("data" => array("success" => "Sending files...")));

	flush();

	$response = OCA\Fileee_ocr\OCR::http_post($host, $file, $username, $dataId, $callbackurl);

	//TODO: ERRORHANDLING
}

?>