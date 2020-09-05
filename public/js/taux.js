var lab = ["Quantité dechets","points de collecte","Kilométrage","Carburant"];
var dataP = [];
var dataR = [];
var colorr =[];
var colorb=[];
$.ajax({url:"http://127.0.0.1:8000/dasboard/Bilan/TauxDuMois",
		success: function(response){
            var result = JSON.parse(response);
            result.datap.forEach(function(obj){
                dataP.push(parseFloat(obj));
                colorb.push('rgba(65, 159, 240, 0.6)');
            });
            for(i=0; i<result.datar.length; i++){
                dataR.push(parseFloat(result.datar[i]));
                colorr.push('rgba(237, 33, 58, 0.6)');
            }
            var ctxptt = document.getElementById('Charttauxmois').getContext('2d');
            var myBarChartt = new Chart(ctxptt, {
                type: 'bar',
                data: {
                    labels: lab,
                    datasets: [{
                        label : 'Prévue',
                        data: dataP,
                        backgroundColor:colorb
                    },
                        {
                        label: "réalisée",
                        data: dataR,
                        backgroundColor: colorr
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