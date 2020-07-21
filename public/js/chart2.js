$.ajax({url:"http://127.0.0.1:8000/dashboard/charts/data2",
		success: function(response){
			
			alert(response);
			
		},
		error: function e(error){
			alert("Error: "+ error);
		}
    });

var ctxL = document.getElementById("Chart2").getContext('2d');
var myLineChart = new Chart(ctxL, {
type: 'line',
data: {
    labels: ["Janvier", "Fevrier", "Mars", "Avril", "Mai", "Juin", "Juillet","Août","Septembre", "Octobre", "Novembre","Décembre"],
    datasets: [{
        label: "My First dataset",
        data: [],
        backgroundColor: ['rgba(105, 0, 132, .2)',],
        borderColor: ['rgba(200, 99, 132, .7)',],
        borderWidth: 2
        },]
    },
options: {
    responsive: true
    }
});