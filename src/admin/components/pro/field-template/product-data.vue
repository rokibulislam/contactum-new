 <template>
  <div>
    <div>
      <label class="contactum-label">Product Display Type</label>
      <el-radio-group v-model="select_type">
        <el-radio-button label="checkbox"></el-radio-button>
        <el-radio-button label="radio"></el-radio-button>
        <el-radio-button label="select"></el-radio-button>
      </el-radio-group>
    </div>

    <div>

      <ul style="display: flex;justify-content: space-between; margin-top: 10px">
        <li>{{ 'Payment Items' }}</li>
        <li> <el-checkbox v-model="editfield.photo_value" :label="photo_value"> {{ "Photo" }} </el-checkbox> </li>
      </ul>

      <ul :class="['field-options', 'option-field-swapper']" :key="select_type">
        <li v-for="(option, index) in options" :key="index" style="display: flex;" class="option-field-option">
          
          <div class="selector">
            <template v-if="select_type == 'radio' || select_type == 'select' ">
              <input type="radio"  v-model="selectedSingle" :value="option.name"/>
            </template>

            <template v-else-if="select_type == 'checkbox'">
              <input type="checkbox"  v-model="selectedMultiple" :value="option.name" />
            </template>
          </div>

          <div class="sort-handler">
            <!-- <i class="fa fa-bars"></i> -->
            <i class="el-icon-s-fold"></i>
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

            <i class="el-icon-circle-plus-outline" @click.prevent="add_option"></i>
            <i class="el-icon-remove-outline"  v-if="options.length > 1" @click="delete_option(index)"></i>
            
        </li>
      </ul>

      <div>
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
      selectedMultiple: [],
      selectedSingle: null,
      show_value: true,
      isInitialized: false
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
created() {
  this.set_options();
},
  methods: {

    set_options: function () {
      this.options = this.editfield.options;
      this.select_type = this.field_select_type;

      if (this.editfield.select_type == 'checkbox') {
        this.selectedMultiple = Array.isArray(this.editfield.selected)
          ? [...this.editfield.selected]
          : [];
          this.field_selected = this.selectedMultiple;
          this.selectedSingle = null;
      } else {
        this.selectedSingle = this.editfield.selected || null;
        this.field_selected = this.selectedSingle;
        this.selectedMultiple = [];
      }

      this.$nextTick(() => {
        this.isInitialized = true;
      });
    },

    add_option: function() {
      const numbers = this.options
      .map(opt => {
        const match = opt.name.match(/Product-(\d+)/);
        return match ? parseInt(match[1]) : 0;
      });

      const nextNumber = numbers.length ? Math.max(...numbers) + 1 : 1;

      const name = `Product-${nextNumber}`;

      this.options.push({
        name: name,
        price: 0,
        photo: "",
      });
    },

    delete_option: function(index) {
      this.options.splice(index, 1);
    },

    clear_selection: function () {
        this.selected = [];
        this.selectedSingle = null;
        this.selectedMultiple = [];
    },

  },
  watch: {
    select_type: function(newValue, oldValue) {
      if (!this.isInitialized) return;
      if (newValue === oldValue) return;

      this.selectedSingle = null;
      this.selectedMultiple = [];

      this.$store.dispatch("update_editing_form_field", {
        id: this.editfield.id,
        property: "select_type",
        value: newValue
      });

      this.$store.dispatch("update_editing_form_field", {
        id: this.editfield.id,
        property: "selected",
        value: newValue === "checkbox" ? [] : null
      });

    },

    selectedMultiple: function(new_value) {
      if (!this.isInitialized || this.select_type !== "checkbox") return;
      this.$store.dispatch("update_editing_form_field", {
        id: this.editfield.id,
        property: "selected",
        value: new_value
      });
    },


    selectedSingle: function(new_value) {
      if (!this.isInitialized || this.select_type === "checkbox") return;

      this.$store.dispatch("update_editing_form_field", {
        id: this.editfield.id,
        property: "selected",
        value: new_value
      });
    },
  }
};
</script>



 <style lang="scss" scoped>

 .field-option-actions {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-top: 6px;
  padding: 8px 12px;
  background: #f9fafb;
  border-radius: 6px;
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
  display: grid;
  grid-template-columns: 30px 24px auto 1fr 1fr 32px;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  background: #ffffff;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  margin-bottom: 8px;
 }

.option-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px;
  background: #f9fafb;
  border-radius: 8px;


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

 .selector input[type="checkbox"],
 .selector input[type="radio"] {
   transform: scale(1.2);
 }

 </style>
