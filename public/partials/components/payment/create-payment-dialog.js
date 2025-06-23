const template = `
    <q-dialog
       v-model="paymentDialog"
       persistent transition-show="fade-in" transition-hide="fade-out" 
    >
      <q-card style="width: 600px; max-width: 80vw;" class="bd-rd-0">
        <q-card-section class="row items-center" style="background:#FDA524; color:#fff">
          <div class="text-h6 text-white">Tạo lệnh rút tiền: </div>
          <q-space />
          <q-btn icon="close" flat round dense @click="$emit('close')" />
        </q-card-section>


        <q-card-section class="">
            <div class="row q-col-gutter-md">
                <div class="col-12">
                <q-form @submit="addRecord" class="q-gutter-sm" ref="myForm">
                  
                    <q-input @keyup="formatMoney(record, 'amount')" type="text" filled stack-label v-model="record.amount" label="Nhập số tiền muốn rút" lazy-rules
                        :rules="[val => (parseInt(val) && parseInt(val) > 0) || 'Bạn cần nhập số tiền muốn rút', val => (val.replaceAll(',', '') >= parseInt(settings.aff_min_request)) || 'Số tiền rút tối thiểu cần lớn hơn ' + addCommas(settings.aff_min_request) + 'đ']" />


                    <q-select filled v-model="record.bank_info.bank_name" :options="bank_options" label="Ngân hàng" stack-label emit-value map-options class="q-mb-md" :rules="[val => (val !== null && val !== '') || 'Chọn ngân hàng']"/>
                 
                    <q-input filled stack-label v-model="record.bank_info.bank_owner" label="Tên chủ tài khoản" lazy-rules
                        :rules="[val => (val !== null && val !== '') || 'Điền tên chủ tài khoản']" />
                
                    <q-input type="number" filled stack-label v-model="record.bank_info.bank_number" label="Số tài khoản"
                        :rules="[val => (val !== null && val !== '') || 'Điền số tài khoản']" />
                
                    <q-input type="password" filled stack-label v-model="record.password" label="Nhập mật khẩu tài khoản cộng tác viên" :rules="[
                        val =>
                          (val !== null && val !== '') ||
                          'Yêu cầu điền mật khẩu tài khoản cộng tác viên '
                      ]" />
                
                    <q-btn color="pink" label="Sử dụng tài khoản ngân hàng cũ" @click="showUserBanks" />
                    <q-btn color="orange" label="Tạo lệnh rút tiền" @click="addRecord" />
                </q-form>
                </div>

              
            </div>
        </q-card-section>

        
      </q-card>
    </q-dialog>
        
          
`;
import { banks } from "../../constants/banks.js";
import { getUserBankInfo } from "../../api/user.js";
import { createPaymentRequest } from "../../api/payment.js";
export default {
  props: ["paymentDialog"],
  data: () => ({
    settings: {},
    record: {
      amount: '',
      active: true,
      bank_info: {
        bank_name: "",
        bank_owner: "",
        bank_number: "",
      },
      password: "",
    },
    bank_options: [],
    user_banks: [],

  }),
  methods: {
    addRecord() {
      this.$refs.myForm.validate().then((success) => {
        if (success) {
          const response = createPaymentRequest(this.record).then((res) => {
            const { success, msg } = res.data;
            this.NOTIFY(msg, success);
            this.$q.loading.hide();
            if(success){
                this.$eventBus.$emit('user.refresh');
                this.$emit('refresh')
                this.$emit('close')
            }
          });
          this.$q.loading.show();
        }
      });
    },
    showUserBanks(){
      if(!this.user_banks.length)
        return this.NOTIFY('Không tìm thấy dữ liệu, dữ liệu rỗng', false)
      

      
      this.$q.dialog({
        title: 'Tài khoản ngân hàng',
        message: 'Hãy lựa chọn tài khoản ngân hàng cũ:',
        options: {
          type: 'radio',
          model: 'opt1',
          // inline: true
          items: this.user_banks
        },
        cancel: true,
        persistent: true
      }).onOk(data => {
        this.record.bank_info.bank_name = data.bank_name
        this.record.bank_info.bank_number = data.bank_number
        this.record.bank_info.bank_owner = data.bank_owner
      })

    },
    async getUserBanks(){
        const res = await getUserBankInfo();
        const { data } = res.data
        let user_banks = []
        if(data.length){
          user_banks = data.map(el => {
            return {label: `${el.bank_name} - ${el.bank_owner} - ${el.bank_number}`, value: el}
          })

        }
        this.user_banks = user_banks
    },
    
  },
  computed: {},
  components: {},
  template: template,
  created() {
    this.bank_options = banks.data.map(el => {return {label: el.shortName, value: el.shortName}})
    this.getConfigs().then((res) => {
      this.settings = res;
      this.level_options = [];
    });
    this.getUserBanks()
  },
  watch: {},
};
