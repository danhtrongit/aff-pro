

const template = `
<div class="q-pa-md">
<loading-component v-if="isLoading"/>
<empty-component v-else-if="records.length == 0 && isLoading == false" />
<template v-else>

            
            <div class="row q-col-gutter-md q-mb-md">
                <div class="col-6 flex desktop-only" style="align-items:center">
            
                </div>
                <div class="col-2 desktop-only">
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
                <div class="dash-item col-12">
                    <div :class="!$q.platform.is.mobile ? 'card-item' : ''">
                        <q-markup-table separator="horizontal" :bordered="$q.platform.is.mobile ? true : false" :wrap-cells="$q.platform.is.mobile ? false : true" flat>
                            <thead>
                                <tr>
                                    <th class="text-left" width="50px">#</th>
                                    <th class="text-left">Số tiền</th>
                                    <th class="text-left">Số dư đầu</th>
                                    <th class="text-left">Số dư cuối</th>
                                    <th class="text-left desktop-only">Mô tả</th>
                                    <th class="text-left">Ngày</th>
                                    <th class="text-left">Loại</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(record, i) in records" :key="record.id">
                                    <td class="text-left">
                                        <template v-if="!$q.platform.is.mobile">
                                            {{record.id}}
                                        </template>
                                        <template v-else>
                                            <q-btn icon="help_outline" flat size="sm" round color="primary">
                                                <q-tooltip anchor="top middle" self="bottom middle" :offset="[10, 10]">{{record.description}}</q-tooltip>
                                            </q-btn>
                                        </template>
                                    </td>
                                    <td class="text-left text-bold" :class="record.type == 1 ? 'text-green' : 'text-red'">{{ record.type == 1 ? '+ ' : '- ' }}{{ addCommas(record.amount) }}</td>
                                    <td class="text-left">{{ addCommas(record.begin_balance) }}</td>
                                    <td class="text-left">{{ addCommas(record.end_balance) }}</td>
                                    <td class="text-left desktop-only">{{record.description}} </td>
                                    <td class="text-left">{{formatDateMoment(record.date)}}</td>
                                    <td class="text-left">
                                        <q-badge :color="record.type == 1 ? 'green' : 'red'"
                                            :label="record.type == 1 ? 'Cộng tiền' : 'Trừ tiền'"></q-badge>
                                    </td>
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
            

         


            
            
        </template>
</div>
`;

import { getBalanceHistory } from "../api/history.js"
import { LANG_DATE_PICKER, sevenDaysAgoMoment } from "../constants/constants.js"
const { RV_CONFIGS } = window 
export default {
    data: () => ({
        configs: RV_CONFIGS,
        isLoading: false,
        filters: {
            date_range: []
        },
        lang: LANG_DATE_PICKER,
        records: [],
        pagination: {
            page: 1,
            max: 1,
            per_page: 15,
            total: 0,
        },
      
    }),
   
    methods: {
        async getData(){
             this.isLoading = true;
            const res = await getBalanceHistory({filters: this.filters,  page: this.pagination.page, per_page: this.pagination.per_page})
            const {data, pagination} = res.data
            if(data)
                this.records = data
            this.pagination.total = pagination.total
            this.pagination.max = pagination.max_page
            this.isLoading = false
          
        }
	},
	components:{
	},
    watch:{
        
        'pagination.page': function(){
          this.getData()
        },
        'filters.date_range': function(){
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
        this.$eventBus.$emit('set.page_title', 'Lịch sử số dư');
    }

}