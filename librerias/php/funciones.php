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
		if($stmt->execute()){
			echo json_encode("ok");
		}else{
			echo json_encode("ko");
		}
	}
	catch(PDOException $e) {
		echo "error: ".$e->getMessage();
	}
}
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
			echo json_encode("ko,".$pdo->errorinfo());
		}else {
				// si fue ok, debemos buscar los id de los profes y luego crear firma para cada uno con el id del clautro.
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
					echo json_encode("ok");
				}else{
					echo json_encode("ko");
				}
			}
		}
	}
	catch(PDOException $e) {
		echo "error: ".$e->getMessage();
	}
}
if(!empty($_POST['historicos'])){
	try{
		$arrayDatos=[];
		$stmt = $pdo->prepare("select * from claustro order by id DESC;");
		$stmt->execute();
		$filas=$stmt->fetchAll(PDO::FETCH_ASSOC);
		if($filas){
			foreach ($filas as $fila ) {
				array_push($arrayDatos,array("id"=>$fila["id"],"titulo"=>$fila["titulo"],"dia"=>$fila["dia"],"horaInicio"=>$fila["horaInicio"],"horaFin"=>$fila["horaFin"],"curso"=>$fila["curso"],"orden"=>$fila["orden"],"observacion"=>$fila["observacion"]));	
			}
		}else{
			$arry=$stmt->errorInfo();
			$errorCode=$stmt->errorCode();
			print_r(" error Varios Profes---> ".$arry);
			print_r($arry);
			print_r($errorCode);
		}
		echo json_encode($arrayDatos);
	}
	catch(PDOException $e) {
		echo "error: ".$e->getMessage();
	}
}
if(!empty($_POST['historico'])){
	try{
		$id=$_POST['historico'];
		$arrayDatos=[];
		$stmt = $pdo->prepare("select * from claustro where id=?;");
		$stmt->bindParam(1,$id);
		$stmt->execute();
		$fila=$stmt->fetch(PDO::FETCH_ASSOC);
		if($fila){
			array_push($arrayDatos,$fila);
			//echo json_encode($fila);
			$stmtFirma = $pdo->prepare("select * from firma where idClaustro=?;");
			$stmtFirma->bindParam(1,$id);
			$stmtFirma->execute();
			$filaFirma=$stmtFirma->fetchAll(PDO::FETCH_ASSOC);
			if($filaFirma){
				$dat=[];
				$prof=[];
				foreach ($filaFirma as $fila ) {
					# code...
					array_push($dat,array("id"=>$fila["id"],"claustro"=>$fila["idClaustro"],"profesor"=>$fila["idProfesor"],"firma"=>$fila["firma"]));

					$stmtProfe = $pdo->prepare("select * from profesor where id=?;");
					$stmtProfe->bindParam(1,$fila["idProfesor"]);
					$stmtProfe->execute();
					$filaProfe=$stmtProfe->fetchAll(PDO::FETCH_ASSOC);
					if($filaProfe){
						foreach ($filaProfe as $filaP ) {
							array_push($prof,array("id"=>$filaP["id"],"nombre"=>$filaP["nombre"],"email"=>$filaP["email"]));
						}
					}
				}				
				array_push($arrayDatos,$dat,$prof);
				echo json_encode($arrayDatos);
			}else{
				//echo json_encode($arrayDatos);
			}
		}else{
			$arry=$stmt->errorInfo();
			$errorCode=$stmt->errorCode();
			print_r(" error Varios Profes---> ".$arry);
			print_r($arry);
			print_r($errorCode);
		}
		//echo json_encode($arrayDatos);
	}
	catch(PDOException $e) {
		echo "error: ".$e->getMessage();
	}
}

?>