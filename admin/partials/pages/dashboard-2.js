

const template = `
<div class="q-pa-md dashboard-2-page">
    <div>
            
            <div class="row q-col-gutter-md q-mb-md">
                <div class="col-6 flex desktop-only" style="align-items:center">
            
                </div>
                <div class="col-2 desktop-only">
                    <q-select filled map-options v-model="filters.view_mode" :options="level_options" label="Chế độ xem" emit-value dense/>
                </div>
            
                <div class="col-8 col-md-3">
                    <div class="mx-height-40">
                        <date-picker v-model="filters.date_range" type="date" range placeholder="Chọn theo ngày" confirm
                            format="DD-MM-YYYY" valueType="YYYY-MM-DD" :lang="lang"></date-picker>
            
                    </div>
                </div>
                <div class="col-4 col-md-1">
                    <q-btn color="primary" label="Lọc" style="width: 100%" @click="getData" />
                </div>
            </div>

            <div class="row q-col-gutter-md">
                <div class="dash-item col-12 col-md-4">
                    <div class="card-item ">
                        <div class="flex justify-between items-center">
                            <div class="">
                                <div class="card-name">Đơn thành công / Tổng đơn</div>
                                <div class="card-number">{{addCommas(this.info.completed_orders)}} / {{addCommas(this.info.orders)}}</div>
                            </div>
                            <div class="b-avatar badge-light-warning rounded-circle">
                                <q-img :src=" configs.plugin_url + '/public/images/czuHxJ.svg'" :ratio="1" width="60px"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dash-item col-12 col-md-4">
                    <div class="card-item ">
                        <div class="flex justify-between items-center">
                            <div class="">
                                <div class="card-name">Thành công / Tổng doanh thu</div>
                                <div class="card-number">{{addCommas(this.info.approved_income)}} / {{addCommas(this.info.income)}}</div>
                            </div>
                            <div class="b-avatar badge-light-warning rounded-circle">
                                <q-img :src=" configs.plugin_url + '/public/images/cZJJSE.svg'" :ratio="1" width="60px" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dash-item col-12 col-md-4 q-mobile-mb-md">
                    <div class="card-item">
                        <div class="flex justify-between items-center">
                            <div class="">
                                <div class="card-name">Đã duyệt /Hoa hồng chờ duyệt</div>
                                <div class="card-number">{{addCommas(this.info.approved_commission)}} / {{addCommas(this.info.commission)}}</div>
                            </div>
                            <div class="b-avatar badge-light-warning rounded-circle">
                                <q-img :src=" configs.plugin_url + '/public/images/cqwzYn.svg'" :ratio="1" width="60px" />
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

            <div class="row q-col-gutter-md" v-if="!isLoading">
                
                <div class="dash-item col-12" v-if="orders.length">
                    <div class="card-item ">
                        <OrderBox :orders="orders"/>
                    </div>
                </div>
            </div>
            
     


            
            
    </div>
</div>
`;
import OrderBox from '../components/dashboard/order-box-2.js'

import { LANG_DATE_PICKER, sevenDaysAgoMoment } from "../constants/constants.js"
import { getDashboardInfo2 } from '../api/dashboard.js'
const { RV_CONFIGS } = window
export default {
    props: ['user_id'],
    data: () => ({
        configs: RV_CONFIGS,
        isLoading: true,
        filters: {
            date_range: sevenDaysAgoMoment("array", "YYYY-MM-DD"),
            user_id: null,
            view_mode: 'all'
        },
        level_options: [
            {
                label: 'Tất cả',
                value: 'all',
            },
            {
                label: 'Chỉ mình tôi',
                value: 'only_me',
            },
            {
                label: 'Chỉ cộng tác viên',
                value: 'collaborators',
            },
        ],
        lang: LANG_DATE_PICKER,
        info: {
            orders: 0,
            completed_orders: 0,
            income: 0,
            approved_income: 0,
            commission: 0,
            approved_commission: 0,
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

            this.orders = []
            this.traffic = []
            this.isLoading = true;
            const res = await getDashboardInfo2(this.filters)


            const { info, orders } = res.data.data
            this.info = info
            this.orders = orders
            this.isLoading = false

        }
    },
    components: {
        OrderBox,
    },
    watch: {

        'filters.date_range': function () {
            this.getData()
        },
        'filters.view_mode': function () {
            this.getData()
        },

    },
    template: template,
    created() {

        if (this.configs.user_id)
            this.filters.user_id = this.configs.user_id

        if (this.user_id)
            this.filters.user_id = this.user_id

        this.getData();
        this.$eventBus.$emit('set.page_title', 'Đơn hàng từ CTV');
    }

}