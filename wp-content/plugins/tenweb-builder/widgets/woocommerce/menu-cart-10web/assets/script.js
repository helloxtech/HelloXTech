jQuery( window ).on( 'elementor/frontend/init', function() {
    class MiniCart10webHandler extends elementorModules.frontend.handlers.Base {
        onInit() {
            super.onInit();

            if ( typeof MiniCartTraitHandler !== 'undefined' ) {
                new MiniCartTraitHandler({ $element: this.$element });
                this.$element.addClass('twbb-mini-cart-trait');
            }
        }
    }

    elementorFrontend.elementsHandler.attachHandler( 'twbb_woocommerce-menu-cart-10web', MiniCart10webHandler );
});
