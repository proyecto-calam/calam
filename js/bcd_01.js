var obj_json = null;
$( document ).ready(function() {
    var labels = [];
    getLabels();

    function getLabels(){
        labels['bcd_01_graph_json_accesdata'] = $("[name='bcd_01_graph_json_accesdata']").val();
        labels['bcd_01_graph_json_averagetime'] = $("[name='bcd_01_graph_json_averagetime']").val();
        labels['bcd_01_graph_json_minutes'] = $("[name='bcd_01_graph_json_minutes']").val();
        labels['bcd_01_graph_json_usertime'] = $("[name='bcd_01_graph_json_usertime']").val();
        labels['bcd_01_graph_json_date'] = $("[name='bcd_01_graph_json_date']").val();
        labels['bcd_01_graph_json_minutesabr'] = $("[name='bcd_01_graph_json_minutesabr']").val();
    }

    makeChart();

    function makeChart(){
        $.ajax({
            type: 'POST',
            url: 'bcd_01_1.php',
            data: $("#mform1").serialize(),
            complete: function(data) {
                console.log(data);
                obj_json = jQuery.parseJSON(data.responseText);
                console.log(obj_json);		
                drawChart_1(obj_json);
            }
        });
    }	

    function drawChart_1(obj_json) 
    {
        var dps =[];

        dps[1]= {
            type:"spline", 
            markerSize: 5, 
            showInLegend: true, 
            legendText: labels['bcd_01_graph_json_averagetime'],
            color: "rgba(213,159,15,.99)", 
            dataPoints:new Array()
        }; 
        dps[0]= {
            toolTipContent: "{x}<br/>{y} " + labels['bcd_01_graph_json_minutes'],
            type:"splineArea",
            markerSize: 5, 
            showInLegend: true, 
            legendText: labels['bcd_01_graph_json_usertime'],
            color: "rgba(0,61,121,.99)",  
            dataPoints:new Array()
        }; 

        for (var elemento in obj_json.header) {
            if (!isNaN(elemento)){
                var fElemento = new Date(elemento*1000);

                var nuser = parseInt(obj_json.user[elemento])/60;
                var nprom = parseInt(obj_json.prom[elemento])/60;

                dps[1].dataPoints.push({x: fElemento, y:nprom });
                dps[0].dataPoints.push({x: fElemento, y:nuser });
            }
        }

        var chart2 = new CanvasJS.Chart("result",
        {
            title:{
                text: labels['bcd_01_graph_json_accesdata'] + obj_json.user.firstname + " " + obj_json.user.lastname,
                fontSize: 40,
            },  
            axisX: {
                lineThickness:2,
                title: labels['bcd_01_graph_json_date'],
                labelFontSize: 15,
                labelFontColor: "black",
                intervalType: "week"
            }, 		
            axisY:{
                title: labels['bcd_01_graph_json_minutes'],
                labelFontSize: 15,
                labelFontColor: "black",
                suffix: labels['bcd_01_graph_json_minutesabr']
            }, 
                data: dps
        });
        chart2.render();
    }

    $("#id_submitbutton").on("click", function(){
        $("input[name=manual]").val("1");
        makeChart();		
    });
});