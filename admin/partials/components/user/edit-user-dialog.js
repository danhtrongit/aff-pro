const template = `
    <q-dialog
       v-model="edit_user.showDialog"
       persistent transition-show="fade-in" transition-hide="fade-out" 
    >
      <q-card style="width: 600px; max-width: 80vw;">
        <q-card-section class="row items-center" style="background:#FDA524; color:#fff">
          <div class="text-h6">Cập nhật tài khoản: {{user.user_login}}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>


        <q-card-section class="" v-if="user.user_login">
            <!-- {{order}} -->
            <div class="row q-col-gutter-md">
                <div class="col-12">
                    <q-input filled v-model="user.user_login" label="Tài khoản" stack-label class="q-mb-md" disable/>
                    <q-input filled v-model="user.user_email" label="Email" stack-label class="q-mb-md"/>
                    <q-input filled v-model="user.display_name" label="Tên hiển thị" stack-label class="q-mb-md"/>
                    <q-input filled v-model="user.user_phone" label="Điện thoại" stack-label class="q-mb-md"/>
                    <q-input filled type="number" v-model="user.commission_percent" label="% chiết khấu" stack-label class="q-mb-md"/>
                    <q-input filled type="number" v-model="user.balance" label="Số dư" stack-label class="q-mb-md"/>
                    <q-input filled type="text" v-model="user.description_balance_change" label="Lý do thay đổi số dư (Nếu có)" stack-label class="q-mb-md"/>
                    <q-select filled v-model="user.level" :options="level_options" label="Cấp độ tài khoản" stack-label emit-value map-options class="q-mb-md" />
                    <q-select filled v-model="user.is_vip" :options="[{label: 'Không', value: '0'}, {label: 'Có', value: '1'}]" label="Tài khoản đặc biệt" stack-label emit-value map-options class="q-mb-md" />

                </div>

              
            </div>
        </q-card-section>

        <q-card-section align="right" class="bg-white text-teal">
          <q-btn color="orange" label="Cập nhật" @click="update"/>
        </q-card-section >
      </q-card>
    </q-dialog>
        
          
`;
import { getUser, updateUser } from '../../api/user.js'
import { DATE_PICKER_LOCALE_VN } from "../../constants/constants.js"
export default {
    props: ['edit_user'],
    data: () => ({
        settings: {},
        level_options: [],
        user: {
            user_login: '', 
        }
    }),
    methods: {
        async update(){
            this.$emit('update', this.user.ID, this.user, this.edit_user.index, true)
        },
        
    },
    computed: {
        
    },
    components:{
        
    },
    template: template,
    created(){
        this.getConfigs().then(res => { 
            this.settings = res 
            this.level_options = []
            
            res.commission_user_levels.forEach((el, i) => { 
                if(res.user_level > i)
                this.level_options.push({label: el.name, value: i.toString()})
            })
            
            this.user = {...this.edit_user.user}
        })
    },
    watch: {
        
    },


}