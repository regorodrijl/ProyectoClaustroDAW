<?php 
error_reporting(E_ALL);
require("./librerias/ldap/class.ldap.php");

$ldap = new ldap(Config::$ldapServidor);
//print_r($ldap->getProfes());
$result = $ldap->getProfes();

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
  

  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/css/bootstrap-select.min.css">

  <!-- Latest compiled and minified JavaScript -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script>



</head>
<body>

  <div class="jumbotron" align="center">
    <div id="titulo" class="container" align="center">
      <h3>Aplicación de Configuración de Claustros.</h3>
    </div>
    <h2 id="reset">ClaustroiNet</h2>
  </div>
  <div class="container" >
    <div class="row" align="center">
      <div class="col-md-4">
        <button id="btnNuevo" class="btn btn-default" type="button">Nuevo Claustro</button>
      </div>
      <div class="col-md-4">
        <button id="btnHistorico" class="btn btn-default" type="button">Histórico de Claustros</button>
      </div>
      <div class="col-md-4">
        <button id="btnProfes" class="btn btn-default" type="button">Actualizar Profesores</button>
      </div>
    </div>
    <br>

    <div class="row" id="nuevo">
      <div>
       <div class="" id="niz" >
        <form class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label">Título Claustro:</label>
            <div class="col-sm-4">
              <input name="titulo" type="text" id="tituloClaustro" size="50" required placeholder="Escriba un título para el Claustro." />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label">Día:</label>
            <div class="col-sm-4">
              <input name="fecha" type="date" id="fecha" required value="<?php echo date('Y-m-d'); ?>" />
            </div>
            <label class="col-sm-2 control-label">Curso:</label>
            <div class="col-sm-4">
              <input name="curso" type="text" id="curso" required placeholder="Curso: 2015-2016" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label">Hora Inicio:</label>
            <div class="col-sm-4">
              <input type="time" name="fecha" id="horaInicio" required value="<?php echo date('H:i'); ?>">
            </div>
            <label class="col-sm-2 control-label">Hora Fin:</label>
            <div class="col-sm-4">
              <input type="time" name="fecha" id="horaFin" required value="<?php echo date('H:i'); ?>">
            </div>
          </div>
        </form>
        <br>
        <label>Orden del día:</label>
        <textarea id="orden" class="form-control" rows="4" required placeholder="Escriba la orden del día."></textarea>
        <label>Observaciones:</label>
        <textarea id="observacion" class="form-control" rows="4" placeholder="Alguna observación?"></textarea>
      </div>
      <select class="selectpicker" required multiple id="selecProfe" data-live-search="true" title="Seleccione Profesor">  
        <?php    
        $result = $ldap->getProfes();

        foreach ($result as  $key) {
          ?>
          <option value=" <?php  echo $key['apellidos'] ?> " >
            <?php  echo $key['apellidos'] ?>
          </option> 
          <?php
        }    
        ?> 
      </select>
      <div id="seleccion"></div>
    </div>
    <div>
     <button id="crearClaustro" class=".col-md-3 .col-md-offset-3 center-block">Crear Claustro</button>
   </div>
 </div>

 <br> 
 <div class="row" id="historico">
  <div class="col-xs-6 col-md-4" id="hiz" >
    <label>Listado de Claustros:</label>
    <div id="historicoClaustros"></div>
  </div>
  <div class="col-xs-12 col-sm-6 col-md-8" id="hdr">
    <label>Datos del Claustro:</label>
    <div class="jumbotron" id="datosClaustroHistorico"></div>
  </div>
  <div class="col-md-4 center-block">
    <button type="button" id="imprimir" class="btn btn-success center-block">Imprimir!</button>
  </div>
  <div class="col-md-4 center-block">
    <button type="button" id="editar" class="btn btn-primary center-block">Editar!</button>
  </div>
  <div class="col-md-4 center-block">
  <button type="button" id="borrar" class="btn btn-danger center-block">Borrar!</button>
  </div>
</div>
<hr>

<footer>
  <p>&copy; 2016 regorodrijl.</p>
