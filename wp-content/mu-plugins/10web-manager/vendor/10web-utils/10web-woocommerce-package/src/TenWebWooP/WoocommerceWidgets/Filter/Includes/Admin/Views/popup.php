<?php
    $tww_post_type_url = add_query_arg(array('post_type' => 'tww_filter'), admin_url() . 'edit.php');
    $fieldsList = \TenWebWooP\WoocommerceWidgets\Filter\Includes\Helper::getFieldsList();
    $context['fields_list'] = $fieldsList;
    $container_class = '';

    if (isset($context['popup']) && $context['popup'] === 'elementor') {
        $container_class = ' tww_filter_container_elementor';
    }
?>
<div class="twwf_overlay"></div>

<div class="tww_filter_container<?php echo esc_attr($container_class); ?>">
    <span data-href="<?php echo esc_url($tww_post_type_url); ?>" class="tww_close_popup"></span>
    <?php $template_loader->render_template('filter.php', $context, dirname(__DIR__) . '/Views'); ?>
    <?php $template_loader->render_template('filter_fields.php', $context, dirname(__DIR__) . '/Views'); ?>
    <?php $template_loader->render_template('field_settings.php', $context, dirname(__DIR__) . '/Views'); ?>



    <?php
    foreach ($fieldsList as $field_data) {
        $context['field_data'] = $field_data;
        $template_loader->render_template($field_data['view'], $context, dirname(__DIR__) . '/Views/fields');
    }
    ?>
</div>
<div class="tww_leave_block twwf_display_none">
    <div class="tww_leave_info">
        <p>Are you sure you want to leave?</p>
        <p>Changes you made won’t be saved.</p>
    </div>
    <div class="tww_leave_actions">
        <span class="tww_leave_cancel">Cancel</span>
        <span class="tww_leave">Leave</span>
    </div>
</div>
