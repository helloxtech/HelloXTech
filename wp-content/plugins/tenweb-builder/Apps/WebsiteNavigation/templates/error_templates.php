<?php
?>
<script type="text/template" id="twbb-navmenu-sidebar-other-to-pages-error-template">
    <div class="twbb-navmenu-sidebar-error twbb-navmenu-sidebar-other-to-pages-error">
        <div class="twbb-navmenu-sidebar-error__text">
            <p>
                <?php
                esc_html_e('Only pages can be moved from the menu to the page list.', 'tenweb-builder'); ?>
            </p>
        </div>
        <span class="twbb-webs-nav-sidebar-error-close" onclick="twbb_webNavSidebarErrorClose('twbb-navmenu-sidebar-other-to-pages-error')"></span>
</script>

<script type="text/template" id="twbb-navmenu-sidebar-draft-to-nav-error-template">
    <div class="twbb-navmenu-sidebar-error twbb-navmenu-sidebar-draft-to-nav-error">
        <div class="twbb-navmenu-sidebar-error__text">
            <p>
                <?php
                esc_html_e('Only published pages can be added to the navigation menu.', 'tenweb-builder'); ?>
            </p>
        </div>
        <span class="twbb-webs-nav-sidebar-error-close" onclick="twbb_webNavSidebarErrorClose('twbb-navmenu-sidebar-draft-to-nav-error')"></span>
</script>

<script type="text/template" id="twbb-navmenu-sidebar-exceeded-max-depth-error-template">
    <div class="twbb-navmenu-sidebar-error twbb-navmenu-sidebar-exceeded-max-depth-error">
        <div class="twbb-navmenu-sidebar-error__text">
            <p>
                <?php
                esc_html_e('You can only create up to 3 levels of hierarchy. Try reorganizing your menu.', 'tenweb-builder'); ?>
            </p>
        </div>
        <span class="twbb-webs-nav-sidebar-error-close" onclick="twbb_webNavSidebarErrorClose('twbb-navmenu-sidebar-exceeded-max-depth-error')"></span>
</script>

<script type="text/template" id="twbb-navmenu-sidebar-nested-to-pages-error-template">
    <div class="twbb-navmenu-sidebar-error twbb-navmenu-sidebar-nested-to-pages-error">
        <div class="twbb-navmenu-sidebar-error__text">
            <p>
                <?php
                esc_html_e('Nested items can’t be moved to Pages. Try moving submenus first.', 'tenweb-builder'); ?>
            </p>
        </div>
        <span class="twbb-webs-nav-sidebar-error-close" onclick="twbb_webNavSidebarErrorClose('twbb-navmenu-sidebar-nested-to-pages-error')"></span>
</script>
