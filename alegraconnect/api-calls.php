<?php
session_start();
include_once("connection.php");
include('app-data.php');

$shop = $_SESSION['usernameShopify'];
$tokenShopify = $_SESSION['tokenShopify'];
$usernameAlegra = $_SESSION['usernameAlegra'];
$tokenAlegra = $_SESSION['tokenAlegra'];

//URL compuesta para comunicarse con el API de Shopify
$url = 'https://'.$api_key.':'.$tokenShopify.'@'.$shop;
$GLOBALS['url'] = $url;

//Datos para la comunicacion con API de Alegra
$authorization = base64_encode($usernameAlegra.':'.$tokenAlegra);
$headersAlegra = ['Authorization: Basic '.$authorization];
$GLOBALS['headersAlegra'] = $headersAlegra;


/*
 * Compara las monedas de Alegra y la tienda Shopify
 */
function checkSameCurrency(){
	//Recupero info de la tienda Shopify
	$url = $GLOBALS['url'];
	$handle = curl_init();
				curl_setopt($handle, CURLOPT_URL,$url.'/admin/shop.json');
				curl_setopt($handle, CURLOPT_VERBOSE, 1);
				curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, FALSE);
				curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
				$shopify_output = curl_exec ($handle);
				curl_close ($handle);

	$jsonShopify = json_decode($shopify_output, TRUE);
	$shopifyCurrency = $jsonShopify['shop']['currency'];


	//Recupero info de la cuenta de Alegra
	$headersAlegra = $GLOBALS['headersAlegra'];
	$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,"https://app.alegra.com/api/v1/company");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headersAlegra);
			$alegra_output = curl_exec ($ch);
			curl_close ($ch);

	$jsonAlegra = json_decode($alegra_output, TRUE);
	$alegraCurrency = $jsonAlegra['currency']['code'];

	//Comparo las monedas
	if($shopifyCurrency == $alegraCurrency)
		return true;
	else
		return false;

}

/*
 * Devuelve el país de la cuenta de Alegra
 */
function getAlegraCountry(){
	//Recupero info de la cuenta de Alegra
	$headersAlegra = $GLOBALS['headersAlegra'];
	$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,"https://app.alegra.com/api/v1/company");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headersAlegra);
			$alegra_output = curl_exec ($ch);
			curl_close ($ch);

	$jsonAlegra = json_decode($alegra_output, TRUE);
	$alegraCountry = $jsonAlegra['applicationVersion'];
	return $alegraCountry;
}


/*
 * Recupera los bancos asociados a la cuenta de Alegra
 */
function getBanksAlegra(){
	$headersAlegra = $GLOBALS['headersAlegra'];
	$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,"https://app.alegra.com/api/v1/bank-accounts/");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headersAlegra);
			$server_output = curl_exec ($ch);
			curl_close ($ch);

			return $server_output;
}

/*
 * Recupera los webhooks activos
 */
function getWebhooks(){
	$url = $GLOBALS['url'];
	$handle = curl_init();
				curl_setopt($handle, CURLOPT_URL,$url.'/admin/webhooks.json');
				curl_setopt($handle, CURLOPT_VERBOSE, 1);
				curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, FALSE);
				curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
				$server_output = curl_exec ($handle);
				curl_close ($handle);
				$json = json_decode($server_output, TRUE);

				sendResponse($server_output);
}

/*
 * Recupera todas las ordenes de Shopify
 */
function getOrdersShopify($url, $headersAlegra){
	$handle = curl_init();
				curl_setopt($handle, CURLOPT_URL,$url.'/admin/orders.json');
				curl_setopt($handle, CURLOPT_VERBOSE, 1);
				curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, FALSE);
				curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
				$server_output = curl_exec ($handle);
				curl_close ($handle);
				$json = json_decode($server_output, TRUE);

				$invoicesCreation = createInvoiceAlegra($json, $headersAlegra);

				return $invoicesCreation;
}


/*
 * Recupera todos los clientes de Shopify
 */
