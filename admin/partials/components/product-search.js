const template = `
    <div class="auto-complete-search"  v-click-outside-app="defocusApp">
        <div class="row flex justify-between items-center">
            <div style="width: calc(100% - 90px)">
                <q-input filled :loading="isLoading" v-model="filters.search" label="Gõ tên sản phẩm để tìm kiếm" debounce="800" @focus="showForm = true"></q-input>
            </div>
            <span><q-btn round color="primary" icon="add" size="sm" @click="addProductById"></q-btn></span>
        </div>
        <div class="search-result scroll-bar-thin-5 card-item" v-show="showForm" ref="list">
            <table class="full-width" v-if="products.length > 0">
                <thead>
                    <th class="text-left q-pa-md">Ảnh</th>
                    <th class="text-left q-pa-md">Tên sản phẩm</th>
                    <th class="text-left q-pa-md">Mã sản phẩm</th>
                    <th class="text-left q-pa-md">Giá thường</th>
                    <th class="text-left q-pa-md">Giá giảm</th>
                    <th class="text-left q-pa-md">#</th>
                </thead>
                <tbody>

                    <template  v-for="product in products" :key="product.id">
                        <tr>
                            <td class="text-left q-pa-md">
                                <q-img :src="product.image" :ratio="1" style="width:50px"></q-img>
                            </td>
                            <td class="text-left q-pa-md"> {{product.name}} </td>
                            <td class="text-left q-pa-md"> {{product.sku}} </td>
                            <td class="text-left q-pa-md">{{ addCommas(product.regular_price)}}</td>
                            <td class="text-left q-pa-md"> <template v-if="product.sale_price">{{ addCommas(product.sale_price)}}</template></td>
                            <td>
                                <q-btn round color="primary" icon="add" size="sm" @click="$emit('addProduct', product)"></q-btn>
                            </td>
            
                        </tr>
                        <template v-if="product.children">
                            <tr class="product-childred" v-for="product_c in product.children" :key="product_c.id">
                                <td class="text-left q-pa-md">
                                    <!-- <q-img :src="product_c.image" :ratio="1" style="width:50px"></q-img> -->
                                </td>
                                <td class="text-left q-pa-md"> {{product_c.name}} </td>
                                <td class="text-left q-pa-md"> {{product_c.sku}} </td>
                                <td class="text-left q-pa-md">{{ addCommas(product_c.regular_price)}}</td>
                                <td class="text-left q-pa-md"> <template v-if="product.sale_price">{{ addCommas(product_c.sale_price)}}</template></td>
                                <td>
                                    <q-btn round color="primary" icon="add" size="sm" @click="$emit('addProduct', product_c)"></q-btn>
                                </td>
                            
                            </tr>
                        </template>
                    </template>
        
                </tbody>
            </table>
            <p class="text-center q-pa-md" v-else>
                <span v-if="name">Không tìm thấy kết quả</span>
                <span v-else>Gõ từ khóa để tìm kiếm</span>
        
            </p>
        
        </div>
    </div>   
`;


import { getProducts, getProductById } from '../api/product.js'
const { RV_CONFIGS } = window 
export default {
    props: ['addProduct'],
    data: () => ({
        filters:{
            search: null
        },
        showForm: false,
        configs: RV_CONFIGS,
        search: '',
        products: [],
        isLoading: false,
    }),
   
    methods: {
    	async getData()
        {
            
            if(this.filters.search == '')
            return;

            if(this.filters.search.length < 3){
                this.NOTIFY('Hãy nhập tối thiểu 3 kí tự để tìm kiếm', false)
                return;
            }
            this.isLoading = true
            const res = await getProducts(this.filters);
            this.isLoading = false
            this.products = res.data.data
            this.showForm = true

            // console.log(this.name)
        },
        async addProductById(){
            const id = await this.PROMPT('Nhập ID hoặc mã SKU sản phẩm');
            if(!id){
                this.NOTIFY('Hãy nhập ID hoặc SKU để tìm kiếm', false)
                return;
            }
            const res = await getProductById(id)
            const {success, msg, data} = res.data
            if(success)
                this.$emit('addProduct', data)
            else
                this.NOTIFY(msg, success)
        },
     
        defocusApp(){
              this.showForm = false
        },

	},
	components:{

	},
    template: template,
    directives: {
        "click-outside-app": {
            bind: function(el, binding) {
            // Define ourClickEventHandler
            const ourClickEventHandler = event => {
                if (!el.contains(event.target) && el !== event.target) {
                // as we are attaching an click event listern to the document (below)
                // ensure the events target is outside the element or a child of it
                binding.value(event); // before binding it
                }
            };
            // attached the handler to the element so we can remove it later easily
            el.__vueClickEventHandler__ = ourClickEventHandler;

            // attaching ourClickEventHandler to a listener on the document here
            document.addEventListener("click", ourClickEventHandler);
            },
            unbind: function(el) {
            // Remove Event Listener
            document.removeEventListener("click", el.__vueClickEventHandler__);
            }
        }
    },
    watch: {
        'filters.search': function(val)
        {
            console.log(val);
            if(val == ''){
                this.products = []
                this.showForm = false
            }
            else
                this.getData()
        }
    },
    created(){
      
    }

}