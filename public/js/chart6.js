var labels6 = [];
var data6 = [];

$.ajax({url:"http://127.0.0.1:8000/dashboard/charts/data6",
		success: function(response){
			
            var result = JSON.parse(response);
			result.forEach(function(obj){
                labels6.push(obj.label);
                data6.push(obj.data);
            });

            var ctxP = document.getElementById("Chart6").getContext('2d');
            var myPieChart = new Chart(ctxP, {
            type: 'pie',
            data: {
                labels: labels6,
                datasets: [{
                    data: data6,
                    backgroundColor: ["#C0392B", "#E74C3C","#9B59B6", "#8E44AD","#2980B9","#3498DB", "#1ABC9C","#16A085", "#F1C40F","#F39C12","#105051"],
                    hoverBackgroundColor: []
                    }]
                },
                options: {
                    responsive: true,
                    legend: {
                        position:'left',
                        labels: {
                            padding: 20,
                            boxWidth: 10
                        }
                    }
                }
            });
               
			
		},
		error: function e(error){
			alert("Error: "+ error);
		}
    });
