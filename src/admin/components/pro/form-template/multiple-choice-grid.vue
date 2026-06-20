<template>
  <div class="checkbox-grid-container">
    <table class="checkbox-grid-table">
      <thead>
        <tr>
          <th class="row-label-header"></th>
          <th
            v-for="(column, cIdx) in field.grid_columns"
            :key="'col-' + cIdx"
            class="column-label"
          >
            {{ column.label }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr 
          v-for="(row, rIdx) in field.grid_rows" 
          :key="'row-' + rIdx"
          class="grid-row"
        >
          <td class="row-label">{{ row.label }}</td>
          <td 
            v-for="(column, cIdx) in field.grid_columns" 
            :key="'cell-' + cIdx"
            class="checkbox-cell"
          >
            <div class="radio-wrapper">
              <input 
                type="radio" 
                :name="'field_' + field.id + '_row_' + rIdx" 
                :value="column.label"
              />
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <p v-if="field.help" class="help-text">{{ field.help }}</p>
  </div>
</template>

<script>
import form_field from "../../../mixin/form-field.js";
export default {
  name: "form_multiple_choice_grid",
  mixins: [form_field],
};
</script>


<style lang="scss">

$row-bg-odd: #ffffff;
$row-bg-even: #f1f1f1; // Matches the light grey in the image
$header-bg: #e8e8e8;
$text-color: #555;
$border-radius: 4px;

.checkbox-grid-container {
  background: #fff;
  border-radius: $border-radius;
  font-family: sans-serif;

  // Header Bar with Icons

  // Table Styling
  .checkbox-grid-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;

    thead tr {
      background-color: $header-bg;
      
      th {
        padding: 15px 10px;
        color: #333;
        font-weight: 600;
        text-align: center;
      }
    }

    .grid-row {
      // Alternating row colors
      &:nth-child(even) {
        background-color: $row-bg-even;
      }
      &:nth-child(odd) {
        background-color: $row-bg-odd;
      }

      .row-label {
        padding: 15px 20px;
        color: $text-color;
        font-weight: 500;
        text-align: left;
        width: 25%; // Fixed width for row titles
      }

      .checkbox-cell {
        text-align: center;
        padding: 15px 10px;

        .checkbox-wrapper {
          display: flex;
          justify-content: center;
          align-items: center;

          input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #409eff; // Modern checkbox color
          }
        }
      }
    }
  }

  .help-text {
    font-size: 12px;
    color: #999;
    padding: 10px;
    margin: 0;
  }
}

</style>