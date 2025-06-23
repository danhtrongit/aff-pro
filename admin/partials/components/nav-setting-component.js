const template = `

        <q-breadcrumbs v-if="Object.keys(settings).length">
                  <q-breadcrumbs-el to="/settings" label="Cài đặt chung" icon="home"/>
                  <q-breadcrumbs-el to="/settings/user-level" label="Cấp độ tài khoản" icon="widgets" />
                  <q-breadcrumbs-el v-if="!isNormal()" to="/settings/user-relationship" label="Cấu hình đa cấp" icon="widgets" />
                  <q-breadcrumbs-el to="/settings/notification" label="Thông báo & Email" icon="widgets" />
                  <q-breadcrumbs-el to="/settings/banner" label="Banner cộng tác viên" icon="widgets" />
                  <!-- <q-breadcrumbs-el to="/settings/additional" label="Cài đặt khác" icon="widgets" /> -->
                  <q-breadcrumbs-el label="" />
        </q-breadcrumbs>
          
`;
import {  getConfigs } from "../api/config.js";
export default {
    data: () => ({
        settings: {}
    }),
   
    methods: {

    },
    components:{
        
    },
    template: template,
    created(){
       getConfigs().then(res => {
            this.settings = res.data.data
        }) 
       
    },

}