function getCustomersShopify($url, $headersAlegra){
	$handle = curl_init();
				curl_setopt($handle, CURLOPT_URL,$url.'/admin/customers.json');
				curl_setopt($handle, CURLOPT_VERBOSE, 1);
				curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, FALSE);
				curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
				$server_output = curl_exec ($handle);
				curl_close ($handle);
				$json = json_decode($server_output, TRUE);

				$customersCreation = createCustomerAlegra($json, $headersAlegra);

				return $customersCreation;
}


/*
 * Recupera todos los productos de Shopify
 */
function getProductsShopify($url, $headersAlegra){
	$handle = curl_init();
				curl_setopt($handle, CURLOPT_URL,$url.'/admin/products.json');
				curl_setopt($handle, CURLOPT_VERBOSE, 1);
				curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, FALSE);
				curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
				$server_output = curl_exec ($handle);
				curl_close ($handle);
				$json = json_decode($server_output, TRUE);

				$productsCreation = createProductAlegra($json, $headersAlegra);

				if(!recordExists('product', 0)){
					createShippingItemAlegra($headersAlegra);
				}

				return $productsCreation;
}


/*
 * Crea en Alegra los productos recibidos de Shopify
 */
function createProductAlegra($jsonShopify, $header){
	$goodResponse = true;

	$DB_ = new PDO("mysql:host={$GLOBALS['db_host']};dbname={$GLOBALS['database']};charset=utf8", $GLOBALS['db_user'], $GLOBALS['db_password']);
	foreach ($jsonShopify['products'] as $product) {
		if (!recordExists('product', $product['id'])){
			$productPrice = (float) $product['variants'][0]['price'];
			$jsonAlegra = array(
		    				'name' => $product['title'],
		                    'price' => $productPrice,
		                    'description' => $product['body_html']
		                   );

		if ($product['variants'][0]['inventory_management'] == "shopify"){
			$quantity = $product['variants'][0]['inventory_quantity'];
			$jsonAlegra['inventory'] = array(
												'unit' => 'piece',
												'unitCost' => 0,
												'initialQuantity' => $quantity
												);
		}

	    	$jsonRequest = json_encode($jsonAlegra);
	    	$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,"https://app.alegra.com/api/v1/items");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequest);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			$server_output = curl_exec ($ch);
			curl_close ($ch);

			$jsonResponse = json_decode($server_output, true);

			if(array_key_exists('code', $jsonResponse)){
				$goodResponse = FALSE;
				break;
			}
			else{
		        $query = $DB_->query("INSERT into productsMatch (idShopify, idAlegra, fkUserId) VALUES (".$product['id'].", ".$jsonResponse['id'].",".$_SESSION['userId'].")");
			}
		}
	}
	$DB_ = null;
	return $goodResponse;
}

/*
 * Crea en Alegra un producto que será usado para asociar costos de envío de Shopify
 */
function createShippingItemAlegra($header){
	$DB_ = new PDO("mysql:host={$GLOBALS['db_host']};dbname={$GLOBALS['database']};charset=utf8", $GLOBALS['db_user'], $GLOBALS['db_password']);
			$jsonAlegra = array(
		    				'name' => 'Envío Shopify',
		                    'price' => 0,
		                    'description' => 'Ítem utilizado para relacionar costos de envío de compras efectuadas a través de Shopify.'
		                   );

	    	$jsonRequest = json_encode($jsonAlegra);
	    	$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,"https://app.alegra.com/api/v1/items");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequest);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			$server_output = curl_exec ($ch);
			curl_close ($ch);

			$jsonResponse = json_decode($server_output, true);

			if(!array_key_exists('code', $jsonResponse)){
		        $query = $DB_->query("INSERT into productsMatch (idShopify, idAlegra, fkUserId) VALUES (0, ".$jsonResponse['id'].",".$_SESSION['userId'].")");
			}
	$DB_ = null;
}


/*
 * Crea en Alegra los clientes recibidos de Shopify
 */
