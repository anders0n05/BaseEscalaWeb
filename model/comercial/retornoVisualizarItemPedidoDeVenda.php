<?php
$sequencial = NULL;
$tabela = NULL;
$tabelaHandle = NULL;
$lista = NULL;
$listaHandle = NULL;
$quantidade = NULL;
$ValorUnitario = NULL;
$ValorTotal = NULL;
$TotalGeral = NULL;
$produto = NULL;
$produtoHandle = NULL;
$unidadeMedida = NULL;
$unidadeMedidaHandle = NULL;
$numeroPedidoDeVenda = NULL;
$almoxarifado = NULL;
$almoxarifadoHandle = NULL;
$complemento = NULL;
$observacao = NULL;
$informacaoTecnica = NULL;
$aplicacao = NULL;
$aplicacaoHandle = NULL;
$disabled = NULL;


$queryItem = $connect->prepare("SELECT A.HANDLE HANDLE, 
								A.QUANTIDADE QUANTIDADE, 
								A.VALORUNITARIO VALORUNITARIO, 
								A.VALORTOTAL VALORTOTAL, 
								A.TOTALGERAL TOTALGERAL, 
								B0.NOME PRODUTO, 
								B0.HANDLE PRODUTOHANDLE,
								B1.SIGLA UNIDADEMEDIDA,
								B1.HANDLE UNIDADEMEDIDAHANDLE,
								B2.NUMERO PEDIDO,
								B3.NOME ALMOXARIFADO,
								B3.HANDLE ALMOXARIFADOHANDLE,
								A.DESCRICAOCOMPLEMENTARITEM COMPLEMENTO,
								A.OBSERVACAO OBSERVACAO,
								A.STATUS,
								B0.OBSERVACAO INFORMACAOTECNICA,
								B4.HANDLE APLICACAOHANDLE,
								B4.NOME APLICACAO
								FROM VE_ORDEMITEM A
								LEFT JOIN MT_ITEM B0 ON A.ITEM = B0.HANDLE
								LEFT JOIN MT_UNIDADEMEDIDA B1 ON A.UNIDADEMEDIDA = B1.HANDLE
								LEFT JOIN VE_ORDEM B2 ON A.ORDEM = B2.HANDLE
								LEFT JOIN MT_ALMOXARIFADO B3 ON A.ALMOXARIFADO = B3.HANDLE
								LEFT JOIN TR_APLICACAOITEM B4 ON A.APLICACAO = B4.HANDLE
								WHERE A.ORDEM = '".$handlePedidoDeVenda."'
								AND A.HANDLE = '".$handleItemPedidoDeVenda."'");			   
$queryItem->execute();
$rowItem = $queryItem->fetch(PDO::FETCH_ASSOC);
$quantidade = number_format($rowItem['QUANTIDADE'], '4', ',', '.');
$ValorUnitario = number_format($rowItem['VALORUNITARIO'], '10', ',', '.');
$ValorTotal = number_format($rowItem['VALORTOTAL'], '2', ',', '.');
$TotalGeral = number_format($rowItem['TOTALGERAL'], '2', ',', '.');
$produto = $rowItem['PRODUTO'];
$produtoHandle = $rowItem['PRODUTOHANDLE'];
$unidadeMedida = $rowItem['UNIDADEMEDIDA'];
$unidadeMedidaHandle = $rowItem['UNIDADEMEDIDAHANDLE'];
$numeroPedidoDeVenda = $rowItem['PEDIDO'];
$almoxarifado = $rowItem['ALMOXARIFADO'];
$almoxarifadoHandle = $rowItem['ALMOXARIFADOHANDLE'];
$complemento = $rowItem['COMPLEMENTO'];
$observacao = $rowItem['OBSERVACAO'];
$statusItem = $rowItem['STATUS'];
$informacaoTecnica = $rowItem['INFORMACAOTECNICA'];
$aplicacaoHandle = $rowItem['APLICACAOHANDLE'];
$aplicacao = $rowItem['APLICACAO'];

$querySeq = $connect->prepare("SELECT SEQUENCIAL FROM VE_ORDEMITEM WHERE ORDEM = '".$handlePedidoDeVenda."'");			   
$querySeq->execute();
$rowSeq = $querySeq->fetch(PDO::FETCH_ASSOC);
$sequencial = $rowSeq['SEQUENCIAL'];


$queryTabelaLista = $connect->prepare("SELECT B0.NOME TABELA,
										 B0.HANDLE TABELAHANDLE,
										 B1.NOME LISTA,
										 B1.HANDLE LISTAHANDLE
										 FROM VE_ORDEM A
										 LEFT JOIN CM_TABELA B0 ON A.TABELA = B0.HANDLE
										 LEFT JOIN CM_LISTA B1 ON A.TABELALISTA = B1.HANDLE
										 WHERE A.EMPRESA =  '".$empresa."'
										 AND A.HANDLE =  '".$handlePedidoDeVenda."'
										 AND A.VENDEDOR = '".$handleUsuario."'");			   
$queryTabelaLista->execute();
$rowTabelaLista = $queryTabelaLista->fetch(PDO::FETCH_ASSOC);
$tabela = $rowTabelaLista['TABELA'];
$tabelaHandle = $rowTabelaLista['TABELAHANDLE'];
$lista = $rowTabelaLista['LISTA'];
$listaHandle = $rowTabelaLista['LISTAHANDLE'];


	
if(isset($_SESSION['mensagem']) and isset($_SESSION['retornoItemPedido'])){
	$mensagem = $_SESSION['mensagem'];
	
	echo "<script type='text/javascript'>
			$(window).load(function(){
			$('#MensagemModal').modal('show');
			});
		</script>";

	echo '<div class="modal fade" id="MensagemModal" role="dialog" aria-spanledby="MensagemModalspan">
<div class="modal-dialog" role="document">
  <div class="modal-content">
<form method="post" action="#">
	  <div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-span="Close"><span aria-hidden="true">&times;</span></button>
	<h4 class="modal-title" id="MensagemModal">Erro ao inserir despesa</h4>
  </div>
	  <div class="modal-body"> '.$mensagem.'
	<div class="clearfix"></div>
  </div>
	  <div class="modal-footer">
	<button type="button" class="botaoBrancoLg"  data-dismiss="modal">Ok</button>
  </div>
	</form>
</div>
</div>
</div>';

	
$sequencial = $_SESSION['sequencial'];
$tabela = $_SESSION['tabela'];
$tabelaHandle = $_SESSION['tabelaHandle'];
$lista = $_SESSION['lista'];
$listaHandle = $_SESSION['listaHandle'];
$quantidade = $_SESSION['quantidade'];
$ValorUnitario = $_SESSION['ValorUnitario'];
$ValorTotal = $_SESSION['ValorTotal'];
$TotalGeral = $_SESSION['TotalGeral'];
$produto = $_SESSION['produto'];
$produtoHandle = $_SESSION['produtoHandle'];
$unidadeMedida = $_SESSION['unidadeMedida'];
$unidadeMedidaHandle = $_SESSION['unidadeMedidaHandle'];
$numeroPedidoDeVenda = $_SESSION['numeroPedidoDeVenda'];
$almoxarifado = $_SESSION['almoxarifado'];
$almoxarifadoHandle = $_SESSION['almoxarifadoHandle'];
$complemento = $_SESSION['complemento'];
$observacao = $_SESSION['observacao'];
$informacaoTecnica = $_SESSION['informacaoTecnica'];
$aplicacao = $_SESSION['aplicacao'];
$aplicacaoHandle = $_SESSION['aplicacaoHandle'];

unset($_SESSION['sequencial']);
unset($_SESSION['aplicacaoHandle']);
unset($_SESSION['aplicacao']);
unset($_SESSION['tabela']);
unset($_SESSION['tabelaHandle']);
unset($_SESSION['lista']);
unset($_SESSION['listaHandle']);
unset($_SESSION['quantidade']);
unset($_SESSION['ValorUnitario']);
unset($_SESSION['ValorTotal']);
unset($_SESSION['TotalGeral']);
unset($_SESSION['produto']);
unset($_SESSION['produtoHandle']);
unset($_SESSION['unidadeMedida']);
unset($_SESSION['unidadeMedidaHandle']);
unset($_SESSION['numeroPedidoDeVenda']);
unset($_SESSION['almoxarifado']);
unset($_SESSION['almoxarifadoHandle']);
unset($_SESSION['complemento']);
unset($_SESSION['observacao']);
unset($_SESSION['informacaoTecnica']);
unset($_SESSION['mensagem']);
	
}
else if(isset($_SESSION['mensagem']) and !isset($_SESSION['retornoItemPedido'])){
	$mensagem = $_SESSION['mensagem'];
	
	echo "<script type='text/javascript'>
			$(window).load(function(){
			$('#MensagemModal').modal('show');
			});
		</script>";

	echo '<div class="modal fade" id="MensagemModal" role="dialog" aria-spanledby="MensagemModalspan">
<div class="modal-dialog" role="document">
  <div class="modal-content">
<form method="post" action="#">
	  <div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-span="Close"><span aria-hidden="true">&times;</span></button>
	<h4 class="modal-title" id="MensagemModal">Erro ao inserir despesa</h4>
  </div>
	  <div class="modal-body"> '.$mensagem.'
	<div class="clearfix"></div>
  </div>
	  <div class="modal-footer">
	<button type="button" class="botaoBrancoLg"  data-dismiss="modal">Ok</button>
  </div>
	</form>
</div>
</div>
</div>';

unset($_SESSION['retornoItemPedido']);
unset($_SESSION['mensagem']);	
}
	
else if(isset($_SESSION['protocolo'])){
	$protocolo = $_SESSION['protocolo'];	
	unset($_SESSION['protocolo']);
}

if(isset($_POST['check'])){
	
	$check =  $_POST['check'];
	
foreach($check as $chk){
	$checkValue = $chk;
}
}

if($statusItem == '1' || $statusItem == '2'){
	$disabled = '';	
}
else{
	$disabled = 'disabled';	
}
	
if(isset($_SESSION['mensagem'])){
		$display = '';
}
else{
	$display = 'display';	
}

if(isset($_SESSION['arquivo']) and $_SESSION['arquivo'] > null){
					$nomeAnexoExplode = explode('.', $_SESSION['nomeAnexo']);
					$nomeAnexo = $nomeAnexoExplode[0];
					$extAnexo = $nomeAnexoExplode[1];
										
?>
            <script type='text/javascript'>
    				$(window).load(function(){
        			$('#AnexoModal').modal('show');
    				});
					
			$.blockUI({ css: { 
            border: 'none', 
            padding: '10px',
            backgroundColor: 'transparent', 
            '-webkit-border-radius': '0px', 
            '-moz-border-radius': '0px',
            opacity: 1,
            color: '#fff'
			} 
			});
 
          setTimeout($.unblockUI, 4000);
			</script>
            
            
			<div class="modal fade" id="AnexoModal" role="dialog" style="z-index:3040;" aria-spanledby="AnexoModalspan">
    			<div class="modal-dialog" role="document">
          			<div class="modal-content">
                    		<div class="modal-header">
            					<button type="button" class="close" data-dismiss="modal" aria-span="Close"><span aria-hidden="true">&times;</span></button>
            						<h4 class="modal-title" id="AnexoModal"><?php echo $nomeAnexo; ?></h4>
          					</div>
              				<div class="modal-body"> 
								<img src="../../controller/estrutura/VisualizarAnexo.php" width="100%" class="image-responsive" alt="<?php echo $nomeAnexo; ?>" title="<?php echo $nomeAnexo; ?>" />
            					<div class="clearfix"></div>
          					</div>
      				</div>
        		</div>
  			</div>
            
<?php
				}
?>