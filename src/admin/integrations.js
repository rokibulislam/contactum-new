import Vue from 'vue';
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css';
import 'element-ui/lib/theme-chalk/icon.css';

import lang from 'element-ui/lib/locale/lang/en'
import locale from 'element-ui/lib/locale'
// configure language
locale.use(lang);


import {
    Switch,
    Row,
    Col,
    RadioGroup,
    RadioButton,
    Input,
    Button
}  from 'element-ui';

Vue.use(Switch)
Vue.use(Row)
Vue.use(Col)
Vue.use(RadioGroup)
Vue.use(RadioButton)
Vue.use(Input)
Vue.use(Button)
import integration  from "./pages/integration.vue";

let app = new Vue({
    el: '#contactum-admin-integration',
    render: (h) => h(integration)
});