function createCustomerAlegra($jsonShopify, $header){
	$goodResponse = true;

	$DB_ = new PDO("mysql:host={$GLOBALS['db_host']};dbname={$GLOBALS['database']};charset=utf8", $GLOBALS['db_user'], $GLOBALS['db_password']);
	foreach ($jsonShopify['customers'] as $customer) {
		if (!recordExists('customer', $customer['id'])){

			if($customer['last_name'] != null){
				$jsonAlegra = array(
		    				'name' => $customer['first_name'].' '.$customer['last_name'],
		    				'type' => 'client'
		                   );
			}
			if($customer['last_name'] == null){
				$jsonAlegra = array(
		    				'name' => $customer['first_name'],
		    				'type' => 'client'
		                   );
			}
			if($customer['email'] != null){
				$jsonAlegra['email'] = $customer['email'];
			}

			$jsonAlegra['observations'] = $customer['note'];

			//Concateno la direccion para cuentas fuera de Mexico
			$address = $customer['default_address']['address1'];

			if ($customer['default_address']['city'] != "")
				$address = $address . ", " . $customer['default_address']['city'];

			if ($customer['default_address']['zip'] != "")
				$address = $address . ", " . $customer['default_address']['zip'];

			//Json completo para Mexico y demás paises
			$jsonAlegra['address'] = array(
											'address' => $address,
											'street' => $customer['default_address']['address1'],
											'city' => $customer['default_address']['city'], 
											'country' => $customer['default_address']['country'],
											'state' => $customer['default_address']['province'],
											'zipCode' => $customer['default_address']['zip']
											);
			$jsonAlegra['phonePrimary'] = $customer['default_address']['phone'];

	    	$jsonRequest = json_encode($jsonAlegra);
	    	$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,"https://app.alegra.com/api/v1/contacts");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequest);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			$server_output = curl_exec ($ch);
			curl_close ($ch);

			$jsonResponse = json_decode($server_output, true);

			if(array_key_exists('code', $jsonResponse)){
				$goodResponse = FALSE;
				continue;
			}
			else{
		        $query = $DB_->query("INSERT into customersMatch (idShopify, idAlegra, fkUserId) VALUES (".$customer['id'].", ".$jsonResponse['id'].",".$_SESSION['userId'].")");
			}
		}
	}
	$DB_ = null;
	return $goodResponse;
}


/*
 * Crea en Alegra las facturas recibidas de Shopify
 */
function createInvoiceAlegra($jsonShopify, $header){
	$goodResponse = true;	
file_put_contents('log', json_encode($jsonShopify)."\r\n", FILE_APPEND);	

	$DB_ = new PDO("mysql:host={$GLOBALS['db_host']};dbname={$GLOBALS['database']};charset=utf8", $GLOBALS['db_user'], $GLOBALS['db_password']);
	foreach ($jsonShopify['orders'] as $order) {
		if ( (!recordExists('invoice', $order['id'])) && (array_key_exists('customer', $order)) ){
			//Formateando la fecha
			$originalDate = $order['created_at'];
			$formattedDate = date("Y-m-d", strtotime($originalDate));

			//Buscando ids de Alegra
			$clientId = findAlegraId('customer', $order['customer']['id']);

				$jsonAlegra = array(
		    				'date' => $formattedDate,
		    				'dueDate' => $formattedDate,
		    				'client' => $clientId,
		    				'items' => array()
		                   );
			foreach ($order['line_items'] as $item) {
				//Verifico si la factura tiene algun custom product (no registrado en Shopify)
				if ($item['product_id'] != null){
					$itemPrice = (float) $item['price'];
					$itemId = findAlegraId('product', $item['product_id']);
					$itemArray = array('id' => $itemId, 'quantity' => $item['quantity'], 'price' => $itemPrice);
					$jsonAlegra['items'][] = $itemArray;
				}
				else{
					$skipInvoice = true;
					break;
				}
			}

			if ($skipInvoice == true){
				$skipInvoice = false;
				continue;
			}

	    	$jsonRequest = json_encode($jsonAlegra);
	    	$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,"https://app.alegra.com/api/v1/invoices");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequest);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			$server_output = curl_exec ($ch);
			curl_close ($ch);

			$jsonResponse = json_decode($server_output, true);

			if(array_key_exists('code', $jsonResponse)){
				storeSyncErrors($order['id'], $jsonResponse);
				$goodResponse = FALSE;
				break;
			}
			else{
		        $query = $DB_->query("INSERT into invoicesMatch (idShopify, idAlegra, fkUserId) VALUES (".$order['id'].", ".$jsonResponse['id'].",".$_SESSION['userId'].")");
			}
		}
	}
	$DB_ = null;
	return $goodResponse;
}


