const template = `
    <q-dialog
       v-model="view_user_stats.showDialog"
       persistent transition-show="slide-up"
        transition-hide="slide-down" maximized
        class="view-user-stats-dialog"
    >
      <q-card class="scroll-view-user">
        <q-card-section class="row items-center" style="background:#FDA524; color:#fff">
          <div class="text-h6">Thống kê tài khoản: {{user.user_login}}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>


        <q-card-section class="" v-if="user.user_login">
            <userDashboard :user_id="user.ID"/>
        </q-card-section>

      </q-card>
    </q-dialog>
        
          
`;
import userDashboard from "../../pages/dashboard.js"
export default {
    props: ['view_user_stats'],
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
        userDashboard
    },
    template: template,
    created(){
        this.getConfigs().then(res => { 
            this.settings = res 
            this.level_options = []
            
            
            
            this.user = {...this.view_user_stats.user}
        })
    },
    watch: {
        
    },


}