<?php 
//require("./librerias/busca.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="description" content="Proyecto DAW IES San Clemente. Claustro de Profesores.">
  <meta name="author" content="Jose Luis Rego Rodríguez y Óscar Fuentes Maña">
  <link rel="icon" href="../../favicon.ico">
  <link rel="stylesheet" type="text/css" href="css/ClaustroiNet.css">
  <title>ClaustroiNet</title>
  <!-- jQuery CDN versión 2.2.4 ya que bootstrap no sopeorta la 3 -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

  <!-- Optional theme -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
  
</head>
<body>

  <div class="jumbotron" align="center">
    <div id="titulo" class="container" align="center">
      <h3>Aplicación de Configuración de Claustros.</h3>
    </div>
    <h2>ClaustroiNet</h2>
  </div>
  <div class="container" >
    <div class="row" align="center">
      <div class="col-md-6">
        <button id="btnNuevo" type="button">Nuevo Claustro</button>
      </div>
      <div class="col-md-6">
        <button id="btnHistorico" type="button">Histórico de Claustro</button>
      </div>
    </div>
    <br>

    <div class="row" id="nuevo">
     <div class="col-xs-12 col-sm-6 col-md-8" id="niz" >

      <form class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-2 control-label">Día:</label>
          <div class="col-sm-4">
            <input name="fecha" type="date" id="fecha" value="<?php echo date('Y-m-d'); ?>" />
          </div>
          <label class="col-sm-2 control-label">Curso:</label>
          <div class="col-sm-4">
            <input name="curso" type="text" id="curso" />
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">Hora Inicio:</label>
          <div class="col-sm-4">
            <input type="time" name="fecha" id="horaInicio" value="<?php echo date('H:i'); ?>">
          </div>
          <label class="col-sm-2 control-label">Hora Fin:</label>
          <div class="col-sm-4">
            <input type="time" name="fecha" id="horaFin" value="<?php echo date('H:i'); ?>">
          </div>
        </div>
      </form>
      <br>
      <label>Orden del día:</label>
      <textarea class="form-control" rows="4" placeholder="Escriba la orden del día."></textarea>
      <label>Observaciones:</label>
      <textarea class="form-control" rows="2" placeholder="Alguna observación?"></textarea>
    </form>
  </div>

  <div class="col-xs-6 col-md-4" id="ndr">
   <select id="historicoSelect">
     <option value="" disabled selected>Seleccione Profesor</option>
     <option value="volvo"></option>
   </select>
   <br>
   <label>Profesores seleccionados:</label>
   <textarea class="form-control" rows="2" placeholder="Seleccione algún profesor para que aparezca aquí."></textarea>
 </div>
 <div>
   <button>Crear Claustro</button>
 </div>
</div>
<br> 
<div class="row" id="historico">
  <div class="col-xs-6 col-md-4" id="hiz" >

    <label>Listado de Claustros ordenado por:</label>
    <select id="historicoSelect">
      <option value="volvo"></option>
    </select>
    <textarea class="form-control" rows="4"></textarea>
  </div>
  <div class="col-xs-12 col-sm-6 col-md-8" id="hdr">

    <label>Datos del Claustro:</label>
    <div class="jumbotron">
      <laber id="datosClaustro"> Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
        tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
        quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
        consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
        cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
        proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
      </laber>
    </div>

  </div>
</div>
<hr>

<footer>
  <p>&copy; 2016 regorodrijl.</p>
</footer>
</div>
<script type="text/javascript">
  $(document).ready(function(){
   // $("#nuevo").hide();
   // $("#historico").hide();

   $("#btnNuevo").click(function(){
     $("#titulo").hide();
     $("#nuevo").show();
     $("#historico").hide();

   });
   $("#btnHistorico").click(function(){
    $("#titulo").hide();
    $("#nuevo").hide();
    $("#historico").show();

  });

 });
</script>


</body>
</html>
