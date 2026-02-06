class TwbbGeneration {
  constructor(params, uniqueId, type, isMobile, isSinglePage) {
    this.container = jQuery('.twbb-ai-builder__container[data-type="generation"]');
    this.isEcommerce = type === 'ecommerce';
    this.uniqueId = uniqueId;
    this.isMobile = isMobile;
    this.isSinglePage = isSinglePage;
    this.steps = this.isSinglePage ? twbb_ai_builder.mobile_steps : twbb_ai_builder.steps.filter(step => step['isEcommerce'] === this.isEcommerce || step['isEcommerce'] === undefined);

    params.image_model = "default";
    params.trial_hosted_flow = 0;
    params.ai_type = "ai_builder_demo";
    params.domain_name = JSON.stringify({
      want_domain: 0,
      domain_name: ""
    });
    this.params = params;

    const _this = this;
    jQuery(document).on('click', '.twbb-ai-builder__popup.retry .twbb-ai-builder__btn', function(){
      _this.generateWebsite();
      jQuery(this).closest('.twbb-ai-builder-overlay').addClass('hidden');
    });
    jQuery(document).on('click', '.twbb-ai-builder__popup.congrats .twbb-ai-builder__btn', function(){
      jQuery(this).closest('.twbb-ai-builder-overlay').addClass('hidden');
      jQuery(this).closest('.twbb-ai-builder').addClass('hidden');
      if (twbb_ai_builder.reseller_mode) {
        twbSendEventToRouth({
          eventCategory: 'Reseller Action',
          eventAction: _this.isMobile ? 'Section-based AI Flow - Preview' : 'Section-based AI Flow - Preview and Edit',
          eventLabel: '',
          websiteType: _this.params.website_type === 'basic' ? 'Business' : 'Ecommerce',
          uniqueId: _this.uniqueId,
          isMobile: _this.isMobile
        });
      } else {
        twbSendEventToPublicRouth( {
          eventCategory: 'Hosted Website Action',
          eventAction: _this.isMobile ? 'Section-based AI Flow - Preview' : 'Section-based AI Flow - Preview and Edit',
          eventLabel: '',
          websiteType: _this.params.website_type === 'basic' ? 'Business' : 'Ecommerce',
          uniqueId: _this.uniqueId,
          isMobile: _this.isMobile
        } );
      }
      setTimeout(() => location.reload(), 200);
    });
  }

  async generateWebsite(){
    const _this = this;
    const params = this.params;
    params.website_type === 'ecommerce' && this.installWoocommerce();
    this.changeInfo();
    this.printSteps();
    let intervalId;
    let generate = false;
    let action = 'generate_site_files_from_description';
    if (twbb_ai_builder.reseller_mode) {
      twbSendEventToRouth({
        eventCategory: 'Reseller Action',
        eventAction: 'Section-based AI Flow - Generation Started',
        eventLabel: '',
        uniqueId: this.uniqueId,
        isMobile: this.isMobile
      });
    } else {
      twbSendEventToPublicRouth( {
        eventCategory: 'Hosted Website Action',
        eventAction: 'Section-based AI Flow - Generation Started',
        eventLabel: '',
        uniqueId: this.uniqueId,
        isMobile: this.isMobile
      } );
    }

    if (this.isSinglePage) {
      generate = await this.generatePageFiles();
      action = 'generate_page_files';
    } else if (this.isMobile) {
      generate = await this.generateSiteFilesFromDescription();
    } else {
      generate = await this.generateSiteFilesFromOutline();
      action = 'generate_site_files_from_outline';
    }
    if(generate){
      const timeoutId = setTimeout(function(){
        clearInterval(intervalId);
        _this.showPopup({
          title: twbb_ai_builder.retry_title,
          desc: this.isSinglePage ? twbb_ai_builder.retry_single_page_description : twbb_ai_builder.retry_description,
          buttonText: twbb_ai_builder.retry_button,
          class: 'retry',
        });
      },320000)

      intervalId = setInterval(async () => {
          const result = await this.getSiteData(action);
          if (typeof result === 'string') {
            clearInterval(intervalId);
            clearTimeout(timeoutId);
            await _this.templateImport(result, action);
          }
          if (result === false) {
            clearInterval(intervalId);
            clearTimeout(timeoutId);
            _this.showPopup({
              title: twbb_ai_builder.retry_title,
              desc: twbb_ai_builder.retry_description,
              buttonText: twbb_ai_builder.retry_button,
              class: 'retry',
            });
            if (twbb_ai_builder.reseller_mode) {
              twbSendEventToRouth({
                eventCategory: 'Reseller Action',
                eventAction: 'Section-based AI Flow - Failed',
                eventLabel: '',
                websiteType: this.params.website_type === 'basic' ? 'Business' : 'Ecommerce',
                uniqueId: _this.uniqueId,
                isMobile: _this.isMobile
              });
            } else {
              twbSendEventToPublicRouth( {
                eventCategory: 'Hosted Website Action',
                eventAction: 'Section-based AI Flow - Failed',
                eventLabel: '',
                websiteType: this.params.website_type === 'basic' ? 'Business' : 'Ecommerce',
                uniqueId: _this.uniqueId,
                isMobile: _this.isMobile
              } );
            }
          }
      }, 10000);

    }

  }

  installWoocommerce() {
    try {
      const data = {
        action: 'twbb_check_woocommerce',
        nonce: twbb_ai_builder.twbb_generate_nonce,
      }
      jQuery.ajax({
        type: 'POST',
        url: twbb_ai_builder.ajax_url,
        data: data,
        success: function (result) {
          console.log(result);
          if (result?.status === 'success') {
            console.log(result?.msg);
          }
        }
      });
    } catch (err) {
      console.log(err);
    }
  }

  templateImport(url, action) {
    const _this = this;
    return new Promise((resolve, reject) => {
      try {
        const websiteData = {
          business_description: _this.params.business_description,
          business_name: _this.params.business_name,
          business_type: _this.params.business_type,
          website_type: _this.params.website_type,
          theme: _this.params.theme,
          fonts: _this.params.fonts,
          colors: _this.params.colors,
        };
        const data = {
          action: 'twbb_import_template',
          nonce: twbb_ai_builder.twbb_generate_nonce,
          url: url,
          ai2_action: action === 'generate_page_files' ? 'build_secondary_page' : 'build_site_from_outline',
          website_data: JSON.stringify(websiteData)
        }
        jQuery.ajax({
          type: 'POST',
          url: twbb_ai_builder.ajax_url,
          data: data,
          success: function (result) {
            if (result.success) {
              _this.completeSteps();
              _this.showPopup({
                  title: twbb_ai_builder.congrats_popup_title,
                  desc: _this.isSinglePage ? twbb_ai_builder.congrats_popup_single_page_desc : (_this.isMobile ? twbb_ai_builder.congrats_popup_desc_mobile : twbb_ai_builder.congrats_popup_desc),
                  buttonText: _this.isMobile ? twbb_ai_builder.congrats_popup_button_mobile : twbb_ai_builder.preview_edit,
                  class: 'congrats',
                  link: _this.isMobile ? twbb_ai_builder.home_url : twbb_ai_builder.admin_post_url + `?post=${result.data}&action=elementor`
                });
              if (twbb_ai_builder.reseller_mode) {
                twbSendEventToRouth({
                  eventCategory: 'Reseller Action',
                  eventAction: 'Section-based AI Flow - Success pop-up opened',
                  eventLabel: '',
                  uniqueId: _this.uniqueId,
                  isMobile: _this.isMobile
                });
                twbSendEventToRouth({
                  eventCategory: 'Reseller Action',
                  eventAction: 'Successful Generation',
                  eventLabel: '',
                  websiteType: _this.params.website_type === 'basic' ? 'Business' : 'Ecommerce',
                  uniqueId: _this.uniqueId,
                  isMobile: _this.isMobile
                });
              } else {
                twbSendEventToPublicRouth( {
                  eventCategory: 'Hosted Website Action',
                  eventAction: 'Section-based AI Flow - Success pop-up opened',
                  eventLabel: '',
                  uniqueId: _this.uniqueId,
                  isMobile: _this.isMobile
                } );
                twbSendEventToPublicRouth( {
                  eventCategory: 'Hosted Website Action',
                  eventAction: 'Successful Generation',
                  eventLabel: '',
                  websiteType: _this.params.website_type === 'basic' ? 'Business' : 'Ecommerce',
                  uniqueId: _this.uniqueId,
                  isMobile: _this.isMobile
                } );
              }
            } else {
              reject(new Error("Unexpected response format or request not successful."));
            }
          },
          error: function (xhr, status, error) {
            reject(new Error(`AJAX error: ${status} - ${error}`));
          }
        });
      } catch (err) {
        reject(err);
      }

    });
  }

  async generatePageFiles() {
    const params = this.params;
    const importedSiteData = safeJsonParse(twbb_ai_builder.imported_site_data);
    const paramsToSend = {
      page_description: params.page_description,
      page_outline:params.page_outline,
      page_title: params.page_title,
      page_type: params.page_type,
      post_status: params.post_status,
      slug: params.slug,
      website_type: importedSiteData?.website_type,
      business_type: importedSiteData?.business_type,
      business_name: importedSiteData?.business_name,
      business_description: importedSiteData?.business_description,
      theme: importedSiteData?.theme,
      fonts: importedSiteData?.fonts,
      colors: importedSiteData?.colors,
      blog: {
        posts_count: twbb_ai_builder.posts_count
      },
      tier1: false,
      website_design_type_level: 1,
    };
    if (paramsToSend.website_type === 'ecommerce'){
      paramsToSend.woocommerce = {
        woocommerce_active: !!twbb_ai_builder.woocommerce_active,
        shop_page_id: twbb_ai_builder.shop_page_id,
        products_count: twbb_ai_builder.products_count
      };
    }
    const data = {
      uniqueId: this.uniqueId,
      params: JSON.stringify(paramsToSend)
    };
    try {
      let url = `ai2/workspaces/${twbb_ai_builder.workspace_id}/generate_page_files`;
      if( twbb_ai_builder.reseller_mode ) {
        url = 'builder/generate_page_files';
      }
      const response = await twbbRequests(
        'POST',
        url,
        true,
        JSON.stringify(data)
      );
      if (response) {
        localStorage.removeItem('single_page');
        return true;
      }
    }
    catch (error) {
      console.log('Error fetching data', error);
      return false;
    }
  }

  async generateSiteFilesFromDescription() {
    const params = this.params;
    const { unique_id, ...paramsToSend } = params;
    const formData = new URLSearchParams();
    formData.append('params', JSON.stringify(paramsToSend));
    formData.append('uniqueId', this.uniqueId);
    formData.append('service_key', 'gTcjslfqqBFFwJKBnFgQYhkQEJpplLaDKfj');

    try {
      let url = `ai2/workspaces/${twbb_ai_builder.workspace_id}/generate_site_files_from_description`;
      if( twbb_ai_builder.reseller_mode ) {
        url = 'builder/generate_site_files_from_description';
      }
      const response = await twbbRequests(
        'POST',
        url,
        true,
        formData,
        'application/x-www-form-urlencoded; charset=UTF-8'
      );
      if (response) {
        localStorage.removeItem('formData');
        return true;
      }
    }
    catch (error) {
      console.log('Error fetching data', error);
      return false;
    }
  }

  async generateSiteFilesFromOutline(){
    const params = this.params;

    const { generated_color, ...paramsToSend } = params;

    const data = {
      params: JSON.stringify(paramsToSend),
      uniqueId: this.uniqueId
    }

    try {
      let url = `ai2/workspaces/${twbb_ai_builder.workspace_id}/generate_site_files_from_outline`;
      if( twbb_ai_builder.reseller_mode ) {
        url = 'builder/generate_site_files_from_outline'
      }
      const response = await twbbRequests(
        'POST',
        url,
        true,
        JSON.stringify(data)
      );
      if (response) {
        localStorage.removeItem('builder_history');
        localStorage.removeItem('fieldData');
        return true;
      }
    }
    catch (error) {
      console.log('Error fetching data', error);
      return false;
    }
  }

  async getSiteData(action){
    const uniqueId = this.uniqueId;
    try {
      let url = `ai2/workspaces/${twbb_ai_builder.workspace_id}/get-data?uniqueId=${uniqueId}&action=${action}`;
      if( twbb_ai_builder.reseller_mode ) {
        url = `builder/get-data?uniqueId=${uniqueId}&action=${action}`;
      }
      const result = await twbbRequests(
        'GET',
        url,
        true
      );
      if (result.status === 200){
         return result.data;
      }
    }
    catch (error) {
      console.log('Error fetching data', error);
      return false
    }
  }

  showPopup(data) {
    const overlay = jQuery('.twbb-ai-builder-overlay');
    overlay.find('.title').html(data.title);
    overlay.find('.desc').html(data.desc);
    overlay.find('.twbb-ai-builder__popup').removeAttr('class').addClass('twbb-ai-builder__popup').addClass(data.class);
    overlay.find('.preview-edit').html(data.buttonText);

    data.link && overlay.find('.preview-edit').attr('href', data.link);
    overlay.find('.preview-edit').attr('target', data.class === 'congrats' ? '_blank' : null);

    overlay.removeClass('hidden');
  }

  completeSteps(){
    jQuery(document).find('.twbb-ai-builder__content .generate-steps li').each(function(){
      jQuery(this).addClass('active')
      jQuery(this).find('.icon').removeClass('process').addClass('passed')
    })
  }

  printSteps() {
    let count = 0;
    let stepsHtml = '<ul class="generate-steps">';
    for (const step in this.steps) {
      const itemClass = count === 0 ? 'process active' : '';
      const icon = count === 0 ? '<span class="icon process"></span>' : '';
      stepsHtml += `<li class="${itemClass} " >${icon} ${this.steps[step]['label']}</li>`;
      count++;
    }
    stepsHtml += '</ul>';
    this.container.find('.twbb-ai-builder__content').html(stepsHtml);
    this.setStepProcessing();
  }

  changeInfo() {
    const container = this.container,
      importedSiteData = safeJsonParse(twbb_ai_builder.imported_site_data),
      type = this.isSinglePage ? importedSiteData?.website_type : this.params.website_type,
      banner = twbb_ai_builder.image_url + `banners/${type}.jpg`,
      title = this.isSinglePage ? twbb_ai_builder.generating_single_page_title : twbb_ai_builder.generating_title;

    container.find('.twbb-ai-builder__title').html(title);
    container.find('.twbb-ai-builder__left-banner').css('background-image', `url(${banner})`);
  }

  setStepProcessing() {
    let counter = -1;
     const interval = setInterval(() => {
      counter++;
      const current = this.container.find('.twbb-ai-builder__content li').eq(counter);
      const next = this.container.find('.twbb-ai-builder__content li').eq(counter + 1);
      const stepsLength = this.steps.length - 1;
      if (counter > stepsLength) counter = stepsLength;
      if (counter !== stepsLength) {
        if (counter === stepsLength) {
          current.addClass('active').prepend("<span class='icon process'></span>");
        } else {
          current.addClass('active').find('.icon').remove();
          current.prepend("<span class='icon passed'></span>");
        }
      }
      if (counter === stepsLength) {
        clearInterval(interval);
        return;
      }
       next.addClass('active').prepend("<span class='icon process'></span>");
    }, 10000);
  }

}
