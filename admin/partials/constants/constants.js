const LANG_DATE_PICKER = {
            formatLocale: {
                monthsShort: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
                weekdaysShort: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
                weekdaysMin: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
              
            },
}
const DATE_PICKER_LOCALE_VN = {
        days: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
        daysShort: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
        months: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
        monthsShort: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
        firstDayOfWeek: 1
}

const sevenDaysAgoMoment = (type = 'object', format = 'YYYY/MM/DD') => {
      const from = moment().add(-7, "days").format(format)
      const to = moment().format(format)
      if(type == 'array')
      {
        return [from, to]
      }
      return {from, to};
}



const ORDER_STATUS = {
    'pending'    : 'Chờ thanh toán',
    'processing' : 'Đang xử lý',
    'on-hold'    : 'Tạm giữ',
    'completed'  : 'Hoàn thành',
    'cancelled'  : 'Đã hủy',
    'refunded'   : 'Hoàn trả',
    'failed'     : 'Đã hủy',

};
const ORDER_STATUS_COLOR = {
    'pending'    : 'grey',
    'processing' : 'grey',
    'on-hold'    : 'grey',
    'completed'  : 'green',
    'cancelled'  : 'pink',
    'refunded'   : 'pink',
    'failed'     : 'pink',

};
const LEVEL_COLOR = [
  'primary',
  'green',
  'red',
  'orange',
  'blue',
  'pink',
  'red'
]
const PAYMENT_STATUS = {
  0: {
    label: 'Chờ duyệt',
    color: 'pink'
  },
  1: {
    label: 'Thành công',
    color: 'green'
  },
  2: {
    label: 'Đã hủy',
    color: 'red'
  }
  
}

const validatePhone = (phone) => {
      let vnf_regex = /((070|079|077|076|078|089|090|093|083|084|085|081|082|088|091|094|032|033|034|035|036|037|038|039|086|096|097|098|056|052|058|092|059|099)+([0-9]{7})\b)/g;
      if (phone !== "") {
          if (vnf_regex.test(phone) == false) {
              return false;
          } else {
              return true;
          } 
      } else {
          return false;
      }
  }
  const validateEmail = (email) => {
    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(String(email).toLowerCase());
}

const hasWhiteSpace = (s) => {
        var regexp = new RegExp(/^[a-zA-Z0-9 ]+$/);

        return /\s/g.test(s) || !regexp.test(s);
}
export  {
    LANG_DATE_PICKER,
    DATE_PICKER_LOCALE_VN,
    LEVEL_COLOR,
    ORDER_STATUS,
    ORDER_STATUS_COLOR,
    PAYMENT_STATUS,
    sevenDaysAgoMoment,
    validatePhone,
    validateEmail,
    hasWhiteSpace
}