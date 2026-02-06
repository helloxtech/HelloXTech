class TWBB_WebsiteNavigation {
    constructor() {
        this.init();
    }

    init() {
        this.events();
    }

    events() {
        this.blankInputBlurEvent();
        this.blankInputKeydownEvent();
        this.openWebsiteNavigation();
        this.addBlankPageEvent();
        this.removeWNError();
        this.openTooltip();
        this.closeTooltipOnClick();
        this.openSubTooltip();
        this.backToMainTooltip();
        this.addMenuItemFromOptions();
        this.addCustomLinkMenuItem();
        this.searchMenuItem();
        this.clearSearch();
        this.tooltipContent();
        this.removeMenuItem();
        this.openSettingsInnerPage();
        this.openOtherTypesInnerPage();
        this.hideOtherInnerPages();
        this.openManageTrashMenu();
        this.openTrashPage();
        this.closeTrashPage();
        this.emptyTrashEvent();
        this.restoreFromTrashEvent();
        this.disableWebNavButton();
    }

    blankInputBlurEvent() {
        jQuery(document).on('blur', '.twbb-wn-blank-page-input', () => {
            let blankPageInput = jQuery(document).find('.twbb-wn-blank-page-input');
            if( blankPageInput.length && !blankPageInput.hasClass('creation-in-progress') ) {
                this.addBlankPage();
            }
        });
    }

    blankInputKeydownEvent() {
        let self = this;
        jQuery(document).on('keydown', 'input.twbb-website-nav-sidebar-item__title-input', function(event) {
            if ( (event.key === 'Enter' || event.keyCode === 13) && !jQuery(this).hasClass('creation-in-progress')){
                self.addBlankPage();
            }
        });
    }

    disableWebNavButton() {
        /* Disable Custom buttons if Panel closed */
        jQuery(document).on("click", "#elementor-mode-switcher", function () {
            if ( jQuery("body").hasClass("elementor-editor-preview") ) {
                jQuery('.twbb_website_structure-topbar-button').removeClass('disabled');
            } else {
                if( !jQuery('.twbb_website_structure-topbar-button').hasClass('disabled')) {
                    jQuery('.twbb_website_structure-topbar-button').addClass('disabled');
                }
            }
        });
    }

    openWebsiteNavigation() {
        let self = this;
        jQuery(document).on('click', '.twbb_website_structure-topbar-button', function () {
            analyticsDataPush('Website structure', 'Manage Navigation button click', 'Top bar');
            jQuery(this).closest(".twbb-wn-structure-sub-menu-content").hide();
            if( jQuery(this).hasClass("twbb-wn-active") && jQuery(document).find(".twbb-website-nav-sidebar-main").hasClass('twbb-animated-sidebar-show') ) {
                twbb_animate_sidebar('close', jQuery('.twbb-website-nav-sidebar-main'), 380, 'twbb-website-navigation-sidebar-opened', twbb_closeWebsiteNavigation);
            } else {
                self.websiteNavigationOpenFunctions(jQuery(this));
            }
        });
    }

    websiteNavigationOpenFunctions(that) {
        twbb_triggerWebsiteNavigationButton(that);

        this.changePageStatusOnSave();
        jQuery('.twbb-website-nav-sidebar-content').scroll(() => {
            twbb_closeTooltip();
        });
    }

    addBlankPageEvent() {
        let self = this;
        jQuery(document).on('click', '.twbb-wn-add-blank-page', function () {
            if( !jQuery('.twbb-wn-blank-page-input').length ) {
                self.createBlankPageInput(jQuery(this));
            }
        });
    }

    createBlankPageInput(that) {
        let itemInputTemplate = `<div class="twbb-website-nav-sidebar-item twbb-wn-item menu-item twbb-wn-blank-page-input">
                          <div class="menu-item-handle" style="width: 100%;">
                            <input class="twbb-website-nav-sidebar-item__title-input" value="New Page">
                          </div>
                        </div>`;
        jQuery('.twbb-website-nav-sidebar-pages-items').prepend(itemInputTemplate);
        jQuery(that).closest('.wn-action-tooltip').css('display', 'none');
        jQuery('input.twbb-website-nav-sidebar-item__title-input').select();
    }

    addBlankPage() {
        let blankPageInput = jQuery('.twbb-wn-blank-page-input');
        if( blankPageInput.length && !blankPageInput.hasClass('creation-in-progress') ) {
            blankPageInput.addClass('creation-in-progress');
            var blankPageTitle = blankPageInput.find('input.twbb-website-nav-sidebar-item__title-input').val();
            //check if function exists
            if( blankPageTitle === '' || blankPageTitle === undefined ) {
                blankPageTitle = 'New Page';
            }
            if (typeof twbb_create_blank_page !== undefined ) {
                twbb_create_blank_page(this, blankPageTitle, false);
            }
        }
    }

    removeWNError() {
        jQuery(document).on('transitionend', '.twbb-navmenu-sidebar-error', function () {
            jQuery(this).remove();
            if (!jQuery('.twbb-navmenu-sidebar-error').length) {
                jQuery('.twbb-website-nav-sidebar-content').removeClass('twbb-navmenu-sidebar-with-error');
            }
        });
    }

    openTooltip() {
        jQuery(document).on('click', '.twbb-wn-tooltip-parent', function (e) {
            e.stopPropagation();
            if (!jQuery(e.target).hasClass('twbb-wn-tooltip-parent')) return;

            twbb_closeTooltip();
            let top = jQuery(this).offset().top + jQuery(this).outerHeight() + 10 - 48,
                left = jQuery(this).offset().left;

            if (jQuery(this).hasClass('twbb-empty-nav-tooltip-container')) {
                top = jQuery(this).offset().top;
                left = jQuery(this).offset().left + jQuery(this).outerWidth() + 10 - 48;
            }

            if (jQuery(this).hasClass('wn-add-menu-item')) {
                const template = jQuery('#twbb-wn-add-menu-item-action-tooltip').html();
                if (!jQuery(this).find('.wn-add-menu-item-action-tooltip').length) {
                    jQuery(this).append(template);
                }
                jQuery('.twbb-wn-secondary-container').hide();
                jQuery('.wn-action-tooltip-container.twbb-wn-main-container').show();
            }

            const thisTooltip = jQuery(this).find('.wn-action-tooltip');
            jQuery(this).addClass('twbb-opacity-1');
            thisTooltip.css({ top: `${top}px`, left: `${left}px`, display: 'block' });
            jQuery(this).addClass('twbb_active');
        });
    }

    openManageTrashMenu() {
        jQuery(document).on('click', '.twbb-wn-manage-trash .wn-menu-icon', function (e) {
            e.stopPropagation();
            let trashEl = jQuery(this).closest(".twbb-wn-manage-trash");
            if( trashEl.hasClass("wn-menu-trash-active") ) {
                trashEl.removeClass("wn-menu-trash-active");
            } else {
                trashEl.addClass("wn-menu-trash-active");
            }
        });
    }

    closeTooltipOnClick() {
        jQuery(document).on('click', (e) => {
            if (!jQuery(e.target).closest('.twbb-wn-tooltip-parent').length) {
                twbb_closeTooltip();
            }
        });
    }

    openSubTooltip() {
        jQuery(document).on('click', '.twbb-wn-main-container .twbb-wn-action-tooltip-item', function (e) {
            e.stopPropagation();
            twbb_renderSubActionTooltip(jQuery(this));
        });
    }

    openTrashPage() {
        jQuery(document).on('click', '.wn-menu-trash-active .twbb-wn-manage-trash-button', function (e) {
            e.stopPropagation();
            twbb_renderPageTrash(jQuery(this));
        });
    }

    closeTrashPage() {
        jQuery(document).on('click', '.twbb-wn-trash-back-to-main-sidebar, .twbb-wn-trash-inner-pages-header-title', function (e) {
            e.stopPropagation();
            jQuery(this).closest('.twbb-wn-inner-trash-page').hide();
        });
    }

    emptyTrashEvent() {
        let self = this;
        jQuery(document).on('click', '.twbb-wn-delete_from_trash', function (e) {
            e.stopPropagation();
            const post_id = jQuery(this).closest(".twbb-wn-trash-item").attr("data-id");
            self.emptyTrash(post_id, jQuery(this));
        });

        jQuery(document).on('click', '.twbb-wn-inner-pages-empty-trush-button', function (e) {
            e.stopPropagation();
            self.emptyTrash(0, jQuery(this) );
        });
    }

    restoreFromTrashEvent() {
        let self = this;
        jQuery(document).on('click', '.twbb-wn-restore_from_trash', function (e) {
            e.stopPropagation();
            self.restoreItemFromTrash(jQuery(this) );
        });
    }

    restoreItemFromTrash( element ) {
        const post_id = element.closest(".twbb-wn-trash-item").attr("data-id");
        const post_type = element.closest(".twbb-wn-inner-trash-page").attr("data-type");

        if ( element.closest(".twbb-wn-trash-item").hasClass("twbb-wn-trash-item-loading") ) {
            return;
        }

        element.closest(".twbb-wn-trash-item").addClass("twbb-wn-trash-item-loading");
        element.addClass("twbb-wn-button-loading");

        let data = {
            action: 'wn_trash_management',
            task: 'wn_restore_from_trash',
            nonce: twbb_website_nav.nonce,
            post_id: post_id
        };


        TwbbPostTrashManager.restoreFromTrash(data).then(response => {
            if (response.success) {
                element.closest(".twbb-wn-inner-trash-page").find(".twbb-wn-trash-item[data-id='"+post_id+"']" ).remove();
                if( !element.closest(".twbb-wn-inner-trash-page").find(".twbb-wn-trash-item").length ) {
                    element.closest(".twbb-wn-inner-trash-page").find(".twbb-wn-inner-pages-empty-trush-button").addClass("twbb-empty-trush-button-inactive");
                }
                const el = jQuery(document).find(".twbb-website-nav-sidebar-content .twbb-wn-item[data-id='"+post_id+"']");
                if( el.length ) {
                    el.attr('data-status', 'draft');
                    el.find(".twbb-wn-status").text('Draft');
                } else {
                    let template = response.data.content;
                    jQuery(document).find(".twbb-website-nav-sidebar-pages-container[data-post-type='"+post_type+"'] .twbb-website-nav-sidebar-pages-items").append(template);
                }
            }
            element.closest(".twbb-wn-trash-item").removeClass(".twbb-wn-button-loading");
            element.removeClass(".twbb-wn-button-loading");
            window.twbb_navMenuActions.reRenderNavMenu();
        }).catch(error => {
            element.closest(".twbb-wn-trash-item").removeClass(".twbb-wn-button-loading");
            element.removeClass(".twbb-wn-button-loading");
        });

    }

    emptyTrash( post_id = 0, element ) {
        const post_type = element.closest(".twbb-wn-inner-trash-page").attr("data-type");
        const emptyTrashButton = jQuery(document).find("twbb-wn-inner-trash-page[data-type='"+post_type+"'] .twbb-wn-inner-pages-empty-trush-button");
        if( emptyTrashButton.hasClass("twbb-wn-button-loading") || emptyTrashButton.hasClass("twbb-empty-trush-button-inactive")) {
            return;
        }

        if( post_id === 0 ) {
            element.addClass("twbb-wn-button-loading");
        } else {
            if ( element.closest(".twbb-wn-trash-item").hasClass("twbb-wn-trash-item-loading") ) {
                return;
            }

            element.closest(".twbb-wn-trash-item").addClass("twbb-wn-trash-item-loading");
            element.addClass("twbb-wn-button-loading");
        }

        jQuery.ajax({
            url: twbb_website_nav.ajaxurl,
            type: 'POST',
            data: {
                action: 'wn_trash_management',
                task: 'wm_empty_trash',
                post_id: post_id,
                post_type: post_type,
                nonce: twbb_website_nav.nonce,
            },
        })
        .done( (data) => {
            if (data.success) {
                if( post_id === 0 ) {
                    element.closest(".twbb-wn-inner-trash-page").find(".twbb-wn-inner-trash-pages-content").empty();
                } else {
                    element.closest(".twbb-wn-inner-trash-page").find(".twbb-wn-trash-item[data-id='"+post_id+"']" ).remove();
                }

                if( !element.closest(".twbb-wn-inner-trash-page").find(".twbb-wn-inner-trash-pages-content .twbb-wn-trash-item").length ) {
                    element.closest(".twbb-wn-inner-trash-page").find(".twbb-wn-inner-pages-empty-trush-button").addClass("twbb-empty-trush-button-inactive");
                }
                window.twbb_navMenuActions.reRenderNavMenu();
            }
        })
        .always(() => {
            element.closest(".twbb-wn-inner-trash-page").find(".twbb-wn-inner-pages-empty-trush-button").removeClass("twbb-wn-button-loading");
            element.closest(".twbb-wn-trash-item").removeClass(".twbb-wn-button-loading");
            element.removeClass(".twbb-wn-button-loading");
        });
    }

    backToMainTooltip() {
        jQuery(document).on('click', '.twbb-wn-back-add-to-menu-button', function (e) {
            e.stopPropagation();
            jQuery(this).closest('.twbb-wn-secondary-container').hide();
            jQuery('.wn-action-tooltip-container.twbb-wn-main-container').show();
        });
        jQuery(document).on('click', '.twbb-wn-action-tooltip-title', function (e) {
            e.stopPropagation();
            jQuery(this).closest('.twbb-wn-secondary-container').hide();
            jQuery('.wn-action-tooltip-container.twbb-wn-main-container').show();
        });
    }

    addMenuItemFromOptions() {
        jQuery(document).on('click', '.twbb-wn-secondary-container .twbb-wn-action-tooltip-item', function (e) {
            e.stopPropagation();
            if (jQuery('.twbb-website-nav-sidebar-main').hasClass('disable-ajax-in-progress')) return;

            const menuId = jQuery('#nav_menu_items').data('nav_id');
            twbb_navMenuActions.addMenuItem(menuId, jQuery(this), twbb_addNavSuccessCallback);
        });
    }

    searchMenuItem() {
        jQuery(document).on('input', '.twbb-wn-secondary-container .twbb-wn-search', function () {
            twbb_searchInit(jQuery(this));
        });
        jQuery(document).on('input', '.twbb-wn-inner-pages-content .twbb-wn-search', function () {
            twbb_searchInit(jQuery(this), '.twbb-wn-item');
        });
    }

    clearSearch() {
        jQuery(document).on('click', '.twbb-wn-clear-search', function () {
            const searchWrapper = jQuery(this).closest('.twbb-wn-search-wrapper');
            searchWrapper.find('.twbb-wn-search').val('');
            const tooltipContainer = jQuery(this).closest('.twbb-wn-search-container');
            tooltipContainer.find('.twbb-wn-action-tooltip-item').show();
            tooltipContainer.find('.twbb-wn-item').show();
            tooltipContainer.find('.twbb-wn-search-noresult').hide();
        });
    }

    tooltipContent() {
        jQuery(document)
            .on('mouseenter', '.twbb-tooltip-parent-container-item', function (e) {
                if (!jQuery(e.target).hasClass('twbb-tooltip-parent-container-item')) {
                    return;
                }
                const text = jQuery(this).attr('data-tooltip-text'),
                    //48 is a top bar height, 10 is a distance from element
                    top = jQuery(this).offset().top + jQuery(this).outerHeight() + 10 - 48,
                    left = jQuery(this).offset().left;
                jQuery('.twbb-tooltip-parent-container .twbb-tooltip').text(text);
                jQuery('.twbb-tooltip-parent-container').css({ top: `${top}px`, left: `${left}px`, display: 'block' });
            })
            .on('mouseleave', '.twbb-tooltip-parent-container-item', () => {
                jQuery('.twbb-tooltip-parent-container').hide();
            });
    }

    removeMenuItem() {
        const self = this;
        jQuery(document).on('click', '.twbb-wn-action-remove', function () {
            const item = jQuery(this).closest('.twbb-website-nav-sidebar-item');
            self.removeMenuItemFromList(item);
        });
    }

    removeMenuItemFromList(item, trash = false) {
        if (jQuery('.twbb-website-nav-sidebar-main').hasClass('disable-ajax-in-progress')) return;

        const navMenu = jQuery('#nav_menu_items'),
            children = item.childMenuItems();

        twbb_navMenuActions.removeMenuItem(item, twbb_removeNavMenuItemCallback, trash);

        if (children.length) {
            children.shiftDepthClass(-1);
            twbb_navMenuActions.bulkEditMenu(children, navMenu.attr('data-nav_id'));
        }
    }

    addCustomLinkMenuItem() {
        jQuery(document).on('click', '.twbb-wn-add-custom-menu-item-button', function () {
            if (jQuery(this).hasClass('disabled')) return;

            const parent = jQuery(this).closest('.twbb-wn-secondary-container'),
                urlInput = parent.find('#wn-custom-link-nav-url').val(),
                menuId = jQuery('#nav_menu_items').data('nav_id');

            if (urlInput !== '') {
                twbb_navMenuActions.addMenuItem(menuId, jQuery(this), twbb_addNavSuccessCallback);
            }
            //empty inputs
            parent.find('#wn-custom-link-nav-label').val('');
            parent.find('#wn-custom-link-nav-url').val('');
        });
    }

    openSettingsInnerPage() {
        window.twbb_websiteNavigationInnerSettings = new TWBB_WebsiteNavigationInnerSettings();
        jQuery(document).on('click', '.twbb-wn-action-settings' , function() {
            let triggered_form_nav_menu = false;
            if( jQuery(this).parents('.twbb-website-nav-sidebar-nav-menus-items').length > 0 ) {
                triggered_form_nav_menu = true;
            }
            twbb_renderInnerSettings(jQuery(this), triggered_form_nav_menu);
        });
    }

    changePageStatusOnSave() {
        const document_id = elementor.config.document.id;
        const document_status = elementor.config.document.status.value;
        const nav_item = jQuery(`.twbb-wn-item[data-id=${document_id}]`);
        const status = document_status === 'publish';
        this.itemStatusChange(status, nav_item);
        /* Need to wait while elementor finish save action then fire our actions for not getting data with draft status */
        elementor.channels.editor.on('saved', () => {
            const status = elementor.config?.document?.status?.value;
            if (status === 'publish') {
                this.itemStatusChange(true, nav_item);
                window.twbb_navMenuActions.reRenderNavMenu();
            }
        });
    }

    openOtherTypesInnerPage() {
        jQuery(document).on('click', '.twbb-website-nav-sidebar-other-item' , function() {
            twbb_renderOtherTypesInnerPage(jQuery(this));
        });
    }

    hideOtherInnerPages() {
        jQuery(document).on('click', '.twbb-wn-inner-page-items .twbb-wn-inner-pages-back' , function() {
            jQuery(this).closest('.twbb-wn-inner-page-items').addClass('hide');
        });
    }

    itemStatusChange(status, nav_item) {
        let old_status = nav_item.attr('data-status');
        if( (old_status === 'publish' && status) || (old_status === 'draft' && !status) ) {
            return;
        }
        if( status ) {
            nav_item.attr('data-status', 'publish').addClass('twbb-good-for-action');
            let id = nav_item.attr('data-id');
            jQuery(`.twbb-wn-item[data-id=${id}] .twbb-wn-status`).text('');
            let auto_added_menus = twbb_website_nav.auto_added_menus;
            const menu_id = parseInt(jQuery('#nav_menu_items').attr('data-nav_id'));
            if ( auto_added_menus &&  (Array.isArray(auto_added_menus) ? auto_added_menus.includes(menu_id) : Object.values(auto_added_menus).includes(menu_id) )) {
                //check if nav_item is not in nav menu
                if( !jQuery(`.twbb-website-nav-sidebar-nav-menus-items .twbb-wn-item[data-id=${id}]`).length ) {
                    twbb_navMenuActions.addMenuItem(menu_id, nav_item, twbb_addNavSuccessCallback, true);
                    jQuery('.twbb-website-nav-sidebar-nav-menus-items').append(nav_item);
                    nav_item.attr('data-nav_item_title', nav_item.attr('data-title'));
                    nav_item.attr('data-nav_menu_status','in_menu');
                    jQuery("#nav_menu_items, #pages_items").sortable("refresh");
                }
            }
        } else {
            nav_item.attr('data-status', 'draft').removeClass('twbb-good-for-action');
            nav_item.find('.twbb-wn-status').text('Draft');
            //check if nav_item is the editing page
            const current_page_id = elementor.config.document.id;
            if( current_page_id === parseInt(nav_item.attr('data-id')) ) {
                jQuery('.twbb-wn-structure-page-status').text('(Draft)').css('display', 'inline-block');
            }
        }
    }
    itemTitleChange(title, nav_item) {
        nav_item.attr('data-title', title);
        const id = nav_item.attr('data-id');
        jQuery(`.twbb-website-nav-sidebar-items:not(.twbb-website-nav-sidebar-nav-menus-items) .twbb-wn-item[data-id=${id}] .twbb-wn-title`).text(title);
    }
    itemNavTitleChange(title, nav_item) {
        nav_item.attr('data-nav_item_title', title);
        const id = nav_item.attr('data-id');
        jQuery(`.twbb-website-nav-sidebar-nav-menus-items .twbb-wn-item[data-id=${id}] .twbb-wn-title`).text(title);
    }
    itemUrlSlugChange(slug, url, nav_item) {
        nav_item.attr('data-slug', slug).attr('data-url', url);
    }
    itemCustomUrlChange(url, nav_item) {
        nav_item.attr('data-url', url);
    }
    itemHomePageChange( value, nav_item) {
        jQuery('.twbb-website-nav-sidebar-item__title').removeClass('twbb-wn-home-page');
        if( value === true ) {
            nav_item.find('.twbb-website-nav-sidebar-item__title').addClass('twbb-wn-home-page');
        }
    }
}

