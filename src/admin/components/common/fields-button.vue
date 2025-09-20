
<template>
  <div>
  <ul class="panel-form-field-buttons">

    <li
        v-for="(field, index) in fields"
        :key="index"
        :data-form-field="field"
        data-source="panel"
        class="panel-button"
        :class="{
          'button-faded': is_pro_feature(field) || is_failed_to_validate(field)
        }"
        @click="handleClick(field)"
    >
      <i
          v-if="field_settings[field].icon"
          :class="['fa', 'fa-' + field_settings[field].icon]"
          aria-hidden="true"
      ></i>
      {{ field_settings[field].title }}
    </li>
<!--    -->
<!--    <li-->
<!--        v-if="is_failed_to_validate(field)"-->
<!--        v-for="(field, index) in fields"-->
<!--        @click="alert_invalidate_msg(field)"-->
<!--        class="panel-button button-faded"-->
<!--        :key="index"-->
<!--        :data-form-field="field"-->
<!--        data-source="panel"-->
<!--    >-->
<!--      <i v-if="field_settings[field].icon" :class="['fa fa-' + field_settings[field].icon]" aria-hidden="true"></i>-->
<!--      {{ field_settings[field].title}}-->
<!--    </li>-->

<!--  <li-->
<!--      v-if="!is_pro_feature(field) && !is_failed_to_validate(field)"-->
<!--      v-for="(field, index) in fields"-->
<!--      @click="add_field(field)"-->
<!--      class="panel-button"-->
<!--      :key="index"-->
<!--      :data-form-field="field"-->
<!--      data-source="panel"-->
<!--  >-->
<!--    <i v-if="field_settings[field].icon" :class="['fa fa-' + field_settings[field].icon]" aria-hidden="true"></i>-->
<!--    {{ field_settings[field].title}} </li>-->
<!--  <li-->
<!--      v-if="is_pro_feature(field)"-->
<!--      v-for="(field, index) in fields"-->
<!--      @click="alert_pro_feature(field)"-->
<!--      class="panel-button button-faded"-->
<!--      :key="index"-->
<!--      :data-form-field="field"-->
<!--  >-->
<!--    <i v-if="field_settings[field].icon" :class="['fa fa-' + field_settings[field].icon]" aria-hidden="true"></i>-->
<!--    {{ field_settings[field].title}}-->
<!--  </li>-->
  </ul>

    <ProFeature
        :visibility.sync="whyDisabledModal"
        :modal="model">
    </ProFeature>

  </div>
</template>


<script>
import {v4 as uuidv4} from "uuid";
import ProFeature from "../dialog/ProFeature.vue";

export default {
  name: "fields_button",
  components: {ProFeature},
  data() {
    return {
      whyDisabledModal: false,
      model: {
        video: '',
        image: '',
        title: '',
        description: '',
        hidePro: false
      }
    }
  },
  props: {
    fields: {
      type: Array,
      required: true
    }
  },
  computed: {
    field_settings: function() {
      return this.$store.getters.field_settings;
    },
    form_fields: function() {
      return this.$store.getters.form_fields;
    }
  },
  mounted: function() {
    // bind jquery ui draggable
    jQuery(this.$el)
        .find(".panel-form-field-buttons .panel-button")
        .draggable({
          connectToSortable:
              ".form-preview-stage .contactum-form, .contactum-column-inner-fields .contactum-column-fields-sortable-list",
          helper: "clone",
          revert: "invalid",
          cancel: ".button-faded",
          start(event, ui) {
          }
        })
        .disableSelection();
  },

  methods: {

    handleClick(field) {
      if (this.is_failed_to_validate(field)) {
        this.alert_invalidate_msg(field);
      } else if (this.is_pro_feature(field)) {
        this.alert_pro_feature(field);
      } else {
        this.add_field(field);
      }
    },

    alert_invalidate_msg(field) {
      console.log(" alert invalidate");
      let validator = this.field_settings[field].validator;

      if (validator && validator.msg) {
        this.$alert(
validator.msg,
  {
    confirmButtonText: 'OK',
    dangerouslyUseHTMLString: true, // allows HTML in message
    type: 'info',                   // icon: success, warning, info, error
    center: true                     // optional: center text
  }
);

      /*
        this.$swal({
          title: validator.msg_title || '',
          html: validator.msg,
          type: 'warning',
          showCancelButton: false,
          confirmButtonColor: '#46b450',
          confirmButtonText: 'OK'
        });

        */
      }
    },

    alert_pro_feature: function alert_pro_feature(field) {
      var title = this.field_settings[field].title;
      var description = this.field_settings[field]?.description;
      this.whyDisabledModal = true;
      this.model.title = title;
      this.model.description = description

      /*

      swal({
        title: '<i class="fa fa-lock"></i> ' + title + ' <br>' + window.contactum.i18n.is_a_pro_feature,
        text: window.contactum.i18n.pro_feature_msg,
        type: '',
        showCancelButton: true,
        cancelButtonText: window.contactum.i18n.close,
        confirmButtonColor: '#46b450',
        confirmButtonText: window.contactum.i18n.upgrade_to_pro
      }).then(function (is_confirm) {
        if (is_confirm) {
          
        }
      }, function () {});

       */

    },

    add_field(field_template) {
      let payload = {
        ...this.field_settings[field_template].field_props,
        id: uuidv4()
      };

      if (!payload.name && payload.label) {
        payload.name = payload.label.replace(/\W/g, "_").toLowerCase();

        var same_template_fields = this.form_fields.filter(form_field => {
          return form_field.template === field_template;
        });

        if (same_template_fields.length) {
          payload.name += "_" + same_template_fields.length;
        }
      }

      this.$store.dispatch("add_field", payload);
    },
  }
};
</script>


<style scoped>

.field-panel li ul {
  padding-left: 10px;
  padding-right: 10px;
}

.panel-form-field-buttons li {
  display: inline-block;
  width: 47%;
  /* width: 125px; */
  margin-right: 10px;
  text-align: center;
  padding: 10px 15px;
  box-sizing: border-box;
  cursor: move;
  color: #000;
  background: #fff;
  font-weight: 500;
  box-shadow: 0 1px 2px 0 #d9d9da;
  margin-bottom: 15px;
  transition: background .5s ease;
  border-radius: 8px;
  font-size: 14px;
  line-height: 26px;
  border: 1px solid #dfdfdf;
}

ul.panel-form-field-buttons li:hover{
  /* background: #7e3bd0; */
  background: #409EFF;
  color: #fff;
}

ul.panel-form-field-buttons .button-faded {
  opacity: .5;
}
</style>