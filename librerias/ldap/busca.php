<?php
require 'class.ldap.php';
$ldap = new ldap(Config::$ldapServidor);

echo "<h3>Consulta de prueba LDAP para un Profesor Administrador de Web. adminweb</h3>";

$usuario='adminweb';
$password= 'abc123-.,';

if ($ldap->validarUsuario("$usuario", "sanclemente.local", "$password")) {

  echo "<h3>Datos obtenidos desde Active Directory:</h3><hr/>";

            // Pasando campos a consultar.
            // echo $ldap->obtenerInfoUsuarios("catalin",array('homedrive','mail'));
            echo $ldap->infoUsuario("$usuario");
}
else
{
    die("<h3 style='color:red'>Acceso denegado al sistema.<br/><br/>Datos de acceso incorrectos, o bien usted no pertenece a un grupo con acceso a esta aplicación.</h3><h4><a href='index.php'>Volver</a></h4>");
}

echo "<hr/>";

echo "<h3>Consulta de prueba LDAP para un Profesor Normal. profeweb</h3>";

$usuario='profeweb';
$password= 'abc123-.,';

if ($ldap->validarUsuario("$usuario", "sanclemente.local", "$password")) {

  echo "<h3>Datos obtenidos desde Active Directory:</h3><hr/>";

            // Pasando campos a consultar.
            // echo $ldap->obtenerInfoUsuarios("catalin",array('homedrive','mail'));
            echo $ldap->infoUsuario("$usuario");
}
else
{
    die("<h3 style='color:red'>Acceso denegado al sistema.<br/><br/>Datos de acceso incorrectos, o bien usted no pertenece a un grupo con acceso a esta aplicación.</h3><h4><a href='index.php'>Volver</a></h4>");
}


echo "<hr/>";


echo "<h3>Consulta de prueba LDAP para un Profesor Administrador de Web. adminweb</h3>";
$ds = ldap_connect('193.144.43.241',65500) or die("Could not connect to $ds");
echo "El resultado de la conexión es " . $ds . "<br />";

if ($ds) {
    echo "Vinculando ...";
    $usuario='adminweb';
    $password= 'abc123-.,';
    $dominioldap='sanclemente.local';

    $r=ldap_bind($ds,"$usuario@$dominioldap",$password);

    $filtroBusqueda = "(|(name=$usuario*)(displayname=*$usuario*))";
    $campos = array("name", "displayname", "cn", "homedirectory", "mail", "lastLogon", "memberOf", "whencreated", "givenname");
                           //
    echo "El resultado de la vinculación es " . $r . "<br />";

    // Unidad organizativa para miembros de admin de APPs.
    $sr=ldap_search($ds,"OU=Especiais,OU=SC-Usuarios,DC=sanclemente,DC=local", $filtroBusqueda ,$campos);

    echo "El resultado de la búsqueda es " . $sr . "<br />";

    echo "El número de entradas devueltas es " . ldap_count_entries($ds, $sr) . "<br />";

    echo "Obteniendo entradas ...<p>";
    $info = ldap_get_entries($ds, $sr);
    echo "Los datos para " . $info["count"] . " objetos devueltos:<p>";

    echo "<pre>";
    print_r($info);
    echo "</pre>";

    for ($i=0; $i<$info["count"]; $i++) {
        echo "El dn es: " . $info[$i]["dn"] . "<br />";
        echo "La primera entrada cn es: " . $info[$i]["cn"][0] . "<br />";
        echo "La primera entrada de correo electrónico es: " . $info[$i]["mail"][0] . "<br />";
    }

    echo "Cerrando la conexión";
   // ldap_close($ds);

} else {
    echo "<h4>No se puede conectar al servidor LDAP</h4>";
}

echo "<h3>Consulta de prueba LDAP para un Profesor Normal ProfeWeb</h3>";
$ds = ldap_connect('193.144.43.241',65500) or die("Could not connect to $ds");
echo "El resultado de la conexión es " . $ds . "<br />";

if ($ds) {
    echo "Vinculando ...";
    $usuario='profeweb';
    $password= 'abc123-.,';
    $dominioldap='sanclemente.local';

    $r=ldap_bind($ds,"$usuario@$dominioldap",$password);

    $filtroBusqueda = "(|(name=$usuario*)(displayname=*$usuario*))";
    $campos = array("name", "displayname", "cn", "homedirectory", "mail", "lastLogon", "memberOf", "whencreated", "givenname");
                           //
    echo "El resultado de la vinculación es " . $r . "<br />";

    // Unidad organizativa para miembros de admin de APPs.
    $sr=ldap_search($ds,"OU=Informatica,OU=Profes,OU=SC-Usuarios,DC=sanclemente,DC=local", $filtroBusqueda ,$campos);

    echo "El resultado de la búsqueda es " . $sr . "<br />";

    echo "El número de entradas devueltas es " . ldap_count_entries($ds, $sr) . "<br />";

    echo "Obteniendo entradas ...<p>";
    $info = ldap_get_entries($ds, $sr);
    echo "Los datos para " . $info["count"] . " objetos devueltos:<p>";

    echo "<pre>";
    print_r($info);
    echo "</pre>";

    for ($i=0; $i<$info["count"]; $i++) {
        echo "El dn es: " . $info[$i]["dn"] . "<br />";
        echo "La primera entrada cn es: " . $info[$i]["cn"][0] . "<br />";
        echo "La primera entrada de correo electrónico es: " . $info[$i]["mail"][0] . "<br />";
    }

    echo "Cerrando la conexión";
   // ldap_close($ds);

} else {
    echo "<h4>No se puede conectar al servidor LDAP</h4>";
}
?>