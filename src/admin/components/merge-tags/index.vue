<template>
    <div class="merge-tag-wrapper">
      <el-button type="" icon="el-icon-more" v-popover:input-popover />
      <el-popover
          ref="input-popover"
          :placement="placement"
          width="200"
          popper-class="el-dropdown-list-wrapper"
          trigger="click">
            <template >
                <ul class="el-dropdown-menu el-dropdown-list" >
                  <template v-if="form_fields.length > 0">
                    <span class="group-title"> Form Fields </span>
                    <li v-for="field in form_fields">
                      <template v-if="field.template === 'name_field'">
                        <li class="el-dropdown-menu__item">  <a href="#" @click.prevent="insertField('name-full', field.name);">{{ field.label }}</a> </li>
                        <li class="el-dropdown-menu__item"> <a href="#" @click.prevent="insertField('name-first', field.name);">first</a>  </li>
                        <li class="el-dropdown-menu__item"> <a href="#" @click.prevent="insertField('name-middle', field.name);">middle</a> </li>
                        <li class="el-dropdown-menu__item"> <a href="#" @click.prevent="insertField('name-last', field.name);">last</a> </li>
                      </template>

                      <template v-else-if="field.template === 'image_upload'">
                        <li class="el-dropdown-menu__item"> <a href="#" @click.prevent="insertField('image', field.name);">{{ field.label }}</a> </li>
                      </template>

                      <template v-else-if="field.template === 'file_upload'">
                        <li class="el-dropdown-menu__item"> <a href="#" @click.prevent="insertField('file', field.name);">{{ field.label }}</a> </li>
                      </template>

                      <template v-else>
                        <li class="el-dropdown-menu__item"> <a  href="#" @click.prevent="insertField('field', field.name);">{{ field.label }} </a> </li>
                      </template>

                    </li>
                  </template>

                    <li v-for="smarttag in smart_tags" v-if="!calculate">
                      <span class="group-title">{{ smarttag.title }}</span>
                        <ul v-if="smarttag.tags">
                            <li class="el-dropdown-menu__item" v-for="(tag,index)  in smarttag.tags" @click.prevent="insertField(index)" :key="index"> {{ tag }} </li>
                        </ul>
                    </li>
                </ul>
            </template>
      </el-popover>
    </div>
</template>

<script type="text/javascript">
    export default {
        name: 'merge_tags',
        props: {
            field: [String, Number,Object],
            name: {
                type: [String, Number,Object],
                default: null
            },
            filter: {
                type: String,
                default: null
            },
            fieldsonly: {
                type: Boolean,
                default: false
            },
            formFields: {
              type: Array,
              default: null
            },
            calculate: {
              type: Boolean,
              default: false
            }
        },
        data: function() {
            return {
                isHidden: true,
                placement: 'bottom',
            }
        },
        computed: {
          smart_tags: function() {
            return this.$store.state.smart_tags;
          },

            form_fields: function () {
              var template = this.filter,
                  // fields = this.$store.state.form_fields;
                  fields = Array.isArray(this.formFields)
                  ? this.formFields
                  : this.$store.state.form_fields;

              if (template !== null) {
                return fields.filter(function(item) {
                  return item.template === template;
                });
              }

              // remove the action/hidden fields
              return fields.filter(item => !['action_hook', 'custom_hidden_field'].includes(item.template));
            },
        },
        methods: {
            toggleFields: function(event) {

            },
            insertField: function(tag, field) {
              if( this.name == null ) {
                this.$emit('insert', tag, field, this.field);
              } else {
                this.$emit('insert', tag,field, this.name,this.field);//
              }
            }
        }
    }
</script>

<style type="text/css">
.mergetag_link{
    position: absolute;
    right: 5px;
    top: 30px;
    color: #999;
}

.merge-tag-wrapper {
    display: block;
    position: absolute;
    right: 0px;
    top: 0px;
    z-index: 1000;
}

/*

.merge-tags ul li h4 {
    padding: 0;
    margin: 0;
    background: #f0f0f0;
    width: 100%;
    padding: 2px;
    text-align: center;
}
*/
.merge-tags ul li ul li {
    cursor: pointer;
}

.group-title {
  background-color: #4b4c4d;
  color: #fff;
  display: block;
  padding: 5px 10px
}

.el-dropdown-menu {
  left: unset;
  max-width: 270px;
}

.el-dropdown-list {
  border: 0;
  box-shadow: none;
  margin: 0;
  max-height: 280px;
  min-width: auto;
  overflow-y: scroll;
  padding: 0;
  position: static;
  z-index: 10
}

.el-dropdown-list .el-dropdown-menu__item {
  border-bottom: 1px solid #f1f1f1;
  font-size: 13px;
  line-height: 18px;
  padding: 4px 10px
}

.el-dropdown-list .el-dropdown-menu__item:last-of-type {
  border-bottom: 0
}

.el-popper[x-placement^=bottom] .popper__arrow {
  top: -7px
}



</style>
