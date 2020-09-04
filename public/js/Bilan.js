var label = [];
var dataPrevuee = [];
var dataRealisee = [];
var colorred =[];
var colorblue=[];
$.ajax({url:"http://127.0.0.1:8000/dashboard/Bilan/QtesParSecteur",
		success: function(response){
            var result = JSON.parse(response);
            result.secteurs.forEach(function(obj){
                label.push(obj.data);
            })
			result.qtePrevue2.forEach(function(obj){
                dataPrevuee.push(parseFloat(obj.data));
                colorblue.push('rgba(65, 159, 240, 0.6)');
            });
            result.qteRealisee2.forEach(function(obj){
                dataRealisee.push(parseFloat(obj.data));
                colorred.push('rgba(237, 33, 58, 0.6)');
            });
            var ctxp = document.getElementById('ChartQteParSecteur').getContext('2d');
            var myBarChart = new Chart(ctxp, {
                type: 'bar',
                data: {
                    labels: label,
                    datasets: [{
                        label : 'Quantité Prévue',
                        data: dataPrevuee,
                        backgroundColor:colorblue
                    },
                        {
                        label: "Quantité réalisée",
                        data: dataRealisee,
                        backgroundColor: colorred
                    }
                ]
                },
                options: {
                    legend:{
                        display : true
                            },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            });
            
		},
		error: function e(error){
			alert("Error: "+ error );
		}
    });