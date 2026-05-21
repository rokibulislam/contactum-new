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
    Tooltip,
    Form,
    FormItem,
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
    SkeletonItem, Dialog, MessageBox, Card,
} from 'element-ui';

Vue.use(Card);
Vue.use(Tooltip);
Vue.use(Form);
Vue.use(FormItem)
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
Vue.use(Loading);

Vue.prototype.$message = Message
Vue.prototype.$confirm = MessageBox.confirm;
Vue.prototype.$prompt = MessageBox.prompt;
Vue.prototype.$notify = Notification

import FormSettings from './components/settings/FormSettings.vue'
import PaymentSettings from './components/settings/PaymentSettings.vue'
import reCaptcha from './components/settings/reCaptcha.vue'
import hCaptcha from './components/settings/hCaptcha.vue'
import turnstile from './components/settings/turnstile.vue'
import Coupon from './components/settings/coupon.vue'
import GeneralIntegrationSettings from './components/settings/GeneralIntegrationSettings.vue'
import store from './store';

const components = {
   // settings: FormSettings,
    coupon: Coupon,
    PaymentSettings: PaymentSettings,
    reCaptcha: reCaptcha,
    hCaptcha: hCaptcha,
    turnstile: turnstile,
    'general-integration-settings': GeneralIntegrationSettings,
};

import { scrollTop, handleSidebarActiveLink, handleSidebarSettingsActiveLink } from './helpers';


/*
const app = new Vue({
    data() {
        return {
          componentName: '',
          setting_key: ''
        };
    },
    components: components,
    store,
    template: `
    <div>
        <component :is="componentName" :setting_key="setting_key" :key="setting_key"></component>
    </div>
    `,
    methods: {
        setRoute($el, $originalEl = false) {
            var self;
            let hash = $el.data('hash');
            this.setting_key = hash;
            let component = '';
            if ($el.data('component')) {
                component = $el.data('component');
            }
            if (this.$options.components[component]) {
                this.componentName = component;
                location.hash = hash;
            }
        },
        maybeGetFirstSubLink($el) {
            if (
                $el.attr('href') === '#' &&
                $el.parent().hasClass('has_sub_menu') &&
                $el.parent().find('ul.contactum_list_submenu li:first a').length
            ) {
                $el = $el.parent().find('ul.contactum_list_submenu li:first a');
            }
            return $el;
        },

        loadFromHash() {
            let hash = location.hash.substr(1) || 'google_recaptcha';
            let $el;
            $el = jQuery('.contactum_settings_list li').find('a[data-hash=' + hash + ']').first();
            if ($el.length) {
                $el = this.maybeGetFirstSubLink($el);
                this.setRoute($el);
                handleSidebarActiveLink($el.parent(), true , true);
            }
        }
    },
    created() {
        this.loadFromHash();
        window.addEventListener('hashchange', this.loadFromHash);
        let $el;
        const that = this;
        jQuery('.contactum_settings_list li a').on('click', function (e) {
            $el = jQuery(this);
            if($el.attr('href') === '#') e.preventDefault();
            if (that.setRoute(that.maybeGetFirstSubLink($el), $el) === 'redirected') {
                return;
            }
            handleSidebarActiveLink($el.parent())
        });
      }
}).$mount('#contactum-admin-settings',);


*/



const app = new Vue({
  data() {
    return {
      componentName: '',
      setting_key: ''
    };
  },

  components: components,
  store,

  template: `
    <div>
      <component
        :is="componentName"
        :setting_key="setting_key"
        :key="setting_key"
      />
    </div>
  `,

  methods: {
    setRoute($el) {
      const hash = $el.data('hash');
      const component = $el.data('component');

      this.setting_key = hash;

      if (this.$options.components[component]) {
        this.componentName = component;
        location.hash = hash;
      }
    },

    maybeGetFirstSubLink($el) {
      if (
        $el.attr('href') === '#' &&
        $el.parent().hasClass('contactum-settings__menu-item--has-submenu')
      ) {
        const $firstSub = $el
          .parent()
          .find('.contactum-settings__submenu li:first a');

        if ($firstSub.length) {
          return $firstSub;
        }
      }

      return $el;
    },

    loadFromHash() {
      const hash = location.hash.substr(1) || 'google_recaptcha';

      const $el = jQuery(
        `.contactum-settings__menu a[data-hash="${hash}"]`
      ).first();

      if ($el.length) {
        const $finalEl = this.maybeGetFirstSubLink($el);
        this.setRoute($finalEl);
        handleSidebarSettingsActiveLink($finalEl);
      }
    }
  },

  created() {
    this.loadFromHash();
    window.addEventListener('hashchange', this.loadFromHash);

    const that = this;

    jQuery('.contactum-settings__menu').on(
      'click',
      'a',
      function (e) {
        const $el = jQuery(this);

        if ($el.attr('href') === '#') {
              e.preventDefault();
        }

        const $target = that.maybeGetFirstSubLink($el);
        that.setRoute($target);

        handleSidebarSettingsActiveLink($target);
      }
    );
  }
}).$mount('#contactum-admin-settings');

