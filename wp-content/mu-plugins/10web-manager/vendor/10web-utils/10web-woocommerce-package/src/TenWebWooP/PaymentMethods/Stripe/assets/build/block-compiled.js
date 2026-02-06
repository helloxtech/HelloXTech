/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = window["React"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!**************************************************************!*\
  !*** ./src/TenWebWooP/PaymentMethods/Stripe/assets/block.js ***!
  \**************************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__);



const {
  registerPaymentMethod
} = window.wc.wcBlocksRegistry;
const loadStripe = () => new Promise(resolve => {
  try {
    resolve({});
  } catch (error) {
    // In order to avoid showing console error publicly to users,
    // we resolve instead of rejecting when there is an error.
    resolve({
      error
    });
  }
});
const tenwebPaymentPromiseStripe = loadStripe();
const TenwebPaymentLabelStripe = props => {
  const {
    PaymentMethodLabel
  } = props.components;
  const labelText = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Credit card / Debit card', 'twwp');
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(PaymentMethodLabel, {
    text: labelText
  });
};
const TenwebPaymentComponentStripe = props => {
  const [errorMessage, setErrorMessage] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)('');
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    Promise.resolve(tenwebPaymentPromiseStripe).then(({
      error
    }) => {
      if (error) {
        setErrorMessage(error.message);
      }
    });
  }, [setErrorMessage]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    if (errorMessage) {
      throw new Error(errorMessage);
    }
  }, [errorMessage]);
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(TenwebPaymentCreditCardStripe, {
    ...props
  });
};
const TenwebPaymentCreditCardStripe = props => {
  const {
    eventRegistration,
    emitResponse
  } = props;
  const {
    onPaymentSetup,
    onCheckoutSuccess,
    onCheckoutFail
  } = eventRegistration;
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    window.twwp_checkout.init();
    onPaymentSetup(() => {
      return handlePaymentSetupAsync(props);
    });
    onCheckoutFail(({
      processingResponse: {
        paymentStatus,
        paymentDetails
      }
    }) => {
      window.twwp_checkout.$paymentNeeded = true;
      return true;
    });
  }, []);
  const handlePaymentSetupAsync = async props => {
    if (!window.twwp_checkout.$paymentNeeded || 'tenweb_payments_stripe' !== props.activePaymentMethod) {
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
        const name = jQuery('#billing-first_name').length && jQuery('#billing-last_name').length ? jQuery('#billing-first_name').val() + ' ' + jQuery('#billing-last_name').val() : jQuery('#shipping-first_name').length && jQuery('#shipping-last_name').length ? jQuery('#shipping-first_name').val() + ' ' + jQuery('#shipping-last_name').val() : undefined;
        const postal_code = jQuery('#billing-postcode').length ? jQuery('#billing-postcode').val() : jQuery('#shipping-postcode').length ? jQuery('#shipping-postcode').val() : undefined;
        const billing_country = jQuery('#billing-country').length ? jQuery('#billing-country').val() : jQuery('#shipping-country').length ? jQuery('#shipping-country').val() : undefined;
        const billing_phone = jQuery('#billing-phone').length ? jQuery('#billing-phone').val() : jQuery('#shipping-phone').length ? jQuery('#shipping-phone').val() : undefined;
        const billing_state = jQuery('#billing-state').length ? jQuery('#billing-state').val() : jQuery('#shipping-state').length ? jQuery('#shipping-state').val() : undefined;
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
        const result = await window.twwp_checkout.stripe.confirmPayment({
          elements,
          confirmParams: {
            payment_method_data: {
              billing_details: billingDetails
            }
          },
          redirect: 'if_required'
        });
        if (result.error) {
          const failResponse = {
            type: emitResponse.responseTypes.FAIL,
            messageContext: emitResponse.noticeContexts.PAYMENTS,
            message: 'Payment failed: ' + result.error.message
          };
          return failResponse;
        } else {
          window.twwp_checkout.$paymentNeeded = false;
          const successResponse = {
            type: emitResponse.responseTypes.SUCCESS,
            meta: {
              paymentMethodData: {
                'intentId': intentId
              }
            }
          };
          return successResponse;
        }
      } else {
        // Payment intent is not found
        const failResponse = {
          type: emitResponse.responseTypes.FAIL,
          messageContext: emitResponse.noticeContexts.PAYMENTS,
          message: 'Payment failed: Payment intent not found.'
        };
        return failResponse;
      }
    } catch (e) {
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
        message: 'Payment failed: ' + err.join(', ')
      };
      return failResponse;
    }
  };
  const isTestMode = wc.wcSettings.getSetting('tenweb_payments_stripe_data')['test_mode'];

  // TODO: this is the html returned by 'wc_print_notice'. find the js alternative to get rid of this.
  const testingInstructions = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wc-block-store-notice wc-block-components-notice-banner is-info"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    xmlns: "http://www.w3.org/2000/svg",
    viewBox: "0 0 24 24",
    width: "24",
    height: "24",
    "aria-hidden": "true",
    focusable: "false"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    d: "M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wc-block-components-notice-banner__content"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("strong", null, "Test mode:"), " use the test VISA card 4242424242424242 with any expiry date and CVC. Never provide your real card data when test mode is enabled."));
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, isTestMode == 'yes' ? testingInstructions : '', (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    id: "twwp-card-element"
  }));
};
const options = {
  name: 'tenweb_payments_stripe',
  content: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(TenwebPaymentComponentStripe, null),
  edit: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(TenwebPaymentComponentStripe, null),
  canMakePayment: () => true,
  // TODO: we can check if the PE is initialized here
  paymentMethodId: 'tenweb_payments_stripe',
  supports: {
    showSavedCards: false,
    showSaveOption: false,
    features: ['products', 'refunds']
  },
  savedTokenComponent: null,
  label: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(TenwebPaymentLabelStripe, null),
  ariaLabel: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Credit card / Debit card', 'twwp')
};
registerPaymentMethod(options);
})();

