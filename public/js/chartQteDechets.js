var dataRealise = [0,0,0,0,0,0,0,0,0,0,0,0];
var dataPrevue = [0,0,0,0,0,0,0,0,0,0,0,0];

$.ajax({url:"http://127.0.0.1:8000/dashboard/charts/qteDechets",
		success: function(response){
            var result = JSON.parse(response);

			result.qtePrevue.forEach(function(obj){
                dataPrevue[parseInt(obj.label)-1] = obj.data;
            });
            result.qteRealisee.forEach(function(obj){
                dataRealise[parseInt(obj.label)-1] = obj.data;
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
                        'rgba(70, 169, 255, 0)',
                        ],
                        borderColor: [
                        'rgba(65, 159, 240, .7)',
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