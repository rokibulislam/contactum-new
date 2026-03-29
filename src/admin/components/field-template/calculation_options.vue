<template>
        <div class="contactum-calculation-options" v-if="editfield.enable_calculation">
            <label>Expression Builder </label>
            <div class="contactum">
                <div class="contactum_calculation_buttons">
                    <div class="contactum_calculation_fields">
                        <merge_tags :calculate="true" :fieldsonly="true" v-on:insert="insertOperand" :formFields="calculableFields"> </merge_tags>
                    </div>
                </div>
            </div>

            <textarea id="contactum_calculation_formula_field" v-model="formula_field" style="width: 100%; height: 60px">{{ formula_field }}</textarea>

            <a href="#"  @click.prevent="validateExpression"> Validate Expression </a>
        </div>
</template>


<script>

import option_field from "../../mixin/option-field.js";
import merge_tags from "../merge-tags/index.vue";
import { evaluate } from 'mathjs'

export default {
    name: "field_calculation_options",
    mixins: [option_field],
    components: {
        merge_tags
    },
    data: function data() {
        return {
            type: null,
            fieldsonly: true
        };
    },
    computed: {
        formula_field: {
            get() {
                return this.editfield.formula_field;
            },
            set(val) {
                this.$set(this.editfield, 'formula_field', val);
            }
        },

        form_fields: {
            get: function () {
                return this.$store.getters.form_fields;
            },
            set: function (value) {
                this.$store.dispatch("set_form_fields", value);
            },
        },

        calculableFields() {
            console.log( this.form_fields );
            const fields = this.form_fields.filter(
                field => field.calc_value == true
            );
            console.log(fields);
            return fields;
        }
    },

    methods: {

        insertOperator: function insertOperator(value) {
            return this.formula_field += value;
        },

        insertOperand: function insertOperand(val1, val2) {
            return this.formula_field += val2;
        },

        validateExpression: function validateExpression() {
            var scope = {};
            for (var i = 0; i < this.calculableFields.length; i++) {
                var index = this.calculableFields[i].name;
                scope[index] = 1;
            }
            
            try {
                var expr = this.formula_field.split('%').join('');
                var result = evaluate(String(expr), scope);
                if (result) {
                    alert("valid");
                } else {
                    alert('invalid')
                }
            } catch (error) {
                alert("invalid");
            } 
        }
    },

    watch: {

    }
}

</script>

<style>

.contactum_calculation_fields {
    position: relative;

    label{
        margin-bottom: 10px;
    }
}
</style>