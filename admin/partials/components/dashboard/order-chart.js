let { Bar } = VueChartJs


export default {
   extends: Bar,
   props: ['chart'],
   data() {
      return {
         datacollection: {
            //Data to be represented on x-axis
            labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            datasets: [{
               label: 'Số đơn gửi',
               yAxisID: 'A',
               backgroundColor: '#2196f3a6',
               pointBackgroundColor: 'white',
             
             
               fill: false,
               cubicInterpolationMode: 'monotone',
               tension: 0.4,

               //Data to be represented on y-axis
               data: [40, 20, 30, 50, 90, 10, 20, 40, 50, 70, 90, 100],
               
            },
            {
               label: 'COD',
               yAxisID: 'B',
               backgroundColor: '#ff6384a3',
               pointBackgroundColor: 'white',
             
             
               fill: false,
               cubicInterpolationMode: 'monotone',
               tension: 0.4,

               //Data to be represented on y-axis
               data: [40, 20, 30, 50, 90, 10, 20, 40, 50, 70, 90, 100],
               
            }
        ]
         },
         //Chart.js options that controls the appearance of the chart
         options: {
            scales: {
               yAxes: [{
                    id: 'A',
                    type: 'linear',
                    position: 'left',
                    ticks: {
                        beginAtZero: true,
                        // stepSize: 1
                     },
                  gridLines: {
                     display: true
                  }
               },{
                    id: 'B',
                    type: 'linear',
                    position: 'right',
                  ticks: {
                     beginAtZero: true,
                     // stepSize: 1,
                  },
                  gridLines: {
                     display: true
                  }
               }],
               xAxes: [{
                  gridLines: {
                     display: false
                  }
               }]
            },
            legend: {
               display: true
            },
            responsive: true,
            maintainAspectRatio: false
         }
      }
   },
   mounted() {
      // console.log(123);
      //renderChart function renders the chart with the datacollection and options object.
      // console.log(this.chart.datacollection.datasets[0].data);
      this.options.scales.yAxes[0].ticks.suggestedMax = Math.ceil(this.chart.datacollection.datasets[0].max + this.chart.datacollection.datasets[0].max*0.1) 
      this.options.scales.yAxes[1].ticks.suggestedMax = Math.ceil(this.chart.datacollection.datasets[1].max + this.chart.datacollection.datasets[0].max*0.1)
      
      this.renderChart(this.chart.datacollection, this.options)
   }
}