jQuery(document).ready(function () {

  if( twbb_admin.sections_folder_exists_and_not_empty === 'install' || twbb_admin.sections_update) {
    twbb_sections_install();
  }

  function twbb_sections_install () {
    jQuery.ajax({
      type: 'POST',
      url: twbb_admin.sections_ajax_url,
      dataType: 'json',
      data: {
        'action': 'twbb_sections_install',
      }
    }).success(function(result) {
      console.log('Sections installed successfully');
    }).error(function() {
      console.log('Error installing sections');
    });
  }

  function templates_page() {
    jQuery(document).on('change', function (e) {
      if (jQuery(e.target).attr('id') !== "elementor-new-template__form__template-type") {
        return true;
      }
      if (e.target.value == "twbb_single") {
        jQuery('#twbb-post-type-form-field').show();
      }
      else {
        jQuery('#twbb-post-type-form-field').hide();
      }
    });
  }

  jQuery( '.display_admin_condition_popup' ).on( 'click', function() {
    jQuery ( '.display_admin_condition_popup' ).removeClass( 'selected_condition' );
    jQuery( this ).addClass( 'selected_condition' );
  });
  
});
