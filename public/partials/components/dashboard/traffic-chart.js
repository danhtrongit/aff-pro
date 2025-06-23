let { Line } = VueChartJs


export default {
   extends: Line,
   props: ['chart'],
   data() {
      return {
         datacollection: {
            //Data to be represented on x-axis
            labels: [],
            datasets: [{
               label: 'Đơn mới',
               backgroundColor: '#2196f3a6',
               pointBackgroundColor: 'white',
             
               borderColor:"#2196f3a6",
               fill: false,
               cubicInterpolationMode: 'monotone',
               tension: 0.4,

               //Data to be represented on y-axis
               data: [],
               
            },
           
        ]
         },
         //Chart.js options that controls the appearance of the chart
         options: {
            scales: {
               yAxes: [{
                  ticks: {
                     beginAtZero: true,
                     // stepSize: 1,
                  },
                  gridLines: {
                     display: true
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
      //renderChart function renders the chart with the datacollection and options object.
      //   this.renderChart(this.datacollection, this.options)
      this.renderChart(this.chart.datacollection, this.options)

   }
}