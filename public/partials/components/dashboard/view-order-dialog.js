const template = `
    <q-dialog
       v-model="view_order.showDialog"
       persistent 
       :maximized="$q.platform.is.mobile"
       transition-show="slide-up"
       transition-hide="slide-down"
    >
      <q-card :style="!$q.platform.is.mobile ? 'width: 600px; max-width: 80vw;' : 'width:100%'" class="bd-rd-0 scroll-view-sc green-bar">
        <q-card-section class="row items-center bg-green text-white">
          <div class="text-h6 text-white">Thông tin đơn hàng: #{{this.order.order_id}}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>


        <q-card-section class="">
           <!-- {{order.order_json}} -->

          <div class="list-products q-pa-md bg-grey-1">
            <div class="product-item flex-dp q-mb-sm q-pb-sm" v-for="product in order.order_json.products">
              <q-img round :src="product.image" :ratio="1" width="50px" @click="openURL(product.link)" />
              <div class="product-name q-ml-md">
                {{product.name}}
                <br />
                <q-badge class="q-pa-xs q-ml-xs" color="primary">Số lượng: {{product.quantity}}</q-badge>
                <q-badge class="q-pa-xs q-ml-xs" color="accent">{{addCommas(product.total)}} đ</q-badge>
              </div>
            </div>
          
          </div>
          <div class="q-my-md">Giá trị đơn hàng</div>
          <q-markup-table flat separator="cell" bordered>
            <tbody class="bg-grey-1">
              <tr>
                <td class="text-left">Tiền hàng</td>
                <td class="text-right">{{ addCommas(order.order_json.sub_total) }} đ</td>
              </tr>
              <tr>
                <td class="text-left">Phí ship</td>
                <td class="text-right">{{ addCommas(order.order_json.shipping_fee) }} đ</td>
              </tr>
              <tr>
                <td class="text-left">Tổng đơn </td>
                <td class="text-right">{{ addCommas(order.total) }} đ</td>
              </tr>
            </tbody>
          </q-markup-table>

          <div class="q-my-md">Thông tin người nhận</div>
          <q-markup-table flat separator="cell" bordered>
            <tbody class="bg-grey-1">
              <tr>
                <td class="text-left">Điện thoại</td>
                <td class="text-right">{{order.order_json.customer_phone}}</td>
              </tr>
              <tr>
                <td class="text-left">Họ tên</td>
                <td class="text-right">{{order.order_json.customer_name}}</td>
              </tr>
              <tr>
                <td class="text-left">Địa chỉ</td>
                <td class="text-right">{{order.order_json.customer_address}}</td>
              </tr>
            
          
            </tbody>
          </q-markup-table>

          <div class="q-my-md" v-if="comments.length">Ghi chú</div>
          <q-markup-table flat separator="cell" wrap-cells bordered v-if="comments.length">
            <tbody class="bg-grey-1">
              <tr v-for="c in comments">
                <td class="text-left">{{c.comment_content}}</td>
                <td class="text-right">{{formatDateMoment(c.comment_date, 'DD/MM/YYYY')}}</td>
              </tr>
              
          
            </tbody>
          </q-markup-table>

        </q-card-section>

        <q-card-section align="right" class="bg-white text-teal">
          <q-btn color="green" label="Thoát" v-close-popup/>
        </q-card-section >
      </q-card>
    </q-dialog>
          
`;
import { getListOrderComments } from "../../api/dashboard.js"
import { ORDER_STATUS, ORDER_STATUS_COLOR } from "../../constants/constants.js"
const { RV_CONFIGS } = window
export default {
    props: ['view_order'],
    data: () => ({
        settings: {},
        ORDER_STATUS,
        ORDER_STATUS_COLOR,
        RV_CONFIGS,
        order: {},
        comments: []
    }),
    methods: {
      async getListOrderComments(order_id){
         const res = await getListOrderComments(order_id)
         this.comments = res.data.data
      }
        
    },
    computed: {
        
    },
    components:{
        
    },
    template: template,
    created(){
      const order = {...this.view_order.order}

      if(order.order_json)
        order.order_json = JSON.parse(order.order_json)

      
      order.order_json.customer_phone = order.order_json.customer_phone.slice(0, -4) + '****'
      // this.getListOrderComments(order.order_id)
      this.order = order
    },
    watch: {
        
    },


}