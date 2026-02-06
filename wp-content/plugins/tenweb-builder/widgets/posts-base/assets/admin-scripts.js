jQuery(window).on("load",function() {
  if (typeof changeDefaultWidgetSetting !== "function") {
    return;
  }
  //this code is for preventing the slider option change in new added posts widget
  changeDefaultWidgetSetting("tenweb-posts",
      {
        'slider_view_option_changed': 'yes',
      }
  );
});

jQuery( window ).on( 'elementor:init', function() {
  elementor.hooks.addAction('panel/open_editor/widget/tenweb-posts', function (panel, model, view) {
    /*
* change is done in 1.28.X version
* this is for insuring backward compatibility with the old version of the widget,
* we remove old control and replace it's values to new one's
*/
    const skin = model.get('settings').get('_skin');
    var oldSkinSlider = skin + '_slides_view';
    const controlIds = {
      [oldSkinSlider]: 'slider_view',
      [skin + '_space_between'] : [skin + '_column_gap'],
      [skin + '_slider_navigation_distance'] : 'navigation_gap',
      [skin + '_slider_navigation_arrows_size'] : 'arrows_size',
      [skin + '_slider_navigation_arrows_color'] : 'arrows_color',
      [skin + '_slider_navigation_dots_color'] : 'pagination_color_secondary',
      [skin + '_slider_navigation_dots_size'] : 'pagination_size',
      [skin + '_loop'] : 'loop',
      [skin + '_autoplay'] : 'autoplay',
      [skin + '_slides_to_scroll'] : 'slides_to_scroll',
      [skin + '_slides_per_view'] : 'slides_per_view',
    }

    let reload = false;
    const sliderView = model.get('settings').get('slider_view_option_changed');
    if (sliderView === 'default')
      if ( model.get('settings').get(skin + '_slides_view') === 'yes' ) {
        reload = true;
      } else {
        replaceOldSliderControlWithNew({
          'model': model,
          'container': view.container,
          'oldControlValue': 'yes',
          'newControlId': 'slider_view_option_changed'
        });
    }
    if( reload ) {
      changeNavigationControls(model, view, skin);
      $full_width = model.get('settings').get(skin + '_slider_view_type') === 'cut_next' ? 'yes' : 'no';
      replaceOldSliderControlWithNew({
        'model': model,
        'container': view.container,
        'oldControlValue': $full_width,
        'newControlId': 'carousel_full_width'
      });
    }
    for (const oldControlId in controlIds) {
      if (controlIds.hasOwnProperty(oldControlId)) {
        const newControlId = controlIds[oldControlId];
        if (reload) {
          let controlValue = model.get('settings').get(oldControlId);
          if( newControlId === 'arrows_size') {
            if ( controlValue !== undefined && !controlValue.size) {
              controlValue.size = 34;
            }
          }
          replaceOldSliderControlWithNew({
            'model': model,
            'container': view.container,
            'oldControlValue': controlValue,
            'newControlId': newControlId
          });
        }

      }
    }

    if( reload ) {
      replaceOldSliderControlWithNew({
        'model': model,
        'container': view.container,
        'oldControlValue': 'yes',
        'newControlId': 'slider_view_option_changed'
      });
      window.parent.$e.run('document/save/default').then(() => {
        window.parent.$e.run('document/elements/deselect-all');
        window.parent.$e.run('document/elements/toggle-selection',
          {
            container: view.container
          });
      });
    }
  });
});

function changeNavigationControls(model, view, skin) {
  const sliderNavigation = skin + '_slider_navigation';
  $show_arrows = '';
  $show_pagination = '';
  if( model.get('settings').get(sliderNavigation).includes('dot') ||
      model.get('settings').get(sliderNavigation) === 'both' ) {
    $show_pagination = 'yes';
  }
  if( model.get('settings').get(sliderNavigation).includes('arrow') ||
      model.get('settings').get(sliderNavigation) === 'both' ) {
      $show_arrows = 'yes';
  }
  replaceOldSliderControlWithNew({
    'model': model,
    'container': view.container,
    'oldControlValue': $show_arrows,
    'newControlId': 'show_arrows'
  });
  replaceOldSliderControlWithNew({
    'model': model,
    'container': view.container,
    'oldControlValue': $show_pagination,
    'newControlId': 'show_pagination'
  });
  replaceOldSliderControlWithNew({
    'model': model,
    'container': view.container,
    'oldControlValue': model.get('settings').get(skin + '_slider_navigation_position'),
    'newControlId': 'navigation_position'
  });
  replaceOldSliderControlWithNew({
    'model': model,
    'container': view.container,
    'oldControlValue': model.get('settings').get(skin + '_slider_navigation_position'),
    'newControlId': 'pagination_position'
  });

}

function replaceOldSliderControlWithNew(args) {
  let settings = {
    [args['newControlId']]: args['oldControlValue'],
  }
  window.parent.$e.commands.run('document/elements/settings', {
    "container": args['container'],
    settings: settings
  });
}
