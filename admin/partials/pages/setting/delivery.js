const template = `
<div class="q-mt-lg">
    <div v-if="!isLoading">
        
        <div class="row q-col-gutter-md q-mb-lg">
          <div class="col-9">
                <navSettingComponent/>
          </div>
          <div class="col-3">
              
          </div>
        </div>
        <q-form  @submit="onSubmit" ref="myForm">
            <p>Cài đặt thông tin tài khoản</p>
            <div class="row q-col-gutter-md">
                <div class="col-4"> <q-input  filled v-model="settings.account.shop_id"  label="Tên Shop" class="q-mb-md" stack-label :rules="[val => (val && val.length > 0) || 'Điền Shop ID']"/> </div>
                <div class="col-4"> <q-input filled v-model="settings.account.token" label="Token" class="q-mb-md" stack-label :rules="[val => (val && val.length > 0) || 'Điền token']"/></div>
                <div class="col-4"> <q-input filled v-model="settings.account.hash" label="Hash" class="q-mb-md" stack-label :rules="[val => (val && val.length > 0) || 'Điền mã Hash']"/></div>
                



            </div>
          
            <p class="q-mt-md">Cài đặt thông tin kho hàng</p>
            <div v-for="(warehouse, i) in settings.warehouses" :key="i">
            <div class="row q-col-gutter-md">
                    <div class="col-3"> <q-input  filled v-model="warehouse.id"  label="ID kho hàng" class="q-mb-md" stack-label/> </div>
                    <div class="col-3"> <q-input  filled v-model="warehouse.owner_name"  label="Tên người nhận" class="q-mb-md" stack-label/> </div>
                    <div class="col-3"> <q-input  filled v-model="warehouse.owner_phone"  label="Số điện thoại" class="q-mb-md" stack-label/> </div>
                    <div class="col-3"> <q-input  filled v-model="warehouse.owner_email"  label="Email" class="q-mb-md" stack-label/> </div>

                    <div class="col-12"><selectWareHouseLocation :cityOptions="cityOptions" :city_name.sync="warehouse.city_name" :city_id.sync="warehouse.city_id" :district_id.sync="warehouse.district_id" :district_name.sync="warehouse.district_name" :ward_id.sync="warehouse.ward_id" :ward_name.sync="warehouse.ward_name" :is_main.sync="warehouse.is_main"/></div>

                    <div class="col-9"> <q-input  filled v-model="warehouse.address"  label="Địa chỉ" class="q-mb-md" stack-label/> </div>
                    <div class="col-3"><q-btn color="red" round icon="delete" class="q-mb-lg " @click="removeWareHouse(i)"/></div>

            </div>
            <hr>
            </div>
                <div class="col-12"><q-btn color="primary"  icon="add" class="q-mb-lg " label="Thêm mới kho hàng" @click="addWareHouse"/></div>

            <q-btn color="primary"  icon="save" class="q-mb-lg q-mt-lg" label="Lưu" type="submit"/>
            <q-btn color="pink"  icon="delete" class="q-mb-lg q-mt-lg" label="Xóa dữ liệu" @click="resetAllData"/>
        </q-form>

    </div>
</div>   
`;
import { setConfigs, getConfigs } from "../../api/config.js";
import { getCityOptions } from "../../api/location.js";
import navSettingComponent from "../../components/nav-setting-component.js";
import selectWareHouseLocation from "../../components/select-warehouse-location.js";
const { RV_CONFIGS } = window;

const warehouse = {
  id: null,
  owner_name: null,
  owner_phone: null,
  owner_email: null,
  city_id: null,
  district_id: null,
  ward_id: null,
  city_name: null,
  district_name: null,
  ward_name: null,
  is_main: false,
  address: null,
};
export default {
  data: () => ({
    isLoading: false,
    cityOptions: [],
    settings: {
      account: {
        shop_id: null,
        token: null,
        hash: null,
      },
      warehouses: [Object.assign({}, warehouse)],
      webhook: null,
      status_map: [],
      logo: null,
    },
  }),

  methods: {
    addWareHouse() {
      this.settings.warehouses.push(Object.assign({}, warehouse));
    },
    removeWareHouse(i) {
      this.settings.warehouses.splice(i, 1);
    },
    save() { },
    onSubmit() {
      this.$refs.myForm.validate().then((success) => {
        if (success) {
          setConfigs(this.settings).then(res => {
            const { success, msg, data } = res.data
            this.NOTIFY(msg, success)
            if (success)
              window.byc_settings = data
          });
        }
      });
    },

    coppyShortcode() {
    },
    resetAllData() {
      this.$q
        .dialog({
          dark: true,
          title: "Xóa toàn bộ dữ liệu",
          message:
            "Bao gồm dữ liệu giao dịch, thông tin tài khoản. Gõ bicycle-mh",
          prompt: {
            model: "",
            type: "text", // optional
          },
          cancel: true,
          persistent: true,
        })
        .onOk((data) => {
          if (data == "bicycle-mh") {
            axios
              .post(
                RV_CONFIGS.ajax_url,
                this.jsonToFormData({ action: "byc_remove_all_data" })
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
    selectWareHouseLocation,
  },
  template: template,
  created() {
    this.getConfigs().then((data) => {
      this.settings = data
    });
    this.$eventBus.$emit("set.page_title", "Cài đặt");

    getCityOptions().then((res) => {
      this.cityOptions = res.data.data;
    });

    getConfigs().then(res => {
      this.settings = res.data.data
    })
  },
  destroyed() { },
};