/*
 * Verifica que el registro exista en la base de datos
 */
function recordExists($type, $shopifyId){
	$DBC_ = new PDO("mysql:host={$GLOBALS['db_host']};dbname={$GLOBALS['database']};charset=utf8", $GLOBALS['db_user'], $GLOBALS['db_password']);
	if ($type == 'product')
		$query = $DBC_->prepare("SELECT * FROM productsMatch WHERE idShopify = :sId AND fkUserId = ".$_SESSION['userId']." LIMIT 1");
	if ($type == 'customer')
		$query = $DBC_->prepare("SELECT * FROM customersMatch WHERE idShopify = :sId AND fkUserId = ".$_SESSION['userId']." LIMIT 1");
	if ($type == 'invoice')
		$query = $DBC_->prepare("SELECT * FROM invoicesMatch WHERE idShopify = :sId AND fkUserId = ".$_SESSION['userId']." LIMIT 1");

	$query-> bindParam(':sId', $shopifyId);
	$query->execute();
	$row = $query->fetchAll(PDO::FETCH_ASSOC);

	if (!empty($row)){
		$DBC_ = null;
		return true;
	}
	else{
		$DBC_ = null;
		return false;
	}
}


/*
 * Recibe el id de Shopify y devuelve el id correspondiente en Alegra
 */
function findAlegraId($type, $shopifyId){
	$DBC_ = new PDO("mysql:host={$GLOBALS['db_host']};dbname={$GLOBALS['database']};charset=utf8", $GLOBALS['db_user'], $GLOBALS['db_password']);
	if ($type == 'product')
		$query = $DBC_->prepare("SELECT * FROM productsMatch WHERE idShopify = :sId AND fkUserId = ".$_SESSION['userId']." LIMIT 1");
	if ($type == 'customer')
		$query = $DBC_->prepare("SELECT * FROM customersMatch WHERE idShopify = :sId AND fkUserId = ".$_SESSION['userId']." LIMIT 1");
	if ($type == 'invoice')
		$query = $DBC_->prepare("SELECT * FROM invoicesMatch WHERE idShopify = :sId AND fkUserId = ".$_SESSION['userId']." LIMIT 1");

	$query-> bindParam(':sId', $shopifyId);
	$query->execute();
	$row = $query->fetchAll(PDO::FETCH_ASSOC);

	if (!empty($row)){
		$DBC_ = null;
		return $row[0]['idAlegra'];
	}
	else{
		$DBC_ = null;
		return false;
	}
}


/*
 * Actualiza la informacion de los productos de Alegra (invocado por webhook)
 */
function updateProductAlegra($data, $header){
	$product = json_decode($data, TRUE);
	$productPrice = (float) $product['variants'][0]['price'];

	$jsonAlegra = array(
		    				'name' => $product['title'],
		                    'price' => $productPrice, 
		                    'description' => $product['body_html']
		                   );

	if ($product['variants'][0]['inventory_management'] == "shopify"){
		$quantity = $product['variants'][0]['inventory_quantity'];
		$jsonAlegra['inventory'] = array(
											'unit' => 'piece',
											'unitCost' => 0,
											'initialQuantity' => $quantity
											);
	}

	$jsonRequest = json_encode($jsonAlegra);
	$productAlegraId = findAlegraId('product', $product['id']);

	    	$ch = curl_init();
	    	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_URL,"https://app.alegra.com/api/v1/items/".$productAlegraId);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequest);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			$server_output = curl_exec ($ch);
			curl_close ($ch);
}


