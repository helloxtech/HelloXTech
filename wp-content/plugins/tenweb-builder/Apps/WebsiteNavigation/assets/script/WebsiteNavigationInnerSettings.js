class TWBB_WebsiteNavigationInnerSettings {
    constructor() {
        this.init();

    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        this.closeSettingsInnerPage();
        this.inputCheckboxChange();
        this.inputValidationOnBlur();
        this.saveSettings();
        this.postDuplication();
        this.moveToTrashEvent();
        this.restoreFromTrash();
    }
    closeSettingsInnerPage() {
        jQuery(document).on('click', '.twbb-wn-inner-page-settings .twbb-wn-inner-pages-back' , function(e) {
            jQuery('.twbb-wn-inner-page-settings').remove();
        });

        jQuery(document).on('click', '.twbb-wn-inner-page-items .twbb-wn-inner-pages-back' , function(e) {
            jQuery('.twbb-wn-inner-page-items').remove();
        });
    }

    inputCheckboxChange() {
        jQuery(document).on('change', '.twbb-wn-checkbox-box input[type="checkbox"]', function() {
            //make save button enabled
            if(!jQuery(this).hasClass('twbb-input-has-changed') ) {
                jQuery(this).addClass('twbb-input-has-changed');
            }
            const save_element = jQuery('.twbb-wn-inner-pages-settings-save');
            //if checkbox checked
            if (jQuery(this).is(':checked')) {
                jQuery(this).parents('.twbb-wn-checkbox-box').addClass('checked');
                if( jQuery(this).attr('id') === 'wn-status-setting-id' ) {
                    jQuery(this).parents('.twbb-wn-checkbox-box-container').find('.twbb-wn-checkbox-label-status').text('Publish');
                    jQuery(this).parent('.twbb-wn-switcher')
                        .find('.twbb-wn-slide-switcher.twbb-tooltip-parent-container-item')
                        .attr('data-tooltip-text','Move to Draft');
                    // this is done for connecting status checkbox with nav and home pages
                    // checkboxes there are not available if page is
                    jQuery('.twbb-wn-show-in-nav-setting').removeClass('disabled').addClass('checked');
                    // check the show in nav checkbox
                    jQuery('.twbb-wn-show-in-nav-setting input').prop('disabled', false).prop('checked', true).addClass('twbb-input-has-changed');
                    jQuery('.twbb-wn-home-page-setting').removeClass('disabled');
                    jQuery('.twbb-wn-home-page-setting input').prop('disabled', false);
                }
            } else {
                jQuery(this).parents('.twbb-wn-checkbox-box').removeClass('checked');
                if( jQuery(this).attr('id') === 'wn-status-setting-id' ) {
                    jQuery(this).parents('.twbb-wn-checkbox-box-container').find('.twbb-wn-checkbox-label-status').text('Draft');
                    jQuery(this).parent('.twbb-wn-switcher')
                        .find('.twbb-wn-slide-switcher.twbb-tooltip-parent-container-item')
                        .attr('data-tooltip-text','Publish');

                    jQuery('.twbb-wn-show-in-nav-setting').addClass('disabled').removeClass('checked');
                    jQuery('.twbb-wn-show-in-nav-setting input').prop('disabled', true).prop('checked', false).addClass('twbb-input-has-changed');
                    jQuery('.twbb-wn-home-page-setting').addClass('disabled').removeClass('checked');
                    jQuery('.twbb-wn-home-page-setting input').prop('disabled', true).prop('checked', false).addClass('twbb-input-has-changed');
                }
            }
            if( !save_element.hasClass('wn-change-exists') ) {
                save_element.addClass('wn-change-exists');
            }
        });
        jQuery(document).on('input', '.twbb-wn-checkbox-box input[type="checkbox"], .twbb-wn-input-box input[type="text"]', function() {
            //make save button enabled
            if(!jQuery(this).hasClass('twbb-input-has-changed')) {
                jQuery(this).addClass('twbb-input-has-changed');
            }
            //I have done it for the slug input because it should changed when status changes
            if( jQuery(this).attr('id') === 'wn-status-setting-id' || jQuery(this).attr('id') === 'wn-home-page-setting-id') {
                if(!jQuery('#wn-url-slug-setting-id').hasClass('twbb-input-has-changed')) {
                    jQuery('#wn-url-slug-setting-id').addClass('twbb-input-has-changed');
                }
            }
            if( jQuery(this).val().trim() !== '' ) {
                jQuery(this).parent('.twbb-wn-input-box').removeClass('wn-input-with-error');
            }
            const save_element = jQuery('.twbb-wn-inner-pages-settings-save');
            if( !save_element.hasClass('wn-change-exists') ) {
                save_element.addClass('wn-change-exists');
            }
        });
    }

    // Add validation logic for blur event
    inputValidationOnBlur() {
        jQuery(document).on('blur', '.twbb-wn-input-box input[type="text"]', function () {
            const input = jQuery(this);

            if (input.val().trim() === '') {// Show error message
                jQuery(this).parent('.twbb-wn-input-box').addClass('wn-input-with-error');
                jQuery('.twbb-wn-inner-pages-settings-save').removeClass('wn-change-exists');
            } else {
                // Hide error message
                jQuery(this).parent('.twbb-wn-input-box').removeClass('wn-input-with-error');
            }
        });
    }

    saveSettings() {
        let self = this;
        jQuery(document).on('click', '.twbb-wn-inner-pages-settings-save', function(e) {
            e.stopPropagation();
            if( !jQuery(this).hasClass('wn-change-exists')) {
                return;
            }
            const save_element = jQuery(this);
            const element_object_id = save_element.attr('data-element-id');
            const element_db_id = save_element.attr('data-element-db-id');
            const item = jQuery(`.twbb-wn-item[data-id=${element_object_id}]`);
            const type = item.attr('data-type');
            const object = item.attr('data-object');
            let data = {
                action: 'wn_change_item_settings',
                nonce: twbb_website_nav.nonce,
                element_object_id,
                element_db_id,
                type,
                object
            };

            let attr_name = '', all_inputs = jQuery('.twbb-wn-element-setting-box input');
            all_inputs.each(function() {
                if( jQuery(this).hasClass('twbb-input-has-changed') ) {
                    attr_name = jQuery(this).attr('data-attr-name');
                    //check input type
                    if (jQuery(this).attr('type') === 'checkbox') {
                        data[attr_name] = jQuery(this).is(':checked');
                    } else {
                        data[attr_name] = jQuery(this).val();
                    }
                }
            });
            all_inputs.attr('disabled', true);
            save_element.addClass('twbb-wn-button-loading');

            let old_home_page_value = item.find('.twbb-wn-home-page').length > 0;
            if( old_home_page_value && data['home_page'] === false ) {
                data['home_page'] = 'unset';
            }
            // Call the save function with the data
            jQuery.ajax({
                url: twbb_website_nav.ajaxurl,
                type: 'POST',
                data,
            })
            .done( (ajaxData) => {
                if (ajaxData.success) {
                    //for each data
                    for (const key in data) {
                        if (data.hasOwnProperty(key)) {
                            const value = data[key];
                            switch (key) {
                                case 'title':
                                    window.twbb_websiteNavigation.itemTitleChange(value, item);
                                    break;
                                case 'nav_item_title':
                                    window.twbb_websiteNavigation.itemNavTitleChange(value, item);
                                    break;
                                case 'slug':
                                    window.twbb_websiteNavigation.itemUrlSlugChange(value, ajaxData.data.url, item);
                                    jQuery('.twbb-wn-url-slug-setting-description').text(ajaxData.data.url).attr('href', ajaxData.data.url);
                                    break;
                                case 'url':
                                    window.twbb_websiteNavigation.itemCustomUrlChange(value, item);
                                    jQuery('.twbb-wn-description.twbb-wn-url-slug-setting-description').text(value).attr('href', value);
                                    break;
                                case 'status':
                                    window.twbb_websiteNavigation.itemStatusChange(value, item);
                                    break;
                                case 'home_page':
                                    window.twbb_websiteNavigation.itemHomePageChange(value, item);
                                    break;
                                default:
                                    // Handle unexpected keys if necessary
                                    break;
                            }
                        }
                    }

                    //this code is here to avoid duplication because of wordpress auto added to menu option
                    if( data['show_in_nav'] !== undefined ) {
                        const menuId = jQuery('#nav_menu_items').data('nav_id');

                        if( data['show_in_nav'] ) {
                            twbb_navMenuActions.addMenuItem(menuId, item, twbb_addNavSuccessCallback, true);
                            if( item.attr('data-nav_menu_status') ) {
                                item.attr('data-nav_menu_status','in_menu');
                            }
                        } else {
                            window.twbb_websiteNavigation.removeMenuItemFromList(item);
                            if( item.attr('data-nav_menu_status') ) {
                                item.attr('data-nav_menu_status','not_in_menu');
                            }
                            if( !['page', 'product', 'post'].includes(object) ) {
                                //go back because user can't other objects
                                jQuery('.twbb-wn-inner-page-settings').remove();
                            }
                        }
                    }



                    window.twbb_navMenuActions.reRenderNavMenu();
                }
            })
            .always( () => {
                all_inputs.attr('disabled', false);
                save_element.attr('disabled', false);
                save_element.removeClass('wn-change-exists').removeClass('twbb-wn-button-loading');
                if( self.save_from_trash === 1 ) {
                    self.save_from_trash = 0;
                    jQuery(document).find('.twbb-wn-move-to-trash-setting').trigger("click");
                }

            });
            if( jQuery(this).attr('data-triggered-from-nav-menu') === 'true' ) {
                analyticsDataPush('Website structure', 'Element settings edit', `Navigation menu ${object}`);
            } else {
                analyticsDataPush('Website structure', 'Element settings edit', object);
            }
        });
    }

    postDuplication() {
        jQuery(document).on('click', '.twbb-wn-duplicate-setting', function(e) {
            e.stopPropagation();
            const element_id = jQuery('.twbb-wn-inner-pages-settings-save').attr('data-element-id');
            let urlType = 'edit';
            if( jQuery('#edit-page-content-link a').attr('href').includes('&action=elementor')) {
                urlType = 'elementor';
            }
            window.TwbbPostDuplicator.duplicatePost(element_id, urlType)
                .then((url) => {
                    window.open(url, '_blank');
                })
                .catch((error) => {
                    console.error('Error duplicating post:', error);
                });
        });
    }

    moveToTrashEvent() {
        let self = this;
        jQuery(document).on('click', '.twbb-wn-move-to-trash-setting', function(e) {
            e.stopPropagation();
            self.moveToTrash(jQuery(this));
        })
    }

    moveToTrash(that) {
        let self = this;
        const trash_button = that;
        const element_id = jQuery('.twbb-wn-inner-pages-settings-save').attr('data-element-id');
        const post_type = 'page';

        trash_button.addClass('twbb-wn-button-loading');
        jQuery(document).find(".twbb-wn-loading-layer").show();

        if (jQuery(".twbb-wn-inner-pages-settings-save").hasClass("wn-change-exists")) {
            self.save_from_trash = 1;
            jQuery(".twbb-wn-inner-pages-settings-save").trigger("click");
            return;
        }

        let data = {
            action: 'wn_trash_management',
            task: 'wn_move_to_trash',
            nonce: twbb_website_nav.nonce,
            post_id: element_id
        };

        jQuery.ajax({
            type: 'POST',
            url: twbb_website_nav.ajaxurl,
            data: data,
            success: function (response) {
                if (response.success) {
                    trash_button.addClass('twbb-wn-button-success');
                    setTimeout(function() {
                        trash_button.removeClass('twbb-wn-button-success');
                        trash_button.removeClass('twbb-wn-move-to-trash-setting').addClass('twbb-wn-restore-from-trash-setting');
                        trash_button.find(".twbb-wn-settings-button-text").text(self.localized_data.restore_from_trash);
                    }, 1000);

                    const el = jQuery(document).find(".twbb-website-nav-sidebar-content .twbb-wn-item[data-id='"+element_id+"']");
                    el.attr('data-status', 'trash');
                    if( el.closest("#nav_menu_items").length ) {
                        window.twbb_websiteNavigation.removeMenuItemFromList(el);
                    }
                    /* Redirect to home page in case of trash page is active page */
                    if( el.hasClass("twbb-wn-item-active") ) {
                        setTimeout(function(){
                            window.location.href = twbb_website_nav.home_edit_url;
                        },1000);
                    }

                    /* Adding trashed item to trash page */
                    if( jQuery(document).find(".twbb-website-nav-sidebar-container.twbb-wn-inner-trash-page[data-type='"+post_type+"']").length ) {
                        const trash_item_template = jQuery('#twbb-wn-trash-page-item-template').html();
                        const title = jQuery("#wn-title-setting-id").val();
                        jQuery(".twbb-wn-inner-trash-page[data-type='page'] .twbb-wn-inner-trash-pages-content").prepend(trash_item_template);
                        const new_trash_item = jQuery(document).find(".twbb-wn-inner-trash-page[data-type='"+post_type+"'] .twbb-wn-trash-item-new");
                        new_trash_item.attr('data-id',element_id);
                        new_trash_item.find(".twbb-wn-trash-item-title").text(title);
                        new_trash_item.removeClass("twbb-wn-trash-item-new");
                        jQuery(document).find(".twbb-wn-inner-trash-page[data-type='"+post_type+"'] .twbb-wn-inner-pages-empty-trush-button").removeClass("twbb-empty-trush-button-inactive");
                    }
                } else {
                    jQuery(document).find(".twbb-wn-loading-layer").hide();
                }
                window.twbb_navMenuActions.reRenderNavMenu();
            },
            complete: function () {
                trash_button.removeClass('twbb-wn-button-loading');
            },
            error: function (jqXHR, exception) {
                trash_button.removeClass('twbb-wn-button-loading');
                jQuery(document).find(".twbb-wn-loading-layer").hide();
            },
        });
    }

    restoreFromTrash() {
        let self = this;
        jQuery(document).on('click', '.twbb-wn-restore-from-trash-setting', function(e) {
            e.stopPropagation();

            const trash_button = jQuery(this);
            const element_id = jQuery('.twbb-wn-inner-pages-settings-save').attr('data-element-id');
            const post_type = 'page';

            trash_button.addClass('twbb-wn-button-loading');
            trash_button.closest(".twbb-wn-inner-pages-content").find(".twbb-wn-loading-layer").show();

            let data = {
                action: 'wn_trash_management',
                task: 'wn_restore_from_trash',
                nonce: twbb_website_nav.nonce,
                post_id: element_id
            };

            TwbbPostTrashManager.restoreFromTrash(data).then(response => {
                if (response.success) {
                    trash_button.addClass('twbb-wn-button-success');
                    setTimeout(function() {
                        trash_button.removeClass('twbb-wn-button-success');
                        trash_button.addClass('twbb-wn-move-to-trash-setting').removeClass('twbb-wn-restore-from-trash-setting');
                        trash_button.find(".twbb-wn-settings-button-text").text(self.localized_data.move_to_trash);
                        trash_button.closest(".twbb-wn-inner-pages-content").find(".twbb-wn-loading-layer").hide();
                    }, 1000);
                    const el = jQuery(document).find(".twbb-website-nav-sidebar-content .twbb-wn-item[data-id='"+element_id+"']");
                    el.attr('data-status', 'draft');
                    el.find(".twbb-wn-status").text('Draft');

                    /* Removing restored item from trash page */
                    if( jQuery(document).find(".twbb-wn-inner-trash-page[data-type='"+post_type+"']").length  ) {
                        jQuery(document).find(".twbb-wn-inner-trash-page[data-type='"+post_type+"'] .twbb-wn-trash-item[data-id='"+element_id+"']").remove();
                        if( !jQuery(".twbb-wn-inner-trash-page[data-type='"+post_type+"'] .twbb-wn-trash-item").length ) {
                            jQuery(document).find(".twbb-wn-inner-trash-page[data-type='"+post_type+"'] .twbb-wn-inner-pages-empty-trush-button").addClass("twbb-empty-trush-button-inactive");
                        }
                    }
                }
                trash_button.removeClass('twbb-wn-button-loading');
            }).catch(error => {
                trash_button.removeClass('twbb-wn-button-loading');
            });
        })
    }

    renderInnerSettingsPage(element) {
        let self = this;
        const object = element.attr('data-object');
        self.localized_data = {
            'title': 'Title',
            'nav_label': 'Navigation label',
            'nav_description': 'The navigation title is the page name in the menu.',
            'url_slug': 'URL slug',
            'show_in_nav': 'Show in navigation menu',
            'page_status': 'Page status',
            'home_page': 'Set as Home Page',
            'home_page_description': 'This page will be set as the home page of your website.',
            'duplicate': 'Duplicate',
            'edit_page': 'Edit page',
            'move_to_trash': 'Move to Trash',
            'restore_from_trash': 'Restore',
            'show_in_menu_desc': `Only published ${object}s can be added to the menu.`,
        };
        let html_content = '';
        if( object === 'custom' ) {
            html_content += this.generateTextInputField(
                'wn-url-slug-setting-id',
                'wn-url-slug-setting',
                'URL*',
                element.attr('data-url'),
                `<a class="twbb-wn-description twbb-wn-url-slug-setting-description" href="${element.attr('data-url')}" target="_blank">${element.attr('data-url')}</a>`,
                'url',
                'URL slug cannot be empty.'
            );
            html_content += this.generateTextInputField(
                'wn-nav-label-setting-id',
                'wn-nav-label-setting',
                self.localized_data.nav_label,
                element.attr('data-nav_item_title'),
                `<span class="twbb-wn-description">${self.localized_data.nav_description}</span>`,
                'nav_item_title',
                'Navigation label cannot be empty.'
            );
            html_content += this.generateCheckbox(
                'wn-show-in-nav-setting-id',
                self.localized_data.show_in_nav,
                element.parents('#nav_menu_items').length > 0,
                `twbb-wn-show-in-nav-setting`,
                '',
                '',
                'show_in_nav',
                'active',
            );
        } else {
            html_content += this.generateTextInputField(
                'wn-title-setting-id',
                'wn-title-setting',
                self.localized_data.title,
                element.attr('data-title'),
                '',
                'title',
                'Page title cannot be empty.'
            );
            if( element.attr('data-nav_item_title') !== undefined ) {
                html_content += this.generateTextInputField(
                    'wn-nav-label-setting-id',
                    'wn-nav-label-setting',
                    self.localized_data.nav_label,
                    element.attr('data-nav_item_title'),
                    `<span class="twbb-wn-description">${self.localized_data.nav_description}</span>`,
                    'nav_item_title',
                    'Navigation label cannot be empty.'
                );
            }
            if( element.attr('data-slug') !== undefined && element.attr('data-url') !== undefined ) {
                html_content += this.generateTextInputField(
                    'wn-url-slug-setting-id',
                    'wn-url-slug-setting',
                    self.localized_data.url_slug,
                    element.attr('data-slug'),
                    `<a class="twbb-wn-description twbb-wn-url-slug-setting-description" href="${element.attr('data-url')}" target="_blank">${element.attr('data-url')}</a>`,
                    'slug',
                    'URL slug cannot be empty.'
                );
            }
            let bordered_class = '';
            if ( object === 'page' ) {
                bordered_class = 'twbb-wn-bordered-box';
                html_content += this.generateCheckbox(
                    'wn-status-setting-id',
                    `${self.localized_data.page_status}: <span class="twbb-wn-checkbox-label-status">${element.attr('data-status') === 'publish' ? 'Publish' : 'Draft'}</span>`,
                    element.attr('data-status') === 'publish',
                    'twbb-wn-status-setting',
                    '',
                    `${bordered_class}_up`,
                    'status',
                    'active'
                );
            }
            const in_menu = element.attr('data-nav_menu_status') === 'in_menu' || element.parents('#nav_menu_items').length > 0;
            const item_status = element.attr('data-status') === 'publish' || in_menu ? 'active' : 'disabled';
            const item_sho_in_nav_desc =
                    ( item_status === 'active' || object === 'page' ) ? '' : self.localized_data['show_in_menu_desc'];
            html_content += this.generateCheckbox(
                'wn-show-in-nav-setting-id',
                self.localized_data.show_in_nav,
                in_menu,
                `twbb-wn-show-in-nav-setting ${item_status === 'active' ? '' : 'disabled'}`,
                item_sho_in_nav_desc,
                `${bordered_class}_bottom`,
                'show_in_nav',
                element.parents('#nav_menu_items').length > 0 ? 'active' : item_status,
            );
            if ( object === 'page' ) {
                html_content += this.generateCheckbox(
                    'wn-home-page-setting-id',
                    self.localized_data.home_page,
                    element.find('.twbb-wn-home-page').length > 0,
                    `twbb-wn-home-page-setting ${item_status === 'active' ? '' : 'disabled'}`,
                    self.localized_data.home_page_description,
                    '',
                    'home_page',
                    element.find('.twbb-wn-home-page').length > 0 ? 'active' : item_status,
                );

                html_content += `<div class="twbb-wn-element-setting-box twbb-wn-link-box twbb-wn-duplicate-setting">
                <span class="twbb-wn-tooltip-links">${self.localized_data.duplicate}</span>
            </div>`;
                html_content += this.generateLinkBox(
                    'edit-page-content-link',
                    self.localized_data.edit_page,
                    element.attr('data-content_edit_link'),
                    'twbb-wn-edit-content-setting',
                    ''
                );
                html_content += `<div class="twbb-wn-loading-layer"></div><div class="twbb-wn-trash-container">
                                    <div class="twbb-wn-element-setting-box twbb-wn-trash-box twbb-wn-move-to-trash-setting">
                                        <span class="wn-move-to-trash-button twbb-wn-settings-button-text">${self.localized_data.move_to_trash}</span>
                                        <span class="twbb-wn-settings-button-spinner"></span>
                                        <span class="twbb-wn-settings-button-success"></span>
                                    </div>
                                </div>`;
            }
            if (object !== 'page') {
                bordered_class = '';
                if (element.attr('data-template_link') !== '' && element.attr('data-template_link') !== undefined) {
                    if (element.attr('data-type') !== 'taxonomy') {
                        bordered_class = 'twbb-wn-bordered-box';
                    }
                    html_content += this.generateLinkBox(
                        'edit-template-link',
                        `Edit ${element.attr('data-template_title')} template`,
                        element.attr('data-template_link'),
                        `twbb-wn-edit-template-setting  ${bordered_class}_up`,
                        ''
                    );
                }
                if ( element.attr('data-type') !== 'taxonomy' ) {
                    html_content += this.generateLinkBox(
                        'edit-content-link',
                        `Edit ${element.attr('data-nav_label')} content`,
                        element.attr('data-content_edit_link'),
                        `twbb-wn-edit-content-setting  ${bordered_class}_bottom`,
                        ''
                    );
                }
            }
        }
        return html_content;
    }

    // Helper function to generate input fields
    generateTextInputField(id, name, label, value, description = '', attr_name = '', error_message = '') {
        //hint: attr_name should be the same as the name of the main nav item data attr
        return `
    <div class="twbb-wn-element-setting-box twbb-wn-input-box ${id}">
        <label for="${id}">${label}</label>
        <input type="text" id="${id}" name="${name}" value="${value}" data-attr-name="${attr_name}"/>
        <span class="twbb-wn-error-message">${error_message}</span>
        ${description ? `${description}` : ''}
    </div>`;
    }

    // Helper function to generate checkboxes
    generateCheckbox(id, label, isChecked, additionalClass = '', description = '',
                     additionalContainerClass = '', attr_name = '', item_status = '') {
        //hint: attr_name should be the same as the name of the main nav item data attr
        return `
    <div class="twbb-wn-element-setting-box twbb-wn-checkbox-box ${isChecked ? 'checked' : ''} ${additionalClass}">
        <div class="twbb-wn-checkbox-box-container ${additionalContainerClass}">
            <span>${label}</span>
            <label class="twbb-wn-switcher">
                <input id="${id}" type="checkbox" ${isChecked ? 'checked' : ''} data-attr-name="${attr_name}"
                ${item_status === 'active' ? '' : 'disabled'}>
                ${id === 'wn-status-setting-id' ? `
                <span class="twbb-wn-slide-switcher twbb-tooltip-parent-container-item" 
                            data-tooltip-text="${isChecked ? 'Move to Draft' : 'Publish'}"></span>` : `<span class="twbb-wn-slide-switcher"></span>`}  
            </label>
        </div>
        ${description ? `<span class="twbb-wn-description">${description}</span>` : ''}
    </div>`;
    }

    generateLinkBox(id, label, url, additionalClass = '', description = '') {
        return `
    <div class="twbb-wn-element-setting-box twbb-wn-link-box ${additionalClass}" id="${id}">
        <a class="twbb-wn-tooltip-links" href="${url}" target="_blank">
            ${label}
        </a>
        ${description ? `<span class="twbb-wn-description">${description}</span>` : ''}
    </div>`;
    }
}
