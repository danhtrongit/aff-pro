const template = `
<div class="q-mt-lg">
        <q-breadcrumbs class="q-mb-md">
                  <q-breadcrumbs-el to="/commission/product" label="Sản phẩm" icon="home"/>
                  <q-breadcrumbs-el to="/commission/category" label="Danh mục" icon="widgets" />
                  <q-breadcrumbs-el to="/commission/tag" label="Tag" icon="widgets" />
                  <q-breadcrumbs-el label="" />
        </q-breadcrumbs>
    <div v-if="!isLoading">
    
            <ProductSearch @addProduct="addProduct"/>
            <q-markup-table separator="cell" wrap-cells v-if="products.length > 0" bordered flat class="q-mt-md">
                <thead>
                    <th class="text-left">Ảnh</th>
                    <th class="text-left" width="250px">Tên sản phẩm</th>
                    <th class="text-left">Mã SP</th>
                    <th class="text-left">Giá bán</th>
                    <th class="text-left">% Chiết khấu</th>
            
                    <th class="text-left">#</th>
                </thead>
                <tbody>
                    <tr :class="{'bg-blue-1': product.hasOwnProperty('new')}" v-for="product in products"
                        :key="product.id">
                        <td class="text-left q-pa-sm">
                            <q-img :src="product.image" :ratio="1" style="width:50px"></q-img>
                        </td>
                        <td class="text-left q-pa-sm" v-html="product.name"></td>
                        <td class="text-left q-pa-sm"> {{product.sku}} </td>
                        <td class="text-left q-pa-sm">{{ addCommas(product.sale_price)}}</td>
                        <td class="text-left q-pa-sm">
                            <q-input filled type="number" min="1" v-model="product.commission" />
                        </td>
            
                        <td class="text-left q-pa-sm">
                            <q-btn round color="accent" icon="remove" size="sm" @click="removeProduct(product.product_id)"></q-btn>
                        </td>
                    </tr>
            
                </tbody>
            </q-markup-table>

             <q-btn color="primary"  icon="save" class="q-mb-lg q-mt-lg" label="Lưu" @click="save"/>
    </div>
</div>   
`;

import ProductSearch from '../../components/product-search.js'
import { getCommissionSetting, saveCommissionSetting } from '../../api/product.js'
const { RV_CONFIGS } = window;

export default {
  data: () => ({
    isLoading: false,
    products: [],

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
    addProduct(product){
      console.log(product);
        const p = Object.assign({commission: 1, total: product.sale_price, new: true}, product)
        let isExist = false
        this.products.forEach(product => {
        if(p.product_id == product.product_id){
            isExist = true
            this.$q.notify({message: 'Sản phẩm đã tồn tại trong danh sách', color: 'warning', position: 'top'})
            // product.qty++
        }

        })
        if(!isExist){
        this.products.unshift(p)
        this.$q.notify({message: 'Thêm mới sản phẩm thành công', color: 'primary', position: 'top'})

        }


    },
    removeProduct(id){

      const index = this.products.findIndex(el => el.product_id == id)
      console.log(id, this.products);
      this.products.splice(index, 1)

    },
    async save(){
        this.$q.loading.show()
        const res = await saveCommissionSetting(this.products, 'product');
        const {msg, success} = res.data
            this.NOTIFY(msg, success)
        this.$q.loading.hide()

    },
    async getData(){
        this.isLoading = true;
            const res = await getCommissionSetting('product')
            const {data} = res.data
            if(data)
                this.products = data
            this.isLoading = false
    }
    
  },
  components: {
    ProductSearch,
  },
  template: template,
  created() {
    this.getData();
    this.$eventBus.$emit("set.page_title", "Chiết khấu sản phẩm");
    
  },
  destroyed() {},
};
