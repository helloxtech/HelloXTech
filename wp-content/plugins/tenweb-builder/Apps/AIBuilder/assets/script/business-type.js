class TwbbBusinessType {
  constructor(params) {
    this.params = params;
    this.container = jQuery('.twbb-ai-builder__container[data-type="businessType"]');
    this.selectcontainer = jQuery('.select_container');
    this.options = [];
    this.params.business_type !== '' && this.fillData();
  }

  fillData() {
    this.container.find('#business-type').val(this.params.business_type);
    jQuery('.twbb-ai-builder__btn.next').removeClass('disabled');
  }

  changeInfo() {
    const container = this.container,
      type = this.params.website_type,
      title = twbb_ai_builder[`business_type_${type}_title`],
      label = twbb_ai_builder[`business_type_${type}_label`],
      placeholder = twbb_ai_builder[`business_type_${type}_placeholder`],
      banner = twbb_ai_builder.image_url + `banners/${type}.jpg`;

    container.find('.twbb-ai-builder__title').html(title);
    container.find('.label').html(label);
    container.find('.search').attr('placeholder', placeholder);
    container.find('.twbb-ai-builder__left-banner').css('background-image', `url(${banner})`);
  }

  async getOptions () {
    const _this = this,
      type = _this.params.website_type,
      param = type === 'ecommerce' ? 'ecommerce' : 'basic';
    try {
      let url = `proxy/generative/categories?website_type=${param}`;
      if( twbb_ai_builder.reseller_mode ) {
        url = `templates/generative/categories?website_type=${param}`;
      }
      const result = await twbbRequests(
        'GET',
        url,
        true
      );
      if (result) {
        if (result.status === 200) {
          let options = result.data;
          options = options.map((value) => ({ ['name']: value.name, ['title']: value.title }));
          this.options = options;
          options = _this.shuffle(options);
          _this.printOptions(options);
          _this.optionClick();
        }
      }
    }
    catch (error) {
      console.log('Error fetching data', error);
    }
  }

  optionClick () {
    const container = this.selectcontainer;
    jQuery('.select_container .option').on('click', function () {
      const text = jQuery(this).text()

      container.removeClass('active')
      container.find('.option').removeClass('selected')
      jQuery(this).addClass('selected')
      container.find('#business-type').val(text)
      jQuery('.twbb-ai-builder__btn.next').removeClass('disabled')
      localStorage.removeItem('formData');
    });
  }

  searchOptions (el, type) {
    const _this = this;
    const value = el.val();
    const param = type === 'ecommerce' ? 'ecommerce' : 'basic';

    const form_data = JSON.stringify({
      "input": value,
      "website_type": param
    });

    let url = `${twbb_ai_builder.twbb_fe_service}api/onboarding/search_categories`;
    if( twbb_ai_builder.reseller_mode ) {
      url = `${twbb_ai_builder.builder_api}onboarding/search_categories`;
    }
    fetch( url, {
      method: "POST",
      headers: {
        "Accept": "application/x.10webx.v1+json",
        "Content-Type": "application/json",
      },
      body: form_data,
    })
      .then((response) => response.json())
      .then((data) => {
        const result = data;
        if (result.status === "success") {
          const searchedItems = result.data;
          _this.printOptions(searchedItems, value);
        } else {
          _this.printOptions(_this.options, value);
        }
        _this.optionClick();
      });
  }

  printOptions (items, searchText = '') {
    const options = this.selectcontainer.find('.options');
    const regex = new RegExp(searchText, 'gi');
    let optionHtml = '';
    for (const item in items) {
      const title = items[item]['title'];
      const highlightText = title.replace(regex, function (str) {
        return "<span class='highlighted'>" + str + "</span>"
      })
      const text = searchText ? highlightText : title;
      optionHtml += '<p class="option " data-value="' + items[item]['name'] + '">' + text + '</p>'
    }
    options.html(optionHtml);
  }

  showOptions() {
    this.selectcontainer.addClass('active');
  }

  hideOptions() {
    this.selectcontainer.removeClass('active');
  }

  shuffle (a) {
    let j, x, i;
    for (i = a.length - 1; i > 0; i--) {
      j = Math.floor(Math.random() * (i + 1));
      x = a[i];
      a[i] = a[j];
      a[j] = x;
    }
    return a;
  }
}
