<?php
error_reporting(E_ALL);
set_time_limit(0);
ini_set('display_errors', 'On');
require_once("../ldap/class.ldap.php");
require_once("./conexion.php");
require_once 'fpdf181/fpdf.php';
define('UPLOAD_DIR', 'PDFs/');
use Dompdf\Dompdf;
use Dompdf\Options;

$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

if (!empty($_POST['datos'])){
	//print_r(json_decode($_POST['datos']));
	try{	
		$ldap = new ldap(Config::$ldapServidor);
		//print_r($ldap->getProfes());
		$result = $ldap->getProfes();

		//$result = $_POST['datos'];
		$todoBien=false;
		foreach ($result as  $key) {
			$patron="/[P|p]rofe\s-(\s[0-9]*)?/";
			$nombre=trim($key['apellidos']);
			$nombre=preg_replace($patron,"",$nombre);
			//select de cada uno, si esta no hacer nada, si no insertar.
			$stmt = $pdo->prepare("select * from profesor where nombre=:nombre and email=:email");
			$stmt->bindParam(':nombre', $nombre);
			$stmt->bindParam(':email', $key['email']);
			$stmt->execute();
			$profe=$stmt->fetch(PDO::FETCH_ASSOC);
			if($profe){
				//hacer update
				$stmtUP=$pdo->prepare("update profesor set nombre=:nombre, email=:email where id=:id");
				$stmtUP->bindParam(':nombre', $nombre);
				$stmtUP->bindParam(':email', $key['email']);
				$stmtUP->bindParam(':id',$profe['id']);
				if($stmtUP->execute()){
					$todoBien=true;
				}else {
					$todoBien=false;
				}
				//array_push($arrayIdProfes,array("id"=>$profe['id']));	
			}else {
				$stmtInsert = $pdo->prepare("insert into profesor(nombre,email) values(:nombre,:email)");
				$stmtInsert->bindParam(':nombre', $nombre);
				$stmtInsert->bindParam(':email', $key['email']);
				if($stmtInsert->execute()){
					$todoBien=true;
				}else {
					$todoBien=false;
				}
			}
		}
		if($todoBien==true){
			echo json_encode("ok");
		}else {
			echo json_encode("ko");
		}
	}
	catch(PDOException $e) {
		echo  json_encode("error: ".$e->getMessage());
	}
}
if(!empty($_POST['claustro'])){
	try{	
		$res = $_POST['claustro'];
		if(!empty($res['titulo'])&& !empty($res['dia'])&& !empty($res['horaInicio'])&& !empty($res['horaFin'])&& !empty($res['curso'])&& !empty($res['orden'])){
			//creamos el claustro
			$stmt = $pdo->prepare("insert into claustro(titulo,dia,horaInicio,horaFin,curso,orden,observacion,activo,borrado) values(:titulo,:dia,:horaInicio,:horaFin,:curso,:orden,:observacion,true,false)");
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
					}
				}
				$insertado=false;
				foreach ($arrayIdProfes as $key ) {
					// insertamos en firma
					$stmt = $pdo->prepare("insert into firma(idClaustro,idProfesor) values(:idClaustro,:idProfesor)");
					$stmt->bindParam(':idClaustro', $lastId);
					$stmt->bindParam(':idProfesor', $key["id"]);

					if($stmt->execute()){
						$insertado=true;
					}else{
						$insertado=false;
					}
				}
				if ($insertado==true) {
					echo json_encode("ok");
				}else{
					echo json_encode("ko");

				}
			}
		}else{
			echo json_encode("ko");
		}
	} 
	catch(PDOException $e) {
		echo  json_encode("error: ".$e->getMessage());
	}
}
if(!empty($_POST['historicos'])){
	try{
		$arrayDatos=[];
		$stmt = $pdo->prepare("select * from claustro where borrado=false order by id DESC;");
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
		echo  json_encode("error: ".$e->getMessage());
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
					//$asistentes=0;
					$img = $fila["firma"];
					
					$img = str_replace('data:image/png;base64,', '', $img);
					$img = str_replace(' ', '+', $img);
					$data = base64_decode($img);
					$src = 'data: image/png;base64,'.$img;


					array_push($dat,array("id"=>$fila["id"],"claustro"=>$fila["idClaustro"],"profesor"=>$fila["idProfesor"],"firma"=>$src));

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
		echo  json_encode("error: ".$e->getMessage());
	}
}
if(!empty($_POST['actualizarClaustro'])){
	$update = $_POST['actualizarClaustro'];
	try{
		$stmt=$pdo->prepare("update claustro set titulo=:titulo, dia=:dia, horaInicio=:horaInicio, horaFin=:horaFin, curso=:curso, orden=:orden, observacion=:observacion, activo=true, borrado=false where id=:id");
		$stmt->bindParam(':titulo', $update['titulo']);
		$stmt->bindParam(':dia', $update['dia']);
		$stmt->bindParam(':horaInicio', $update['horaInicio']);
		$stmt->bindParam(':horaFin', $update['horaFin']);
		$stmt->bindParam(':curso', $update['curso']);
		$stmt->bindParam(':orden', $update['orden']);
		$stmt->bindParam(':observacion', $update['observacion']);
		$stmt->bindParam(':id',$update['id']);

		if($stmt->execute()){
			echo json_encode('ok');
		}else {
			echo json_encode('ko'.mysql_error($pdo));
		}
	}
	catch(PDOException $e)
	{
		echo  json_encode("error: ".$e->getMessage());
	}
}
if(!empty($_POST['borrar'])){
	$borrarId = $_POST['borrar'];
	$borrarId = intval($borrarId);
	try{
		$stmt=$pdo->prepare("update claustro set activo=false, borrado=true where id=:id");
		$stmt->bindParam(":id",$borrarId);
		if($stmt->execute()){
			echo json_encode('ok');
		}else {
			echo json_encode('ko'.mysql_error($pdo));
		}
	}
	catch(PDOException $e)
	{
		echo  json_encode("error: ".$e->getMessage());
	}
}
if(!empty($_POST['desactivar'])){
	$hoy=date('Y-m-d');
	$diaClaustro;
	try{
		$stmt=$pdo->prepare("select * from claustro where activo=true");
		$stmt->execute();
		$filas=$stmt->fetch(PDO::FETCH_ASSOC);
		if($filas){
			$diaClaustro=$filas["dia"];
			$desactivar=$filas["id"];
			$fecha_actual = strtotime($hoy);
			$fecha_entrada = strtotime($diaClaustro);
			if($fecha_actual > $fecha_entrada){
				//echo json_encode("La fecha entrada ya ha pasado ".$fecha_actual." ".$fecha_entrada);
				$stmt=$pdo->prepare("update claustro set activo=false where id=:id");
				$stmt->bindParam(":id",$desactivar);
				if($stmt->execute()){
					echo json_encode('ok');
				}else {
					echo json_encode('ko'.mysql_error($pdo));
				}
			}else{
				echo json_encode("Aun falta algun tiempo");
			}
		}else {//no hay nada
			echo json_encode('ok');
		}
	}
	catch(PDOException $e)
	{
		echo  json_encode("error: ".$e->getMessage());
	}
}
if(!empty($_POST['fecha'])){
	$fechaProbar=$_POST['fecha'];
	$diaClaustro;
	try{
		$stmt=$pdo->prepare("select * from claustro where activo=true");
		$stmt->execute();
		$filas=$stmt->fetch(PDO::FETCH_ASSOC);
		if($filas){
			$diaClaustro=$filas["dia"];
			$idClaustro=$filas["id"];
			$fechaPrueba = strtotime($fechaProbar);
			$fechaClaustro = strtotime($diaClaustro);
			if($fechaPrueba === $fechaClaustro){
				//no dejamos crearlo
				echo json_encode('No se puede crear');
			}else {
				echo json_encode('ok');
			}
		}else {//no hay nada
			echo json_encode('ok');
		}
	}catch(PDOException $e)
	{
		echo  json_encode("error: ".$e->getMessage());
	}
}
if(!empty($_POST['rellenar'])){
	try{
		$arrayD=[];
		$stmt=$pdo->prepare("select * from profesor order by nombre");
		$stmt->execute();
		$filas=$stmt->fetchAll(PDO::FETCH_ASSOC);
		if($filas){
			foreach ($filas as $fila ) {
				array_push($arrayD,array("id"=>$fila["id"],"nombre"=>$fila["nombre"],"email"=>$fila["email"]));	
			}
			echo json_encode($arrayD);
		}
	}catch(PDOException $e)
	{
		echo  json_encode("error: ".$e->getMessage());
	}
}
if(!empty($_POST['pdf'])){
	$name=str_replace(" ","+",$_POST['nombre']);
	$nombre = $name;

	$html=$_POST['pdf'];
	
	/*if(file_exists(UPLOAD_DIR.$nombre.".pdf")){
		echo json_encode("http://regorodri.noip.me/proyecto/librerias/php/".UPLOAD_DIR.$nombre.".pdf");
	}else{ */
		// Creación del objeto de la clase heredada
		$pdf = new FPDF();
		$pdf->AddPage();
		$pdf->Image('../../src/logo.png',10,8,33);
		$pdf->SetFont('Arial','B',15);
		$pdf->Cell(80);
		$pdf->Cell(30,10,'IES San Clemente',0,1,'C');
		$pdf->Ln(10);
		$pdf->SetXY(80, 20);
		$pdf->cell(30,10,utf8_decode($html["title"]));
		$pdf->Ln(40);

		$pdf->SetFont('Times','B',12);
		//$pdf->Cell(0,10,utf8_decode('Título: '.$html["title"]),0,1);
		$pdf->Cell(80,10,utf8_decode('Fecha realización del Claustro: '),0,0);
		//$pdf->Write(5,utf8_decode('Fecha realización del Claustro: '));
		$pdf->SetFont('Times','',12);
		$pdf->Cell(80,10,$html["date"],0,1);
		//$pdf->write(5,$html["date"]);

		$pdf->SetFont('Times','B',12);
		$pdf->Cell(80,10,utf8_decode('Curso: '),0,0);
		//$pdf->Write(5,utf8_decode('Curso: '));
		$pdf->SetFont('Times','',12);
		$pdf->Cell(80,10,utf8_decode($html["curso"]),0,1);
		//$pdf->write(5,utf8_decode($html["curso"]));

		$pdf->SetFont('Times','B',12);
		$pdf->Cell(80,10,utf8_decode('Hora Inicio:  '),0,0);
		//$pdf->Write(5,utf8_decode('Hora Inicio:  '));
		$pdf->SetFont('Times','',12);
		$pdf->Cell(80,10,utf8_decode($html["hi"]),0,1);
		//$pdf->write(5,utf8_decode($html["hi"]));


		$pdf->SetFont('Times','B',12);
		$pdf->Cell(80,10,utf8_decode('Hora Fin:  '),0,0);
		$pdf->SetFont('Times','',12);
		$pdf->Cell(80,10,utf8_decode($html["hf"]),0,1);

		$pdf->SetFont('Times','B',12);
		$pdf->Cell(80,10,utf8_decode('Orden del día:  '),0,0);

		$pdf->SetFont('Times','',12);
		$pdf->Cell(80,10,utf8_decode($html["or"]),0,1);

		$pdf->SetFont('Times','B',12);
		$pdf->Cell(80,10,utf8_decode('Observaciones:  '),0,0);
		
		$pdf->SetFont('Times','',12);
		if(empty($html["ob"])){
			$pdf->Cell(80,10,utf8_decode("Sin Observaciones."),0,1);
		}else{
			$pdf->Cell(80,10,utf8_decode($html["ob"]),0,1);
		}
		$pdf->Ln(10);

		if(count($html["firmas"])>1){
			$pdf->SetFont('Times','B',12);
			$pdf->Cell(0,10,'Asistencias: ',0,1);
			$pdf->SetFont('Times','',12);

			foreach ($html["firmas"] as $key ) {

				//crear la imagen FIRMAS y luego borrarla
				//$pdf->Cell(0,10,utf8_decode($key[0]),0,1);
				if(empty($key[1])){
				//echo json_encode($key[1]."\n");
					$pdf->Cell(95,15,utf8_decode($key[0]),1,0,"C");
					$pdf->SetTextColor(255,0,0);
					$pdf->Cell(95,15,utf8_decode("Falta de asistencia"),1,1,"C");
					$pdf->SetTextColor(0,0,0);
				}else{
					$pdf->Cell(95,15,utf8_decode($key[0]),1,0,"C");
					$pdf->Cell(95,15, $pdf->Image($key[1], $pdf->GetX()+32, $pdf->GetY(), 15, 15,'png'), 1, 1,"C");

					//$pdf->write(15, $pdf->Image($key[1], $pdf->GetX()+32, $pdf->GetY(), 15, 15,'png'), 1, 1,"C");
					//$pdf->Ln(15);
				}

			}
		}
		$pdf->Output("F",UPLOAD_DIR.$nombre.".pdf");
		echo json_encode("http://regorodri.noip.me/proyecto/librerias/php/".UPLOAD_DIR.$nombre.".pdf");
	//}
	}
	?>