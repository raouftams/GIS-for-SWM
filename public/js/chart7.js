$.ajax({url:"http://127.0.0.1:8000/dashboard/charts/data7",
		success: function(response){
			
			alert(response);
			
		},
		error: function e(error){
			alert("Error: "+ error);
		}
    });

    var ctxP = document.getElementById("Chart7").getContext('2d');
    var myPieChart = new Chart(ctxP, {
    type: 'doughnut',
    data: {
        labels: [],
        datasets: [{
            data: [],
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