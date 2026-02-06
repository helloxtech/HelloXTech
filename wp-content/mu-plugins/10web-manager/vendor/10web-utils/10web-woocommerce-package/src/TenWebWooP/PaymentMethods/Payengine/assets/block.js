import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';

const { registerPaymentMethod } = window.wc.wcBlocksRegistry;

const loadPayengine = () =>
  new Promise( ( resolve ) => {
    try {
      resolve({});
    } catch ( error ) {
      // In order to avoid showing console error publicly to users,
      // we resolve instead of rejecting when there is an error.
      resolve({error});
    }
  } );

const tenwebPaymentPromise = loadPayengine();

const TenwebPaymentLabel = (props) => {
  const { PaymentMethodLabel } = props.components;

  const labelText = __('Credit card / Debit card', 'twwp');

  return <PaymentMethodLabel text={labelText} />;
};

const TenwebPaymentComponent = (props) => {
  const [errorMessage, setErrorMessage] = useState('');

  useEffect(() => {
    Promise.resolve(tenwebPaymentPromise).then(({error}) => {
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

  return <TenwebPaymentCreditCard {...props} />;
};

const TenwebPaymentCreditCard = (props) => {
  const { eventRegistration, emitResponse } = props;
  const { onPaymentSetup, onCheckoutSuccess, onCheckoutFail } = eventRegistration;

  useEffect( () => {
    window.twwp_checkout.init();

    onPaymentSetup( () => {
      return handlePaymentSetupAsync( props )
    } );

    onCheckoutSuccess( ( { processingResponse: { paymentStatus, paymentDetails } } ) => {
      return handleCheckoutSuccessAsync( paymentStatus, paymentDetails );
    } );

    onCheckoutFail( ( { processingResponse: { paymentStatus, paymentDetails } } ) => {
        // Received the 'success' response from 3DS pop-up. Trigger form resubmit to get the payment in backend.
        if ( window.twwp_checkout.$performing3ds && 'success' === window.twwp_checkout.$3dsresponse ) {
          // setTimeout is here to perform the click when the button is unblocked.
          setTimeout( () => {
            // Trigger the order button click to resubmit the form with 3ds result.
            jQuery( '.wc-block-components-checkout-place-order-button' ).trigger( 'click' );
          } );
          // Return error to suppress the default error message.
          const failResponse = {
            type: emitResponse.responseTypes.FAIL,
          };
          return failResponse;
        }
        window.twwp_checkout.$paymentNeeded = true;
        return true;
      }
    );
  }, [] );

  const handlePaymentSetupAsync = async ( props ) => {
    // This fires when the submit button click is triggered. Sends the 3ds result to payment processor.
    if ( 'success' === window.twwp_checkout.$3dsresponse ) {
      const twwp_payengine_3ds_result = window.twwp_checkout.$3dsresponse;
      const successResponse = {
        type: emitResponse.responseTypes.SUCCESS,
        meta: {
          paymentMethodData: {
            twwp_payengine_3ds_result,
          },
        },
      };

      window.twwp_checkout.$paymentNeeded = false;
      window.twwp_checkout.resetFields();

      return successResponse;
    }

    if ( !window.twwp_checkout.$paymentNeeded || 'tenweb_payments' !== props.activePaymentMethod ) {
      return true;
    }

    try {
      // Create a card and return success to perform the payment in backend.
      const cardObj = await window.twwp_checkout.$secureForm.createCard();

      if ( cardObj.token ) {
        // Card is successfully create. Pass the token and browser info to backend.
        const twwp_payengine_card_token = cardObj.token;
        const twwp_payengine_browser_info = await PayEngine.collectBrowserInfo();
        const successResponse = {
          type: emitResponse.responseTypes.SUCCESS,
          meta: {
            paymentMethodData: {
              twwp_payengine_card_token,
              twwp_payengine_browser_info,
            },
          },
        };

        window.twwp_checkout.$paymentNeeded = false;

        return successResponse;
      }
      else {
        let err = [];
        for (let key in cardObj) {
          if (cardObj[key].errorMessages) {
            err.push(key + ' ' + cardObj[key].errorMessages.join(', '));
          }
        }
        if (cardObj.data && cardObj.data.error) {
          err.push(e.data.message);
        }
        const failResponse = {
          type: emitResponse.responseTypes.FAIL,
          messageContext: emitResponse.noticeContexts.PAYMENTS,
          message: 'Payment failed: ' + err.join(', '),
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

  const handleCheckoutSuccessAsync = async ( paymentStatus, paymentDetails ) => {
    // If payment requires 3DS verification. This can success without a pop-up opening.
    if ( paymentDetails.twwp_3ds_action_required ) {
      // Wait for the 3ds result if
      return new Promise( ( resolve, reject ) => {
        window.twwp_checkout.$performing3ds = true;
        PayEngine.perform3DSFlow(
          paymentDetails.twwp_3ds_data,
          function ( response ) {
            window.twwp_checkout.$3dsresponse = response.success ? 'success' : 'fail';

            if ( response.success ) {
              resolve();
            }
            else {
              reject();
            }
          }
        );
      } ).then( () => {
        // Return error to handle it and pass the 'success' response to backend.
        window.twwp_checkout.$paymentNeeded = false;
        window.twwp_checkout.$errorMessage = null;
        // Despite the 3ds result being 'success' we return an error response,
        // so we can trigger form submission to pass the value to payment processor.
        const failResponse = {
          type: emitResponse.responseTypes.FAIL,
          retry: true,
        };

        return failResponse;
      } ).catch( () => {
        window.twwp_checkout.$paymentNeeded = true;
        window.twwp_checkout.$errorMessage = '3DS verification failed';
        const failResponse = {
          type: emitResponse.responseTypes.FAIL,
          messageContext: emitResponse.noticeContexts.PAYMENTS,
          message: '3DS verification failed',
        };

        return failResponse;
      } );
    }
    return true;
  };

  // TODO: check why the event does not work
  const handleSubmit = ( event ) => {
    console.log( 'submitted' );
    // event.preventDefault();
    return false;
  };

  const isTestMode = wc.wcSettings.getSetting( 'tenweb_payments_data' )[ 'test_mode' ];

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
      { isTestMode ? testingInstructions : '' }
      <form id="twwp-card-form" onSubmit={ handleSubmit }>
        <div className="form-field" id="cc-name"></div>
        <div className="form-field" id="cc-number"></div>
        <div className="form-field" id="cc-expiration-date"></div>
        <div className="form-field" id="cc-cvc"></div>
      </form>
    </>
  );
};

const options = {
  name: 'tenweb_payments',
  content: <TenwebPaymentComponent/>,
  edit: <TenwebPaymentComponent/>,
  canMakePayment: () => true, // TODO: we can check if the PE is initialized here
  paymentMethodId: 'tenweb_payments',
  supports: {
    showSavedCards: false,
    showSaveOption: false,
    features: [
      'products',
      'refunds',
    ],
  },
  savedTokenComponent: null,
  label: <TenwebPaymentLabel/>,
  ariaLabel: __( 'Credit card', 'twwp' ),
};

registerPaymentMethod( options );