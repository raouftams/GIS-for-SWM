function getRandomRGBA() {
    function numbers() {return Math.floor(Math.random() * 256);} 
    return "rgba(" + numbers() + ", " + numbers() + ", " + numbers() + ", ";
}

var label1 = [];
var data1 = [];
var bccolor = [];
var bocolor = [];
$.ajax({url:"http://127.0.0.1:8000/dashboard/charts/data1",
		success: function(response){
            var result = JSON.parse(response);
			result.forEach(function(obj){
                label1.push(obj.label);
                data1.push(obj.data);
               var c = getRandomRGBA();
                bocolor.push(c + (0.5).toFixed(1) + ")");
                bccolor.push(c + (1).toFixed(1) + ")");
            });
            
            var ctx = document.getElementById('Chart1').getContext('2d');
            var myBarChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: label1,
                    datasets: [{
                        label: 'Quantitée',
                        data: data1,
                        backgroundColor: bocolor,
                        borderColor: bccolor,
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
            
		},
		error: function e(error){
			alert("Error: "+ error );
		}
    });
   