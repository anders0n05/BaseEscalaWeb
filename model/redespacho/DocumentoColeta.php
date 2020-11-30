<?php
include_once('../../controller/tecnologia/Sistema.php');

date_default_timezone_set('America/Sao_Paulo');

$connect = Sistema::getConexao();
$pendente = Sistema::getGet('pendente');
$usuario = $_SESSION['handleUsuario'];
$pessoa = $_SESSION['pessoa'];
$start = $_GET["start"];
$length = $_GET["length"] + $start;
$documento = $_GET["documento"];
$manifesto = $_GET["manifesto"];

$order = $_GET["order"];
$columns = $_GET["columns"];
$col = 0;
$dir = "desc";

if (!empty($order)) {
    foreach ($order as $o) {
        $col = $o['column'];
        $dir = $o['dir'];
    }
}

$where = [];
$whereOr = [];

$where[] = "B1.TRANSPORTADOR IN (".Sistema::getPessoaUsuarioToStr($connect).") 
            AND A.EHCANCELADO <> 'S'
            AND B21.STATUS = 22            
            AND (EXISTS (SELECT X1.HANDLE 
                          FROM GD_DOCUMENTOPERCURSO X1
                         WHERE X1.DOCUMENTO = B1.DOCUMENTO 
                           AND X1.ORDEM = (SELECT MIN(X2.ORDEM) 
                                             FROM GD_DOCUMENTOPERCURSO X2 
                                            WHERE X2.DOCUMENTO = B1.DOCUMENTO
                                              AND X2.REDESPACHADOR IN (".Sistema::getPessoaUsuarioToStr($connect).") 
                                              AND X2.ORDEM > (SELECT Z.ORDEM 
                                                                FROM GD_DOCUMENTOPERCURSO Z
                                                               WHERE Z.HANDLE = B1.PERCURSOATUAL))) 
                 OR EXISTS (SELECT X1.HANDLE 
                              FROM GD_DOCUMENTOPERCURSO X1
                             WHERE X1.DOCUMENTO = B1.DOCUMENTO
                               AND X1.HANDLE = B1.PERCURSOATUAL
                               AND X1.REDESPACHADOR IN (".Sistema::getPessoaUsuarioToStr($connect).")                                           
                               AND NOT EXISTS (SELECT X2.ORDEM 
                                                 FROM GD_DOCUMENTOPERCURSO X2 
                                                WHERE X2.DOCUMENTO = B1.DOCUMENTO
                                                  AND X2.REDESPACHADOR IN (".Sistema::getPessoaUsuarioToStr($connect).") 
                                                  AND X2.ORDEM > (SELECT Z.ORDEM 
                                                                    FROM GD_DOCUMENTOPERCURSO Z
                                                                   WHERE Z.HANDLE = B1.PERCURSOATUAL)))) ";

if ($pendente == 'true') {
    $where[] = "A.STATUS NOT IN (5, 6, 9, 10)";
}

if ($documento) {
    if (intval($documento) !== 0) {
        $whereOr[] = "A.NUMERO = '" . intval($documento) . "'";
    }

}

if ($manifesto) {
    if (intval($manifesto) !== 0) {
        $whereOr[] = "B19.NUMEROMANIFESTO = '" . intval($manifesto) . "'";
    }

}

if (count($whereOr) > 0) {
    $where[] = '(' . join(' OR ', $whereOr) . ')';
}

$whereTexto = '';
if (count($where) > 0) {
    $whereTexto = "WHERE " . join(' AND ', $where);
}

//$order = "ORDER BY A." . $columns[$col]["data"] . " " . $dir;
$order = "ORDER BY A.HANDLE " . $dir;

