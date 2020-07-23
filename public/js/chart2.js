var data2 = [0,0,0,0,0,0,0,0,0,0,0,0];

$.ajax({url:"http://127.0.0.1:8000/dashboard/charts/data2",
		success: function(response){
			var result = JSON.parse(response);
			result.forEach(function(obj){
                
                data2[parseInt(obj.label)] = obj.data;
            });

            

            var ctxL = document.getElementById("Chart2").getContext('2d');
            var myLineChart = new Chart(ctxL, {
            type: 'line',
            data: {
                labels: ["Janvier", "Fevrier", "Mars", "Avril", "Mai", "Juin", "Juillet","Août","Septembre", "Octobre", "Novembre","Décembre"],
                datasets: [{
                    label: "Quantitées de DMA par mois",
                    data: data2,
                    backgroundColor: ['rgba(105, 0, 132, .2)'],
                    borderColor: ['rgba(200, 99, 132, .7)'],
                    borderWidth: 2
                    },]
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