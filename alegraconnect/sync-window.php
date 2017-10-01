<?php
session_start();
error_reporting(0);
include_once('api-calls.php');
?>
<!DOCTYPE html>
<html>
<head>
	<title>Sincronización</title>
	<meta charset="utf-8">
  	<!-- Favicon Icon -->
	<link rel="shortcut icon" href="https://cdn1.alegra.com/images/favicon.ico">
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<!-- Header Font -->
	<link href="https://fonts.googleapis.com/css?family=Comfortaa|Pacifico" rel="stylesheet">
	<!-- Custom CSS -->
	<link href="css/style.css" rel="stylesheet">
	<!-- Datatables CSS -->
	<link href="https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css" rel="stylesheet">

</head>
<body>
	

	<div class="row content-margin">
		<div class="col-md-6 col-md-offset-3">
			<img src="img/alegra.png" class="alegra-logo-size sync-logo-size">
			<br>
			<?php 
				if(!isset($_SESSION['userId'])){
					echo '<div class="alert alert-warning">
  							<strong>Para sincronizar tu información debes acceder desde el panel de control de tu tienda Shopify, en la sección "Apps", o también puedes acceder a través del siguiente enlace </strong><a class="green-link" href="http://shopify.alegra.com/alegraconnect/create-account.php" target="_blank"> aquí.</a>
						 </div>';
				}
			?>
			<div id="status-message">
				<!-- Contenido cargado a través de javascript -->
			</div>
			<?php 
				if(isset($_SESSION['userId']) && checkSameCurrency() == FALSE){
					echo '<div class="alert alert-warning">
  							<strong>La moneda principal de tu cuenta de Alegra es distinta a la moneda de tu tienda de Shopify. Por favor cambia la moneda de tu tienda</strong> <a class="green-link" href="https://'.$_SESSION['usernameShopify'].'/admin/settings/general#shop_currency" target="_blank"> aquí.</a>
						 </div>';
				}
				else if (isset($_SESSION['userId'])){
			?>
			<div id="main-div" class="row rounded-box">
				<div id="table-div">
				<p class="align-center sync-title"><strong>AUTOMÁTICAMENTE SINCRONIZAREMOS TUS CLIENTES Y PRODUCTOS</strong></p>
					<div class="row">
						<p> 
							Por favor indica la cuenta de banco de Alegra a la cual deseas que queden asociados los pagos recibidos desde Shopify:
						</p>
					</div>
					<div class="col-md-6 col-md-offset-3">
						<?php
						  $response = getBanksAlegra();
						  $bankArray = json_decode($response, true);
						  foreach ($bankArray as $bank) {
						  	if($bank['status'] == 'active')
						  		echo '<input type="radio" name="bank" value="'.$bank['id'].'"> '.$bank['name'].'<br>';
						  }
						?>
						<label>&nbsp;</label>
						<br>
					</div>
					<div class="col-md-6 col-md-offset-3">
						<button id="sync-button" class="btn btn-lg btn-primary btn-block alegra-sync-button" onclick="syncProducts()">Sincronizar</button>
					</div>
				</div>
			</div>
			<br>
			<a href="" id="error-button" class="error-link">Ver errores de sincronización</a>
			<?php } ?>
		</div>
	</div>
	<br>

	<!-- jQuery Script -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
	<!-- Bootstrap JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<!-- Datatables Plugin -->
	<script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
</body>
</html>

<script type="text/javascript">

//Cargo el banco seleccionado
$(function() {

	var bankId = <?php echo json_encode($_SESSION['bank']); ?>;

	if (bankId == null)
		bankId = 2;

    var $radios = $('input:radio[name=bank]');
    if($radios.is(':checked') === false) {
        $radios.filter('[value='+bankId+']').prop('checked', true);
    }

});

$('#error-button').click(function(e) {
	e.preventDefault();
    displaySyncErrorsTable();
});

function syncProducts(){
	closeAlert();
	displaySyncStatus();
	$.ajax({ 
     url: 'api-calls.php',
     data: {'syncProducts': 'x'},
     type: 'post',
     success: function(response) {
                if (response != false){
                	storeChosenBank();
                	syncCustomers();
              }
              else{
					displaySyncStatus();
              		//displayFailMessage();
              		displaySuccessMessage();
              }
          }
    });
}

