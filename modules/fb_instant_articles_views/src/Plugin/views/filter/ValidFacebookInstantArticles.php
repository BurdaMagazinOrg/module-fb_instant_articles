<?php

namespace Drupal\fb_instant_articles_views\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Simple filter that checks if a node implements the FIA custom view mode.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("validfacebookinstantarticles")
 */
class ValidFacebookInstantArticles extends FilterPluginBase implements ContainerFactoryPluginInterface{

  const FIA_VIEW_MODE = 'fb_instant_articles';

  /**
   * ValidFacebookInstantArticles constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct($configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeBundleInfoInterface $entity_type_bundle_info,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    return $this->t('Filtering for items that have custom facebook view mode settings');
  }

  /**
   * {@inheritdoc}
   */
  public function canExpose() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function canBuildGroup() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
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
    $this->valueTitle = t('Facebook Instant Articles');
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->enabledNodeBundlesSetValues();
    parent::query();
  }

  /**
   * Set the values for this filter.
   *
   * This applies to all node bundles that implement custom settings for the
   * fb_instant_articles view mode.
   */
  protected function enabledNodeBundlesSetValues() {
    /*
     * @var \Drupal\Core\Entity\EntityTypeBundleInfo $entity_bundle_info
     *  entity_type.bundle.info
     */
    $entity_bundle_info = $this->entityTypeBundleInfo;

    /*
     * @var \Drupal\Core\Entity\EntityStorageInterface $entity_storage
     *   View Mode entity storage handler
     */
    $entity_storage = $this->entityTypeManager->getStorage('entity_view_display');

    /*
     * @var string[] nodeTypes
     *   an array of node types that implement our custom view mode
     */
    $node_types = [];
    foreach ($entity_bundle_info->getBundleInfo('node') as $id => $bundle) {

      /*
       * @var string $view_mode_id
       *   the string id for the view mode entity
       */
      $view_mode_id = 'node.' . $id . '.' . static::FIA_VIEW_MODE;

      /*
       * @var \Drupal\Core\Entity\EntityInterface|null $view_mode
       *   Config entity for the view mode, if it exists
       */
      $view_mode = $entity_storage->load($view_mode_id);

      if ($view_mode instanceof EntityViewDisplayInterface) {
        $node_types[$id] = $id;
      }
    }

    if (count($node_types) > 0) {
      /*
       * Only set the value and operator if we have some valid node types, so
       * that we don't break the query.  Leaving them as they are will result
       * in an empty query, which is good
       */
      $this->value = $node_types;
      $this->operator = "in";
    }
  }

}
