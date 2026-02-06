jQuery( window ).on( 'elementor:init', function() {
  elementor.hooks.addAction('panel/open_editor/widget/twbb_woocommerce-products', function (panel, model, view) {
    /*
* change is done in 1.27.X version
* this is for insuring backward compatibility with the old version of the widget,
* we remove old control and replace it's values to new one's
*/
    const controlIds = {
      'hide_products_images': 'product_images',
      'hide_products_titles': 'product_title',
      'hide_products_description': 'product_description',
      'hide_products_buttons': 'product_buttons',
      'hide_product_quantity': 'product_quantity',
      'classic_skin_hide_products_titles': 'classic_skin_product_title',
      'modern_skin_hide_products_titles': 'modern_skin_product_title',
      'classic_skin_hide_products_description': 'classic_skin_product_description',
      'modern_skin_hide_products_description': 'modern_skin_product_description',
      'classic_skin_hide_product_quantity': 'classic_skin_product_quantity',
      'modern_skin_hide_product_quantity': 'modern_skin_product_quantity',
      'classic_skin_hide_products_images': 'classic_skin_product_images',
      'modern_skin_hide_products_images': 'modern_skin_product_images',
      'classic_skin_hide_products_buttons': 'classic_skin_product_buttons',
      'modern_skin_hide_products_buttons': 'modern_skin_product_buttons',
    }

    let reload = false;
    for (const oldControlId in controlIds) {
      if (controlIds.hasOwnProperty(oldControlId)) {
        const newControlId = controlIds[oldControlId];
        var oldControl = model.get('settings').get(oldControlId);
        var newControl = model.get('settings').get(newControlId);
        if (newControl === 'default') {
          reload = true;
          replaceOldControlWithNew({
            'model': model,
            'container': view.container,
            'oldControlValue': '' === oldControl ? 'yes' : 'yes' === oldControl ? '' : oldControl,
            'newControlId': newControlId
          });
        }

      }
    }

    const controlIdsCount = {
      'products_count': {
        'rows': 'rows',
        'columns': 'columns'
      },
      'classic_skin_products_count': {
        'rows': 'classic_skin_rows',
        'columns': 'classic_skin_columns'
      },
      'modern_skin_products_count': {
        'rows': 'modern_skin_rows',
        'columns': 'modern_skin_columns'
      }
    };

    Object.keys(controlIdsCount).forEach(controlId => {
      var newControl = model.get('settings').get(controlId);
      if (newControl === 'default') {
        reload = true;
        const rows = model.get('settings').get(controlIdsCount[controlId].rows);
        const columns = model.get('settings').get(controlIdsCount[controlId].columns);
        replaceOldControlWithNew({
          'model': model,
          'container': view.container,
          'oldControlValue': rows * columns,
          'newControlId': controlId
        });
      }
    });

    if( reload ) {
      window.parent.$e.run('document/save/default').then(() => {
        window.parent.$e.run('document/elements/deselect-all');
        window.parent.$e.run('document/elements/toggle-selection',
          {
            container: view.container
          });
      });
    }

    changeQuantityPosition(model);
  });
});