function syncCustomers(){
	$.ajax({ 
     url: 'api-calls.php',
     data: {'syncCustomers': 'x'},
     type: 'post',
     success: function(response) {
                if (response != false){
                	displaySuccessMessage();
					displaySyncStatus();
              }
              else{
					displaySyncStatus();
              		//displayFailMessage();
              		displaySuccessMessage();
              }
          }
    });
}

function syncInvoices(){
	$.ajax({ 
     url: 'api-calls.php',
     data: {'syncInvoices': 'x'},
     type: 'post',
     success: function(response) {
                if (response != false){
                	displaySuccessMessage();
					displaySyncStatus();
              }
              else{
					displaySyncStatus();
              		//displayFailMessage();
              		displaySuccessMessage();
              }
          }
    });
}

function storeChosenBank(){
	var bankId = $('input[name=bank]:checked').val();
	$.ajax({ 
     url: 'api-calls.php',
     data: {'storeBank': bankId},
     type: 'post',
     success: function(response) {
     		//console.log('Banco asociado exitosamente');
          }
    });
}

function displaySuccessMessage(){
    closeAlert();
    $("#status-message").addClass("alert alert-success");
    $("#status-message").append('<a href="#" class="close" data-dismiss="alert" aria-label="close" onclick="closeAlert()">&times;</a>' +   
                                    '<strong>Éxito: </strong> La sicronización se ha completado correctamente. <a href="https://app.alegra.com" target= "_blank" class="green-link" >Ir a Alegra.</a>');
}

function displayFailMessage(){
    closeAlert();
    $("#status-message").addClass("alert alert-danger");
    $("#status-message").append('<a href="#" class="close" data-dismiss="alert" aria-label="close" onclick="closeAlert()">&times;</a>' +   
                                    '<strong>Error: </strong> La sicronización ha fallado :(');
}

function closeAlert(){
    $("#status-message").empty();
    $("#status-message").removeClass();
}

function displaySyncStatus(){
    if($("#sync-button").text() == "Sincronizar"){
    	$("#sync-button").text("Sincronizando...");
	$("#sync-button").attr("disabled", true);
    }
    else{
    $("#sync-button").text("Sincronizar");
    $("#sync-button").attr("disabled", false);
    }
}

function displaySyncErrorsTable(){
	$("#main-div").empty();
	closeAlert();
	$("#main-div").append('<p class="align-center sync-title"><strong>A CONTINUACIÓN SE MUESTRAN LAS ÓRDENES QUE NO SE PUDIERON SINCRONIZAR CON ALEGRA:</strong></p>' +
								'<table id="syncErrors-table">'+
								'<thead>'+
								'<tr>'+
								'<th>Orden</th>'+
								'<th>Motivo</th>'+
								'<th>Fecha</th>'+
								'</tr>'+
								'<tbody id="syncErrors-body">'+
								'</tbody>'+
								'</table>');

	$.ajax({ 
     url: 'api-calls.php',
     data: {'syncErrors': 'x'},
     async: false,
     type: 'post',
     success: function(response) {
                if (response != false){
                	var json = jQuery.parseJSON(response);
                	for (var i in json){
                		$("#syncErrors-body").append('<tr>'+
                									 '<td>'+ json[i].nameShopify+'</td>'+
                									 '<td>'+ json[i].reason+'</td>'+
                									 '<td>'+ formatDate(json[i].date)+'</td>'+
                									 '</tr>');

                	}
              }
          }
    });

    $('#syncErrors-table').DataTable({
                    "language": {
                        "lengthMenu": "Mostrar _MENU_ registros por página",
                        "zeroRecords": "No existen registros para mostrar",
                        "info": "Página _PAGE_ de _PAGES_",
                        "infoEmpty": "No se han registrado errores de sincronización",
                        "infoFiltered": "(Filtrado de _MAX_ registros totales)",
                        "sSearch": "Buscar:",
                        "oPaginate": {
                            "sFirst":    "Primero",
                            "sLast":     "Último",
                            "sNext":     "Siguiente",
                            "sPrevious": "Anterior"
			            }
			        },
			        "order": [[ 0, "desc" ]]
			    });

    $('#error-button').text('Volver');
    $('#error-button').off('click');
    $('#error-button').click(function() {
	    location.reload();
	});
}

function formatDate(inputFormat) {
  function pad(s) { return (s < 10) ? '0' + s : s; }
  var d = new Date(inputFormat);
  return [pad(d.getDate()), pad(d.getMonth()+1), d.getFullYear()].join('/');
}
</script>