jQuery(document).ready(function() {
    window.twbb_websiteNavigation = new TWBB_WebsiteNavigation();

    window.TwbbPostTrashManager = window.TwbbPostTrashManager || {};
    window.TwbbWNItem = window.TwbbWNItem || {};

    /**
     * Restore from trash via AJAX
     *
     * @param {Object} data - The AJAX data to send
     * @return {Promise<Object>} - The response JSON
     */
    window.TwbbPostTrashManager.restoreFromTrash = function (data) {
        return fetch(twbb_website_nav.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: new URLSearchParams(data)
        })
            .then(res => res.json())
            .then(response => {
                if (!response.success) {
                    throw new Error(response.data || 'Restore failed');
                }
                return response;
            });
    };

    /**
     * Get Website navigation sidebar item template via AJAX
     *
     * @param {Object} data - The AJAX data to send
     * @return {Promise<Object>} - The response JSON
     */
    window.TwbbWNItem.getWNSidebarItem = function (data) {
        return fetch(twbb_website_nav.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: new URLSearchParams(data)
        })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    const template = response.data.content;
                    if( template !== '' ) {
                        jQuery(document)
                            .find(".twbb-website-nav-sidebar-pages-container[data-post-type='" + response.data.post_type + "'] .twbb-website-nav-sidebar-items")
                            .prepend(template);
                    }
                    window.twbb_navMenuActions.showSavedLabel('twbb-website-nav-sidebar-pages-container');
                }
                else {
                    throw new Error(response.data || 'Restore failed');
                }
            });
    };
})

