<?php

/**
 * @file
 * Contains \Drupal\views\Plugin\views\filter\ImplementsViewMode.
 */

namespace Drupal\fb_instant_articles\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Simple filter that checks if a node implements the FIA custom view mode
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("validfacebookinstantarticles")
 */
class ValidFacebookInstantArticles extends FilterPluginBase {

  const FIA_VIEW_MODE = 'fb_instant_articles_rss';

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
  public function canBuildGroup() { return FALSE; }
  /**
   * Provide the basic form which calls through to subforms.
   * If overridden, it is best to call through to the parent,
   * or to at least make sure all of the functions in this form
   * are called.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['info'] = [
      '#type' => 'markup',
      '#markup' => 'Filter for nodes that implement the FIA view mode',
      '#prefix' => '<div class="clearfix">',
      '#suffix' => '</div>',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = t('Facebook Instant articles');
  }

  /**
   * {@inheritdoc}
   *
   *
   */
  public function query() {

    $this->enabledNodeBundlesSetValues();
    parent::query();
  }

  /**
   * Set the values for this filter to all node bundles that implement custom settings for the view mode
   *
   * This uses an unreliable method for detecting custom settings for node bundles.  The method is to check for a config
   * Entity for the entity display settings, for the bundle/view mode.  Essentially this looks for a yml file/record
   * for the custom settings, and one is found, the bundle is considered FIA active.
   *
   */
  protected function enabledNodeBundlesSetValues() {
    /**
     * @var \Drupal\Core\Entity\EntityTypeBundleInfo $entityBundleInfo
     *  entity_type.bundle.info
     */
    $entityBundleInfo = \Drupal::service('entity_type.bundle.info');

    /**
     * @var \Drupal\Core\Entity\EntityStorageInterface $entityStorage
     *   View Mode entity storage handler
     */
    $entityStorage = \Drupal::service('entity_type.manager')->getStorage('entity_view_display');

    /**
     * @var string[] nodeTypes
     *   an array of node types that implement our custom view mode
     */
    $nodeTypes = [];
    foreach($entityBundleInfo->getBundleInfo('node') as $id=>$bundle) {
      /**
       * @var string $viewModeId
       *   the string id for the view mode entity
       */
      $viewModeId = 'node.'.$id.'.'.static::FIA_VIEW_MODE;

      /**
       * @var \Drupal\Core\Entity\EntityInterface|null $viewMode
       *   Config entity for the view mode, if it exists
       */
      $viewMode = $entityStorage->load($viewModeId);

      if ($viewMode instanceof EntityInterface) {
        $nodeTypes[$id] = $id;
      }
    }

    if (count($nodeTypes)>0) {
      /**
       * Only set the value and operator if we have some valid node types, so
       * that we don't break the query.  Leaving them as they are will result
       * in an empty query, which is good
       */
      $this->value = $nodeTypes;
      $this->operator = "in";
    }
  }

}