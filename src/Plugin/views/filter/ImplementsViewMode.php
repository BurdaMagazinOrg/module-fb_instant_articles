<?php

/**
 * @file
 * Contains \Drupal\views\Plugin\views\filter\BooleanOperator.
 */

namespace Drupal\facebook_instant_articles\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\Bundle
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;

/**
 * Simple filter that checks if a node implements the FIA custom view mode
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("facebookinstantarticles")
 */
class FIA_ImplementsViewMode extends Bundle {

  const FIA_VIEW_MODE = 'facebook_instant_articles_rss';

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    return $this->t('Filtering for items that have custom facebook view mode settings');
  }

  /**
   * {@inheritdoc}
   */
  public function canExpose() { return FALSE; }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    $this->operator = "in";
  }

  /**
   * {@inheritdoc}
   *
   *
   */
  public function query() {
    parent::query();

    $this->enabledNodeBundlesSetValues();
    $this->query->addWhere($this->options['group'], "$this->tableAlias.$this->realField", array_values($this->value), $this->operator);
  }

  /**
   * Set the values for this filter to all node bundles that implement custom settings for the view mode
   */
  protected function enabledNodeBundlesSetValues() {
//    /**
//     * @var \Drupal\Core\Entity\EntityTypeManager $typeManager
//     */
//    $varManager = Drupal::service('entity_type_manager');

    /**
     * @var \Drupal\Core\Entity\EntityDisplayRepository $displayRepository
     * entity_display.repository
     */
    $displayRepository = Drupal::service('entity_display.repository');

    /**
     * @var string[] nodeTypes
     *   an array of node types that implement our custom view mode
     */
    $this->value = [];

    foreach ($displayRepository->getAllViewModes() as $id=>$mode) {
      $this->value[$id] = $id;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function operatorForm(&$form, FormStateInterface $form_state) {
    // do nothing
  }

}