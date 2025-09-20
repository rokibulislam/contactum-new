import Vue from 'vue';
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

import Payment from './pages/Payment.vue'

let app = new Vue({
    el: '#contactum-admin-payment-entries',
    render: (h) => h(Payment)
});
