<?php

namespace Drupal\fb_instant_articles\Form;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\fb_instant_articles\Regions;
use Drupal\field_ui\Form\EntityViewDisplayEditForm as CoreEntityViewDisplayEditForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;

/**
 * Extends the core EntityViewDisplayEditForm to support multiple regions.
 */
class EntityViewDisplayEditForm extends CoreEntityViewDisplayEditForm {

  /**
   * Name of the FBIA view mode.
   */
  const FBIA_VIEW_MODE = 'fb_instant_articles';

  /**
   * {@inheritdoc}
   */
  public function getRegions() {
    $regions = parent::getRegions();
    if ($this->getEntity()->getOriginalMode() === 'fb_instant_articles') {
      $new_regions[Regions::REGION_HEADER] = [
        'title' => $this->t('Header'),
        'message' => $this->t('No fields are displayed in this region.'),
      ];
      $new_regions[Regions::REGION_CONTENT] = [
        'title' => $this->t('Body'),
        'message' => $this->t('No fields are displayed in this region.'),
      ];
      $new_regions[Regions::REGION_FOOTER] = [
        'title' => $this->t('Footer'),
        'message' => $this->t('No fields are displayed in this region.'),
      ];
      $new_regions['hidden'] = $regions['hidden'];
      $regions = $new_regions;
    }
    return $regions;
  }

  /**
   * {@inheritdoc}
   */
  protected function getApplicablePluginOptions(FieldDefinitionInterface $field_definition) {
    $options = parent::getApplicablePluginOptions($field_definition);
    // Filter out FBIA formatters for view modes other than the Facebook instant
    // articles view mode.
    if ($this->getEntity()->getOriginalMode() !== 'fb_instant_articles') {
      foreach ($options as $key => $label) {
        if (preg_match('/^fbia_/', $key)) {
          unset($options[$key]);
        }
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Way to get the fbia rss cache tag dynamically from here?
    Cache::invalidateTags(['config:views.view.facebook_instant_articles_rss']);
    return parent::save($form, $form_state);
  }

}
