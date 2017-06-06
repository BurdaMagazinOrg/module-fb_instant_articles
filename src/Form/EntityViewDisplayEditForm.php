<?php

namespace Drupal\fb_instant_articles\Form;

use Drupal\field_ui\Form\EntityViewDisplayEditForm as CoreEntityViewDisplayEditForm;

/**
 * Extends the core EntityViewDisplayEditForm to support multiple regions.
 */
class EntityViewDisplayEditForm extends CoreEntityViewDisplayEditForm {

  /**
   * {@inheritdoc}
   */
  public function getRegions() {
    $regions = parent::getRegions();
    if ($this->getEntity()->getOriginalMode() === 'fb_instant_articles') {
      $new_regions['header'] = [
        'title' => $this->t('Header'),
        'message' => $this->t('No fields are displayed in this region.'),
      ];
      $new_regions['content'] = [
        'title' => $this->t('Body'),
        'message' => $this->t('No fields are displayed in this region.'),
      ];
      $new_regions['footer'] = [
        'title' => $this->t('Footer'),
        'message' => $this->t('No fields are displayed in this region.'),
      ];
      $new_regions['hidden'] = $regions['hidden'];
      $regions = $new_regions;
    }
    return $regions;
  }

}
