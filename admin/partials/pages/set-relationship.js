

const template = `
<div class="q-mt-lg">
    <p>Lưu ý: Tài khoản con phải chưa là cấp con của tài khoản khác</p>
    <div class="row q-col-gutter-md q-mb-md">
        
        <div class="col-4">
            <q-input filled dense v-model="relationship.ancestor" label="Tên tài khoản cha" debounce="600" />
            <div class="q-pa-md" v-if=" info.ancestor.hasOwnProperty('msg')">
                {{info.ancestor.msg}}
            </div>
        </div>
    
        <div class="col-4">
            <q-input filled dense v-model="relationship.descendant" label="Tên tài khoản con" debounce="600" />
            <div class="q-pa-md" v-if="  info.descendant.hasOwnProperty('msg')">
                {{info.descendant.msg}}
            </div>
        </div>
        <div class="col-2">
            <q-btn color="primary" label="Thiết lập mối quan hệ" style="width: 100%" @click="set" />
        </div>
    </div>
</div>
`;

import { getUser, setRelationship } from "../api/user.js"
const { RV_CONFIGS } = window 
export default {
    data: () => ({
        relationship: {
            ancestor: null,
            descendant: null
        },
        info: {
            ancestor: {},
            descendant: {}
        }
      
    }),
    methods: {
        async getUser(ances = true){
            const value = ances ? this.relationship.ancestor : this.relationship.descendant
            const res = await getUser('user_login', value);
            
            const {success, msg, data} = res.data

            const key = ances ? 'ancestor' : 'descendant'
            
            this.info[key] = res.data
            this.NOTIFY(msg, success);
        
        },
        async set(){
            if(!this.relationship.ancestor || !this.relationship.descendant || !this.info.ancestor.success || !this.info.descendant.success)
                return this.NOTIFY('Thông tin tài khoản không chính xác', false);

            const res = await setRelationship(this.info.descendant.data.ID, this.info.ancestor.data.ID)
            const {success, msg, data} = res.data
            if(success){
                this.relationship.ancestor = null
                this.relationship.descendant = null
                this.info.ancestor = {}
                this.info.descendant = {}

            }
            this.NOTIFY(msg, success);
          
        },
      
	},
	components:{
	},
    watch:{
        'relationship.ancestor': function(val){
            console.log(val);
            if(val)
                this.getUser(true)
        },
        'relationship.descendant': function(val){
            if(val)
                this.getUser(false)
        }
    },
    template: template,
    created(){
        this.$eventBus.$emit('set.page_title', 'Thiết lập quan hệ');
    }

}