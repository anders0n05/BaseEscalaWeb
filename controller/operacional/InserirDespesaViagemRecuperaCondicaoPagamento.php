<?php

	include_once('../tecnologia/Sistema.php');

	$connect = Sistema::getConexao();

	$dados = $connect->prepare("SELECT A.HANDLE id, A.NOME value
								  FROM FN_CONDICAOPAGAMENTO A 
								 WHERE A.STATUS = 4
							  ORDER BY value ASC
							   ");
	$dados->execute();
	
	echo json_encode($dados->fetchAll(PDO::FETCH_ASSOC));
?>