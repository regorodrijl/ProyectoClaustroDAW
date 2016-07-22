<?php
echo "<h3>Consulta de prueba LDAP</h3>";

$ds = ldap_connect('193.144.43.241',65500) or die("Could not connect to $ds");
echo "El resultado de la conexión es " . $ds . "<br />";

if ($ds) { 
    echo "Vinculando ..."; 
    $r=ldap_bind($ds,'adminweb','abc123-.,');   
                           // 
    echo "El resultado de la vinculación es " . $r . "<br />";

    // Busca la entrada de apellidos
    $sr=ldap_search($ds, "CN=G-Profes,OU=Profes,OU=SC-Usuarios,DC=sanclemente,DC=locale");  
    echo "El resultado de la búsqueda es " . $sr . "<br />";

    echo "El número de entradas devueltas es " . ldap_count_entries($ds, $sr) . "<br />";

    echo "Obteniendo entradas ...<p>";
    $info = ldap_get_entries($ds, $sr);
    echo "Los datos para " . $info["count"] . " objetos devueltos:<p>";

    for ($i=0; $i<$info["count"]; $i++) {
        echo "El dn es: " . $info[$i]["dn"] . "<br />";
        echo "La primera entrada cn es: " . $info[$i]["cn"][0] . "<br />";
        echo "La primera entrada de correo electrónico es: " . $info[$i]["mail"][0] . "<br /><hr />";
    }

    echo "Cerando la conexión";
    ldap_close($ds);

} else {
    echo "<h4>No se puede conectar al servidor LDAP</h4>";
}
?>