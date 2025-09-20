import Vue from 'vue';
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css';
import 'element-ui/lib/theme-chalk/icon.css';

import lang from 'element-ui/lib/locale/lang/en'
import locale from 'element-ui/lib/locale'
// configure language
locale.use(lang);


import {
    Switch
}  from 'element-ui';

Vue.use(Switch)

import Module  from "./pages/Module.vue";

let app = new Vue({
    el: '#contactum-admin-module',
    render: (h) => h(Module)
});
