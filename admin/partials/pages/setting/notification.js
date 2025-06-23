const template = `
<div class="q-mt-lg">
    <div v-if="!isLoading">
        
        <div class="row q-col-gutter-md q-mb-lg">
          <div class="col-9">
            <navSettingComponent />
          </div>
          <div class="col-3">
              
          </div>
        </div>

        <div class="card-item">
        <div class="row q-col-gutter-md">
            <div class="col-6">
                <q-toggle  v-model="settings.aff_email" label="Bật tính năng Email (Có thể gây chậm quá trình xử lý, hãy sử dụng cùng với Mail Queue)"/> <br><br>
                
                <p>Thông báo khi Email khi tài khoản được kích hoạt AFF</p>
                <q-editor v-model="settings.noti_email_user_actived" :definitions="{
                        bold: {label: 'Bold', icon: null, tip: 'My bold tooltip'}
                      }" :toolbar="[['left', 'center', 'right', 'justify', 'hr', 'link', 'custom_btn'], ['bold', 'italic', 'strike', 'underline', 'subscript', 'superscript']]"/>
                <div class="p-small">Trong đó: [user_name] là tên của người dùng</div>
                
                </br>
                <p>Thông báo khi User được cộng hoa hồng cho đơn thành công</p>
                <q-editor v-model="settings.noti_email_order_completed" :definitions="{
                        bold: {label: 'Bold', icon: null, tip: 'My bold tooltip'}
                      }" :toolbar="[['left', 'center', 'right', 'justify', 'hr', 'link', 'custom_btn'], ['bold', 'italic', 'strike', 'underline', 'subscript', 'superscript']]"/>
                <div class="p-small">Trong đó: [user_name] là tên của người dùng, [order_id] là mã đơn hàng, [order_total] là giá trị đơn hàng,
                [commision] là hoa hồng từ đơn hàng</div>
                <br>
                <p>Thông báo Email khi User tạo lệnh thanh toán tới Admin</p>
                <q-editor v-model="settings.noti_email_creat_payment_request" :definitions="{
                        bold: {label: 'Bold', icon: null, tip: 'My bold tooltip'}
                      }" :toolbar="[['left', 'center', 'right', 'justify', 'hr', 'link', 'custom_btn'], ['bold', 'italic', 'strike', 'underline', 'subscript', 'superscript']]"/>
                <div class="p-small">Trong đó: [user_name] là tên của người dùng, [total] là số tiền thanh toán, [bank_information] là thông tin tài
                khoản ngân hàng</div>
                </br>
                <p>Thông báo Email khi Admin duyệt lệnh thanh toán tới User</p>
                <q-editor v-model="settings.noti_email_payment_request_completed" :definitions="{
                        bold: {label: 'Bold', icon: null, tip: 'My bold tooltip'}
                      }" :toolbar="[['left', 'center', 'right', 'justify', 'hr', 'link', 'custom_btn'], ['bold', 'italic', 'strike', 'underline', 'subscript', 'superscript']]"/>
                <div class="p-small">Trong đó: [user_name] là tên của người dùng, [total] là số tiền thanh toán, [bank_information] là thông tin tài
                khoản ngân hàng</div>

                  <q-btn color="primary"  icon="save" class="q-mb-lg q-mt-lg" label="Lưu" @click="save"/>
                  
              </div>

                
              <div class="col-6">
                <p>Dòng thông báo toàn trang</p>
                <q-editor v-model="settings.noti_all" :definitions="{
                        bold: {label: 'Bold', icon: null, tip: 'My bold tooltip'}
                      }" :toolbar="[['left', 'center', 'right', 'justify', 'hr', 'link', 'custom_btn'], ['bold', 'italic', 'strike', 'underline', 'subscript', 'superscript']]"/>
                <br>
                <p>Thông báo khi tài khoản chưa kích hoạt AFF</p>
                <q-editor v-model="settings.noti_not_active" :definitions="{
                        bold: {label: 'Bold', icon: null, tip: 'My bold tooltip'}
                      }" :toolbar="[['left', 'center', 'right', 'justify', 'hr', 'link', 'custom_btn'], ['bold', 'italic', 'strike', 'underline', 'subscript', 'superscript']]"/>

                </br>

                <p>Thông báo đơn hàng mới tới Cộng tác viên</p>
                <q-editor v-model="settings.noti_new_order" :definitions="{
                        bold: {label: 'Bold', icon: null, tip: 'My bold tooltip'}
                      }" :toolbar="[['left', 'center', 'right', 'justify', 'hr', 'link', 'custom_btn'], ['bold', 'italic', 'strike', 'underline', 'subscript', 'superscript']]"/>
                <div class="p-small">Trong đó: [user_name] là tên của người dùng, [order_id] là ID đơn hàng, [order_total] là tổng giá trị đơn hàng, [commission] là hoa hồng sẽ nhận được</div>

                </br>

               
            </div>  
            
            </div>
        </div>  
    </div>
</div>   
`;
import { setConfigs, getConfigs } from "../../api/config.js";
import navSettingComponent from "../../components/nav-setting-component.js";
const { RV_CONFIGS } = window;
const orderStatusOptions = [
  { label: "Chờ thanh toán", value: "pending" },
  { label: "Đang xử lý", value: "processing" },
  { label: "Tạm giữ", value: "on-hold" },
  { label: "Đã hoàn thành", value: "completed" },
];
export default {
  data: () => ({
    isLoading: false,
    settings: {
      aff_email: false,
      notification: {
        general: '',
        not_active: '',
      },
      noti_all: '',
      noti_general: '',
      noti_not_active: '',
      noti_email_user_actived: '',
      noti_email_order_completed: '',
      noti_email_creat_payment_request: '',
      noti_email_payment_request_completed: '',
      noti_new_order: ''
    },
    orderStatusOptions,
  }),

  methods: {
    async save() {
      this.$q.loading.show();
      const res = await setConfigs(this.settings);
      console.log(res);
      const { success, msg, data } = res.data;
      this.NOTIFY(msg, success);
      if (success) window.byc_settings = data;
      this.$q.loading.hide();
    },
    coppyShortcode() {

    },
    resetAllData() {

    },
  },
  components: {
    navSettingComponent,
  },
  template: template,
  created() {
    getConfigs().then((res) => {
      this.settings = res.data.data;
    });
    this.$eventBus.$emit("set.page_title", "Cài đặt");
  },
  destroyed() { },
};
