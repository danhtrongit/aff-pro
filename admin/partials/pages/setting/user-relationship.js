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
                    <p class="q-mb-md">Tầng đa cấp bạn mong muốn</p>
                    <p><q-slider v-model="settings.relationship_level" :min="0" :max="7" :step="1" label label-always /></p>
                    <p>Chế độ tính hoa hồng (% theo)</p>
                    
                    <div class="q-gutter-sm q-mb-md">
                      <q-radio v-model="settings.commission_relationship_mode" val="order" label="Tổng giá trị đơn hàng" />
                      <q-radio v-model="settings.commission_relationship_mode" val="commission" label="Hoa hồng của người giới thiệu nhận được" />
                    </div>

                    <q-toggle  v-model="settings.income_include_child" label="Ghi nhận doanh thu của cấp dưới cho cấp trên"/> <br><br>



                    <p>Thiết lập chiết khấu</p>
                    <div>
                        <div class="row q-col-gutter-sm q-mb-sm" v-for="(level, i) in settings.commission_relationship_levels" >
                            <template v-if="(i + 1) <= settings.relationship_level">
                                <div class="col-6">
                                    <q-input filled v-model="level.name" label="Cộng tác viên cấp"  stack-label readonly />
                                </div>
                                <div class="col-6">
                                    <q-input filled v-model="level.commission" label="Chiết khấu %" stack-label />
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <q-btn color="primary"  icon="save" class="q-mb-lg q-mt-lg" label="Lưu" @click="save"/>
                <q-btn color="primary"  icon="people" class="q-mb-lg q-mt-lg" label="Thiết lập quan hệ" to="/set-relationship"/>
                <q-btn color="pink"  icon="join_right" class="q-mb-lg q-mt-lg" label="Khởi tạo User Relationship" @click="initRelationship"/>
                
            </div>
            </div>  

            <div class="col-6">
                <q-banner class="bg-grey-3 q-py-lg">
                    <template v-slot:avatar>
                        <q-icon name="verified_user" color="primary" />
                    </template>
                    Giải thích: Cộng tác viên có thể mời các người bán khác đăng kí làm cộng tác viên cấp dưới của mình tối đa 7 cấp độ. <br><br>
                    <p>
                      <strong>Ví dụ:</strong> <br> 
                      - User A gửi link đăng kí cộng tác viên cho User B, sau khi đăng kí dưới link dưới thiệu thì B sẽ là cộng tác viên cấp 1 của A. <br>
                      - User B lại giới thiệu User C đăng kí dưới link giới thiệu của B, thì C sẽ là cấp 2 của A, và C sẽ là cấp 1 của B
                    </p>
                    <p>
                      <strong>Chế độ tính hoa hồng:</strong> <br>
                      - Tổng giá trị đơn hàng: Mức hoa hồng nhận được tính theo tổng giá trị đơn hàng <br>
                      - Hoa hồng của người giới thiệu nhận được: Mức hoa hồng nhận được tính theo % hoa hồng của người giới thiệu cấp cuối nhận được. (Ví dụ B là CTV cấp 1 của A, B giới thiệu 1 đơn hàng thành công và nhận được 20.000đ hoa hồng, lúc này % hoa hồng tính cho A lúc này sẽ bằng (% hoa hồng cấp 1 x 20.000đ / 100)) <br>
                    </p>
                    <template v-slot:action>
                        <q-btn flat color="primary" label="Thắc mắc vui lòng liên hệ mình" />
                    </template>
                </q-banner>
            </div>
           

        </div>
    </div>
</div>   
`;
import { setConfigs, getConfigs, initUserRelationship } from "../../api/config.js";
import navSettingComponent from "../../components/nav-setting-component.js";
const { RV_CONFIGS } = window;

export default {
  data: () => ({
    isLoading: false,
    settings: {
      commission_relationship_levels: [],
      relationship_level: 4,
      commission_relationship_mode: 'commission',
      income_include_child: true
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
    async initRelationship() {
      const res = await initUserRelationship()
      const { success, msg } = res.data
      this.NOTIFY(msg, success)

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
    this.$eventBus.$emit("set.page_title", "Cấu hình đa cấp");
  },
  destroyed() { },
};
