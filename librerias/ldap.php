<?php
require("datos.php");
/**
 * Búsqueda iterativa sobre grupos de usuario para determinar si un usuario pertenece, o no
 * a dicho grupo.
 * 
 * @param string $user 
 * @param array $groupsToFind 
 * @return boolean - devuelve true si el usuario $user es miembro de $groupsToFind 
 * 
 * $pertenece = checkUserInGroups('Juan Pérez Ramírez', array('Usuarios-Contabilidad', 'Usuarios-Informatica'));
 * $pertenece tendrá valor true si el usuario pertenece a alguno de los grupos agregados como array al segundo argumento
 */

function checkUserInGroups($user, $groupsToFind) {
       $ldapconn = ldap_connect(_LDAP_HOST_, _LDAP_PORT_)
                or die("Nos se puede conectar "._LDAP_HOST_);

        if ($ldapconn){
                ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION,3);
                ldap_set_option($ldapconn, LDAP_OPT_REFERRALS,0);
                $ldapbind = ldap_bind($ldapconn, _LDAP_PRDN_, _LDAP_PASS_);

                if ($ldapbind) {

			/* Recorremos el array donde se almacenan los grupos con permisos de lectura */
			for($group=0;$group<count($groupsToFind);$group++){
    			
				$groupDN = "CN=".$groupsToFind[$group].",OU=Usuarios,OU=Grupos,OU=miEmpresa,DC=miDominio,DC=local";
				$filter = '(memberof:1.2.840.113556.1.4.1941:='.$groupDN.')';

				$userDN = _LDAP_DN_;
				$userDN = str_replace('*replace_name*', $user, $userDN);

				$search = ldap_search($ldapconn, $userDN, $filter, array('dn'), 1);
    				$items = ldap_get_entries($ldapconn, $search);
				
				if ($items['count'] > 0){
					ldap_close($ldapconn);
					return true;
				}
			}

			/* Si llegamos a este punto, el usuario NO se encuentra en el grupo  */
			ldap_close($ldapconn);
        		return false;
		
		/* Fallo al hacer bind en LDAP */
		} else {
			ldap_close($ldapconn);
			return false;
		}
	/* Fallo al realizar la conexión en LDAP */
	}
	else {
		return false;
	}
}

?>
