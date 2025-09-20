export default {
	props: {
		field: {
			type: Object,
			default() {
				return {};
			}
		},

        editfield: {
			type: Object,
			default() {
				return {};
			}
		}
	},

    computed: {
		value: {
			get: function() {
				let property = this.field.name;
				return this.editfield[property];
			},
			set: function(value) {
				this.$store.dispatch("update_editing_form_field", {
					id: this.editfield.id,
					property: this.field.name,
					value: value
				});
			}
		}
    }
};
