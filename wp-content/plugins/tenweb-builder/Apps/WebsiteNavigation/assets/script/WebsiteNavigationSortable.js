//all this functionality is from wordpress wp-admin/js/nav-menu.js with some changes
function twbb_navMenuSortable() {
    api = {
        menuList: jQuery('#nav_menu_items'),
        pagesList: jQuery('#pages_items'),
        menuID: jQuery('#nav_menu_items').attr('data-nav_id'),
        options : {
            menuItemDepthPerLevel : 40, // Do not use directly. Use depthToPx and pxToDepth instead.
            globalMaxDepth:  2,//this for wordpress is 11 changed by me
            sortableItems:   '> *',
            targetTolerance: 0,
            good_for_action: 'twbb-good-for-action',
        },

        init: function() {
            this.jQueryExtensions();
        },

        depthToPx : function(depth) {
            return depth * api.options.menuItemDepthPerLevel;
        },

        pxToDepth : function(px) {
            return Math.floor(px / api.options.menuItemDepthPerLevel);
        },

        jQueryExtensions : function() {
            // jQuery extensions.
            jQuery.fn.extend({
                menuItemDepth : function() {
                    var margin = this.eq(0).css('margin-left');
                    return api.pxToDepth( margin && -1 !== margin.indexOf('px') ? margin.slice(0, -2) : 0 );
                },
                updateDepthClass : function(current, prev) {
                    return this.each(function(){
                        var t = jQuery(this);
                        prev = prev || t.menuItemDepth();
                        jQuery(this).removeClass('menu-item-depth-'+ prev )
                            .addClass('menu-item-depth-'+ current );
                    });
                },
                shiftDepthClass : function(change) {
                    return this.each(function(){
                        var t = jQuery(this),
                            depth = t.menuItemDepth(),
                            newDepth = depth + change;

                        t.removeClass( 'menu-item-depth-'+ depth )
                            .addClass( 'menu-item-depth-'+ ( newDepth ) );

                        if ( 0 === newDepth ) {
                            t.find( '.menu-item-data-parent-id' ).val(0);
                        } else {
                            //set prev item db_it as menu-item-data-parent-id value
                            var parent = t.prevAll( '.menu-item-depth-' + ( newDepth - 1 ) ).first();
                            t.find( '.menu-item-data-parent-id' ).val( parent.find( '.menu-item-data-db-id' ).val() );
                        }
                    });
                },

                childMenuItems : function() {
                    var result = jQuery();
                    this.each(function(){
                        var t = jQuery(this), depth = t.menuItemDepth(), next = t.next( '.menu-item' );
                        while( next.length && next.menuItemDepth() > depth ) {
                            result = result.add( next );
                            next = next.next( '.menu-item' );
                        }
                    });
                    return result;
                },

                updateParentMenuItemDBId : function() {
                    return this.each(function(){
                        var item = jQuery(this),
                            input = item.find( '.menu-item-data-parent-id' ),
                            depth = parseInt( item.menuItemDepth(), 10 ),
                            parentDepth = depth - 1,
                            parent = item.prevAll( '.menu-item-depth-' + parentDepth ).first();

                        if ( 0 === depth ) { // Item is on the top level, has no parent.
                            input.val(0);
                        } else { // Find the parent item, and retrieve its object id.
                            input.val( parent.find( '.menu-item-data-db-id' ).val() );
                        }
                    });
                }
            });
        },
    };
    api.init();
    var currentDepth = 0, originalDepth, minDepth, maxDepth,
        prev, next, prevBottom, nextThreshold, helperHeight, transport,
        body = jQuery('body'), maxChildDepth,
        menuMaxDepth = initialMenuMaxDepth();

    if( api.menuList.length ) {
        api.menuList.sortable({
            connectWith: ".twbb_connectedSortable",
            handle: '.menu-item-handle',
            placeholder: 'sortable-placeholder',
            items: api.options.sortableItems,
            cursor: 'grab',
            tolerance: 'pointer',
            start: function (e, ui) {
                var height, parent, children, tempHolder;
                ui.item.addClass('ui-sort-in-progress');
                transport = ui.item.find('.menu-item-transport');

                // Set depths. currentDepth must be set before children are located.
                originalDepth = ui.item.menuItemDepth();
                updateCurrentDepth(ui, originalDepth);

                // Attach child elements to parent.
                // Skip the placeholder.
                parent = (ui.item.next()[0] === ui.placeholder[0]) ? ui.item.next() : ui.item;
                children = parent.childMenuItems();
                transport.append(children);

                // Update the height of the placeholder to match the moving item.
                height = transport.outerHeight();
                // If there are children, account for distance between top of children and parent.
                height += (height > 0) ? (ui.placeholder.css('margin-top').slice(0, -2) * 1) : 0;
                height += ui.helper.outerHeight();
                helperHeight = height;
                height -= 2;                                              // Subtract 2 for borders.
                ui.placeholder.height(height);

                // Update the list of menu items.
                tempHolder = ui.placeholder.next('.menu-item');
                tempHolder.css('margin-top', helperHeight + 'px'); // Set the margin to absorb the placeholder.
                ui.placeholder.detach();         // Detach or jQuery UI will think the placeholder is a menu item.
                jQuery(this).sortable('refresh');   // The children aren't sortable. We should let jQuery UI know.
                ui.item.after(ui.placeholder); // Reattach the placeholder.
                tempHolder.css('margin-top', 0); // Reset the margin.

                // Now that the element is complete, we can update...
                updateSharedVars(ui);
            },
            stop: function (e, ui) {

                ui.item.removeClass('ui-sort-in-progress');
                //check if any ajax is in progress
                if (jQuery('.twbb-website-nav-sidebar-main').hasClass('disable-ajax-in-progress')) {
                    jQuery(this).sortable("cancel");
                    // Return child elements to the list.
                    transport.children().insertAfter(ui.item);
                    jQuery(this).sortable('refreshPositions');
                    return;
                }
                //error case other to pages
                if (!ui.item.hasClass(api.options.good_for_action) &&
                    ui.item.parent().hasClass('twbb-website-nav-sidebar-pages-items')) {
                    if (!jQuery('.twbb-navmenu-sidebar-other-to-pages-error').length) {
                        let error_template = jQuery('#twbb-navmenu-sidebar-other-to-pages-error-template').html()
                        jQuery('.twbb-website-nav-sidebar-content')
                            .prepend(error_template)
                            .addClass('twbb-navmenu-sidebar-with-error');
                    }
                    jQuery(this).sortable("cancel");
                }

                //error case nested to pages
                if (ui.item.hasClass(api.options.good_for_action) && ui.item.find('.menu-item-transport').eq(0).children().length &&
                    ui.item.parent().hasClass('twbb-website-nav-sidebar-pages-items')) {
                    if (!jQuery('.twbb-navmenu-sidebar-other-to-pages-error').length) {
                        let error_template = jQuery('#twbb-navmenu-sidebar-nested-to-pages-error-template').html()
                        jQuery('.twbb-website-nav-sidebar-content')
                            .prepend(error_template)
                            .addClass('twbb-navmenu-sidebar-with-error');
                    }
                    jQuery(this).sortable("cancel");
                }
                //prevent reordering pages in the pages list
                if (ui.item.hasClass(api.options.good_for_action) && ui.item.parent().hasClass('twbb-website-nav-sidebar-pages-items')) {
                    currentDepth = 0;
                }
                var children, depthChange = currentDepth - originalDepth;

                //error case exceeded max depth
                let depth_1 = ui.item.find('.menu-item-transport').eq(0).find('.menu-item-depth-1').length ? 1 : 0;
                let depth_2 = ui.item.find('.menu-item-transport').eq(0).find('.menu-item-depth-2').length ? 2 : 0;
                let changedElementDepth = currentDepth;
                if (depth_1)
                    changedElementDepth += depth_1;
                if (depth_2)
                    changedElementDepth += depth_2;
                if ((changedElementDepth > api.options.globalMaxDepth + 1 && depth_2) ||
                    (changedElementDepth > api.options.globalMaxDepth && !depth_2)) {
                    if (!jQuery('.twbb-navmenu-sidebar-exceeded-max-depth-error').length) {
                        let error_template = jQuery('#twbb-navmenu-sidebar-exceeded-max-depth-error-template').html()
                        jQuery('.twbb-website-nav-sidebar-content')
                            .prepend(error_template)
                            .addClass('twbb-navmenu-sidebar-with-error');
                    }
                    jQuery(this).sortable("cancel");
                    // Return child elements to the list.
                    transport.children().insertAfter(ui.item);
                    jQuery(this).sortable('refreshPositions');
                    return;
                }

                // Return child elements to the list.
                children = transport.children().insertAfter(ui.item);


                // Update depth classes.
                if (0 !== depthChange) {
                    ui.item.updateDepthClass(currentDepth);
                    children.shiftDepthClass(depthChange);
                    updateMenuMaxDepth(depthChange);
                }

                // Update the item data.
                ui.item.updateParentMenuItemDBId();
                updateMenuItemPositions();

                //ajax Actions
                if (ui.item.hasClass(api.options.good_for_action) && ui.item.parent().hasClass('twbb-website-nav-sidebar-pages-items')) {
                    twbb_navMenuActions.removeMenuItem(ui.item, twbb_removeNavMenuItemCallback);
                } else {
                    //merge ui.item and children in one array
                    children = children.add(ui.item);
                }

                //edit children changeable_items depth and parent ids
                twbb_navMenuActions.bulkEditMenu(children, api.menuID);

                // Address sortable's incorrectly-calculated top in Opera.
                ui.item[0].style.top = 0;
                analyticsDataPush('Website structure', 'Navigation menu edit', 'Left menu');
            },
            change: function (e, ui) {
                // Make sure the placeholder is inside the menu.
                // Otherwise fix it, or we're in trouble.
                if (!ui.placeholder.parent().hasClass('twbb-website-nav-sidebar-nav-menus-items')
                    && !ui.placeholder.parent().hasClass('twbb-website-nav-sidebar-pages-items')) {
                    (prev.length) ? prev.after(ui.placeholder) : api.menuList.prepend(ui.placeholder);
                }
                updateSharedVars(ui);
            },
            sort: function (e, ui) {
                var offset = ui.helper.offset(),
                    edge = offset.left,
                    menuEdge = api.menuList.length ? api.menuList.offset().left : '0',
                    depth = api.pxToDepth(edge - menuEdge);
                //error case other to pages
                if ((!ui.item.hasClass(api.options.good_for_action) || ui.item.find('.menu-item-transport').eq(0).children().length) &&
                    ui.placeholder.parent().hasClass('twbb-website-nav-sidebar-pages-items')) {
                    ui.placeholder.addClass('ui-placeholder-state-error');
                    return;
                } else {
                    ui.placeholder.removeClass('ui-placeholder-state-error');
                }
                /*
                 * Check and correct if depth is not within range.
                 * Also, if the dragged element is dragged upwards over an item,
                 * shift the placeholder to a child position.
                 */
                if (depth > maxDepth || offset.top < (prevBottom - api.options.targetTolerance)) {
                    depth = maxDepth;
                } else if (depth < minDepth) {
                    depth = minDepth;
                }


                if (depth !== currentDepth)
                    updateCurrentDepth(ui, depth);

                //error case exceeded max Depth
                let depth_1 = ui.item.find('.menu-item-transport').eq(0).find('.menu-item-depth-1').length ? 1 : 0;
                let depth_2 = ui.item.find('.menu-item-transport').eq(0).find('.menu-item-depth-2').length ? 2 : 0;
                let changedElementDepth = currentDepth;
                if (depth_1)
                    changedElementDepth += depth_1;
                if (depth_2)
                    changedElementDepth += depth_2;
                if ((changedElementDepth > api.options.globalMaxDepth + 1 && depth_2) ||
                    (changedElementDepth > api.options.globalMaxDepth && !depth_2)) {
                    ui.placeholder.addClass('ui-placeholder-state-error');
                    return;
                } else {
                    ui.placeholder.removeClass('ui-placeholder-state-error');
                }

                // If we overlap the next element, manually shift downwards.
                if (nextThreshold && offset.top + helperHeight > nextThreshold) {
                    next.after(ui.placeholder);
                    updateSharedVars(ui);
                    jQuery(this).sortable('refreshPositions');
                }
            }
        });
    }
    if( api.pagesList.length ) {
        api.pagesList.sortable({
            connectWith: '.twbb_connectedSortable',
            cursor: 'grab',
            placeholder: 'sortable-placeholder',
            cursorAt: {left: 150, top: 17},
            tolerance: 'pointer',
            handle: '.menu-item-handle',
            items: '> *',
            start: function (e, ui) {
                ui.item.addClass('ui-sort-in-progress');
                // Update the list of menu items.
                tempHolder = ui.placeholder.next('.menu-item');
                tempHolder.css('margin-top', helperHeight + 'px'); // Set the margin to absorb the placeholder.
                ui.placeholder.detach();         // Detach or jQuery UI will think the placeholder is a menu item.
                jQuery(this).sortable('refresh');   // The children aren't sortable. We should let jQuery UI know.
                ui.item.after(ui.placeholder); // Reattach the placeholder.
                tempHolder.css('margin-top', 0); // Reset the margin.

                // Now that the element is complete, we can update...
                updateSharedVars(ui);
            },
            change: function (e, ui) {
                if (!ui.placeholder.parent().hasClass('twbb-website-nav-sidebar-nav-menus-items')
                    && !ui.placeholder.parent().hasClass('twbb-website-nav-sidebar-pages-items')) {
                    (prev.length) ? prev.after(ui.placeholder) : api.pagesList.prepend(ui.placeholder);
                    (prev.length) ? prev.after(ui.placeholder) : api.pagesList.prepend(ui.placeholder);
                }
                updateSharedVars(ui);
            },
            stop: function (e, ui) {
                ui.item.removeClass('ui-sort-in-progress');
                //check if any ajax is in progress
                if (jQuery('.twbb-website-nav-sidebar-main').hasClass('disable-ajax-in-progress')) {
                    jQuery(this).sortable("cancel");
                    // Return child elements to the list.
                    if( transport !== undefined ) {
                        transport.children().insertAfter(ui.item);
                    }
                    jQuery(this).sortable('refreshPositions');
                    return;
                }

                //error case draft to nav
                if (!ui.item.hasClass('twbb-good-for-action') && ui.item.parent().hasClass('twbb-website-nav-sidebar-nav-menus-items')) {
                    if (!jQuery('.twbb-navmenu-sidebar-draft-to-nav-error').length) {
                        let error_template = jQuery('#twbb-navmenu-sidebar-draft-to-nav-error-template').html()
                        jQuery('.twbb-website-nav-sidebar-content')
                            .prepend(error_template)
                            .addClass('twbb-navmenu-sidebar-with-error');
                    }
                    jQuery(this).sortable("cancel");
                    return;
                }
                if (ui.item.parent().hasClass('twbb-website-nav-sidebar-nav-menus-items')) {
                    var depthChange = currentDepth - originalDepth;

                    //error case exceeded max depth
                    let depth_1 = ui.item.find('.menu-item-transport').eq(0).find('.menu-item-depth-1').length ? 1 : 0;
                    let depth_2 = ui.item.find('.menu-item-transport').eq(0).find('.menu-item-depth-2').length ? 2 : 0;
                    let changedElementDepth = currentDepth;
                    if (depth_1)
                        changedElementDepth += depth_1;
                    if (depth_2)
                        changedElementDepth += depth_2;
                    if ((changedElementDepth > api.options.globalMaxDepth + 1 && depth_2) ||
                        (changedElementDepth > api.options.globalMaxDepth && !depth_2)) {
                        if (!jQuery('.twbb-navmenu-sidebar-exceeded-max-depth-error').length) {
                            let error_template = jQuery('#twbb-navmenu-sidebar-exceeded-max-depth-error-template').html()
                            jQuery('.twbb-website-nav-sidebar-content')
                                .prepend(error_template)
                                .addClass('twbb-navmenu-sidebar-with-error');
                        }
                        jQuery(this).sortable("cancel");
                        jQuery(this).sortable('refreshPositions');
                        return;
                    }


                    // Update depth classes.
                    if (0 !== depthChange) {
                        ui.item.updateDepthClass(currentDepth);
                        updateMenuMaxDepth(depthChange);
                    }

                    // Update all menu settings
                    ui.item.updateParentMenuItemDBId();
                    updateMenuItemPositions();


                    // Address sortable's incorrectly-calculated top in Opera.
                    ui.item[0].style.top = 0;
                    twbb_navMenuActions.addMenuItem(api.menuID, ui.item, addNavSuccessCallback);
                }
                if (!ui.item.parent().hasClass('twbb-website-nav-sidebar-nav-menus-items')) {
                    //don't let reorder pages in the pages list through drag-and-drop functionality
                    jQuery(this).sortable("cancel");
                }

            },
            sort: function (e, ui) {
                //error case draft to nav
                if (!ui.item.hasClass('twbb-good-for-action') && ui.placeholder.parent().hasClass('twbb-website-nav-sidebar-nav-menus-items')) {
                    ui.placeholder.addClass('ui-placeholder-state-error');
                    return;
                } else {
                    ui.placeholder.removeClass('ui-placeholder-state-error');
                }


                if (ui.placeholder.parent().hasClass('twbb-website-nav-sidebar-nav-menus-items')) {
                    var offset = ui.helper.offset(),
                        edge = offset.left,
                        menuEdge = api.menuList.length ? api.menuList.offset().left : '0',
                        depth = api.pxToDepth(edge - menuEdge);
                    //error case other to pages
                    if ((!ui.item.hasClass(api.options.good_for_action) || ui.item.find('.menu-item-transport').eq(0).children().length) &&
                        ui.placeholder.parent().hasClass('twbb-website-nav-sidebar-pages-items')) {
                        ui.placeholder.addClass('ui-placeholder-state-error');
                        return;
                    } else {
                        ui.placeholder.removeClass('ui-placeholder-state-error');
                    }
                    /*
                     * Check and correct if depth is not within range.
                     * Also, if the dragged element is dragged upwards over an item,
                     * shift the placeholder to a child position.
                     */
                    if (depth > maxDepth || offset.top < (prevBottom - api.options.targetTolerance)) {
                        depth = maxDepth;
                    } else if (depth < minDepth) {
                        depth = minDepth;
                    }
                    if (depth !== currentDepth)
                        updateCurrentDepth(ui, depth);

                    //error case exceeded max Depth
                    let depth_1 = ui.item.find('.menu-item-transport').eq(0).find('.menu-item-depth-1').length ? 1 : 0;
                    let depth_2 = ui.item.find('.menu-item-transport').eq(0).find('.menu-item-depth-2').length ? 2 : 0;
                    let changedElementDepth = currentDepth;
                    if (depth_1)
                        changedElementDepth += depth_1;
                    if (depth_2)
                        changedElementDepth += depth_2;
                    if ((changedElementDepth > api.options.globalMaxDepth + 1 && depth_2) ||
                        (changedElementDepth > api.options.globalMaxDepth && !depth_2)) {
                        ui.placeholder.addClass('ui-placeholder-state-error');
                        return;
                    } else {
                        ui.placeholder.removeClass('ui-placeholder-state-error');
                    }

                    // If we overlap the next element, manually shift downwards.
                    if (nextThreshold && offset.top + helperHeight > nextThreshold) {
                        next.after(ui.placeholder);
                        updateSharedVars(ui);
                        jQuery(this).sortable('refreshPositions');
                    }
                }
            },
        }).disableSelection();
    }
    function updateSharedVars(ui) {
        var depth;

        prev = ui.placeholder.prev( '.menu-item' );
        next = ui.placeholder.next( '.menu-item' );

        // Make sure we don't select the moving item.
        if( prev[0] === ui.item[0] ) prev = prev.prev( '.menu-item' );
        if( next[0] === ui.item[0] ) next = next.next( '.menu-item' );

        prevBottom = (prev.length) ? prev.offset().top + prev.height() : 0;
        nextThreshold = (next.length) ? next.offset().top + next.height() / 3 : 0;
        minDepth = (next.length) ? next.menuItemDepth() : 0;

        if( prev.length )
            maxDepth = ( (depth = prev.menuItemDepth() + 1) > api.options.globalMaxDepth ) ? api.options.globalMaxDepth : depth;
        else
            maxDepth = 0;
    }

    function updateCurrentDepth(ui, depth) {
        ui.placeholder.updateDepthClass( depth, currentDepth );
        currentDepth = depth;
    }

    function initialMenuMaxDepth() {
        if( ! body[0].className ) return 0;
        var match = body[0].className.match(/menu-max-depth-(\d+)/);
        return match && match[1] ? parseInt( match[1], 10 ) : 0;
    }

    function updateMenuMaxDepth( depthChange ) {
        var depth, newDepth = menuMaxDepth;
        if ( depthChange === 0 ) {
            return;
        } else if ( depthChange > 0 ) {
            depth = maxChildDepth + depthChange;
            if( depth > menuMaxDepth )
                newDepth = depth;
        } else if ( depthChange < 0 && maxChildDepth === menuMaxDepth ) {
            while( ! jQuery('.menu-item-depth-' + newDepth, api.menuList).length && newDepth > 0 )
                newDepth--;
        }
        // Update the depth class.
        body.removeClass( 'menu-max-depth-' + menuMaxDepth ).addClass( 'menu-max-depth-' + newDepth );
        menuMaxDepth = newDepth;
    }

    function updateMenuItemPositions() {
        var i = 1;
        return api.menuList.children().each(function(){
            var item = jQuery(this),
                input = item.find( '.menu-item-data-position' );
            input.val(i);
            i++;
        });
    }

    function addNavSuccessCallback(db_id, ui) {
        jQuery('.twbb-wn-add-menu-item-blue-button').css('display','none');
        jQuery('.twbb-website-nav-sidebar-navigation-header .twbb-wn-add-item.wn-add-menu-item.twbb-wn-tooltip-parent').removeClass('twbb-wn-not-visible');
        ui.attr('data-nav_item_title', ui.attr('data-title'));
        ui.attr('data-nav_menu_status','in_menu');
        twbb_navMenuActions.fillMenuItemSettings(ui, db_id);
        twbb_navMenuActions.updateOrdering();
        analyticsDataPush('Website structure', 'Navigation menu edit', 'Left menu');
    }
}
