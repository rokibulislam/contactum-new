import Vue from 'vue';
import VueRouter from 'vue-router'
Vue.use(VueRouter);
// import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css';
import 'element-ui/lib/theme-chalk/icon.css';

import lang from 'element-ui/lib/locale/lang/en'
import locale from 'element-ui/lib/locale'
// configure language
locale.use(lang);



import { 
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
    SkeletonItem
} from 'element-ui';

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

import Entries from './pages/Entries.vue'
import Entry from './pages/Entry.vue'

const routes = [
    {
        path: '/',
        name: 'form-entries',
        component: Entries,
        props: true
    },
    {
        path: '/forms/:form_id/entries/:entry_id',
     //   path: '/entries/:entry_id',
        name: 'form-entry',
        component: Entry,
        props: true
    }
];

const router = new VueRouter({
    linkActiveClass: 'active',
    routes
});



const app = new Vue({
    router
}).$mount('#contactum-admin-entries',);