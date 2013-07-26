<?php
OCP\JSON::checkAppEnabled('data_id');
OCP\JSON::checkLoggedIn();
OCP\JSON::callCheck();

$path = $_POST['path'];

if (trim($path) != ""){

	//echo $path;

	$view = new \OC\Files\View('/' . OCP\User::getUser() . '/files/');
	$meta = $view->getFileInfo(\OC\Files\Filesystem::normalizePath($path));

	if($meta !== false) {
		OCP\JSON::encodedPrint(array('data-id' => $meta['fileid']));
	}
}else{
	OCP\JSON::encodedPrint(array('data-id' => null));
}