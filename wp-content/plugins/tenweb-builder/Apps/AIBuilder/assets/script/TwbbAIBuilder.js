class TwbbAIBuilder {

    constructor(isSinglePage) {
        this.uniqueId = '';
        this.isSinglePage = isSinglePage;
        this.isMobile = matchMedia('screen and (max-width: 768px)').matches;
        this.importedSiteData = safeJsonParse(twbb_ai_builder.imported_site_data);
        this.historyKey = this.isSinglePage ? 'single_page' : (this.isMobile ? 'formData' : 'builder_history');
        this.ecomData = {};
        this.step = 1;
        this.params = {
            'business_type': '',
            'business_name': '',
            'business_description': '',
            'website_type': '',
            'tier1': false
        };
        this.paramsFromOutline = {};
        this.twbbWebsiteStyle = new TwbbWebsiteStyle(this.historyKey, this.uniqueId, this.isMobile);
        this.twbbEcommerceData = new TwbbEcommerceData();

        const history = {};
        if(Object.keys(history).length > 0){
            this.params = {
                business_type: history.business_type ?? "",
                business_name: history.business_name ?? "",
                business_description: history.business_description ?? "",
                website_type: history.website_type ?? "",
                unique_id: history.unique_id ?? "",
                tier1: false,
            }
            this.isMobileHistory = true;
            this.ecomData = history.ecomData ?? {};
            this.uniqueId = history.unique_id ?? "";
        }
        // variable to determine whether the step went back
        this.isBack = false;
    }

    selectWebsiteType( value ) {
        jQuery('.twbb-ai-builder__btn.next').removeClass('disabled');
        jQuery('.twbb-ai-builder').attr('data-websiteType', value);
        this.params.website_type = value;
    }

    changeStep( button, nextStep = 0 ) {
        const _this = this,
          container = jQuery('.twbb-ai-builder__container'),
          isNextButtonClicked = button.hasClass('next'),
          isGenerateButtonClicked = button.hasClass('generate'),
          nextButton = jQuery('.twbb-ai-builder__btn.next'),
          currentStep = nextStep !== 0 ? nextStep - 1 : parseInt(jQuery('.twbb-ai-builder__container:visible').attr('data-step'));
        let next = isNextButtonClicked ? currentStep + 1 : currentStep - 1;
        next = nextStep !== 0 ? nextStep : next;

        if (this.params.website_type !== 'ecommerce' && currentStep === 3 && isNextButtonClicked ){
            next = 5;
            nextButton.addClass('disabled');
        }
        if ( next === 1  && !isNextButtonClicked ) {
            this.websiteType();
            nextButton.addClass('disabled');
        }
        else if ( currentStep === 5 && !isNextButtonClicked){
            if (!this.isSinglePage ) {
                this.isBack = true;
                this.showLeavePopup();
                return;
            } else {
                next = 3;
            }
        }
        if ( currentStep === 3 && isNextButtonClicked ){
            this.setBusinessInfo();
        }
        if ( isGenerateButtonClicked ) next = 7;
        const  nextContainer = jQuery(`.twbb-ai-builder__container[data-step="${next}"]`),
          type = nextContainer.attr('data-type');


        if (typeof this[type] === "function" ) {
            this[type]();
        }

        const time = (next === 2 || next === 3) ? 500 : 0;
        setTimeout(function(){
            container.closest('.twbb-ai-builder').attr('data-websiteType', _this.params.website_type).attr('data-type', type).attr('data-generationType', _this.isSinglePage ? 'single_page' : 'website');
            container.addClass('hidden');
            nextContainer.removeClass('hidden');
            (next !== 4 && next !== 5) && _this.checkButtonState( 'button' );
        },time);
    }

    checkButtonState( from = '' ){
        const nextButton = jQuery('.twbb-ai-builder__btn.next');
        const emptyInputs = jQuery('.twbb-ai-builder__container:visible').find('.twbb-input').filter(function() {
            const $input = jQuery(this);

            if ($input.attr('type') === 'text') {
                return jQuery.trim($input.val()) === '';
            }

            if ($input.attr('type') === 'radio') {
                const name = $input.attr('name');
                // Check if no radio button in this group is checked
                return !jQuery(`input[type="radio"][name="${name}"]:checked`).length;
            }

            return false;
        });
        emptyInputs.length && nextButton.addClass('disabled');

        if ( from === 'button' && !emptyInputs.length ) {
            nextButton.removeClass('disabled');
        }
    }

    showLeavePopup(){
        const overlay = jQuery('.twbb-ai-builder-overlay');

        overlay.find('.title').html(twbb_ai_builder.leave_popup_title);
        overlay.find('.desc').html(twbb_ai_builder.leave_popup_desc);
        overlay.find('.twbb-ai-builder__popup').addClass('leave');
        overlay.removeClass('hidden');
    }

    setBusinessInfo(from = '') {
        this.params.business_name = jQuery('#business-name').val();
        this.params.business_description = jQuery('#business-description').val();
        this.setMobileHistory(this.params);
        if (twbb_ai_builder.reseller_mode) {
            twbSendEventToRouth({
                eventCategory: 'Reseller Action',
                eventAction: 'Section-based AI Flow - Question answered',
                eventLabel: '["company_name","company_description"]',
                websiteType: this.params.website_type === 'basic' ? 'Business' : 'Ecommerce',
                companyType: this.params.business_type,
                uniqueId: this.uniqueId,
                isMobile: this.isMobile
            });
        } else {
            twbSendEventToPublicRouth( {
                eventCategory: 'Hosted Website Action',
                eventAction: 'Section-based AI Flow - Question answered',
                eventLabel: '["company_name","company_description"]',
                websiteType: this.params.website_type === 'basic' ? 'Business' : 'Ecommerce',
                companyType: this.params.business_type,
                uniqueId: this.uniqueId,
                isMobile: this.isMobile
            } );
        }
    }

    websiteType() {
        this.removeMobileData();
        this.removeData();
        const twbbWebsiteType = new TwbbWebsiteType();
        twbbWebsiteType.setAnimations();
    }

    removeMobileData(){
        localStorage.removeItem('formData');
        this.params = {
            'business_type': '',
            'business_name': '',
            'business_description': '',
            'website_type': '',
            'tier1': false
        };
    }

    removeData(){
        jQuery('.radio[name="website_type"]').prop('checked', false);
        jQuery('.twbb-input:not(.twbb-radio)').val('');
        sessionStorage.removeItem('sessionId');
    }

    async businessType() {
        const _this = this;
        this.uniqueId = await _this.getUniqueId();

        const twbbBusinessType = new TwbbBusinessType(this.params);
        twbbBusinessType.changeInfo();
        twbbBusinessType.getOptions();
        jQuery('.option-search .search')
          .on('focus', function () {
              twbbBusinessType.showOptions();
        })
          .on('keyup', function () {
              twbbBusinessType.searchOptions(jQuery(this), _this.params.website_type);
              _this.checkButtonState();
        })
          .on('click', function () {
              jQuery(this).val() !== '' && twbbBusinessType.searchOptions(jQuery(this), _this.params.website_type);
        });
        jQuery('body').on('click', function () {
              twbbBusinessType.hideOptions();
        })
        jQuery('.select_container').on('click', function (e) {
              e.stopPropagation()
        })

        if (twbb_ai_builder.reseller_mode) {
            twbSendEventToRouth({
                eventCategory: 'Reseller Action',
                eventAction: 'Generative AI - Website type selected',
                eventLabel: 'Section-based AI Flow',
                websiteType: this.params.website_type === 'basic' ? 'Business' : 'Ecommerce',
                isMobile: this.isMobile
            });
        } else {
            twbSendEventToPublicRouth( {
                eventCategory: 'Hosted Website Action',
                eventAction: 'Generative AI - Website type selected',
                eventLabel: 'Section-based AI Flow',
                websiteType: this.params.website_type === 'basic' ? 'Business' : 'Ecommerce',
                isMobile: this.isMobile
            } );
        }
    }

    async aboutWebsite() {
        if (this.isSinglePage) {
            this.uniqueId = await this.getUniqueId();
        }
        jQuery('.twbb-ai-builder__btn.next').addClass('disabled');
        const businessTypeVal = jQuery('#business-type').val();
        if (businessTypeVal !== '') {
            this.params.business_type = businessTypeVal;
        }

        const  mobileHistory = {},
          twbbAboutWebsite = new TwbbAboutWebsite(this.params, this.isSinglePage,),
          inputInfo = (this.isMobile && mobileHistory) ? mobileHistory?.info : {},
          info = await twbbAboutWebsite.changeInfo( this.params, inputInfo );
        this.setMobileHistory({...this.params, info});
        if (twbb_ai_builder.reseller_mode) {
            twbSendEventToRouth({
                eventCategory: 'Reseller Action',
                eventAction: 'Section-based AI Flow - Question answered',
                eventLabel: '["business_type"]',
                websiteType: this.params.website_type === 'basic' ? 'Business' : 'Ecommerce',
                companyType: this.params.business_type,
                isMobile: this.isMobile
            });
        } else {
            twbSendEventToPublicRouth( {
                eventCategory: 'Hosted Website Action',
                eventAction: 'Section-based AI Flow - Question answered',
                eventLabel: '["business_type"]',
                websiteType: this.params.website_type === 'basic' ? 'Business' : 'Ecommerce',
                companyType: this.params.business_type,
                isMobile: this.isMobile
            } );
        }
    }

    async ecommerceData() {
        this.ecomData = await this.twbbEcommerceData.ecomData( this.uniqueId, this.params ) || {};
        this.setMobileHistory(this.params);
    }

    outline(from = '') {
        this.changeEcomData();

        jQuery('.twbb-ai-builder__btn.next').addClass('disabled');
        const _this = this,
          outlineView = document.getElementById('outline-view'),
          savedData = null;

        let params = {
            generation: {
                ..._this.params,
                unique_id: this.uniqueId
            },
            callbackFn: readyOutline,
            eventCallback: eventsFromOutline,
            builder_api : twbb_ai_builder.builder_api,
            saved_data: savedData,
            workspace_id: twbb_ai_builder.workspace_id,
            access_token: twbb_ai_builder.access_token,
            elements_path: twbb_ai_builder.elements_path,
            lang: twbb_ai_builder.lang,
        };

        if (_this.isSinglePage) {
            params.new_page = true;
            params.generation.website_type = this.importedSiteData?.website_type;
            params.generation.business_type = this.importedSiteData?.business_type;
        }
        const twbbOutline = new TwbbOutline();

        function readyOutline(value) {
            _this.paramsFromOutline = value;
            from === '' && _this.setHistory(value);
            console.log(value);
        }
        function eventsFromOutline(evn) {
            const action = evn?.action ?? 'Section-based AI Flow - Outline Generated';
            if (twbb_ai_builder.reseller_mode) {
                twbSendEventToRouth({
                    eventCategory: 'Reseller Action',
                    eventAction: action,
                    eventLabel: '',
                    uniqueId: _this.uniqueId,
                    isMobile: _this.isMobile
                });
            } else {
                twbSendEventToPublicRouth( {
                    eventCategory: 'Hosted Website Action',
                    eventAction: action,
                    eventLabel: '',
                    uniqueId: _this.uniqueId,
                    isMobile: _this.isMobile
                } );
            }
        }
        outlineView.show = true;
        if( _this.isBack ) {
            outlineView.resetValue = params;
            //unset outlineView.formValue
            outlineView.formValue = null;
            _this.isBack = false;
        } else {
            outlineView.formValue = params;
            outlineView.resetValue = null;
        }


        outlineView.addEventListener('loaded', function(event) {
            if(event.detail === true) {
                twbbOutline.changeInfo();
            }
        });

    }

    websiteStyle() {
        const allParams = this.paramsFromOutline;
        this.twbbWebsiteStyle.setColorsThemes(allParams);
        if (twbb_ai_builder.reseller_mode) {
            twbSendEventToRouth({
                eventCategory: 'Reseller Action',
                eventAction: 'Section-based AI Flow - Outline Submitted',
                eventLabel: '',
                uniqueId: this.uniqueId,
                isMobile: this.isMobile
            });
        } else {
            twbSendEventToPublicRouth( {
                eventCategory: 'Hosted Website Action',
                eventAction: 'Section-based AI Flow - Outline Submitted',
                eventLabel: '',
                uniqueId: this.uniqueId,
                isMobile: this.isMobile
            } );
        }
    }

    generation() {
        this.isMobile && this.changeEcomData();
        const activeThemes = this.twbbWebsiteStyle.getActiveThemes(),
          allParams = this.paramsFromOutline;

        let baseParams = this.params.website_type === 'ecommerce'
          ? { ...allParams, 'ecom_data': this.ecomData }
          : allParams;

        const params = baseParams;

        if (this.isMobile){
            delete params.info;
            localStorage.removeItem('formData');
        }
        else {
             params.colors = activeThemes?.colors ?? baseParams.colors;
             params.fonts = activeThemes?.fonts ?? baseParams.fonts;
             params.theme = activeThemes?.theme ?? baseParams.theme;
            localStorage.removeItem('builder_history');
            localStorage.removeItem('fieldData');
        }
        const twbbGeneration = new TwbbGeneration(params, this.uniqueId, this.params.website_type, this.isMobile, this.isSinglePage);
        twbbGeneration.generateWebsite();
    }

    async getUniqueId() {
        let uniqueId = '';
        try {
            let url = 'ai2/start_generation_session';
            if( twbb_ai_builder.reseller_mode ) {
                url = 'builder/start_generation_session';
            }
            const result = await twbbRequests(
              'POST',
              url,
              true
            );
            if (result) {
                if (result.status === 200) {
                    uniqueId = result.data?.uniqueId;
                    sessionStorage.setItem('sessionId', uniqueId);
                    return uniqueId;
                }
            }
        }
        catch (error) {
            console.log('Error fetching data', error);
            return uniqueId;
        }
    }

    changeEcomData(){
        let ecomDataCategoriesFromInputs = [];
        jQuery('.twbb-input.ecom_data').each(function(){
            const val = jQuery(this).val();
            ecomDataCategoriesFromInputs.push(val);
        });
        if( this.ecomData.categories ) this.ecomData.categories = ecomDataCategoriesFromInputs; //Change ecom_data categories according inputs value
    }

    setHistory(value) {
        let data = value;
        data.unique_id = this.uniqueId;
        data.website_type = this.params.website_type;
        data.ecom_data = this.ecomData;
        localStorage.setItem(this.historyKey, JSON.stringify(data));
    }

    setMobileHistory(value) {
        if (!this.isMobile || this.isSinglePage) {
            return;
        }
        let data = value;
        if (localStorage.getItem(this.historyKey)) {
            let history = JSON.parse(localStorage.getItem(this.historyKey));
            data = {...history, ...data};
        }
        else {
            data.unique_id = this.uniqueId;
            data.website_type = this.params.website_type;
            data.ecom_data = this.ecomData;
            data.image_model = "default";
            data.trial_hosted_flow = 0;
            data.ai_type = "ai_builder_demo";
            data.domain_name = {
                want_domain: 0,
                domain_name: ""
            }
        }
        localStorage.setItem(this.historyKey, JSON.stringify(data));
    }

}