function twbb_renderInnerSettings(element, triggered_form_nav_menu = false) {
    let html_content = jQuery('#twbb-wn-inner-page-settings').html();
    const main_element = element.parents('.twbb-wn-item');
    const webNavSettings = window.twbb_websiteNavigationInnerSettings;
    jQuery('.twbb-website-nav-sidebar-main').append(html_content);
    const element_object = main_element.attr('data-object');
    const object_mapping = {
        'page': 'Page',
        'post': 'Post',
        'custom': 'Custom link',
        'category': 'Post category',
        'tag': 'Post tag',
        'product_cat': 'Product category',
        'product_tag': 'Product collection',
        'product_brand': 'Product brand',
    }
    let title_text = object_mapping[element_object] !== undefined ? object_mapping[element_object] : twbb_capitalizeWords(element_object);
    jQuery('.twbb-wn-inner-page-settings .twbb-wn-inner-pages-header-title').text(title_text + ' settings');
    let the_page_content = webNavSettings.renderInnerSettingsPage(main_element);
    jQuery('.twbb-wn-inner-page-settings .twbb-wn-inner-pages-content').append(the_page_content);
    jQuery('.twbb-wn-inner-pages-settings-save').attr('data-element-id', main_element.attr('data-id') )
        .attr('data-element-db-id', main_element.find('.menu-item-data-db-id').val() )
        .attr('data-triggered-from-nav-menu', triggered_form_nav_menu )
        .attr('data-object',  main_element.attr('data-object') );
}

