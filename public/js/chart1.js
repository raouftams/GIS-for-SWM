$.ajax({url:"http://127.0.0.1:8000/dashboard/charts/data1",
		success: function(response){
			
			alert(response);
			
		},
		error: function e(error){
			alert("Error: "+ error);
		}
    });

    var ctx = document.getElementById('Chart1').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ["C013-A11","C013-A12","C013-A21","C013-A22","C013-B11","C013-B12","C013-C11","C013-C12","C013-C21","C013-C22"],
            datasets: [{
                label: 'Quantitée',
                data: [1230,12355,15846,15621,415469,48968946,54869,8,35,259],
                backgroundColor: [
                    'rgba(204, 25, 5, 0.3)',
                    'rgba(175, 53, 42, 0.3)',
                    'rgba(235, 145, 0, 0.3)',
                    'rgba(244, 218, 19, 0.3)',
                    'rgba(63, 221, 19, 0.3)',
                    'rgba(0, 194, 155, 0.3)',
                    'rgba(71, 127, 247, 0.3)',
                    'rgba(40, 26, 191, 0.3)',
                    'rgba(135, 36, 178, 0.3)',
                    'rgba(198, 39, 219, 0.3)'
                ],
                borderColor: [
                    'rgba(204, 25, 5, 1)',
                    'rgba(175, 53, 42, 1)',
                    'rgba(235, 145, 0, 1)',
                    'rgba(244, 218, 19, 1)',
                    'rgba(63, 221, 19, 1)',
                    'rgba(0, 194, 155, 1)',
                    'rgba(71, 127, 247, 1)',
                    'rgba(40, 26, 191, 1)',
                    'rgba(135, 36, 178, 1)',
                    'rgba(198, 39, 219, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            legend:{
                display : false
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