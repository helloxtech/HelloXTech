jQuery( window ).on( 'elementor/frontend/init', function() {
  class DynamicFeaturesSwiper {
    element = null;
    widget = null;

    constructor(widget, element) {
      this.widget = widget;
      this.element = element;
      this.featureItems = this.widget.querySelectorAll('.twbb-dynamic-features-list-item');
      this.settings = this.getSettings();
      this.initSwiper();
    }

    destroySwiper() {
      if (this.swiper) {
        this.swiper.destroy(true, true);
        this.swiper = null;
      }
    }

    getSettings() {
      var that = this;
      let settings = {
        swiperClass: 'twbb-dynamic-features-swiper',
        containerClass: 'twbb-dynamic-features-swiper-container',
        wrapperClass: 'twbb-dynamic-features-swiper-wrapper',
        slideClass: 'twbb-dynamic-features-swiper-slide',
        slideActiveClass: 'twbb-dynamic-features-swiper-slide-active',
        direction: 'horizontal',
        slidesPerView: 1,
        spaceBetween: parseInt(this.element.dataset.slidesSpacing) || 20,
        speed: parseInt(this.element.dataset.animationDuration) || 500,
        pauseOnMouseEnter: false,
        // loop: true,
        autoplay: this.element.dataset.autoplay === 'yes' ? {
          delay: parseInt(this.element.dataset.interval) || 5000,
          waitForTransition: false,
          disableOnInteraction: false,
        } : false,
        effect: this.element.dataset.animation || 'fade',
        pagination: {
          el: '.twbb-dynamic-features-swiper-pagination',
          clickable: true,
          renderBullet: function (index, className) {
            return '<span class="' + className + '"><span class="progress-indicator"></span></span>';
          },
        },
        breakpoints: {
          768: {
            direction: this.element.dataset.direction || 'horizontal'
          },
        },
        on: {
          init: function() {
            this.el.classList.add('swiper-initialized');
            that.featureItems[this.activeIndex]?.classList.add('active');
            const descriptionElement = that.featureItems[this.activeIndex]?.querySelector('.twbb-dynamic-features-list-item-description');
            if (descriptionElement) {
              descriptionElement.style.maxHeight = that.getHiddenHeight(descriptionElement) + 'px';
            }
            that.setMediaHeight();
          },
          slideChangeTransitionStart: function () {
            that.featureItems.forEach((item, index) => {
              item.classList.toggle('active', index === this.activeIndex);
              const description = item.querySelector('.twbb-dynamic-features-list-item-description');
              if (description) {
                description.style.maxHeight = index === this.activeIndex
                  ? that.getHiddenHeight( description ) + 'px'
                  : 0;
              }
            });
            //Calculate the media height during the animation te get it animating when height is set in %
            that.animateInterval = setInterval(function () {
              that.setMediaHeight();
            });
          },
          slideChangeTransitionEnd: function () {
            that.setMediaHeight();
            this.el.parentElement.parentElement.style.setProperty("--progress", 0);
            clearInterval(that.animateInterval);
          },
          autoplayTimeLeft(s, time, progress) {
            this.el.parentElement.parentElement.style.setProperty("--progress", 1 - progress);
          }
        }
      };

      if (jQuery(window).width() >= 768) {
        if ( settings.effect === 'fade' ) {
          settings.fadeEffect = {
            crossFade: true
          };
        }
        else if ( settings.effect === 'soft_shift' ) {
          settings.effect = 'creative';
          const isImageLeft = this.widget.classList.contains( 'twbb-dynamic-features-layout-image-left' );
          const translateValue = isImageLeft ? [ 15, 0, 0 ] : [ -15, 0, 0 ];
          settings.creativeEffect = {
            prev: {
              opacity: 0,
              translate: translateValue
            },
            next: {
              opacity: 0,
              translate: translateValue
            }
          }
        }
        else if ( settings.effect === 'zoom' ) {
          settings.effect = 'creative';
          settings.creativeEffect = {
            prev: {
              opacity: 0,
              scale: 0.8,
              translate: [ 0, 0, -800 ],
            },
            next: {
              opacity: 0,
              scale: 0.8,
              translate: [ 0, 0, -800 ],
            },
          };
        }
      }
      else {
        settings.effect = 'vertical_slider';
      }

      return settings;
    }

    setMediaHeight() {
      if (this.element.dataset.adaptiveMedia !== 'true') { return; }
      const media = this.widget.querySelector( '.twbb-dynamic-features-media' );
      media.style.setProperty("--height", 0);
      const container = this.widget.querySelector( '.twbb-dynamic-features-inner-container' );
      media.style.setProperty("--height", container.offsetHeight + 'px');
    }

    getHiddenHeight(el) {
      if (!el?.cloneNode) {
        return null;
      }

      const clone = el.cloneNode(true);

      Object.assign(clone.style, {
        overflow: "visible",
        height: "auto",
        maxHeight: "none",
        opacity: "0",
        visibility: "hidden",
        display: "block",
      });

      el.after(clone);
      const height = clone.offsetHeight;

      clone.remove();

      return height;
    }

    initSwiper() {
      this.swiper = new TWBBSwiper(this.element, this.settings);

      // Add click handlers for feature list items
      const featureItems = this.widget.querySelectorAll('.twbb-dynamic-features-list-item');
      featureItems.forEach((item, index) => {
        item.addEventListener('click', () => {
          this.swiper.slideTo(index);
        });
      });
    }
  }

  elementorFrontend.hooks.addAction( 'frontend/element_ready/widget', function ( $scope ) {
    if ($scope[0].dataset.widget_type.startsWith('twbb_dynamic_features.')) {
      const swiperElement = $scope[0].querySelector( '.twbb-dynamic-features-media' );
      if ( swiperElement ) {
        // Destroy existing instance if it exists
        if ( swiperElement.swiperInstance ) {
          swiperElement.swiperInstance.destroySwiper();
        }
        // Create new instance and store reference
        swiperElement.swiperInstance = new DynamicFeaturesSwiper( $scope[0], swiperElement );
      }
    }
  });
});