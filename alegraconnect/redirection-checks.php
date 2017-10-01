<?php
session_start();
ob_start();
include_once("connection.php"); 
include_once("app-data.php");


//El usuario está ingresando a la app
if (isset($_SERVER['QUERY_STRING'])){

	//Obtener la información del query string
	$queryString = $_SERVER['QUERY_STRING'];
	parse_str($queryString, $qs_array);

	$web = 'https://'.$qs_array["shop"];
	$userShop = $qs_array["shop"];

	//Es el primer acceso a la app o está reinstalando
	if (isset($qs_array["code"])){

		//Verificación de seguridad para OAuth
		$message = 'code='.$qs_array["code"].'&'.'shop='.$qs_array["shop"].'&'.'state='.$qs_array["state"].'&'.'timestamp='.$qs_array["timestamp"];
		$calculatedHmac = hash_hmac('sha256' , $message , $sharedKey ); 

		//Si el hmac corresponde con el enviado, solicita login alegra
		if (($qs_array["hmac"] == $calculatedHmac) && (!userExists($userShop))){
		  getShopifyToken($web, $api_key, $sharedKey, $qs_array);
		  header('Location: alegra-login.php');
		}
		//Si el usuario está reinstalando, actualizo token shopify
		else if (($qs_array["hmac"] == $calculatedHmac) && (userExists($userShop))){
		  updateShopifyToken($web, $api_key, $sharedKey, $qs_array);

		  //El usuario no tiene credenciales de Alegra registradas
		  if (($_SESSION['usernameAlegra'] == null) || ($_SESSION['tokenAlegra'] == null)){
		  	header('Location: alegra-login.php');
		  }
		  else{
		  	//El usuario esta activo
		  	if ($_SESSION['status'] == 1)
		  		header('Location: sync-window.php');
		  	else
		  		header('Location: '.$web.'/admin/oauth/authorize?client_id=d0936cc5d317004db934c533e566962a&scope=read_orders,read_customers,read_products&redirect_uri=http://shopify.alegra.com/alegraconnect/redirection-checks.php&state=7');
		  }
		}
	}
	//El usuario ya ha accedido anteriormente
	else{

		//Verificando usuario
		$accessMessage = 'shop='.$qs_array["shop"].'&'.'timestamp='.$qs_array["timestamp"];
		$accessHmac = hash_hmac('sha256' , $accessMessage , $sharedKey );

		//No concuerda la verificación
		if($qs_array["hmac"] != $accessHmac){
			echo "Usuario no autorizado.";
		}

		//La verificación concuerda pero el usuario no existe en la base de datos
		if(($qs_array["hmac"] == $accessHmac) && (!userExists($qs_array["shop"]))){
			header('Location: '.$web.'/admin/oauth/authorize?client_id=d0936cc5d317004db934c533e566962a&scope=read_orders,read_customers,read_products&redirect_uri=http://shopify.alegra.com/alegraconnect/redirection-checks.php&state=7');
		}

		//Verificación y usuario correctos
		if(($qs_array["hmac"] == $accessHmac) && (userExists($qs_array["shop"]))){
			//Si no tiene credenciales de Alegra
			if (($_SESSION['usernameAlegra'] == null) || ($_SESSION['tokenAlegra'] == null)){
				header('Location: alegra-login.php');
			}
			else{
				//El usuario esta activo
			  	if ($_SESSION['status'] == 1)
			  		header('Location: sync-window.php');
			  	else
			  		header('Location: '.$web.'/admin/oauth/authorize?client_id=d0936cc5d317004db934c533e566962a&scope=read_orders,read_customers,read_products&redirect_uri=http://shopify.alegra.com/alegraconnect/redirection-checks.php&state=7');
			}
		}
	}
}

//El usuario introdujo sus credenciales de Alegra
if(isset($_POST["email"]) && isset($_POST["password"])) { 
getAlegraToken($_POST["email"], $_POST["password"]);
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
		$_SESSION['bank'] = $userRow[0]['bank'];
		$DB_ = null;
		return true;
	}
	else{
		$DB_ = null;
		return false;
	}
}

