<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <?php if (isset($resource)) { ?>
    <h1 class="multiline">
      <?php echo $title; ?>
      <span class="sub"><?php echo render_title($resource); ?></span>
    </h1>
  <?php } else { ?>
    <h1><?php echo $title; ?></h1>
  <?php } ?>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php if (isset($resource)) { ?>
    <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'object', 'action' => 'importSelect']), ['enctype' => 'multipart/form-data']); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for(['module' => 'object', 'action' => 'importSelect']), ['enctype' => 'multipart/form-data']); ?>
  <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion" id="object-import">
      <div class="accordion-item">
        <h2 class="accordion-header" id="import-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#import-collapse" aria-expanded="true" aria-controls="import-collapse">
            <?php echo __('Import options'); ?>
          </button>
        </h2>
        <div id="import-collapse" class="accordion-collapse collapse show" aria-labelledby="import-heading" data-bs-parent="#object-import">
          <div class="accordion-body">
            <input type="hidden" name="importType" value="<?php echo esc_entities($type); ?>"/>

            <?php if ('csv' == $type) { ?>
              <div class="form-item">
                <label><?php echo __('Type'); ?></label>
                <select name="objectType">
                  <option value="informationObject"><?php echo sfConfig::get('app_ui_label_informationobject'); ?></option>
                  <option value="accession"><?php echo sfConfig::get('app_ui_label_accession', __('Accession')); ?></option>
                  <option value="authorityRecord"><?php echo sfConfig::get('app_ui_label_actor'); ?></option>
                  <option value="authorityRecordRelationship"><?php echo sfConfig::get('app_ui_label_authority_record_relationships'); ?></option>
                  <option value="event"><?php echo sfConfig::get('app_ui_label_event', __('Event')); ?></option>
                  <option value="repository"><?php echo sfConfig::get('app_ui_label_repository', __('Repository')); ?></option>
                </select>
              </div>
            <?php } ?>

            <?php if ('csv' != $type) { ?>
              <div class="form-item">
                <label><?php echo __('Type'); ?></label>
                <select name="objectType">
                  <option value="ead"><?php echo __('EAD 2002'); ?></option>
                  <option value="eac-cpf"><?php echo __('EAC CPF'); ?></option>
                  <option value="mods"><?php echo __('MODS'); ?></option>
                  <option value="dc"><?php echo __('DC'); ?></option>
                </select>

                <p class="alert alert-info text-center"><?php echo __('If you are importing a SKOS file to a taxonomy other than subjects, please go to the %1%', ['%1%' => link_to(__('SKOS import page'), ['module' => 'sfSkosPlugin', 'action' => 'import'], ['class' => 'alert-link'])]); ?></p>
              </div>
            <?php } ?>


            <div id="updateBlock">

              <?php if ('csv' == $type) { ?>
                <div class="form-item">
                  <label><?php echo __('Update behaviours'); ?></label>
                  <select name="updateType">
                    <option value="import-as-new"><?php echo __('Ignore matches and create new records on import'); ?></option>
                    <option value="match-and-update"><?php echo __('Update matches ignoring blank fields in CSV'); ?></option>
                    <option value="delete-and-replace"><?php echo __('Delete matches and replace with imported records'); ?></option>
                  </select>
                </div>
              <?php } ?>

              <?php if ('csv' != $type) { ?>
                <div class="form-item">
                  <label><?php echo __('Update behaviours'); ?></label>
                  <select name="updateType">
                    <option value="import-as-new"><?php echo __('Ignore matches and import as new'); ?></option>
                    <option value="delete-and-replace"><?php echo __('Delete matches and replace with imports'); ?></option>
                  </select>
                </div>
              <?php } ?>

              <div class="form-item">

                <div class="panel panel-default" id="matchingOptions" style="display:none;">
                  <div class="panel-body">
                    <label>
                      <input name="skipUnmatched" type="checkbox"/>
                      <?php echo __('Skip unmatched records'); ?>
                    </label>

                    <div class="criteria">
                      <div class="filter-row repos-limit">
                        <div class="filter">
                          <?php echo $form->repos
                              ->label(__('Limit matches to:'))
                              ->renderRow(); ?>
                        </div>
                      </div>

                      <div class="filter-row collection-limit">
                        <div class="filter">
                          <?php echo $form->collection
                              ->label(__('Top-level description'))
                              ->renderLabel(); ?>
                          <?php echo $form->collection->render(['class' => 'form-autocomplete']); ?>
                          <input class="list" type="hidden" value="<?php echo url_for(['module' => 'informationobject', 'action' => 'autocomplete', 'parent' => QubitInformationObject::ROOT_ID, 'filterDrafts' => true]); ?>"/>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="panel panel-default" id="importAsNewOptions">
                  <div class="panel-body">
                    <label>
                      <input name="skipMatched" type="checkbox"/>
                      <?php echo __('Skip matched records'); ?>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-item" id="noIndex">
              <label>
                <input name="noIndex" type="checkbox"/>
                <?php echo __('Do not index imported items'); ?>
              </label>
            </div>

            <?php if ('csv' == $type && sfConfig::get('app_csv_transform_script_name')) { ?>
              <div class="form-item">
                <label>
                  <input name="doCsvTransform" type="checkbox"/>
                  <?php echo __('Include transformation script'); ?>
                  <div class="pull-right">
                    <?php echo __(sfConfig::get('app_csv_transform_script_name')); ?>
                  </div>
                </label>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="file-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#file-collapse" aria-expanded="false" aria-controls="file-collapse">
            <?php echo __('Select file'); ?>
          </button>
        </h2>
        <div id="file-collapse" class="accordion-collapse collapse" aria-labelledby="file-heading" data-bs-parent="#object-import">
          <div class="accordion-body">
            <div class="form-item">
              <label><?php echo __('Select a file to import'); ?></label>
              <input name="file" type="file"/>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Import'); ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
