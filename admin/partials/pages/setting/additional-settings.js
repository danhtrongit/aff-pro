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
                    <p class="text-subtitle">Thưởng đăng kí tài khoản</p>
                    <date-picker v-model="settings.bonus_register_date_range" class="q-mb-md" type="date" range placeholder="Chọn khoảng ngày diễn ra" confirm format="DD-MM-YYYY" valueType="YYYY-MM-DD" :lang="lang"></date-picker>
                    <q-input filled type="number" mask="#.#" v-model="settings.bonus_register" label="Số tiền thưởng" stack-label class="q-mb-md" />
                </div>
                <q-btn color="primary"  icon="save" class="q-mb-lg q-mt-lg" label="Lưu" @click="save"/>
                
            </div>
            </div>  

            <div class="col-6">
                
            </div>
           

        </div>
    </div>
</div>   
`;
import { setConfigs, getConfigs } from "../../api/config.js";
import { LANG_DATE_PICKER, sevenDaysAgoMoment } from "../../constants/constants.js"

import navSettingComponent from "../../components/nav-setting-component.js";
const { RV_CONFIGS } = window;

export default {
  data: () => ({
    isLoading: false,
    lang: LANG_DATE_PICKER,
    settings: {
        bonus_register_date_range: [],
        bonus_register: 0,
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
  },
  components: {
    navSettingComponent,
  },
  template: template,
  created() {
    getConfigs().then((res) => {
      this.settings = res.data.data;
    });
    this.$eventBus.$emit("set.page_title", "Cài đặt khác");
  },
  destroyed() {},
};
