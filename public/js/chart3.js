$.ajax({url:"http://127.0.0.1:8000/dashboard/charts/data3",
		success: function(response){
			
			alert(response);
			
		},
		error: function e(error){
			alert("Error: "+ error);
		}
    });

    var ctxP = document.getElementById("Chart3").getContext('2d');
    var myPieChart = new Chart(ctxP, {
    type: 'pie',
    data: {
        labels: ["15M049","16M306","16M608","16M609","17E360","17E380","17E430","17E566","17E568","17E758","17E765","17E766","17E771","17E794","17M315","17M316","17M317","17M431"],
        datasets: [{
            data: [],
            backgroundColor: ["#C0392B", "#E74C3C","#9B59B6", "#8E44AD","#2980B9","#3498DB", "#1ABC9C","#16A085", "#F1C40F","#F39C12"],
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