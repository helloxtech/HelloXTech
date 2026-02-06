jQuery(document).ready(function () {

  twbbShowGenerationCount();

  const addNewBtn = jQuery('.wrap .page-title-action').first();
  if (addNewBtn.length) {
    jQuery('<a>', {
      class: 'twbb-add-page-with-ai twbb-ai-builder-page__button',
      'data-id': 'single_page',
      text: 'Add page with AI'
    }).insertAfter(addNewBtn);
  }
});

async function twbbShowGenerationCount(){
  try{
    let url = `templates/workspaces/${twbb_ai_builder.workspace_id}/generation-count`;
    if( twbb_ai_builder.reseller_mode ) {
      url = 'templates/generation-count';
    }
    const generationCount = await twbbRequests(
      'GET',
      url,
      true
    );
    if (generationCount) {
      if (generationCount.status === 200) {
        twbbChangeButtonsInfo(generationCount.data);
      }
    }
  }
  catch (error) {
    console.log('Error fetching data', error);
  }
}

function twbbChangeButtonsInfo(data) {
  const aiBuilderPage = jQuery('.twbb-ai-builder-page');
  const buttonContainer = aiBuilderPage.find('.twbb-ai-builder-page__button-container.generate');
  const { can_regenerate, count, plan_regeneration_limit } = data || {};
  if (!can_regenerate) {
    buttonContainer.addClass('disabled');
    buttonContainer.find('.twbb-ai-builder-page__button').addClass('disabled');
  }
  aiBuilderPage.find('#generated_count').html(count);
  aiBuilderPage.find('#generation_count').html(plan_regeneration_limit);
  aiBuilderPage.find('.generation-count').removeClass('hidden');
}
