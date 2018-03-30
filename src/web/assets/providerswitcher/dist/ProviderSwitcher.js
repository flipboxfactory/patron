(function ($) {
    /** global: Craft */
    /** global: Garnish */
    Craft.ProviderSwitcher = Garnish.Base.extend(
        {
            $providerSelect: null,
            $fields: null,
            $spinner: null,

            init: function ($providerSelect, $fields) {
                this.$providerSelect = $providerSelect;
                this.$fields = $fields;
                this.$spinner = $('<div class="spinner hidden"/>').insertAfter(this.$providerSelect.parent());

                this.addListener(this.$providerSelect, 'change', 'onProviderSwitch');
            },

            onProviderSwitch: function (ev) {
                this.$spinner.removeClass('hidden');

                Craft.postActionRequest(
                    'patron/cp/provider/settings',
                    Craft.cp.$primaryForm.serialize(),
                    $.proxy(function (response, textStatus) {
                        this.$spinner.addClass('hidden');

                        if (textStatus === 'success') {
                            this.$fields.html(response.html);
                            Craft.initUiElements(this.$fields);
                            Craft.appendHeadHtml(response.headHtml);
                            Craft.appendFootHtml(response.bodyHtml);
                        }
                    }, this)
                );
            }

        }
    );
})(jQuery);