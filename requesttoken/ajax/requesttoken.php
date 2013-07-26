<?php
OCP\JSON::checkAppEnabled('requesttoken');
OCP\JSON::checkLoggedIn();

OCP\JSON::encodedPrint(array('token' => OC_Util::callRegister())); //$_SESSION['requesttoken']));
?>