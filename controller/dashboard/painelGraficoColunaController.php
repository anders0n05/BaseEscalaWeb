<?php
$queryCampos = $connect->prepare("SELECT A.NOME
				    FROM BI_PAINELCAMPOSQL A
				   WHERE A.PAINELCOMPONENTE = '" . $handleComponente . "'
				   ORDER BY A.ORDEM ASC");
$queryCampos->execute();

while ($rowCampos = $queryCampos->fetch(PDO::FETCH_ASSOC)) {
    $arrayCampo[] = $rowCampos['NOME'];
}

$campoLabel = $arrayCampo[0];
$campoPeriodo = $arrayCampo[1];
if(isset($arrayCampo[2])){
$campoValor = $arrayCampo[2];
}
else{
$campoValor = $campoPeriodo;
}

$queryComponente = $connect->prepare($sqlComponente);
$queryComponente->execute();

$arrayJson = array();
$arrayRow = array();

while ($rowSqlComponente = $queryComponente->fetch(PDO::FETCH_ASSOC)) {
    
    $yRow = str_replace(" ", "_", $rowSqlComponente[$campoLabel]);
    
    if (!isset($y2) && isset($y1)) {
        $y2 = $yRow;
        $label1 = $rowSqlComponente[$campoLabel];
    }
    
    if (!isset($y1)) {
        $y1 = $yRow;
        $label2 = $rowSqlComponente[$campoLabel];
    }
	
    $inserir = true;
    
    for ($y = 0; $y < count($arrayJson); $y++) {
        if ($arrayJson[$y][$campoPeriodo] == $rowSqlComponente[$campoPeriodo]) {
            $arrayJson[$y][$yRow] = $rowSqlComponente[$campoValor];
            $inserir = false;
        }
    }
    
    if ($inserir) {
        $arrayRow = array();
        $arrayRow[$campoPeriodo] = $rowSqlComponente[$campoPeriodo];        
        $arrayRow[$yRow] = $rowSqlComponente[$campoValor];

        $arrayJson[] = $arrayRow;
    }
}

?>

<script>
    $(function () {

        var dataSeries = [
            {valueField: "<?php echo $y1; ?>", name: "<?php echo $label1; ?>"},
            {valueField: "<?php echo $y2; ?>", name: "<?php echo $label2; ?>"}
        ];


        $("div#grafico<?php echo $handleComponente; ?>").dxChart({
            dataSource: <?php echo json_encode($arrayJson, JSON_UNESCAPED_UNICODE); ?>,
            commonSeriesSettings: {
                argumentField: "<?php echo $campoPeriodo; ?>",
                type: "bar",
                hoverMode: "allArgumentPoints",
                selectionMode: "allArgumentPoints",
                label: {
                    visible: false,
                    format: {
                        type: "fixedPoint",
                        precision: 0
                    }
                }
            },
            series: dataSeries,
            legend: {
                verticalAlignment: "bottom",
                horizontalAlignment: "center",
				border: { visible: true }
            },
            onPointClick: function (e) {
                e.target.select();
            },
			
			argumentAxis: {
            tickInterval: 5,
            label: {
                overlappingBehavior: { mode: 'rotate', rotationAngle: 45 }
            }
			},
			commonAxisSettings: {
				grid: { visible: true }
			},
			tooltip: {
            enabled: true,
            location: "edge",
            customizeTooltip: function (arg) {
                return {
                    text: arg.seriesName + " Total: " + arg.valueText
                };
            }
        	}
        });
	

});
</script>
<?php
unset($queryCampos);
unset($rowCampos);
unset($campoLabel);
unset($campoValor);
unset($queryComponente);
unset($arrayJson);
unset($arrayRow);
unset($rowSqlComponente);
unset($inserir);
unset($yRow);
unset($y2);
unset($y1);
unset($label1);
unset($label2);
unset($arrayCampo);
?>