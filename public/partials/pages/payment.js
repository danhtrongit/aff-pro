

const template = `
<div class="q-pa-md">
    <q-btn color="primary" label="Tạo lệnh rút tiền" class="q-mr-sm q-mb-md" @click="paymentDialog = true"/>
<loading-component v-if="isLoading"/>

<template v-else>

        
        <empty-component v-if="records.length == 0 && isLoading == false" />
        <div class="row q-col-gutter-md" v-else>
            <div class="dash-item col-12">
                    <div :class="!$q.platform.is.mobile ? 'card-item' : ''">
                        <q-markup-table separator="horizontal" :bordered="$q.platform.is.mobile ? true : false" :wrap-cells="$q.platform.is.mobile ? false : true" flat>
                            <thead>
                                <tr>
                                    <th class="text-left" width="50px">#</th>
                                    <th class="text-left desktop-only">Tài khoản</th>
                                    <th class="text-left">Số tiền</th>
                                    <th class="text-left">Ngân hàng</th>
                                    <th class="text-left">STK</th>
                                    <th class="text-left">Chủ TK</th>
                                    <th class="text-left">Mô tả</th>
                                    <th class="text-left">Thời gian</th>
                                    <th class="text-left">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(record, i) in records" :key="record.id">
                                   <td>{{record.id}}</td>
                                   <td class="desktop-only">{{record.user_login}}</td>
                                   <td>{{addCommas(record.amount)}}</td>
                                   <td>{{record.bank_info.bank_name}}</td>
                                   <td>{{record.bank_info.bank_number}}</td>
                                   <td>{{record.bank_info.bank_owner}}</td>
                                   <td>{{record.description}}</td>
                                   <td class="text-left">{{formatDateMoment(record.date)}}</td>

                                   <td><q-badge  :color="PAYMENT_STATUS[record.status].color" :label="PAYMENT_STATUS[record.status].label"></q-badge> </td>
                                  
                                </tr>
                        
                            </tbody>
                        </q-markup-table>

                        <div class="flex flex-center q-mt-lg">
                        
                            <q-pagination v-model="pagination.page" :max="pagination.max" :max-pages="6"  direction-links boundary-links :disabled="isLoading"></q-pagination>
                        
                            <template v-if="!$q.platform.is.mobile">
                                | Tổng {{pagination.total}} | Số bản ghi trên trang
                                <q-btn-dropdown color="primary" :label="pagination.per_page" class="q-ml-xs">
                                    <q-list>
                            
                                        <q-item clickable v-close-popup>
                                            <q-item-section @click="pagination.per_page = 10">
                                                <q-item-label>10</q-item-label>
                                            </q-item-section>
                                        </q-item>
                            
                                        <q-item clickable v-close-popup>
                                            <q-item-section @click="pagination.per_page = 20">
                                                <q-item-label>20</q-item-label>
                                            </q-item-section>
                                        </q-item>
                            
                                        <q-item clickable v-close-popup>
                                            <q-item-section @click="pagination.per_page = 50">
                                                <q-item-label>50</q-item-label>
                                            </q-item-section>
                                        </q-item>
                            
                                        <q-item clickable v-close-popup>
                                            <q-item-section @click="pagination.per_page = 100">
                                                <q-item-label>100</q-item-label>
                                            </q-item-section>
                                        </q-item>
                                        <q-item clickable v-close-popup>
                                            <q-item-section @click="pagination.per_page = 1000">
                                                <q-item-label>1000</q-item-label>
                                            </q-item-section>
                                        </q-item>
                            
                                    </q-list>
                                </q-btn-dropdown>
                            </template>
                        </div>

                    </div>
                </div>
                
            </div>
            
            <PaymentDialog :paymentDialog="paymentDialog" @close="paymentDialog = false" @refresh="getData"/>
         


            
            
        </template>
</div>
`;
import PaymentDialog from '../components/payment/create-payment-dialog.js'
import { getPayments } from "../api/payment.js"
import { LANG_DATE_PICKER, PAYMENT_STATUS } from "../constants/constants.js"
const { RV_CONFIGS } = window 
export default {
    data: () => ({
        configs: RV_CONFIGS,
        PAYMENT_STATUS,
        isLoading: false,
        filters: {
            status: ''
        },
        lang: LANG_DATE_PICKER,
        records: [],
        pagination: {
            page: 1,
            max: 1,
            per_page: 15,
            total: 0,
        },
        paymentDialog: false,
        
      
    }),
   
    methods: {
        async getData(){
             this.isLoading = true;
            const res = await getPayments({filters: this.filters,  page: this.pagination.page, per_page: this.pagination.per_page})
            const {data, pagination} = res.data
            if(data){
                data.forEach(el => {
                    el.bank_info = JSON.parse(el.bank_info)
                })
                this.records = data
            }
            this.pagination.total = pagination.total
            this.pagination.max = pagination.max_page
            this.isLoading = false
          
        },
        async approve(id, status){
            const confirm = await this.CONFIRM(`Bạn chắc chắn muốn ${status == 0 ? 'Hủy' : 'Duyệt'} lệnh rút tiền này`)
            if(!confirm)
                return;
            const res = await approvePayment({id, status})
            const {success, msg} = res.data
            this.NOTIFY(msg, success)
            if(success)
                this.getData()
        }
	},
	components:{
        PaymentDialog
	},
    watch:{
      'filters.status': function(){
          this.getData()
      },
      'pagination.page': function(){
          this.getData()
      },
      'filters.search': function(){
        this.pagination.page = 1
        this.getData()
      },
      'pagination.per_page': function(){
        this.pagination.page = 1
        this.getData()
      },
      
    },
    template: template,
    created(){
        if(this.configs.user_id)
            this.filters.user_id = this.configs.user_id

        this.getData();
        this.$eventBus.$emit('set.page_title', 'Yêu cầu thanh toán');
    }

}