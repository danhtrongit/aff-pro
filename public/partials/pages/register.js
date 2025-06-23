

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
        <p class="text-h6 q-mb-xl">ĐĂNG KÝ CỘNG TÁC VIÊN</p>
        <q-form @submit="onSubmit" class="q-gutter-sm">

            <q-input filled type="text" v-model="user.user_login" label="Tên đăng nhập *" lazy-rules
                :rules="[val => (val && val.length > 0) || 'Điền tên tài khoản', val => !hasWhiteSpace(val) || 'Tên đăng nhập không được chứa kí tự khoảng trắng']" />

            <q-input filled type="text" v-model="user.display_name" label="Tên hiển thị" lazy-rules
                :rules="[val => (val && val.length > 0) || 'Điền tên hiển thị']" />

            <q-input filled type="text" v-model="user.user_phone" label="Số điện thoại *" lazy-rules :rules="[
                        val => (val && val.length > 0) || 'Số điện thoại',
                        val => (val && validatePhone(val)) || 'Số điện thoại bạn nhập không chính xác' ]" />
        
            <q-input filled type="email" v-model="user.user_email" label="Email *" lazy-rules :rules="[
                          val => (val && val.length > 0) || 'Điền tên email',
                          val =>
                            (val && validateEmail(val)) || 'Định dạng Email không chính xác'
                        ]" />
        
            <q-input filled type="password" v-model="user.password" label="Mật khẩu *" lazy-rules :rules="[
                      val => (val !== null && val !== '') || 'Vui lòng điền mật khẩu'
                    ]" />


            <q-input filled type="password" v-model="user.password_confirmation" label="Nhập lại mật khẩu *" lazy-rules :rules="[
                          val => (val !== null && val !== '') || 'Nhập lại mật khẩu',
                          val =>
                            (val && val == user.password) || 'Mật khẩu bạn nhập không khớp'
                        ]" />

            <q-input filled type="text" v-model="user.ref" label="Người giới thiệu" class="hidden"/>

          <div>
            <q-btn label="Đăng ký" type="submit" color="primary" />
            <div class="q-mt-md">
              Bạn đã có tài khoản,
              <router-link to="/dang-nhap">đăng nhập tại đây</router-link>
            </div>
          </div>
        </q-form>
      </div>
    </div>
</div>
`;
import { register } from '../api/user.js'
import { validateEmail, validatePhone, hasWhiteSpace } from "../constants/constants.js";

const { RV_CONFIGS } = window 
export default {
    data: () => ({
        configs: RV_CONFIGS,
        isLoading: false,
        isNormal: false,
        settings: {},
        user: {
            user_login: "",
            display_name: "",
            user_phone: "",
            user_email: "",
            password: "",
            password_confirmation: "",
            ref: '',
        },
        
    }),
   
    methods: {
        validateEmail,
        validatePhone,
        hasWhiteSpace,
       onSubmit() {
        // let account = { username: this.username, password: this.password, grant_type: 'password', scope: 'all', client_id: 'dac266d0-080a-11ea-91a9-297331205358', remember_me: true }
        // this.isLoading = true;
            let account = { username: this.username, password: this.password };
               
            this.$q.loading.show()
            register(this.user).then(res => {
                const { success, msg, data } = res.data
                this.NOTIFY(msg, success)
                if(success){
                    
                    this.$q.loading.hide()
                    if(!this.configs.user_login)
                      this.$router.push('/dang-nhap')
                }
                else
                    this.$q.loading.hide()
            })
        },
        
	},
	components:{
       
	},
    watch:{

    },
    template: template,
    created(){
       
        // Điền Ref tuyển CTV từ Ref link mua hàng
        // if(Quasar.plugins.Cookies.get(this.configs.ref_name))
        //      this.user.ref = Quasar.plugins.Cookies.get(this.configs.ref_name).mhref

        // Lưu ref tuyển CTV thành link Ref mua hàng
        // if(this.$route.query[this.configs.ref_name]){
        //     this.user.ref = this.$route.query[this.configs.ref_name]
        //     Quasar.Cookies.set(this.configs.ref_name, JSON.stringify({
        //         mhref: this.$route.query[this.configs.ref_name],
        //         mhproduct: 0,
        //         mhpath: this.configs.site_url,
        //         mhcoupon: ''
        //     }), { expires: 30, path: '/' })
        // }
        
        if(this.configs.user_login)
            this.user.ref = this.configs.user_login

        if(this.$route.query[this.configs.ref_name])
            this.user.ref = this.$route.query[this.configs.ref_name]
            
        
        this.$eventBus.$emit('set.page_title', 'Đăng ký thành viên');
    }

}