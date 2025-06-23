const template = `
    <template>
    <q-dialog
       v-model="view_user_tree.showDialog"
       persistent transition-show="slide-up"
        transition-hide="slide-down" maximized
        
    >
      <q-card class="scroll-view-user">
        <q-card-section class="row items-center bg-warning" style=" color:#fff">
          <div class="text-h6">Cộng tác viên cấp dưới: {{user.user_login}}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>


        <q-card-section class="" v-if="user.user_login">
            <q-option-group v-model="panel" inline :options="[
                      { label: 'Hiển thị dạng bảng', value: 'table' },
                      { label: 'Dạng cây phân cấp', value: 'tree' },
            ]" />
            
            <q-tab-panels v-model="panel" animated class="" keep-alive>
                <q-tab-panel name="table">
                    <q-markup-table separator="horizontal" wrap-cells flat>
                        <thead>
                            <tr>
                                <th class="text-left" width="50px">ID</th>
                                <th class="text-left">Tài khoản</th>
                                <th class="text-left">Tên hiển thị</th>
                                <th class="text-left">Email</th>
                                <th class="text-left">Số dư</th>
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
                                <td class="text-left">{{addCommas(record.balance)}}</td>
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
                    
                        <q-pagination v-model="pagination.page" :max="pagination.max" :max-pages="6" direction-links boundary-links
                            :disabled="isLoading"></q-pagination>
                    
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
                </q-tab-panel>
            
                <q-tab-panel name="tree">
                    <q-tree :nodes="tree" default-expand-all node-key="label" ref="tree">
                        <template v-slot:default-header="prop">
                            <div class="row items-center">
                                <q-img v-if="prop.node?.avatar ?? prop.node?.avatar" :src="prop.node.avatar" width="30px" class="q-mr-sm" style="border-radius: 50%;"/>
                                <div class="">{{ prop.node.label }} (ID: {{prop.node.ID}})</div>
                                <q-badge class="q-ml-md" outline :color="LEVEL_COLOR[prop.node.distance - 1]" v-if="parseInt(prop.node.distance) > 0" :label="'Cấp ' + prop.node.distance" />
                                <q-btn round color="pink" icon="light_mode" flat size="xs" class="q-ml-md" @click="viewUserStats(record, 1)"/>
                            </div>
                        </template>
                    </q-tree>
                    
                </q-tab-panel>
            
              
            </q-tab-panels>
            

        </q-card-section>

      </q-card>
      
      <viewUserStatsDialog :view_user_stats="view_user_stats"  v-if="view_user_stats.showDialog"/>
      </q-dialog>
    </template>
          
`;

import viewUserStatsDialog from "../../components/user/user-stats-dialog.js"

import { getUserTree, getUserTree2 } from '../../api/user.js'
import { LEVEL_COLOR } from "../../constants/constants.js"
export default {
    props: ['view_user_tree'],
    data: () => ({
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
        async getData() {
            this.isLoading = true
            const res = await getUserTree({ filters: this.filters, page: this.pagination.page, per_page: this.pagination.per_page })
            const { data, pagination } = res.data
            if (data)
                this.records = data
            this.pagination.total = pagination.total
            this.pagination.max = pagination.max_page
            this.isLoading = false
        },
        async getData2() {
            this.$q.loading.show()
            const res = await getUserTree2({ filters: this.filters })
            const { data, pagination } = res.data
            data.forEach(el => {
                el.avatar = 'https://cdn.quasar.dev/img/boy-avatar.png'
            })
            if (data)
                this.tree = data
            setTimeout(() => {
                this.$refs.tree.expandAll()
            }, 500)
            this.$q.loading.hide()

        },
        viewUserStats(user, index) {
            console.log(user)
            this.view_user_stats.user = user
            this.view_user_stats.showDialog = true
        },

    },
    computed: {

    },
    components: {
        viewUserStatsDialog
    },
    watch: {
        panel: function (val) {
            if (val == 'tree' && this.tree.length == 0) {
                this.getData2()
            }

        },
        'pagination.page': function () {
            this.getData()
        },
        'pagination.per_page': function () {
            this.pagination.page = 1
            this.getData()
        },
    },
    template: template,
    created() {
        this.getConfigs().then(res => {
            this.settings = res
            this.level_options = []


            this.filters.user_id = this.view_user_tree.user.ID
            this.user = { ...this.view_user_tree.user }
            this.getData();
            // this.getData2();
        })
    },



}