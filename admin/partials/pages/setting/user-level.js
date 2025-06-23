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
            <div class="col-6">
            <div class="card-item">

            
                <div>   
                    <p class="q-mb-md">Cấp độ tài khoản bạn mong muốn</p>
                    <p><q-slider v-model="settings.user_level" :min="0" :max="7" :step="1" label label-always /></p>
                    <p>Thiết lập chiết khấu</p>
                    <div>
                        <div class="row q-col-gutter-sm q-mb-sm" v-for="(level, i) in settings.commission_user_levels" >
                            <template v-if="(i + 1) <= settings.user_level">
                                <div class="col-4">
                                    <q-input filled v-model="level.name" label="Tên cấp độ"  stack-label />
                                </div>
                                <div class="col-4">
                                    <q-input filled v-model="level.income" label="Doanh thu"  stack-label />
                                </div>
                                <div class="col-4">
                                    <q-input filled v-model="level.commission" label="Chiết khấu %" stack-label />
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <q-toggle label="Reset doanh thu và cấp bậc khi hết tháng" v-model="settings.aff_reset_level_month" class="hidden"/><br>
                <q-btn color="primary"  icon="save" class="q-mb-lg q-mt-lg" label="Lưu" @click="save"/>
                
            </div>
            </div>  

            <div class="col-6">
                <q-banner class="bg-grey-3 q-py-md">
                    <template v-slot:avatar>
                        <q-icon name="verified_user" color="primary" />
                    </template>
                    Giải thích: khi người giới thiệu đạt được doanh thu tương ứng sẽ được tự động nâng cấp độ tài khoản. Lúc này cách tính hoa hồng sẽ bằng: <br><br>
                    <b>Hoa hồng nhận được</b> =  <b>% chiết khấu mặc định</b> + <b>% chiết khấu cấp độ tài khoản</b>
                   
                </q-banner>
            </div>
           

        </div>
    </div>
</div>   
`;
import { setConfigs, getConfigs } from "../../api/config.js";
import navSettingComponent from "../../components/nav-setting-component.js";
const { RV_CONFIGS } = window;

export default {
  data: () => ({
    isLoading: false,
    settings: {
      commission_user_levels: [],
      aff_reset_level_month: false,
      user_level: 4,
    },
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
    uploadLogo() {
      const file_frame = (wp.media.frames.file_frame = wp.media({
        title: "Upload ảnh Logo",
        library: { type: "image" },
        button: { text: "Lựa chọn" },
        multiple: false,
      }));

      file_frame.on("select", () => {
        const attachment = file_frame.state().get("selection").first().toJSON();
        this.settings.logo = attachment.url;
      });
      file_frame.open();
    },

    coppyShortcode() {
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
    this.$eventBus.$emit("set.page_title", "Cấp độ tài khoản");
  },
  destroyed() { },
};
