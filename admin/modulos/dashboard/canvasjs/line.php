
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
            ,axisY2: labelDefault
            ,data: [{
                type: "line"
                ,lineThickness: 2
                ,showInLegend: false
                // ,toolTipContent: "{ legendText } - { label }% - ({ y })"
                ,indexLabelFontFamily: labelDefault.labelFontFamily
                ,indexLabelFontSize: labelDefault.labelFontSize - 2
                ,indexLabelFontColor: labelDefault.labelFontColor
                ,indexLabel: "{ legendText } - ({ y })"
                ,dataPoints: <?php echo json_encode($data) ?>
            }]
            ,axisY:{
                // valueFormatString:  "#,##0.##", // move comma to change formatting
                labelFormatter: function ( e ) {
                    // return "" + e.value.format(2, 3, '.', ',');
                    return "" + e.value;
                }
                ,labelFontSize: labelDefault.labelFontSize - 2
                ,labelFontFamily: labelDefault.labelFontFamily
                ,labelFontColor: labelDefault.labelFontColor
                ,gridColor: "#eee"
                // ,interlacedColor: "#fefefe"
            }
        });

        chart.render();
    });
</script>