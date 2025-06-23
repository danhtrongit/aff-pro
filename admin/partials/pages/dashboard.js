

const template = `
<div class="q-mt-lg">
    <div>
            
            <div class="row q-col-gutter-md q-mb-md">
              <div class="col-6 flex" style="align-items:center">

              </div>
            <div class="col-2 text-right">
                    <q-btn round class="q-mt-xs" color="pink" size="sm" :icon=" col == 6 ? 'crop_free' : 'zoom_in_map'" @click="col == 12 ? col = 6 : col = 12"/>
            </div>
            
            <div class="col-3">
                <div class="mx-height-40">
                    <date-picker v-model="filters.date_range" type="date" range placeholder="Chọn theo ngày" confirm format="DD-MM-YYYY" valueType="YYYY-MM-DD" :lang="lang"></date-picker>

                </div>
            </div>
            <div class="col-1">
                <q-btn color="primary" label="Lọc" style="width: 100%" @click="getData"/>
            </div>
            </div>

            <div class="row q-col-gutter-md">
                <div class="dash-item col-3">
                    <div class="card-item ">
                        <div class="flex justify-between items-center">
                            <div class="">
                                <div class="card-name">Đơn thành công / Tổng đơn</div>
                                <div class="card-number">{{addCommas(this.info.completed_orders)}} / {{addCommas(this.info.orders)}}</div>
                            </div>
                            <div class="b-avatar badge-light-warning rounded-circle">
                                <q-img :src=" confis.plugin_url + '/public/images/box.svg'" :ratio="1" width="60px"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dash-item col-3">
                    <div class="card-item ">
                        <div class="flex justify-between items-center">
                            <div class="">
                                <div class="card-name">Thành công / Tổng doanh thu</div>
                                <div class="card-number">{{addCommas(this.info.approved_income)}} / {{addCommas(this.info.income)}}</div>
                            </div>
                            <div class="b-avatar badge-light-warning rounded-circle">
                                <q-img :src=" confis.plugin_url + '/public/images/cZJJSE.svg'" :ratio="1" width="60px" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dash-item col-3">
                    <div class="card-item ">
                        <div class="flex justify-between items-center">
                            <div class="">
                                <div class="card-name">Đã duyệt / Tổng hoa hồng</div>
                                <div class="card-number">{{addCommas(this.info.approved_commission)}} / {{addCommas(this.info.commission)}}</div>
                            </div>
                            <div class="b-avatar badge-light-warning rounded-circle">
                                <q-img :src=" confis.plugin_url + '/public/images/cqwzYn.svg'" :ratio="1" width="60px" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dash-item col-3">
                    <div class="card-item ">
                        <div class="flex justify-between items-center">
                            <div class="">
                                <div class="card-name">Lượt truy cập / % Chuyển đổi</div>
                                <div class="card-number">{{addCommas(this.info.views)}} / {{this.info.conversion_rate}} %</div>
                            </div>
                            <div class="b-avatar badge-light-warning rounded-circle">
                                <q-img :src=" confis.plugin_url + '/public/images/cz00k6.svg'" :ratio="1" width="60px" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row q-col-gutter-md" v-if="!isLoading">
                <div :class="'dash-item col-' + col">
                    <div class="card-item ">
                        <OrderChart :chart="order_chart"/>
                    </div>
                </div>
                <div :class="'dash-item col-' + col">
                    <div class="card-item ">
                        <TrafficChart :chart="traffic_chart"/>
                    </div>
                </div>
            </div>
            
            <div class="row q-col-gutter-md">
                <div :class="'dash-item col-' + col"  v-if="orders.length">
                    <div class="card-item ">
                        <OrderBox :orders="orders"/>
                    </div>
                </div>

                <div :class="'dash-item col-' + col"  v-if="traffic.length">
                    <div class="card-item ">
                        <TrafficBox :traffic="traffic" :orders="orders"/>
                    </div>
                </div>
                
            </div>

         


            
            
    </div>
</div>
`;
import OrderChart from '../components/dashboard/order-chart.js'
import TrafficChart from '../components/dashboard/traffic-chart.js'
import OrderBox from '../components/dashboard/order-box.js'
import TrafficBox from '../components/dashboard/traffic-box.js'

import { LANG_DATE_PICKER, sevenDaysAgoMoment } from "../constants/constants.js"
import { getDashboardInfo } from '../api/dashboard.js'
const { RV_CONFIGS } = window
export default {
    props: ['user_id'],
    data: () => ({
        confis: RV_CONFIGS,
        isLoading: true,
        filters: {
            date_range: sevenDaysAgoMoment("array", "YYYY-MM-DD"),
            user_id: null
        },
        lang: LANG_DATE_PICKER,
        col: 6,
        info: {
            orders: 0,
            completed_orders: 0,
            views: 0,
            income: 0,
            approved_income: 0,
            commission: 0,
            approved_commission: 0,
            conversion_rate: 0,
        },
        order_chart: {
            datacollection: {
                labels: [],
                datasets: [

                    {
                        label: 'Tổng đơn',
                        yAxisID: 'B',
                        backgroundColor: 'rgba(255, 155, 176, 1)',
                        tension: 0.4,
                        //Data to be represented on y-axis
                        data: [],
                    },
                    {
                        label: 'Đơn thành công',
                        yAxisID: 'A',
                        backgroundColor: 'rgba(110, 187, 247, 1)',
                        tension: 0.4,
                        //Data to be represented on y-axis
                        data: [],
                    },
                ]
            },
            options: {

            }

        },
        traffic_chart: {
            datacollection: {
                //Data to be represented on x-axis
                labels: ['2'],
                datasets: [{
                    label: 'Lượt truy cập',
                    backgroundColor: '#2196f3a6',
                    pointBackgroundColor: 'white',

                    borderColor: "#2196f3a6",
                    fill: false,
                    cubicInterpolationMode: 'monotone',
                    tension: 0.4,

                    //Data to be represented on y-axis
                    data: ['2'],

                },

                ]
            }
        },
        orders: [],
        traffic: [],

    }),

    methods: {
        async getData() {

            if (!this.filters.date_range[0]) {
                this.NOTIFY('Vui lòng chọn khoảng ngày', false)
                return;
            }
            this.$q.loading.show()
            this.isLoading = true

            this.orders = []
            this.traffic = []
            const res = await getDashboardInfo(this.filters)
            const { info, chart, orders, traffic } = res.data.data

            this.orders = orders
            this.traffic = traffic
            this.$set(this, 'traffic', traffic)

            this.info = info
            this.order_chart.datacollection.labels = chart.date_range
            this.order_chart.datacollection.datasets[0].data = chart.orders
            this.order_chart.datacollection.datasets[1].data = chart.completed_orders

            this.traffic_chart.datacollection.labels = chart.date_range
            this.traffic_chart.datacollection.datasets[0].data = chart.views

            this.order_chart.datacollection.datasets[0].max = Math.max(...chart.orders)
            this.order_chart.datacollection.datasets[1].max = Math.max(...chart.orders)

            this.$q.loading.hide()
            this.isLoading = false
        }
    },
    components: {
        TrafficChart,
        OrderChart,
        OrderBox,
        TrafficBox
    },
    watch: {

        'filters.date_range': function () {
            this.getData()
        },

    },
    template: template,
    created() {
        if (this.$route.query.id)
            this.filters.user_id = this.$route.query.id

        if (this.user_id)
            this.filters.user_id = this.user_id

        this.getData();
        this.$eventBus.$emit('set.page_title', 'Dashboard');
    }

}