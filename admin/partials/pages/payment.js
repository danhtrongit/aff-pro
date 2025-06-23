

const template = `
<div class="q-mt-lg">
<loading-component v-if="isLoading"/>

<template v-else>

            
            <div class="row q-col-gutter-md q-mb-md">
              <div class="col-6 flex" style="align-items:center">
                <q-btn :color="filters.status === '' ? 'primary' : 'grey'" label="Tất cả" class="q-mr-sm" @click="filters.status = ''"/>
                <q-btn :color="filters.status === 0 ? 'primary' : 'grey'" label="Chờ duyệt" class="q-mr-sm" @click="filters.status = 0"/>
                <q-btn :color="filters.status == 1 ? 'primary' : 'grey'" label="Đã duyệt" class="q-mr-sm" @click="filters.status = 1"/>
                <q-btn :color="filters.status == 2 ? 'primary' : 'grey'" label="Đã hủy" @click="filters.status = 2"/>
              </div>
            <div class="col-2">
            </div>
            
            <div class="col-3">
                <q-input filled dense v-model="filters.search" label="Nhập tên tài khoản" debounce="600"/>
            </div>
            <div class="col-1">
                <q-btn color="primary" label="Lọc" style="width: 100%" @click="getData"/>
            </div>
            </div> 

            <empty-component v-if="records.length == 0 && isLoading == false" />
            <div class="row q-col-gutter-md" v-else>
                <div class="dash-item col-12">
                    <div class="card-item ">
                        <q-markup-table separator="horizontal" wrap-cells flat>
                            <thead>
                                <tr>
                                    <th class="text-left" width="50px">#</th>
                                    <th class="text-left">Tài khoản</th>
                                    <th class="text-left">Số tiền</th>
                                    <th class="text-left">Ngân hàng</th>
                                    <th class="text-left">STK</th>
                                    <th class="text-left">Chủ TK</th>
                                    <th class="text-left">Mô tả</th>
                                    <th class="text-left">Thời gian</th>
                                    <th class="text-left">Trạng thái</th>
                                    <th class="text-left">#</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(record, i) in records" :key="record.id">
                                   <td>{{record.id}}</td>
                                   <td>{{record.user_login}}</td>
                                    <td>{{addCommas(record.amount)}}</td>
                                    <td>{{record.bank_info.bank_name}}</td>
                                    <td>{{record.bank_info.bank_number}}</td>
                                    <td>{{record.bank_info.bank_owner}}</td>
                                   <td>{{record.description}}</td>
                                   <td class="text-left">{{formatDateMoment(record.date)}}</td>
                                   <td><q-badge  :color="PAYMENT_STATUS[record.status].color" :label="PAYMENT_STATUS[record.status].label"></q-badge> </td>
                                   <td>
                                       <template v-if="record.status == 0">
                                           <q-btn round color="green" icon="done" size="sm" class="q-mr-xs" @click="approve(record.id, 1)" />
                                           <q-btn round color="pink" icon="close" size="sm" class="q-mr-xs" @click="approve(record.id, 2)" />
                                        </template>
                                       <q-btn round color="primary" icon="qr_code_2" size="sm" class="q-mr-xs" @click="showQr(record)" />
                                    </td>
                                </tr>
                        
                            </tbody>
                        </q-markup-table>

                        <div class="flex flex-center q-mt-lg">
                        
                            <q-pagination v-model="pagination.page" :max="pagination.max" :max-pages="6"  direction-links boundary-links :disabled="isLoading"></q-pagination>
                        
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
                        </div>

                    </div>
                </div>
                
            </div>
            

            <q-dialog
               v-model="showPayment.showDialog"
               persistent transition-show="fade-in" transition-hide="fade-out" 
            >
              <q-card style="width: 800px; max-width: 80vw;">
                <q-card-section class="row items-center" style="background:#FDA524; color:#fff">
                  <div class="text-h6">#{{showPayment.payment.id}} Thanh toán cho tài khoản: {{showPayment.payment.user_login}}</div>
                  <q-space />
                  <q-btn icon="close" flat round dense v-close-popup />
                </q-card-section>


                <q-card-section class="">
                    <div class="row q-col-gutter-md">
                        <div class="col-5">
                            <q-img ratio="1" :src="showPayment.payment.qr">
                        </div>
                        <div class="col-7">
                            <q-markup-table v-if="Object.keys(showPayment.payment).length" flat>
                                  <tbody>
                                    <tr>
                                      <td>Ngân hàng</td>
                                      <td>{{showPayment.payment.bank_info.bank_name}}</td>
                                    </tr>
                                    <tr>
                                      <td>Số tài khoản</td>
                                      <td>{{showPayment.payment.bank_info.bank_number}}</td>
                                    </tr>
                                    <tr>
                                      <td>Chủ tài khoản</td>
                                      <td>{{showPayment.payment.bank_info.bank_owner}}</td>
                                    </tr>
                                    <tr>
                                      <td>Số tiền</td>
                                      <td>{{addCommas(showPayment.payment.amount)}}</td>
                                    </tr>
                                    <tr>
                                      <td>Nội dung</td>
                                      <td>{{showPayment.payment.message}}</td>
                                    </tr>
                                    
                                  </tbody>
                            </q-markup-table>
                           <template v-if="showPayment.payment.status == 0">
                                <q-btn  color="green" icon="done" label="Đã thanh toán"  class="q-mr-xs" @click="approve(showPayment.payment.id, 1)" />
                                <q-btn  color="pink" icon="close" label="Hủy lệnh thanh toán"  class="q-mr-xs" @click="approve(showPayment.payment.id, 2)" />
                            </template>
                        </div>
                    </div>
                </q-card-section>

               
              </q-card>
            </q-dialog>


            
            
        </template>
</div>
`;
import { banks } from "../../../../public/partials/constants/banks.js";
import { getPayments, approvePayment } from "../api/payment.js"
import { LANG_DATE_PICKER, PAYMENT_STATUS } from "../constants/constants.js"
const { RV_CONFIGS } = window 
export default {
    data: () => ({
        configs: RV_CONFIGS,
        PAYMENT_STATUS,
        isLoading: false,
        filters: {
            status: '',
            search: ''
        },
        lang: LANG_DATE_PICKER,
        records: [],
        pagination: {
            page: 1,
            max: 1,
            per_page: 15,
            total: 0,
        },
        showPayment: {
            showDialog: false,
            payment: {

            }
        }
      
    }),
   
    methods: {
        async getData(){
            this.$q.loading.show()
            const res = await getPayments({filters: this.filters, page: this.pagination.page, per_page: this.pagination.per_page})
            const {data, pagination} = res.data
            if(data){
                data.forEach(el => {
                    el.bank_info = JSON.parse(el.bank_info)
                })
                this.records = data
            }
            this.pagination.total = pagination.total
            this.pagination.max = pagination.max_page
            this.$q.loading.hide()

          
        },
        async approve(id, status){
            let confirm, res, description = '';
            if(status == 2){
                description = await this.PROMPT('Nhập lý do bạn hủy lệnh rút tiền này')
                if(description){
                    this.$q.loading.show()
                    res = await approvePayment({id, status, description})
                    this.$q.loading.hide()

                    const {success, msg} = res.data
                    this.NOTIFY(msg, success)
                    if(success)
                        this.getData()
                }
            }
            else{
                confirm = await this.CONFIRM(`Bạn chắc chắn muốn ${status == 0 ? 'Hủy' : 'Duyệt'} lệnh rút tiền này`)
                if(confirm){
                    this.$q.loading.show()
                    res = await approvePayment({id, status})
                    this.$q.loading.hide()

                    const {success, msg} = res.data
                    this.NOTIFY(msg, success)
                    if(success)
                        this.getData()
                }

            }
                
            
        },

        showQr(record){
            // https://api.vietqr.io/'970436/stk/amount/note/qr_only.jpg
            const payment = {...record}
            if(payment.bank_info.bank_name){
                let site_url = this.configs.site_url
                site_url = site_url.replace('https://', '')
                site_url = site_url.replace('http://', '')
                site_url = site_url.replace('/', '')
                const message = site_url + ' thanh toan hoa hong'
                payment.message = message
                const bankCode = banks.data.find(el => payment.bank_info.bank_name == el.shortName)
                payment.qr = bankCode ? `https://api.vietqr.io/${bankCode.bin}/${payment.bank_info.bank_number}/${encodeURI(payment.amount)}/${message}/qr_only.jpg` : ''
            }
            console.log(payment.qr);
            this.showPayment.payment = payment
            // this.showPayment.payment.qr = 
            this.showPayment.showDialog = true
        }
	},
	components:{
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
        this.getData();
        this.$eventBus.$emit('set.page_title', 'Thanh toán');
    }

}