</footer>
</div>
<script type="text/javascript">
  $(document).ready(function(){
    $("#historico").hide();
    $("#nuevo").hide();
    $("#imprimir").hide();
    $("#editar").hide();
    $("#borrar").hide();
    $("#reset").click(function(){
      location.reload();
    });
    // NUEVO
    $("#btnNuevo").click(function(){
      $("#titulo").hide();
      $("#nuevo").show();
      $("#historico").hide();
      $("#selecProfe").change(function () {
        var str = "PROFESORES SELECCIONADOS:<br>";
        $( "select option:selected" ).each(function() {
          str += $( this ).text() + "<br>";
        });
        $( "#seleccion" ).html("<div>"+str+"</div>");
      }).change();
    });// fin nuevo 
    //HISTORICO
    $("#btnHistorico").click(function(){
      $("#titulo").hide();
      $("#nuevo").hide();
      $("#historico").show();
      $.ajax({
        url: "./librerias/php/funciones.php",
        type: 'post',
        dataType: 'json',
        data: {historicos: "Claustro historico"},
        success:function(respuesta){
          console.log(respuesta[0]);
          var tabla="<table id='tabla' border='1px'><tr><th>Título</th><th>Día</th><th>Cursos</th></tr>";
          for (var i in respuesta){
            tabla += "<tr id="+respuesta[i].id+"><td>"+respuesta[i].titulo+"</td><td>"+respuesta[i].dia+"</td><td>"+respuesta[i].curso+"</td></tr>";
          }
          tabla+="</table>";
          // imprimimos tabala
          $("#historicoClaustros").html(tabla);
          // hacemos clickeable
          $('tr').click( function() {
            $(this).parents('table').find('tr').each( function( index, element ) {
              $(element).removeClass('color');
            } );
            $(this).addClass('color');
          } );     
          $("#tabla tr td").click(function(){
            $("#imprimir").show();
            $("#editar").show();
            $("#borrar").show();
            var x = $(this).parent("tr");
            $.ajax({
              url: "./librerias/php/funciones.php",
              type: 'post',
              dataType: 'json',
              data: {historico:x.attr('id')},
              success:function(respuesta){
                console.log(respuesta);
                var datos="<div>";
                datos+="<p><label><strong>Titulo:&nbsp; </strong></label>"+respuesta[0].titulo+"</p>";
                datos+='<div class="row"><div class="col-md-5"><p><label><strong>Curso:&nbsp; </strong></label>'+respuesta[0].curso+'</p></div><div class="col-md-5"><p><label><strong>Día:&nbsp; </strong></label>'+respuesta[0].dia+'</p></div></div>';
                datos+='<div class="row"><div class="col-md-5"><p><label><strong>Hora Inicio:&nbsp; </strong></label>'+respuesta[0].horaInicio+'</p></div><div class="col-md-5"><p><label><strong>Hora Fin:&nbsp; </strong></label>'+respuesta[0].horaFin+'</p></div></div>';
                datos+='<p class="lead"><strong>Orden del día: </strong><article>'+respuesta[0].orden+'</article></p><p class="lead"><strong>Observaciones realizadas: </strong><article>'+respuesta[0].observacion+'</article></p>';
                datos+="<strong>Profesores: </strong><br>";
                for(var i=0;i<respuesta[1].length;i++){
                  datos+='nombre: '+respuesta[2][i].nombre+" firma: "+respuesta[1][i].firma+"<br>";
                }
                datos+="</div>";
                $("#datosClaustroHistorico").html(datos);
              }
            }).fail( function() {
              alert("Error al buscar los claustros!");
            });
          });
        }
      });
    });// fin historico
    // ACTUALIZAR Profesores por retocar
    $("#btnProfes").click(function(){
      var datos =  '<?php echo json_encode($result); ?>';
      datos=JSON.parse(datos);
      console.log(typeof(datos));
      $.ajax({
        url: "./librerias/php/funciones.php",
        type: 'post',
        dataType: 'json',
        data: {datos:datos},          
        success:function(respuesta){
          if(respuesta=="ok"){
            alert("Profesores actualizados correctamente!!");
          }else alert("Error al actualizar!");
        }
      }).fail( function() {
        alert("Error al actualizar!");
      });
    });// fin Botón Atualizar Profes
    // CREAR NUEVO CLAUSTRO
    $("#crearClaustro").click(function(){
      var profes=[];
      $("#selecProfe option:selected").each(function() {
        profes.push($(this).val());
      });
      console.log($("#tituloClaustro").val());
      console.log(typeof($("#tituloClaustro").val()));
      //validar
      if($("#tituloClaustro").val().trim() === ''){
        console.log("algo");
      }else console.log("nada");
      if($("#tituloClaustro").val() && $("#fecha").val() == "" && $("#horaInicio").val() == "" && $("#horaFin").val() == "" && $("#curso").val() == "" && $("#orden").val() == ""){
        alert("Relleno los campos: Título, día, Fecha, Hora Inicio, Hora Fin, Curso y Orden del Día.")
      }else{
        var claustro={
          "titulo":$("#tituloClaustro").val(),
          "dia": $("#fecha").val(),
          "horaInicio":$("#horaInicio").val(),
          "horaFin":$("#horaFin").val(),
          "curso":$("#curso").val(),
          "orden":$("#orden").val(),
          "observacion":$("#observacion").val(),
          "profesores":profes };
          $.ajax({
            url: "./librerias/php/funciones.php",
            type: 'post',
            dataType: 'json',
            data: {claustro:claustro},
            success:function(respuesta){
              if(respuesta=="ok"){
                alert("Creado correctamente!");
              }else alert("Error al crear un claustro!");
            }
          }).fail( function() {
            alert("Error al crear un claustro!");
          });
        }
    });//fin botón CrearClaustro
  });
</script>
</body>
</html>
