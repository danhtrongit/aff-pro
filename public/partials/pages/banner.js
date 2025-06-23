

const template = `
<div class="q-pa-md">
<loading-component v-if="isLoading"/>
<template v-else>

        
        <empty-component v-if="records.length == 0 && isLoading == false" />
        <div class="row q-col-gutter-md" v-else>
        <div class="col-12 col-md-12 col-lg-6" v-for="record in records" :key="record.id">
                <div class="card-item q-mb-none">
                        <div class="relative-position">
                            <div class="banner-item flex">
                                <div class="banner-img" style="width: 200px">
                                    <q-img :src="record.url" />
                                </div>
                                <div class="banner-info q-ml-md col">
                                    <p><b>Tên banner:</b> {{ record.name }}</p>
                                    <p><b>Kích thước:</b> {{ record.dimension }}</p>
                                    <p><b>Liên kết:</b> {{ record.link }}</p>
                                    <div>Coppy đoạn mã dưới đây và gắn lên website của bạn</div>
                                    <q-input :value="generateScript(record)" filled type="textarea" readonly />
                                </div>
                                <div class="absolute-right">
                                    <q-btn color="primary" label="Sao chép" @click="generateScript(record, true)" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
            
         


            
            
        </template>
</div>
`;
import { getBanners,  } from "../api/banner.js";
const { RV_CONFIGS } = window 
export default {
    data: () => ({
        configs: RV_CONFIGS,
        isLoading: false,
        records: [],
      
        
      
    }),
   
    methods: {
       
            generateScript(banner, coppy = false) {
            const dimen = banner.dimension.split("x");
            const text = `<a target="_blank" href="${banner.link}?ref=${this.configs.user_login}"><img src="${banner.url}" width="${dimen[0]}" height="${dimen[1]}"></a>`;
            if (!coppy) return text;
            else {
                Quasar.utils.copyToClipboard(text)
                this.NOTIFY("Coppy thành công");
            }
            },
            async getData() {
                const res = await getBanners();
                this.records = res.data.data;
            },
        
  
	},
	components:{
	},
    watch:{
      
    },
    template: template,
    created(){
        this.getConfigs().then(res => {
            this.settings = res
           
            this.getData();
        })
        this.$eventBus.$emit('set.page_title', 'Banner cộng tác viên');
    }

}