jQuery(window).on("load",function() {
  if (typeof changeDefaultWidgetSetting !== "function") {
    return;
  }
  changeDefaultWidgetSetting("twbb_woocommerce-products",
      {
        'variation_images': '',
        'image_gallery': '',
        'product_buttons': 'yes',
        'product_quantity': '',
        'column_gap': { unit: 'px', size: 14, sizes: {} },
        'row_gap': { unit: 'px', size: 40, sizes: {} },
        'align': 'left',
        'title_typography_typography': "",
        'old_price_color': "",
        'old_price_typography_typography' : "",
        'star_color' : "",
        'price_typography_typography' : "",
        'price_color' : "",
        'button_text_color' : "",
        'onsale_text_color' : "",
        'onsale_text_background_color' : "",
        'onsale_typography_typography': "",
        'title_spacing': { unit: 'px', size: 3, sizes: {} },
        'box_padding': {
          "unit": "px",
          "top": 0,
          "right": 0,
          "bottom": 14,
          "left": 0,
          "isLinked": 1
        },
        'content_padding': {
          "unit": "px",
          "top": 0,
          "right": 0,
          "bottom": 0,
          "left": 20,
          "isLinked": 1
        },
        'image_hover_animation' : 'zoom-out',
        'image_spacing' : { unit: 'px', size: 10, sizes: {} },
        'star_size' : { unit: 'px', size: 16, sizes: {} },
        'rating_spacing' : { unit: 'px', size: 14, sizes: {} },
        'onsale_width' : { unit: 'px', size: 74, sizes: {} },
        'onsale_height' : { unit: 'px', size: 36, sizes: {} },
        'onsale_distance' : { unit: 'px', size: 20, sizes: {} },
        'onsale_border_radius' : { unit: 'px', size: 0, sizes: {} },
        'button_typography_typography':'custom',
        'button_typography_font_family': "Montserrat",
        'button_typography_font_size': {
          unit: 'px',
          size: 14
        },
        'button_typography_text_decoration': "underline",
        'button_typography_line_height': {
          unit: '%',
          size: 150
        },
        'modern_skin_product_description': '',
        'modern_skin_variation_images': '',
        'modern_skin_image_gallery': '',
        'modern_skin_title_color' : "",
        'modern_title_typography_typography' : "",
        'modern_skin_price_color' : "",
        'modern_old_price_typography_typography' : "",
        'modern_price_typography_typography' : "",
        'modern_skin_button_text_color' : "",
        'modern_skin_button_border_color' : "",
        'modern_button_typography_typography' : "",
        'modern_button_border_border' : "solid",
        'modern_button_border_width' : {
          "unit": "px",
          "top": 1,
          "right": 1,
          "bottom": 1,
          "left": 1,
          "isLinked": 1
        },
        'modern_skin_button_text_padding' : {
          "unit": "px",
          "top": 4,
          "right": 30,
          "bottom": 4,
          "left": 30,
          "isLinked": 1
        },
        'modern_view_cart_typography_typography' : "",
        'modern_skin_variations_gap' : { unit: 'px', size: 10, sizes: {} },
        'modern_skin_variation_image_width' : { unit: 'px', size: 40, sizes: {} },
        'modern_skin_variation_image_height' : { unit: 'px', size: 40, sizes: {} },
        'modern_skin_variations_number_color' : "",
        'modern_skin_variations_typography_typography' : "",
        'modern_skin_image_hover_animation' : "zoom-in",
        'modern_skin_onsale_width' : { unit: 'px', size: 74, sizes: {} },
        'modern_skin_onsale_height' : { unit: 'px', size: 36, sizes: {} },
        'modern_skin_onsale_distance' : { unit: 'px', size: 20, sizes: {} },
        'modern_skin_onsale_border_radius' : { unit: 'px', size: 0, sizes: {} },
        'modern_skin_onsale_horizontal_position' : "right",
        'modern_skin_onsale_text_color' : "",
        'modern_skin_onsale_text_background_color' : "",
        'modern_onsale_typography_typography' : "",
        'classic_skin_product_description': '',
        'classic_skin_variation_images': '',
        'classic_skin_image_gallery': '',
        'classic_skin_product_buttons': 'yes',
        'classic_skin_product_quantity': '',
        'classic_skin_column_gap': { unit: 'px', size: 14, sizes: {} },
        'classic_skin_row_gap': { unit: 'px', size: 40, sizes: {} },
        'classic_skin_align': 'left',
        'classic_title_typography_typography': "",
        'classic_skin_old_price_color': "",
        'classic_old_price_typography_typography' : "",
        'classic_skin_star_color' : "",
        'classic_price_typography_typography' : "",
        'classic_skin_price_color' : "",
        'classic_skin_button_text_color' : "",
        'classic_skin_onsale_text_color' : "",
        'classic_skin_onsale_text_background_color' : "",
        'classic_onsale_typography_typography': "",
        'classic_button_typography_typography':'custom',
        'classic_button_typography_font_family': "Montserrat",
        'classic_button_typography_font_size': {
          unit: 'px',
          size: 14
        },
        'classic_button_typography_text_decoration': "underline",
        'classic_button_typography_line_height': {
          unit: '%',
          size: 150
        },
        // Set global reference in `__globals__`
        '__globals__': {
          'modern_skin_title_color': "globals/colors?id=primary",
          'modern_title_typography_typography': "globals/typography?id=twbb_bold",
          'modern_skin_price_color' : "globals/colors?id=primary",
          'modern_old_price_typography_typography' : "globals/typography?id=twbb_p3",
          'modern_price_typography_typography' : "globals/typography?id=twbb_p5",
          'modern_skin_button_text_color' : "globals/colors?id=primary",
          'modern_skin_button_border_color' : "globals/colors?id=twbb_bg_inv",
          'modern_button_typography_typography' : "globals/typography?id=accent",
          'modern_view_cart_typography_typography' : "globals/typography?id=accent",
          'modern_skin_variations_number_color' : "globals/typography?id=primary",
          'modern_skin_variations_typography_typography' : "globals/typography?id=text",
          'modern_skin_onsale_text_color' : "globals/colors?id=twbb_primary_inv",
          'modern_skin_onsale_text_background_color' : "globals/colors?id=twbb_bg_inv",
          'modern_onsale_typography_typography' : "globals/typography?id=twbb_p5",
          'classic_skin_title_color': "globals/colors?id=primary",
          'classic_title_typography_typography': "globals/typography?id=twbb_bold",
          'classic_skin_old_price_color': "globals/colors?id=text",
          'classic_skin_star_color': "globals/colors?id=twbb_bg_inv",
          'classic_old_price_typography_typography' : "globals/typography?id=twbb_p5",
          'classic_price_typography_typography' : "globals/typography?id=twbb_p3",
          'classic_skin_price_color' : "globals/colors?id=text",
          'classic_skin_button_text_color' : "globals/colors?id=primary",
          'classic_skin_onsale_text_color' : "globals/colors?id=twbb_button_inv",
          'classic_skin_onsale_text_background_color' : "globals/colors?id=twbb_button",
          'classic_onsale_typography_typography' : "globals/typography?id=twbb_p4",
          'title_typography_typography': "globals/typography?id=twbb_bold",
          'old_price_color': "globals/colors?id=text",
          'star_color': "globals/colors?id=twbb_bg_inv",
          'old_price_typography_typography' : "globals/typography?id=twbb_p5",
          'price_typography_typography' : "globals/typography?id=twbb_p3",
          'price_color' : "globals/colors?id=text",
          'button_text_color' : "globals/colors?id=primary",
          'onsale_text_color' : "globals/colors?id=twbb_button_inv",
          'onsale_text_background_color' : "globals/colors?id=twbb_button",
          'onsale_typography_typography' : "globals/typography?id=twbb_p4",
        },
        'classic_skin_title_spacing': { unit: 'px', size: 3, sizes: {} },
        'classic_skin_box_padding': {
          "unit": "px",
          "top": 0,
          "right": 0,
          "bottom": 14,
          "left": 0,
          "isLinked": 1
        },
        'classic_skin_content_padding': {
          "unit": "px",
          "top": 0,
          "right": 0,
          "bottom": 0,
          "left": 20,
          "isLinked": 1
        },
        'classic_skin_image_hover_animation' : 'zoom-out',
        'classic_skin_image_spacing' : { unit: 'px', size: 10, sizes: {} },
        'classic_skin_star_size' : { unit: 'px', size: 16, sizes: {} },
        'classic_skin_rating_spacing' : { unit: 'px', size: 14, sizes: {} },
        'classic_skin_onsale_width' : { unit: 'px', size: 74, sizes: {} },
        'classic_skin_onsale_height' : { unit: 'px', size: 36, sizes: {} },
        'classic_skin_onsale_distance' : { unit: 'px', size: 20, sizes: {} },
        'classic_skin_onsale_border_radius' : { unit: 'px', size: 0, sizes: {} },
      });
});


