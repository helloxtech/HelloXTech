jQuery( window ).on( 'elementor/frontend/init', function() {
    class Checkout extends TWBB_WooCommerce_Base {
        getDefaultSettings() {
            const defaultSettings = super.getDefaultSettings(...arguments);
            return {
                selectors: {
                    ...defaultSettings.selectors,
                    container: '.elementor-widget-twbb_woocommerce-checkout-page',
                    loginForm: '.e-woocommerce-login-anchor',
                    loginSubmit: '.e-woocommerce-form-login-submit',
                    loginSection: '.e-woocommerce-login-section',
                    showCouponForm: '.e-show-coupon-form',
                    couponSection: '.e-coupon-anchor',
                    showLoginForm: '.e-show-login',
                    applyCoupon: '.e-apply-coupon',
                    checkoutForm: 'form.woocommerce-checkout',
                    couponBox: '.e-coupon-box',
                    address: 'address',
                    wpHttpRefererInputs: '[name="_wp_http_referer"]',
                },
                classes: defaultSettings.classes,
                ajaxUrl: elementorTenwebFrontend.config.ajaxurl
            };
        }
        getDefaultElements() {
            const selectors = this.getSettings('selectors');
            return {
                ...super.getDefaultElements(...arguments),
                $container: this.$element.find(selectors.container),
                $loginForm: this.$element.find(selectors.loginForm),
                $showCouponForm: this.$element.find(selectors.showCouponForm),
                $couponSection: this.$element.find(selectors.couponSection),
                $showLoginForm: this.$element.find(selectors.showLoginForm),
                $applyCoupon: this.$element.find(selectors.applyCoupon),
                $loginSubmit: this.$element.find(selectors.loginSubmit),
                $couponBox: this.$element.find(selectors.couponBox),
                $checkoutForm: this.$element.find(selectors.checkoutForm),
                $loginSection: this.$element.find(selectors.loginSection),
                $address: this.$element.find(selectors.address),
            };
        }
        bindEvents() {
            super.bindEvents(...arguments);
            this.elements.$showCouponForm.on('click', event => {
                event.preventDefault();
                this.elements.$couponSection.slideToggle();
            });

            this.elements.$showLoginForm.on('click', event => {
                event.preventDefault();
                this.elements.$loginForm.slideToggle();
            });

            elementorFrontend.elements.$body.on('updated_checkout', () => {
                this.applyPurchaseButtonHoverAnimation();
                this.updateWpReferers();
            });

            jQuery(document).off('click', '.twbb-payment-title').on('click', '.twbb-payment-title', function () {
                if(jQuery(this).closest(".twbb-payment-item").hasClass("twbb-active-payment")) {
                    return false;
                }
                jQuery(this).find("input").prop('checked', true);
                jQuery(this).find("input").trigger('change');
            });

            jQuery(document).off('change', 'input[name="payment_method"]').on('change', 'input[name="payment_method"]', function () {
                if(jQuery(this).closest(".twbb-payment-item").hasClass('twbb-active-payment')) {
                    return;
                }
                jQuery(document).find('.twbb-payment-item.twbb-active-payment').find(".twbb-payment-description").slideUp(300);

                jQuery(document).find('.twbb-payment-item.twbb-active-payment').removeClass('twbb-active-payment');
                jQuery(this).closest(".twbb-payment-item").addClass('twbb-active-payment');

                jQuery(this).closest(".twbb-payment-item").find(".twbb-payment-description").slideDown(300, function(){
                    jQuery(window).trigger("resize");
                    if ( jQuery(this).closest(".twbb-payment-item").find("#twwp-card-element").length ) {
                        self.detectStripeIframeResize();
                    }
                });
            });

            jQuery(document).off('change', 'input.shipping_method').on('change', 'input.shipping_method', function () {
                jQuery(document).find('.twbb-active-shipping-method').removeClass('twbb-active-shipping-method');
                jQuery(this).closest('li').addClass('twbb-active-shipping-method');
                setTimeout(function() {
                    jQuery(window).trigger('resize');
                    }, 500);

            });

            let self = this;
            // Apply coupon code
            jQuery(document).off('click','.twbb-coupon-button').on('click', '.twbb-coupon-button', function (e) {
                e.preventDefault();
                if( jQuery(this).hasClass("twbb-coupon-button-inactive") ) return false;
                self.applyCoupon();
            });

            jQuery(document).on('click', '#place_order', function () {
                const $btn = jQuery(this);
                $btn.addClass('twbb-loading');
                setTimeout(function(){
                    let container_width = jQuery(".elementor-widget-twbb_10web_checkout").parent().width();
                    let window_width = jQuery(window).width();
                    let leftGap = (window_width - container_width) / 2;
                    jQuery(document).find(".blockUI.blockOverlay").css({
                        'width': jQuery(window).width(),
                        'left': -leftGap
                    });
                },500);
            });

            jQuery(document.body).on('checkout_error', function () {
                // Find all fields that have an error message and add red border
                jQuery('.woocommerce-invalid:visible').addClass('twbb-require-field');
                const $btn = jQuery('#place_order');
                $btn.removeClass('twbb-loading');

                const $invalidPayment = jQuery('.woocommerce-error [data-id="invalid-payment-method"]');

                if ($invalidPayment.length && jQuery(".twbb-nopayment-item").length) {
                    jQuery(".twbb-payment-item").after(
                        '<p id="payment_method_description" class="checkout-inline-error-message">' +
                        $invalidPayment.text() +
                        '</p>'
                    );
                }

                setTimeout(function() {
                    jQuery(window).trigger('resize');
                }, 2000);
            });

            jQuery(document).on('.elementor-widget-twbb_10web_checkout input change', 'input, select, textarea', function() {
                jQuery(this).closest('.twbb-require-field').removeClass('twbb-require-field');
            });

            jQuery('#billing-details').find('input, select, textarea').each(function() {
                if (jQuery(this).prop('required')) {
                    jQuery(this).attr('data-was-required', 'true');
                }
            });

            jQuery(document).off('click', '.twbb_use_shipping_as_billing_label').on('click', '.twbb_use_shipping_as_billing_label', function() {
                jQuery('#twbb_use_shipping_as_billing').click();
            })

            jQuery('#twbb_use_shipping_as_billing').change(self.toggleBillingDetails);
            self.toggleBillingDetails();

            // Remove Select2 on page load
            self.removeSelect2();
            self.removeSelect2Actions();

            // Initial check on page load
            self.updateStateArrow();

            self.changePlaceholderRequiredText();

            jQuery(document).on("click","#shipping_method li",function() {
                jQuery(this).find('input[type="radio"]').prop('checked', true).trigger('change');
            })

            /* Allow only +,-,space and numbers in phone field */
            jQuery(document).on('input', 'input[type="tel"]', function() {
                let cleaned = jQuery(this).val().replace(/[^0-9+\-\s]/g, '');
                jQuery(this).val(cleaned);
            });

            /* Add/remove inactive class to coupon button */
            jQuery(document).on('input', '#coupon_code', function() {
                if( jQuery(this).val() === '' ) {
                    jQuery(".twbb-coupon-button").addClass("twbb-coupon-button-inactive");
                } else {
                    jQuery(".twbb-coupon-button").removeClass("twbb-coupon-button-inactive");
                }
            });
            if( jQuery('#coupon_code').val() === '' ) {
                jQuery(".twbb-coupon-button").addClass("twbb-coupon-button-inactive");
            } else {
                jQuery(".twbb-coupon-button").removeClass("twbb-coupon-button-inactive");
            }

            jQuery(window).resize(function() {
                jQuery(".twbb-checkout-col2, .twbb-checkout-col1, .twbb-checkout-container, .elementor-widget-twbb_10web_checkout," +
                    ".twbb-checkout-col2 .twbb-checkout-section," +
                    ".twbb-checkout-col1 .twbb-checkout-section").removeAttr("style");


                if( jQuery(window).width() > 600 ) {
                    self.left_column_count_started = false;
                    self.right_column_count_started = false;
                    /* Right column out of box calculation */
                    self.rightColumnCalculation(self);
                    /* Left column out of box calculation */
                    self.leftColumnCalculation(self);
                }

                self.checkoutButtonToggleTempIds();
                self.setOrderSummaryTopMobile();

            })
            self.checkoutButtonToggleTempIds();

            jQuery(document).off("click",".twbb-order-review-heading").on("click",".twbb-order-review-heading",function() {
                if(jQuery(this).closest('.twbb-mobile-order-summery').hasClass("twbb-mobile-order-summery-opem")) {
                    jQuery(this).closest('.twbb-mobile-order-summery').removeClass("twbb-mobile-order-summery-opem");
                    jQuery(this).closest('#order_review').find(".twbb-order-review-content").slideUp(300);
                } else {
                    jQuery(this).closest('.twbb-mobile-order-summery').addClass("twbb-mobile-order-summery-opem");
                    jQuery(this).closest('#order_review').find(".twbb-order-review-content").slideDown(300);
                }

            })

            self.setOrderSummaryTopMobile();

            /* Observe order comments textarea to trigger resize window */
            if (jQuery('#order_comments').length && 'ResizeObserver' in window) {
                let resizeTimeout;

                const observer = new ResizeObserver(function() {
                    clearTimeout(resizeTimeout);
                    resizeTimeout = setTimeout(function() {
                        jQuery(window).trigger('resize');
                    }, 300); // Adjust delay as needed
                });

                observer.observe(jQuery('#order_comments')[0]);
            }

            /* in case of 10web payment active detect load and resize */
            if ( jQuery("#twwp-card-element").closest(".twbb-payment-item").hasClass("twbb-active-payment") ) {
                self.detectStripeIframe();
            }

            jQuery(document.body).on('updated_checkout', function() {
                setTimeout(function() {
                    jQuery(window).trigger('resize');
                }, 300); // Adjust delay as needed
            });


            /* This functionality disable zoom in mobile view input focuse */
            if (window.matchMedia('(max-width: 767px)').matches) {
                jQuery('.elementor-widget-twbb_10web_checkout input').each(function () {
                    this.addEventListener('touchstart', self.disableZoom, { passive: true });
                    this.addEventListener('focus', self.disableZoom, { passive: true });
                    this.addEventListener('blur', self.restoreZoom, { passive: true });
                });
            }
        }

        disableZoom() {
            const viewport = document.querySelector('meta[name="viewport"]');
            if (viewport) {
                viewport.setAttribute(
                    'content',
                    'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no'
                );
            }
        }

        restoreZoom() {
            setTimeout(() => {
                const viewport = document.querySelector('meta[name="viewport"]');
                if (viewport) {
                    viewport.setAttribute('content', 'width=device-width, initial-scale=1.0');
                }
            }, 300); // delay restore by 300ms
        }

        /* This function catch resize in the iframe */
        detectStripeIframeResize() {
            if( !jQuery(document).find(".elementor-widget-twbb_10web_checkout").hasClass("twbb-col1-full") ) return;
            const iframe = document.querySelector('#twwp-card-element iframe');

            let resizeTimeout;

            const resizeObserver = new ResizeObserver(entries => {
                clearTimeout(resizeTimeout); // reset the timer on every resize
                resizeTimeout = setTimeout(() => {
                    for (let entry of entries) {
                        jQuery(window).trigger('resize');
                    }
                }, 300); // wait 300ms after last resize event
            });

            if (iframe) {
                resizeObserver.observe(iframe);
            }
        }

        detectStripeIframe() {
            let iframeCheckCount = 0;
            const maxChecks = 5000 / 300; // ~16 checks in 5 seconds
            const interval = setInterval(() => {
                iframeCheckCount++;

                const iframe = document.querySelector('#twwp-card-element iframe');
                if (iframe) {
                    jQuery(document).trigger('twwp_stripe_iframe_ready');

                    iframe.addEventListener('load', function () {
                        jQuery(window).trigger('resize');
                    });

                    clearInterval(interval);
                }

                if (iframeCheckCount >= maxChecks) {
                    clearInterval(interval);
                }
            }, 300);
        }

        setOrderSummaryTopMobile() {
            const isMobile = jQuery(window).width() <= 600;
            if( isMobile ) {
                let widget = jQuery(document).find(".elementor-widget-twbb_10web_checkout");
                widget.closest(".elementor-element[data-element_type='container']").css('padding', 0);
                widget.closest(".e-con-inner").css('padding', 0);
            }
        }

        /**
         *  Function is changing id attr to value to temp value to prevent the same id duplication in the content,
         *  which make process broken during the checkout process
        */
        checkoutButtonToggleTempIds() {
            const $mobileSection = jQuery(".twbb-checkout-button-section.twbb-checkout-button-section-mobile");
            const $desktopSection = jQuery(".twbb-checkout-button-section:not(.twbb-checkout-button-section-mobile)");

            const isMobile = jQuery(window).width() <= 600;

            const activateTempIds = ($container) => {
                if (!$container.hasClass("toggleTempIdsActive")) {
                    $container.find("*[id]").each(function () {
                        const $el = jQuery(this);
                        const oldId = $el.attr("id");
                        if (!oldId.endsWith("twb_temp")) {
                            $el.attr("id", oldId + "twb_temp");
                        }
                    });
                    $container.addClass("toggleTempIdsActive");
                }
            };

            const deactivateTempIds = ($container) => {
                if ($container.hasClass("toggleTempIdsActive")) {
                    $container.find("*[id$='twb_temp']").each(function () {
                        const $el = jQuery(this);
                        const oldId = $el.attr("id");
                        const newId = oldId.replace(/twb_temp$/, "");
                        $el.attr("id", newId);
                    });
                    $container.removeClass("toggleTempIdsActive");
                }
            };

            if (isMobile) {
                activateTempIds($desktopSection);
                deactivateTempIds($mobileSection);
            } else {
                activateTempIds($mobileSection);
                deactivateTempIds($desktopSection);
            }
        }


        /* Right column out of box calculation */
        rightColumnCalculation(self) {

            if( !jQuery(".elementor-widget-twbb_10web_checkout").hasClass("twbb-col2-full") || self.right_column_count_started === true) {
                return;
            }
            let container_width = jQuery(".elementor-widget-twbb_10web_checkout").parent().width();
            let window_width = jQuery(window).width();
            let first_col_width = jQuery(".twbb-checkout-col1").outerWidth();
            let second_col_width = jQuery(".twbb-checkout-col2").outerWidth();
            let sectionWidth = jQuery(".twbb-checkout-col2 .twbb-checkout-section").outerWidth();
            let widgetHeight = jQuery(".elementor-widget-twbb_10web_checkout .twbb-checkout-col1").outerHeight();
            let leftGap = (window_width - container_width) / 2;

            jQuery(".twbb-checkout-col2").css({
                'left': first_col_width,
                'width': (leftGap + second_col_width),
                'position': 'absolute',
                'height': widgetHeight,
            });

            jQuery(".twbb-checkout-col2 .twbb-checkout-section").css({
                'width': sectionWidth,
            });
            self.right_column_count_started = true;
        }

        /* Left column out of box calculation */
        leftColumnCalculation(self) {

            if( !jQuery(".elementor-widget-twbb_10web_checkout").hasClass("twbb-col1-full") || self.left_column_count_started === true) {
                return;
            }
            let container_width = jQuery(".elementor-widget-twbb_10web_checkout").parent().width();
            let window_width = jQuery(window).width();
            let first_col_width = jQuery(".twbb-checkout-col1").outerWidth();
            let sectionWidth = jQuery(".twbb-checkout-col1 .twbb-checkout-section").outerWidth();
            let widgetHeight = jQuery(".elementor-widget-twbb_10web_checkout .twbb-checkout-col1").outerHeight();

            let widgetHeightMsg = widgetHeight;
            let $msgEl = jQuery(".elementor-widget-twbb_10web_checkout .woocommerce-message");
            if ($msgEl.length && $msgEl.is(':visible')) {
                let msgHeight = $msgEl.outerHeight() || 0;
                let marginBottom = parseFloat($msgEl.css('margin-bottom')) || 0;

                widgetHeightMsg = widgetHeight + msgHeight + marginBottom;
            }
            jQuery(".twbb-checkout-container").css({
                'height': widgetHeight,
            });

            jQuery(".elementor-widget-twbb_10web_checkout").css("height", widgetHeightMsg);

            let leftGap = (window_width - container_width) / 2;
            jQuery(".twbb-checkout-col1").css({
                'left': -leftGap,
                'width': (leftGap + first_col_width),
                'position': 'absolute',
                'display': 'flex',
                'flex-direction': 'column',
                'align-items': 'end',
                'height': widgetHeight,
            });

            jQuery(".twbb-checkout-col1 .twbb-checkout-section").css({
                'width': sectionWidth,
            });

            if( !jQuery(".elementor-widget-twbb_10web_checkout").hasClass("twbb-col2-full") ) {
                jQuery(".twbb-checkout-col2").css({
                    'margin-left': first_col_width,
                    'height': widgetHeight
                });
            }

            self.left_column_count_started = true;
        }


        /**
         *  Function is adding * symbol  and (optional) nearby placeholder,
         *  in case of labels hidden
         */
        changePlaceholderRequiredText() {
            const labelsShown = this.$element.hasClass('twbb-show-label-yes');

            if (labelsShown) return; // Do nothing if labels are shown

            this.$element.find('input:not([name="coupon_code"]), select, textarea').each(function () {
                const $el = jQuery(this);
                const isRequired = $el.prop('required') || $el.attr('aria-required') === 'true';
                const currentPlaceholder = $el.attr('placeholder') || '';

                if (isRequired && currentPlaceholder !== '' && !currentPlaceholder.includes('*')) {
                    $el.attr('placeholder', `${currentPlaceholder} *`);
                } else if (!isRequired && !currentPlaceholder.toLowerCase().includes('(optional)') && currentPlaceholder !== '') {
                    $el.attr('placeholder', `${currentPlaceholder} (optional)`);
                }
            });

        }

        updateStateArrow() {
            const updateArrow = (fieldId, wrapperSelector) => {
                const $field = jQuery(fieldId);
                const $wrapper = jQuery(wrapperSelector);

                if ($field.length && $wrapper.length) {
                    $wrapper.toggleClass('hide-arrow', !$field.is('select'));
                }
            };

            updateArrow('#shipping_state', '#shipping_state_field .woocommerce-input-wrapper');
            updateArrow('#billing_state', '#billing_state_field .woocommerce-input-wrapper');

            const $billing = jQuery('select[name="billing_state"]');
            const $shipping = jQuery('select[name="shipping_state"]');

            $billing.find('option:first-child').text('State/region/province');
            $shipping.find('option:first-child').text('State/region/province');

            // Optional: force selection if none selected
            if (!$billing.val()) $billing.val($billing.find('option:first-child').val()).trigger('change');
            if (!$shipping.val()) $shipping.val($shipping.find('option:first-child').val()).trigger('change');
        }

        removeSelect2Actions() {
            let self = this;
            // Detect when WooCommerce applies Select2 and block it
            jQuery(document).on('ajaxSend', function() {
                setTimeout(function () {
                    self.removeSelect2();
                }, 10); // Remove Select2 as soon as AJAX starts
            });

            // Also remove Select2 after WooCommerce updates checkout fields
            jQuery(document).on('updated_checkout update_order_review', function() {
                jQuery(document).find(".twbb-mobile-order-summery .twbb-order-summary-row-coupon-form").remove();


                /* Order review called from fragment and change dynamic values, so this solution replace values to old */
                jQuery('#coupon_code').attr('placeholder', self.couponPlaceholder);
                jQuery(".twbb-coupon-button").val(self.couponButtonText).text(self.couponButtonText);

                if( jQuery('#coupon_code').val() === '' ) {
                    jQuery(".twbb-coupon-button").addClass("twbb-coupon-button-inactive");
                } else {
                    jQuery(".twbb-coupon-button").removeClass("twbb-coupon-button-inactive");
                }

                setTimeout(function () {
                    self.removeSelect2();
                }, 10);
            });

            // Prevent re-initialization when clicking on the select field
            jQuery(document).on('focus', '#billing_country, #shipping_country, #billing_state, #shipping_state', function() {
                setTimeout(function () {
                    self.removeSelect2();
                }, 10);
            });

            // Detect when WooCommerce applies Select2 dynamically and block it
            jQuery(document).on('select2:open', '#billing_country, #shipping_country, #billing_state, #shipping_state', function() {
                jQuery(this).select2('destroy'); // Kill Select2 when it tries to open
            });

            // Detect when Select2 is re-applied on change
            jQuery(document).on('change', '#billing_country, #shipping_country, #billing_state, #shipping_state', function() {
                setTimeout(function () {
                    self.removeSelect2();
                }, 10);
            });

            jQuery(document).off('change',".country_to_state").on('change',".country_to_state", function () {
                // Small delay to allow field replacement
                setTimeout(function () {
                    self.updateStateArrow();
                    self.billingFieldsStructureChange();
                }, 100);
            });
        }

        removeSelect2() {
            jQuery('#billing_country, #shipping_country, #billing_state, #shipping_state').each(function() {
                if (jQuery(this).hasClass('select2-hidden-accessible')) {
                    jQuery(this).select2('destroy'); // Remove Select2 only from these fields
                }
            });
        }

        toggleBillingDetails() {
            if (jQuery('#twbb_use_shipping_as_billing').is(':checked')) {
                jQuery('#billing-details').addClass("twbb-billing-hidden");
                jQuery('#billing-details').find('input, select, textarea').each(function() {
                    jQuery(this).removeAttr('required').attr('data-was-required', 'true');
                });
                jQuery(this).closest('.twbb_use_shipping_as_billing_content').addClass("twbb-use-shipping-as-billing-active");
            } else {
                jQuery('#billing-details').removeClass("twbb-billing-hidden");
                jQuery('#billing-details').find('input, select, textarea').each(function() {
                    if (jQuery(this).attr('data-was-required') === 'true') {
                        jQuery(this).attr('required', 'required');
                    }
                });
                jQuery(this).closest('.twbb_use_shipping_as_billing_content').removeClass("twbb-use-shipping-as-billing-active");
            }
            jQuery(window).trigger('resize');
        }

        onInit() {
            super.onInit(...arguments);
            this.toggleStickyRightColumn();
            this.updateWpReferers();
            this.equalizeElementHeight(this.elements.$address); // Equalize <address> boxes height

            if (elementorFrontend.isEditMode()) {
                this.elements.$loginForm.show();
                this.elements.$couponSection.show();
                this.applyPurchaseButtonHoverAnimation();
            }

            this.billingFieldsStructureChange();
            this.keepCouponButtonText();
        }

        keepCouponButtonText() {
            this.couponPlaceholder = jQuery("#coupon_code").attr("placeholder");
            this.couponButtonText = jQuery(".twbb-coupon-button").val();
        }

        billingFieldsStructureChange() {
            jQuery('.elementor-widget-twbb_10web_checkout').each(function () {
                let widget = jQuery(this);
                let billingCity = widget.find('.woocommerce-billing-fields .form-row#billing_city_field');
                let billingState = widget.find('.woocommerce-billing-fields .form-row#billing_state_field');
                let billingZip = widget.find('.woocommerce-billing-fields .form-row#billing_postcode_field');

                let shippingCity = widget.find('.woocommerce-shipping-fields .form-row#shipping_city_field');
                let shippingState = widget.find('.woocommerce-shipping-fields .form-row#shipping_state_field');
                let shippingZip = widget.find('.woocommerce-shipping-fields .form-row#shipping_postcode_field');

                if (billingCity.length && billingState.length && billingZip.length) {
                    let row = jQuery('<div class="twbb-multicolumn-form-row"></div>');
                    row.append(billingCity).append(billingState).append(billingZip);
                    widget.find('.woocommerce-billing-fields').append(row);
                }
                if (shippingCity.length && shippingState.length && shippingZip.length) {
                    let row = jQuery('<div class="twbb-multicolumn-form-row"></div>');
                    row.append(shippingCity).append(shippingState).append(shippingZip);
                    widget.find('.shipping_address .woocommerce-shipping-fields__field-wrapper').append(row);
                }
            })
            jQuery(window).trigger("resize");
        }

        onElementChange(propertyName) {
            if ('sticky_right_column' === propertyName) {
                this.toggleStickyRightColumn();
            }
        }
        onDestroy() {
            super.onDestroy(...arguments);
            this.deactivateStickyRightColumn();
        }
        applyPurchaseButtonHoverAnimation() {
            const purchaseButtonHoverAnimation = this.getElementSettings('purchase_button_hover_animation');
            if (purchaseButtonHoverAnimation) {
                // This element is recaptured every time because the checkout markup can refresh
                jQuery('#place_order').addClass('elementor-animation-' + purchaseButtonHoverAnimation);
            }
        }
        applyCoupon() {
            // Wc_checkout_params is required to continue, ensure the object exists
            // eslint-disable-next-line camelcase
            if (!wc_checkout_params) {
                return;
            }
            this.startProcessing(this.elements.$couponBox);
            const data = {
                // eslint-disable-next-line camelcase
                security: wc_checkout_params.apply_coupon_nonce,
                coupon_code: jQuery(document).find('input[name="coupon_code"]').val()
            };
            const $btn = jQuery('.twbb-coupon-button');
            $btn.addClass('twbb-loading');
            jQuery('.twbb-coupon-error-message').remove();
            this.elements.$couponBox.removeClass('processing').unblock();
            jQuery.ajax({
                type: 'POST',
                // eslint-disable-next-line camelcase
                url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'apply_coupon'),
                context: this,
                data,
                success(code) {
                    jQuery('.woocommerce-error, .woocommerce-message').remove();
                    this.elements.$couponBox.removeClass('processing').unblock();
                    if (code) {
                        if (code.includes('woocommerce-error')) {
                            jQuery(document).find(".twbb-order-summary-row-coupon-form").css("flex-direction","column");

                            // Extract only the plain text message from WooCommerce
                            const message = "We don’t recognize that code. Enter valid code.";
                            const $errorBox = this.$element.find('.twbb-coupon-error-message');
                            if($errorBox.length) {
                                $errorBox
                                    .html(message)
                                    .slideDown();
                            } else {
                                jQuery(document).find(".twbb-order-summary-row-coupon-form").append("<p class='twbb-coupon-error-message checkout-inline-error-message'>"+message+"</p>");
                            }

                            elementorFrontend.elements.$body.trigger('checkout_error', [code]);
                        } else {
                            this.elements.$checkoutForm.before(code);
                            this.elements.$couponSection.slideUp();
                            elementorFrontend.elements.$body.trigger('applied_coupon_in_checkout', [data.coupon_code]);
                            elementorFrontend.elements.$body.trigger('update_checkout', {
                                update_shipping_method: false
                            });
                        }
                    }
                },
                complete() {
                    jQuery('.twbb-coupon-button').removeClass('twbb-loading');
                },
                dataType: 'html'
            });
        }

        startProcessing($form) {
            if ($form.is('.processing')) {
                return;
            }

            /**
             * .block() is from a jQuery blockUI plugin loaded by WooCommerce. This code is based on WooCommerce
             * core in order for the Checkout widget to behave the same as WooCommerce Checkout pages.
             */
            $form.addClass('processing').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        }

    }
    elementorFrontend.hooks.addAction( 'frontend/element_ready/twbb_10web_checkout.default', function ( $scope ) {
        new Checkout( { $element: $scope } );
    });
});
