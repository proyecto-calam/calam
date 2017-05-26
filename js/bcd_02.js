
$(document).on("ready", function(){
	var obj_json = null;
	var multiplicador = 0.5;

	/*Función que se invoca al cambiar el combo con id "groups" de valor*/
	$("#groups").on("change", function(){		
		var idGrupo = $("#groups option:selected").val();
		var url = "bcd_02_1.php";
		
		$.ajax({
				type: "POST",
				url: url,
				data: $("#gropus_data").serialize(),
				success: function(data){
					//$("#grafica").html(data);
					obj_json = jQuery.parseJSON(data);
					console.log(obj_json);
					dibujaChart(obj_json);
				}
			});
			return false;
	});


function dibujaChart(obj_json) 
{

	var data_nuevo = [
		{        
			type: "scatter",  
			markerType: "cross", 
      toolTipContent: "<span style='\"'color: {color};'\"'><strong>{name}</strong></span><br/><strong>Calificación</strong> {valor}<br/>",
			name: "Tareas no calificadas",
			showInLegend: true,  
			dataPoints: []
		},
		{        
			type: "scatter",  
			markerType: "cross", 
      toolTipContent: "<span style='\"'color: {color};'\"'><strong>{name}</strong></span><br/><strong>Calificación</strong> {valor} <br/>",
			name: "Tareas sin entregar",
			showInLegend: true,  
			dataPoints: []
		},
		{        
			type: "scatter",  
			markerType: "triangle", 
      toolTipContent: "<span style='\"'color: {color};'\"'><strong>{name}</strong></span><br/><strong>Calificación</strong> {valor} <br/>",
			name: "Tareas 6 y 7.9",
			showInLegend: true,  
			dataPoints: []
		},
		{        
			type: "scatter",  
			markerType: "circle", 
      toolTipContent: "<span style='\"'color: {color};'\"'><strong>{name}</strong></span><br/><strong>Calificación</strong> {valor} <br/>",
			name: "Tareas 8 y más",
			showInLegend: true,  
			dataPoints: []
		}
	];
	var contador = 1;
	for (var yy in obj_json.datos) {
		if(yy > 0){
			c_y = parseInt(yy)* multiplicador;
			for(var xx in obj_json.datos[yy]){
				if($.isNumeric(xx)){
					c_x = parseInt(obj_json.datos[0][xx].orden);
					valor = parseInt(obj_json.datos[yy][xx]);
					switch(valor){
						case 0:
						case 1:
						case 2:
						case 3:
						case 4:
						case 5:
							data_nuevo[1].dataPoints.push({ x: c_x, y: c_y, valor: valor });
						break;
						case 6:
						case 7:
							data_nuevo[2].dataPoints.push({ x: c_x, y: c_y, valor: valor });
						break;
					
						case 8:
						case 9:
						case 10:
							data_nuevo[3].dataPoints.push({ x: c_x, y: c_y, valor: valor });
						break;
						default:
							data_nuevo[0].dataPoints.push({ x: c_x, y: c_y, valor: valor });
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
				text: "Índice de actividades entregadas en el curso: " + obj_json.info.coursename,
				fontSize: 20
			},
                        animationEnabled: true,
			axisX: {
				title:"Tareas",
				titleFontSize: 13,
				labelAngle: -30,
				labelFontSize: 13,				
				labelFormatter: function (e) {
						if(e.value == 0)
							return 0;
						for(var idx in obj_json.datos[0]){
							if($.isNumeric(idx)){
								if(e.value == obj_json.datos[0][idx].orden)
									return obj_json.datos[0][idx].orden;
							}
						}						
					 }				
			},
			axisY:{
				title: "Alumnos",
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


			data: data_nuevo,

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

chart.render();
}

});
