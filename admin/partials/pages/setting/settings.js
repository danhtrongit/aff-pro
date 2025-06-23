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

        <div class="row q-col-gutter-md">
            <div class="col-7">
              <div class="card-item">

                  <div class="row q-col-gutter-md">
                    <div class="col-6">
                      <q-select filled v-model="settings.aff_mode" :options="[{label: 'Tính hoa hồng theo tổng giá trị đơn hàng', value: 'order_mode'}, {label: 'Tính hoa hồng theo sản phẩm và danh mục', value: 'product_mode'}]" label="Chế độ tính hoa hồng" stack-label emit-value map-options class="q-mb-md" />
                    </div>
                    <div class="col-6">
                        <q-input filled type="number" mask="#.#" v-model="settings.commission_percent_default" label="Chiết khấu mặc định (%)" stack-label class="q-mb-md" />
                    </div>                    
                  </div>

                  <div class="row q-col-gutter-md">
                    <div class="col-6">
                        <q-input filled type="number" v-model="settings.aff_cookie_time" label="Thời gian hiệu lực Cookie" stack-label class="q-mb-md" />
                    </div>
                    <div class="col-6">
                        <q-input filled type="number" v-model="settings.aff_min_request" label="Số tiền tối thiểu khi rút tiền" stack-label class="q-mb-md" />
                    </div>                    
                  </div>

                  <div class="row q-col-gutter-md">
                    <div class="col-6">
                        <q-input filled type="text" v-model="settings.aff_user_page" label="URL trang cộng tác viên" stack-label class="q-mb-md" />
                    </div>
                    <div class="col-6">
                        <q-input filled type="text" v-model="settings.ref_name" label="Thay đổi tham số ?ref= trên URL bẳng từ khóa khác" stack-label class="q-mb-md" />
                    </div>                    
                  </div>
                  
                  
                  <div class="row q-col-gutter-md">
                    <div class="col-6">
                       <span v-if="settings.logo">Logo: </span><img style="width: 50px" :src="settings.logo" v-if="settings.logo" @click="uploadImg('logo')"
                         class="cursor-pointer" />
                       <q-btn outline color="pink" label="Logo" icon="upload" v-else @click="uploadImg('logo')" />
                     </div>

                     <div class="col-6">
                       <span v-if="settings.favicon">Favicon: </span> <img style="width: 50px" :src="settings.favicon" v-if="settings.favicon" @click="uploadImg('favicon')"
                         class="cursor-pointer" />
                       <q-btn outline color="pink" label="Favicon" icon="upload" v-else @click="uploadImg('favicon')" />
                     </div>
                  </div>


                  <q-toggle v-if="settings.aff_mode == 'order_mode'" v-model="settings.aff_commission_include_order_shipping"
                    label="Loại trừ phí ship và thuế ra giá trị đơn hàng khi tính hoa hồng" /><br>
                  <q-toggle label="Tự động kích hoạt Affiliate cho tài khoản mới" v-model="settings.aff_auto_active" /><br>
                  <q-toggle label="Tự động xóa Cookie sau khi khách đặt hàng" v-model="settings.aff_cookie_once" /><br>
                  <q-toggle label="Tính lượt truy cập dựa trên cookie" v-model="settings.cookie_traffic_mode" /><br>
                  <q-toggle label="Tự động trừ hoa hồng của người giới thiệu cho đơn hoàn, đơn hủy" v-model="settings.aff_refund_commission" /><br>
                  <q-toggle label="Cho phép CTV tự lên đơn hàng" v-model="settings.allow_order_self" /><br>
                  <q-toggle label="Sử dụng ref=ID tài khoản thay cho ref=username" v-model="settings.ref_value_is_id" /><br>

                  <q-btn color="primary"  icon="save" class="q-mb-lg q-mt-lg" label="Lưu" @click="save"/>
                  <q-btn color="pink"  icon="delete" class="q-mb-lg q-mt-lg" label="Xóa dữ liệu" @click="resetAllData"/>
                  
              </div>

                
            </div>  
            <div class="col-5">
                <q-banner class="bg-grey-3 q-py-md">
                  <template v-slot:avatar>
                    <q-icon name="verified_user" color="primary" />
                  </template>
                   Có 2 chế độ tính hoa hồng là <br><br>
                  <b>- Tính hoa hồng dựa trên tổng giá trị đơn hàng</b>: chế độ này nếu bạn muốn loại bỏ phí ship và thuế ra khỏi giá trị đơn hàng trước khi tính hoa hồng thì vui lòng bật chế độ <b>Loại trừ phí ship và thuế ra giá trị đơn hàng khi tính hoa hồng</b>
