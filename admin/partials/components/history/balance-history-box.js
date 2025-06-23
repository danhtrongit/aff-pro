const template = `
    <div>
       
        <q-markup-table separator="horizontal" wrap-cells flat>
            <thead>
                <tr>
                    <th class="text-left" width="50px">#</th>
                    <th class="text-left">Tài khoản</th>
                    <th class="text-left">Số dư đầu</th>
                    <th class="text-left">Số dư cuối</th>
                    <th class="text-left">Mô tả</th>
                    <th class="text-left">Ngày</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(record, i) in orders" :key="record.ID">
                    <td class="text-left">{{ record.order_id }}</td>
                    <td class="text-left">{{ record.user_login }}</td>
                    <td class="text-left">{{ addCommas(record.total) }}</td>
                    <td class="text-left">
                        <q-badge v-if="record.order_status" :color="ORDER_STATUS_COLOR[record.order_status]">{{ ORDER_STATUS[record.order_status] }}</q-badge></td>
                     <td class="text-left">{{formatDateMoment(record.date)}}</td>
                    <td class="text-left">
                        <q-btn round color="primary" icon="visibility" size="sm" class="q-mr-xs" @click="openURL(RV_CONFIGS.site_url + '/wp-admin/post.php?post=' + record.order_id + '&action=edit')"/>
                    </td>
                </tr>
        
            </tbody>
        </q-markup-table>
    </div>
          
`;
import { ORDER_STATUS, ORDER_STATUS_COLOR } from "../../constants/constants.js"
const { RV_CONFIGS } = window
export default {
    props: ['balance_history'],
    data: () => ({
        settings: {},
        ORDER_STATUS,
        ORDER_STATUS_COLOR,
        RV_CONFIGS
    }),
    methods: {
     
        
    },
    computed: {
        
    },
    components:{
        
    },
    template: template,
    created(){
        
    },
    watch: {
        
    },


}