<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php include_component('repository', 'contextMenu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo render_title($resource); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'informationobject', 'action' => 'updatePublicationStatus'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="pub-status-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#pub-status-collapse" aria-expanded="true" aria-controls="pub-status-collapse">
            <?php echo __('Update publication status'); ?>
          </button>
        </h2>
        <div id="pub-status-collapse" class="accordion-collapse collapse show" aria-labelledby="pub-status-heading">
          <div class="accordion-body">
            <?php echo $form->publicationStatus->label(__('Publication status'))->renderRow(); ?>

            <?php if ($resource->rgt - $resource->lft > 1) { ?>
              <?php echo $form->updateDescendants->label(__('Update descendants'))->renderRow(); ?>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions nav gap-2">
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Update'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