function twbb_renderOtherTypesInnerPage(element) {
    let html_content = jQuery('#twbb-wn-inner-page-items').html();
    let inner_page_by_type = jQuery(`.twbb-wn-inner-page-items[data-post-type=${element.data('post-type')}]`);
    if (!inner_page_by_type.length) {
        //add class to the html_content first div
        html_content = html_content.replace(/<div class="twbb-website-nav-sidebar-container twbb-wn-inner-page-items">/g, `<div class="twbb-website-nav-sidebar-container twbb-wn-inner-page-items" data-post-type="${element.data('post-type')}">`);
        jQuery('.twbb-website-nav-sidebar-main').append(html_content);

        inner_page_by_type = jQuery(`.twbb-wn-inner-page-items[data-post-type=${element.data('post-type')}]`);
        let title_text = element.attr('data-type-title');
        inner_page_by_type.find('.twbb-wn-inner-pages-header-title').text(title_text);
        twbb_otherTypesInnerPage(element, 1, true);
    } else {
        inner_page_by_type.removeClass('twbb-animated-sidebar-hide').addClass('twbb-animated-sidebar-show');
    }
}

function twbb_triggerWebsiteNavigationButton(element) {
    if (element.hasClass('disabled')) {
        return;
    }
    if (element.hasClass('selected') && jQuery(document).find(".twbb-website-nav-sidebar-main").hasClass('twbb-animated-sidebar-show') ) {
        twbb_animate_sidebar('close', jQuery('.twbb-website-nav-sidebar-main'), 380, 'twbb-website-navigation-sidebar-opened', twbb_closeWebsiteNavigation);
        return;
    }
    //close the invisible backdrop from elementor
    jQuery('.MuiBackdrop-invisible').trigger('click');
    element.addClass('selected');
    const header_add_element_button = jQuery('.MuiToolbar-root .MuiBox-root .MuiGrid-root:first-child .MuiStack-root:eq(1) .MuiBox-root:first-child button');
    header_add_element_button.removeClass('Mui-selected');
    //open website navigation
    if ( !jQuery('.twbb-website-nav-sidebar-main').length ) {
        let website_navigation_sidebar = jQuery('#twbb-navmenu-sidebar-template').html();
        jQuery('#elementor-editor-wrapper-v2').append(website_navigation_sidebar);
        twbb_navMenuSortable();
    }
    twbb_animate_sidebar('open', jQuery('.twbb-website-nav-sidebar-main'), 380, 'twbb-website-navigation-sidebar-opened', twbb_closeWebsiteNavigation);
    //add template for adding pages just for saving time
    let template = jQuery("#twbb-wn-add-menu-item-action-tooltip").html();
    if( !jQuery('.wn-add-menu-item-action-tooltip').length ) {
        jQuery('.twbb-website-nav-sidebar-navigation-header .twbb-wn-add-item.wn-add-menu-item').append(template);
    }
}

