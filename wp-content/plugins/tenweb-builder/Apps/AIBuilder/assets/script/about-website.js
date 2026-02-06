class TwbbAboutWebsite {
  constructor( params, isSinglePage ) {
    this.container = jQuery('.twbb-ai-builder__container[data-type="aboutWebsite"]');
    this.params = params;
    this.isSinglePage = isSinglePage;
    this.businessType = this.params.business_type;
    this.params.business_name !== '' && this.fillData();
  }

  fillData() {
    this.container.find('#business-name').val(this.params.business_name);
    this.container.find('#business-description').val(this.params.business_description);
  }

  async changeInfo( params, inputsInfo = {} ) {
    let type = this.isSinglePage ? 'single_page' : params.website_type;
    let info = {};
    const container = this.container,
      title = twbb_ai_builder[`about_website_${type}_title`],
      desc = twbb_ai_builder[`about_website_${type}_desc`],
      example = twbb_ai_builder[`about_website_${type}_example`],
      slideUpText = jQuery('.twbb-ai-builder__left-content.slide_up_text'),
      createInfo = container.find('.create-info'),
      leftContainer = container.find('.twbb-ai-builder__left'),
      inputsHistory = Object.keys(inputsInfo).length > 0;

    container.find('.twbb-ai-builder__title').html(title);
    container.find('.twbb-ai-builder__desc').html(desc);
    if (this.isSinglePage) {
      const titleLabel = twbb_ai_builder[`about_website_${type}_title_label`],
        titlePlaceholder = twbb_ai_builder[`about_website_${type}_title_placeholder`],
        descLabel = twbb_ai_builder[`about_website_${type}_desc_label`],
        descPlaceholder = twbb_ai_builder[`about_website_${type}_desc_placeholder`];

      container.find('.business-name .input-label').html(titleLabel);
      container.find('.business-name .twbb-input').attr('placeholder', titlePlaceholder);
      container.find('.business-description .input-label').html(descLabel);
      container.find('.business-description .twbb-input').attr('placeholder', descPlaceholder);

      !leftContainer.find('.slide_up_text').length && leftContainer.append(slideUpText.clone());
      twbbSetAnimations(type);
      createInfo.addClass('hidden');
    } else {
      container.find('.example-content').html(example);
      createInfo.removeClass('hidden');
      leftContainer.find('.slide_up_text').length && leftContainer.find('.slide_up_text').remove();
      inputsHistory && this.setInputsInfo( inputsInfo );

      info = inputsHistory ? inputsInfo : await this.getInputsInfo();
    }
    this.validateInfo();
    return info;
  }

  async getInputsInfo() {
    const _this = this,
      category = this.businessType;
    try {
      let url = `proxy/generative/templates/questions?template_selection=usability&category=${category}`;
      if( twbb_ai_builder.reseller_mode ) {
        url = `templates/generative/questions?template_selection=usability&category=${category}`;
      }
      const result = await twbbRequests(
        'GET',
        url,
        true
      );
      if (result) {
        if ( result.status === 200 ) {
          let allData = result.data;
          let inputsData = allData.questions?.lang_name_desc_domain?.inputs ?? [];
          if ( !inputsData.length ){
            inputsData = allData.questions?.a_lang_name_desc_domain?.inputs ?? [];
          }
          inputsData = inputsData.reduce(( acc, input ) => {
            if( input?.field_type === 'textarea' || (input?.field_type === 'text' && input?.name !== 'domain_name') ) {
              acc[input.field_type] = input;
            }
            return acc;
          }, {});
          _this.setInputsInfo( inputsData );
          return inputsData;
        }
      }
    }
    catch (error) {
      console.log('Error fetching data', error);
      return false;
    }
  }

  setInputsInfo( inputsData ){
    this.container.find('.input-wrap').each(function(){
      const type = jQuery(this).attr('data-type');
      const data = inputsData[type];
      jQuery(this).find('.input-label').html(data.label);
      jQuery(this).find('.twbb-input').attr('placeholder', data.placeholder);
    })
  }

  validateInfo() {
    const _this = this,
      inputs = _this.container.find('.twbb-input');
    inputs.on('change', function () {
      _this.validateInputs(jQuery(this), 'change');
    })
      .on('keyup', function () {
      _this.validateInputs(jQuery(this), 'keyup');
    })
      .on('blur', function () {
        _this.validateInputs(jQuery(this), 'blur');
        jQuery(this).addClass('touched');
    });
  }

  validateInputs(input, event) {
    const container = input.closest('.input-wrap'),
      allInputs = this.container.find('.twbb-input'),
      value = input.val().trim(),
      valueLength = value.length;

    const getErrorMessage = () => {
      switch (input.attr('id')) {
        case 'business-name':
          return value === '' ? 'This field is required.' : '';
        case 'business-description':
          if (value === '') return 'This field is required.';
          if (valueLength < 20) return 'To get better results input no less than 20 symbols.';
          if (valueLength > 800) return 'Character limit reached.';
          return '';
        default:
          return '';
      }
    };

    const errorMsg = getErrorMessage();

    // Show/hide error based on event and validation
    if (errorMsg) {
      if (event !== 'keyup' || (event === 'keyup' && input.hasClass('touched'))) {
        container.addClass('error-wrap');
        container.find('.error-msg').html(errorMsg).removeClass('hidden');
      }
    } else {
      container.removeClass('error-wrap');
      container.find('.error-msg').html('').addClass('hidden');
    }


    const allValid = allInputs.toArray().every(el => {
      const val = jQuery(el).val().trim();

      if (jQuery(el).closest('.input-wrap').hasClass('error-wrap')) return false;

      switch (jQuery(el).attr('id')) {
        case 'business-name':
          return val !== '';
        case 'business-description':
          return val.length >= 20 && val.length <= 800;
        default:
          return true;
      }
    });

    jQuery('.twbb-ai-builder__btn.next').toggleClass('disabled', !allValid);
  }

}
