import Vue from 'vue';
import 'element-ui/lib/theme-chalk/index.css';
import 'element-ui/lib/theme-chalk/icon.css';

import lang from 'element-ui/lib/locale/lang/en'
import locale from 'element-ui/lib/locale'
locale.use(lang);

import {
    Button,
    Collapse,
    CollapseItem,
    Tooltip,
    Message,
} from 'element-ui';

Vue.use(Button);
Vue.use(Collapse);
Vue.use(CollapseItem);
Vue.use(Tooltip);

Vue.prototype.$message = Message;

import Support from './pages/Support.vue'

new Vue({
    el: '#contactum-admin-support',
    render: (h) => h(Support)
});
