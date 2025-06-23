import { increaseTotalCall, updateOrderStatus } from '../api/order.js'
export default {
   methods: {
       async cancelOrder(){
            this.$q.dialog({
                title: 'Xác nhận hủy đơn',
                message: 'Lựa chọn lý do',
                options: {
                type: 'checkbox',
                model: [],
                // isValid: model => model.length > 0,
                // inline: true
                items: [
                    { label: 'Trùng đơn', value: 'Trùng đơn' },
                    { label: 'Khách đổi ý, không muốn mua nữa', value: 'Khách đổi ý, không muốn mua nữa' },
                    { label: 'Sản phẩm chất lượng kém', value: 'Sản phẩm chất lượng kém' },
                ]
                },
                cancel: true,
                persistent: true
            }).onOk(async data => {
                this.$q.loading.show()
                const res = await updateOrderStatus({id: this.order.id, status: 7, notes: data})
                this.$q.loading.hide()
                this.NOTIFY(res.data.msg, res.data.success)
            })
        },
   }
}