/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,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Q3pEakYsb0RBQUEsaUJBQVEsWUFBa0IsQ0FBQyx1SUFFeEIsQ0FDRixDQUNSO0VBRUQsT0FDSUEsb0RBQUEsQ0FBQXdGLDJDQUFBLFFBQ0lYLFVBQVUsSUFBRSxLQUFLLEdBQUdHLG1CQUFtQixHQUFHLEVBQUUsRUFDOUNoRixvREFBQTtJQUFLeUYsRUFBRSxFQUFDO0VBQW1CLENBQU0sQ0FDakMsQ0FBQztBQUVULENBQUM7QUFFRCxNQUFNQyxPQUFPLEdBQUc7RUFDZHhELElBQUksRUFBRSx3QkFBd0I7RUFDOUJ5RCxPQUFPLEVBQUUzRixvREFBQSxDQUFDRSw0QkFBNEIsTUFBQyxDQUFDO0VBQ3hDMEYsSUFBSSxFQUFFNUYsb0RBQUEsQ0FBQ0UsNEJBQTRCLE1BQUMsQ0FBQztFQUNyQzJGLGNBQWMsRUFBRUEsQ0FBQSxLQUFNLElBQUk7RUFBRTtFQUM1QkMsZUFBZSxFQUFFLHdCQUF3QjtFQUN6Q0MsUUFBUSxFQUFFO0lBQ1JDLGNBQWMsRUFBRSxLQUFLO0lBQ3JCQyxjQUFjLEVBQUUsS0FBSztJQUNyQkMsUUFBUSxFQUFFLENBQ1IsVUFBVSxFQUNWLFNBQVM7RUFFYixDQUFDO0VBQ0RDLG1CQUFtQixFQUFFLElBQUk7RUFDekJDLEtBQUssRUFBRXBHLG9EQUFBLENBQUNMLHdCQUF3QixNQUFDLENBQUM7RUFDbEMwRyxTQUFTLEVBQUV0SCxtREFBRSxDQUFFLDBCQUEwQixFQUFFLE1BQU87QUFDcEQsQ0FBQztBQUVERyxxQkFBcUIsQ0FBRXdHLE9BQVEsQ0FBQyxDIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vMTB3ZWItd29vY29tbWVyY2UtcGFja2FnZS9leHRlcm5hbCB3aW5kb3cgXCJSZWFjdFwiIiwid2VicGFjazovLzEwd2ViLXdvb2NvbW1lcmNlLXBhY2thZ2UvZXh0ZXJuYWwgd2luZG93IFtcIndwXCIsXCJlbGVtZW50XCJdIiwid2VicGFjazovLzEwd2ViLXdvb2NvbW1lcmNlLXBhY2thZ2UvZXh0ZXJuYWwgd2luZG93IFtcIndwXCIsXCJpMThuXCJdIiwid2VicGFjazovLzEwd2ViLXdvb2NvbW1lcmNlLXBhY2thZ2Uvd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vMTB3ZWItd29vY29tbWVyY2UtcGFja2FnZS93ZWJwYWNrL3J1bnRpbWUvY29tcGF0IGdldCBkZWZhdWx0IGV4cG9ydCIsIndlYnBhY2s6Ly8xMHdlYi13b29jb21tZXJjZS1wYWNrYWdlL3dlYnBhY2svcnVudGltZS9kZWZpbmUgcHJvcGVydHkgZ2V0dGVycyIsIndlYnBhY2s6Ly8xMHdlYi13b29jb21tZXJjZS1wYWNrYWdlL3dlYnBhY2svcnVudGltZS9oYXNPd25Qcm9wZXJ0eSBzaG9ydGhhbmQiLCJ3ZWJwYWNrOi8vMTB3ZWItd29vY29tbWVyY2UtcGFja2FnZS93ZWJwYWNrL3J1bnRpbWUvbWFrZSBuYW1lc3BhY2Ugb2JqZWN0Iiwid2VicGFjazovLzEwd2ViLXdvb2NvbW1lcmNlLXBhY2thZ2UvLi9zcmMvVGVuV2ViV29vUC9QYXltZW50TWV0aG9kcy9TdHJpcGUvYXNzZXRzL2Jsb2NrLmpzIl0sInNvdXJjZXNDb250ZW50IjpbIm1vZHVsZS5leHBvcnRzID0gd2luZG93W1wiUmVhY3RcIl07IiwibW9kdWxlLmV4cG9ydHMgPSB3aW5kb3dbXCJ3cFwiXVtcImVsZW1lbnRcIl07IiwibW9kdWxlLmV4cG9ydHMgPSB3aW5kb3dbXCJ3cFwiXVtcImkxOG5cIl07IiwiLy8gVGhlIG1vZHVsZSBjYWNoZVxudmFyIF9fd2VicGFja19tb2R1bGVfY2FjaGVfXyA9IHt9O1xuXG4vLyBUaGUgcmVxdWlyZSBmdW5jdGlvblxuZnVuY3Rpb24gX193ZWJwYWNrX3JlcXVpcmVfXyhtb2R1bGVJZCkge1xuXHQvLyBDaGVjayBpZiBtb2R1bGUgaXMgaW4gY2FjaGVcblx0dmFyIGNhY2hlZE1vZHVsZSA9IF9fd2VicGFja19tb2R1bGVfY2FjaGVfX1ttb2R1bGVJZF07XG5cdGlmIChjYWNoZWRNb2R1bGUgIT09IHVuZGVmaW5lZCkge1xuXHRcdHJldHVybiBjYWNoZWRNb2R1bGUuZXhwb3J0cztcblx0fVxuXHQvLyBDcmVhdGUgYSBuZXcgbW9kdWxlIChhbmQgcHV0IGl0IGludG8gdGhlIGNhY2hlKVxuXHR2YXIgbW9kdWxlID0gX193ZWJwYWNrX21vZHVsZV9jYWNoZV9fW21vZHVsZUlkXSA9IHtcblx0XHQvLyBubyBtb2R1bGUuaWQgbmVlZGVkXG5cdFx0Ly8gbm8gbW9kdWxlLmxvYWRlZCBuZWVkZWRcblx0XHRleHBvcnRzOiB7fVxuXHR9O1xuXG5cdC8vIEV4ZWN1dGUgdGhlIG1vZHVsZSBmdW5jdGlvblxuXHRfX3dlYnBhY2tfbW9kdWxlc19fW21vZHVsZUlkXShtb2R1bGUsIG1vZHVsZS5leHBvcnRzLCBfX3dlYnBhY2tfcmVxdWlyZV9fKTtcblxuXHQvLyBSZXR1cm4gdGhlIGV4cG9ydHMgb2YgdGhlIG1vZHVsZVxuXHRyZXR1cm4gbW9kdWxlLmV4cG9ydHM7XG59XG5cbiIsIi8vIGdldERlZmF1bHRFeHBvcnQgZnVuY3Rpb24gZm9yIGNvbXBhdGliaWxpdHkgd2l0aCBub24taGFybW9ueSBtb2R1bGVzXG5fX3dlYnBhY2tfcmVxdWlyZV9fLm4gPSAobW9kdWxlKSA9PiB7XG5cdHZhciBnZXR0ZXIgPSBtb2R1bGUgJiYgbW9kdWxlLl9fZXNNb2R1bGUgP1xuXHRcdCgpID0+IChtb2R1bGVbJ2RlZmF1bHQnXSkgOlxuXHRcdCgpID0+IChtb2R1bGUpO1xuXHRfX3dlYnBhY2tfcmVxdWlyZV9fLmQoZ2V0dGVyLCB7IGE6IGdldHRlciB9KTtcblx0cmV0dXJuIGdldHRlcjtcbn07IiwiLy8gZGVmaW5lIGdldHRlciBmdW5jdGlvbnMgZm9yIGhhcm1vbnkgZXhwb3J0c1xuX193ZWJwYWNrX3JlcXVpcmVfXy5kID0gKGV4cG9ydHMsIGRlZmluaXRpb24pID0+IHtcblx0Zm9yKHZhciBrZXkgaW4gZGVmaW5pdGlvbikge1xuXHRcdGlmKF9fd2VicGFja19yZXF1aXJlX18ubyhkZWZpbml0aW9uLCBrZXkpICYmICFfX3dlYnBhY2tfcmVxdWlyZV9fLm8oZXhwb3J0cywga2V5KSkge1xuXHRcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIGtleSwgeyBlbnVtZXJhYmxlOiB0cnVlLCBnZXQ6IGRlZmluaXRpb25ba2V5XSB9KTtcblx0XHR9XG5cdH1cbn07IiwiX193ZWJwYWNrX3JlcXVpcmVfXy5vID0gKG9iaiwgcHJvcCkgPT4gKE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbChvYmosIHByb3ApKSIsIi8vIGRlZmluZSBfX2VzTW9kdWxlIG9uIGV4cG9ydHNcbl9fd2VicGFja19yZXF1aXJlX18uciA9IChleHBvcnRzKSA9PiB7XG5cdGlmKHR5cGVvZiBTeW1ib2wgIT09ICd1bmRlZmluZWQnICYmIFN5bWJvbC50b1N0cmluZ1RhZykge1xuXHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCBTeW1ib2wudG9TdHJpbmdUYWcsIHsgdmFsdWU6ICdNb2R1bGUnIH0pO1xuXHR9XG5cdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCAnX19lc01vZHVsZScsIHsgdmFsdWU6IHRydWUgfSk7XG59OyIsImltcG9ydCB7IF9fIH0gZnJvbSAnQHdvcmRwcmVzcy9pMThuJztcbmltcG9ydCB7IHVzZUVmZmVjdCwgdXNlU3RhdGUgfSBmcm9tICdAd29yZHByZXNzL2VsZW1lbnQnO1xuXG5jb25zdCB7IHJlZ2lzdGVyUGF5bWVudE1ldGhvZCB9ID0gd2luZG93LndjLndjQmxvY2tzUmVnaXN0cnk7XG5cbmNvbnN0IGxvYWRTdHJpcGUgPSAoKSA9PlxuICAgIG5ldyBQcm9taXNlKCAoIHJlc29sdmUgKSA9PiB7XG4gICAgICB0cnkge1xuICAgICAgICByZXNvbHZlKHt9KTtcbiAgICAgIH0gY2F0Y2ggKCBlcnJvciApIHtcbiAgICAgICAgLy8gSW4gb3JkZXIgdG8gYXZvaWQgc2hvd2luZyBjb25zb2xlIGVycm9yIHB1YmxpY2x5IHRvIHVzZXJzLFxuICAgICAgICAvLyB3ZSByZXNvbHZlIGluc3RlYWQgb2YgcmVqZWN0aW5nIHdoZW4gdGhlcmUgaXMgYW4gZXJyb3IuXG4gICAgICAgIHJlc29sdmUoe2Vycm9yfSk7XG4gICAgICB9XG4gICAgfSApO1xuXG5jb25zdCB0ZW53ZWJQYXltZW50UHJvbWlzZVN0cmlwZSA9IGxvYWRTdHJpcGUoKTtcblxuY29uc3QgVGVud2ViUGF5bWVudExhYmVsU3RyaXBlID0gKHByb3BzKSA9PiB7XG4gIGNvbnN0IHsgUGF5bWVudE1ldGhvZExhYmVsIH0gPSBwcm9wcy5jb21wb25lbnRzO1xuXG4gIGNvbnN0IGxhYmVsVGV4dCA9IF9fKCdDcmVkaXQgY2FyZCAvIERlYml0IGNhcmQnLCAndHd3cCcpO1xuXG4gIHJldHVybiA8UGF5bWVudE1ldGhvZExhYmVsIHRleHQ9e2xhYmVsVGV4dH0gLz47XG59O1xuXG5jb25zdCBUZW53ZWJQYXltZW50Q29tcG9uZW50U3RyaXBlID0gKHByb3BzKSA9PiB7XG5cbiAgY29uc3QgW2Vycm9yTWVzc2FnZSwgc2V0RXJyb3JNZXNzYWdlXSA9IHVzZVN0YXRlKCcnKTtcblxuICB1c2VFZmZlY3QoKCkgPT4ge1xuICAgIFByb21pc2UucmVzb2x2ZSh0ZW53ZWJQYXltZW50UHJvbWlzZVN0cmlwZSkudGhlbigoe2Vycm9yfSkgPT4ge1xuICAgICAgaWYgKGVycm9yKSB7XG4gICAgICAgIHNldEVycm9yTWVzc2FnZShlcnJvci5tZXNzYWdlKTtcbiAgICAgIH1cbiAgICB9KTtcbiAgfSwgW3NldEVycm9yTWVzc2FnZV0pO1xuXG4gIHVzZUVmZmVjdCgoKSA9PiB7XG4gICAgaWYgKGVycm9yTWVzc2FnZSkge1xuICAgICAgdGhyb3cgbmV3IEVycm9yKGVycm9yTWVzc2FnZSk7XG4gICAgfVxuICB9LCBbZXJyb3JNZXNzYWdlXSApO1xuXG4gIHJldHVybiA8VGVud2ViUGF5bWVudENyZWRpdENhcmRTdHJpcGUgey4uLnByb3BzfSAvPjtcbn07XG5cbmNvbnN0IFRlbndlYlBheW1lbnRDcmVkaXRDYXJkU3RyaXBlID0gKHByb3BzKSA9PiB7XG4gIGNvbnN0IHsgZXZlbnRSZWdpc3RyYXRpb24sIGVtaXRSZXNwb25zZSB9ID0gcHJvcHM7XG4gIGNvbnN0IHsgb25QYXltZW50U2V0dXAsIG9uQ2hlY2tvdXRTdWNjZXNzLCBvbkNoZWNrb3V0RmFpbCB9ID0gZXZlbnRSZWdpc3RyYXRpb247XG5cbiAgdXNlRWZmZWN0KCAoKSA9PiB7XG4gICAgd2luZG93LnR3d3BfY2hlY2tvdXQuaW5pdCgpO1xuXG4gICAgb25QYXltZW50U2V0dXAoICgpID0+IHtcbiAgICAgIHJldHVybiBoYW5kbGVQYXltZW50U2V0dXBBc3luYyggcHJvcHMgKVxuICAgIH0gKTtcblxuICAgIG9uQ2hlY2tvdXRGYWlsKCAoIHsgcHJvY2Vzc2luZ1Jlc3BvbnNlOiB7IHBheW1lbnRTdGF0dXMsIHBheW1lbnREZXRhaWxzIH0gfSApID0+IHtcbiAgICAgICAgICB3aW5kb3cudHd3cF9jaGVja291dC4kcGF5bWVudE5lZWRlZCA9IHRydWU7XG4gICAgICAgICAgcmV0dXJuIHRydWU7XG4gICAgICAgIH1cbiAgICApO1xuICB9LCBbXSApO1xuXG4gIGNvbnN0IGhhbmRsZVBheW1lbnRTZXR1cEFzeW5jID0gYXN5bmMgKCBwcm9wcyApID0+IHtcbiAgICBpZiAoICF3aW5kb3cudHd3cF9jaGVja291dC4kcGF5bWVudE5lZWRlZCB8fCAndGVud2ViX3BheW1lbnRzX3N0cmlwZScgIT09IHByb3BzLmFjdGl2ZVBheW1lbnRNZXRob2QgKSB7XG4gICAgICByZXR1cm4gdHJ1ZTtcbiAgICB9XG4gICAgbGV0IGludGVudERhdGEgPSBudWxsO1xuICAgIGxldCBwYXltZW50Q2xpZW50U2VjcmV0ID0gbnVsbDtcbiAgICBsZXQgaW50ZW50SWQgPSBudWxsO1xuICAgIGNvbnN0IGNhcnRIYXNoID0gQ29va2llcy5nZXQoJ3dvb2NvbW1lcmNlX2NhcnRfaGFzaCcpO1xuICAgIHRyeSB7XG4gICAgICAvLyAvLyBSZXRyaWV2ZSBwYXltZW50IGludGVudCBmcm9tIHNlc3Npb25cbiAgICAgIC8vIGludGVudERhdGEgPSBhd2FpdCB3aW5kb3cudHd3cF9jaGVja291dC5nZXRQYXltZW50SW50ZW50RnJvbVNlc3Npb24oKTtcbiAgICAgIC8vIGxldCBwYXltZW50SW50ZW50QWN0aW9uID0gJ2NyZWF0ZSc7XG4gICAgICAvL1xuICAgICAgLy8gaWYgKGludGVudERhdGEuaW50ZW50SWQpIHtcbiAgICAgIC8vICAgcGF5bWVudEludGVudEFjdGlvbiA9ICd1cGRhdGUnO1xuICAgICAgLy8gfVxuICAgICAgLy9cbiAgICAgIC8vIC8vIENoZWNrIGlmIHRoZSBjYXJ0IGhhc2ggbWF0Y2hlczsgaWYgbm90LCB1cGRhdGUgb3IgY3JlYXRlIHBheW1lbnQgaW50ZW50XG4gICAgICAvLyBpZiAoY2FydEhhc2ggIT09IGludGVudERhdGEuY2FydEhhc2gpIHtcbiAgICAgIC8vICAgaW50ZW50RGF0YSA9IGF3YWl0IHdpbmRvdy50d3dwX2NoZWNrb3V0LnBheW1lbnRJbnRlbnRBY3Rpb25zKHBheW1lbnRJbnRlbnRBY3Rpb24pO1xuICAgICAgLy8gfVxuXG4gICAgICBwYXltZW50Q2xpZW50U2VjcmV0ID0gd2luZG93LnR3d3BfY2hlY2tvdXQuJHBheW1lbnRDbGllbnRTZWNyZXQ7XG4gICAgICBpbnRlbnRJZCA9IHdpbmRvdy50d3dwX2NoZWNrb3V0LiRpbnRlbnRJZDtcbiAgICAgIGF3YWl0IHdpbmRvdy50d3dwX2NoZWNrb3V0LnBheW1lbnRJbnRlbnRBY3Rpb25zKCd1cGRhdGUnLCBpbnRlbnRJZCk7XG5cbiAgICAgIGlmIChwYXltZW50Q2xpZW50U2VjcmV0KSB7XG4gICAgICAgIHdpbmRvdy50d3dwX2NoZWNrb3V0LiRjaGVja291dF9mb3JtLnJlbW92ZSgnLnR3d3BfaW50ZW50SWQnKTtcbiAgICAgICAgd2luZG93LnR3d3BfY2hlY2tvdXQuJGNoZWNrb3V0X2Zvcm0uYXBwZW5kKGA8aW5wdXQgY2xhc3M9XCJ0d3dwX2ludGVudElkXCIgdHlwZT0naGlkZGVuJyBuYW1lPSdpbnRlbnRJZCcgdmFsdWU9JyR7aW50ZW50SWR9Jz5gKTtcblxuXG4gICAgICAgIGNvbnN0IG5hbWUgPSBqUXVlcnkoJyNiaWxsaW5nLWZpcnN0X25hbWUnKS5sZW5ndGggJiYgalF1ZXJ5KCcjYmlsbGluZy1sYXN0X25hbWUnKS5sZW5ndGggP1xuICAgICAgICAgICAgalF1ZXJ5KCcjYmlsbGluZy1maXJzdF9uYW1lJykudmFsKCkgKyAnICcgKyBqUXVlcnkoJyNiaWxsaW5nLWxhc3RfbmFtZScpLnZhbCgpIDpcbiAgICAgICAgICAgIChqUXVlcnkoJyNzaGlwcGluZy1maXJzdF9uYW1lJykubGVuZ3RoICYmIGpRdWVyeSgnI3NoaXBwaW5nLWxhc3RfbmFtZScpLmxlbmd0aCA/XG4gICAgICAgICAgICAgICAgalF1ZXJ5KCcjc2hpcHBpbmctZmlyc3RfbmFtZScpLnZhbCgpICsgJyAnICsgalF1ZXJ5KCcjc2hpcHBpbmctbGFzdF9uYW1lJykudmFsKCkgOiB1bmRlZmluZWQpO1xuXG4gICAgICAgIGNvbnN0IHBvc3RhbF9jb2RlID0galF1ZXJ5KCcjYmlsbGluZy1wb3N0Y29kZScpLmxlbmd0aCA/XG4gICAgICAgICAgICBqUXVlcnkoJyNiaWxsaW5nLXBvc3Rjb2RlJykudmFsKCkgOlxuICAgICAgICAgICAgKGpRdWVyeSgnI3NoaXBwaW5nLXBvc3Rjb2RlJykubGVuZ3RoID8galF1ZXJ5KCcjc2hpcHBpbmctcG9zdGNvZGUnKS52YWwoKSA6IHVuZGVmaW5lZCk7XG5cbiAgICAgICAgY29uc3QgYmlsbGluZ19jb3VudHJ5ID0galF1ZXJ5KCcjYmlsbGluZy1jb3VudHJ5JykubGVuZ3RoID9cbiAgICAgICAgICAgIGpRdWVyeSgnI2JpbGxpbmctY291bnRyeScpLnZhbCgpIDpcbiAgICAgICAgICAgIChqUXVlcnkoJyNzaGlwcGluZy1jb3VudHJ5JykubGVuZ3RoID8galF1ZXJ5KCcjc2hpcHBpbmctY291bnRyeScpLnZhbCgpIDogdW5kZWZpbmVkKTtcblxuICAgICAgICBjb25zdCBiaWxsaW5nX3Bob25lID0galF1ZXJ5KCcjYmlsbGluZy1waG9uZScpLmxlbmd0aCA/XG4gICAgICAgICAgICBqUXVlcnkoJyNiaWxsaW5nLXBob25lJykudmFsKCkgOlxuICAgICAgICAgICAgKGpRdWVyeSgnI3NoaXBwaW5nLXBob25lJykubGVuZ3RoID8galF1ZXJ5KCcjc2hpcHBpbmctcGhvbmUnKS52YWwoKSA6IHVuZGVmaW5lZCk7XG5cbiAgICAgICAgY29uc3QgYmlsbGluZ19zdGF0ZSA9IGpRdWVyeSgnI2JpbGxpbmctc3RhdGUnKS5sZW5ndGggP1xuICAgICAgICAgICAgalF1ZXJ5KCcjYmlsbGluZy1zdGF0ZScpLnZhbCgpIDpcbiAgICAgICAgICAgIChqUXVlcnkoJyNzaGlwcGluZy1zdGF0ZScpLmxlbmd0aCA/IGpRdWVyeSgnI3NoaXBwaW5nLXN0YXRlJykudmFsKCkgOiB1bmRlZmluZWQpO1xuXG4gICAgICAgIGNvbnN0IGJpbGxpbmdfZW1haWwgPSBqUXVlcnkoJyNlbWFpbCcpLmxlbmd0aCA/IGpRdWVyeSgnI2VtYWlsJykudmFsKCkgOiB1bmRlZmluZWQ7XG5cbi8vIFByZXBhcmUgYmlsbGluZyBkZXRhaWxzIG9iamVjdFxuICAgICAgICBjb25zdCBiaWxsaW5nRGV0YWlscyA9IHt9O1xuICAgICAgICBpZiAobmFtZSkgYmlsbGluZ0RldGFpbHMubmFtZSA9IG5hbWU7XG4gICAgICAgIGlmIChiaWxsaW5nX2VtYWlsKSBiaWxsaW5nRGV0YWlscy5lbWFpbCA9IGJpbGxpbmdfZW1haWw7XG4gICAgICAgIGlmIChiaWxsaW5nX3Bob25lKSBiaWxsaW5nRGV0YWlscy5waG9uZSA9IGJpbGxpbmdfcGhvbmU7XG4gICAgICAgIGlmIChiaWxsaW5nX3N0YXRlIHx8IHBvc3RhbF9jb2RlIHx8IGJpbGxpbmdfY291bnRyeSkge1xuICAgICAgICAgIGJpbGxpbmdEZXRhaWxzLmFkZHJlc3MgPSB7fTtcbiAgICAgICAgICBpZiAoYmlsbGluZ19zdGF0ZSkgYmlsbGluZ0RldGFpbHMuYWRkcmVzcy5zdGF0ZSA9IGJpbGxpbmdfc3RhdGU7XG4gICAgICAgICAgaWYgKHBvc3RhbF9jb2RlKSBiaWxsaW5nRGV0YWlscy5hZGRyZXNzLnBvc3RhbF9jb2RlID0gcG9zdGFsX2NvZGU7XG4gICAgICAgICAgaWYgKGJpbGxpbmdfY291bnRyeSkgYmlsbGluZ0RldGFpbHMuYWRkcmVzcy5jb3VudHJ5ID0gYmlsbGluZ19jb3VudHJ5O1xuICAgICAgICB9XG4gICAgICAgIGxldCBlbGVtZW50cyA9IHdpbmRvdy50d3dwX2NoZWNrb3V0LiRlbGVtZW50cztcbiAgICAgICAgY29uc3QgcmVzdWx0ID0gYXdhaXQgd2luZG93LnR3d3BfY2hlY2tvdXQuc3RyaXBlLmNvbmZpcm1QYXltZW50KCB7XG4gICAgICAgICAgZWxlbWVudHMsXG4gICAgICAgICAgY29uZmlybVBhcmFtczoge1xuICAgICAgICAgICAgcGF5bWVudF9tZXRob2RfZGF0YToge1xuICAgICAgICAgICAgICBiaWxsaW5nX2RldGFpbHM6IGJpbGxpbmdEZXRhaWxzXG4gICAgICAgICAgICB9XG4gICAgICAgICAgfSxcbiAgICAgICAgICByZWRpcmVjdDogJ2lmX3JlcXVpcmVkJyxcbiAgICAgICAgfSApO1xuICAgICAgICBpZiAocmVzdWx0LmVycm9yKSB7XG4gICAgICAgICAgY29uc3QgZmFpbFJlc3BvbnNlID0ge1xuICAgICAgICAgICAgdHlwZTogZW1pdFJlc3BvbnNlLnJlc3BvbnNlVHlwZXMuRkFJTCxcbiAgICAgICAgICAgIG1lc3NhZ2VDb250ZXh0OiBlbWl0UmVzcG9uc2Uubm90aWNlQ29udGV4dHMuUEFZTUVOVFMsXG4gICAgICAgICAgICBtZXNzYWdlOiAnUGF5bWVudCBmYWlsZWQ6ICcgKyByZXN1bHQuZXJyb3IubWVzc2FnZSxcbiAgICAgICAgICB9O1xuXG4gICAgICAgICAgcmV0dXJuIGZhaWxSZXNwb25zZTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICB3aW5kb3cudHd3cF9jaGVja291dC4kcGF5bWVudE5lZWRlZCA9IGZhbHNlO1xuICAgICAgICAgIGNvbnN0IHN1Y2Nlc3NSZXNwb25zZSA9IHtcbiAgICAgICAgICAgIHR5cGU6IGVtaXRSZXNwb25zZS5yZXNwb25zZVR5cGVzLlNVQ0NFU1MsXG4gICAgICAgICAgICBtZXRhOiB7XG4gICAgICAgICAgICAgIHBheW1lbnRNZXRob2REYXRhOiB7XG4gICAgICAgICAgICAgICAgJ2ludGVudElkJzppbnRlbnRJZCxcbiAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgIH0sXG4gICAgICAgICAgfTtcblxuICAgICAgICAgIHJldHVybiBzdWNjZXNzUmVzcG9uc2U7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICAgIGVsc2Uge1xuICAgICAgICAvLyBQYXltZW50IGludGVudCBpcyBub3QgZm91bmRcbiAgICAgICAgY29uc3QgZmFpbFJlc3BvbnNlID0ge1xuICAgICAgICAgIHR5cGU6IGVtaXRSZXNwb25zZS5yZXNwb25zZVR5cGVzLkZBSUwsXG4gICAgICAgICAgbWVzc2FnZUNvbnRleHQ6IGVtaXRSZXNwb25zZS5ub3RpY2VDb250ZXh0cy5QQVlNRU5UUyxcbiAgICAgICAgICBtZXNzYWdlOiAnUGF5bWVudCBmYWlsZWQ6IFBheW1lbnQgaW50ZW50IG5vdCBmb3VuZC4nLFxuICAgICAgICB9O1xuXG4gICAgICAgIHJldHVybiBmYWlsUmVzcG9uc2U7XG4gICAgICB9XG4gICAgfSBjYXRjaCAoIGUgKSB7XG4gICAgICBsZXQgZXJyID0gW107XG4gICAgICBmb3IgKGxldCBrZXkgaW4gZSkge1xuICAgICAgICBpZiAoZVtrZXldLmVycm9yTWVzc2FnZXMpIHtcbiAgICAgICAgICBlcnIucHVzaChrZXkgKyAnICcgKyBlW2tleV0uZXJyb3JNZXNzYWdlcy5qb2luKCcsICcpKTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgICAgaWYgKGUuZGF0YSAmJiBlLmRhdGEuZXJyb3IpIHtcbiAgICAgICAgZXJyLnB1c2goZS5kYXRhLm1lc3NhZ2UpO1xuICAgICAgfVxuICAgICAgY29uc3QgZmFpbFJlc3BvbnNlID0ge1xuICAgICAgICB0eXBlOiBlbWl0UmVzcG9uc2UucmVzcG9uc2VUeXBlcy5GQUlMLFxuICAgICAgICBtZXNzYWdlQ29udGV4dDogZW1pdFJlc3BvbnNlLm5vdGljZUNvbnRleHRzLlBBWU1FTlRTLFxuICAgICAgICBtZXNzYWdlOiAnUGF5bWVudCBmYWlsZWQ6ICcgKyBlcnIuam9pbignLCAnKSxcbiAgICAgIH07XG5cbiAgICAgIHJldHVybiBmYWlsUmVzcG9uc2U7XG4gICAgfVxuICB9O1xuXG4gIGNvbnN0IGlzVGVzdE1vZGUgPSB3Yy53Y1NldHRpbmdzLmdldFNldHRpbmcoICd0ZW53ZWJfcGF5bWVudHNfc3RyaXBlX2RhdGEnIClbICd0ZXN0X21vZGUnIF07XG5cbiAgLy8gVE9ETzogdGhpcyBpcyB0aGUgaHRtbCByZXR1cm5lZCBieSAnd2NfcHJpbnRfbm90aWNlJy4gZmluZCB0aGUganMgYWx0ZXJuYXRpdmUgdG8gZ2V0IHJpZCBvZiB0aGlzLlxuICBjb25zdCB0ZXN0aW5nSW5zdHJ1Y3Rpb25zID0gKFxuICAgICAgPGRpdiBjbGFzc05hbWU9XCJ3Yy1ibG9jay1zdG9yZS1ub3RpY2Ugd2MtYmxvY2stY29tcG9uZW50cy1ub3RpY2UtYmFubmVyIGlzLWluZm9cIj5cbiAgICAgICAgPHN2ZyB4bWxucz1cImh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnXCIgdmlld0JveD1cIjAgMCAyNCAyNFwiIHdpZHRoPVwiMjRcIiBoZWlnaHQ9XCIyNFwiIGFyaWEtaGlkZGVuPVwidHJ1ZVwiXG4gICAgICAgICAgICAgZm9jdXNhYmxlPVwiZmFsc2VcIj5cbiAgICAgICAgICA8cGF0aFxuICAgICAgICAgICAgICBkPVwiTTEyIDMuMmMtNC44IDAtOC44IDMuOS04LjggOC44IDAgNC44IDMuOSA4LjggOC44IDguOCA0LjggMCA4LjgtMy45IDguOC04LjggMC00LjgtNC04LjgtOC44LTguOHptMCAxNmMtNCAwLTcuMi0zLjMtNy4yLTcuMkM0LjggOCA4IDQuOCAxMiA0LjhzNy4yIDMuMyA3LjIgNy4yYzAgNC0zLjIgNy4yLTcuMiA3LjJ6TTExIDE3aDJ2LTZoLTJ2NnptMC04aDJWN2gtMnYyelwiPjwvcGF0aD5cbiAgICAgICAgPC9zdmc+XG4gICAgICAgIDxkaXYgY2xhc3NOYW1lPVwid2MtYmxvY2stY29tcG9uZW50cy1ub3RpY2UtYmFubmVyX19jb250ZW50XCI+XG4gICAgICAgICAgPHN0cm9uZz5UZXN0IG1vZGU6PC9zdHJvbmc+IHVzZSB0aGUgdGVzdCBWSVNBIGNhcmQgNDI0MjQyNDI0MjQyNDI0MlxuICAgICAgICAgIHdpdGggYW55IGV4cGlyeSBkYXRlIGFuZCBDVkMuIE5ldmVyIHByb3ZpZGUgeW91ciByZWFsIGNhcmQgZGF0YSB3aGVuIHRlc3QgbW9kZSBpcyBlbmFibGVkLlxuICAgICAgICA8L2Rpdj5cbiAgICAgIDwvZGl2PlxuICApO1xuXG4gIHJldHVybiAoXG4gICAgICA8PlxuICAgICAgICB7IGlzVGVzdE1vZGU9PSd5ZXMnID8gdGVzdGluZ0luc3RydWN0aW9ucyA6ICcnIH1cbiAgICAgICAgPGRpdiBpZD1cInR3d3AtY2FyZC1lbGVtZW50XCI+PC9kaXY+XG4gICAgICA8Lz5cbiAgKTtcbn07XG5cbmNvbnN0IG9wdGlvbnMgPSB7XG4gIG5hbWU6ICd0ZW53ZWJfcGF5bWVudHNfc3RyaXBlJyxcbiAgY29udGVudDogPFRlbndlYlBheW1lbnRDb21wb25lbnRTdHJpcGUvPixcbiAgZWRpdDogPFRlbndlYlBheW1lbnRDb21wb25lbnRTdHJpcGUvPixcbiAgY2FuTWFrZVBheW1lbnQ6ICgpID0+IHRydWUsIC8vIFRPRE86IHdlIGNhbiBjaGVjayBpZiB0aGUgUEUgaXMgaW5pdGlhbGl6ZWQgaGVyZVxuICBwYXltZW50TWV0aG9kSWQ6ICd0ZW53ZWJfcGF5bWVudHNfc3RyaXBlJyxcbiAgc3VwcG9ydHM6IHtcbiAgICBzaG93U2F2ZWRDYXJkczogZmFsc2UsXG4gICAgc2hvd1NhdmVPcHRpb246IGZhbHNlLFxuICAgIGZlYXR1cmVzOiBbXG4gICAgICAncHJvZHVjdHMnLFxuICAgICAgJ3JlZnVuZHMnLFxuICAgIF0sXG4gIH0sXG4gIHNhdmVkVG9rZW5Db21wb25lbnQ6IG51bGwsXG4gIGxhYmVsOiA8VGVud2ViUGF5bWVudExhYmVsU3RyaXBlLz4sXG4gIGFyaWFMYWJlbDogX18oICdDcmVkaXQgY2FyZCAvIERlYml0IGNhcmQnLCAndHd3cCcgKSxcbn07XG5cbnJlZ2lzdGVyUGF5bWVudE1ldGhvZCggb3B0aW9ucyApOyJdLCJuYW1lcyI6WyJfXyIsInVzZUVmZmVjdCIsInVzZVN0YXRlIiwicmVnaXN0ZXJQYXltZW50TWV0aG9kIiwid2luZG93Iiwid2MiLCJ3Y0Jsb2Nrc1JlZ2lzdHJ5IiwibG9hZFN0cmlwZSIsIlByb21pc2UiLCJyZXNvbHZlIiwiZXJyb3IiLCJ0ZW53ZWJQYXltZW50UHJvbWlzZVN0cmlwZSIsIlRlbndlYlBheW1lbnRMYWJlbFN0cmlwZSIsInByb3BzIiwiUGF5bWVudE1ldGhvZExhYmVsIiwiY29tcG9uZW50cyIsImxhYmVsVGV4dCIsImNyZWF0ZUVsZW1lbnQiLCJ0ZXh0IiwiVGVud2ViUGF5bWVudENvbXBvbmVudFN0cmlwZSIsImVycm9yTWVzc2FnZSIsInNldEVycm9yTWVzc2FnZSIsInRoZW4iLCJtZXNzYWdlIiwiRXJyb3IiLCJUZW53ZWJQYXltZW50Q3JlZGl0Q2FyZFN0cmlwZSIsImV2ZW50UmVnaXN0cmF0aW9uIiwiZW1pdFJlc3BvbnNlIiwib25QYXltZW50U2V0dXAiLCJvbkNoZWNrb3V0U3VjY2VzcyIsIm9uQ2hlY2tvdXRGYWlsIiwidHd3cF9jaGVja291dCIsImluaXQiLCJoYW5kbGVQYXltZW50U2V0dXBBc3luYyIsInByb2Nlc3NpbmdSZXNwb25zZSIsInBheW1lbnRTdGF0dXMiLCJwYXltZW50RGV0YWlscyIsIiRwYXltZW50TmVlZGVkIiwiYWN0aXZlUGF5bWVudE1ldGhvZCIsImludGVudERhdGEiLCJwYXltZW50Q2xpZW50U2VjcmV0IiwiaW50ZW50SWQiLCJjYXJ0SGFzaCIsIkNvb2tpZXMiLCJnZXQiLCIkcGF5bWVudENsaWVudFNlY3JldCIsIiRpbnRlbnRJZCIsInBheW1lbnRJbnRlbnRBY3Rpb25zIiwiJGNoZWNrb3V0X2Zvcm0iLCJyZW1vdmUiLCJhcHBlbmQiLCJuYW1lIiwialF1ZXJ5IiwibGVuZ3RoIiwidmFsIiwidW5kZWZpbmVkIiwicG9zdGFsX2NvZGUiLCJiaWxsaW5nX2NvdW50cnkiLCJiaWxsaW5nX3Bob25lIiwiYmlsbGluZ19zdGF0ZSIsImJpbGxpbmdfZW1haWwiLCJiaWxsaW5nRGV0YWlscyIsImVtYWlsIiwicGhvbmUiLCJhZGRyZXNzIiwic3RhdGUiLCJjb3VudHJ5IiwiZWxlbWVudHMiLCIkZWxlbWVudHMiLCJyZXN1bHQiLCJzdHJpcGUiLCJjb25maXJtUGF5bWVudCIsImNvbmZpcm1QYXJhbXMiLCJwYXltZW50X21ldGhvZF9kYXRhIiwiYmlsbGluZ19kZXRhaWxzIiwicmVkaXJlY3QiLCJmYWlsUmVzcG9uc2UiLCJ0eXBlIiwicmVzcG9uc2VUeXBlcyIsIkZBSUwiLCJtZXNzYWdlQ29udGV4dCIsIm5vdGljZUNvbnRleHRzIiwiUEFZTUVOVFMiLCJzdWNjZXNzUmVzcG9uc2UiLCJTVUNDRVNTIiwibWV0YSIsInBheW1lbnRNZXRob2REYXRhIiwiZSIsImVyciIsImtleSIsImVycm9yTWVzc2FnZXMiLCJwdXNoIiwiam9pbiIsImRhdGEiLCJpc1Rlc3RNb2RlIiwid2NTZXR0aW5ncyIsImdldFNldHRpbmciLCJ0ZXN0aW5nSW5zdHJ1Y3Rpb25zIiwiY2xhc3NOYW1lIiwieG1sbnMiLCJ2aWV3Qm94Iiwid2lkdGgiLCJoZWlnaHQiLCJmb2N1c2FibGUiLCJkIiwiRnJhZ21lbnQiLCJpZCIsIm9wdGlvbnMiLCJjb250ZW50IiwiZWRpdCIsImNhbk1ha2VQYXltZW50IiwicGF5bWVudE1ldGhvZElkIiwic3VwcG9ydHMiLCJzaG93U2F2ZWRDYXJkcyIsInNob3dTYXZlT3B0aW9uIiwiZmVhdHVyZXMiLCJzYXZlZFRva2VuQ29tcG9uZW50IiwibGFiZWwiLCJhcmlhTGFiZWwiXSwic291cmNlUm9vdCI6IiJ9