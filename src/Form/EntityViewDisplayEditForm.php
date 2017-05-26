<?php

namespace Drupal\fb_instant_articles\Form;

use Drupal\field_ui\Form\EntityViewDisplayEditForm as CoreEntityViewDisplayEditForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Extends the core EntityViewDisplayEditForm to support multiple regions.
 */
class EntityViewDisplayEditForm extends CoreEntityViewDisplayEditForm {

  /**
   * {@inheritdoc}
   */
  public function getTableHeader() {
    $table_headers = parent::getTableHeader();
    if ($this->getEntity()->getOriginalMode() === 'fb_instant_articles') {
      $region = $this->t('Region');
      array_splice($table_headers, 1, 0, array($region));
    }
    return $table_headers;
  }

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

  /**
   * {@inheritdoc}
   */
  protected function buildFieldRow(FieldDefinitionInterface $field_definition, array $form, FormStateInterface $form_state) {
    $field_row = parent::buildFieldRow($field_definition, $form, $form_state);

    $field_name = $field_definition->getName();
    $display_options = $this->entity->getComponent($field_name);
    if ($this->getEntity()->getOriginalMode() === 'fb_instant_articles') {
      // Insert the label column.
      $region = array(
        'region_column' => array(
          '#type' => 'select',
          '#title' => $this->t('Label display for @title', array('@title' => $field_definition->getLabel())),
          '#title_display' => 'invisible',
          '#options' => ['Header',
                         'Body',
                         'Footer',
          ],
          '#default_value' => $display_options ? $display_options['label'] : 'above',
        ),
      );
      $label_position = array_search('label', array_keys($field_row));
      $field_row = array_slice($field_row, 0, $label_position, TRUE) + $region + array_slice($field_row, $label_position, count($field_row) - 1, TRUE);
      // Update the (invisible) title of the 'plugin' column.
      $field_row['plugin']['#title'] = $this->t('Formatter for @title', array('@title' => $field_definition->getLabel()));
      if (!empty($field_row['plugin']['settings_edit_form']) && ($plugin = $this->entity->getRenderer($field_name))) {
        $plugin_type_info = $plugin->getPluginDefinition();
        $field_row['plugin']['settings_edit_form']['label']['#markup'] = $this->t('Format settings:') . ' <span class="plugin-name">' . $plugin_type_info['label'] . '</span>';
      }
    }

    return $field_row;
  }

}