function    twbb_closeWebsiteNavigation() {
    jQuery('.MuiButtonBase-root[aria-label="Add Element"]').addClass('Mui-selected');
    jQuery('.twbb_website_structure-topbar-button ').removeClass('selected');
    jQuery('.twbb-website-nav-sidebar-main').removeClass('twbb-animated-sidebar-show').addClass('twbb-animated-sidebar-hide');
    jQuery("body").removeClass('twbb-website-navigation-sidebar-opened');
    jQuery(document).find(".twbb_website_structure-topbar-button.twbb-wn-active").removeClass("twbb-wn-active");

}

function twbb_webNavSidebarErrorClose(error) {
    jQuery(`.${error}`).addClass('remove-animation');
}

/* keeping pagination last loaded page number for every post type */
let twbb_page = [];
/* keeping post types which are fully loaded and has no pagination */
let twbb_finished = [];
let twbb_loading = false;
let twbb_lastScrollTop = 0;

/**
 * Function fire ajax request and get items for post type
 *
 * @param element object
 * @param page integer page number which should be requested
 * @paran scroll bool for checking if function called during the scroll
*/
function twbb_renderSubActionTooltip(element, page = 1, scroll = false, add_parent_container = true) {
    if (twbb_loading) {
        return;
    }
    let post_type = element.data('post-type'),
        type = element.data('type'),
        action_element = element.closest('.wn-add-menu-item-action-tooltip').find(`.wn-action-tooltip-container[data-post-type="${post_type}"]`),
        html_title = element.text();
    if( typeof twbb_finished[post_type] === 'undefined' ) {
        twbb_finished[post_type] = false;
    }

    twbb_attachScroll(element.closest('.wn-add-menu-item-action-tooltip'), element, twbb_renderSubActionTooltip); //  ATTACH SCROLL AFTER FIRST LOAD

    if( post_type !== 'custom' && (!action_element.length || (scroll && !twbb_finished[post_type])) ) {
        twbb_loading = true;
        if( action_element.length > 0 ) {
            action_element.find('.twbb-wn-action-tooltip-items').append('<div class="twbb-wn-item-loading"></div>');
        }
        let container = element.closest('.wn-add-menu-item-action-tooltip');
        add_parent_container = false;
        if( page === 1 ) {
            add_parent_container = true;
        }
        twbb_getDataByTypeAjax(post_type, type, html_title, page, container, container.find(".twbb-wn-action-tooltip-items:visible"), add_parent_container, 10, 'tooltip', 'true');
    }
    jQuery('.twbb-wn-main-container').css('display', 'none');
    if( action_element.length > 0 ) {
        action_element.css('display', 'block');
    } else {
        '<div class="twbb-wn-item-loading"></div><div class="twbb-wn-item-loading"></div><div class="twbb-wn-item-loading"></div>'
        action_element.find('.twbb-wn-action-tooltip-items').prepend(
            '<div class="twbb-wn-item-loading"></div><div class="twbb-wn-item-loading"></div><div class="twbb-wn-item-loading"></div>'
        );
    }
}

