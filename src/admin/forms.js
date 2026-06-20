import Vue from 'vue';
import 'element-ui/lib/theme-chalk/index.css';
import 'element-ui/lib/theme-chalk/icon.css';
import lang from 'element-ui/lib/locale/lang/en'
import locale from 'element-ui/lib/locale'
locale.use(lang);

import '../styles/main.scss';

import {
    Dropdown,
    DropdownItem,
    Form,
    Row,
    Col,
    Button,
    ButtonGroup,
    RadioGroup,
    RadioButton,
    Input,
    Checkbox,
    Select,
    Option,
    Radio,
    Table,
    TableColumn,
    Switch,
    Pagination,
    Loading,
    Message,
    Notification,
    DatePicker,
    Skeleton,
    SkeletonItem,
    Dialog,
    MessageBox, DropdownMenu,
    FormItem,
    Tooltip,
    Card,
    Popover,
    InputNumber,
    Tag
} from 'element-ui';

Vue.use(Popover);
Vue.use(Card);
Vue.use(Tooltip)
Vue.use(FormItem);
Vue.use(DropdownItem);
Vue.use(Dropdown);
Vue.use(DropdownMenu)
Vue.use(Form);
Vue.use(Row);
Vue.use(Col);
Vue.use(Button);
Vue.use(ButtonGroup);
Vue.use(Input);
Vue.use(Switch);
Vue.use(Checkbox);
Vue.use(Pagination);
Vue.use(Select);
Vue.use(Option);
Vue.use(RadioGroup);
Vue.use(RadioButton);
Vue.use(Radio);
Vue.use(Table);
Vue.use(TableColumn);
Vue.use(DatePicker);
Vue.use(Skeleton);
Vue.use(SkeletonItem);
Vue.use(Dialog);
Vue.use(InputNumber)
Vue.use(Tag)


import VueSweetalert2 from 'vue-sweetalert2';
Vue.use(VueSweetalert2);

Vue.prototype.$message = Message
Vue.prototype.$confirm = MessageBox.confirm;
Vue.prototype.$prompt = MessageBox.prompt;
Vue.prototype.$alert = MessageBox.alert;
Vue.prototype.$notify = Notification

import AllForms from './pages/Form.vue'
import FormEntry from './pages/FormEntry.vue'
import FormSettings from './pages/FormSettings.vue'
import FormNotification from './pages/FormNotification.vue'
import Builder from './components/builder/index.vue'
import store from './store';
import menuFix from './utils/admin-menu-fix';

Vue.config.productionTip = false;

Vue.prototype.$eventHub = new Vue();
window.Vue = Vue;

if (!Array.prototype.hasOwnProperty('swap')) {
    Array.prototype.swap = function (from, to) {
        this.splice(to, 0, this.splice(from, 1)[0]);
    };
}


Vue.mixin({
    methods: {
        is_failed_to_validate: function(template) {

            let validator = this.field_settings[template] ? this.field_settings[template].validator : false;

            if (validator && validator.callback && !this[validator.callback]()) {
                return true;
            }

            return false;
        },

        is_pro_feature: function is_pro_feature(template) {
            return this.field_settings[template] && this.field_settings[template].pro_feature ? true : false;
        },

        is_recaptcha_v2: function() {
            return contactum.recaptcha_type === 'v2';
        },

        has_recaptcha_api_keys: function() {
            return (contactum.reCaptcha.siteKey && contactum.reCaptcha.secretKey ) ? true : false;
        },

        has_hcaptcha_api_keys: function() {
            return ( window.contactum.hCaptcha.siteKey && window.contactum.hCaptcha.secretKey ) ? true : false;
        },

        has_turnstile_api_keys: function() {
            return (contactum.turnstile.siteKey && contactum.turnstile.secretKey ) ? true : false;;
        },

        settings_taxonomy: function (form_field) {
            return this.$store.state.field_settings[form_field.name].settings;
        },
   
        has_gmap_api_key: function() {
            return ( contactum.gmap_key != '' ) ? true : false;
        },

        isSingleInstance: function(field_name) {
            var singleInstance = ['post_title', 'post_content', 'post_excerpt', 'featured_image',
                'user_login', 'first_name', 'last_name', 'nickname', 'user_email', 'user_url',
                'user_bio', 'password', 'user_avatar', 'taxonomy', 'humanpresence'];

            if ( jQuery.inArray(field_name, singleInstance) >= 0 ) {
                return true;
            }

            return false;
        },

        get_random_id: function() {
            var min = 999999,
                max = 9999999999;

            return Math.floor(Math.random() * (max - min + 1)) + min;
        },
    }
})


import VueRouter from "vue-router";
Vue.use(VueRouter);

const routes = [
    {
        path: '/',
        name: 'forms',
        component: AllForms,
        props: true
    },
    {
        path: '/forms/:id',
        name: 'form-edit',
        component: Builder,
        props: true
    },
    {
        path: '/forms/settings/:id',
        name: 'form-settings',
        component: FormSettings,
        props: true
    },
    {
        path: '/forms/notifications/:id',
        name: 'form-notification',
        component: FormNotification,
        props: true
    },
    {
        path: '/forms/entries/:form_id',
        name: 'form-entry',
        component: FormEntry,
        props: true
    }
];

const router = new VueRouter({
    linkActiveClass: 'active',
    routes
});



const app = new Vue({
     router,
     store
 }).$mount('#contactum-admin-forms',);