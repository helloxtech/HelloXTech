jQuery(window).on('elementor:init', function () {
    elementor.hooks.addAction('panel/open_editor/widget', function (panel, model, view) {
        if (model.attributes.widgetType === 'twbb_10web_checkout') {
            const settingsModel = model.get('settings');

            settingsModel.on('change', function (changedModel) {
                setTimeout(() => {
                    jQuery('#elementor-preview-iframe')[0].contentWindow.dispatchEvent(new Event('resize'));
                }, 100);
            });
        }
    });
});