/*
 * Actualiza la informacion de los clientes de Alegra (invocado por webhook)
 */
function updateCustomerAlegra($data, $header){
	$customer = json_decode($data, TRUE);

	if($customer['last_name'] != null){
				$jsonAlegra = array(
		    				'name' => $customer['first_name'].' '.$customer['last_name'],
		    				'type' => 'client'
		                   );
	}
	if($customer['last_name'] == null){
				$jsonAlegra = array(
		    				'name' => $customer['first_name'],
		    				'type' => 'client'
		                   );
	}
	if($customer['email'] != null){
				$jsonAlegra['email'] = $customer['email'];
	}

	$jsonAlegra['observations'] = $customer['note'];

	//Concateno la direccion para cuentas fuera de Mexico
	$address = $customer['default_address']['address1'];

	if ($customer['default_address']['city'] != "")
		$address = $address . ", " . $customer['default_address']['city'];

	if ($customer['default_address']['zip'] != "")
		$address = $address . ", " . $customer['default_address']['zip'];

	//Json completo para Mexico y demás paises
	$jsonAlegra['address'] = array(
									'address' => $address,
									'street' => $customer['default_address']['address1'],
									'city' => $customer['default_address']['city'], 
									'country' => $customer['default_address']['country'],
									'state' => $customer['default_address']['province'],
									'zipCode' => $customer['default_address']['zip']
									);

	$jsonAlegra['phonePrimary'] = $customer['default_address']['phone'];

	$jsonRequest = json_encode($jsonAlegra);
	$customerAlegraId = findAlegraId('customer', $customer['id']);

	    	$ch = curl_init();
	    	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_URL,"https://app.alegra.com/api/v1/contacts/".$customerAlegraId);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequest);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			$server_output = curl_exec ($ch);
			curl_close ($ch);
}


/*
 * Actualiza la informacion de las facturas de Alegra (invocado por webhook)
 */
function updateInvoiceAlegra($data, $header){
	$order = json_decode($data, TRUE);
	//Formateando la fecha
	$originalDate = $order['created_at'];
	$formattedDate = date("Y-m-d", strtotime($originalDate));

	//Buscando ids de Alegra
	$clientId = findAlegraId('customer', $order['customer']['id']);

	$invoiceAlegraId = findAlegraId('invoice', $order['id']);

	$jsonAlegra = array(
					'date' => $formattedDate,
					'dueDate' => $formattedDate,
					'client' => $clientId,
					'items' => array(),
					'status' => 'open'
	               );

	if ($order['financial_status'] == "paid"){
		$jsonAlegra['payments'] = getTransactions($order['id']);
	}

	foreach ($order['line_items'] as $item) {
		$itemPrice = (float) $item['price'];
		$itemId = findAlegraId('product', $item['product_id']);
		$itemArray = array('id' => $itemId, 'quantity' => $item['quantity'], 'price' => $itemPrice);
		$jsonAlegra['items'][] = $itemArray;
	}

	$jsonRequest = json_encode($jsonAlegra);

	    	$ch = curl_init();
	    	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_URL,"https://app.alegra.com/api/v1/invoices/".$invoiceAlegraId);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequest);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			$server_output = curl_exec ($ch);
			curl_close ($ch);
}


/*
 * Sincroniza nuevas facturas creadas luego de la instalación de la app
 */
