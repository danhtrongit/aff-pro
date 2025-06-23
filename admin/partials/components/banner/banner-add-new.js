const template = `

    <q-form @submit="addRecord" class="q-gutter-md" ref="myForm">
    <q-input
      filled
      stack-label
      v-model="record.name"
      label="Tên Banner"
      lazy-rules
      :rules="[val => (val !== null && val !== '') || 'Điền tên banner']"
    />
    <q-input
      filled
      stack-label
      v-model="record.dimension"
      label="Kích thước (Dài x Rộng)"
      lazy-rules
      :rules="[val => (val !== null && val !== '') || 'Điền kích thước banner']"
    />

    <div>
        <img width="120px" :src="record.url" v-if="record.url" @click="uploadImage" />
        <q-btn outline color="pink" label="Chọn hình ảnh" icon="polymer" v-else @click="uploadImage" />
    </div>
    

    <q-input
      type="Liên kết"
      filled
      stack-label
      v-model="record.link"
      label="Điền Liên kết Banner"
      :rules="[val => (val !== null && val !== '') || 'Điền Liên kết Banner']"
    />

    <q-btn color="primary" label="Thêm mới banner" @click="addRecord" />
  </q-form> 
`;


import { addBanner } from "../../api/banner.js";
const { RV_CONFIGS } = window 
export default {
    data: () => ({
        record: {
            dimension: null,
            name: null,
            url: null,
            link: null
        }
    }),
   
    methods: {
    	addRecord() {
            this.$refs.myForm.validate().then(success => {
                if (success) {
                addBanner(this.record).then(res => {
                   const {success, msg} = res.data
                    this.NOTIFY(msg, success);
                    this.$emit("getBanners");
                    this.$q.loading.hide();
                });

                this.$q.loading.show();
                }
            });
        },
        uploadImage()
        {
            const file_frame = wp.media.frames.file_frame = wp.media({ title: 'Upload ảnh', library: { type: 'image' }, button: { text: 'Lựa chọn' }, multiple: false });

            file_frame.on('select',  () => {

                const attachment = file_frame.state().get('selection').first().toJSON();
                this.record.url = attachment.url
                
            });
            file_frame.open();
        },

	},
	components:{

	},
    template: template,
    created(){
      
    }

}