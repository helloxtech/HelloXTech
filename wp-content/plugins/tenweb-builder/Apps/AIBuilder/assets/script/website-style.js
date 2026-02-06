class TwbbWebsiteStyle {
  constructor(historyKey, uniqueId, isMobile) {
    this.container = jQuery('.twbb-ai-builder__container[data-type="websiteStyle"]');
    this.customizerContainer = jQuery('.website-customizer-container');
    this.historyKey = historyKey;
    this.uniqueId = uniqueId;
    this.isMobile = isMobile;
    this.count = 0;
    this.styles = {
      '--accentColor': '#18778f',
      '--secondaryColor': '#f5f0e8',
      '--accentColorShade': '#18778f0A',
      '--accentColorShade2': '#18778f1A',
      '--mainFontFamily': 'Georgia'
    };
    this.theme = 'classic';
    this.font = {
      primary_font: "Montserrat"
    };
    this.color = {
      primary_color: "#18778f",
      secondary_color: "#f5f0e8",
      background_dark: "#18778f"
    };
    this.colors = [
      {
        title: 'Indigo',
        primaryColor: '#283566',
        secondaryColor: '#986424'
      },
      {
        title: 'Han blue',
        primaryColor: '#3A5BCC',
        secondaryColor: '#898989',
      },
      {
        title: 'Orchid pink',
        primaryColor: '#E33665',
        secondaryColor: '#898989',
      },
      {
        title: 'Basque green',
        primaryColor: '#5A5E32',
        secondaryColor: '#DBC75B',
      },
      {
        title: 'Red berry',
        primaryColor: '#5F1928',
        secondaryColor: '#DE87C2',
      },
      {
        title: 'Purplish blue',
        primaryColor: '#5833F1',
        secondaryColor: '#898989',
      },
      {
        title: 'Obsidian shell',
        primaryColor: '#821FA0',
        secondaryColor: '#F4C730',
      },
      {
        title: 'Beagle brown',
        primaryColor: '#8D6D36',
        secondaryColor: '#9EBD59',
      },
      {
        title: 'Aurora orange',
        primaryColor: '#ED6744',
        secondaryColor: '#A7A2A0',
      },
    ];
    this.themes = [
      {
        title: 'Classic',
        font: 'Georgia',
        class: 'classic'
      },
      {
        title: 'Flat',
        font: 'PT Sans',
        class: 'flat'
      },
      {
        title: 'Material',
        font: 'Montserrat',
        class: 'material'
      },
      {
        title: 'Minimalistic',
        font: 'Lexend',
        class: 'minimalistic'
      },
      {
        title: 'Soft',
        font: 'Poppins',
        class: 'soft'
      }
    ];
  }

  getActiveThemes() {
    return {
      colors: this.color,
      fonts: this.font,
      theme: this.theme,
    }
  }

  setColorsThemes( paramsFromOutline ) {
    const _this = this,
      container = this.container,
      history = JSON.parse('{}'),
      isField = localStorage.getItem('fieldData') === '1',
      selectedSource = isField ? history : paramsFromOutline,
      aiColorSource = (paramsFromOutline.colors && !isField) ? selectedSource?.colors : history?.generated_color;

    // Add AI-generated color if needed
    if (this.count === 0) {
      const aiColor = {
        title: 'AI',
        primaryColor: aiColorSource?.primary_color ?? this.color.primary_color,
        secondaryColor: aiColorSource?.secondary_color ?? this.color.secondary_color
      }
      this.color = {
        primary_color: aiColorSource?.primary_color ?? this.color.primary_color,
        secondary_color: aiColorSource?.secondary_color ?? this.color.secondary_color,
        //background_dark: aiColorSource?.primary_color ?? this.color.primary_color
      };

      // Mark selected if not from history
      if (!isField) aiColor.selected = true;

      this.colors.unshift(aiColor);

      if (!isField) {
        this.changeHistory({ generated_color: aiColor });
        this.themes[0].selected = true;
        localStorage.setItem('fieldData', '0');
      } else {
        // Set selected flags from history
        this.colors.forEach(color => {
          if (
            color.primaryColor === history?.colors?.primary_color &&
            color.secondaryColor === history?.colors?.secondary_color
          ) {
            color.selected = true;
          }
        });

        this.themes.forEach(theme => {
          if (theme.font === history?.fonts?.primary_font) {
            theme.selected = true;
            this.theme = theme.class;
          }
        });

      }

      this.count++;
    }

    // Apply selected styles
    this.styles['--accentColor'] = selectedSource.colors.primary_color;
    this.styles['--secondaryColor'] = selectedSource.colors.secondary_color;
    this.styles['--accentColorShade'] = `${selectedSource.colors.primary_color}0A`;
    this.styles['--accentColorShade2'] = `${selectedSource.colors.primary_color}1A`;
    this.styles['--mainFontFamily'] = selectedSource.fonts.primary_font;

    // Render UI
    const colorsList = this.colors.map(color => {
      const selected = color.selected ? 'selected' : '';
      return `
      <div class="color-item ${selected}" data-title="${color.title}">
        <div class="color-set-container">
          <div class="color" style="background-color:${color.primaryColor}"></div>
          <div class="color" style="background-color:${color.secondaryColor}"></div>
        </div>
      </div>`;
    }).join('');

    const themesList = this.themes.map(theme => {
      const selected = theme.selected ? 'selected' : '';
      return `
      <div class="theme ${theme.class} ${selected}" data-title="${theme.title}">
        <div>
          <span class="theme-title">${theme.title}</span>
          <span class="theme-paragraph">This is your paragraph.</span>
        </div>
        <a class="theme-btn ${theme.class}">
          <span>Button</span>
        </a>
      </div>`;
    }).join('');
    container.find('.color-picker-container .list').html(colorsList);
    container.find('.themes-picker-container .list').html(themesList);

    jQuery(document).on('click', '.twbb-ai-builder__container[data-type="websiteStyle"] .color-item', function() {
      _this.switchColor(jQuery(this));
    });
    jQuery(document).on('click', '.twbb-ai-builder__container[data-type="websiteStyle"] .theme', function() {
      _this.switchTheme(jQuery(this));
    });

    this.changeStyles();
  }

  switchColor( element ) {
    const selectedColor = element.attr('data-title'),
      colorOption = this.colors.filter( color => color.title === selectedColor );
    this.color = {
      primary_color: colorOption[0]?.primaryColor,
      secondary_color: colorOption[0]?.secondaryColor,
      //background_dark: colorOption[0]?.primaryColor
    };

    this.styles['--accentColor'] = colorOption[0]?.primaryColor;
    this.styles['--secondaryColor'] = colorOption[0]?.secondaryColor;
    this.styles['--accentColorShade'] = `${colorOption[0]?.primaryColor}0A`;
    this.styles['--accentColorShade2'] = `${colorOption[0]?.primaryColor}1A`;
    this.changeHistory(
      {
        'colors': this.color,
      }
    );
    this.changeStyles();
    jQuery('.twbb-ai-builder__container[data-type="websiteStyle"] .color-item').removeClass('selected');
    element.addClass('selected');
    if (twbb_ai_builder.reseller_mode) {
      twbSendEventToRouth({
        eventCategory: 'Reseller Action',
        eventAction: 'Section-based AI Flow - Customize Website',
        eventLabel: 'Choose Color',
        uniqueId: this.uniqueId,
        isMobile: this.isMobile
      });
    } else {
      twbSendEventToPublicRouth({
        eventCategory: 'Hosted Website Action',
        eventAction: 'Section-based AI Flow - Customize Website',
        eventLabel: 'Choose Color',
        uniqueId: this.uniqueId,
        isMobile: this.isMobile
      });
    }
  }

  switchTheme(element) {
    const selectedTheme = element.attr('data-title'),
      themeOption = this.themes.filter( theme => theme.title === selectedTheme );
    this.styles['--mainFontFamily'] = themeOption[0]?.font;
    this.theme = themeOption[0]?.class;
    this.font = {
      primary_font: themeOption[0]?.font
    };
    this.changeHistory(
      {
        'fonts': this.font,
        'theme': this.theme,
      }
    );
    this.changeStyles();
    jQuery('.twbb-ai-builder__container[data-type="websiteStyle"] .theme').removeClass('selected');
    element.addClass('selected');
    if (twbb_ai_builder.reseller_mode) {
      twbSendEventToRouth({
        eventCategory: 'Reseller Action',
        eventAction: 'Section-based AI Flow - Customize Website',
        eventLabel: 'Choose Theme',
        uniqueId: this.uniqueId,
        isMobile: this.isMobile
      });
    } else {
      twbSendEventToPublicRouth({
        eventCategory: 'Hosted Website Action',
        eventAction: 'Section-based AI Flow - Customize Website',
        eventLabel: 'Choose Theme',
        uniqueId: this.uniqueId,
        isMobile: this.isMobile
      });
    }
  }

  changeStyles() {
    let style = '';
    for (const key in this.styles) {
      if (this.styles.hasOwnProperty(key)) {
        style += key + ':' + this.styles[key] + ';';
      }
    }
    this.customizerContainer.attr('style', style);
    this.customizerContainer.removeAttr('class');
    this.customizerContainer.addClass('website-customizer-container').addClass(this.theme);
  }

  changeHistory(options) {
    const history = {};

    if (history) {
      for (const option in options) {
        history[option] = options[option];
      }
    }
    localStorage.setItem( this.historyKey,  JSON.stringify(history));
  }
}
