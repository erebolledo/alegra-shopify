<?php
session_start();
include_once('../connection.php');
include_once('../app-data.php');

$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
$data = file_get_contents('php://input');
$verified = verify_webhook($data, $hmac_header, $sharedKey);

//Si se verifica la procedencia del hook
if ($verified == true){
	$user = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
	if (userExists($user)){
		include_once('../api-calls.php');
		updateCustomerAlegra($data, $headersAlegra);
	}
}

/*
 * Verifica la procedencia del hook comparando con hash
 */
function verify_webhook($data, $hmac_header, $sharedKey)
{
  $calculated_hmac = base64_encode(hash_hmac('sha256', $data, $sharedKey, true));
  return ($hmac_header == $calculated_hmac);
}


/*
 * Verifica si el usuario existe en la base de datos
 */
function userExists($shop){
	$DB_ = new PDO("mysql:host={$GLOBALS['db_host']};dbname={$GLOBALS['database']};charset=utf8", $GLOBALS['db_user'], $GLOBALS['db_password']);
	$query = $DB_->prepare("SELECT * FROM users WHERE usernameShopify = :shop LIMIT 1");
	$query-> bindParam(':shop', $shop);
	$query->execute();
	$userRow = $query->fetchAll(PDO::FETCH_ASSOC);

	if (!empty($userRow)){
		$_SESSION['userId'] = $userRow[0]['id'];
		$_SESSION['usernameShopify'] = $userRow[0]['usernameShopify'];
		$_SESSION['tokenShopify'] = $userRow[0]['tokenShopify'];
		$_SESSION['usernameAlegra'] = $userRow[0]['usernameAlegra'];
		$_SESSION['tokenAlegra'] = $userRow[0]['tokenAlegra'];
		$_SESSION['status'] = $userRow[0]['status'];
		$DB_ = null;
		return true;
	}
	else{
		$DB_ = null;
		return false;
	}
}

?>
