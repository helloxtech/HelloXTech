// jQuery(window).on('elementor/frontend/init', function() {
//     // Load html2canvas library
//     const loadHtml2Canvas = () => {
//         return new Promise((resolve, reject) => {
//             if (window.html2canvas) {
//                 resolve(window.html2canvas);
//                 return;
//             }
//             const script = document.createElement('script');
//             script.src = twbb_editor.plugin_url + '/widgets/header-widget/assets/html2canvas.min.js';
//             script.onload = () => resolve(window.html2canvas);
//             script.onerror = reject;
//             document.head.appendChild(script);
//         });
//     };
//
//     class HeaderScrollHandler extends elementorModules.frontend.handlers.Base {
//         onInit() {
//             super.onInit();
//             this.initScrollHandler();
//             this.initColorDetection();
//             this.initMenuCart();
//
//         }
//
//         initMenuCart() {
//             if ( typeof MiniCartTraitHandler !== 'undefined' ) {
//                 new MiniCartTraitHandler({ $element: this.$element });
//                 this.$element.addClass('twbb-mini-cart-trait');
//             }
//         }
//
//         initScrollHandler() {
//             this.lastScrollTop = 0;
//             const settings = this.getElementSettings();
//             this.scrollThreshold = settings.scroll_treshold || 100;
//             this.isScrollingUp = false;
//             this.isScrollingDown = false;
//             this.isReinitializing = true;
//
//             this.$header = this.$element.find('.twbb-header-widget');
//             this.headerHeight = this.$header.outerHeight();
//
//             // Remove existing scroll-related classes
//             this.$element.removeClass([
//                 'twbb-header-widget-scrolled',
//                 'twbb-header-widget-sticky',
//                 'twbb-header-widget-floating'
//             ]).addClass('twbb-header-widget-visible');
//
//             // Check if scroll behavior is enabled
//             this.isScrollEnabled = this.$element.hasClass('twbb-header-widget-show-on-scroll-yes');
//
//             // Check scroll behavior directions
//             this.showOnScrollDown = this.$element.hasClass('twbb-header-widget-scroll-behavior-on_scroll_down');
//             this.showOnScrollUp = this.$element.hasClass('twbb-header-widget-scroll-behavior-on_scroll_up');
//
//             // Check if floating effect is enabled
//             this.isFloatingEnabled = this.$element.hasClass('twbb-header-widget-floating-on-scroll-yes');
//
//             // Check if header is sticky
//             this.isSticky = this.$element.hasClass('twbb-header-widget-sticky-yes');
//
//             jQuery(window).on('scroll', this.onScroll.bind(this));
//
//             // Trigger scroll event to update header state
//             this.lastScrollTop = jQuery(window).scrollTop();
//             this.onScroll();
//             this.isReinitializing = false;
//         }
//
//         onScroll() {
//             // Return early if neither scroll nor floating behavior is enabled
//             if (!this.isScrollEnabled && !this.isFloatingEnabled) {
//                 return;
//             }
//
//             const currentScroll = jQuery(window).scrollTop();
//
//             // Return early if scroll position hasn't changed and not reinitializing
//             if (currentScroll === this.lastScrollTop && !this.isReinitializing) {
//                 return;
//             }
//
//             // Determine scroll direction
//             this.isScrollingUp = currentScroll <= this.lastScrollTop;
//             this.isScrollingDown = currentScroll >= this.lastScrollTop;
//
//             // Handle floating effect
//             if (this.isFloatingEnabled) {
//                 if (currentScroll > this.scrollThreshold) {
//                     this.$element.addClass('twbb-header-widget-floating').addClass('twbb-header-widget-scrolled');
//                 } else {
//                     this.$element.removeClass('twbb-header-widget-floating').removeClass('twbb-header-widget-scrolled');
//                 }
//             }
//
//             // Skip scroll visibility logic for sticky headers
//             if (this.isSticky) {
//                 return;
//             }
//
//             // Handle visibility based on scroll direction preferences
//             if (currentScroll <= this.scrollThreshold || currentScroll === 0) {
//                 this.$element.removeClass('twbb-header-widget-visible');
//                 if (currentScroll === 0) {
//                     this.$element.addClass('twbb-header-widget-visible');
//                     this.$element.removeClass('twbb-header-widget-sticky').removeClass('twbb-header-widget-scrolled');
//                 }
//             } else {
//                 if (this.isScrollingUp) {
//                     this.$element.addClass('twbb-header-widget-sticky').addClass('twbb-header-widget-scrolled').addClass('twbb-header-widget-visible');
//                 } else if (this.isScrollingDown && this.showOnScrollDown) {
//                     this.$element.addClass('twbb-header-widget-sticky').addClass('twbb-header-widget-scrolled').addClass('twbb-header-widget-visible');
//                 } else {
//                     this.$element.removeClass('twbb-header-widget-visible');
//                 }
//             }
//
//             this.lastScrollTop = currentScroll;
//         }
//
//         async initColorDetection() {
//             // Function to get dominant color
//             this.getDominantColor = async () => {
//                 const headerHeight = this.$header.outerHeight();
//                 // Detect area at the very top of the page, with the same size as the header
//                 const captureY = 0;
//                 const captureHeight = headerHeight;
//
//                 // Create a temporary div to capture the area (not strictly needed, but kept for compatibility)
//                 const tempDiv = document.createElement('div');
//                 tempDiv.style.position = 'absolute';
//                 tempDiv.style.top = captureY + 'px';
//                 tempDiv.style.left = '0';
//                 tempDiv.style.width = window.innerWidth + 'px';
//                 tempDiv.style.height = captureHeight + 'px';
//                 tempDiv.style.zIndex = '-1';
//                 document.body.appendChild(tempDiv);
//
//                 try {
//                     // Capture the area using html2canvas
//                     const canvas = await this.html2canvas(document.body, {
//                         x: 0,
//                         y: captureY,
//                         width: window.innerWidth,
//                         height: captureHeight,
//                         useCORS: true,
//                         allowTaint: true,
//                         backgroundColor: null
//                     });
//
//                     // Get image data
//                     const ctx = canvas.getContext('2d');
//                     const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
//                     const data = imageData.data;
//
//                     // Calculate average color
//                     let r = 0, g = 0, b = 0, count = 0;
//
//                     for (let i = 0; i < data.length; i += 4) {
//                         // Skip transparent pixels
//                         if (data[i + 3] === 0) continue;
//
//                         r += data[i];
//                         g += data[i + 1];
//                         b += data[i + 2];
//                         count++;
//                     }
//
//                     let hex;
//                     if (count > 0) {
//                         r = Math.round(r / count);
//                         g = Math.round(g / count);
//                         b = Math.round(b / count);
//                         hex = '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
//                     } else {
//                         // Fallback to background color if no pixels found
//                         const styles = window.getComputedStyle(document.body);
//                         const backgroundColor = styles.backgroundColor;
//                         hex = this.rgbToHex(backgroundColor);
//                     }
//
//                     // Emit event with the dominant color
//                     this.$element.trigger('twbb:dominantColorDetected', [hex]);
//                     console.log('Detected color:', hex);
//
//                     return hex;
//                 } catch (error) {
//                     console.error('Error detecting color:', error);
//                     // Fallback to background color
//                     const styles = window.getComputedStyle(document.body);
//                     const backgroundColor = styles.backgroundColor;
//                     const hex = this.rgbToHex(backgroundColor);
//                     this.$element.trigger('twbb:dominantColorDetected', [hex]);
//                     console.log('Detected color:', hex);
//                     return hex;
//                 } finally {
//                     // Clean up
//                     document.body.removeChild(tempDiv);
//                 }
//             };
//
//             // Helper function to convert RGB to hex
//             this.rgbToHex = (rgb) => {
//                 const rgbMatch = rgb.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*[\d.]+)?\)$/);
//                 if (rgbMatch) {
//                     const r = parseInt(rgbMatch[1]);
//                     const g = parseInt(rgbMatch[2]);
//                     const b = parseInt(rgbMatch[3]);
//                     return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
//                 }
//                 return rgb;
//             };
//
//             // Helper function to determine if a color is light or dark
//             this.isColorLight = (hex) => {
//                 // Convert hex to RGB
//                 const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
//                 if (!result) return true; // Default to light if invalid hex
//
//                 const r = parseInt(result[1], 16);
//                 const g = parseInt(result[2], 16);
//                 const b = parseInt(result[3], 16);
//
//                 // YIQ equation to determine brightness
//                 // This formula takes into account human perception of color
//                 const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
//                 return yiq >= 128; // >= 128 is considered light
//             };
//
//             // Helper function to update contrast classes
//             this.updateContrastClass = (hex) => {
//                 // Remove existing contrast classes
//                 this.$element.removeClass('twbb-header-widget-contrast-dark twbb-header-widget-contrast-light');
//
//                 // Add appropriate contrast class
//                 const isLight = this.isColorLight(hex);
//                 this.$element.addClass(isLight ? 'twbb-header-widget-contrast-dark' : 'twbb-header-widget-contrast-light');
//             };
//
//             // Modify getDominantColor to update contrast class
//             const originalGetDominantColor = this.getDominantColor;
//             this.getDominantColor = async () => {
//                 const hex = await originalGetDominantColor();
//                 if (hex) {
//                     this.updateContrastClass(hex);
//                 }
//                 return hex;
//             };
//
//             // Run color detection when window is fully loaded, only if wrapper has no background
//             const shouldDetect = () => {
//                 const $wrapper = this.$element.find('.twbb-header-widget-wrapper');
//                 if ($wrapper.length === 0) return true; // If not found, proceed
//                 const style = window.getComputedStyle($wrapper[0]);
//                 const bgColor = style.backgroundColor;
//                 const bgImage = style.backgroundImage;
//                 // Check for transparent/none backgrounds
//                 const isTransparent = (
//                     bgColor === 'transparent' ||
//                     bgColor === 'rgba(0, 0, 0, 0)' ||
//                     bgColor === 'rgba(0,0,0,0)'
//                 );
//                 const isNoImage = (bgImage === 'none');
//                 return isTransparent && isNoImage;
//             };
//
//             if (shouldDetect()) {
//                 try {
//                     this.html2canvas = await loadHtml2Canvas();
//                 } catch (error) {
//                     console.error('Failed to load html2canvas:', error);
//                     return;
//                 }
//
//                 if (document.readyState === 'complete') {
//                     this.getDominantColor();
//                 } else {
//                     window.addEventListener('load', () => {
//                         this.getDominantColor();
//                     });
//                 }
//             }
//         }
//
//         onDestroy() {
//             jQuery(window).off('scroll resize');
//             super.onDestroy();
//         }
//
//         onElementChange(propertyName) {
//             // Scroll behavior related properties
//             const scrollProperties = [
//                 'sticky_header',
//                 'show_on_scroll',
//                 'scroll_behavior',
//                 'floating_effect_scroll',
//                 'scroll_treshold',
//                 'floating_animation_speed'
//             ];
//
//             // Background related properties
//             const backgroundProperties = [
//                 'normal_header_box_background_background',
//                 'normal_header_box_background_color',
//                 'normal_header_box_background_image',
//                 'normal_header_box_background_position',
//                 'normal_header_box_background_attachment',
//                 'normal_header_box_background_repeat',
//                 'normal_header_box_background_size'
//             ];
//
//             // Handle scroll behavior changes
//             if (scrollProperties.includes(propertyName)) {
//                 // Remove existing scroll handler
//                 jQuery(window).off('scroll', this.onScroll);
//                 // Reinitialize the scroll handler
//                 this.initScrollHandler();
//             }
//
//             // Handle background changes
//             if (backgroundProperties.includes(propertyName)) {
//                 // Reinitialize color detection
//                 this.initColorDetection();
//             }
//         }
//     }
//
//     var HeaderWidgetMenuHandler = elementorModules.frontend.handlers.Base.extend({
//         // stretchElement: null,
//         getDefaultSettings: function () {
//           return {
//             selectors: {
//               menu: '.twbb-nav-menu--main .twbb-nav-menu',
//               dropdownMenu: '.twbb-nav-menu__container.twbb-nav-menu--dropdown',
//               menuToggle: '.twbb-menu-toggle',
//               dropdownMenuItem: '.twbb-nav-menu--dropdown .menu-item',
//             }
//           };
//         },
//         getDefaultElements: function () {
//           var selectors = this.getSettings('selectors'),
//               elements = {};
//           elements.$menu = this.$element.find(selectors.menu);
//           elements.$dropdownMenu = this.$element.find(selectors.dropdownMenu);
//           elements.$dropdownMenuItem = this.$element.find(selectors.dropdownMenuItem);
//           elements.$dropdownMenuFinalItems = elements.$dropdownMenu.find('.menu-item:not(.menu-item-has-children) > a');
//           elements.$menuToggle = this.$element.find(selectors.menuToggle);
//           return elements;
//         },
//         bindEvents: function () {
//           if (!this.elements.$menu.length) {
//             return;
//           }
//           this.elements.$menuToggle.on('click', this.toggleMenu.bind(this));
//           this.elements.$dropdownMenuFinalItems.on('click', this.toggleMenu.bind(this, false));
//           this.toggleDropdown(this.elements.$dropdownMenuItem);
//           elementorFrontend.addListenerOnce(this.$element.data('model-cid'), 'resize', this.onWindowResize.bind(this));
//         },
//
//         toggleDropdown(element) {
//             //mobile
//             jQuery(element).click(function(e) {
//                 if (jQuery('.sub-menu', this).length >= 1) {
//                     e.preventDefault();
//
//                     const $subMenu = jQuery(this).find('> .sub-menu');
//                     const $menuItems = $subMenu.find('.menu-item');
//
//                     // Set item indices for staggered animation
//                     $menuItems.each(function(index) {
//                         jQuery(this).css('--twbb-item-index', index);
//                     });
//
//                     // Close other submenus
//                     jQuery(this).siblings().find('> .sub-menu').removeClass('open');
//                     jQuery(this).siblings().find('> .sub-menu').find('.twbb-header-nav-back').remove();
//
//                     // Add back button if not exists
//                     if($subMenu.find('.twbb-header-nav-back').length == 0) {
//                         $subMenu.prepend('<div class="twbb-header-nav-back" onclick="twbb_menu_back(this, event)">Back</div>');
//                     }
//
//                     // Open submenu with animation
//                     $subMenu.addClass("open");
//                 }
//                 e.stopPropagation();
//             });
//         },
//
//         toggleMenu: function (show) {
//             var $dropdownMenu = this.elements.$dropdownMenu,
//                 isDropdownVisible = this.elements.$menuToggle.hasClass('twbb-active');
//             show = !isDropdownVisible;
//
//             // Reset all submenus
//             $dropdownMenu.find('.sub-menu').removeClass('open');
//             $dropdownMenu.find('.sub-menu').find('.twbb-header-nav-back').remove();
//
//             // Toggle menu button state
//             this.elements.$menuToggle.toggleClass('twbb-active', show);
//
//             if (show) {
//                 // Set item indices for staggered animation
//                 this.setItemIndices($dropdownMenu.find('.menu-item'));
//
//                 // Show dropdown with animation
//                 $dropdownMenu.addClass('twbb-nav-menu--dropdown-visible')
//                             .removeClass('twbb-nav-menu--dropdown-hidden');
//
//                 // Prevent body scroll
//                 jQuery('body').addClass('twbb-header-menu-open');
//             } else {
//                 // Hide dropdown with animation
//                 $dropdownMenu.removeClass('twbb-nav-menu--dropdown-visible')
//                             .addClass('twbb-nav-menu--dropdown-hidden');
//
//                 // Allow body scroll
//                 jQuery('body').removeClass('twbb-header-menu-open');
//
//                 // Reset styles after animation
//                 setTimeout(() => {
//                     $dropdownMenu.removeAttr('style');
//                 }, 300);
//             }
//         },
//
//         setItemIndices: function($items) {
//             $items.each(function(index) {
//                 jQuery(this).css('--twbb-item-index', index);
//             });
//         },
//
//         onInit: function () {
//             if ( jQuery.fn.smartmenus ) {
//                 // Override the default stupid detection
//                 jQuery.SmartMenus.prototype.isCSSOn = function() {
//                     return true;
//                 };
//
//                 if ( elementorFrontend.config.is_rtl  ) {
//                     jQuery.fn.smartmenus.defaults.rightToLeftSubMenus = true;
//                 }
//             }
//
//             elementorModules.frontend.handlers.Base.prototype.onInit.apply(this, arguments);
//             if (!this.elements.$menu.length) {
//                 return;
//             }
//
//             const elementSettings = this.getElementSettings();
//             let subIndicatorsContent = '';
//             if ( elementSettings && elementSettings.submenu_indicator_icon){
//                 const iconLibrary = elementSettings.submenu_indicator_icon.library,
//                     iconValue = elementSettings.submenu_indicator_icon.value;
//                 if (iconLibrary === 'svg') {
//                     subIndicatorsContent = '<i class="twbb-uploaded-svg-icon" style="background-image: url(' + iconValue.url + ');"></i>';
//                 } else {
//                     if (iconValue) {
//                         // The value of iconValue can be either className inside the editor or a markup in the frontend.
//                         subIndicatorsContent = iconValue.indexOf('<') > -1 ? iconValue : `<i class="${iconValue}"></i>`;
//                     }
//                 }
//             }
//             // Add sub-arrow to all menu items with children in both main menu and dropdown
//             const addSubArrows = ($menu) => {
//                 $menu.find('.menu-item-has-children > a').each(function() {
//                     if (!jQuery(this).find('.sub-arrow').length) {
//                         jQuery(this).append(`<span class="sub-arrow">${subIndicatorsContent}</span>`);
//                     }
//                 });
//             };
//
//             // Add sub-arrows to dropdown menu
//             addSubArrows(this.elements.$dropdownMenu);
//
//             //desktop
//             jQuery(this.elements.$menu).smartmenus({
//                 subIndicators: '' !== subIndicatorsContent,
//                 subIndicatorsText: subIndicatorsContent,
//                 subIndicatorsPos: 'append',
//                 subMenusMaxWidth: '1000px',
//             });
//         },
//
//         onWindowResize: function () {
//             const windowWidth = jQuery(window).width();
//             const $dropdownMenu = this.elements.$dropdownMenu;
//             const $menuToggle = this.elements.$menuToggle;
//
//             // Reset menu state on resize
//             if (jQuery('body').attr('data-elementor-device-mode') === 'desktop') {
//                 // Desktop view
//                 $dropdownMenu.removeClass('twbb-nav-menu--dropdown-visible twbb-nav-menu--dropdown-hidden');
//                 $menuToggle.removeClass('twbb-active');
//
//                 // Reset any mobile-specific styles
//                 $dropdownMenu.css({
//                     'position': '',
//                     'width': '',
//                     'height': '',
//                     'top': '',
//                     'left': '',
//                     'margin': '',
//                     'opacity': '',
//                     'visibility': '',
//                     'transform': ''
//                 });
//
//                 // Show main menu
//                 this.elements.$menu.closest('.twbb-nav-menu--main').show();
//             } else if( jQuery('body').attr('data-elementor-device-mode') === 'tablet') {
//                 if( this.elements.$menu.closest('.twbb-nav-menu--main').parents('.twbb-header-widget-breakpoint-tablet').length ) {
//                     this.elements.$menu.closest('.twbb-nav-menu--main').hide();
//                 } else {
//                     this.elements.$menu.closest('.twbb-nav-menu--main').show();
//                 }
//             } else if( jQuery('body').attr('data-elementor-device-mode') === 'mobile') {
//                 if( this.elements.$menu.closest('.twbb-nav-menu--main').parents('.twbb-header-widget-breakpoint-tablet').length ||
//                     this.elements.$menu.closest('.twbb-nav-menu--main').parents('.twbb-header-widget-breakpoint-mobile').length ) {
//                     this.elements.$menu.closest('.twbb-nav-menu--main').hide();
//                 } else {
//                     this.elements.$menu.closest('.twbb-nav-menu--main').show();
//                 }
//             }
//             else {
//                 // Mobile/tablet view
//                 if (!$menuToggle.hasClass('twbb-active')) {
//                     $dropdownMenu.addClass('twbb-nav-menu--dropdown-hidden');
//                 }
//
//                 if( jQuery('body').attr('data-elementor-device-mode') === 'tablet') {
//                     if( this.elements.$menu.closest('.twbb-nav-menu--main').parents('.twbb-header-widget-breakpoint-tablet').length ) {
//                         this.elements.$menu.closest('.twbb-nav-menu--main').hide();
//                     } else {
//                         this.elements.$menu.closest('.twbb-nav-menu--main').show();
//                     }
//                 } else if( jQuery('body').attr('data-elementor-device-mode') === 'mobile') {
//                     if( this.elements.$menu.closest('.twbb-nav-menu--main').parents('.twbb-header-widget-breakpoint-mobile').length ) {
//                         this.elements.$menu.closest('.twbb-nav-menu--main').hide();
//                     }
//                 }
//             }
//
//             // Update header height for scroll calculations
//             if (this.scrollHandler) {
//                 this.scrollHandler.headerHeight = this.$element.find('.twbb-header-widget').outerHeight();
//             }
//         },
//
//         onDestroy: function() {
//             jQuery('body').removeClass('twbb-header-menu-open');
//         }
//     });
//
//     elementorFrontend.elementsHandler.attachHandler( 'twbb-header-widget', HeaderScrollHandler );
//     elementorFrontend.elementsHandler.attachHandler( 'twbb-header-widget', HeaderWidgetMenuHandler );
// });
//
//   function twbb_menu_back(that, e) {
//     e.stopPropagation();
//     const $subMenu = jQuery(that).closest('.sub-menu');
//
//     // Add closing class for animation
//     $subMenu.addClass('closing');
//
//     // Wait for animation to complete before removing classes
//     setTimeout(() => {
//         $subMenu.removeClass('open closing');
//     }, 300); // Match the transition duration from CSS
//   }
