

const template = `
<div class="q-pa-md" v-if="!isLoading">
    <div class="row">
        <div class="col-md-6 col-xs-12 text-center card-item">
            <transition appear enter-active-class="animated zoomIn" :duration="1000">
                <div class="flex justify-center">
                    <!-- <img src="~assets/vbimex.jpg" width="150px" style="position:relative; left:-15px"> -->
                </div>
            </transition>
            <p class="text-h6 q-mb-md">THÔNG TIN TÀI KHOẢN</p>
            <q-form @submit="onSubmit" class="q-gutter-sm">

                <q-chip :color="LEVEL_COLOR[user.level]" icon="stars" text-color="white">{{settings.commission_user_levels?.[user.level].name}}</q-chip>
                <q-chip color="orange" text-color="white" icon="attach_money">Số dư: {{addCommas(user.balance)}} đ</q-chip>
                <q-chip color="purple" text-color="white" icon="shopping_cart">Doanh số: {{addCommas(user.income)}} đ</q-chip>

                <!-- <div>
                    <input id="image-upload" hidden="" type="file" name="image" onchange="document.getElementById('image-preview').src = window.URL.createObjectURL(this.files[0])" ref="avatar">
                    <div style="position: relative; display: inline-block;">
                        <img style="max-width: 200px" onclick="document.getElementById('image-upload').click()" id="image-preview" class="ui fluid rounded image" :src="user.avatar ? user.avatar : 'https://magiamgiashopee.vn/wp-content/uploads/2018/08/logoshopee.png'">
                        <div class="image-picker" onclick="document.getElementById('image-upload').click()">
                            <q-btn label="Đổi ảnh đại diện"  color="primary" icon="edit" size="sm" class="q-mb-md"/>
                        </div>
                    </div>
                </div> -->

                <q-input disable filled type="text" v-model="user.user_login" label="Tên đăng nhập *" lazy-rules
                    :rules="[val => (val && val.length > 0) || 'Điền tên tài khoản']" />
        
                <q-input  filled type="text" v-model="user.display_name" label="Tên hiển thị" lazy-rules
                    :rules="[val => (val && val.length > 0) || 'Điền tên hiển thị']" />

                
                <q-input  filled type="text" v-model="user.user_phone" label="Số điện thoại *" lazy-rules :rules="[
                        val => (val && val.length > 0) || 'Số điện thoại',
                        val => (val && validatePhone(val)) || 'Số điện thoại bạn nhập không chính xác' ]"/>
        
                <q-input  filled type="email" v-model="user.user_email" label="Email *" lazy-rules :rules="[
                                  val => (val && val.length > 0) || 'Điền tên email',
                                  val =>
                                    (val && validateEmail(val)) || 'Định dạng Email không chính xác'
                                ]" />
        
        
        
                <div class="q-gutter-md">
                    <q-btn label="Lưu thông tin" type="submit" color="primary" />
                    <q-btn label="Đổi mật khẩu" color="red" @click="change_pass.show_dialog = true"/>
                   
                </div>
            </q-form>
            <q-dialog v-model="change_pass.show_dialog">
                <q-card style="min-width: 350px;">
                    <q-card-section class="row items-center q-pb-none">
                        <div class="text-h6">Đổi mật khẩu</div>
                        <q-space />
                        <q-btn icon="close" flat round dense v-close-popup />
                    </q-card-section>
            
                    <q-card-section class="q-gutter-md">
                            <q-input filled type="password" v-model="change_pass.old_password" label="Mật khẩu cũ *"/>
                            <q-input filled type="password" v-model="change_pass.new_password" label="Đổi mật khẩu mới *"/>
                            <q-input filled type="password" v-model="change_pass.renew_password" label="Nhập lại mật khẩu *"  />

                            <q-btn label="Đổi mật khẩu" color="primary" @click="changePass"/>

                    </q-card-section>
                    </q-card>
            </q-dialog>
        </div>
    </div>
</div>
`;
import { updateUserProfile, getUserProfile, changePassword } from '../api/user.js'
import {  LEVEL_COLOR, validateEmail, validatePhone } from "../constants/constants.js"
const { RV_CONFIGS } = window 
export default {
    data: () => ({
        configs: RV_CONFIGS,
        isLoading: true,
        settings: {},
        validateEmail,
        validatePhone,
        user: {
            user_login: "",
            display_name: "",
            user_phone: "",
            user_email: "",
            password: "",
            password_confirmation: "",
            ref: '',
            balance: 0,
            income: 0,
            data_json: ''
        },
        LEVEL_COLOR,
        change_pass: {
            show_dialog: false,
            old_password: '',
            new_password: '',
            renew_password: '',
        },

    }),
   
    methods: {
        getBase64(file) {
            return new Promise((res, rej) => {
                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function () {
                  res(reader.result);
                };
                reader.onerror = function (error) {
                  rej('Error: ', error);
                };
            })
           
        },
        async onSubmit() {
            this.$q.loading.show()
            // if(this.$refs.avatar.files.length)
            //     this.user.avatar_base64 = await this.getBase64(this.$refs.avatar.files[0])

            updateUserProfile(this.user).then(res => {
                const { success, msg } = res.data
                this.NOTIFY(msg, success)
                this.$q.loading.hide()
            })
        },
        async getData(){
            this.$q.loading.show()
            const res = await getUserProfile(this.configs.user_id)
            const {data} = res.data
            this.user = data
            this.$q.loading.hide()
            this.isLoading = false
        },
        async changePass(){
            const {old_password, new_password, renew_password} = this.change_pass
            if(old_password == '' || new_password == '' || renew_password == '')
                return this.NOTIFY('Yêu cầu điền đầy đủ thông tin mật khẩu', false)

            this.$q.loading.show()
            const res = await changePassword({...this.change_pass})
            const { success, msg } = res.data
            this.NOTIFY(msg, success)

            console.log(res)
            this.$q.loading.hide()
        }
	},
	components:{
       
	},
    watch:{

    },
    template: template,
    created(){
        this.getConfigs().then(res => {
            this.settings = res
        })
        this.getData();
        this.$eventBus.$emit('set.page_title', 'Thông tin tài khoản');
    }

}