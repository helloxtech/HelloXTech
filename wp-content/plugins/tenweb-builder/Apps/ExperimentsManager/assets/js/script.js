jQuery(document).ready(function () {
    let $form = jQuery('#elementor-settings-form');

    // From 10Web tab to Elementor tab
    $form.on('change', 'select[data-sync-name]', function () {
        let $changed = jQuery(this);
        let experimentName = $changed.data('sync-name');
        let newValue = $changed.val();

        let $elementorSelect = $form.find('select[name="' + experimentName + '"]');
        if ($elementorSelect.length && $elementorSelect.val() !== newValue) {
            $elementorSelect.val(newValue).trigger('change');
        }
    });

    // From Elementor tab to 10Web tab
    $form.on('change', 'select[name^="elementor_experiment-"]', function () {
        let $changed = jQuery(this);
        let experimentName = $changed.attr('name');
        let newValue = $changed.val();

        let $tenwebSelect = $form.find('select[data-sync-name="' + experimentName + '"]');
        if ($tenwebSelect.length && $tenwebSelect.val() !== newValue) {
            $tenwebSelect.val(newValue);
        }
    });
});
