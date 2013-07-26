<?php 
// Init owncloud
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('group_share');
OCP\JSON::callCheck();

if(empty($_POST['src']) || empty($_POST['crmId'])){
	OCP\JSON::error(array('data'=> array('message'=>'No data supplied.')));
	exit();
}

/**
 * prepare files
 */
	$file  = $_POST['src'];
	if(strpos($file,';')!==false){
		$path1 = array();
		$file  = explode(';',$file);
		array_pop($file); // empty element at the end
	}
	else{
		$file = array($file);
	}


//	$_POST['crmId'] = '642e18784c6c4bb0bf6d488e7d500721';

/**
 * Fetch JSON File and Parse into array
 */
//$jsonfile = file_get_contents('https://sso.uni-muenster.de/CareerService/php/owncloud/owncloud.php?crmid='.$_POST['crmId']);



$username=$_POST['user'];
$password=$_POST['password'];
$location='https://sso.uni-muenster.de/CareerService/php/owncloud/owncloud.php?crmid='.$_POST['crmId'];


$ch = curl_init ();
curl_setopt($ch,CURLOPT_URL,$location);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_USERPWD,"$username:$password");
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$result = curl_exec ($ch); 
echo $result;

$jsonarray = json_decode($result, true);

$jsonarray = $jsonarray['Teilnehmer'];

/** TEMPORÄRE FIXES */
//$jsonarray = Array ('e_rave02', 'tonowak' );
$_POST['permissions'] = 15; /* 17 = read, 31 = write?? */
$_POST['shareType'] = 0; /* KA was das ist */
//$_POST['itemType'] = "folder"; /* sonst "file"? */
//$file = array('8479');

if($_POST['itemType'] == "dir") $_POST['itemType'] = 'folder';

/** ENDE FIXES */

$view = new \OC\Files\View('/' . OCP\User::getUser() . '/files/');

/**
 * share the files
 */
$error = 0;
$copy = $_POST['copy']=='true';
$files = array();
print_r ($jsonarray);

foreach($jsonarray as $person){
	foreach($file as $f){
				if (isset($_POST['shareType']) && isset($_POST['permissions'])) {
					try {
						$shareType = (int)$_POST['shareType'];
						$shareWith = $person;
/* Zum debuggen 

echo "share options\n";
echo $_POST['itemType']."\n";
echo $f."\n";
echo $shareType."\n";
echo $shareWith."\n";
echo $_POST['permissions']."\n";
echo "end"."\n";
*/

						//$meta = $view->getFileInfo(\OC\Files\Filesystem::normalizePath($path));

						//$f = $meta['fileid'];

						$token = OCP\Share::shareItem(
							$_POST['itemType'],
							$f,
							$shareType,
							$shareWith,
							$_POST['permissions']
						);
						if (is_string($token)) {
							OC_JSON::success(array('data' => array('token' => $token)));
						} else {
							OC_JSON::success();
						}
					} catch (Exception $exception) {
						OC_JSON::error(array('data' => array('message' => $exception->getMessage())));
					}
				}
			
		$files[] = $f; // successful shared files
	}	
}
$result = array('status'=>'success','action'=>'share','name'=>$files);
OCP\JSON::encodedPrint($result);

