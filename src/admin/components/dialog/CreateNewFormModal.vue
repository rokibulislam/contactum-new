<template>
  <el-dialog :visible.sync="visibility" :before-close="cancelNewForm">

    <template slot="title">
      
      <div class="el-dialog__header_group">
          <h4 class="mr-3"> Create A New Form </h4>
          <el-button size="medium" @click="showFormsImport = !showFormsImport" type="info"
                      :class="{'el-button--soft': !showFormsImport}"> Import Form
              &nbsp;<i v-if=" showFormsImport" class="el-icon-circle-close"></i>
          </el-button>
      </div>

      <transition name="slide-down">
          <import-forms class="import-forms-section mt-4" v-if="showFormsImport" @forms-imported="updateFormsImported" :app="{forms:[]}"/>
      </transition>
  </template>

    <div class="contactum-form-template-modal">
            <div class="content">
              <ul>
                <li v-for="(template, key, index ) in templates" :class="[{ 'template-active': template.enabled, 'template-inactive': !template.enabled }]">
                  <h3> {{ template.title }} {{ key }} </h3>
                  <img :src="template.image" />
                  <div class="form-create-overlay">
                    <div class="title"> {{ template.title }}</div>
                    <div class="description">{{ template.description }}</div>
                    <a  :href="buildTemplateUrl(key)" class="btn btn-submit"> Create form </a>
                  </div>
                </li>
              </ul>
            </div>
      </div>
  </el-dialog>
</template>

<script>

import ImportForms from '../../pages/tools/ImportForms.vue';

export default{
  name: "CreateNewFormModal",
  components: {
      ImportForms
  },
  data() {
    return {
      url : window.contactum.url,
      showFormsImport: false,
      formsImported : false,
      templates: window.contactum.templates,
    }
  },
  mounted() {

  },
  props: {
    visibility: Boolean,
   // templates: Array,
  },
  methods: {
    cancelNewForm() {
      this.$emit("close");
    },
    createForm(formType, form) {
      
    },
    updateFormsImported(value) {
      console.log()
      this.formsImported = value;
    },
    
    buildTemplateUrl(templateSlug) {
      const url = new URL(contactum.admin_url);
      url.searchParams.set('action', 'create_template');
      url.searchParams.set('template', templateSlug);
      url.searchParams.set('_wpnonce', contactum.nonce);
      return url.toString();
    }
  }
}
</script>

<style scoped lang="scss">

.el-dialog__header_group {
  display: flex;
  align-items: center;

  h4{
    margin-right: 15px;
    font-weight: 500;
    color: #1e1f21;
    font-size: 20px;
  }
}

.contactum-form-template-modal .content ul {
  display: flex;
  flex-wrap: wrap;
}

.contactum-form-template-modal .content ul li {
  text-align: center;
  min-height: 280px;
  width: 169px;
  border: 0px;
  box-shadow: 1px 2px 5px rgba(0, 0, 0, 0.1);
  border-radius: 3px;
  margin-bottom: 30px;
  margin-left: 30px;
  position: relative;

  & h3 {
    margin-top: 0;
    margin-bottom: 0;
    border: 0px;
    background: #0076FF;
    padding: 13px;
    font-weight: normal;
    font-size: 13px;
    color: #fff;
    border-radius: 3px 3px 0px 0px;
    text-align: left;
  }

  & .title {
    font-size: 17px;
    margin: 0 0 10px 0;
    line-height: 23px;
  }

  & li.template-active:hover img {
    display: none;
  }


  & li.template-active img,
  & li.template-inactive img {
    max-width: 100%;
    max-height: 211px;
  }

  & .form-create-overlay {
    position: absolute;
    display: none;
  }
/*
  & .form-create-overlay {
    display: block;
  }
*/
  & .description {
    color: #fff;
  }

  & li.template-inactive .title,
  & li.template-inactive .description {
    color: #ddd;
  }
}

.contactum-form-template-modal .content ul li:hover .form-create-overlay {
  animation: contactumFadeIn .25s;
  padding: 10px;
  text-align: center;
  display: flex;
  flex-direction: column;
  justify-content: center;
  background: rgba(0, 0, 0, 0.7);
  height: 100%;
  width: 100%;
  top: 0px;
  left: 0px;
  color: #fff;
  border-radius: 3px;
}


.btn-submit {
  background: #0076FF;
  padding: 10px 20px;
  color: #fff;
  border: none;
}


</style>