function twbb_getDataByTypeAjax(post_type,
                                type,
                                html_title,
                                page,
                                main_container,
                                items_container,
                                add_parent_container = false,
                                pages_count_per_requested = 10,
                                rendering_type = 'tooltip',
                                exclude_menu_items = 'true',
                                exclude_draft = 'true') {

    const current_page_id = jQuery(document).find(".twbb-website-nav-sidebar-container").attr('data-current-page_id');


    jQuery.ajax({
        url: twbb_website_nav.ajaxurl,
        type: 'POST',
        data: {
            action: 'wn_get_available_menu_items',
            post_type,
            type,
            html_title,
            nav_menu_id: jQuery('#nav_menu_items').data('nav_id'),
            nonce: twbb_website_nav.nonce,
            page,
            add_parent_container,
            pages_count_per_requested,
            rendering_type,
            exclude_menu_items,
            exclude_draft,
            current_page_id,
        },
    })
        .done( (data) => {
            if (data.success) {
                let template = data.data.content;
                if ( add_parent_container === true ) {
                    let full_container = `<div class="wn-action-tooltip-container twbb-wn-secondary-container twbb-wn-search-container" 
                         data-post-type="${post_type}"><div class="twbb-wn-action-tooltip-title-container">
            <span class="twbb-wn-back-add-to-menu-button"></span>
            <div class="twbb-wn-action-tooltip-title">${html_title}</div></div>${template}</div>`;
                    main_container.append(full_container);
                } else {
                    if( template === '' ) {
                        twbb_finished[post_type] = true;
                    } else {
                        items_container.append(template);
                        if( data.data.items.length < pages_count_per_requested ) {
                            twbb_finished[post_type] = true;
                        }
                    }
                }
            }
        })
        .always(() => {
            jQuery('.twbb-wn-item-loading').remove();
            twbb_loading = false;
        });
}

/**
 * Function fire ajax request and get items for post type
 *
 * @param element object
 * @param page integer page number which should be requested
 * @paran scroll bool for checking if function called during the scroll
 */
function twbb_otherTypesInnerPage(element, page = 1, scroll = false) {
    if (twbb_loading) {
        return;
    }
    let post_type = element.data('post-type'),
        type = element.data('type'),
        html_title = element.data('post-type-title'),
        container = jQuery(`.twbb-wn-inner-page-items[data-post-type=${element.data('post-type')}] .twbb-wn-inner-pages-content`);
    twbb_attachScroll(container, element,twbb_otherTypesInnerPage); //  ATTACH SCROLL AFTER FIRST LOAD
    if( !container.length || (scroll && !twbb_finished[post_type]) || page === 1) {
        twbb_loading = true;
        if (container.length > 0) {
            container.append('<div class="twbb-wn-item-loading"></div>');
        }
        twbb_getDataByTypeAjax(post_type, type, html_title, page, container, container, false, 20, 'navigation', 'false', 'false');
    }
    if( !container.find('.twbb-wn-item').length ) {
        container.append(
            '<div class="twbb-wn-item-loading"></div><div class="twbb-wn-item-loading"></div><div class="twbb-wn-item-loading"></div>'
        );
    }
}


function twbb_attachScroll(container, element, callback) {
    container.off('scroll.twbb').on('scroll.twbb', function() {
        let post_type = element.data('post-type');
        if (post_type === 'custom' || twbb_loading || twbb_finished[post_type]) return;

        const scrollTop = container.scrollTop();
        const scrollHeight = container.prop('scrollHeight');
        const containerHeight = container.outerHeight();

        // Check that user is scrolling down
        if (scrollTop > twbb_lastScrollTop) {
            // Check if near the bottom
            if (scrollTop + containerHeight >= scrollHeight - 50) {
                if(typeof twbb_page[post_type] !== 'undefined') {
                    twbb_page[post_type]++;
                } else {
                    twbb_page[post_type] = 2;
                }
                callback(element, twbb_page[post_type], true);
            }
        }
        twbb_lastScrollTop = scrollTop; // update last scroll position
    });
}

