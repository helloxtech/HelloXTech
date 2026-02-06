<div class="tenweb_overlay"></div>
<div class="tenweb_popup_container">
    <div class="tenweb_header">
        <b>Warning: Migration in Progress</b>
        <span class="tenweb_popup_close"></span>
        <br><br>
        Deactivating the plugin will stop the migration process and your website won’t be copied to 10Web.
    </div>
    <form name="tenweb_deactivate" method="POST">
        <div class="tenweb_popup_content" data-adminemail="<?php echo $admin->data->user_email; ?>">
            <div>
                <input type="radio" value="reason_i_didnt_have_a_chance_to_select_a_data-center"
                       name="tenweb_manager_reasons"
                       id="tenweb_data-center_select_chance"
                       class="tenweb_radio">
                <label for="tenweb_data-center_select_chance">I didn’t have a chance to select a data-center<label>
            </div>
            <div>
                <input type="radio" value="reason_migration_started_automatically"
                       name="tenweb_manager_reasons"
                       id="tenweb_migration_started_automatically"
                       class="tenweb_radio">
                <label for="tenweb_migration_started_automatically">Migration started automatically<label>
            </div>
          <div>
            <input type="radio" value="reason_migration_takes_too_long_and_i_think_it_is_stuck"
                   name="tenweb_manager_reasons"
                   id="tenweb_migration_takes_too_long"
                   class="tenweb_radio">
            <label for="tenweb_migration_takes_too_long">Migration takes too long and I think it is stuck<label>
          </div>
          <div>
            <input type="radio" value="reason_want_to_migrate_another_website"
                   name="tenweb_manager_reasons"
                   id="tenweb_migrate_another_website"
                   class="tenweb_radio">
            <label for="tenweb_migrate_another_website">I want to migrate another website<label>
          </div>
          <div>
            <input type="radio" value="reason_other"
                   name="tenweb_manager_reasons"
                   id="tenweb_other"
                   class="tenweb_radio">
            <label for="tenweb_other">Other<label>
          </div>
          <div class="checkbox_container" style="margin-top: 5px;">
              <input type="checkbox" name="tenweb_checkbox">
              By submitting this form your email and website URL will be sent to 10Web. Click the checkbox if you
              consent to usage of mentioned data by 10Web in accordance with our
              <a target="_blank" href="https://10web.io/privacy-policy/">Privacy Policy</a>.
          </div>
          <div class="tenweb_reason_other tenweb_content">
              <div>
                  <strong>Please describe your issue.</strong>
              </div>
              <br/>
              <textarea name="tenweb_reason_other_textarea" rows="4"></textarea>
              <br/>
              <div class="reason_other_email">
                  Our support will contact
                  <input type="text" name="tenweb_reason_other_email" class="tenweb_email_field"/>
                  shortly.
              </div>
          </div>
          <?php wp_nonce_field('tenweb_manager_deactivate'); ?>
          <input type="hidden" class="tenweb_submit_and_deactivate" name="tenweb_submit_and_deactivate" value="1"/>
        </div>
        <div class="tenweb_button">
            <a href="#" class="button button-secondary tenweb_close_btn">
                Cancel
            </a>
            <a href="<?php echo $deactivate_url; ?>"
               class="button button-primary button-primary-disabled button-close tenweb_deactivate_btn">
                Deactivate
            </a>
        </div>
    </form>
</div>