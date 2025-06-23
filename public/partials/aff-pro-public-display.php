<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang cộng tác viên</title>
</head>

<body>



    <!-- This file should primarily consist of HTML with a little bit of PHP. -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons" rel="stylesheet"
        type="text/css">

    <link href="<?php echo AFF_URL ?>/admin/css/animate.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo AFF_URL ?>/admin/css/quasar.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo AFF_URL ?>/admin/css/date2picker.css">
    <link rel="stylesheet" href="<?php echo AFF_URL ?>/public/css/spa.css">

    <script src="<?php echo AFF_URL ?>/admin/js/vue.min.js"></script>
    <script src="<?php echo AFF_URL ?>/admin/js/quasar.umd.min.js"></script>
    <script src="<?php echo AFF_URL ?>/admin/js/vue-router.js"></script>
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
        /* height:100%; */
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

    .d-none {
        display: none;
    }
    </style>
    <?php $user = get_userdata( get_current_user_id() ) ?>
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
        user_id: '<?php echo isset($user->ID) ? $user->ID : '' ?>',
        user_login: '<?php echo isset($user->user_login) ? $user->user_login : '' ?>'
    }
    </script>



    <div id="q-app" class="d-none">

        <q-layout view="lHh Lpr lFf">
            <q-header elevated>
                <q-toolbar>
                    <q-btn flat dense round @click="leftDrawerOpen = !leftDrawerOpen" icon="menu" aria-label="Menu">
                    </q-btn>
                    <template v-if="!$q.platform.is.mobile"> <a :href="configs.site_url"
                            style="text-decoration: none; color:#fff; font-weight: bold;">Quay lại trang chủ</a>
                    </template>
                    <template v-if="$q.platform.is.mobile"> {{page_title}} </template>
                    <q-space></q-space>
                    <div v-if="configs.user_login != ''">Số dư: {{addCommas(user.balance)}}đ</div>
                </q-toolbar>

            </q-header>

            <q-drawer v-if="configs.user_id" v-model="leftDrawerOpen" show-if-above bordered :width="250"
                content-class="bg-grey-1">
                <q-scroll-area style="height: calc(100% - 150px); margin-top: 150px; ">
                    <q-list>
                        <template v-for="nav in navs">
                            <!-- <q-item v-if="nav.children.length == 0" :to="nav.to" class="text-bold" exact clickable > -->
                            <q-item :to="nav.to" class="text-bold" exact clickable :key="nav.to">
                                <q-item-section avatar>
                                    <q-icon :name="nav.icon" />
                                </q-item-section>
                                <q-item-section>
                                    <q-item-label>{{ nav.label }}</q-item-label>
                                </q-item-section>
                            </q-item>

                            <!-- <q-expansion-item v-else :content-inset-level="0.2" expand-separator :icon="nav.icon" :label="nav.label">

          <q-item v-for="nav_child in nav.children" clickable :to="nav_child.to">
            <q-item-section avatar>
              <q-icon :name="nav_child.icon" />
            </q-item-section>
            <q-item-section>
              <q-item-label>{{nav_child.label}}</q-item-label>
            </q-item-section>
          </q-item>

     

        </q-expansion-item> -->
                        </template>

                        <q-item class="text-bold" exact @click="logout" clickable :key="logout">
                            <q-item-section avatar>
                                <q-icon name="logout" />
                            </q-item-section>
                            <q-item-section>
                                <q-item-label>Đăng xuất</q-item-label>
                            </q-item-section>
                        </q-item>

                    </q-list>
                </q-scroll-area>
                <q-img class="absolute-top" src="https://cdn.quasar.dev/img/material.png" style="height: 150px">
                    <div class="absolute-bottom bg-transparent">
                        <q-avatar size="56px" class="q-mb-sm">
                            <img :src="
                'https://magiamgiashopee.vn/wp-content/uploads/2018/08/logoshopee.png'
              " />
                        </q-avatar>
                        <div> Xin chào <span class="text-weight-bold">{{user.display_name}}</span></div>
                        <div>Số dư: <span class="text-weight-bold">{{addCommas(user.balance)}}đ</span></div>
                    </div>
                </q-img>
            </q-drawer>

            <q-page-container class="padding-bottom-mb-50">
                <div class="q-pa-md" v-if="user.aff_active === '0'" v-html="settings.noti_not_active"></div>

                <transition name="fade" mode="out-in" v-else>
                    <router-view></router-view>
                </transition>
            </q-page-container>
            <q-tabs no-caps inline-label class="mobile-only bg-warning text-white shadow-2 fixed-bottom"
                v-if="configs.user_login != ''">
                <q-tab name="event_available" icon="child_care" label="Trang chủ" @click="openURL(configs.site_url)">
                </q-tab>
                <q-tab name="settings" icon="logout" label="Đăng xuất" @click="logout"></q-tab>
            </q-tabs>

        </q-layout>
    </div>

    <script type='module'>
    /*
    Example kicking off the UI. Obviously, adapt this to your specific needs.
    Assumes you have a <div id="q-app"></div> in your <body> above
    */
    import indexPage from '<?php echo AFF_URL ?>/public/partials/pages/dashboard.js';
    import collaboratorPage from '<?php echo AFF_URL ?>/public/partials/pages/dashboard-2.js';
    import historyPage from '<?php echo AFF_URL ?>/public/partials/pages/history.js';
    import paymentPage from '<?php echo AFF_URL ?>/public/partials/pages/payment.js';
    import userPage from '<?php echo AFF_URL ?>/public/partials/pages/user.js';
    import toolPage from '<?php echo AFF_URL ?>/public/partials/pages/tool.js';
    import loginPage from '<?php echo AFF_URL ?>/public/partials/pages/login.js';
    import registerPage from '<?php echo AFF_URL ?>/public/partials/pages/register.js';
    import profilePage from '<?php echo AFF_URL ?>/public/partials/pages/user-profile.js';
    import bannerPage from '<?php echo AFF_URL ?>/public/partials/pages/banner.js';

    import emptyComponent from '<?php echo AFF_URL ?>/admin/partials/components/data-empty.js';
    import loading from '<?php echo AFF_URL ?>/admin/partials/components/loading.js';
    import {
        getConfigs
    } from "<?php echo AFF_URL ?>/admin/partials/api/config.js";
    import {
        getUserProfile,
        logout
    } from "<?php echo AFF_URL ?>/public/partials/api/user.js";
    import {
        route
    } from "<?php echo AFF_URL ?>/public/partials/constants/constants.js";
    // import VueEasyLightbox from 'https://unpkg.com/vue-easy-lightbox@next/dist/vue-easy-lightbox.esm.min.js';
    const EventBus = new Vue()

    const router = new VueRouter({
        routes: [{
                path: '/',
                component: indexPage
            }, // Root IndexIndex
            {
                path: '/tai-khoan',
                component: profilePage
            },
            {
                path: '/dang-nhap',
                component: loginPage
            },
            {
                path: '/dang-ky',
                component: registerPage
            },
            {
                path: '/thong-ke-cong-tac-vien',
                component: collaboratorPage
            },
            {
                path: '/lich-su',
                component: historyPage
            },
            {
                path: '/thanh-toan',
                component: paymentPage
            },
            {
                path: '/cong-tac-vien',
                component: userPage
            },
            {
                path: '/cong-cu',
                component: toolPage
            },
            {
                path: '/banner',
                component: bannerPage
            },

        ]
    });
    Vue.prototype.$eventBus = EventBus
    Vue.component('empty-component', emptyComponent)
    Vue.component('loading-component', loading)
    Vue.mixin({
        methods: {
            openURL(url) {
                Quasar.utils.openURL(url)
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



    new Vue({
        router,
        el: '#q-app',
        data: function() {
            return {
                configs: {},
                page_title: 'AffPro AFF',
                settings: {

                },
                logo: `${window.RV_CONFIGS.plugin_url}/public/images/virus.svg`,
                leftDrawerOpen: false,
                navs: [{
                        label: "Thống kê của bạn",
                        to: "/",
                        icon: "favorite_border",
                        login: false
                    },
                    {
                        label: "Lịch sử số dư",
                        to: "/lich-su",
                        icon: "fingerprint",
                        login: "all"
                        // children: []
                    },
                    {
                        label: "Thanh toán",
                        to: "/thanh-toan",
                        icon: "credit_card",
                        login: false
                    },
                    {
                        label: "Banner CTV",
                        to: "/banner",
                        icon: "photo_library",
                        login: false
                    },
                    {
                        label: "Tài khoản của bạn",
                        to: "/tai-khoan",
                        icon: "person",
                        login: false
                    },
                    {
                        label: "Công cụ hỗ trợ",
                        to: "/cong-cu",
                        icon: "gesture",
                        login: false
                    }
                ],
                user: {
                    balance: 0,
                    display_name: ''
                },

            }
        },
        methods: {
            setPageTitle(title) {
                this.page_title = title
            },
            async getUserProfile(id = '') {
                const res = await getUserProfile(id)
                const {
                    data
                } = res.data
                console.log(data);
                if (data)
                    this.user = data
            },
            getC() {
                const {
                    RV_CONFIGS
                } = window
                this.configs = RV_CONFIGS
            },
            getLoggedInfo() {
                this.getC();
                this.getUserProfile()
                this.getConfigs().then(res => {
                    this.settings = res
                    this.navs = this.isNormal() ? route.normal : route.pro

                })
            },
            async logout() {
                const confirm = await this.CONFIRM('Bạn chắc chắn muốn đăng xuất')
                if (!confirm)
                    return
                await logout()
                window.RV_CONFIGS.user_id = ''
                window.RV_CONFIGS.user_login = ''
                this.configs.user_id = ''
                this.configs.user_login = ''
                this.$router.push('/dang-nhap')
            },
        },
        components: {

        },
        created() {

            document.querySelector('#q-app').classList.remove('d-none')

            this.getConfigs().then(res => {
                this.settings = res
                this.navs = this.isNormal() ? route.normal : route.pro
            })
            this.getC();



            if (!this.configs.user_id && this.$route.path != '/dang-nhap' && this.$route.path != '/dang-ky') {
                console.log(this.$route.path);
                this.$router.push('/dang-nhap')
            }

            if (this.$route.path == '/dang-nhap' && this.configs.user_id)
                this.$router.push('/')

            if (this.configs.user_id)
                this.getLoggedInfo()

            EventBus.$on('set.page_title', this.setPageTitle);
            EventBus.$on('user.refresh', this.getUserProfile);
            EventBus.$on('user.login', this.getLoggedInfo);

        }
        // 
    });
    </script>

</body>

</html>