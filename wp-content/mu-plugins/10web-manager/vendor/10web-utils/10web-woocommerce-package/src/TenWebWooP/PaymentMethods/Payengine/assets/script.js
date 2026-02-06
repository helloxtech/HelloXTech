window.twwp_checkout = {
  $initialized: false,
  $checkout_form: jQuery('form.checkout'),
  $block_checkout: false,
  $secureForm: null,
  $paymentNeeded: true,
  $performing3ds: false,
  $3dsresponse: false,
  $css: {
    background: 'rgba(215, 224, 235, 0.18)',
    border: 'solid 1px black',
    borderRadius: '4px',
    boxSizing: 'border-box',
    color: 'black',
    fontSize: '12px',
    height: '40px',
    letterSpacing: '.7px',
    lineHeight: 'normal',
    padding: '0 10px',
    '&::placeholder': {
      color: 'black',
      fontSize: '12px',
      opacity: '.4',
    },
    '&:hover': {
      '&::placeholder': {
        opacity: '.7'
      }
    },
    '&:focus': {
      '&::placeholder': {
        opacity: '.7'
      }
    },
  },
  init: function() {
    if (0 === jQuery('form.checkout').length) {
      this.$checkout_form = jQuery('form.wc-block-checkout__form');
      this.$block_checkout = true;
    }
    else {
      this.$checkout_form = jQuery('form.checkout');
    }
    if ('undefined' != typeof PayEngine && jQuery('#twwp-card-form').length) {
      if (!this.$initialized) {
        this.createSecureFields();
        this.$checkout_form.on('checkout_place_order_tenweb_payments', function(event, wc_checkout_form) {
          if (window.twwp_checkout.$paymentNeeded) {
            window.twwp_checkout.handle10WebPay(event, wc_checkout_form);
            return false;
          }
        });
        this.$checkout_form.on('checkout_place_order_success', function(e, result) {
          if (result.twwp_3ds_action_required) {
            window.twwp_checkout.blockForm();
            PayEngine.perform3DSFlow(
              result.twwp_3ds_data,
              function (response) {
                window.twwp_checkout.resetFields();
                window.twwp_checkout.unblockForm();
                window.twwp_checkout.$performing3ds = false;
                window.twwp_checkout.$3dsresponse = response.success ? 'success' : 'fail';
                if (response.success) {
                  window.twwp_checkout.$checkout_form.append(jQuery('<input type="hidden" id="twwp_payengine_3ds_succeeded" name="twwp_payengine_3ds_result" />' ).val(window.twwp_checkout.$3dsresponse));
                  window.twwp_checkout.$paymentNeeded = false;
                } else {
                  window.twwp_checkout.$checkout_form.append(jQuery('<input type="hidden" id="twwp_payengine_3ds_succeeded" name="twwp_payengine_3ds_result" />' ).val(window.twwp_checkout.$3dsresponse));
                }
                window.twwp_checkout.$checkout_form.trigger('submit');
              }
            );
            window.twwp_checkout.$performing3ds = true;
            return false;
          }
          window.twwp_checkout.resetFields();
          window.twwp_checkout.unblockForm();
          window.twwp_checkout.$paymentNeeded = true;
          return true;
        });
        jQuery(document.body).on('checkout_error', function(e) {
          // Woocommerce shows an error in any case we stop checkout.
          // We don't want an error message when 3DS verification is required.
          // Remove error message if 3DS is performing
          if (window.twwp_checkout.$performing3ds) {
            window.twwp_checkout.blockForm();
            jQuery('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove();
          }

          // Reset everything in case of any error.
          window.twwp_checkout.$paymentNeeded = true;
          window.twwp_checkout.resetFields();
        });
        this.$initialized = true;
      }
      else if (jQuery('#cc-number iframe').length === 0) {
        // Redraw the fields as they are dissapearing in some cases.
        this.createSecureFields();
      }
    }
  },
  blockForm: function () {
    if (!this.$block_checkout) {
      this.$checkout_form.addClass( 'processing' ).block( {
        message: null,
        overlayCSS: {
          background: '#fff',
          opacity: 0.6
        }
      } );
    }
  },
  unblockForm: function () {
    if (!this.$block_checkout) {
      this.$checkout_form.removeClass( 'processing' ).unblock();
    }
  },
  createSecureFields: function() {
    this.blockForm();

    PayEngine.SecureFields.create().then((form) => {
      form.field("#cc-name", {
        type: "text",
        name: "card_holder",
        placeholder: "Card holder",
        validations: ["required"],
        css: this.$css,
      });
      form.field("#cc-number", {
        type: "card-number",
        name: "card_number",
        successColor: "#4F8A10",
        errorColor: "#D8000C",
        placeholder: "Card number",
        showCardIcon: false,
        validations: ["required", "validCardNumber"],
        css: this.$css,
      });
      form.field("#cc-cvc", {
        type: "card-security-code",
        name: "card_cvc",
        successColor: "#4F8A10",
        errorColor: "#D8000C",
        placeholder: "CVC",
        maxLength: 4,
        validations: ["required", "validCardSecurityCode"],
        css: this.$css,
      });
      form.field("#cc-expiration-date", {
        type: "card-expiration-date",
        name: "card_exp",
        successColor: "#4F8A10",
        errorColor: "#D8000C",
        placeholder: "MM / YY",
        validations: ["required", "validCardExpirationDate"],
        css: this.$css,
      });

      this.$secureForm = form;
      this.unblockForm();
    });
  },
  handle10WebPay: async function(event, wc_checkout_form) {
    window.twwp_checkout.blockForm();
    try {
      const cardObj = await twwp_checkout.$secureForm.createCard();
      if ( cardObj.token ) {
        // Card is successfully create. Pass the token and browser info to backend.
        this.$checkout_form.append( jQuery( '<input type="hidden" id="twwp_payengine_card_token" name="twwp_payengine_card_token" />' ).val( cardObj.token ) );
        this.$checkout_form.append( jQuery( '<input type="hidden" id="twwp_payengine_browser_info" name="twwp_payengine_browser_info" />' ).val( await PayEngine.collectBrowserInfo() ) );
        window.twwp_checkout.unblockForm();
        window.twwp_checkout.$paymentNeeded = false;
        window.twwp_checkout.$checkout_form.trigger('submit');
      }
      else {
        window.twwp_checkout.unblockForm();
        window.twwp_checkout.$paymentNeeded = true;
        window.twwp_checkout.resetFields();
        let err = [];
        for (let key in cardObj) {
          if (cardObj[key].errorMessages) {
            err.push(key + ' ' + cardObj[key].errorMessages.join(', '));
          }
        }
        if (cardObj.data && cardObj.data.error) {
          err.push(e.data.message);
        }
        wc_checkout_form.submit_error('<div class="woocommerce-error">Payment failed: ' + err.join(', ') + '.</div>');
      }
    } catch ( e ) {
      window.twwp_checkout.unblockForm();
      window.twwp_checkout.$paymentNeeded = true;
      window.twwp_checkout.resetFields();
      let err = [];
      for (let key in e) {
        if (e[key].errorMessages) {
          err.push(key + ' ' + e[key].errorMessages.join(', '));
        }
      }
      if (e.data && e.data.error) {
        err.push(e.data.message);
      }
      // TODO: we probably need to modify errors returned here as they are kinda ugly.
      wc_checkout_form.submit_error('<div class="woocommerce-error">Payment failed: ' + err.join(', ') + '</div>');
    }
  },
  resetFields: function(field_ids) {
    jQuery('#twwp_payengine_card_token').remove();
    jQuery('#twwp_payengine_browser_info').remove();
    jQuery('#twwp_payengine_3ds_succeeded').remove();
    window.twwp_checkout.$performing3ds = false;
    window.twwp_checkout.$3dsresponse = false;
  }
};
jQuery(document).ready(function(){
  jQuery('body').on('updated_checkout', function () {
    window.twwp_checkout.init();
  });
});

window.addEventListener( 'elementor/frontend/init', () => {
  // Elementor edit page.
  /*if (elementor) {
    elementor.on( 'document:loaded', () => {
      window.twwp_checkout.init();
    } );
  }*/
  // The first one should be a general solution, unfortunately it does not work, so had to use this one. This will work for one widget only.
  elementorFrontend.hooks.addAction( 'frontend/element_ready/twbb_woocommerce-checkout-page.default', function ( $scope ) {
    window.twwp_checkout.init();
  });
} );
