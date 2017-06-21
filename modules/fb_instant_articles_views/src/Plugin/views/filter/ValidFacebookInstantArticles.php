<?php

namespace Drupal\fb_instant_articles_views\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Simple filter that checks if a node implements the FIA custom view mode.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("validfacebookinstantarticles")
 */
class ValidFacebookInstantArticles extends FilterPluginBase {

  const FIA_VIEW_MODE = 'fb_instant_articles';

  protected $entityTypeBundleInfo;

  protected $entityTypeManager;

  /**
   * ValidFacebookInstantArticles constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   Entity type bundle info service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct($configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeBundleInfoInterface $entity_type_bundle_info,
                              EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
    $entity_storage = $this->entityTypeManager->getStorage('entity_view_display');
    $node_types = [];
    foreach ($this->entityTypeBundleInfo->getBundleInfo('node') as $id => $bundle) {
      $view_mode_id = 'node.' . $id . '.' . static::FIA_VIEW_MODE;
      $view_mode = $entity_storage->load($view_mode_id);

      if ($view_mode instanceof EntityViewDisplayInterface) {
        $node_types[$id] = $id;
      }
    }

    if (count($node_types) > 0) {
      $this->value = $node_types;
      $this->operator = 'IN';
    }
  }

}