<br><br>
                  
                  <b>- Tính hoa hồng theo từng sản phẩm, danh mục riêng:</b> khi chọn chế độ này mặc định % chiết khấu toàn bộ sản phẩm sẽ bằng % chiết khấu mặc định, trong trường hợp bạn muốn thiết lập % chiết khấu riêng cho từng loại sản phẩm hay từng lọai danh mục thì hãy vào phần Cài đặt chiết khấu ở trên Menu chính để thiết lập riêng.<br>

                  <hr><br>

                  <b>Thời gian hiệu lực Cookie:</b> sau khi khách hàng truy cập vào Website bằng Link Affiliate sẽ được lưu Cookie, trong khoảng thời gian Cookie có hiệu lực thì khi khách hàng mua hàng, hệ thống sẽ tự động ghi nhận đơn hàng cho Cộng tác viên giới thiệu.<br><br>

                  <b>Tự động xóa Cookie sau khi khách đặt hàng:</b> khi bật chế độ này thì cứ sau mỗi 1 lần khách mua hàng thành công thì Cookie sẽ tự động được xóa đi, vậy nên thông tin người giới thiệu cũng sẽ bị xóa theo.<br><br>

                  <b>Tính lượt truy cập dựa trên Cookie:</b> mặc định hệ thống sẽ chỉ ghi nhận lượt xem trang khi trên URL có chứa biến <b>?ref=user</b>. Khi bật chế độ này thì sẽ ghi nhận lượt truy cập dựa vào Cookie, nếu tồn tại Cookie dù không có biến <b>?ref=user</b> trên URL hệ thống vẫn sẽ ghi nhận lượt xem trang của người dùng<br><br>
                  
                </q-banner>
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
      aff_auto_active: false,
      note_on_bill: "",
      logo: '',
      favicon: '',
      commission_percent_default: null,
      aff_mode: null,
      aff_commission_include_order_shipping: false,
      aff_cookie_once: false,
      aff_cookie_time: 1,
      aff_min_request: 0,
      cookie_traffic_mode: true,
      aff_user_page: '',
      aff_refund_commission: false,
      allow_order_self: false,
      ref_name: 'ref',
      ref_value_is_id: false
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
    uploadImg(field) {
      const file_frame = wp.media.frames.file_frame = wp.media({ title: 'Upload', library: { type: 'image' }, button: { text: 'Lựa chọn' }, multiple: false });

      file_frame.on('select', () => {

        const attachment = file_frame.state().get('selection').first().toJSON();

        this.settings[field] = attachment.url

      });
      file_frame.open();
    },
    resetAllData() {
      this.$q
        .dialog({
          dark: true,
          title: "Xóa toàn bộ dữ liệu",
          message: "Dữ liệu của Plugin sẽ bị xóa. Gõ vuacode-aff để tiếp tục chú ý toàn bộ số dư và doanh thu của cộng tác viên cũng sẽ bị xóa theo",
          prompt: {
            model: "",
            type: "text", // optional
          },
          cancel: true,
          persistent: true,
        })
        .onOk((data) => {
          if (data == "vuacode-aff") {
            axios
              .post(
                RV_CONFIGS.ajax_url,
                this.jsonToFormData({ action: "aff_remove_all_data" })
              )
              .then((res) => {
                const { success, msg } = res.data;
                this.NOTIFY(msg, success);
              });
          }
        });
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