function createInvoiceAlegraAfterInstall($data, $header){
	$order = json_decode($data, TRUE);
file_put_contents('log', $data."\r\n", FILE_APPEND);	
	//Formateando la fecha
	$originalDate = $order['created_at'];
	$formattedDate = date("Y-m-d", strtotime($originalDate));
	if (!recordExists('invoice', $order['id'])){
		//Guardar factura como recibida
		$DB_ = new PDO("mysql:host={$GLOBALS['db_host']};dbname={$GLOBALS['database']};charset=utf8", $GLOBALS['db_user'], $GLOBALS['db_password']);
		$query = $DB_->query("INSERT into invoicesMatch (idShopify, fkUserId, status) VALUES (".$order['id'].",".$_SESSION['userId'].",0)");
		$DB_ = null;

		//Buscando ids de Alegra
		$clientId = findAlegraId('customer', $order['customer']['id']);

		$jsonAlegra = array(
					'date' => $formattedDate,
					'dueDate' => $formattedDate,
					'client' => $clientId,
					'items' => array(),
					'status' => 'open',
					'observations' => $order['note']
	               );

		//Si la cuenta de Alegra es de México, la factura tendrá creada como borrador
		if(getAlegraCountry() == "mexico"){
			$jsonAlegra['status'] = 'draft';
			$jsonAlegra['paymentMethod'] = 'credit-card';
		}


		//Si la factura tiene estado "Pagada" o "Autorizada"
		if (($order['financial_status'] == "paid") || ($order['financial_status'] == "authorized")){
			$jsonAlegra['payments'] = getTransactions($order['id']);
		}


		$totalPriceBeforeTaxes = $order['total_line_items_price'];

		foreach ($order['line_items'] as $item) {
			$itemPrice = (float) $item['price'];

			//Aplico descuento al item
			if (floatval($item['total_discount']) > 0)
				$itemPrice = $itemPrice - floatval($item['total_discount']);

			//Aplico descuento general de la factura
			if (floatval($order['total_discounts']) > 0){
				$percentage = $itemPrice / $totalPriceBeforeTaxes;
				$itemPrice = $itemPrice - floatval($order['total_discounts']) * $percentage;
			}

			$itemId = findAlegraId('product', $item['product_id']);
			$itemArray = array('id' => $itemId, 'quantity' => $item['quantity'], 'price' => $itemPrice, 'tax' => array());

			//Busco si el impuesto del item esta registrado en Alegra
			if ($item['tax_lines'][0]['price'] != 0){
				$tax = getTaxes($item['tax_lines'][0]['rate']);
				if ($tax != false)
					$itemArray['tax'][] = array('id' => $tax);
			}

			$jsonAlegra['items'][] = $itemArray;
		}

		//Si existe un costo de envío, lo añado a la factura
		if (isset($order['shipping_lines'][0])){
			$shippingItemId = findAlegraId('product', 0);
			$shippingPrice = floatval($order['shipping_lines'][0]['price']);
			$shipping = array('id' => $shippingItemId, 'quantity' => 1, 'price' => $shippingPrice);
			$jsonAlegra['items'][] = $shipping;
		}

		$jsonRequest = json_encode($jsonAlegra);
		sendResponse($jsonRequest);

    	$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://app.alegra.com/api/v1/invoices");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequest);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		$server_output = curl_exec ($ch);
		curl_close ($ch);

		$jsonResponse = json_decode($server_output, true);

		if(!array_key_exists('code', $jsonResponse)){
			$DB_ = new PDO("mysql:host={$GLOBALS['db_host']};dbname={$GLOBALS['database']};charset=utf8", $GLOBALS['db_user'], $GLOBALS['db_password']);

			$query = $DB_->query("UPDATE invoicesMatch SET idAlegra=".$jsonResponse['id'].", status=1 WHERE idShopify=".$order['id']);

			$DB_ = null;
		}
		else{
			storeSyncErrors($order['name'], $jsonResponse);
		}
	}
}

/*
 * Recupera todas las ordenes de Shopify
 */
function getTransactions($orderId){
	$url = $GLOBALS['url'];
	$handle = curl_init();
				curl_setopt($handle, CURLOPT_URL,$url.'/admin/orders/'.$orderId.'/transactions.json');
				curl_setopt($handle, CURLOPT_VERBOSE, 1);
				curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, FALSE);
				curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
				$server_output = curl_exec ($handle);
				curl_close ($handle);

	$responseJson = json_decode($server_output, TRUE);

	$payments = array();

	foreach ($responseJson['transactions'] as $transaction) {
		//Formateando la fecha
		$originalDate = $transaction['created_at'];
		$formattedDate = date("Y-m-d", strtotime($originalDate));

		$transactionArray = array(
								   'date' => $formattedDate, 
								   'account' => array('id' => $_SESSION['bank']),
								   'amount' => $transaction['amount']
								   );
		$payments[] = $transactionArray;
	}

	return $payments;
}