$sqlOrdens = "WITH ORDENS AS
(
SELECT ROW_NUMBER() OVER ($order) ROW_NUMBER,
B16.RESOURCENAME,
B21.HANDLE,
B21.NUMERO NOTAFISCAL,
B22.HANDLE ROMANEIOITEM,
B2.NOME STATUSNOME,
A.NUMERO,
A.SERIE,
B19.NUMEROMANIFESTO,
B10.NOME MUNICIPIODESTINO,
A.VALORBRUTO
FROM GD_DOCUMENTO A  
LEFT JOIN GD_DOCUMENTOTRANSPORTE B1 ON B1.DOCUMENTO = A.HANDLE 
LEFT JOIN GD_STATUSDOCUMENTOTRANSPORTE B2 ON B1.STATUS = B2.HANDLE 
AND ((NOT EXISTS (SELECT HANDLE 
			FROM MS_OPERACAOPAPELUSUARIO 
			WHERE OPERACAO = A.OPERACAO)
 OR EXISTS (SELECT HANDLE 
			FROM MS_OPERACAOPAPELUSUARIO 
             
			WHERE OPERACAO = A.OPERACAO 
			AND PAPEL IN (SELECT PAPEL 
				FROM MS_USUARIOPAPEL 
                                                              
				WHERE USUARIO = $handleUsuario))))  
AND ((A.EHRH <> 'S') OR (EXISTS (SELECT Z0.HANDLE                                             
			FROM MS_USUARIOPAPEL Z0                                            
			INNER JOIN MS_PAPEL Z1 ON Z1.HANDLE = Z0.PAPEL                                            
			WHERE Z0.USUARIO = $handleUsuario                                              
			AND Z1.EHVISUALIZARRECURSOSHUMANOS <> 'S'))) 
LEFT JOIN TR_TIPODOCUMENTO B4 ON A.TIPODOCUMENTOFISCAL = B4.HANDLE 
LEFT JOIN MS_FILIAL B5 ON A.FILIAL = B5.HANDLE 
LEFT JOIN MS_FILIAL B6 ON B1.FILIALTRANSPORTE = B6.HANDLE 
LEFT JOIN MS_PESSOA B7 ON B1.TRANSPORTADOR = B7.HANDLE 
LEFT JOIN MS_MUNICIPIO B8 ON B1.MUNICIPIOORIGEM = B8.HANDLE 
LEFT JOIN MS_ESTADO B9 ON B8.ESTADO = B9.HANDLE 
LEFT JOIN MS_MUNICIPIO B10 ON B1.MUNICIPIODESTINO = B10.HANDLE 
LEFT JOIN MS_ESTADO B11 ON B10.ESTADO = B11.HANDLE 
LEFT JOIN MS_PESSOA B12 ON B1.REMETENTE = B12.HANDLE 
LEFT JOIN MS_PESSOA B13 ON B1.DESTINATARIO = B13.HANDLE 
LEFT JOIN MS_PESSOA B14 ON A.PESSOA = B14.HANDLE 
LEFT JOIN MT_NATUREZAMERCADORIA B15 ON B1.NATUREZAMERCADORIA = B15.HANDLE
INNER JOIN MD_IMAGEM B16 ON B2.IMAGEM = B16.HANDLE
LEFT JOIN MS_USUARIO B17 ON A.LOGUSUARIOALTERACAO = B17.HANDLE
LEFT JOIN GD_DOCUMENTOPERCURSO B18 ON B18.HANDLE = B1.PERCURSOATUAL
LEFT JOIN OP_MANIFESTO B19 ON B19.HANDLE = B18.MANIFESTO
LEFT JOIN GD_DOCUMENTOORIGINARIO B20 ON B20.DOCUMENTO = A.HANDLE
LEFT JOIN GD_ORIGINARIO B21 ON B21.HANDLE = B20.ORIGINARIO
LEFT JOIN OP_VIAGEMROMANEIOITEM B22 ON B22.ORIGINARIO = B21.HANDLE AND B22.DOCUMENTOTRANSPORTE = B1.HANDLE AND B22.STATUS <> 4      
$whereTexto
)   
SELECT * FROM ORDENS A WHERE row_number BETWEEN $start AND $length
";


$queryOrdens = $connect->prepare($sqlOrdens);
$queryOrdens->execute();

$ordens = [];

while ($dados = $queryOrdens->fetch(PDO::FETCH_ASSOC)) {
    $dados["HANDLE"] = $dados["HANDLE"];
    $dados["STATUS"] = Sistema::getImagem($dados['RESOURCENAME'], $dados['STATUSNOME']);
    $dados["NUMERO"] = $dados['NUMERO'];
    $dados["NOTAFISCAL"] = $dados['NOTAFISCAL'];
    $dados["ROMANEIOITEM"] = $dados['ROMANEIOITEM'];
    $dados["SERIE"] = $dados['SERIE'];    
    $dados["NUMEROMANIFESTO"] = $dados['NUMEROMANIFESTO'];    
    $dados["MUNICIPIO"] = $dados['MUNICIPIODESTINO'];        
    $dados["VALOR"] = Sistema::formataValor($dados["VALORBRUTO"]);

    $ordens[] = $dados;
}

