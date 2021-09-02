<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('I18n language'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <div class="alert alert-info">
    <p><?php echo __('Please rebuild the search index if you are adding new languages.'); ?></p>
    <pre>$ php symfony search:populate</pre>
  </div>

  <?php echo $form->renderGlobalErrors(); ?>

  <form action="<?php echo url_for('settings/language'); ?>" method="post">

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="language-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#language-collapse" aria-expanded="true" aria-controls="language-collapse">
            <?php echo __('i18n language settings'); ?>
          </button>
        </h2>
        <div id="language-collapse" class="accordion-collapse collapse show" aria-labelledby="language-heading">
          <div class="accordion-body">
            <?php foreach ($i18nLanguages as $setting) { ?>
              <div class="row mb-3">
                <label class="col-11 col-form-label">
                  <?php echo format_language($setting->getName()); ?>
                  <code class="ms-1"><?php echo $setting->getName(); ?></code>
                </label>
                <div class="col-1 px-2 text-end align-middle">
                  <?php if ($setting->deleteable) { ?>
                    <a class="btn atom-btn-white" href="<?php echo url_for([$setting, 'module' => 'settings', 'action' => 'delete']); ?>">
                      <i class="fas fa-fw fa-times" aria-hidden="true"></i>
                      <span class="visually-hidden"><?php echo __('Delete'); ?></span>
                    </a>
                  <?php } else { ?>
                    <span class="btn disabled" aria-hidden="true">
                      <i class="fas fa-fw fa-lock"></i>
                    </span>
                  <?php } ?>
                </div>
              </div>
            <?php } ?>

            <hr>

            <?php echo render_field($form->languageCode); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Add'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
