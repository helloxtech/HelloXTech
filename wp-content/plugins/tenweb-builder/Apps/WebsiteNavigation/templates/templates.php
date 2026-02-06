<?php
$pages_info = \Tenweb_Builder\Modules\WebsiteNavigation\GetWPData::filteredPagesList();
$nav_menu_info = \Tenweb_Builder\Modules\WebsiteNavigation\GetWPData::getNavMenuItems();
?>
<!--Navigation Template sidebar template-->
<script type="text/template" id="twbb-navmenu-sidebar-template" data-nav_id="<?php echo esc_attr($nav_menu_info['nav_menu_id']); ?>">
    <?php
    //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::navMenuSidebarTemplate($nav_menu_info);
    ?>
</script>

<!--Navigation Template action tooltip add nav menu item-->
<script type="text/template" id="twbb-wn-add-menu-item-action-tooltip">
    <div class="wn-action-tooltip wn-add-menu-item-action-tooltip">
        <div class="wn-action-tooltip-container twbb-wn-main-container">
            <?php
            $needed_types = \Tenweb_Builder\Modules\WebsiteNavigation\GetWPData::getNeededTypes($nav_menu_info['nav_menu_items']);
            //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo \Tenweb_Builder\Modules\WebsiteNavigation\RenderingHelper::twbb_rederAddMenuItemTooltip('Add menu items', 'all_types', $needed_types);
            ?>
        </div>
        <div class="wn-action-tooltip-container twbb-wn-secondary-container" data-post-type="custom">
            <div class="twbb-wn-action-tooltip-title-container">
                <span class="twbb-wn-back-add-to-menu-button"></span>
                <div class="twbb-wn-action-tooltip-title"><?php esc_html_e('Custom links', 'tenweb-builder');?></div>
            </div>
            <div class="twbb-wn-add-menu-item-input-container">
                <div class="twbb-wn-add-menu-item-input">
                    <label for="wn-custom-link-nav-url"><?php esc_html_e('URL*', 'tenweb-builder'); ?></label>
                    <input type="text" id="wn-custom-link-nav-url" name="wn-custom-link-nav-url"
                           placeholder="https://example.com"  oninput="twbb_customLinkInputFunction(this)" />
                </div>
                <div class="twbb-wn-add-menu-item-input">
                    <label for="wn-custom-link-nav-label"><?php esc_html_e('Navigation label', 'tenweb-builder'); ?></label>
                    <input type="text" id="wn-custom-link-nav-label" name="wn-custom-link-nav-label"
                           placeholder="Ex: contact us"  oninput="twbb_customLinkInputFunction(this)"/>
                </div>
            </div>
            <div class="twbb-wn-add-custom-menu-item-button disabled" data-type="custom" data-object="custom">
                <span><?php esc_html_e('Add Custom Item', 'tenweb-builder'); ?></span>
            </div>
        </div>
    </div>
</script>

<script type="text/template" id="twbb-wn-add-menu-item-button">
    <div class="twbb-wn-button twbb-wn-add-menu-item twbb-wn-bordered twbb-wn-add-menu-item-blue-button">
        <?php esc_html_e('Add Menu Item', 'tenweb-builder'); ?>
        <div class="wn-add-menu-item twbb-wn-tooltip-parent twbb-empty-nav-tooltip-container"></div>
    </div>
</script>

<script type="text/template" id="twbb-wn-inner-page-settings">
    <div class="twbb-website-nav-sidebar-container twbb-wn-inner-page-settings">
        <div class="twbb-wn-inner-pages-header-container">
            <span class="twbb-wn-inner-pages-settings-save">
                <span class="twbb-wn-settings-button-text"><?php esc_html_e('Save', 'tenweb-builder')?></span>
                <span class="twbb-wn-settings-button-spinner"></span>
            </span>
            <div class="twbb-wn-inner-pages-header">
                <div class="twbb-wn-inner-pages-back">
                    <span class="twbb-wn-back-to-main-sidebar"></span>
                    <div class="twbb-wn-inner-pages-header-title"><?php esc_html_e('Settings', 'tenweb-builder')?></div>
                </div>
            </div>
        </div>
        <div class="twbb-wn-inner-pages-content">
        </div>
    </div>
</script>

<script type="text/template" id="twbb-wn-inner-page-items">
    <div class="twbb-website-nav-sidebar-container twbb-wn-inner-page-items">
        <div class="twbb-wn-inner-pages-header-container">
            <div class="twbb-wn-inner-pages-header">
                <div class="twbb-wn-inner-pages-back">
                    <span class="twbb-wn-back-to-main-sidebar"></span>
                    <div class="twbb-wn-inner-pages-header-title"><?php esc_html_e('Posts', 'tenweb-builder')?></div>
                </div>
            </div>
        </div>
        <div class="twbb-wn-inner-pages-content twbb-wn-search-container twbb-website-nav-sidebar-items">
            <div class="twbb-wn-search-wrapper">
                <input type="text" name="twbb_wn_search" class="twbb-wn-search" placeholder="<?php esc_attr_e('Search', 'tenweb-builder');?>">
                <span class="twbb-wn-clear-search"></span>
            </div>
        </div>
    </div>
</script>

<script type="text/template" id="twbb-wn-trash-managment-page">
    <div class="twbb-website-nav-sidebar-container twbb-wn-inner-trash-page" data-type="">
        <div class="twbb-wn-inner-pages-header-container">
            <span class="twbb-wn-inner-pages-empty-trush-button twbb-empty-trush-button-inactive">
                <span class="twbb-wn-settings-button-text"><?php esc_html_e('Empty Trash', 'tenweb-builder')?></span>
                <span class="twbb-wn-settings-button-spinner"></span>
            </span>
            <div class="twbb-wn-inner-pages-header">
                <span class="twbb-wn-trash-back-to-main-sidebar"></span>
                <div class="twbb-wn-trash-inner-pages-header-title"><?php esc_html_e('Trash', 'tenweb-builder')?></div>
            </div>
        </div>
        <div class="twbb-wn-inner-trash-pages-content">
        </div>
    </div>
</script>

<script type="text/template" id="twbb-wn-trash-page-item-template">
    <div class="twbb-wn-trash-item twbb-wn-flex-space-between twbb-wn-trash-item-new" data-id="">
        <span class="twbb-wn-trash-item-title"></span>
        <span class="twbb-wn-restore_from_trash">
            <span class="twbb-wn-settings-button-text"><?php esc_html_e("Restore", 'tenweb-builder'); ?></span>
            <span class="twbb-wn-settings-button-spinner"></span></span>
        <span class="twbb-wn-delete_from_trash">
            <span class="twbb-wn-settings-button-text"><?php esc_html_e("Delete", 'tenweb-builder'); ?></span>
            <span class="twbb-wn-settings-button-spinner"></span></span>
        <span class="twbb--settings-button-spinner"></span>
    </div>
</script>
