<template>
<div class="entry_details">
    <el-row :gutter="20">
        <el-col :span="16">

          <div class="contactum_card">

            <div class="contactum_card_head">
              <div class="entry_info_box_header">
                <div class="entry_info_box_title">Entry Details</div>
              </div>
            </div>


            <div class="contactum_card_body">
              <table class="form-table">
                <tr
                    v-for="(field, index) in fields"
                    :key="index"
                    class="contactum_table_row"
                >
                  <td class="form-title">{{ field.label }}</td>
                  <td>
                <span v-if="Array.isArray(field.value)" class="form-value">
                  {{ field.value.join(' | ') }}
                </span>
                    <span v-else class="form-value">
                  {{ field.value }}
                </span>
                  </td>
                </tr>
              </table>

            </div>

          </div>

        </el-col>


    <el-col :span="8">
      <div class="contactum_card">
        <div class="contactum_card_head">
          <div class="entry_info_box_header">
            <div class="entry_info_box_title">Submission Info</div>
            <div>
              <el-button size="small" icon="el-icon-printer" @click="printEntry()"> </el-button>
            </div>
          </div>
        </div>

        <div class="contactum_card_body">
            <div id="contactum-forms-entry-details">
              <div class="inside">
                <ul class="contactum-forms-entry-details-meta contactum_list_border_bottom">
                  <li class="contactum-forms-entry-id">
                    <span class="lead-title"> Entry ID: </span>
                    <span class="lead-text"> <strong> {{ metadata.id }} </strong> </span>
                  </li>
                  <li class="contactum-forms-entry-date">
                    <span class="lead-title"> Submitted: </span>
                    <span class="lead-text"> <strong> {{ metadata.created }} </strong> </span>
                  </li>

                  <li class="contactum-forms-entry-user">
                    <span class="lead-title"> User: </span>
                    <span class="lead-text"> <strong> {{ metadata.user }} </strong> </span>
                  </li>

                  <li class="contactum-forms-entry-ip">
                    <span class="lead-title"> User IP: </span>
                    <span class="lead-text"> <strong> {{ metadata.ip_address }} </strong> </span>
                  </li>

                  <li class="contactum-forms-entry-referer">
                    <span class="lead-title"> Referer Link: </span>
                    <span class="lead-text"> <strong> <a :href="metadata.referer" target="_blank"> View </a> </strong> </span>
                  </li>
                </ul>
              </div>
            </div>

        </div>
      </div>

    </el-col>

  </el-row>

</div>

</template>

<script>

export default{
  name: "Entry",
  props: ['entry_id','form_id'],
  data() {
    return {
      'metadata': null,
      'fields': [],
      'report': []
    }
  },
  mounted() {
    this.fetchItem();
  },

  methods: {

    printEntry() {
      
    },

    fetchItem() {
      const self = this;
      jQuery.ajax({
        url: contactum.ajaxurl, // WordPress automatically defines this if you're in admin area
        type: 'POST',
        data: {
          'action': 'contactum_get_entries_details',
          '_ajax_nonce': contactum.nonce,
          'form_id': this.form_id,
          'entry_id': this.entry_id
        },
        success: function(response) {
          if (response.success) {
           self.metadata =  response.data.metadata;
           self.fields = response.data.fields;
          }
        }
      });
    }
  }
}
</script>

<style scoped lang="scss">

.entry_details {
  padding: 24px;
}

.form-table {
  width: 100%;
  border-collapse: collapse;

  tr {
    border-bottom: 1px solid #e5e7eb;
  }

  td {
    padding: 12px 16px;   // more breathing room
    vertical-align: top;
  }

  .form-title {
    width: 30%;
    font-weight: 500;
    color: #374151;
  }

  .form-value {
    color: #111827;
    font-size: 14px;
    line-height: 1.6;
    padding: 8px 0;// improve readability

    & a {
      color: #2563eb;
      text-decoration: none;

      &:hover {
        text-decoration: underline;
      }
    }
  }
}


.form-label {
  width: 25%;
  padding: 8px 0;
  color: #6b7280;
  font-weight: 500;
}


.entry_info_box_header {
  align-items: center;
  display: flex;
  justify-content: space-between;
}

.contactum_list_border_bottom>li:not(:last-child) {
  border-bottom: 1px solid #ececec;
  margin-bottom: 12px;
  padding-bottom: 12px;
}

.contactum_list_border_bottom li {
  display: flex
;
}
.contactum_list_border_bottom > li {
  font-size: 15px;
  position: relative;
}

.lead-title {
  color: #1e1f21;
  display: block;
  font-size: 15px;
  font-weight: 500;
  transition: .2s;
  flex-shrink: 0;
  width: 120px;
}

.lead-text {
  color: #606266;
  font-size: 13px;
}


.entry_info_box_title {
  font-size: 16px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 12px;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 6px;
}

</style>