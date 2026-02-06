function  twbbSetAnimations( type ) {

  jQuery('.twbb-ai-builder__left-content.slide_up_text .text-slide-up').each(function() {
    const text = jQuery(this).text(),
      animate = jQuery(this).attr('data-animate'),
      words = text.split(' '),
      baseDelay = animate === 'slideUp1' ? 90 : 60;

    const html = words.map((word, i) => {
      const delay = i * baseDelay;
      return `<span style="animation-delay: ${delay}ms">${word}</span>`;
    }).join(' ');

    jQuery(this).addClass('show').html(html);
  })
}

function safeJsonParse(str) {
  try {
    return JSON.parse(str);
  } catch (e) {
    try {
      const cleaned = str.replace(/\\"/g, '"');
      return JSON.parse(cleaned);
    } catch (e2) {
      return null;
    }
  }
}