/*
 * Devuelve el id del impuesto que concuerde con el solicitado
 */
function getTaxes($requiredTax){
	$headersAlegra = $GLOBALS['headersAlegra'];
	$requiredTax = $requiredTax * 100;
	$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,"https://app.alegra.com/api/v1/taxes/");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headersAlegra);
			$server_output = curl_exec ($ch);
			curl_close ($ch);

	$responseJson = json_decode($server_output, TRUE);

	foreach ($responseJson as $tax) {
		$percentage = floatval($tax['percentage']);

		//Comparo los dos valores float
		if(abs($percentage-floatval($requiredTax)) < 0.00001){
			return $tax['id'];
		}
	}

	return false;
}

/*
 * Almacena los errores de sincronización
 */
function storeSyncErrors($nameShopify,$response){
	$reason = $response['message'];
	$DBC_ = new PDO("mysql:host={$GLOBALS['db_host']};dbname={$GLOBALS['database']};charset=utf8", $GLOBALS['db_user'], $GLOBALS['db_password']);

	$query = $DBC_->query("INSERT into syncErrors (nameShopify, reason, fkUserId) VALUES ('".$nameShopify."','".$reason."', ".$_SESSION['userId'].")");

	$DBC_ = null;
}

/*
 * Devuelve los errores de sincronización
 */
function getSyncErrors(){
	$DBC_ = new PDO("mysql:host={$GLOBALS['db_host']};dbname={$GLOBALS['database']};charset=utf8", $GLOBALS['db_user'], $GLOBALS['db_password']);

	$query = $DBC_->prepare("SELECT * FROM syncErrors WHERE fkUserId = :uId");

	$query-> bindParam(':uId', $_SESSION['userId']);
	$query->execute();
	$row = $query->fetchAll(PDO::FETCH_ASSOC);

	if (!empty($row)){
		$response = json_encode($row);
		$DBC_ = null;
		return $response;
	}
	else{
		$DBC_ = null;
		return false;
	}
}

/*
 * Activa la sincronizacion cuando se crea un producto
 */
function createProductCreationHook(){
	$url = $GLOBALS['url'];
	$headers= array('Accept: application/json','Content-Type: application/json'); 
	$hookRequest = array(
		    				'webhook' => array('topic'=>'products/create',
		    									'address' => 'http://shopify.alegra.com/alegraconnect/webhooks/creation-hooks.php',
		    									'format' => 'json'
		    									)
		                   );
	$jsonHook = json_encode($hookRequest);

		    $ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url."/admin/webhooks.json");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonHook);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$server_output = curl_exec ($ch);
			curl_close ($ch);

}

/*
 * Activa la sincronizacion cuando se crea un cliente
 */
function createCustomerCreationHook(){
	$url = $GLOBALS['url'];
	$headers= array('Accept: application/json','Content-Type: application/json'); 
	$hookRequest = array(
		    				'webhook' => array('topic'=>'customers/create',
		    									'address' => 'http://shopify.alegra.com/alegraconnect/webhooks/creation-hooks.php',
		    									'format' => 'json'
		    									)
		                   );
	$jsonHook = json_encode($hookRequest);

		    $ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url."/admin/webhooks.json");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonHook);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$server_output = curl_exec ($ch);
			curl_close ($ch);
}

/*
 * Activa la sincronizacion cuando se crea una factura
 */
function createOrderCreationHook(){
	$url = $GLOBALS['url'];
	$headers= array('Accept: application/json','Content-Type: application/json'); 
	$hookRequest = array(
		    				'webhook' => array('topic'=>'orders/create',
		    									'address' => 'http://shopify.alegra.com/alegraconnect/webhooks/order-creation-hook.php',
		    									'format' => 'json'
		    									)
		                   );
	$jsonHook = json_encode($hookRequest);

	    	$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url."/admin/webhooks.json");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonHook);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$server_output = curl_exec ($ch);
			curl_close ($ch);
}


