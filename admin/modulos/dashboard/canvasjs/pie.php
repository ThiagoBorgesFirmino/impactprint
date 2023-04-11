<?php
// var_dump($rep_id);
// var_dump($titulo);
// var_dump($data);
?>
<div class="panel panel-primary">
    <div class="panel-body">
        <div id="chartContainer<?php echo $rep_id ?>" style="height:400px; width:100%;"></div>
    </div>
</div>

<script>
    $(document).ready(function(){
        var chart = new CanvasJS.Chart("chartContainer<?php echo $rep_id ?>", {
            animationEnabled: true
            ,title: {
                text: '<?php echo $titulo ?>'
                ,fontSize: 16
                ,fontFamily: labelDefault.labelFontFamily
                ,padding:10
            }
            ,legend: {
                verticalAlign: "center"
                ,horizontalAlign: "left"
                ,fontSize: 14
                ,fontFamily: labelDefault.labelFontFamily
                ,show: false
            }
            ,theme: "theme3"
            ,data: [{
                type: "pie"
                // ,lineThickness: 2
                ,showInLegend: false
                ,toolTipContent: "{ legendText } - { label }% - ({ y })"
                ,indexLabelFontFamily: labelDefault.labelFontFamily
                ,indexLabelFontSize: labelDefault.labelFontSize - 2
                ,indexLabelFontColor: labelDefault.labelFontColor
                ,indexLabel: "{ legendText } - { label }% - ({ y })"
                ,dataPoints: <?php echo json_encode($data) ?>
            }]
        });
        chart.render();
    });
</script>