/* The function change Quantity position control value to top in case of align control is center */
function changeQuantityPosition(model) {
  let settings = model.get('settings');
  let skin = settings.get('_skin');
  if (skin !== '') {
    skin = skin + '_skin_';
  }

  let sessionQuantityPosition = settings.get('quantity_position');

  if (settings instanceof Backbone.Model) {
    let alignKey = 'change:' + skin + 'align';

    // Remove previous event listener to prevent duplicates
    settings.off(alignKey);

    settings.on(alignKey, function () {
      let alignValue = settings.get(skin + 'align');

      if (alignValue === 'center') {
        // Store the current value before changing it
        sessionQuantityPosition = settings.get('quantity_position');

        // Set quantity_position to 'top' temporarily
        settings.set('quantity_position', 'column');

        // Manually trigger Elementor UI update
        settings.trigger('change:quantity_position');
      } else if (alignValue === 'left') {
        // Store the current value before changing it
        sessionQuantityPosition = settings.get('quantity_position');

        // Set quantity_position to 'top' temporarily
        settings.set('quantity_position', 'row-reverse');

        // Manually trigger Elementor UI update
        settings.trigger('change:quantity_position');
      } else if (alignValue === 'right') {
        // Store the current value before changing it
        sessionQuantityPosition = settings.get('quantity_position');

        // Set quantity_position to 'top' temporarily
        settings.set('quantity_position', 'row');

        // Manually trigger Elementor UI update
        settings.trigger('change:quantity_position');
      } else {
        // Restore the previous value without saving in Elementor settings
        settings.set('quantity_position', sessionQuantityPosition);

        // Manually trigger Elementor UI update
        settings.trigger('change:quantity_position');
      }
    });
  }

}

function replaceOldControlWithNew(args) {
  let settings = {
    [args['newControlId']]: args['oldControlValue'],
  }
  window.parent.$e.commands.run('document/elements/settings', {
    "container": args['container'],
    settings: settings
  });
}