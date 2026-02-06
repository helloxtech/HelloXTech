class TWBB_NavMenuActions {
    constructor() {}

    bulkEditMenu(items, menu_id) {
        this.addDisableClass();
        const elements = [];

        items.each(function () {
            const item_title = jQuery(this).attr('data-title'),
                menu_item_db_id = jQuery(this).find('.menu-item-data-db-id').val(),
                item_id = jQuery(this).find('.menu-item-data-object-id').val(),
                object = jQuery(this).find('.menu-item-data-object').val(),
                item_type = jQuery(this).find('.menu-item-data-type').val(),
                item_position = jQuery(this).find('.menu-item-data-position').val(),
                item_parent_id = jQuery(this).find('.menu-item-data-parent-id').val(),
                item_url = jQuery(this).attr('data-url'),
                status = jQuery(this).attr('data-status');

            elements.push({
                menu_item_db_id,
                item_id,
                item_title,
                object,
                item_type,
                item_position,
                item_parent_id,
                item_url,
                status,
            });
        });

        jQuery.ajax({
            url: twbb_website_nav.ajaxurl,
            type: 'POST',
            data: {
                action: 'wn_nav_menu_changes',
                process: 'editNavMenuBulkItems',
                nonce: twbb_website_nav.nonce,
                args: {
                    menu_id,
                    items: elements,
                },
            }
        })
        .done( (data) => {
            if (data.success) {
                this.updateOrdering();
                this.reRenderNavMenu();
            }
        })
        .always( () => {
            this.removeDisableClass();
        });
    }

    editMenuItem(ui, menu_id) {
        this.addDisableClass();

        const item_title = ui.attr('data-title'),
            menu_item_db_id = ui.find('.menu-item-data-db-id').val(),
            item_id = ui.find('.menu-item-data-object-id').val(),
            item_object = ui.find('.menu-item-data-object').val(),
            item_type = ui.find('.menu-item-data-type').val(),
            item_position = ui.find('.menu-item-data-position').val(),
            item_parent_id = ui.find('.menu-item-data-parent-id').val(),
            item_url = ui.attr('data-url'),
            status = ui.attr('data-status');

        jQuery.ajax({
            url: twbb_website_nav.ajaxurl,
            type: 'POST',
            data: {
                action: 'wn_nav_menu_changes',
                process: 'editNavMenuItem',
                nonce: twbb_website_nav.nonce,
                menu_id,
                menu_item_db_id,
                item_id,
                item_title,
                item_object,
                item_type,
                item_position,
                item_parent_id,
                item_url,
                status,
            },
        })
        .done( (data) => {
            if (data.success) {
                this.updateOrdering();
                this.reRenderNavMenu();
            }
        })
        .always( () => {
            this.removeDisableClass();
        });
    }

    addMenuItem(menu_id, ui, success_callback, return_last_added_item = false) {
        this.addDisableClass();

        const item_id = ui.attr('data-id'),
            item_title = ui.attr('data-title'),
            item_object = ui.attr('data-object'),
            item_type = ui.attr('data-type'),
            item_url = ui.attr('data-url'),
            item_position = ui.find('.menu-item-data-position').val() || 0,
            item_parent_id = ui.find('.menu-item-data-parent-id').val() || 0,
            data = {
            action: 'wn_nav_menu_changes',
            process: 'addNavMenuItem',
            nonce: twbb_website_nav.nonce,
            menu_id,
            item_id,
            item_title,
            item_object,
            item_type,
            item_position,
            item_parent_id,
            item_url,
        };

        if (ui.closest('.wn-action-tooltip-container').length || return_last_added_item) {
            data.return_last_added_item = true;
        }

        jQuery.ajax({
            url: twbb_website_nav.ajaxurl,
            type: 'POST',
            data,
        })
        .done( (data) => {
            if ( data.success ) {
                // Case 1: New menu item was successfully added
                if( data.data.code === 200 ) {
                    if (success_callback) {
                        const remove = item_type !== 'custom';
                        success_callback(data.data.item_html, ui, remove);
                    }
                    this.reRenderNavMenu();
                }
                // Case 2: Menu item already existed, only update the settings section (hidden inputs with real IDs)
                else if( data.data.code === 201 ) {
                    this.updateSettingsHtml(data.data.item_html, ui);
                }
            }
        })
        .always( () => {
            this.removeDisableClass();
            this.addActiveItemClass();
        });
    }

    /**
     * Replaces the current menu item settings HTML with updated HTML from the server response.
     *
     * @param {string} data - The new HTML markup for the menu item settings.
     * @param {HTMLElement} ui - The DOM element representing the menu item UI container.
     *
     * If the menu item does not yet have a database ID (i.e., it's a new item),
     * and valid HTML data is provided, this function replaces the existing
     * `.menu-item-settings` block with the new HTML to reflect updated settings.
     */
    updateSettingsHtml(data, ui) {
        const $settings = jQuery(ui).find(".menu-item-settings");
        const dbId = $settings.find(".menu-item-data-db-id").val();

        if ((dbId === '' || typeof dbId === 'undefined') && data !== '') {
            $settings.replaceWith(data);
        }
    }

    removeMenuItem(ui, success_callback) {
        this.addDisableClass();

        const db_id = ui.find('.menu-item-data-db-id').val();

        jQuery.ajax({
            url: twbb_website_nav.ajaxurl,
            type: 'POST',
            data: {
                action: 'wn_nav_menu_changes',
                process: 'removeNavMenuItem',
                nonce: twbb_website_nav.nonce,
                menu_item_db_id: db_id,
            }
        })
        .done((data) => {
            if (data.success) {
                this.updateOrdering();
                this.reRenderNavMenu();
                success_callback(ui);
            }
        })
        .always( () => {
            this.removeDisableClass();
        });
    }

    updateOrdering() {
        const menu_items = jQuery('.twbb-website-nav-sidebar-nav-menus-items .menu-item'),
            menu_item_positions = [];

        menu_items.each(function () {
            const db_id = jQuery(this).find('.menu-item-data-db-id').val(),
                position = jQuery(this).find('.menu-item-data-position').val();

            menu_item_positions.push({ db_id, position });
        });

        jQuery.ajax({
            url: twbb_website_nav.ajaxurl,
            type: 'POST',
            data: {
                action: 'wn_nav_menu_changes',
                process: 'updateNavMenuOrdering',
                nonce: twbb_website_nav.nonce,
                args: {menu_item_positions},
            }
        })
        .done( (data) => {
            if (data.success) {
                this.showSavedLabel('twbb-website-nav-sidebar-navigation-container');
            }
        })
    }

    fillMenuItemSettings(ui, db_id) {
        const object_id = ui.attr('data-id'),
            object = ui.attr('data-object'),
            item_type = ui.attr('data-type');

        ui.find('.menu-item-data-db-id').val(db_id);
        ui.find('.menu-item-data-object-id').val(object_id);
        ui.find('.menu-item-data-object').val(object);
        ui.find('.menu-item-data-type').val(item_type);
        ui.find('.menu-item-settings').attr('id', `menu-item-settings-${db_id}`);

        ui.find('input').each(function () {
            const input = jQuery(this),
                name = `${input.attr('name').slice(0, -1)}${db_id}]`;
            input.attr('name', name);
        });
    }

    showSavedLabel(parent_class) {
        jQuery(`.${parent_class}`).find('.twbb-saved-label').css('display', 'inline-block');
        setTimeout(() => {
            jQuery(`.${parent_class}`).find('.twbb-saved-label').css('display', 'none');
        }, 2000);
    }

    reRenderNavMenu(post_id = null, element_id = null) {
        const iframe = jQuery('#elementor-preview-iframe')[0];

        if (!post_id) {
            post_id = jQuery('.twbb-website-nav-sidebar-main').attr('data-page_where_is_menu');
        }

        if (!element_id) {
            element_id = jQuery('.twbb-website-nav-sidebar-main').attr('data-nav_widget_id');
        }

        jQuery.ajax({
            url: twbb_options.ajaxurl,
            type: 'POST',
            data: {
                action: 'wn_nav_menu_changes',
                process: 'changeNavMenuContent',
                postID: post_id,
                elementID: element_id,
                nonce: twbb_options.nonce,
            },
        })
        .done( (data) => {
            if (data.success) {
                const $scope = jQuery('#elementor-preview-iframe')
                    .contents()
                    .find(`div[data-id=${element_id}]`);
                reRenderNavMenu($scope, data.data);
            }
        });
    }

    addDisableClass() {
        jQuery('.twbb-website-nav-sidebar-main').addClass('disable-ajax-in-progress');
    }

    removeDisableClass() {
        jQuery('.twbb-website-nav-sidebar-main').removeClass('disable-ajax-in-progress');
        jQuery('.twbb-tooltip-parent-container').css('display', 'none');
    }

    addActiveItemClass() {
        jQuery(`.twbb-wn-item[data-id="${elementor.config.document.id}"`).addClass('twbb-wn-item-active');
    }
}

jQuery(document).ready(() => {
    window.twbb_navMenuActions = new TWBB_NavMenuActions();
});
