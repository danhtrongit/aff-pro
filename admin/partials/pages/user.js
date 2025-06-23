

const template = `
<div class="q-mt-lg">
    <loading-component v-if="isLoading" />
    
    <div v-else>
            <div class="row q-col-gutter-md">
                <div class="dash-item col-12">
                    <div class="card-item ">


                    <div class="row q-col-gutter-md q-mb-md">
                        <div class="col-6 flex" style="align-items:center">
                            <q-chip color="primary" text-color="white" icon="person" class="q-mb-md">
                                Tổng số tài khoản: {{pagination.total}}
                            </q-chip>
                    
                        </div>
                        <div class="col-2 text-right">
                            <!-- <q-btn  color="primary" icon="add" to="/register"/> -->
                            <q-select filled map-options v-model="filters.aff_active" :options="[{label: 'Tất cả', value:''},{label: 'Cộng tác viên', value:'1'}, {label: 'Tài khoản thường', value:'0'}]" label="Trạng thái" emit-value dense/>
                        </div>
                    
                        <div class="col-3">
                            <div class="mx-height-40">
                                <q-input filled v-model="filters.search" label="Nhập ID, tên tài khoản, email ..." dense debounce="500" />
                    
                            </div>
                        </div>
                        <div class="col-1">
                            <q-btn color="primary" label="Lọc" style="width: 100%" @click="getData" />
                        </div>
                    </div>
                    <hr>

                        <empty-component v-if="records.length == 0 && isLoading == false" />
                        <template v-else>    
                        <q-markup-table separator="horizontal" wrap-cells flat >
                            <thead>
                                <tr>
                                    <th class="text-left" width="50px">ID</th>
                                    <th class="text-left">Tài khoản</th>
                                    <th class="text-left">Tên hiển thị</th>
                                    <th class="text-left">Người giới thiệu</th>
                                    <th class="text-left">Email</th>
                                    <th class="text-left">Điện thoại</th>
                                    <th class="text-left">Số dư</th>
                                    <th class="text-left">Doanh thu</th>
                                    <th class="text-left">Cấp độ</th>
                                    <th class="text-center">% CK</th>
                                    <th class="text-left">Kích hoạt</th>
                                    <th class="text-left">#</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(record, i) in records" :key="record.ID">
                                    <td class="text-left">{{ record.ID }}</td>
                                    <td class="text-left">{{ record.user_login }}</td>
                                    <td class="text-left">{{ record.display_name }}</td>
                                    <td class="text-left">{{ record.parent_login }}</td>
                                    <td class="text-left">{{ record.user_email }}</td>
                                    <td class="text-left">{{ record.user_phone }}</td>
                                    <td class="text-left">{{addCommas(record.balance)}}</td>
                                    <td class="text-left">{{addCommas(record.income)}}</td>
                                    <td class="text-left">
                                        <q-badge v-if="settings.commission_user_levels?.[record.level]" :color="LEVEL_COLOR[record.level]" :label="settings.commission_user_levels?.[record.level].name"></q-badge>
                                    </td>
                                    <td class="text-center">{{record.commission_percent}}</td>
                                    <td class="text-left">
                                        
                                        <q-btn v-if="record.aff_active == 1" flat color="green" icon="check_circle_outline" @click="activeUser(record, 0, i)"/>
                                        <q-btn v-else flat color="red" icon="highlight_off" @click="activeUser(record, 1, i)"/>

                                    </td>
                                    <td class="text-left">
                                        <q-btn round color="pink" icon="timeline" size="sm" class="q-mr-xs" @click="viewUserStats2(record, i)"/>
                                        <q-btn round color="pink" icon="timeline" size="sm" class="q-mr-xs" @click="viewUserStats(record, i)"/>
                                        <q-btn round color="primary" icon="edit" class="q-mr-xs" size="sm" @click="editUser(record, i)"/>
                                        <q-btn round v-if="!isNormal()" color="purple" icon="groups" class="q-mr-xs" size="sm" @click="viewUserTree(record, i)" />

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
                        </template>
                    </div>
                </div>
             
            </div>
            

            <!-- Edit User Dialog Component -->
            <editUserDialog :edit_user="edit_user" @update="updateData" v-if="edit_user.showDialog"/>
            <viewUserStatsDialog :view_user_stats="view_user_stats"  v-if="view_user_stats.showDialog"/>
            <viewUserStats2Dialog :view_user_stats2="view_user_stats2"  v-if="view_user_stats2.showDialog"/>
            <viewUserTreeDialog :view_user_tree="view_user_tree"  v-if="view_user_tree.showDialog"/>


            
            
    </div>
</div>
`;


