<?php

// Para que funcione LDAP en XAMPP.
// Habilitar extension=php_ldap.dll
// Copiar xampp/php/libsasl.dll  al directorio xampp/apache/bin
// Activar también extension=php_openssl.dll en xamp/php/php.ini
// Intro a LDAP : http://ldapman.org/articles/sp_intro.html
// http://www.php.net/manual/es/book.ldap.php

/*
  GLOSARIO:
  DN -> Distinguised Name: Todas las entradas almacenadas en un directorio LDAP tienen un único "Distinguished Name," o DN
  El DN para cada entrada está compuesto de dos partes: el Nombre Relativo Distinguido (RDN por sus siglas en ingles, Relative Distinguished Name) y la localización dentro del directorio LDAP donde el registro reside. l RDN es la porción de tu DN que no está relacionada con la estructura del árbol de directorio.

  DC -> Domain Component.
  OU -> Organizational Unit
 * CN -> Common Name: la mayoría de los objetos que almacenarás en LDAP utilizarán su valor cn como base para su RDN

  EJEMPLO:
  El DN base de mi directorio es dc=foobar,dc=com
  Estoy almacenando todos los registros LDAP para mis recetas en ou=recipes
  El RDN de mi registro LDAP es cn=Oatmeal Deluxe

  Dado todo esto, ¿cuál es el DN completo del registro LDAP para esta receta de comida de avena ? Recuerda, se lee en órden inverso, hacia atrás - como los nombres de máquina en los DNS.

  cn=ComidaDeAvena Deluxe,ou=recipes,dc=foobar,dc=com

 */
  require_once 'class.config.php';

  class ldap {

    private $_servidorLDAP;
    private $_puerto;
    private $_conexion = false;
    private $_filtroBusqueda;
    private $_ou = false;
    private $_camposMostrarLDAP = array("givenName", "sn", "displayName", "name", "streetAddress", "cn", "telephoneNumber", "st", "physicalDeliveryOfficeName", "c", "co", "wWWHomePage", "description", "homeDirectory", "mail", "lastLogon", "memberOf", "whenCcreated",);
// Estas variables se utilizarán para acceder desde otros módulos de diferentes aplicaciones.
    public $givenName = false; // Nombre
    public $sn = false;  // Apellidos
    public $displayName = false; // Apellidos, Nombre
    public $streetAddress = Config::direccion; // Dirección
    public $cn = false; // Canonical Name
    public $telephoneNumber = Config::telefono;
    public $st = Config::provincia;  // Provincia
    public $physicalDeliveryOfficeName = false; // Organización
    public $c = false; // Código del País ES
    public $co = false; // Nombre del País España
    public $wWWHomePage = Config::website; // Página web personal
    public $description = false; // Descripción
    public $mail = Config::dominiodefecto;
    public $dn = false;
    public $homeDirectory = false; // Directorio home del usuario

    /**
     * Realiza la conexión a un servidor LDAP.
     *
     * @param string $servidor  Puede contener la ip, nombre o si es conexión segura ldaps://ip
     * @param int $puerto puerto de conexión no segura, por defecto 389.
     */

    public function __construct($servidor) {
      $this->_servidorLDAP = $servidor;
      $this->_puerto = Config::$ldapPuerto;
      $this->_ou = Config::$OUdefecto;

        if (strstr($servidor, 'ldaps://') === false) // No es una conexión segura.
        $this->_conexion = ldap_connect($this->_servidorLDAP, $this->_puerto) or die("Imposible conectar al servidor ldap $this->_servidorLDAP");
        else    // Es una conexión segura.
        $this->_conexion = ldap_connect($this->_servidorLDAP) or die("Imposible conectar al servidor ldap $this->_servidorLDAP");

// Los datos van en UTF-8 por defecto en LDAP version 3.
        ldap_set_option($this->_conexion, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->_conexion, LDAP_OPT_REFERRALS, 0);
      }

      public function __destruct() {
        ldap_unbind($this->_conexion);
      }

    /**
     * Valida un usuario contra el LDAP y en la OU proporcionada.
     * Si no se le pasa OU lo hace con la OUdefecto indicada en el fichero de config.php
     *
     * @param string $usuario
     * @param string $password
     * @param string $dominioldap
     * @return boolean
     */
    public function validarUsuario($usuario, $dominioldap, $password, $ou = false) {
      if ($this->_conexion) {
// Intenta autenticarse en el ldap con un usuario y una contraseña.
        $autenticacion = @ldap_bind($this->_conexion, "$usuario@$dominioldap", "$password");
        if (!$autenticacion)
          return false;
        else {
// Si se le pasa una OU busca ese usuario en esa OU, sino busca en la por defecto del fichero de config.php.
          if ($ou) {
            $this->_ou = $ou;
          }

          if ($this->infoUsuario($usuario) != "Error")
            return true;
          else
            return false;
        }
      }
      else {
        die("No hay conexión con el servidor LDAP.");
      }
    }

    /**
     * Función infoUsuario obtiene la información de usuario, dentro de su OU y la almacena en variables public del objeto.
     * Devuelve Error si no se encuentra el usuario en la unidad organizativa indicada.
     * O devuelve la información para imprimir.
     *
     * @param string $usuario
     * @param array $camposMostrar opcional, por ejemplo, array("mail", "sn", "cn").
     */
    public function infoUsuario($usuario, $camposMostrar = false) {

      $cadena = '';

      $this->_filtroBusqueda = "(name=$usuario)";

      if (!$camposMostrar)
            $campos = $this->_camposMostrarLDAP;    // Muestra los campos por defecto de la clase.
          else
            $campos = $camposMostrar;

// Recorremos los resultados
          $busqueda = ldap_search($this->_conexion, $this->_ou, $this->_filtroBusqueda, $campos);

// Ordenamos los resultados por surname 'sn'.
          ldap_sort($this->_conexion, $busqueda, 'sn');

// Recorremos todos los resultados comenzando en el primero.
          $entrada = ldap_first_entry($this->_conexion, $busqueda);

// Leemos los atributos(campos) obtenidos que hemos obtenido en esa entrada, por que independientemente de los que hayamos solicitado,
// puede pasar que alguno de esos atributos no exista como mail.

        $atributos = ldap_get_attributes($this->_conexion, $entrada); // Es un array de atributos.
// echo $atributos[$i]  muestra cn whencreated displayName....

        $cadena="<table border=1><tr><thead><th>Atributo LDAP</th><th>Datos almacenados en el Directorio</th></thead></tr>";
        for ($i = 0; $i < $atributos['count']; $i++) {
          $dato = ldap_get_values($this->_conexion, $entrada, $atributos[$i]);
// Devuelve: Array ( [0] => asirMD - Veiga Martinez, Bruno [count] => 1 )
// Cuando tenemos más de un atributo repetido en el ldap.
          if ($dato['count'] != 1) {
            for ($j = 0; $j < $dato['count']; $j++)
              $cadena.="<tr><td>".$atributos[$i] . "</td><td>" . $dato[$j] . "</td></tr>";
          }
          else
            $cadena.="<tr><td>".$atributos[$i] . "</td><td>" . $dato[0] . "</td></tr>";

// Actualizamos las variables públicas del usuario con el que estamos trabajando.
          switch ($atributos[$i]) {
            case 'givenName':
            $this->givenName = $dato[0];
            break;
            case 'sn':
            $this->sn = $dato[0];
            break;
            case 'displayName':
            $this->displayName = $dato[0];
            break;
            case 'streetAddress':
            $this->streetAddress = $dato[0];
            break;
            case 'cn':
            $this->cn = $dato[0];
            break;
            case 'telephoneNumber':
            $this->telephoneNumber = $dato[0];
            break;
            case 'st':
            $this->st = $dato[0];
            break;
            case 'physicalDeliveryOfficeName':
            $this->physicalDeliveryOfficeName = $dato[0];
            break;
            case 'c':
            $this->c = $dato[0];
            break;
            case 'wWWHomePage':
            $this->wWWHomePage = $dato[0];
            break;
            case 'description':
            $this->description = $dato[0];
            break;
            case 'mail':
            $this->mail = $dato[0];
            break;
            case 'dn':
            $this->dn = $dato[0];
            break;
            case 'homeDirectory':
            $this->homeDirectory = $dato[0];
            break;
          }
        }

        if ($cadena == '')
          return "Error";
        else {
          $cadena.= "<tr><td>DN del usuario</td><td>" . ldap_get_dn($this->_conexion, $entrada)."</td></tr>";
          $this->dn = ldap_get_dn($this->_conexion, $entrada);
          $cadena.= "</table><hr/>";
        }

        return $cadena;
      }

    /**
     * Se le pasa un nuevo e-mail y el DN del usuario a actualizar.
     * Usa el usuario ldapwrite para escribir en el LDAP.
     *
     * @param type $nuevomail
     * @param type $dn
     */
    function actualizarEmail($nuevomail, $dn) {
        /*
          $entry["objectclass"][0] = "email";
          $entry["objectclass"][1] = "ieee802Device"; // add an auxiliary objectclass
          $entry["macAddress"][0] = "aa:bb:cc:dd:ee:ff";
         *      CN=veiga,OU=Informatica,OU=Profes,OU=SC-Usuarios,DC=sanclemente,DC=local
         */
          $nuevosdatos['mail'] = $nuevomail;
          ldap_modify($this->_conexion, $dn, $nuevosdatos) or die("Problema actualizando e-mail en AD.");
          echo "modificado ok";
        }

    /**
     * Se le pasa un array asociativo con los datos y también se le pasa el DN del usuario a actualizar.
     * Usa el usuario ldapwrite para escribir en el LDAP.
     *
     * @param type $nuevosdatos // Array asociativo con las siguientes claves: mail,
     * @param type $dn
     */
    function actualizarDatos($nuevosdatos, $dn) {
//print_r($nuevosdatos);
      ldap_modify($this->_conexion, $dn, $nuevosdatos) or die("Problema actualizando sus datos en AD.");
//echo "Respuesta Active Directory";
    }

    /**
     * Devuelve true si el usuario pertenece al grupo en cuestión.
     *
     *
     */
    public function pertenenciaGrupo($usuario, $grupoChequeo) {
        //$this->_filtroBusqueda = "(&(sAMAccountName=$usuario) (memberOf=CN=G-Profes,OU=Profes,OU=SC-Usuarios,DC=sanclemente,DC=local))";
      $this->_filtroBusqueda = "(&(objectClass=user) (samAMAccountName=$usuario) (memberOf=CN=G-Profes,OU=Profes,OU=SC-Usuarios,DC=sanclemente,DC=local))";
      echo $this->_filtroBusqueda;

      $busqueda = ldap_search($this->_conexion, $this->_ou, $this->_filtroBusqueda, $this->_camposMostrarLDAP);
      if (count(ldap_get_entries($this->_conexion, $busqueda)) == 1) {
        return "No pertenece";
      } else {
        echo "Pertenece";
      };
    }

    /**
     *
     * @param string $usuario
     * @param array $camposMostrar
     */
    public function ojearUsuarios($usuario, $camposMostrar = false) {
        // Filtros de búsqueda.
        // http://www.centos.org/docs/5/html/CDS/ag/8.0/Finding_Directory_Entries-LDAP_Search_Filters.html
        // https://confluence.atlassian.com/display/DEV/How+to+write+LDAP+search+filters
        // http://grover.open2space.com/content/use-php-create-modify-active-directoryldap-entries

      $this->_filtroBusqueda = "(|(name = $usuario*)(displayName = *$usuario*))";

        // Un array de los atributos requeridos, por ejemplo, array("mail", "sn", "cn"). Nótese que el "dn" siempre es devuelto independientemente de qué tipos de atributos sean requeridos.
      if (!$camposMostrar)
            $campos = $this->_camposMostrarLDAP;    // Muestra los campos por defecto de la clase.
          else
            $campos = $camposMostrar;

        // Una primera forma de recorrer los resultados.
        //$busqueda=ldap_search($conexion, $UnidadOrganizativa ,"name = $usuario");
        /* $info = ldap_get_entries($this->_conexion, $this->_filtroBusqueda); // Devuelve un array.
          echo "Encontrados " . $info['count'] . " registros.<br/>";
         * */


        /*
         *
         * print_r($info);
         *
          return_value["count"] = nÃºmero de entradas en el en el resultado
          return_value[0] : se refiere a los detalles de la primer entrada

          return_value[i]["dn"] =  DN de la i-Ã©sima en el resultado

          return_value[i]["count"] = NÃºmero de atributos en la i-Ã©sima entrada
          return_value[i][j] = NOMBRE del j-Ã©simo atributo en la i-Ã©sima entrada en el resultado

          return_value[i]["attribute"]["count"] = nÃºmero de valores para
          el atributo en la i-Ã©sima entrada
          return_value[i]["attribute"][j] = j-Ã©simo valor del atributo en la i-Ã©sima entrada
         */



        // Otra forma de recorrer los resultados.
          $busqueda = ldap_search($this->_conexion, $this->_ou, $this->_filtroBusqueda, $campos);

        // Ordenamos los resultados por surname 'sn'.
          ldap_sort($this->_conexion, $busqueda, 'sn');

        // Recorremos todos los resultados comenzando en el primero.
          for ($entrada = ldap_first_entry($this->_conexion, $entrada); $entrada != false; $entrada = ldap_next_entry($this->_conexion, $entrada)) {
            /*
              $atributos = ldap_get_attributes($this->_conexion, $entrada);
              echo "Atributos Mostrados: " . $atributos['count'] . "<br/>";


              for ($i = 0; $i < $atributos["count"]; $i++) {
              echo $atributos[$i] . "<br />";
              }

              $valores=ldap_get_values($this->_conexion,$entrada,"name");
              print_r($valores);

             */

            // ldap_get_values -> Obtiene todos los valores de un resultado dado.
            // $valores=ldap_get_values($conexion,$entrada,'displayName');
            // print_r($valores);
            // Devuelve: Array ( [0] => asirMD - Veiga Martinez, Bruno [count] => 1 )
            // Vamos a imprimir todos los atributos obtenidos.

            /*
             * ldap_get_values;
             *  return_value["count"] = Número de valores para el atributo
              return_value[0] = Primer valor del atributo
              return_value[i] = i-ésimo valor del atributo
             *
             *
             *
             */

            // Leemos los atributos(campos) obtenidos que hemos obtenido en esa entrada, por que independientemente de los que hayamos solicitado,
            // puede pasar que alguno de esos atributos no exista como mail.
            $atributos = ldap_get_attributes($this->_conexion, $entrada); // Es un array de atributos.
            // echo $atributos[$i]  muestra cn whencreated displayName....

            for ($i = 0; $i < $atributos['count']; $i++) {
              $dato = ldap_get_values($this->_conexion, $entrada, $atributos[$i]);
                // Devuelve: Array ( [0] => asirMD - Veiga Martinez, Bruno [count] => 1 )
              if ($dato['count'] != 1) {
                for ($j = 0; $j < $dato['count']; $j++)
                  echo $atributos[$i] . ": " . $dato[$j] . "<br/>";
              }
              else
                echo $atributos[$i] . ": " . $dato[0] . "<br/>";
            }

            echo "DN del usuario: " . ldap_get_dn($this->_conexion, $entrada);
            echo "<hr/>";
          }
        }

    /**
     *
     * @param string $usuario
     * @param array $camposMostrar
     */

    public function getProfes(){
      //config
     // $ldapserver = '193.144.43.241';
      $ldapuser      = 'adminweb';  
      $ldappass     = 'abc123-.,';
      //   OU=Informatica,
      $ldaptree    = 'OU=Profes,OU=SC-Usuarios,DC=sanclemente,DC=local';

      //$ldapconn = ldap_connect($ldapserver,'65500') or die("Could not connect to LDAP server.");
      $ldapbind = ldap_bind($this->_conexion, $ldapuser, $ldappass) or die ("Error trying to bind: ".ldap_error($ldapconn));

      if ($ldapbind) {
        $result = ldap_search($this->_conexion,$ldaptree, "(cn=*)") or die ("Error in search query: ".ldap_error($ldapconn));
        $data = ldap_get_entries($this->_conexion, $result);
        $arrayDatos=[];
       // $datos= new stdClass();
       //var_dump($data["count"]); 
       // hago tanta comprobación, ya que devolvía errores porque hay campos que no tienen datos.
        for ($i=0; $i<$data["count"]; $i++) {
          if(isset($data[$i]["displayname"][0])){
           if(isset($data[$i]["mail"][0])) {
            //$datos->profe=$data[$i]["cn"][0];
            //$datos->email=$data[$i]["mail"][0];
             array_push($arrayDatos,array("profe"=>$data[$i]["cn"][0],"email"=>$data[$i]["mail"][0],"apellidos"=>$data[$i]["displayname"][0]));
           } else {
           // $datos->profe=$data[$i]["cn"][0];
            array_push($arrayDatos,array("profe"=>$data[$i]["cn"][0],"email"=>"sin email","apellidos"=>$data[$i]["displayname"][0]));
          }
        }else{
          if(isset($data[$i]["mail"][0])) {
            array_push($arrayDatos,array("profe"=>$data[$i]["cn"][0],"email"=>$data[$i]["mail"][0],"apellidos"=>"Sin nombre completo"));
          } else {
           // $datos->profe=$data[$i]["cn"][0];
            array_push($arrayDatos,array("profe"=>$data[$i]["cn"][0],"email"=>"sin email","apellidos"=>"Sin nombre completo"));
          }
        }

      }
      return $arrayDatos;
      //return $datos;
        //return json_encode($arrayDatos);
    } else {

      return "LDAP bind failed...";
    }
    ldap_close($this->_conexion);
  }

// Fin de la clase.)

    /* Campos LDAP.
      objectClass
      cn
      sn
      description
      userPassword
      givenName
      givenName
      distinguishedName
      instanceType
      whenCreated
      whenChanged
      displayName
      uSNCreated
      memberOf
      uSNChanged
      name
      objectGUID
      userAccountControl
      codePage
      countryCode
      homeDirectory
      homeDrive
      scriptPath
      pwdLastSet
      primaryGroupID
      objectSid
      accountExpires
      sAMAccountName
      sAMAccountType
      userPrincipalName
      objectCategory
      dSCorePropagationData
     */

    }