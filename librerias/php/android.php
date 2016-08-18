<?php 
require_once('./conexion.php');
$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

if(!empty($_POST['nombre'])&&!empty($_POST['img'])){
	$nombre=$_POST['nombre'];
	$img=$_POST['img'];
	$hoy=date('Y-m-d');

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
			$idProfesor=$filas['id'];

			$stmtFirma = $pdo->prepare("update firma set firma=:firma where idClaustro=:idClaustro and idProfesor=:idProfesor");
			$stmtFirma->bindParam(':firma', $img);
			$stmtFirma->bindParam(':idClaustro', $idClaustro);
			$stmtFirma->bindParam(':idProfesor', $idProfesor);
			if($stmtFirma->execute()){
				echo json_encode('ok, insertada firma');
			}else {
				echo json_encode('ko, problema al firmar '.mysql_error($pdo));
			}
		}else{
			echo json_encode('ko, problema al seleccionar profe '.mysql_error($pdo));
		}
	}else{
		echo json_encode('ko, problema al seleccionar claustro '.mysql_error($pdo));
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
			echo json_encode('No hay datos errores '.$arry.' codigo: '.$errorCode);
		}
	}
	catch(PDOException $e) {
		echo  json_encode('error: '.$e->getMessage());
	}
}