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

        <div class="">
            
            <div class="row q-col-gutter-md q-mb-md">
               
                <div class="col-12 col-md-4">
                    <div class="card-item">
                        <BannerAddNew @getBanners="getBanners" />
                    </div>
                </div>
                <div class="col-12 col-md-8" v-if="records.length">
                    <div class="card-item" v-for="record in records" :key="record.id">
                        <div class="relative-position">
                            <div class="banner-item flex">
                                <div class="banner-img" style="width: 200px">
                                    <q-img :src="record.url" />
                                </div>
                                <div class="banner-info q-ml-md">
                                    <p><b>Tên banner:</b> {{ record.name }}</p>
                                    <p><b>Kích thước:</b> {{ record.dimension }}</p>
                                    <p><b>Liên kết:</b> {{ record.link }}</p>
                                </div>
                                <div class="absolute-right">
                                    <q-btn color="red" label="Xóa" @click="removeBanner(record.id)" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
    </div>
</div>   
`;
import BannerAddNew from "../../components/banner/banner-add-new.js";
import { getBanners, removeBanner } from "../../api/banner.js";
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
    records: [],
    settings: {
     
    },
  }),

  methods: {
    async getBanners() {
      const res = await getBanners();
      const {data} = res.data
      this.records = data;
    },
    async removeBanner(id) {
      const confirm = await this.CONFIRM('Bạn chắc chắn muốn xóa')
      if(!confirm)
        return;
        const res = await removeBanner(id);
        const {msg, success} = res.data
        if (success) {
            this.NOTIFY("Xóa thành công");
            this.getBanners();
        }
    }
  },
  components: {
    navSettingComponent,
    BannerAddNew
  },
  template: template,
  created() {
    this.getBanners();
    this.$eventBus.$emit("set.page_title", "Banner Cộng tác viên");
  },
  destroyed() {},
};
