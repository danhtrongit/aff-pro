

const template = `
<div class="q-pa-md">
    <div>
        <div class="row q-col-gutter-md row-eq">
            <div class="col-md-6 col-12">
                <div class="card-item">
                    <q-chip icon="event">Tạo URL giới thiệu</q-chip>
                    <div class="flex q-mt-md space-bw">
                        <q-input filled v-model="url" label="Nhập URL cần tạo đường dẫn giới thiệu" style="width:80%" dense/>
                        <q-btn color="primary" style="width:18%;" @click="createAffUrl" size="sm">Tạo URL</q-btn>
                        <p class="q-mt-md">
                            Nhập URL sản phẩm, URL danh mục hoặc bất kì URL nào để tạo URL tiếp thị liên kết
                        </p>
                    </div>
                    <div v-if="url">
                        <p>Mã QR tương ứng: <q-btn round color="primary" icon="get_app"  size="sm" @click="downloadQR(url)"/></p>
                        <img :src="generateQR(url)">
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="card-item" v-if="Object.keys(settings).length && !isNormal()">
                    <q-chip icon="event">Chia sẻ URL đăng kí cho cộng tác viên</q-chip>
                    <div class="flex q-mt-md space-bw">
                        <q-input filled v-model="register_link"  style="width:80%" dense />
                        <q-btn color="primary" style="width:18%;" @click="copyRegisterLink" size="sm">COPY</q-btn>
                        <p class="q-mt-md">
                            Bạn có thể tuyển cộng tác viên cấp dưới theo URL này
                        </p>
                    </div>

                    <div>
                        <p>Mã QR tương ứng: <q-btn round color="primary" icon="get_app"  size="sm" @click="downloadQR(register_link)"/></p>
                        <img :src="generateQR(register_link)">
                    </div>

                </div>
            </div>
        </div>
            
    </div>
</div>
`;

const { RV_CONFIGS } = window 
export default {
    data: () => ({
        configs: RV_CONFIGS,
        isLoading: true,
        settings: {},
        url: '',
        register_link: ''

    }),
   
    methods: {
       createAffUrl() {
        if (this.url == "") {
            this.$notify("URL không thể để trống", 0);
            return;
        }
        const ref_value = 'ref_value_is_id' in this.settings && this.settings.ref_value_is_id ? 'user_id' :'user_login'
        const final_url =
            this.url.split('?')[0] + "?"+this.settings.ref_name+"=" + this.configs[ref_value];

        let site_url = this.configs.site_url
            site_url = site_url.replaceAll('https://', '')
            site_url = site_url.replaceAll('http://', '')

        if(this.url.indexOf(site_url) == -1)
            return this.NOTIFY('Tên miền không hợp lệ', false)

        this.$q.dialog({
                title: "Chia sẻ link giới thiệu",
                message: final_url,
                cancel: false,
                ok: {
                    label: 'Sao chép'
                },
                persistent: true
            })
            .onOk(() => {
                this.NOTIFY('Sao chép thành công')
                
                Quasar.utils.copyToClipboard(final_url)
            });
        },
        copyRegisterLink(){
            Quasar.utils.copyToClipboard(this.register_link)
            this.NOTIFY('Sao chép thành công')
        },
        generateQR(data){
            return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(data) 
        },
        downloadQR(data){
            var url = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(data)
            var fileName = 'qr.jpg'
            var xhr = new XMLHttpRequest();
            xhr.open("GET", url, true);
            xhr.responseType = "blob";
            xhr.onload = function(){
                var urlCreator = window.URL || window.webkitURL;
                var imageUrl = urlCreator.createObjectURL(this.response);
                var tag = document.createElement('a');
                tag.href = imageUrl;
                tag.download = fileName;
                document.body.appendChild(tag);
                tag.click();
                document.body.removeChild(tag);
            }
            xhr.send();
        }
	},
	components:{
       
	},
    watch:{

    },
    template: template,
    created(){
         this.getConfigs().then(res => {
            this.settings = res
            const ref_value = 'ref_value_is_id' in this.settings && this.settings.ref_value_is_id ? 'user_id' :'user_login'
            this.register_link = this.settings.aff_user_page + '/#/dang-ky?' + this.settings.ref_name + '=' + this.configs[ref_value]
        })
        this.$eventBus.$emit('set.page_title', 'Công cụ hỗ trợ');
    }

}