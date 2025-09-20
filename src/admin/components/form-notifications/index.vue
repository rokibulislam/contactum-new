<template>
  <div class="form-notification-wrap">
        <!-- Toolbar -->
    <div class="notification-toolbar">
      <el-button type="primary" icon="el-icon-plus"   @click.prevent="addNotification"> Add Notification  </el-button>
      <el-button type="info" icon="el-icon-arrow-left"  size="small" v-if="editing !== false " @click.prevent="back"> Back </el-button>
    </div>

    <div class="form-notifications">
          <!-- Notification Table -->
          <el-card shadow="hover" class="notification-table-card">
              <el-table  v-if="!editing" :data="notifications" border stripe style="width: 100%">
                <el-table-column prop="active" label="Status" width="180">
                  <template slot-scope="scope">
                    <span class="mr-3" v-if="scope.row.active"> Enabled </span>
                    <span class="mr-3 text-danger" v-else> Disabled </span>
                    <el-switch :width="40" @change="(val) => toggelNotification(scope.$index, val)" :value="scope.row.active"
                    ></el-switch>
                  </template>
                </el-table-column>
                <el-table-column prop="name" label="Name" width="180"> </el-table-column>
                <el-table-column prop="subject" label="Subject" width="480"> </el-table-column>
                <el-table-column label="Actions">
                  <template slot-scope="scope">
                    <el-button size="mini" type="primary" icon="el-icon-edit" @click="editNotification(scope.$index)"></el-button>
                    <el-button size="mini" icon="el-icon-copy-document" @click="duplicateNotification(scope.$index)"></el-button>
                    <el-button size="mini" type="danger" icon="el-icon-delete" v-if="notifications.length > 1" @click="deleteNotification(scope.$index)"></el-button>
                  </template>
                </el-table-column>
              </el-table>

            <!-- Edit Dialog -->
<!--            <el-dialog :title="editing ? 'Edit Notification' : 'Add Notification'" :visible.sync="editing" width="700px">-->
                
                <div v-if="notifications[editingIndex] && editing">

                  <el-form v-model="notifications[editingIndex]">

                    <div class="form-fields">
                      
                        <div class="notification-head">
                          <el-form-item label="Notification Title">
                            <el-input v-model="notifications[editingIndex].name"></el-input>
                          </el-form-item>
                        </div>
                        
                        
                        <div class="notification-row notification-field">
                            <label for="notification-title" class="contactum-label">{{ 'Type' }}</label>
                            <el-select v-model="notifications[editingIndex].type" placeholder="Select Type">
                              <el-option label="Email Notification" value="email"></el-option>
                            </el-select>
                        </div>
                    
                        <template v-if="notifications[editingIndex].type == 'email' ">
                            <div class="notification-row notification-field">
                              <label for="notification-title" class="contactum-label">{{ 'To' }}</label>
                              <el-input  v-model="notifications[editingIndex].to"> </el-input>
                              <merge_tags @insert="insertValue" field="to"/>
                            </div>

                            <div class="notification-row notification-field">
                              <label for="notification-title" class="contactum-label">{{ 'Reply To' }}</label>
                              <el-input v-model="notifications[editingIndex].replyTo"> </el-input>
                              <merge_tags @insert="insertValue" field="replyTo"/>
                            </div>

                            <div class="notification-row notification-field">
                              <label for="notification-title" class="contactum-label">{{ 'Subject' }}</label>
                              <el-input v-model="notifications[editingIndex].subject"> </el-input>
                              <merge_tags @insert="insertValue" field="subject"/>
                            </div>

                            <div class="notification-row notification-field">
                              <label for="notification-title" class="contactum-label">{{ 'Email Message' }}</label>
                              <el-input type="textarea" v-model="notifications[editingIndex].message"> </el-input>
          <!--                    <wp_editor :value="notifications[editingIndex].message"  @content-changed="onEditorContentChange" />-->
                              <merge_tags @insert="insertValue" field="message"/>
                            </div>
                                
                            <div class="notification-row notification-field">
                              <label for="notification-title" class="contactum-label"> From Name </label>
                              <!-- <input type="text" v-model="notifications[editingIndex].fromName"> -->
                              <el-input v-model="notifications[editingIndex].fromName"> </el-input>
                              <merge_tags @insert="insertValue" field="fromName"></merge_tags>
                            </div>

                            <div class="notification-row notification-field">
                                <label for="notification-title" class="contactum-label"> From Address </label>
                                <!-- <input type="email" name="" v-model="notifications[editingIndex].fromAddress"> -->
                                <el-input type="email" v-model="notifications[editingIndex].fromAddress"> </el-input>
                                <merge_tags filter="email_address" @insert="insertValue" field="fromAddress"></merge_tags>
                            </div>

                            <div class="notification-row notification-field">
                                <label for="notification-title" class="contactum-label"> CC </label>
                                <el-input type="email" name="" v-model="notifications[editingIndex].cc"> </el-input>
                            </div>

                            <div class="notification-row notification-field">
                                <label for="notification-title" class="contactum-label"> BCC </label>
                                <el-input type="email" v-model="notifications[editingIndex].bcc"> </el-input>
                            </div>

                            <div class="dialog-footer">
                              <el-button @click="editing = false">Cancel</el-button>
                              <el-button type="primary" @click="updateNotification"> Update </el-button>
                            </div>
                        
                        </template>

                      </div>

                  </el-form>
              </div>

