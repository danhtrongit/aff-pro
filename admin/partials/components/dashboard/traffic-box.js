const template = `
    <div>
        
        <div class="flex justify-between q-mb-sm">
            <p>Thông tin lượt truy cập</p>
            <!-- <q-btn round size="sm" color="primary" icon="filter_list" class="q-mb-sm"/> -->
            <q-btn-dropdown split class="glossy" color="primary" label="Sắp xếp">
                <q-list>
                    <q-item clickable v-close-popup @click="sort('view', sort_type)">
                        <q-item-section avatar>
                            <q-avatar icon="folder" color="primary" text-color="white" />
                        </q-item-section>
                        <q-item-section>
                            <q-item-label>Số lượt truy cập</q-item-label>
                            <q-item-label caption>{{sort_type == 'asc' ? 'Giảm dần' : 'Tăng dần'}}</q-item-label>
                        </q-item-section>
                        <q-item-section side>
                            <q-icon name="info" color="amber" />
                        </q-item-section>
                    </q-item>
            
                    <q-item clickable v-close-popup @click="sort('order', sort_type)">
                        <q-item-section avatar>
                            <q-avatar icon="assignment" color="secondary" text-color="white" />
                        </q-item-section>
                        <q-item-section>
                            <q-item-label>Số đơn</q-item-label>
                            <q-item-label caption>{{sort_type == 'asc' ? 'Giảm dần' : 'Tăng dần'}}</q-item-label>
                        </q-item-section>
                        <q-item-section side>
                            <q-icon name="info" color="amber" />
                        </q-item-section>
                    </q-item>
                </q-list>
            </q-btn-dropdown>

        </div>
        <q-markup-table separator="horizontal" wrap-cells flat class="max-height-sc scroll-view-sc primary-bar">
            <thead>
                <tr>
                    <th class="text-left" width="50px">#</th>
                    <th class="text-left">URL | Sản phẩm</th>
                    <th class="text-center">Số truy cập</th>
                    <th class="text-center">Số đơn</th>
                    <th class="text-center">Tỉ lệ</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(record, i) in traffic_">
                    <td class="text-left"><q-img class="cursor-pointer" v-if="record.product_image" round :src="record.product_image" :ratio="1" width="50px" @click="openURL(record.url)" /></td>
                    <td class="text-left" v-html="record.product_name ? record.product_name : record.url"></td>
                    <td class="text-center"> {{ addCommas(record.total) }} </td>
                    <td class="text-center"><span v-if="record.orders_total">{{ addCommas(record.orders_total) }}</span></td>
                    <td class="text-center">
                        <span v-if="record.rate_conversion">{{ record.rate_conversion }}% </span>

                    </td>
                </tr>
        
            </tbody>
        </q-markup-table>
    </div>
          
`;
import { ORDER_STATUS, ORDER_STATUS_COLOR } from "../../constants/constants.js"

export default {
    props: ['traffic', 'orders'],
    data: () => ({
        settings: {},
        traffic_: [],
        sort_by: 'view',
        sort_type: 'asc',
        
    }),
    methods: {
        sort(by, type){
            this.sort_by = by
            this.sort_type = type == 'asc' ? 'desc' : 'asc'

            if(by == 'view'){
                if(this.sort_type == 'asc')
                    this.traffic_.sort((a, b) => (parseInt(a.total) < parseInt(b.total)) ? 1 : -1)
                else
                    this.traffic_.sort((a, b) => (parseInt(a.total) > parseInt(b.total)) ? 1 : -1)
            }
            if(by == 'order'){
                if(this.sort_type == 'asc')
                    this.traffic_.sort((a, b) => (parseInt(a.orders_total) < parseInt(b.orders_total)) ? 1 : -1)
                else
                    this.traffic_.sort((a, b) => (parseInt(a.orders_total) > parseInt(b.orders_total)) ? 1 : -1)
            }
        }
    },
    computed: {
        
    },
    components:{
        
    },
    template: template,
    created(){
        const traffic_ = [...this.traffic]
        if(traffic_.length){
            traffic_.forEach(el => {
                el.orders_total = 0;
                this.orders.forEach(o => {
                    if(o.ref_path == el.url)
                        el.orders_total++;
                })

                el.rate_conversion = el.orders_total ? (el.orders_total/el.total*100).toFixed(2) : 0 
            })
            this.traffic_ = traffic_

        }
        
        
    },
    watch: {
        
    },


}