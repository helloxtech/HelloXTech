import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';

const { registerPaymentMethod } = window.wc.wcBlocksRegistry;

const loadStripe = () =>
    new Promise( ( resolve ) => {
      try {
        resolve({});
      } catch ( error ) {
        // In order to avoid showing console error publicly to users,
        // we resolve instead of rejecting when there is an error.
        resolve({error});
      }
    } );

const tenwebPaymentPromiseStripe = loadStripe();

const TenwebPaymentLabelStripe = (props) => {
  const { PaymentMethodLabel } = props.components;

  const labelText = __('Credit card / Debit card', 'twwp');

  return <PaymentMethodLabel text={labelText} />;
};

const TenwebPaymentComponentStripe = (props) => {

  const [errorMessage, setErrorMessage] = useState('');

  useEffect(() => {
    Promise.resolve(tenwebPaymentPromiseStripe).then(({error}) => {
      if (error) {
        setErrorMessage(error.message);
      }
    });
  }, [setErrorMessage]);

  useEffect(() => {
    if (errorMessage) {
      throw new Error(errorMessage);
    }
  }, [errorMessage] );

  return <TenwebPaymentCreditCardStripe {...props} />;
};

const TenwebPaymentCreditCardStripe = (props) => {
  const { eventRegistration, emitResponse } = props;
  const { onPaymentSetup, onCheckoutSuccess, onCheckoutFail } = eventRegistration;

  useEffect( () => {
    window.twwp_checkout.init();

    onPaymentSetup( () => {
      return handlePaymentSetupAsync( props )
    } );

    onCheckoutFail( ( { processingResponse: { paymentStatus, paymentDetails } } ) => {
          window.twwp_checkout.$paymentNeeded = true;
          return true;
        }
    );
  }, [] );

  const handlePaymentSetupAsync = async ( props ) => {
    if ( !window.twwp_checkout.$paymentNeeded || 'tenweb_payments_stripe' !== props.activePaymentMethod ) {
      return true;
    }
    let intentData = null;
    let paymentClientSecret = null;
    let intentId = null;
    const cartHash = Cookies.get('woocommerce_cart_hash');
    try {
      // // Retrieve payment intent from session
      // intentData = await window.twwp_checkout.getPaymentIntentFromSession();
      // let paymentIntentAction = 'create';
      //
      // if (intentData.intentId) {
      //   paymentIntentAction = 'update';
      // }
      //
      // // Check if the cart hash matches; if not, update or create payment intent
      // if (cartHash !== intentData.cartHash) {
      //   intentData = await window.twwp_checkout.paymentIntentActions(paymentIntentAction);
      // }

      paymentClientSecret = window.twwp_checkout.$paymentClientSecret;
      intentId = window.twwp_checkout.$intentId;
      await window.twwp_checkout.paymentIntentActions('update', intentId);

      if (paymentClientSecret) {
        window.twwp_checkout.$checkout_form.remove('.twwp_intentId');
        window.twwp_checkout.$checkout_form.append(`<input class="twwp_intentId" type='hidden' name='intentId' value='${intentId}'>`);


        const name = jQuery('#billing-first_name').length && jQuery('#billing-last_name').length ?
            jQuery('#billing-first_name').val() + ' ' + jQuery('#billing-last_name').val() :
            (jQuery('#shipping-first_name').length && jQuery('#shipping-last_name').length ?
                jQuery('#shipping-first_name').val() + ' ' + jQuery('#shipping-last_name').val() : undefined);

        const postal_code = jQuery('#billing-postcode').length ?
            jQuery('#billing-postcode').val() :
            (jQuery('#shipping-postcode').length ? jQuery('#shipping-postcode').val() : undefined);

        const billing_country = jQuery('#billing-country').length ?
            jQuery('#billing-country').val() :
            (jQuery('#shipping-country').length ? jQuery('#shipping-country').val() : undefined);

        const billing_phone = jQuery('#billing-phone').length ?
            jQuery('#billing-phone').val() :
            (jQuery('#shipping-phone').length ? jQuery('#shipping-phone').val() : undefined);

        const billing_state = jQuery('#billing-state').length ?
            jQuery('#billing-state').val() :
            (jQuery('#shipping-state').length ? jQuery('#shipping-state').val() : undefined);

        const billing_email = jQuery('#email').length ? jQuery('#email').val() : undefined;

// Prepare billing details object
        const billingDetails = {};
        if (name) billingDetails.name = name;
        if (billing_email) billingDetails.email = billing_email;
        if (billing_phone) billingDetails.phone = billing_phone;
        if (billing_state || postal_code || billing_country) {
          billingDetails.address = {};
          if (billing_state) billingDetails.address.state = billing_state;
          if (postal_code) billingDetails.address.postal_code = postal_code;
          if (billing_country) billingDetails.address.country = billing_country;
        }
        let elements = window.twwp_checkout.$elements;
        const result = await window.twwp_checkout.stripe.confirmPayment( {
          elements,
          confirmParams: {
            payment_method_data: {
              billing_details: billingDetails
            }
          },
          redirect: 'if_required',
        } );
        if (result.error) {
          const failResponse = {
            type: emitResponse.responseTypes.FAIL,
            messageContext: emitResponse.noticeContexts.PAYMENTS,
            message: 'Payment failed: ' + result.error.message,
          };

          return failResponse;
        } else {
          window.twwp_checkout.$paymentNeeded = false;
          const successResponse = {
            type: emitResponse.responseTypes.SUCCESS,
            meta: {
              paymentMethodData: {
                'intentId':intentId,
              },
            },
          };

          return successResponse;
        }
      }
      else {
        // Payment intent is not found
        const failResponse = {
          type: emitResponse.responseTypes.FAIL,
          messageContext: emitResponse.noticeContexts.PAYMENTS,
          message: 'Payment failed: Payment intent not found.',
        };

        return failResponse;
      }
    } catch ( e ) {
      let err = [];
      for (let key in e) {
        if (e[key].errorMessages) {
          err.push(key + ' ' + e[key].errorMessages.join(', '));
        }
      }
      if (e.data && e.data.error) {
        err.push(e.data.message);
      }
      const failResponse = {
        type: emitResponse.responseTypes.FAIL,
        messageContext: emitResponse.noticeContexts.PAYMENTS,
        message: 'Payment failed: ' + err.join(', '),
      };

      return failResponse;
    }
  };

  const isTestMode = wc.wcSettings.getSetting( 'tenweb_payments_stripe_data' )[ 'test_mode' ];

  // TODO: this is the html returned by 'wc_print_notice'. find the js alternative to get rid of this.
  const testingInstructions = (
      <div className="wc-block-store-notice wc-block-components-notice-banner is-info">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"
             focusable="false">
          <path
              d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path>
        </svg>
        <div className="wc-block-components-notice-banner__content">
          <strong>Test mode:</strong> use the test VISA card 4242424242424242
          with any expiry date and CVC. Never provide your real card data when test mode is enabled.
        </div>
      </div>
  );

  return (
      <>
        { isTestMode=='yes' ? testingInstructions : '' }
        <div id="twwp-card-element"></div>
      </>
  );
};

const options = {
  name: 'tenweb_payments_stripe',
  content: <TenwebPaymentComponentStripe/>,
  edit: <TenwebPaymentComponentStripe/>,
  canMakePayment: () => true, // TODO: we can check if the PE is initialized here
  paymentMethodId: 'tenweb_payments_stripe',
  supports: {
    showSavedCards: false,
    showSaveOption: false,
    features: [
      'products',
      'refunds',
    ],
  },
  savedTokenComponent: null,
  label: <TenwebPaymentLabelStripe/>,
  ariaLabel: __( 'Credit card / Debit card', 'twwp' ),
};

registerPaymentMethod( options );