<!--            </el-dialog>-->
          </el-card>

        </div>
    
    </div>

</template>

<script>

import merge_tags from "../merge-tags/index.vue";
import wp_editor from '../common/wp-editor.vue';

export default {
  name: "form_notifications",
  components: {
    merge_tags,
    wp_editor
  },
  data: function() {
    return {
      editing: false,
      editingIndex: 0,
      isAdvanceHidden: true
    };
  },
  computed: {
    notifications: function() {
      return this.$store.state.notifications;
    }
  },
  methods: {

    saveNotification() {
      this.$emit('save-notification');
    },

    addNotification: function() {
      this.$store.commit("addNotification", contactum.defaultNotification);
      this.editingIndex = this.notifications.length - 1;
      this.editing = true;
    },
    editNotification: function(index) {
      this.editingIndex = index;
      this.editing = true;
    },

    updateNotification: function() {
      this.$store.commit("updateNotification", {
        index: this.editingIndex,
        value: this.notifications[this.editingIndex]
      });

      this.editing = false;

      this.$emit('save-notification');
    },

    deleteNotification: function(index) {
      this.$confirm('Are you sure you want to delete this notification?', 'Warning', {
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        type: 'warning'
      }).then(() => {
        this.$store.commit("deleteNotification", index);
        this.$emit('save-notification');
      });

    },

    duplicateNotification: function(index) {
      this.$store.commit("duplicateNotification", index);
      this.$emit('save-notification');
    },

    toggelNotification: function(index, val) {
      this.$store.commit("updateNotificationProperty", {
        index: index,
        property: "active",
        value: val
      });
      this.$emit('save-notification');
    },

    onEditorContentChange() {

    },

    toggleAdvanced() {

    },

    insertValue: function(type,field,property) {
        let notification = this.notifications[this.editingIndex],
            value = ( field !== undefined ) ? '{' + type + ':' + field + '}' : '{' + type + '}';
            notification[property] = notification[property] + value;
    },

    back: function() {
      this.editing = false;
    }
  }
};
</script>

<style type="text/css" scoped>


.form-notification-wrap {
  padding: 20px;
}

.notification-toolbar {
  margin-bottom: 15px;
  text-align: right;
}

.notification-table-card {
  padding: 10px;
}


 .form-fields {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
}

.advance-fields .notification-field, .form-fields  > div {
  flex-basis: 44%;
}

.form-notification-wrap {
    margin-top: 20px;
    padding-left: 20px;
}

.form-notifications {
  margin-top: 15px;
}

 table {
    box-shadow: 2px 1px 10px 2px #d9d9da;
}

.notification-field label {
    display: block;
    margin-bottom: 5px;
}

.notification-field{
    position: relative;
    margin: 5px;
}

.notification-field input[type="text"], .notification-field textarea, .notification-field input[type="email"], .notification-head input[type="text"]{
  padding: 8px;
  width: 100%;
}

</style>
