const template = `
    <div class="q-pa-md">
        <loading-component v-if="isLoading" />
        <empty-component v-else-if="records.length == 0 && isLoading == false" />
        <template v-else>
            <div :class="!$q.platform.is.mobile ? 'card-item' : ''">
                <div class="row q-col-gutter-md">
                    <div class="col-12 col-md-8">
                        <q-option-group v-model="panel" inline :options="[
                                                  { label: 'Hiển thị dạng bảng', value: 'table' },
                                                  { label: 'Dạng cây phân cấp', value: 'tree' },
                                        ]" />
                    </div>
                    <div class="desktop-only col-md-4">
                        <q-input filled v-model="filters.search" label="Nhập tên đăng nhập, email hoặc ID tài khoản" dense debounce="600"/>
                    </div>
                </div>
                
            <q-tab-panels v-model="panel" animated class="">
            <!-- <q-tab-panels v-model="panel" animated class="" keep-alive> -->
                <q-tab-panel name="table" :class="$q.platform.is.mobile ? 'q-pa-none q-mt-xs' : ''">
                    <q-markup-table separator="horizontal" :bordered="$q.platform.is.mobile ? true : false" :wrap-cells="$q.platform.is.mobile ? false : true" flat>
                        <thead>
                            <tr>
                                <th class="text-left" width="50px">ID</th>
                                <th class="text-left">Tài khoản</th>
                                <th class="text-left">Tên hiển thị</th>
                                <th class="text-left">Email</th>
                                <th class="text-left">Doanh thu</th>
                                <th class="text-left">Cấp độ tài khoản</th>
                                <th class="text-left">Cộng tác viên cấp</th>
                                <th class="text-left">Kích hoạt</th>
                                <th class="text-left">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(record, i) in records" :key="record.ID">
                                <td class="text-left">{{ record.ID }}</td>
                                <td class="text-left">{{ record.user_login }}</td>
                                <td class="text-left">{{ record.display_name }}</td>
                                <td class="text-left">{{ record.user_email }}</td>
                                <td class="text-left">{{addCommas(record.income)}}</td>
                                <td class="text-left">
                                    <q-badge v-if="settings.commission_user_levels?.[record.level]" :color="LEVEL_COLOR[record.level]"
                                        :label="settings.commission_user_levels?.[record.level].name"></q-badge>
                                </td>
                                <td>
                                    <q-badge :color="LEVEL_COLOR[record.distance - 1]">Cấp {{record.distance}}</q-badge>
                                </td>
                                <td class="text-left">
                    
                                    <q-btn v-if="record.aff_active == 1" flat color="green" icon="check_circle_outline"/>
                                    <q-btn v-else flat color="red" icon="highlight_off"/>
                    
                                </td>
                                <td class="text-left">
                                    <q-btn round color="pink" icon="timeline" size="sm" class="q-mr-xs" @click="viewUserStats(record, 1)" />
                    
                                </td>
                            </tr>
                    
                        </tbody>
                    </q-markup-table>
                    <div class="flex flex-center q-mt-lg">
                    
                        <q-pagination v-model="pagination.page" :max="pagination.max" :max-pages="6" direction-links boundary-links :disabled="isLoading"></q-pagination>

                    
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
                </q-tab-panel>
            
                <q-tab-panel name="tree">
                    <q-tree :nodes="tree" default-expand-all node-key="label" ref="tree">
                        <template v-slot:default-header="prop">
                            <div class="row items-center">
                                <q-img v-if="prop.node?.avatar ?? prop.node?.avatar" :src="prop.node.avatar" width="30px" class="q-mr-sm"
                                    style="border-radius: 50%;" />
                                <div class="">{{ prop.node.label }} </div>
                                <q-badge class="q-ml-md" outline :color="LEVEL_COLOR[prop.node.distance - 1]"
                                    v-if="parseInt(prop.node.distance) > 0" :label="'Cấp ' + prop.node.distance" />
                                <q-btn round color="pink" icon="light_mode" flat size="xs" class="q-ml-md"  @click="viewUserStats(prop.node, 1)"/>
                            </div>
                        </template>
                    </q-tree>
                    
                </q-tab-panel>
            
              
            </q-tab-panels>
            </div>


      </template>
      <viewUserStatsDialog :view_user_stats="view_user_stats"  v-if="view_user_stats.showDialog"/>
      </div>
    </div>
    
          
`;

import viewUserStatsDialog from "../components/user/user-stats-dialog.js"

import { getUserTree, getUserTree2 } from '../api/user.js'
import { DATE_PICKER_LOCALE_VN, LEVEL_COLOR } from "../constants/constants.js"
const { RV_CONFIGS } = window

export default {
    data: () => ({
        configs: RV_CONFIGS,
        isLoading: false,
        settings: {},
        level_options: [],
        LEVEL_COLOR,
        user: {
            user_login: '', 
        },
        filters: {
            search: '',
            user_id: '',
        },
        records: [],
        pagination: {
            page: 1,
            max: 1,
            per_page: 15,
            total: 0,
        },
        tree: [],
        panel: 'table',
        view_user_stats: {
            user: null,
            showDialog: false,
        },

    }),
    methods: {
        async getData(){
            this.$q.loading.show()
            const res = await getUserTree({filters: this.filters, page: this.pagination.page, per_page: this.pagination.per_page})
            const {data, pagination} = res.data
            if(data)
                this.records = data
            this.pagination.total = pagination.total
            this.pagination.max = pagination.max_page
            this.$q.loading.hide()
        },
        async getData2(){
            this.$q.loading.show()
            const res = await getUserTree2({filters: this.filters})
            const {data, pagination} = res.data
            data.forEach(el => {
                el.avatar = 'https://cdn.quasar.dev/img/boy-avatar.png'
            })
            if(data)
            this.tree = data
            setTimeout(() => {
                this.$refs.tree.expandAll()
            }, 500)
            this.$q.loading.hide()

        },
        viewUserStats(user, index){
            this.view_user_stats.user = user
            this.view_user_stats.showDialog = true
        },
        viewUserStats2(user){
            console.log(user);
            // this.view_user_stats.user = user
            // this.view_user_stats.showDialog = true
        },
        
    },
    computed: {
        
    },
    components:{
        viewUserStatsDialog
    },
    watch:{
        panel: function(val){
            if(val == 'tree' && this.tree.length == 0){
                this.getData2()
            }
            
        },
        'filters.search': function(){
            this.pagination.page = 1
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
        this.getConfigs().then(res => { 
            this.settings = res 
            this.level_options = []
            
            
            if(this.configs.user_id)
                this.filters.user_id = this.configs.user_id
                
            //this.user = {...this.view_user_tree.user}
            this.getData();
            this.$eventBus.$emit('set.page_title', 'Cộng tác viên');

       
        })
    },



}