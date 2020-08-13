var dataRealise = [0,0,0,0,0,0,0,0,0,0,0,0];
var dataPrevue = [0,0,0,0,0,0,0,0,0,0,0,0];

$.ajax({url:"http://127.0.0.1:8000/dashboard/charts/qteDechets",
		success: function(response){
            var result = JSON.parse(response);

			result.qtePrevue.forEach(function(obj){
                dataPrevue[parseInt(obj.label)] = obj.data;
            });
            result.qteRealisee.forEach(function(obj){
                dataRealise[parseInt(obj.label)] = obj.data;
            });

            console.log(dataPrevue);


            var ctxL = document.getElementById("ChartQteDechets").getContext('2d');
            var myLineChart = new Chart(ctxL, {
            type: 'line',
            data: {
                labels: ["Janvier", "Fevrier", "Mars", "Avril", "Mai", "Juin", "Juillet","Août","Septembre", "Octobre", "Novembre","Décembre"],
                datasets: [{
                    label: "Quantité réalisée",
                    data: dataRealise,
                    backgroundColor: [
                        'rgba(109, 213, 237, 0)',
                        ],
                        borderColor: [
                        'rgba(0, 10, 130, .7)',
                        ],
                    borderWidth: 2
                    },
                    {
                        label: "Quantité Prévue",
                        data: dataPrevue,
                        backgroundColor: [
                            'rgba(105, 0, 132, 0)',
                            ],
                            borderColor: [
                            'rgba(237, 33, 58, .7)',
                            ],
                        borderWidth: 2
                        },
                ]
                },
            options: {
                responsive: true
                }
            });
                        
			
		},
		error: function e(error){
			alert("Error: "+ error);
		}
    });