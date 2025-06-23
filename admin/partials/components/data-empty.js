const template = `

    <div class="flex flex-center" style="padding-top:100px; flex-direction:column">
    <img
      alt="Quasar logo"
      :src=" configs.plugin_url + '/public/images/box_empty.svg'"
      style="width: 200px; height: 200px"
    >
    <div class="q-mt-md">Không có dữ liệu hiển thị</div>
    </div>   
`;



const { RV_CONFIGS } = window 
export default {
    data: () => ({
        configs: RV_CONFIGS,
    }),
   
    methods: {
    	
	},
	components:{

	},
    template: template,
    created(){
      
    }

}