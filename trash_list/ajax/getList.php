<?php
OCP\JSON::checkAppEnabled('data_id');
OCP\JSON::checkLoggedIn();


$user = \OCP\User::getUser();
$view = new OC_Filesystemview('/'.$user.'/files_trashbin/files');

$dir = isset($_GET['dir']) ? stripslashes($_GET['dir']) : '';

$result = array();
if ($dir) {
	$dirlisting = true;
	$dirContent = $view->opendir($dir);
	$i = 0;
	while($entryName = readdir($dirContent)) {
		if ( $entryName != '.' && $entryName != '..' ) {
			$pos = strpos($dir.'/', '/', 1);
			$tmp = substr($dir, 0, $pos);
			$pos = strrpos($tmp, '.d');
			$timestamp = substr($tmp, $pos+2);
			$result[] = array(
					'id' => $entryName,
					'timestamp' => $timestamp,
					'mime' =>  $view->getMimeType($dir.'/'.$entryName),
					'type' => $view->is_dir($dir.'/'.$entryName) ? 'dir' : 'file',
					'location' => $dir,
					);
		}
	}
	closedir($dirContent);

} else {
	$dirlisting = false;
	$query = \OC_DB::prepare('SELECT `id`,`location`,`timestamp`,`type`,`mime` FROM `*PREFIX*files_trash` WHERE `user` = ?');
	$result = $query->execute(array($user))->fetchAll();
}

$files = array();
foreach ($result as $r) {
	$i = array();
	$i['name'] = $r['id'];
	$i['date'] = OCP\Util::formatDate($r['timestamp']);
	$i['timestamp'] = $r['timestamp'];
	$i['mimetype'] = $r['mime'];
	$i['type'] = $r['type'];
	if ($i['type'] == 'file') {
		$fileinfo = pathinfo($r['id']);
		$i['basename'] = $fileinfo['filename'];
		$i['extension'] = isset($fileinfo['extension']) ? ('.'.$fileinfo['extension']) : '';
	}
	$i['directory'] = $r['location'];
	if ($i['directory'] == '/') {
		$i['directory'] = '';
	}
	$i['permissions'] = OCP\PERMISSION_READ;
	$files[] = $i;
}

function fileCmp($a, $b) {
	if ($a['type'] == 'dir' and $b['type'] != 'dir') {
		return -1;
	} elseif ($a['type'] != 'dir' and $b['type'] == 'dir') {
		return 1;
	} else {
		return strnatcasecmp($a['name'], $b['name']);
	}
}

usort($files, "fileCmp");

OCP\JSON::encodedPrint($files);