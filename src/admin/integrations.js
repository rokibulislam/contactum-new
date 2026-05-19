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
    Button,
    Select,
    Option,
    Form,
    FormItem,
    Skeleton,
    SkeletonItem,
    Notification,
    Loading,
    Tooltip,
} from 'element-ui';

Vue.use(Switch);
Vue.use(Row);
Vue.use(Col);
Vue.use(RadioGroup);
Vue.use(RadioButton);
Vue.use(Input);
Vue.use(Button);
Vue.use(Select);
Vue.use(Option);
Vue.use(Form);
Vue.use(FormItem);
Vue.use(Skeleton);
Vue.use(SkeletonItem);
Vue.use(Loading);
Vue.use(Tooltip);

Vue.prototype.$notify = Notification;
import integration  from "./pages/integration.vue";

let app = new Vue({
    el: '#contactum-admin-integration',
    render: (h) => h(integration)
});
