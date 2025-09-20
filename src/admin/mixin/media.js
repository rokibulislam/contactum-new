export default {

    mounted: function () {
        if (!window.wpActiveEditor) {
            window.wpActiveEditor = null;
        }
        var self = this;

        this.set_options();

        jQuery(this.$el)
            .find(".option-field-swapper")
            .sortable({
                items: ".option-field-option",
                handle: ".sort-handler",
                update: function (e, ui) {
                    var item = ui.item[0],
                        data = item.dataset,
                        toIndex = parseInt(jQuery(ui.item).index()),
                        fromIndex = parseInt(data.index);

                    self.options.swap(fromIndex, toIndex);
                },
            });
    },
    methods: {

        delete_option: function (index) {
            this.options.splice(index, 1);

            this.$store.dispatch("update_editing_form_field", {
                id: this.editfield.id,
                property: this.field.name,
                value: this.options,
            });
        },

        clear_selection: function () {
            this.selected = [];
        },

        isLodash: function () {
            let isLodash = false;

            // If _ is defined and the function _.forEach exists then we know underscore OR lodash are in place
            if ('undefined' != typeof (_) && 'function' == typeof (_.forEach)) {

                // A small sample of some of the functions that exist in lodash but not underscore
                const funcs = ['get', 'set', 'at', 'cloneDeep'];

                // Simplest if assume exists to start
                isLodash = true;

                funcs.forEach(function (func) {
                    // If just one of the functions do not exist, then not lodash
                    isLodash = ('function' != typeof (_[func])) ? false : isLodash;
                });
            }

            if (isLodash) {
                // We know that lodash is loaded in the _ variable
                return true;
            } else {
                // We know that lodash is NOT loaded
                return false;
            }
        },

        initUploader(option) {
            // e.preventDefault();

            if (this.isLodash()) {
                _.noConflict();
            }

            if (typeof wp !== "undefined" && wp.media && wp.media.editor) {
                var self = this;
                var send_attachment_bkp = wp.media.editor.send.attachment;
                wp.media.editor.send.attachment = function (props, attachment) {
                    option.photo = attachment.url;
                    wp.media.editor.send.attachment = send_attachment_bkp;
                };
                wp.media.editor.open();
            }
            return false;
        },
    },

    watch: {
        options: {
            deep: true,
            handler: function (new_option) {
                this.$store.dispatch("update_editing_form_field", {
                    id: this.editfield.id,
                    property: this.field.name,
                    // value: options,
                    value: new_option,
                });
            },
        },

        selected: function (new_val) {
            this.$store.dispatch("update_editing_form_field", {
                id: this.editfield.id,
                property: "selected",
                value: new_val,
            });
        },
    },
}
