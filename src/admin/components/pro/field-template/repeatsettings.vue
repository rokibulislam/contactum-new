<template>
    <div class="panel-field">

        <label class="contactum-label"> Repeat Field Settings
        <i class="el-icon-caret-bottom el-icon-clickable pull-right" ></i>

        </label>

        <div class="address-field-option" v-for="(innerfield, i) in inner_fields" :key="i">

            <i class="el-icon-circle-plus-outline" @click.prevent="add_field"></i>         
        <i class="el-icon-remove-outline" @click="delete_field(i)"></i>


            <div class="repeat-field-option__settings address-input-fields" >
                
                <div class="panel-field">
                    <label class="contactum-label"> Field Type </label>
                    <el-select v-model="innerfield.fields">
                        <el-option v-for="(element,elementName) in available_elements" :key="elementName" :value="elementName" :label="element"></el-option>
                    </el-select>
                </div>

                <div class="panel-field">
                    <label class="contactum-label"> Label </label>
                    <el-input v-model="innerfield.field_properties.title"> </el-input>
                </div>

                <div class="panel-field">
                    <label class="contactum-label"> Placeholder </label>
                    <el-input v-model="innerfield.field_properties.placeholder"> </el-input>
                </div>

                <div v-if="fields == 'form_dropdown_field'" class="panel-field">
                    <label class="contactum-label"> Options </label>
                    <field_option_data :editfield="innerfield.field_properties" :field="innerfield" />
                </div>
                
                <div v-if="fields == 'form_text_field' || fields == 'form_number_field' || fields == 'form_email_address' " class="panel-field">
                    <label class="contactum-label"> Default </label>
                    <el-input v-model="innerfield.field_properties.default"> </el-input>
                </div>

                <div class="panel-field">
                    <label class="contactum-label"> Required </label>
                    <ul class="list-inline">
                        <li>
                            <label>
                                <el-radio :label="true" v-model="innerfield.field_properties.required"> True </el-radio>
                                <el-radio :label="false" v-model="innerfield.field_properties.required"> false  </el-radio>
                            </label>
                        </li>
                    </ul>
                </div>

                <div v-if=" editfield.field_properties.required == true " class="panel-field"> 
                    <label class="contactum-label"> Error Message: </label>
                    <el-input v-model="innerfield.field_properties.message"></el-input>
                </div>

            </div>


        </div>
        

<!--        <div class="repeat-field-option__settings address-input-fields" >-->
<!--            -->
<!--            <div class="panel-field">-->
<!--                <label class="contactum-label"> Field Type </label>-->
<!--                <el-select v-model="fields">-->
<!--                    <el-option v-for="(element,elementName) in available_elements" :key="elementName" :value="elementName" :label="element"></el-option>-->
<!--                </el-select>-->
<!--            </div>-->

<!--            <div class="panel-field">-->
<!--                <label class="contactum-label"> Label </label>-->
<!--                <el-input v-model="editfield.field_properties.title"> </el-input>-->
<!--            </div>-->

<!--            <div class="panel-field">-->
<!--                <label class="contactum-label"> Placeholder </label>-->
<!--                <el-input v-model="editfield.field_properties.placeholder"> </el-input>-->
<!--            </div>-->

<!--            <div v-if="fields == 'form_dropdown_field'" class="panel-field">-->
<!--                <label class="contactum-label"> Options </label>-->
<!--                <field_option_data :editfield="editfield.field_properties" :field="editfield" />-->
<!--            </div>-->
<!--            -->
<!--            <div v-if="fields == 'form_text_field' || fields == 'form_number_field' || fields == 'form_email_address' " class="panel-field">-->
<!--                <label class="contactum-label"> Default </label>-->
<!--                <el-input v-model="editfield.field_properties.default"> </el-input>-->
<!--            </div>-->

<!--            <div class="panel-field">-->
<!--                <label class="contactum-label"> Required </label>-->
<!--                <ul class="list-inline">-->
<!--                    <li>-->
<!--                        <label>-->
<!--                            <el-radio :label="true" v-model="editfield.field_properties.required"> True </el-radio>-->
<!--                            <el-radio :label="false" v-model="editfield.field_properties.required"> false  </el-radio>-->
<!--                        </label>-->
<!--                    </li>-->
<!--                </ul>-->
<!--            </div>-->

<!--            <div v-if=" editfield.field_properties.required == true " class="panel-field"> -->
<!--                <label class="contactum-label"> Error Message: </label>-->
<!--                <el-input v-model="editfield.field_properties.message"></el-input>-->
<!--            </div>-->

<!--        </div>-->

    </div>

</template>

<script>

import option_field from "../../../mixin/option-field.js";
import field_option_data from '../../field-template/option-data.vue'
import field_required from '../../field-template/required.vue'

export default {
    name: "field_repeatsettings",
    mixins: [option_field],
    components: {
        field_option_data,
        field_required
    },
    data: function() {
        return {
            available_elements: {
                'form_text_field': 'Text Field',
                'form_email_address': 'Email Field',
                'form_number_field': 'Numeric Field',
                'form_dropdown_field': 'Select Field'
            }
        }
    },
    computed: {
        inner_fields: {
             get: function() {
                return this.editfield.inner_fields;
            }
        },
        fields: {
            get: function() {
                return this.editfield['fields'];
            },
            set: function(value) {
                this.$store.dispatch("update_editing_form_field", {
                    id: this.editfield.id,
                    property: 'fields',
                    value: value
                });
            }
        }
    },
    methods: {
        add_field: function() {

            if( this.inner_fields.length > 0 ) {

                let clonefield = JSON.parse(JSON.stringify(this.inner_fields[0]));

                this.inner_fields.push({
                    ...clonefield
                });
            }



            this.$store.dispatch("update_editing_form_field", {
                id: this.editfield.id,
                property: "inner_fields",
                value: this.inner_fields,
            });
        },

        delete_field: function(index) {
             this.inner_fields.splice(index, 1);
    this.$store.dispatch("update_editing_form_field", {
        id: this.editfield.id,
        property: "inner_fields",
        value: this.inner_fields
    });
        }
    }
}

</script>


<style scoped>
    .panel-field-address {
        display: block;
    }

    .address-fields {
      margin-top: 10px;
    }

    .address-fields li {
      background: #fff;
      padding: 10px;
      margin-bottom: 10px;
    }

    .address-input-fields {
      background: #797979;
      padding: 10px;
      color: #fff;
    }

    .panel-child-field {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
      gap: 10px;
    }

    .panel-field-option-select label {
        display: block;
        margin-bottom: 5px;
    }

    .panel-field-option-select .panel-field-btn-group {
        display: flex;
        justify-content: space-between;
    }


    .address-field-option {
      margin-bottom: 10px;
      padding-bottom: 10px;
      width: 100%
    }

    .address-field-option .el-icon-caret-top,.address-field-option>.el-icon-caret-bottom {
      border-radius: 3px;
      font-size: 18px;
      margin-top: -2px;
      padding: 2px 4px;
      transition: .2s
    }

    .address-field-option .el-icon-caret-top:hover,.address-field-option>.el-icon-caret-bottom:hover,.address-field-option>.el-icon-caret-top {
      background-color: #f2f2f2
    }

    .address-field-option__settings {
      position: relative
    }

    .address-field-option__settings:after {
      background-color: #f2f2f2;
      content: "";
      height: 10px;
      left: 20px;
      position: absolute;
      top: -5px;
      transform: rotate(134deg);
      width: 10px
    }

    .address-field-option__settings.is-open {
      background: #f2f2f2;
      border-radius: 6px;
      padding: 15px
    }

</style>
