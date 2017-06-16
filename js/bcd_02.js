
$(document).on("ready", function(){
	var obj_json = null;
	var labels = [];

	getLabels();
	
	$("#id_submitbutton").hide();


	if(!$("#id_groups")[0] )
		generateGraph();		

	/*Función que se invoca al cambiar el combo con id "groups" de valor*/
	$("#id_groups").on("change", function(){	
		$("#error").hide('slow');
		$("#error").html('');
		var idGrupo = $("#id_groups option:selected").val();
		if(idGrupo != -1)
			generateGraph();					
		else{
			$("#error").append("<label for='error_group'>"+labels['bcd_02_error_group']+"</label>");
			$("#error").show("slow");
			$("#grafica").hide("slow");
		}
	});


	function getLabels(){		
		//errors
		labels['bcd_02_error_group'] = $("[name='bcd_02_error_group']").val();

		//graphic
		labels['bcd_02_graph_qualification'] = $("[name='bcd_02_graph_qualification']").val();
		labels['bcd_02_graph_no_data'] = $("[name='bcd_02_graph_no_data']").val();
		labels['bcd_02_graph_title'] = $("[name='bcd_02_graph_title']").val();
		labels['bcd_02_graph_activities'] = $("[name='bcd_02_graph_activities']").val();
		labels['bcd_02_graph_students'] = $("[name='bcd_02_graph_students']").val();
		labels['bcd_02_graph_unde_tasks'] = $("[name='bcd_02_graph_unde_tasks']").val();
		labels['bcd_02_graph_task_with_qual'] = $("[name='bcd_02_graph_task_with_qual']").val();
		labels['bcd_02_graph_less_than_60'] = $("[name='bcd_02_graph_less_than_60']").val();
		labels['bcd_02_graph_activ_betw_67'] = $("[name='bcd_02_graph_activ_betw_67']").val();
		labels['bcd_02_graph_activ_betw_81'] = $("[name='bcd_02_graph_activ_betw_81']").val();
	}
	function generateGraph(){
		var url = "bcd_02_1.php";			
		$.ajax({
			type: "POST",
			url: url,
			data: $("#mform1").serialize(),
			success: function(data){
				obj_json = jQuery.parseJSON(data);
				console.log(obj_json);
				dibujaChart(obj_json);
			}
		});
		return false;
	}


	function getValue(y,x,maxValue){
		if (maxValue == 10)
			return parseInt(obj_json.datos[y][x]);
		else
			return (parseInt(obj_json.datos[y][x]) * 10)/maxValue;

	}
	function dibujaChart(obj_json) 
	{
		var string = "<span style='\"'color: {color};'\"'><strong>{name}</strong></span><br/><strong>" + 
			labels['bcd_02_graph_qualification'] + "</strong> {valor}/{escala}<br/>";
		var string2 = "<span style='\"'color: {color};'\"'><strong>{name}</strong></span><br/><strong>" + 
			labels['bcd_02_graph_no_data'] +"<br/>";
		var dataNew = getDataPoints(string,string2);
		var contador = 1;
		var multiplicador = 1;

		for (var yy in obj_json.datos) {
			if(yy > 0){
				c_y = parseInt(yy)* multiplicador;
				for(var xx in obj_json.datos[yy]){
					if($.isNumeric(xx)){
						c_x = parseInt(obj_json.datos[0][xx].orden);
						gradeMax = parseInt(obj_json.datos[0][xx].grademax);
						value = getValue(yy,xx,gradeMax);
						valor = parseInt(obj_json.datos[yy][xx]);
						console.log(obj_json.datos[yy].firstname + " " + obj_json.datos[yy].lastname );
						console.log(value);
						console.log(valor);

						switch(true){
							case (value >=0 && value < 6):
								dataNew[2].dataPoints.push({ x: c_x, y: c_y, valor: valor, escala:gradeMax});
							break;
							case (value >=6 && value < 8):
								dataNew[3].dataPoints.push({ x: c_x, y: c_y, valor: valor, escala:gradeMax });
							break;
						
							case (value >=8 && value <= 10):
								dataNew[4].dataPoints.push({ x: c_x, y: c_y, valor: valor, escala:gradeMax });
							break;
							case (value < 0):
								dataNew[1].dataPoints.push({ x: c_x, y: c_y, valor: valor, escala:gradeMax });
							break;

							default:
								dataNew[0].dataPoints.push({ x: c_x, y: c_y, valor: valor, escala:gradeMax });
							break;

						}
					}
				}
				contador++;
			}
		}
	

	var chart = new CanvasJS.Chart("grafica",
	{
		title:{
			text: labels['bcd_02_graph_title'] + obj_json.info.coursename,
			fontSize: 20
		},
                        animationEnabled: true,
		axisX: {
			title: labels['bcd_02_graph_activities'],
			titleFontSize: 13,
			labelAngle: -20,
			interval: 1,
			minimum:0,
			labelFontSize: 10,				
			labelFormatter: function (e) {
				console.log('e');console.log( e);
				if(e.value == 0)
					return 0;
				for(var idx in obj_json.datos[0]){
					if($.isNumeric(idx)){
						if(e.value == obj_json.datos[0][idx].orden)
							return obj_json.datos[0][idx].itemname;
					}
				}						
			}				
		},
		axisY:{
			title: labels['bcd_02_graph_students'],
			titleFontSize: 16,
			interval: multiplicador,
			labelFontSize: 10,
			labelFormatter: function (e) {
				if(e.value == 0)
					return "";
				for(var idx in obj_json.datos){
					if(idx > 0){
						if(e.value == idx * multiplicador)
							return obj_json.datos[idx].firstname + ", " + obj_json.datos[idx].lastname;
					}
				}
				return "";
			}				
		},
		legend: {
			verticalAlign: 'bottom',
			horizontalAlign: "center"
		},
		data: dataNew,
      	legend:{
            cursor:"pointer",
			fontSize: 12,
            itemclick : function(e) {
              	if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                	e.dataSeries.visible = false;              
              	}
              	else {
                	e.dataSeries.visible = true;              
              	}
              	chart.render();
            }
      	}
	});

	$('#grafica').show();
		chart.render();
	}

	function getDataPoints(label, label2){
		var dataPoints = [
			{   //0     
				type: "scatter",  
				markerType: "cross", 
	      		toolTipContent: label2,
				name: labels['bcd_02_graph_unde_tasks'],
				showInLegend: true,  
				dataPoints: []
			},
			{      //1  
				type: "scatter",  
				markerType: "cross", 
	      			toolTipContent: label2,
				name: labels['bcd_02_graph_task_with_qual'],
				showInLegend: true,  
				dataPoints: []
			},

			{        //2
				type: "scatter",  
				markerType: "cross", 
	      		toolTipContent: label,
				name: labels['bcd_02_graph_less_than_60'],
				showInLegend: true,  
				dataPoints: []
			},
			{        //3
				type: "scatter",  
				markerType: "triangle", 
	      		toolTipContent: label,
				name: labels['bcd_02_graph_activ_betw_67'],
				showInLegend: true,  
				dataPoints: []
			},
			{        //4
				type: "scatter",  
				markerType: "circle", 
	      		toolTipContent: label,
				name: labels['bcd_02_graph_activ_betw_81'],
				showInLegend: true,  
				dataPoints: []
			}
		];
		return dataPoints;
	}
});
