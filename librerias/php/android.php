<?php 
require_once('./conexion.php');
//$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
header('Content-type: application/json');

if($_SERVER['REQUEST_METHOD'] == "POST"){
	if(!empty($_POST['nombre'])&&!empty($_POST['img'])){
		$nombre=trim($_POST['nombre']);
		$img=$_POST['img'];
		$hoy=date('Y-m-d');

		//file_put_contents('nombre.txt', file_get_contents($nombre));
		
		@file_put_contents('img.txt', file_get_contents($img));

		$stmt = $pdo->prepare('select * from claustro where activo=true and dia=?;');
		$stmt->bindParam(1,$hoy);
		$stmt->execute();
		$filas=$stmt->fetchAll(PDO::FETCH_ASSOC);
		if($filas){
			foreach ($filas as $fila ) {
				$idClaustro=$fila['id'];
			}
			$stmtProfesor = $pdo->prepare("select * from profesor where nombre=?");
			$stmtProfesor->bindParam(1, $nombre,PDO::PARAM_STR);
			$stmtProfesor->execute();
			$filasProfe=$stmtProfesor->fetch(PDO::FETCH_ASSOC);
			if($filasProfe){
				$idProfesor=$filasProfe['id'];

				$stmtFirma = $pdo->prepare("update firma set firma=:firma where idClaustro=:idClaustro and idProfesor=:idProfesor");
				$stmtFirma->bindParam(':firma', $img);
				$stmtFirma->bindParam(':idClaustro', $idClaustro);
				$stmtFirma->bindParam(':idProfesor', $idProfesor);
				if($stmtFirma->execute()){
					//echo json_encode(array('status' => 'ok', 'msg' => 'Firma Guardada, nombre del profesor: '. $nombre));
				}else {
					echo json_encode(array('status' => 'ko', 'msg' => 'Error al guardar!'));
				}
			}else{
				echo json_encode(array('status' => 'ko', 'msg' => 'Problema al seleccionar profe.'));
			}
		}else{
			echo json_encode(array('status' => 'ko', 'msg' => 'Problema al seleccionar claustro.'));
		}
	}else{
		//$data = file_get_contents('php://input');
		//echo json_encode(array('status' => 'ko', 'msg' => 'No entra.'.var_dump($data)));
		//echo json_encode(array('status' => 'ko', 'msg' => 'No entra.'.base64_decode($data)));
		echo json_encode(array('status' => 'ko', 'msg' =>"No entro!"));
		var_dump($_POST['img']);

	}
}else{
	try{
		$control=false;
		$hoy=date('Y-m-d');
		$arrayDatos=[];
		$noHay=[];
		$stmt = $pdo->prepare('select * from claustro where activo=true order by id DESC;');
		$stmt->execute();
		$filas=$stmt->fetchAll(PDO::FETCH_ASSOC);

		if($filas){
			foreach ($filas as $fila ) {
				$id=$fila['id'];
				$diaClaustro=$fila['dia'];
				$fecha_actual = strtotime($hoy);
				$fecha_entrada = strtotime($diaClaustro);
				if($fecha_actual === $fecha_entrada){
					$control=true;
					array_push($arrayDatos,array('id'=>$fila['id'],'titulo'=>$fila['titulo'],'dia'=>$fila['dia'],'horaInicio'=>$fila['horaInicio'],'horaFin'=>$fila['horaFin'],'curso'=>$fila['curso'],'orden'=>$fila['orden'],'observacion'=>$fila['observacion']));
				}else{
					$control=false;
				//array_push($noHay,array('id'=>0,'nombre'=>'Aun falta algun tiempo'));
				//echo json_encode("Aun falta algun tiempo");
				}
			}
		//echo json_encode($arrayDatos);
		//echo json_encode($fila);

			if($control==true){
				$stmtFirma = $pdo->prepare('select * from firma where idClaustro=?;');
				$stmtFirma->bindParam(1,$id);
				$stmtFirma->execute();
				$filaFirma=$stmtFirma->fetchAll(PDO::FETCH_ASSOC);
				if($filaFirma){
					$firma=[];
					$prof=[];
					foreach ($filaFirma as $fila ) {
						array_push($firma,array('id'=>$fila['id'],'claustro'=>$fila['idClaustro'],'profesor'=>$fila['idProfesor'],'firma'=>$fila['firma']));

						$stmtProfe = $pdo->prepare('select * from profesor where id=?;');
						$stmtProfe->bindParam(1,$fila['idProfesor']);
						$stmtProfe->execute();
						$filaProfe=$stmtProfe->fetchAll(PDO::FETCH_ASSOC);
						if($filaProfe){
							foreach ($filaProfe as $filaP ) {
								array_push($prof,array('id'=>$filaP['id'],'nombre'=>$filaP['nombre'],'email'=>$filaP['email']));
							}
						}
					}				
			//array_push($arrayDatos,$firma,$prof);
					echo json_encode($prof);
				}else{
				//echo json_encode($arrayDatos);
				}
			}else{
				array_push($noHay,array('id'=>0,'nombre'=>'No hay claustro para hoy'));
				echo json_encode($noHay);
			}
		}else{
			$arry=$stmt->errorInfo();
			$errorCode=$stmt->errorCode();
			array_push($noHay,array('id'=>0,'nombre'=>'No hay claustro para hoy'));
			echo json_encode($noHay);
			//echo json_encode('No hay datos errores '.$arry.' codigo: '.$errorCode);
		}
	}
	catch(PDOException $e) {
		echo  json_encode('error: '.$e->getMessage());
	}
}?>