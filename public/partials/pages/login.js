

const template = `
<div class="q-pa-md">
    <div class="row   flex flex-center">
      <div class="col-md-5 col-xs-12 text-center card-item">
        <transition
          appear
          enter-active-class="animated zoomIn"
          :duration="1000"
        >
          <div class="flex justify-center">
            <!-- <img src="~assets/vbimex.jpg" width="150px" style="position:relative; left:-15px"> -->
          </div>
        </transition>
        <p class="text-h6 q-mb-xl">ĐĂNG NHẬP</p>
        <q-form @submit="onSubmit" class="q-gutter-md">
          <q-input
            filled
            type="text"
            v-model="username"
            label="Tên đăng nhập *"
            lazy-rules
            :rules="[val => (val && val.length > 0) || 'Điền tên tài khoản']"
          />

          <q-input
            filled
            type="password"
            v-model="password"
            label="Mật khẩu *"
            lazy-rules
            :rules="[
              val => (val !== null && val !== '') || 'Vui lòng điền mật khẩu'
            ]"
          />

          <div class="q-mt-md">
            <q-btn label="Đăng nhập" type="submit" color="primary" />
            <q-btn label="Quên mật khẩu" type="submit" color="red" @click="lostpassword = true"/>
            <div class="q-mt-md">
              Bạn chưa có tài khoản,
              <router-link to="/dang-ky">đăng ký tại đây</router-link>
            </div>
          </div>

          <q-dialog v-model="lostpassword">
            <q-card style="min-width: 350px">
              <q-card-section>
                <div class="text-h6">Bạn quên mật khẩu</div>
              </q-card-section>
          
              <q-card-section class="q-pt-none">
                  <p>Vui lòng nhập Email đăng ký tài khoản</p>
                  <q-input filled type="text"  v-model="email_lostpassword"/>
              </q-card-section>
          
              <q-card-actions align="right">
                <q-btn  label="Lấy lại mật khẩu" color="red" @click="lostPassword"/>
              </q-card-actions>
            </q-card>
          </q-dialog>


        </q-form>
      </div>
    </div>
</div>
`;
import { login, lost_password } from '../api/user.js'
import { validateEmail } from "../constants/constants.js";

const { RV_CONFIGS } = window 
export default {
    data: () => ({
        configs: RV_CONFIGS,
        isLoading: false,
        username: "",
        password: "",
        lostpassword: false,
        email_lostpassword: ''
    }),
   
    methods: {
       onSubmit() {
        // let account = { username: this.username, password: this.password, grant_type: 'password', scope: 'all', client_id: 'dac266d0-080a-11ea-91a9-297331205358', remember_me: true }
        // this.isLoading = true;
            let account = { username: this.username, password: this.password };
            this.$q.loading.show()
            login(account).then(res => {
                const { success, msg, data } = res.data
                this.NOTIFY(msg, success)
                if(success){
                    window.RV_CONFIGS.user_id = data.data.ID
                    window.RV_CONFIGS.user_login = data.data.user_login
                    // console.log(window.RV_CONFIGS);
                    this.$eventBus.$emit('user.login')
                    this.$q.loading.hide()
                    this.$router.push('/')
                    window.location.reload()
                }
                else
                    this.$q.loading.hide()
            })
        },
        async lostPassword(){
           if(!validateEmail(this.email_lostpassword))
              return this.NOTIFY('Định dạng Email không chính xác', 0)
            this.$q.loading.show()
            const res = await lost_password({user_email: this.email_lostpassword});
            const { success, msg } = res.data
            this.NOTIFY(msg, success)
            if(success)
              this.lostpassword = false;
              
            this.$q.loading.hide()

        }
  },
  components:{
       
  },
    watch:{

    },
    template: template,
    created(){
        if(this.configs.user_login)
          this.$router.push('/')
        this.$eventBus.$emit('set.page_title', 'Đăng nhập tài khoản');
    }

}