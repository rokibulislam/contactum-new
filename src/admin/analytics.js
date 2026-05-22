import Vue from 'vue';
import VueRouter from 'vue-router';
Vue.use(VueRouter);

import 'element-ui/lib/theme-chalk/index.css';
import 'element-ui/lib/theme-chalk/icon.css';

import lang from 'element-ui/lib/locale/lang/en';
import locale from 'element-ui/lib/locale';
locale.use(lang);

import {
    Row, Col, Button, Select, Option,
    Table, TableColumn, DatePicker, Skeleton, SkeletonItem,
} from 'element-ui';

Vue.use(Row);
Vue.use(Col);
Vue.use(Button);
Vue.use(Select);
Vue.use(Option);
Vue.use(Table);
Vue.use(TableColumn);
Vue.use(DatePicker);
Vue.use(Skeleton);
Vue.use(SkeletonItem);

import Analytics from './pages/Analytics.vue';

const routes = [
    { path: '/', name: 'analytics', component: Analytics }
];

const router = new VueRouter({
    linkActiveClass: 'active',
    routes
});

new Vue({ router }).$mount('#contactum-admin-analytics');
