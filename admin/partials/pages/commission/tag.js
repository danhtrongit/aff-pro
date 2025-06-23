const template = `
<div class="q-mt-lg">
<q-breadcrumbs class="q-mb-md">
                  <q-breadcrumbs-el to="/commission/product" label="Sản phẩm" icon="home"/>
                  <q-breadcrumbs-el to="/commission/category" label="Danh mục" icon="widgets" />
                  <q-breadcrumbs-el to="/commission/tag" label="Tag" icon="widgets" />
                  <q-breadcrumbs-el label="" />
        </q-breadcrumbs>
    <div v-if="!isLoading">
            <div class="wrap-search" v-click-outside="defocusApp">
                <q-input filled v-model="name" label="Gõ tên danh mục" debounce="800" @focus="showForm = true"></q-input>
                <div class="search-result style-5 q-pl-md q-pt-md" v-show="showForm" ref="list">
                    <div v-if="categories">
                        <category v-for="category in categories" :node="category" :add="add"></category>
                    </div>
            
                    <p class="text-center q-pa-md" v-else>
                        <span v-if="name">Không tìm thấy kết quả</span>
                        <span v-else>Gõ từ khóa để tìm kiếm</span>
            
            
                    </p>
            
                </div>
            </div>
            <q-markup-table separator="cell" wrap-cells v-if="selected_categories.length > 0" bordered flat class="q-mt-md">
                    <thead>
                
                        <th class="text-left q-pa-md" width="250px">Tên danh mục</th>
                        <th class="text-left q-pa-md">ID</th>
                        <th class="text-left q-pa-md">% chiết khấu</th>
                
                
                        <th class="text-left q-pa-md">#</th>
                    </thead>
                    <tbody>
                        <tr :class="{'bg-blue-1': categorie.hasOwnProperty('new')}" v-for="categorie in selected_categories"
                            :key="categorie.id">
                
                            <td class="text-left q-pa-md" v-html>{{categorie.cat_name}}</td>
                            <td class="text-left q-pa-md"> {{categorie.cat_ID}} </td>
                            <td class="text-left q-pa-md">
                            
                            <q-input filled type="number" min="1" v-model="categorie.commission" />

                            </td>
                
                
                            <td class="text-left q-pa-md">
                                <q-btn round color="accent" icon="remove" size="sm" @click="removeProduct(categorie.cat_ID)"></q-btn>
                            </td>
                        </tr>
                
                    </tbody>
            </q-markup-table>

             <q-btn color="primary"  icon="save" class="q-mb-lg q-mt-lg" label="Lưu" @click="save"/>
    </div>
</div>   
`;
Vue.component('category', {
    props: ['node', 'add'],
    template: `<div class="c-item">
        <div>
            {{node.cat_name}}  <q-btn flat round color="primary" icon="add" @click="add(node.cat_name, node.cat_ID)"/>
        </div>
        <div class="c-item-child" v-if="node.hasOwnProperty('wpse_children')">
            <category v-for="child in node.wpse_children" :node="child" :add="add"></category>
        </div>
    </div>`
})
import clickOutSide from '../../components/click-out-side.js'
import ProductSearch from '../../components/product-search.js'
import { getCommissionSetting, saveCommissionSetting, getProductCat } from '../../api/product.js'
const { RV_CONFIGS } = window;

export default {
  data: () => ({
    isLoading: false,
    products: [],
    categories: {},
    selected_categories: [],
    name: '',
    showForm: false,

  }),
  directives: { 'click-outside' :  clickOutSide},
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
    
    removeProduct(id){
      const index = this.selected_categories.findIndex(el => el.cat_ID === id)
      this.selected_categories.splice(index, 1)

    },
    async save(){
        this.$q.loading.show()
        const res = await saveCommissionSetting(this.selected_categories, 'product_tag');
        const {msg, success} = res.data
            this.NOTIFY(msg, success)
        this.$q.loading.hide()

    },
    async getData(){
        this.isLoading = true;
            const res = await getCommissionSetting('product_tag')
            const {data} = res.data
            if(data)
                this.selected_categories = data
            this.isLoading = false
    },
    async getAllCat(){
        this.isLoading = true;
            const res = await getProductCat('product_tag')
            const {data} = res.data
            if(data)
                this.categories = data
            this.isLoading = false
    },
    defocusApp(){
        this.showForm = false
    },
    add(cat_name, cat_ID){
        let isExist = false
        this.selected_categories.forEach(cat => {
        if(cat.cat_ID == cat_ID){
            isExist = true
            this.$q.notify({message: 'Danh mục đã tồn tại trong danh sách', color: 'warning', position: 'top'})
            // categorie.qty++
        }

        })
        if(!isExist){
        this.selected_categories.unshift({
            cat_name,
            cat_ID,
            commission: 1,
            new: true
        })
        this.$q.notify({message: 'Thêm mới danh mục thành công', color: 'primary', position: 'top'})

        }


    },
    
  },
  components: {
    ProductSearch,
  },
  template: template,
  created() {
    this.getAllCat();
    this.getData();
    this.$eventBus.$emit("set.page_title", "Chiết khấu theo Tag");
    
  },
  destroyed() {},
};
