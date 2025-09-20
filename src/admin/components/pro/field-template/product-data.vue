 <template>
  <div>
    <div>
      <label class="contactum-label">Product Display Type</label>
      <el-radio-group v-model="select_type">
        <el-radio-button label="checkbox"></el-radio-button>
        <el-radio-button label="radio"></el-radio-button>
      </el-radio-group>
    </div>

    <div>

      <ul style="display: flex;justify-content: space-between; margin-top: 10px">
        <li>{{ 'Payment Items' }}</li>
        <li> <el-checkbox v-model="editfield.photo_value" :label="photo_value"> {{ "Photo" }} </el-checkbox> </li>
      </ul>

      <ul :class="['field-options', 'option-field-swapper']">
        <li v-for="(option, index) in options" :key="index" style="display: flex;" class="option-field-option">
          <div class="selector">

            <template v-if="select_type == 'radio'">
              <input type="radio" :value="option.type" v-model="selected" />
            </template>

            <template v-else>
              <input type="checkbox" :value="option.type" v-model="selected" />
            </template>

          </div>

          <div class="sort-handler">
            <i class="fa fa-bars"></i>
          </div>

          <div v-if="editfield.photo_value == true ">
            <div class="ff_photo_card">
              <div class="wpf_photo_holder">
                <img
                    style="max-width: 100%"
                    v-if="option.photo"
                    :src="option.photo"
                    width="50px"
                />
                <div class="photo_widget_btn">
                  <i class="el-icon-upload" @click="initUploader(option)"> </i>
                  <i class="el-icon-delete" v-if="option.photo" @click="option.photo = ''"> </i>
                </div>
              </div>
            </div>
          </div>

          <div>
            <el-input v-model="option.name" />
          </div>

          <div>
            <el-input-number v-model="option.price"  />
          </div>

          <div>
            <i class="el-icon-remove-outline"  v-if="options.length > 1" @click="delete_option(index)"></i>
          </div>
        </li>
      </ul>

      <div>
        <i class="el-icon-circle-plus-outline" @click.prevent="add_option"></i>
        <el-button  type="danger" @click.prevent="clear_selection">{{ 'Clear Selection' }} </el-button>
      </div>
    </div>
  </div>
</template>

<script>
import option_field from "../../../mixin/option-field.js";
import media from "../../../mixin/media.js";
export default {
  name: "field_product_data",
  mixins: [option_field, media],
  data: function() {
    return {
      options: [],
      select_type: "radio",
      selected: "",
      show_value: true
    };
  },
  computed: {
    field_options: function() {
      return this.editfield.options;
    },
    field_selected: function() {
      return this.editfield.selected;
    },
    field_select_type: function() {
      return this.editfield.select_type;
    }
  },

  methods: {
    set_options: function() {
      this.options = this.editfield.options;
      this.selected = this.field_selected;
      this.select_type = this.field_select_type;
    },

    add_option: function() {
      let name = "Product-" + this.options.length;
      this.options.push({
        price: 10,
        name: name,
        photo: "",
      });
    },

    delete_option: function(index) {
      this.options.splice(index, 1);
    }

  },
  watch: {
    select_type: function(new_value) {
      this.$store.dispatch("update_editing_form_field", {
        id: this.editfield.id,
        property: "select_type",
        value: new_value
      });
    }
  }
};
</script>



 <style lang="scss" scoped>
 .field-option-actions {
   display: flex;
   justify-content: space-between;
 }

 .option-field-option i {
   align-items: center;
 }

 .field-options {
   margin-bottom: 5px;
   margin-top: 15px;
   & li {
     display: flex;
     justify-content: flex-start;
     gap: 20px;

     & div.option_label, & div.option_value {
       margin-right: 10px;
     }
   }
 }

 .option-field-option {
   display: flex;
   align-items: center;
   gap: 10px;
   padding: 8px 0;
   border-bottom: 1px solid #f0f0f0;
 }

 .option-actions {
   margin-top: 15px;
   display: flex;
   align-items: center;
   gap: 10px;

   & button {
     padding: 6px 10px;
   }
 }

 .option-field-swapper .sort-handler {
   width: 22px;
   cursor: grab;
   color: #888;
 }


 .ff_photo_card {
   width: 80px;
   height: 60px;
   position: relative;
   border: 1px solid #ccc;
   border-radius: 6px;
   overflow: hidden;
   display: flex;
   align-items: center;
   justify-content: center;

   & img {
     width: 100%;
     height: 100%;
     object-fit: cover;
   }
 }

 .photo_widget_btn {
   position: absolute;
   bottom: 4px;
   left: 4px;
   display: flex;
   gap: 4px;

   & i {
     font-size: 16px;
     cursor: pointer;
     color: #007bff;
     background: #fff;
     border-radius: 50%;
     padding: 2px;

     &:hover {
       color: #0056b3;
     }
   }
 }

 .el-icon-circle-plus-outline {
   font-size: 24px;
   color: #5cb85c;
   cursor: pointer;

   &:hover {
     color: #449d44;
   }
 }

 .el-icon-remove-outline {
   color: #d9534f;
   font-size: 20px;
   cursor: pointer;

   & :hover {
     color: #c9302c;
   }
 }

 .selector input[type="checkbox"],
 .selector input[type="radio"] {
   transform: scale(1.2);
 }

 </style>