/*
 * Activa la sincronizacion cuando se edita un producto
 */
function createProductUpdateHook(){
	$url = $GLOBALS['url'];
	$headers= array('Accept: application/json','Content-Type: application/json'); 
	$hookRequest = array(
		    				'webhook' => array('topic'=>'products/update',
		    									'address' => 'http://shopify.alegra.com/alegraconnect/webhooks/product-update-hook.php',
		    									'format' => 'json'
		    									)
		                   );
	$jsonHook = json_encode($hookRequest);

		    $ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url."/admin/webhooks.json");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonHook);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$server_output = curl_exec ($ch);
			curl_close ($ch);

}

/*
 * Activa la sincronizacion cuando se edita un cliente
 */
function createCustomerUpdateHook(){
	$url = $GLOBALS['url'];
	$headers= array('Accept: application/json','Content-Type: application/json'); 
	$hookRequest = array(
		    				'webhook' => array('topic'=>'customers/update',
		    									'address' => 'http://shopify.alegra.com/alegraconnect/webhooks/customer-update-hook.php',
		    									'format' => 'json'
		    									)
		                   );
	$jsonHook = json_encode($hookRequest);

		    $ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url."/admin/webhooks.json");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonHook);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$server_output = curl_exec ($ch);
			curl_close ($ch);

}

/*
 * Activa la sincronizacion cuando se edita una factura
 */
function createOrderUpdateHook(){
	$url = $GLOBALS['url'];
	$headers= array('Accept: application/json','Content-Type: application/json'); 
	$hookRequest = array(
		    				'webhook' => array('topic'=>'orders/updated',
		    									'address' => 'http://shopify.alegra.com/alegraconnect/webhooks/order-update-hook.php',
		    									'format' => 'json'
		    									)
		                   );
	$jsonHook = json_encode($hookRequest);

	    	$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url."/admin/webhooks.json");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonHook);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$server_output = curl_exec ($ch);
			curl_close ($ch);
}

/*
 * Activa la sincronizacion cuando un usuario desinstala la app
 */
function createAppUninstalledHook(){
	$url = $GLOBALS['url'];
	$headers= array('Accept: application/json','Content-Type: application/json'); 
	$hookRequest = array(
		    				'webhook' => array('topic'=>'app/uninstalled',
		    									'address' => 'http://shopify.alegra.com/alegraconnect/webhooks/app-uninstalled-hook.php',
		    									'format' => 'json'
		    									)
		                   );
	$jsonHook = json_encode($hookRequest);

	    	$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url."/admin/webhooks.json");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonHook);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$server_output = curl_exec ($ch);
			curl_close ($ch);
}

/*
 * Almacena el banco seleccionado por el usuario
 */
function storeBankSelected($bankId){
	$DB_ = new PDO("mysql:host={$GLOBALS['db_host']};dbname={$GLOBALS['database']};charset=utf8", $GLOBALS['db_user'], $GLOBALS['db_password']);
    $query = $DB_->query("UPDATE users SET bank =".$bankId." WHERE id =".$_SESSION["userId"]);
    $_SESSION['bank'] = $bankId;
    $DB_ = null;
    return true;
}


/*
 * Envía respuestas recibidas a un requestbin (para debug)
 */
function sendResponse($json){
	$headers= array('Accept: application/json','Content-Type: application/json'); 
	$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,"http://requestb.in/wc3l3lwc");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			$server_output = curl_exec ($ch);
			curl_close ($ch);
}


//Llamada a las funciones segun lo que pida el ajax
	if (isset($_POST['syncProducts'])) {
    	echo getProductsShopify($url, $headersAlegra);
	}

	if (isset($_POST['syncCustomers'])) {
    	echo getCustomersShopify($url, $headersAlegra);
	}

	if (isset($_POST['syncInvoices'])) {
    	echo getOrdersShopify($url, $headersAlegra);
	}

	if (isset($_POST['storeBank'])) {
    	echo storeBankSelected($_POST['storeBank']);
	}

	if (isset($_POST['syncErrors'])){
		echo getSyncErrors();
	}
?>
