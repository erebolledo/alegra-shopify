<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
  <title>Alegra Connect To Shopify</title>
  <meta charset="utf-8">
  <!-- Favicon Icon -->
  <link rel="shortcut icon" href="https://cdn1.alegra.com/images/favicon.ico">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <!-- Custom CSS -->
  <link href="css/style.css" rel="stylesheet">
  <!-- Fa Icons CDN -->
  <script src="https://use.fontawesome.com/73e6e7db31.js"></script>
  <!-- jQuery CDN -->
  <script src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>

</head>
<body>
  <div class="col-md-10 col-md-offset-1 upper-margin">
    <div class="col-md-12">
      <div class="row border-logo">
        <img src="img/alegra.png" class="alegra-logo-size">
      </div>
      <div class="row">
        <div class="col-md-8 col-md-offset-2">
          <div class="col-md-12">
            <div class="col-md-12 lower-margin">
              <h4 class="subtitle">Introduce el nombre de tu tienda <strong>Shopify</strong> para crear el link de instalación</h4>
                <section>
                  <form id="installLink" method="post" role="login">
                  <div class="col-md-8" style="padding-right: 5px;">
                    <input id="shopName" type="text" placeholder="Nombre de tienda" class="form-control input-lg" value="" required/>
                    </div>
                    <div class="col-md-4" style="text-align: left; padding: 0;">
                    <label style="font-size: 20px; padding-top: 30px;">.MyShopify.com</label>
                  </div>
                    <div class="pwstrength_viewport_progress"></div>
    
                    <button id="installBtn" class="btn btn-lg btn-primary btn-block alegra-login-button">Instalar</button>                    
                  </form>
                  
                  <div class="form-links">
                    <a href="http://alegra.com" target="_blank">Alegra.com</a>
                  </div>
                </section>
              </div>
            </div>
        </div>
        <div class="col-md-4 col-md-offset-4 app-align">
          <h5 class="app-text">Descarga nuestra app:</h5>
          <div class="row">
            <a href="https://play.google.com/store/apps/details?id=co.alegra.app" target="_blank"><img src="img/google_play_badge.png" class="apps-icon-size">
            </a>
            <a href="https://itunes.apple.com/WebObjects/MZStore.woa/wa/viewSoftware?id=1084859936&mt=8" target="_blank">
            <img src="img/app-store.png" class="apps-icon-size app-separation">
            </a>
          </div>
          <div class="row social-margin">
            <button type="button" class="btn btn-primary btn-circle" onclick="window.open('https://www.facebook.com/alegra.web')"><i class="fa fa-facebook fa-lg" aria-hidden="true"></i>
            </button>
            <button type="button" class="btn btn-primary btn-circle" onclick="window.open('https://twitter.com/AlegraWeb')"><i class="fa fa-twitter fa-lg" aria-hidden="true"></i>
            </button>
            <button type="button" class="btn btn-primary btn-circle" onclick="window.open('https://plus.google.com/+AlegraCo')"><i class="fa fa-google-plus fa-lg" aria-hidden="true"></i>
            </button>
            <button type="button" class="btn btn-primary btn-circle" onclick="window.open('https://www.instagram.com/alegraweb')"><i class="fa fa-instagram fa-lg" aria-hidden="true"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
<script type="text/javascript">
  $("#installBtn").click(function(event) {
    event.preventDefault();
    if ($("#shopName").val() != "") {
      $(location).attr('href', "https://" + $("#shopName").val() + ".myshopify.com/admin/oauth/authorize?client_id=d0936cc5d317004db934c533e566962a&scope=read_orders,read_customers,read_products,read_fulfillments&redirect_uri=http://shopify.alegra.com/alegraconnect/redirection-checks.php&state=7");
    }
});
</script>