let twbbAIBuilder;
jQuery(document).ready(function () {
    const isMobile = matchMedia('screen and (max-width: 768px)').matches,
      outlineView = document.getElementById('outline-view');
    twbbAIBuilder = new TwbbAIBuilder();

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('open_ai_generation') === '1') {
        jQuery('.twbb-ai-builder').removeClass('hidden');
        jQuery('.twbb-ai-builder__container[data-type="websiteType"]').removeClass('hidden');
    }

    jQuery(document).on("change", ".radio[name='website_type']", function(){
        twbbAIBuilder.selectWebsiteType( jQuery(this).val() );
    });
    jQuery(document).on("change", ".twbb-ai-builder__container[data-type='businessType'] .twbb-input", function(){
        twbbAIBuilder.setBusinessInfo('button');
    });

    jQuery(document).on("click", ".twbb-ai-builder__btn.next,.twbb-ai-builder__btn.back", function(){
        twbbAIBuilder.changeStep( jQuery(this) );
    });

    jQuery(document).on("click", ".twbb-ai-builder__btn.cancel", function(){
        jQuery('.twbb-ai-builder-overlay').addClass('hidden')
        return false;
    });

    jQuery(document).on("click", ".twbb-ai-builder__btn.leave", function(){
        twbbAIBuilder.changeStep(jQuery(this), 1);
        localStorage.removeItem('builder_history');
        localStorage.removeItem('fieldData');
        twbbAIBuilder.removeData();
        outlineView.show = false;
        jQuery('.twbb-ai-builder-overlay').addClass('hidden');
        if (twbb_ai_builder.reseller_mode) {
            twbSendEventToRouth({
                eventCategory: 'Reseller Action',
                eventAction: 'Section-based AI Flow - Leave Outline editor',
                eventLabel: '',
                websiteType: twbbAIBuilder.params.website_type === 'basic' ? 'Business' : 'Ecommerce',
                uniqueId: twbbAIBuilder.uniqueId,
                isMobile: twbbAIBuilder.isMobile
            });
        } else {
            twbSendEventToPublicRouth( {
                eventCategory: 'Hosted Website Action',
                eventAction: 'Section-based AI Flow - Leave Outline editor',
                eventLabel: '',
                websiteType: twbbAIBuilder.params.website_type === 'basic' ? 'Business' : 'Ecommerce',
                uniqueId: twbbAIBuilder.uniqueId,
                isMobile: twbbAIBuilder.isMobile
            } );
        }
    });

    jQuery(document).on("click", ".twbb-ai-builder-page__button", function(){
        const id = jQuery(this).attr('data-id'),
          generationType = id === 'single_page',
          hasBuilderHistory = false,
          hasSinglePage = false,
          hasFormData = false;
        let step;
        if (id === 'generate') {
            if (isMobile) {
                step = hasFormData ? 3 : 1;
            } else {
                step = hasBuilderHistory ? 6 : 1;
            }
        } else {
            step = hasSinglePage ? 5 : 3;
        }
        jQuery('.twbb-ai-builder').removeClass('hidden');
        jQuery('body').addClass('not-scroll');
        twbbAIBuilder = new TwbbAIBuilder(generationType);
        twbbAIBuilder.removeData();
        twbbAIBuilder.changeStep( jQuery('.twbb-ai-builder__btn.next:not(.generate)'), step );
    });

    jQuery(document).on("click", ".twbb-ai-builder__close, .twbb-ai-builder[data-type='websiteType'] .twbb-ai-builder__btn.back,  .twbb-ai-builder[data-type='outline'] .twbb-ai-builder__btn.back", function(){
        twbbAIBuilder.removeData();
        jQuery('.twbb-ai-builder, .twbb-ai-builder__container').addClass('hidden');
        jQuery('body').removeClass('not-scroll');
        outlineView.show = false;
        outlineView.formValue = null;
    });
});
