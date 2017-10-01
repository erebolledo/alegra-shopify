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

</head>
<body>
  <div class="col-md-10 col-md-offset-1 upper-margin">
    <div class="col-md-12">
      <div class="row border-logo">
        <img src="img/alegra.png" class="alegra-logo-size">
      </div>
<!--       <div class="col-md-6 col-md-offset-3"> 
         
      </div> -->
      <div class="row">
        <div class="col-md-6 login-border">
          <div class="col-md-12">
            <div class="col-md-12 lower-margin">
              <h4 class="subtitle">Ingresa la información de tu cuenta de <strong>Alegra</strong> para enlazarla con tu información de <strong>Shopify</strong></h4>
              <?php 
              //Si existe un error en el login, despliega mensaje 
                if(isset($_SESSION['loginError'])){ 
                  $errorMsg = $_SESSION['loginError']; 
                  echo '<div class="alert alert-danger">'; 
                  echo '<strong>Error: </strong> '.$errorMsg; 
                  echo '</div>'; 
                  unset($_SESSION['loginError']); 
                } 
              ?>
                <section>
                  <form method="post" action="redirection-checks.php" role="login">
                    <h4 class="subtitle-2">Ingresa con tu <strong>usuario y contraseña</strong> de Alegra:</h4>
                    <input type="email" name="email" placeholder="Usuario" class="form-control input-lg" value="" required/>
                    
                    <input type="password" name="password" class="form-control input-lg" id="password" placeholder="Contraseña" required/>
                    
                    
                    <div class="pwstrength_viewport_progress"></div>
    
                    <button type="submit" name="go" class="btn btn-lg btn-primary btn-block alegra-login-button">Ingresar</button>
                    <div>
                      <a href="https://app.alegra.com/user/remember-password" target="_blank" class="subtitle-2"><h5><strong>Olvidé mi contraseña</strong></h5></a>
                    </div>
                    
                  </form>
                  
                  <div class="form-links">
                    <a href="http://alegra.com" target="_blank">Alegra.com</a>
                  </div>
                </section>
              </div>
            </div>
        </div>
        <div class="col-md-6">
          <img src="img/registrate-alegra.png" class="register-img">
          <div class="col-md-10 col-md-offset-1">
            <h4 class="register-text-1">Si no eres usuario de <strong class="register-text-2">Alegra</strong>, regístrate ya y <strong class="register-text-2">obtendrás 30 días de uso gratis</strong>:</h4>
            <button class="btn btn-lg btn-primary btn-block crear-cuenta-btn register-button" onclick="window.open('https://app.alegra.com/user/register')">Crear Cuenta</button>
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