$sqlOrdensFiltro = "SELECT COUNT(A.HANDLE) FILTRADO
FROM GD_DOCUMENTO A  
LEFT JOIN GD_DOCUMENTOTRANSPORTE B1 ON B1.DOCUMENTO = A.HANDLE 
LEFT JOIN GD_STATUSDOCUMENTOTRANSPORTE B2 ON B1.STATUS = B2.HANDLE 
AND ((NOT EXISTS (SELECT HANDLE 
			FROM MS_OPERACAOPAPELUSUARIO 
			WHERE OPERACAO = A.OPERACAO)
 OR EXISTS (SELECT HANDLE 
			FROM MS_OPERACAOPAPELUSUARIO 
             
			WHERE OPERACAO = A.OPERACAO 
			AND PAPEL IN (SELECT PAPEL 
				FROM MS_USUARIOPAPEL 
                                                              
				WHERE USUARIO = $handleUsuario))))  
AND ((A.EHRH <> 'S') OR (EXISTS (SELECT Z0.HANDLE                                             
			FROM MS_USUARIOPAPEL Z0                                            
			INNER JOIN MS_PAPEL Z1 ON Z1.HANDLE = Z0.PAPEL                                            
			WHERE Z0.USUARIO = $handleUsuario                                              
			AND Z1.EHVISUALIZARRECURSOSHUMANOS <> 'S'))) 
LEFT JOIN TR_TIPODOCUMENTO B4 ON A.TIPODOCUMENTOFISCAL = B4.HANDLE 
LEFT JOIN MS_FILIAL B5 ON A.FILIAL = B5.HANDLE 
LEFT JOIN MS_FILIAL B6 ON B1.FILIALTRANSPORTE = B6.HANDLE 
LEFT JOIN MS_PESSOA B7 ON B1.TRANSPORTADOR = B7.HANDLE 
LEFT JOIN MS_MUNICIPIO B8 ON B1.MUNICIPIOORIGEM = B8.HANDLE 
LEFT JOIN MS_ESTADO B9 ON B8.ESTADO = B9.HANDLE 
LEFT JOIN MS_MUNICIPIO B10 ON B1.MUNICIPIODESTINO = B10.HANDLE 
LEFT JOIN MS_ESTADO B11 ON B10.ESTADO = B11.HANDLE 
LEFT JOIN MS_PESSOA B12 ON B1.REMETENTE = B12.HANDLE 
LEFT JOIN MS_PESSOA B13 ON B1.DESTINATARIO = B13.HANDLE 
LEFT JOIN MS_PESSOA B14 ON A.PESSOA = B14.HANDLE 
LEFT JOIN MT_NATUREZAMERCADORIA B15 ON B1.NATUREZAMERCADORIA = B15.HANDLE
INNER JOIN MD_IMAGEM B16 ON B2.IMAGEM = B16.HANDLE
LEFT JOIN MS_USUARIO B17 ON A.LOGUSUARIOALTERACAO = B17.HANDLE
LEFT JOIN GD_DOCUMENTOPERCURSO B18 ON B18.HANDLE = B1.PERCURSOATUAL
LEFT JOIN OP_MANIFESTO B19 ON B19.HANDLE = B18.MANIFESTO
LEFT JOIN GD_DOCUMENTOORIGINARIO B20 ON B20.DOCUMENTO = A.HANDLE
LEFT JOIN GD_ORIGINARIO B21 ON B21.HANDLE = B20.ORIGINARIO
LEFT JOIN OP_VIAGEMROMANEIOITEM B22 ON B22.ORIGINARIO = B21.HANDLE AND B22.DOCUMENTOTRANSPORTE = B1.HANDLE AND B22.STATUS <> 4      
$whereTexto";