/*
 * Recupera y almacena el token de Shopify
 */
function getShopifyToken($web, $api_key, $sharedKey, $qs_array){
  $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL,$web.'/admin/oauth/access_token');
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_VERBOSE, 1);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($handle, CURLOPT_POSTFIELDS,
              'client_id='.urlencode($api_key).'&client_secret='.urlencode($sharedKey).'&code='.urlencode($qs_array["code"]));
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($handle);
        curl_close ($handle);

        $jsonResponse = json_decode($server_output, true);
        $DB_ = new PDO("mysql:host={$GLOBALS['db_host']};dbname={$GLOBALS['database']};charset=utf8", $GLOBALS['db_user'], $GLOBALS['db_password']);
        $query = $DB_->query("INSERT into users (usernameShopify, tokenShopify, usernameAlegra, tokenAlegra, status) VALUES ('".$GLOBALS['userShop']."', '".$jsonResponse["access_token"]."', null, null, 1)");
        $_SESSION['userId'] = $DB_->lastInsertId();
        $_SESSION['usernameShopify'] = $GLOBALS['userShop'];
        $_SESSION['tokenShopify'] = $jsonResponse["access_token"];
        $DB_ = null;
}

/*
 * Renueva el token de shopify y cambia el estado del usuario a activo (ocurre cuando reinstala)
 */
function updateShopifyToken($web, $api_key, $sharedKey, $qs_array){
  $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL,$web.'/admin/oauth/access_token');
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_VERBOSE, 1);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($handle, CURLOPT_POSTFIELDS,
              'client_id='.urlencode($api_key).'&client_secret='.urlencode($sharedKey).'&code='.urlencode($qs_array["code"]));
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($handle);
        curl_close ($handle);

        $jsonResponse = json_decode($server_output, true);
        $DB_ = new PDO("mysql:host={$GLOBALS['db_host']};dbname={$GLOBALS['database']};charset=utf8", $GLOBALS['db_user'], $GLOBALS['db_password']);
        $query = $DB_->query("UPDATE users SET tokenShopify ='".$jsonResponse["access_token"]."', status = 1 WHERE id =".$_SESSION["userId"]);
        $_SESSION['tokenShopify'] = $jsonResponse["access_token"];
        $DB_ = null;

        //Activar los hooks
		include_once('api-calls.php');
        createProductCreationHook();
        createCustomerCreationHook();
        createOrderCreationHook();
        createProductUpdateHook();
        createCustomerUpdateHook();
        createOrderUpdateHook();
        createAppUninstalledHook();
}

/*
 * Recupera y almacena el token de Alegra
 */
function getAlegraToken($email, $password){
    $json = array(
    				'email' => $email,
                    'password' => $password
                   );

    $jsonRequest = json_encode($json);

    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,"https://app.alegra.com/api/v1/login");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequest);

	curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

	$server_output = curl_exec ($ch);

	curl_close ($ch);

	$responseJson = json_decode($server_output, TRUE, JSON_UNESCAPED_UNICODE);

	//Si devuelve el token, lo guardo. Si no, redirijo a login.
	if(array_key_exists('token', $responseJson)){
		$DB_ = new PDO("mysql:host={$GLOBALS['db_host']};dbname={$GLOBALS['database']};charset=utf8", $GLOBALS['db_user'], $GLOBALS['db_password']);
        $query = $DB_->query("UPDATE users SET usernameAlegra = '".$email."', tokenAlegra = '".$responseJson["token"]."' WHERE id = ".$_SESSION["userId"]);
        $_SESSION['usernameAlegra'] = $email;
        $_SESSION['tokenAlegra'] = $responseJson["token"];
        $DB_ = null;

        //Activar los hooks
        include_once('api-calls.php');
        createProductCreationHook();
        createCustomerCreationHook();
        createOrderCreationHook();
        createProductUpdateHook();
        createCustomerUpdateHook();
        createOrderUpdateHook();
        createAppUninstalledHook();

        header('Location: sync-window.php');
	}
	else{
		$_SESSION['loginError'] = $responseJson["message"];
		header('Location: alegra-login.php');
	}
}

?>
