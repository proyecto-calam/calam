var obj_json = null;
$( document ).ready(function() {
	//$("#mform1").attr("action","bcd_01_1.php");
	$.ajax({
	    type: 'POST',
	    url: 'bcd_01_1.php',
	    data: $("#mform1").serialize(),
	    complete: function(data) {
		console.log(data);
		obj_json = jQuery.parseJSON(data.responseText);
		console.log(obj_json);		
		dibujaChart_1(obj_json);
	    }
	});

	function dibujaChart_1(obj_json) 
	{
		var dps =[];

		dps[1]= {
				type:"spline", 
				markerSize: 5, 
				showInLegend: true, 
				legendText: "Tiempos promedio", 
				color: "rgba(213,159,15,.99)", 
				dataPoints:new Array()
			}; 
		dps[0]= {
				toolTipContent: "{x}<br/>{y} minutos",			
				type:"splineArea",
				markerSize: 5, 
				showInLegend: true, 
				legendText: "Tiempos usuario ", 
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
				text: "Datos de acceso de " + obj_json.user.firstname + " " + obj_json.user.lastname,
				fontSize: 40,
				},  
		axisX: {
			lineThickness:2,
			title: "fecha",
			labelFontSize: 15,
			labelFontColor: "black",
			intervalType: "week"

		      }, 		
		axisY:{
			title: "Minutos",
			labelFontSize: 15,
			labelFontColor: "black",
			suffix: " min"
		}, 
			data: dps
		});

		chart2.render();

	}
});