import { LANG_DATE_PICKER, sevenDaysAgoMoment, LEVEL_COLOR } from "../constants/constants.js"
import editUserDialog from "../components/user/edit-user-dialog.js"
import viewUserStatsDialog from "../components/user/user-stats-dialog.js"
import viewUserStats2Dialog from "../components/user/user-stats2-dialog.js"
import viewUserTreeDialog from "../components/user/user-tree-dialog.js"

import { getUsers, updateUser } from '../api/user.js'
const { RV_CONFIGS } = window 
export default {
    data: () => ({
        confis: RV_CONFIGS,
        isLoading: false,
        settings:{},
        lang: LANG_DATE_PICKER,
        LEVEL_COLOR,
        filters: {
            search: '',
            date_range: sevenDaysAgoMoment("array", "YYYY-MM-DD"),
            aff_active: ''
        },
        records: [],
        pagination: {
            page: 1,
            max: 1,
            per_page: 15,
            total: 0,
        },
        edit_user: {
            user: null,
            showDialog: false,
            index: null
        },
        view_user_stats: {
            user: null,
            showDialog: false,
        },
        view_user_stats2: {
            user: null,
            showDialog: false,
        },
        view_user_tree: {
            user: null,
            showDialog: false,
        }
    }),
   
    methods: {
        async getData(){
            this.$q.loading.show()
            const res = await getUsers({filters: this.filters, page: this.pagination.page, per_page: this.pagination.per_page})
            const {data, pagination} = res.data
            if(data)
                this.records = data
            this.pagination.total = pagination.total
            this.pagination.max = pagination.max_page
            this.$q.loading.hide()

        },
        async updateData(user, data, index, hide_dialog = ''){
            this.$q.loading.show()
            const res = await updateUser({ID: user.ID, ...data})
            const {msg, success} = res.data
            this.NOTIFY(msg, success)
            if(success && res.data.data)
                this.$set(this.records, index, res.data.data)
            this.$q.loading.hide()

            if(hide_dialog)
                this.edit_user.showDialog = false

        },
        async activeUser(user, status, index){
            const msg = `${status == 1 ? 'Kích hoạt tính năng cộng tác viên: ' : 'Tắt tính năng cộng tác viên: '}` + user.user_login
            const confirm = await this.CONFIRM(msg)
            if(confirm)
                this.updateData(user, {aff_active: status}, index)
        },
        editUser(user, index){
            this.edit_user.user = user
            this.edit_user.index = index
            this.edit_user.showDialog = true
        },
        viewUserStats(user, index){
            window.scrollTo(0, 0)
            this.view_user_stats.user = user
            this.view_user_stats.index = index
            this.view_user_stats.showDialog = true
        },
        viewUserStats2(user, index){
            window.scrollTo(0, 0)
            this.view_user_stats2.user = user
            this.view_user_stats2.index = index
            this.view_user_stats2.showDialog = true
        },
        viewUserTree(user, index){
            this.view_user_tree.user = user
            this.view_user_tree.index = index
            this.view_user_tree.showDialog = true
        },
	},
	components:{
        editUserDialog,
        viewUserStatsDialog,
        viewUserStats2Dialog,
        viewUserTreeDialog
	},
    watch:{
       'pagination.page': function(){
          this.getData()
      },
      'filters.search': function(){
        this.pagination.page = 1
        this.getData()
      },
      'filters.aff_active': function(){
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
        this.getConfigs().then(settings => {
            console.log(settings);
            this.settings = settings
            this.getData();
        });
        this.$eventBus.$emit('set.page_title', 'Cộng tác viên');
        
    }

}