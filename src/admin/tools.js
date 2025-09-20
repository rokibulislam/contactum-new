import Vue from 'vue';
// import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css';
import 'element-ui/lib/theme-chalk/icon.css';

import lang from 'element-ui/lib/locale/lang/en'
import locale from 'element-ui/lib/locale'
locale.use(lang);

import {
    Button,
    Form,
    FormItem,
    Tooltip,
    Row,
    Col,
    Select,
    Option,
    Tabs,
    TabPane,
    Table,
    TableColumn,
    Pagination,
    Popover,
    Notification,
    Loading,
    Tag,
    Skeleton,
    SkeletonItem,
    DatePicker, Message, MessageBox,
} from 'element-ui';

Vue.use(Button);
Vue.use(Table);
Vue.use(TableColumn);
Vue.use(Form);
Vue.use(FormItem);
Vue.use(Tooltip);
Vue.use(Row);
Vue.use(Col);
Vue.use(Select);
Vue.use(Option);
Vue.use(Pagination);
Vue.use(Popover);
Vue.use(Tabs);
Vue.use(TabPane);
Vue.use(Tag);
Vue.use(Skeleton);
Vue.use(SkeletonItem);
Vue.use(DatePicker);

Vue.prototype.$message = Message
Vue.prototype.$confirm = MessageBox.confirm;
Vue.prototype.$prompt = MessageBox.prompt;
Vue.prototype.$notify = Notification

import Tools from './pages/Tools.vue'
import NewTools from './pages/NewTools.vue'

let app = new Vue({
    el: '#contactum-admin-tools',
    render: (h) => h(NewTools)
});
