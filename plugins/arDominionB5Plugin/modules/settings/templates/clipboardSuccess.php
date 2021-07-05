<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Clipboard settings'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'clipboard'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion" id="settings-clipboard">
      <div class="accordion-item">
        <h2 class="accordion-header" id="saving-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#saving-collapse" aria-expanded="true" aria-controls="saving-collapse">
            <?php echo __('Clipboard saving'); ?>
          </button>
        </h2>
        <div id="saving-collapse" class="accordion-collapse collapse show" aria-labelledby="saving-heading" data-bs-parent="#settings-clipboard">
          <div class="accordion-body">
            <?php echo $form->clipboard_save_max_age
                ->label(__('Saved clipboard maximum age (in days)'))
                ->help(__('The number of days a saved clipboard should be retained before it is eligible for deletion'))
                ->renderRow(); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="sending-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sending-collapse" aria-expanded="false" aria-controls="sending-collapse">
            <?php echo __('Clipboard sending'); ?>
          </button>
        </h2>
        <div id="sending-collapse" class="accordion-collapse collapse" aria-labelledby="sending-heading" data-bs-parent="#settings-clipboard">
          <div class="accordion-body">
            <?php echo $form->clipboard_send_enabled
                ->label(__('Enable clipboard send functionality'))
                ->renderRow(); ?>

            <?php echo $form->clipboard_send_url
                ->label(__('External URL to send clipboard contents to'))
                ->renderRow(); ?>

            <?php echo get_partial('settings/i18n_form_field',
              [
                  'name' => 'clipboard_send_button_text',
                  'label' => __('Send button text'),
                  'settings' => $settings,
                  'form' => $form, ]); ?>

            <?php echo get_partial('settings/i18n_form_field',
              [
                  'name' => 'clipboard_send_message_html',
                  'label' => __('Text or HTML to display when sending clipboard contents'),
                  'settings' => $settings,
                  'form' => $form, ]); ?>

            <?php echo $form->clipboard_send_http_method
                ->label(__('HTTP method to use when sending clipboard contents'))
                ->renderRow(); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="export-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#export-collapse" aria-expanded="false" aria-controls="export-collapse">
            <?php echo __('Clipboard export'); ?>
          </button>
        </h2>
        <div id="export-collapse" class="accordion-collapse collapse" aria-labelledby="export-heading" data-bs-parent="#settings-clipboard">
          <div class="accordion-body">
            <?php echo $form->clipboard_export_digitalobjects_enabled
                ->label(__('Enable digital object export'))
                ->renderRow(); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