/*
* Functionality for different case after Add menu item to nav menu
 */
function twbb_addNavSuccessCallback(data, ui, remove= true) {
    let nav_menu = jQuery("#nav_menu_items");
    ui.find('.twbb-wn-add-item-to-page').css('background-image', 'none');
    ui.find('.twbb-wn-add-item-to-page').append('<i class="fas fa-check"></i>');
    ui.attr('data-nav_item_title', ui.attr('data-title'));
    ui.attr('data-nav_menu_status','in_menu');
    if( !jQuery(`#nav_menu_items .twbb-wn-item[data-id="${ui.attr('data-id')}"]`).length ) {
        nav_menu.append(data);
    }
    /* in case of item moved from page list to menu list there is no settings data in the content so need to replace */
    else if(jQuery(`#nav_menu_items .twbb-wn-item[data-id="${ui.attr('data-id')}"]`).find(".menu-item-data-db-id").val() === '') {
        jQuery(`#nav_menu_items .twbb-wn-item[data-id="${ui.attr('data-id')}"]`).replaceWith(data);
    }
    nav_menu.sortable('refresh');
    //if this item data-object is page remove the item from pages sortable too
    if( ui.data('object') === 'page' ) {
        jQuery(`#pages_items .twbb-wn-item[data-id="${ui.data('id')}"]`).remove();
        jQuery('#pages_items').sortable('refresh');
    } else {
        //check if count only itself
        if( ui.closest('.twbb-wn-action-tooltip-items').children('.twbb-wn-action-tooltip-item').length === 1 ) {
            jQuery(`.twbb-wn-main-container .twbb-wn-action-tooltip-item[data-post-type="${ui.data('post_type')}"]`).addClass('twbb-wn-item-not-available');
        }
    }
    //add the item to nav menu sortable
    if( remove ) {
        //remove item after 1 sec
        setTimeout(function () {
            jQuery('.twbb-wn-action-tooltip-items').find(`.twbb-wn-action-tooltip-item[data-id=${ui.attr('data-id')}]`).remove();
        }, 500);
    }
    jQuery('.twbb-wn-add-menu-item-blue-button').css('display','none');
    jQuery('.twbb-website-nav-sidebar-navigation-header .twbb-wn-add-item.wn-add-menu-item.twbb-wn-tooltip-parent').removeClass('twbb-wn-not-visible');
    twbb_updateMenuItemPositions(nav_menu);
    twbb_navMenuActions.updateOrdering();
    analyticsDataPush('Website structure', 'Navigation menu edit', 'Left menu');
}

function twbb_removeNavMenuItemCallback(ui) {
    let nav_menu = jQuery("#nav_menu_items"), pages = jQuery('#pages_items'), data_object = ui.attr('data-object'),
    specific_type_collections = jQuery(`.twbb-wn-type-${data_object}`);
    ui.removeAttr('data-nav_item_title');
    ui.find('.twbb-wn-title').text(ui.attr('data-title'));
    ui.attr('data-nav_menu_status','not_in_menu');
    if( data_object === 'page' ) {
        ui.updateDepthClass(0);
        pages.prepend(ui);
        pages.sortable('refresh');
    } else {
        specific_type_collections = jQuery(`.twbb-wn-action-tooltip-item[data-post-type="${ui.attr('data-object')}"]`);
        let specific_type_collections_secondary_containers = jQuery(`.wn-action-tooltip-container.twbb-wn-secondary-container[data-post-type="${data_object}"]`);
        let tooltip_ui = `<div class="twbb-wn-action-tooltip-item twbb-wn-flex-space-between"
            data-type="${ui.attr('data-type')}" data-post_type="${data_object}"
            data-id="${ui.attr('data-id')}" data-title="${ui.attr('data-title')}"
            data-object="${data_object}" data-url="${ui.attr('data-url')}">
                <span>${ui.attr('data-title')}</span><span class="twbb-wn-add-item-to-page"></span></div>`;
        if( specific_type_collections.length  && specific_type_collections_secondary_containers.length ) {
            specific_type_collections.removeClass('twbb-wn-item-not-available');
            //for each specific_type_collections_secondary_containers we can have two from blue button and from + sign
            specific_type_collections_secondary_containers.each(function() {
                if( !jQuery(this).find('.twbb-wn-action-tooltip-items').find(`.twbb-wn-action-tooltip-item[data-id=${ui.attr('data-id')}]`).length) {
                    jQuery(this).find('.twbb-wn-action-tooltip-items').append(tooltip_ui);
                }
            });

        }
        //after further implementation  ui will go to the specific type collection
        jQuery(`.twbb-website-nav-sidebar-nav-menus-items .twbb-wn-item[data-id="${ui.attr('data-id')}"]`).remove();
    }
    //add removed item back to proper place
    nav_menu.sortable('refresh');

    //update menu item positions
    twbb_updateMenuItemPositions(nav_menu);

    if( !jQuery('.twbb-website-nav-sidebar-nav-menus-items > .twbb-wn-item').length ) {
        if( !jQuery('.twbb-wn-add-menu-item-blue-button').length ) {
            let template = jQuery('#twbb-wn-add-menu-item-button').html();
            jQuery('.twbb-website-nav-sidebar-nav-menus-items').append(template);
        } else {
            jQuery('.twbb-wn-add-menu-item-blue-button').css('display','block');
        }

        jQuery('.twbb-website-nav-sidebar-navigation-header .twbb-wn-add-item.wn-add-menu-item.twbb-wn-tooltip-parent').addClass('twbb-wn-not-visible');
    }

    analyticsDataPush('Website structure', 'Navigation menu edit', 'Left menu');
}

function twbb_updateMenuItemPositions(nav_menu) {
    let i = 0;
    nav_menu.children().each(function(){
        var item = jQuery(this),
            input = item.find( '.menu-item-data-position' );
        input.val(i);
        i++;
    });
}

