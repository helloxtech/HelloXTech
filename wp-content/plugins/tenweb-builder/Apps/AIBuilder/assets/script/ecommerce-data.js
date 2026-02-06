class TwbbEcommerceData {
  constructor() {
    this.container = jQuery('.twbb-ai-builder__container[data-type="ecommerceData"]');
  }

  changeInfo( type ) {
    const container = this.container,
      title = twbb_ai_builder[`about_website_${type}_title`],
      desc = twbb_ai_builder[`about_website_${type}_desc`],
      example = twbb_ai_builder[`about_website_${type}_example`];

    container.find('.twbb-ai-builder__title').html(title);
    container.find('.twbb-ai-builder__desc').html(desc);
    container.find('.example-content').html(example);
  }

  async ecomData(  uniqueId, params ) {
    const _this = this;
    try {
      const paramsForEcomData = {
        'business_type': params.business_type,
        'business_name': params.business_name,
        'business_description': params.business_description,
        'website_type': params.website_type,
        'tier1': false
      };
      let data = {
        'params': JSON.stringify(paramsForEcomData),
        'uniqueId': uniqueId,
        'service_key': 'gTcjslfqqBFFwJKBnFgQYhkQEJpplLaDKfj'
      };
      let url = 'ai2/ecom_data';
      if( twbb_ai_builder.reseller_mode ) {
        url = 'builder/ecom_data';
      }
      const response = await twbbRequests(
        'POST',
        url,
        true,
        JSON.stringify(data)
      );
      if (response) {
        if (response.status === 200) {
          const categories = response.data?.categories ?? [];
          _this.setInputsInfo(categories);
          const { language_code, ...ecomData } = response.data;
          return ecomData;
        } else {
          return false;
        }
      }
    }
    catch (error) {
      console.log('Error fetching data', error);
      return false;
    }
  }

  setInputsInfo( categories ){
    this.container.find('.twbb-input').each(function(index, el){
      if(categories[index]) {
        jQuery(this).val(categories[index]);
      }
    })
  }

}
