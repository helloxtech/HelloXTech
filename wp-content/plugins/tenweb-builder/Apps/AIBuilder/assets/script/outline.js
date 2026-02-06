class TwbbOutline {
  constructor() {
    this.container = jQuery('.twbb-ai-builder__container[data-type="outline"]');
  }

  changeInfo() {
    const container = this.container;
    container.find('.twbb-ai-builder__title').html(twbb_ai_builder.outline_modify_title);
    container.find('.twbb-ai-builder__desc').html(twbb_ai_builder.outline_modify_desc);

    container.find('.twbb-lazy-load').removeClass('twbb-lazy-load');
    jQuery('.twbb-ai-builder__btn.next').removeClass('disabled');
  }

}