$queryOrdensFiltro = $connect->prepare($sqlOrdensFiltro);
$queryOrdensFiltro->execute();

$filtro = $queryOrdensFiltro->fetch(PDO::FETCH_ASSOC);

$sqlOrdensTotal = "SELECT COUNT(A.HANDLE) TOTAL
FROM GD_DOCUMENTO A  
LEFT JOIN GD_DOCUMENTOTRANSPORTE B1 ON B1.DOCUMENTO = A.HANDLE 
LEFT JOIN GD_STATUSDOCUMENTOTRANSPORTE B2 ON B1.STATUS = B2.HANDLE 
AND ((NOT EXISTS (SELECT HANDLE 
			FROM MS_OPERACAOPAPELUSUARIO 
			WHERE OPERACAO = A.OPERACAO)
 OR EXISTS (SELECT HANDLE 
			FROM MS_OPERACAOPAPELUSUARIO 
             
			WHERE OPERACAO = A.OPERACAO 
			AND PAPEL IN (SELECT PAPEL 
				FROM MS_USUARIOPAPEL 
                                                              
				WHERE USUARIO = $handleUsuario))))  
AND ((A.EHRH <> 'S') OR (EXISTS (SELECT Z0.HANDLE                                             
			FROM MS_USUARIOPAPEL Z0                                            
			INNER JOIN MS_PAPEL Z1 ON Z1.HANDLE = Z0.PAPEL                                            
			WHERE Z0.USUARIO = $handleUsuario                                              
			AND Z1.EHVISUALIZARRECURSOSHUMANOS <> 'S'))) 
LEFT JOIN TR_TIPODOCUMENTO B4 ON A.TIPODOCUMENTOFISCAL = B4.HANDLE 
LEFT JOIN MS_FILIAL B5 ON A.FILIAL = B5.HANDLE 
LEFT JOIN MS_FILIAL B6 ON B1.FILIALTRANSPORTE = B6.HANDLE 
LEFT JOIN MS_PESSOA B7 ON B1.TRANSPORTADOR = B7.HANDLE 
LEFT JOIN MS_MUNICIPIO B8 ON B1.MUNICIPIOORIGEM = B8.HANDLE 
LEFT JOIN MS_ESTADO B9 ON B8.ESTADO = B9.HANDLE 
LEFT JOIN MS_MUNICIPIO B10 ON B1.MUNICIPIODESTINO = B10.HANDLE 
LEFT JOIN MS_ESTADO B11 ON B10.ESTADO = B11.HANDLE 
LEFT JOIN MS_PESSOA B12 ON B1.REMETENTE = B12.HANDLE 
LEFT JOIN MS_PESSOA B13 ON B1.DESTINATARIO = B13.HANDLE 
LEFT JOIN MS_PESSOA B14 ON A.PESSOA = B14.HANDLE 
LEFT JOIN MT_NATUREZAMERCADORIA B15 ON B1.NATUREZAMERCADORIA = B15.HANDLE
INNER JOIN MD_IMAGEM B16 ON B2.IMAGEM = B16.HANDLE
LEFT JOIN MS_USUARIO B17 ON A.LOGUSUARIOALTERACAO = B17.HANDLE
LEFT JOIN GD_DOCUMENTOPERCURSO B18 ON B18.HANDLE = B1.PERCURSOATUAL
LEFT JOIN OP_MANIFESTO B19 ON B19.HANDLE = B18.MANIFESTO
LEFT JOIN GD_DOCUMENTOORIGINARIO B20 ON B20.DOCUMENTO = A.HANDLE
LEFT JOIN GD_ORIGINARIO B21 ON B21.HANDLE = B20.ORIGINARIO
LEFT JOIN OP_VIAGEMROMANEIOITEM B22 ON B22.ORIGINARIO = B21.HANDLE AND B22.DOCUMENTOTRANSPORTE = B1.HANDLE AND B22.STATUS <> 4      
$whereTexto";


$queryOrdensTotal = $connect->prepare($sqlOrdensTotal);
$queryOrdensTotal->execute();

$total = $queryOrdensTotal->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "draw" => $_GET['draw'],
    "recordsTotal" => $total["TOTAL"],
    "recordsFiltered" => $filtro["FILTRADO"],
    "data" => $ordens
]);