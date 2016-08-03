<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once("./conexion.php");
$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

if (!empty($_POST['datos'])){
	//print_r(json_decode($_POST['datos']));
	try{	
		$result = $_POST['datos'];
		
		foreach ($result as  $key) {
			$stmt = $pdo->prepare("insert into profesor(nombre,email) values(:nombre,:email)");
			$stmt->bindParam(':nombre', $key['apellidos']);
			$stmt->bindParam(':email', $key['email']);
			$stmt->execute();
		}    
		
		echo "ok";
		if($stmt->execute()){
			echo " ok, insert in profesor";
		}else{
			echo " ko, insert in profesor";
		}
	}
	catch(PDOException $e) {
		echo "error: ".$e->getMessage();
	}
}else {
	if(!empty($_POST['claustro'])){
		try{	
			$res = $_POST['claustro'];
			//creamos el claustro
			$stmt = $pdo->prepare("insert into claustro(titulo,dia,horaInicio,horaFin,curso,orden,observacion) values(:titulo,:dia,:horaInicio,:horaFin,:curso,:orden,:observacion)");
			$stmt->bindParam(':titulo', $res['titulo']);
			$stmt->bindParam(':dia', $res['dia']);
			$stmt->bindParam(':horaInicio', $res['horaInicio']);
			$stmt->bindParam(':horaFin', $res['horaFin']);
			$stmt->bindParam(':curso', $res['curso']);
			$stmt->bindParam(':orden', $res['orden']);
			$stmt->bindParam(':observacion', $res['observacion']);
			$stmt->execute();

			if($stmt ==false){
				echo " ko, insert in claustro";
				print_r($pdo->errorinfo());
			}else {
				// si fue ok, debemos buscar los id de los profes y luego crear firma para cada uno con el id del clautro.
				echo " ok, insert in claustro";
				// ultimo id insertado en claustro.
				$lastId = $pdo->lastInsertId(); 
				
				$profesores = $res["profesores"];
				// Para quitar espacios antes y despues de string
				$profesores = array_map('trim', $profesores);

				$arrayIdProfes=[];
				foreach ($profesores as $key ) {
					//buscamos id profes.
					$stmt = $pdo->prepare("select * from profesor where nombre=?");
					$stmt->bindParam(1, $key,PDO::PARAM_STR);
					$stmt->execute();
					$filas=$stmt->fetch(PDO::FETCH_ASSOC);
					if($filas){
						array_push($arrayIdProfes,array("id"=>$filas['id']));	
					}else{
						$arry=$stmt->errorInfo();
						$errorCode=$stmt->errorCode();
						print_r(" error Varios Profes---> ".$arry);
						print_r($arry);
						print_r($errorCode);
					}
				}
				foreach ($arrayIdProfes as $key ) {
					// insertamos en firma
					$stmt = $pdo->prepare("insert into firma(idClaustro,idProfesor) values(:idClaustro,:idProfesor)");
					$stmt->bindParam(':idClaustro', $lastId);
					$stmt->bindParam(':idProfesor', $key["id"]);
					
					if($stmt->execute()){
						echo " ok, insert in firma";
					}else{
						echo " ko,  insert in firma";
					}
				}
			}
		}
		catch(PDOException $e) {
			echo "error: ".$e->getMessage();
		}
	}
}
?>