<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://vuacode.io/
 * @since      1.0.0 
 *
 * @package    Wordpress_Review_Mh
 * @subpackage Wordpress_Review_Mh/admin/partials
 */
?>
<div class="wrap-vuacode">
    <!-- This file should primarily consist of HTML with a little bit of PHP. -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons" rel="stylesheet"
        type="text/css">

    <link href="<?php echo AFF_URL ?>/admin/css/animate.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo AFF_URL ?>/admin/css/quasar.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo AFF_URL ?>/admin/css/date2picker.css">

    <script src="<?php echo AFF_URL ?>/admin/js/main.min.js"></script>
    <script src="<?php echo AFF_URL ?>/admin/js/quasar.umd.min.js"></script>
    <script src="<?php echo AFF_URL ?>/admin/js/axios.js"></script>
    <script src="<?php echo AFF_URL ?>/admin/js/date2picker.js"></script>
    <script src="<?php echo AFF_URL ?>/admin/js/moment-with-locales.min.js"></script>
    <script src="<?php echo AFF_URL ?>/admin/js/Chart.min.js"></script>
    <script src="<?php echo AFF_URL ?>/admin/js/vue-chartjs.min.js"></script>
    <style>
    .card-item {
        border: none;
        margin-bottom: 1rem;
        -webkit-box-shadow: 0 4px 24px 0 rgb(34 41 47 / 10%);
        box-shadow: 0 4px 24px 0 rgb(34 41 47 / 10%);
        border-radius: .428rem;
        overflow: hidden;
        padding: 1.5rem;
    }

    .card-name {
        font-weight: 400;
        font-size: 0.875rem;
        color: rgb(94, 88, 115);
    }

    .card-number {
        line-height: 1.2;
        color: rgb(94, 88, 115);
        font-size: 1.514rem;
        font-weight: 500;
        letter-spacing: 0px;
        margin-top: 5px;
    }
    </style>
    <script>
    window.quasarConfig = {
        brand: { // this will NOT work on IE 11
            primary: '',
            // ... or all other brand colors
        },
        notify: {}, // default set of options for Notify Quasar plugin
        loadingBar: {
            color: 'blue'
        }
        // ..and many more (check Installation card on each Quasar component/directive/plugin)
    }
    window.RV_CONFIGS = {
        plugin_url: '<?php echo AFF_URL ?>',
        site_url: '<?php echo get_site_url() ?>',
        ajax_url: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
    }
    </script>



    <div id="q-app">
        <div class="q-pa-md">

            <q-toolbar class="bg-warning text-white">
                <q-btn round dense icon="west" color="pink" class="q-mr-sm" to="/" v-show="$route.path != '/'"></q-btn>
                <!-- <q-btn flat round dense icon="wifi_tethering" class="q-mr-sm" to="/" v-show="$route.path == '/'"></q-btn> -->
                <!-- <q-btn flat round dense icon="menu" class="q-mr-sm" to="/" v-show="$route.path == '/'"></q-btn> -->
                <q-avatar to="/" class="quasar-logo" v-show="$route.path == '/'">
                    <img :src="logo">
                    <!-- <img :src="https://cdn.quasar.dev/logo/svg/quasar-logo.svg"> -->
                </q-avatar>

                <q-toolbar-title>{{page_title}}</q-toolbar-title>
                <q-space></q-space>
                <q-tabs>
                    <q-route-tab to="/" exact label="Dashboard"></q-route-tab>
                    <q-route-tab to="/users" exact label="Tài khoản"></q-route-tab>
                    <q-route-tab to="/history" exact label="Lịch sử"></q-route-tab>
                    <q-route-tab to="/payments" exact label="Thanh toán"></q-route-tab>
                    <q-route-tab to="/commission/product" exact label="Cài đặt chiết khấu"></q-route-tab>
                    <q-route-tab to="/settings" exact label="Cài đặt"></q-route-tab>
                    <!-- <q-tab @click="openURL('https://docs.google.com/document/d/1zdvGp-tBRtI0GAFxAW7kTKubAdyLScdRn_9cWAaa--8/edit')" exact label="Hướng dẫn"></q-tab> -->
                </q-tabs>

                <q-btn flat round dense icon="whatshot" @click="openURL('https://vuacode.io')"></q-btn>
            </q-toolbar>




            <transition name="fade" mode="out-in">
                <router-view></router-view>
            </transition>


        </div>
    </div>

    <script type='module'>
    /*
        Example kicking off the UI. Obviously, adapt this to your specific needs.
        Assumes you have a <div id="q-app"></div> in your <body> above
       */
    import setAddtionalSettings from '<?php echo AFF_URL ?>/admin/partials/pages/setting/additional-settings.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import setRelationshipPage from '<?php echo AFF_URL ?>/admin/partials/pages/set-relationship.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';

    import settingsPage from '<?php echo AFF_URL ?>/admin/partials/pages/setting/settings.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import userLevelSettingPage from '<?php echo AFF_URL ?>/admin/partials/pages/setting/user-level.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import userRelationshipSettingPage from '<?php echo AFF_URL ?>/admin/partials/pages/setting/user-relationship.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import notificationSettingPage from '<?php echo AFF_URL ?>/admin/partials/pages/setting/notification.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import bannerSettingPage from '<?php echo AFF_URL ?>/admin/partials/pages/setting/banner.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import indexPage from '<?php echo AFF_URL ?>/admin/partials/pages/dashboard.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import productCommissionPage from '<?php echo AFF_URL ?>/admin/partials/pages/commission/product.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import categoryCommissionPage from '<?php echo AFF_URL ?>/admin/partials/pages/commission/category.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import tagCommissionPage from '<?php echo AFF_URL ?>/admin/partials/pages/commission/tag.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import userPage from '<?php echo AFF_URL ?>/admin/partials/pages/user.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import historyPage from '<?php echo AFF_URL ?>/admin/partials/pages/history.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import historyIncomePage from '<?php echo AFF_URL ?>/admin/partials/pages/history-income.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import historyCommissionPage from '<?php echo AFF_URL ?>/admin/partials/pages/history-commission.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import paymentPage from '<?php echo AFF_URL ?>/admin/partials/pages/payment.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import registerPage from '<?php echo AFF_URL ?>/admin/partials/pages/register.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import emptyComponent from '<?php echo AFF_URL ?>/admin/partials/components/data-empty.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import loading from '<?php echo AFF_URL ?>/admin/partials/components/loading.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>';
    import {
        getConfigs
    } from "<?php echo AFF_URL ?>/admin/partials/api/config.js?ver=<?php echo WP_VuaCode_AFF_VERSION ?>";

    // import VueEasyLightbox from 'https://unpkg.com/vue-easy-lightbox@next/dist/vue-easy-lightbox.esm.min.js';
    let router = null
    const EventBus = new Vue()
    try {

        router = new VueRouter({
            routes: [{
                    path: '/',
                    component: indexPage
                }, // Root IndexIndex
                {
                    path: '/users',
                    component: userPage
                },
                {
                    path: '/settings',
                    component: settingsPage
                },
                {
                    path: '/history',
                    component: historyPage
                },
                {
                    path: '/history-commission',
                    component: historyCommissionPage
                },
                {
                    path: '/history-income',
                    component: historyIncomePage
                },
                {
                    path: '/payments',
                    component: paymentPage
                },
                {
                    path: '/settings/user-level',
                    component: userLevelSettingPage
                },
                {
                    path: '/settings/user-relationship',
                    component: userRelationshipSettingPage
                },
                {
                    path: '/settings/notification',
                    component: notificationSettingPage
                },
                {
                    path: '/settings/banner',
                    component: bannerSettingPage
                },
                {
                    path: '/commission/product',
                    component: productCommissionPage
                },
                {
                    path: '/commission/category',
                    component: categoryCommissionPage
                },
                {
                    path: '/commission/tag',
                    component: tagCommissionPage
                },
                {
                    path: '/set-relationship',
                    component: setRelationshipPage
                },
                {
                    path: '/register',
                    component: registerPage
                },
                {
                    path: '/settings/additional',
                    component: setAddtionalSettings
                },
            ]
        });
    } catch (e) {

    }
    Vue.prototype.$eventBus = EventBus
    Vue.component('empty-component', emptyComponent)
    Vue.component('loading-component', loading)
    Vue.mixin({
        methods: {
            openURL(url) {
                Quasar.utils.openURL(url)
            },
            copyText(text) {
                Quasar.utils.copyToClipboard(text)
                this.NOTIFY('Sao chép thành công')
            },
            addCommas(nStr) {
                nStr += '';
                let x = nStr.split('.');
                let x1 = x[0];
                let x2 = x.length > 1 ? '.' + x[1] : '';
                let rgx = /(\d+)(\d{3})/;
                while (rgx.test(x1)) {
                    x1 = x1.replace(rgx, '$1' + ',' + '$2');
                }
                return x1 + x2;
            },
            formatDateRange(date) {
                const replaceW = (d) => {
                    return d.split('/').join('-');
                }

                if (date == '')
                    return '';
                if (typeof date == 'object')
                    return {
                        from: replaceW(date.from),
                        to: replaceW(date.to)
                    }
                else
                    return {
                        from: replaceW(date),
                        to: replaceW(date)
                    }
            },
            formatDateMoment(mysqlDate, format = 'DD/MM/YYYY HH:mm') {
                return moment(mysqlDate, "YYYY/MM/DD HH:mm:ss").format(format)
            },
            buildFormData(formData, data, parentKey) {
                if (data && typeof data === 'object' && !(data instanceof Date) && !(data instanceof File)) {
                    Object.keys(data).forEach(key => {
                        this.buildFormData(formData, data[key], parentKey ? `${parentKey}[${key}]` :
                            key);
                    });
                } else {
                    const value = data == null ? '' : data;

                    formData.append(parentKey, value);
                }
            },
            jsonToFormData(data) {
                const formData = new FormData();

                this.buildFormData(formData, data);

                return formData;
            },
            NOTIFY(msg, type = true) {
                this.$q.notify({
                    message: msg,
                    progress: true,
                    type: type == true ? 'positive' : 'negative',
                    color: type == true ? 'green' : 'red',
                    position: 'top',
                    timeout: 2000
                })
            },
            PROMPT(text) {
                console.log(text);
                return new Promise((res, rej) => {
                    this.$q.dialog({
                        title: 'Xác nhận',
                        message: text,
                        prompt: {
                            model: '',
                            type: 'text' // optional
                        },
                        cancel: true,
                        persistent: true
                    }).onOk((data) => {
                        res(data)
                    }).onCancel(() => {
                        res(false)

                    })
                })
            },
            CONFIRM(text) {
                return new Promise((res, rej) => {
                    this.$q.dialog({
                        title: 'Xác nhận',
                        message: text,
                        cancel: true,
                        persistent: true,

                    }).onOk(() => {
                        res(true)
                    }).onCancel(() => {
                        res(false)

                    })
                })
            },

            deepMerge(target, source) {
                Object.entries(source).forEach(([key, value]) => {
                    if (value && typeof value === 'object') {
                        this.deepMerge(target[key] = target[key] || {}, value);
                        return;
                    }
                    target[key] = value;
                });
                return target;
            },
            getConfigs() {
                return new Promise((resolve, reject) => {
                    if (window.hasOwnProperty('aff_settings'))
                        resolve(window.aff_settings)
                    else {
                        getConfigs().then(res => {
                            window.aff_settings = res.data.data
                            resolve(window.aff_settings)
                        })
                    }

                })
            },
            isNormal() {
                const settings = window.aff_settings
                return settings.hasOwnProperty('version') && settings.version == 'normal' ? true : false
            }
        }
    })

    if (router && window?.routerx) new Vue({
        router,
        el: '#q-app',
        data: function() {
            return {
                page_title: 'VuaCode AFF',
                lightbox: {
                    toggler: false,
                    sources: [],
                    id: 0
                },
                logo: `${window.RV_CONFIGS.plugin_url}/public/images/virus.svg`
                // configs: window.RV_CONFIGS

            }
        },
        methods: {
            setPageTitle(title) {
                this.page_title = title
            }
        },
        components: {

        },
        created() {
            this.getConfigs()
            EventBus.$on('set.page_title', this.setPageTitle);

        }
        // 
    });




    //Set Height Div Wrap
    const setViewPort = () => {

        const vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
        document.querySelector(".wrap-vuacode").style.minHeight = `${vh - 120}px`;
    }
    window.onresize = () => {
        setViewPort();
    }
    setViewPort();

    //Fix Admin Href 
    const aList = document.querySelectorAll('#adminmenu a');
    aList.forEach(el => {
        const href = el.getAttribute("href");
        el.href = window.RV_CONFIGS.site_url + '/wp-admin/' + href;
    })
    document.title = 'VuaCode AFF'

    document.querySelector('body').classList.add("wp-review-q-app");
    </script>
</div>