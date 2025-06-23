

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
        <p class="text-h6 q-mb-xl">TẠO TÀI KHOẢN</p>
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

            <q-input filled type="text" v-model="user.ref" label="Người giới thiệu"/>

          <div>
            <q-btn label="Đăng ký" type="submit" color="primary" />
           
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
            let account = { username: this.username, password: this.password };
               
            this.$q.loading.show()
            register(this.user).then(res => {
                const { success, msg, data } = res.data
                this.NOTIFY(msg, success)
                if(success){
                    this.user = {
                        user_login: "",
                        display_name: "",
                        user_phone: "",
                        user_email: "",
                        password: "",
                        password_confirmation: "",
                        ref: '',
                    }
                    this.$q.loading.hide()
                }
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
        
        this.$eventBus.$emit('set.page_title', 'Tạo tài khoản CTV');
    }

}