function twbb_closeTooltip() {
    let item_actions = jQuery('.twbb-website-nav-sidebar-item__actions');
    if( item_actions.length ) {
        item_actions.removeClass('twbb-wn-visible-tooltip');
    }
    jQuery('.wn-action-tooltip').css('display', 'none');
    jQuery('.twbb-wn-add-item').removeClass('twbb_active');
    jQuery('.twbb-wn-tooltip-parent').removeClass('twbb-opacity-1');
    jQuery('.twbb-wn-manage-trash').removeClass('wn-menu-trash-active');
}

function twbb_customLinkInputFunction(that) {
    let parent = jQuery(that).closest('.twbb-wn-secondary-container'),
        label_input = parent.find('#wn-custom-link-nav-label').val() ? parent.find('#wn-custom-link-nav-label').val() : 'Menu Item',
        nav_url = parent.find('#wn-custom-link-nav-url'),
        url_input = nav_url.val() ? nav_url.val() : '',
        item_button = parent.find('.twbb-wn-add-custom-menu-item-button');
    if( url_input !== '' ) {
        item_button.removeClass('disabled');
    } else {
        item_button.addClass('disabled');
    }
    item_button.attr('data-title', label_input).attr('data-url', url_input);
}

function twbb_searchInit(element, item = '.twbb-wn-action-tooltip-item') {
    let searchText = element.val().toLowerCase();
    if( searchText !== '' ) {
        element.closest(".twbb-wn-search-wrapper").find(".twbb-wn-clear-search").show();
    } else {
        element.closest(".twbb-wn-search-wrapper").find(".twbb-wn-clear-search").hide();
    }
    let searchResult = 0;
    element.closest('.twbb-wn-search-container').find(item).each(function() {
        let text = jQuery(this).find('span').first().text().toLowerCase();

        if (text.includes(searchText)) {
            jQuery(this).show();
            searchResult = 1
        } else {
            jQuery(this).hide();
        }
    });
    if( searchResult ) {
        element.closest('.twbb-wn-search-container').find(".twbb-wn-search-noresult").hide();
    } else {
        element.closest('.twbb-wn-search-container').find(".twbb-wn-search-noresult").show();
    }
}

function twbb_capitalizeWords(string) {
    if (typeof string !== undefined) {
        return string.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    }
    return '';
}

function twbb_reRenderWebsiteNavigationSidebar(open_sidebar= true) {
    jQuery('.twbb-website-nav-sidebar-container').remove();
    //get content from twbb_navmenu_sidebar_template function

    jQuery.ajax({
        url: twbb_website_nav.ajaxurl,
        type: 'POST',
        data: {
            action: 'wn_get_navmenu_sidebar_template',
            nonce: twbb_website_nav.nonce,
        },
    })
        .done( (data) => {
            if (data.success) {
                let website_navigation_sidebar = data.data;
                jQuery('#elementor-editor-wrapper-v2').append(website_navigation_sidebar);
                let action = 'close';
                if( open_sidebar ) {
                    action = 'open';
                }
                twbb_animate_sidebar(action, jQuery('.twbb-website-nav-sidebar-main'), 380, 'twbb-website-navigation-sidebar-opened', twbb_closeWebsiteNavigation);
                twbb_navMenuSortable();
                twbb_websiteNavigation = new TWBB_WebsiteNavigation();
            }
        })
        .always(() => {});
}

function twbb_renderPageTrash(element) {
    element.closest(".wn-menu-trash-active").removeClass("wn-menu-trash-active");
    const post_type = element.closest(".twbb-website-nav-sidebar-pages-container").attr('data-post-type');

    if( jQuery(document).find(".twbb-website-nav-sidebar-container.twbb-wn-inner-trash-page[data-type='"+post_type+"']").length ) {
        jQuery(document).find(".twbb-website-nav-sidebar-container.twbb-wn-inner-trash-page[data-type='"+post_type+"']").show();
        return;
    }

    let html_content = jQuery('#twbb-wn-trash-managment-page').html();
    jQuery('.twbb-website-nav-sidebar-container').append(html_content);

    jQuery(document).find(".twbb-website-nav-sidebar-container.twbb-wn-inner-trash-page[data-type='']").attr('data-type',post_type);

    jQuery(document)
        .find(".twbb-wn-inner-trash-page[data-type='"+post_type+"'] .twbb-wn-inner-trash-pages-content")
        .append('<div class="twbb-wn-item-loading"></div>'.repeat(3));

    jQuery.ajax({
        url: twbb_website_nav.ajaxurl,
        type: 'POST',
        data: {
            action: 'wn_trash_management',
            task: 'wm_get_trash_items',
            post_type: post_type,
            nonce: twbb_website_nav.nonce,
        },
    })
        .done( (data) => {
            if (data.success) {
                let template = data.data.content;
                if( template !== '' ) {
                    jQuery(document).find(".twbb-wn-inner-trash-page[data-type='"+post_type+"'] .twbb-wn-inner-pages-empty-trush-button")
                        .removeClass("twbb-empty-trush-button-inactive");
                    jQuery(document).find(".twbb-wn-inner-trash-page[data-type='"+post_type+"'] .twbb-wn-inner-trash-pages-content").append(template);
                }
            }
        })
        .always(() => {
            jQuery(document).find(".twbb-wn-inner-trash-page[data-type='"+post_type+"'] .twbb-wn-inner-trash-pages-content .twbb-wn-item-loading").remove();
        })
}

//used in WPMenuController.js
function reRenderNavMenu($scope, content) {
    jQuery($scope).find(' > .elementor-widget-container').remove();
    jQuery($scope).append(content);
    elementorFrontend.hooks.doAction(`frontend/element_ready/twbb-nav-menu.default`, $scope, jQuery);
    elementorFrontend.hooks.doAction(`frontend/element_ready/twbb-header-widget.default`, $scope, jQuery);
}

