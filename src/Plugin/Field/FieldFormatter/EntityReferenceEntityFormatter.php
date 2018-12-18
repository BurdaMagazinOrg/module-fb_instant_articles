<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface;
use Facebook\InstantArticles\Elements\InstantArticle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Instant articles field formatter for entity reference fields.
 *
 * Like its render array cousin,
 * \Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceEntityFormatter
 * this will iterate over any entity references and render them each in turn,
 * only here the serializer is used to do the work.
 *
 * @FieldFormatter(
 *   id = "fbia_entity_reference_entity_view",
 *   label = @Translation("FBIA rendered entity"),
 *   description = @Translation("Append referenced entities to the instant article."),
 *   field_types = {
 *     "entity_reference",
 *     "entity_reference_revisions"
 *   }
 * )
 */
class EntityReferenceEntityFormatter extends EntityReferenceFormatterBase implements InstantArticleFormatterInterface, ContainerFactoryPluginInterface {

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Constructs a EntityReferenceEntityFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityDisplayRepositoryInterface $entity_display_repository) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'view_mode' => 'default',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements['view_mode'] = [
      '#type' => 'select',
      '#options' => $this->entityDisplayRepository->getViewModeOptions($this->getFieldSetting('target_type')),
      '#title' => $this->t('View mode'),
      '#default_value' => $this->getSetting('view_mode'),
      '#required' => TRUE,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $view_modes = $this->entityDisplayRepository->getViewModeOptions($this->getFieldSetting('target_type'));
    $view_mode = $this->getSetting('view_mode');
    $summary[] = $this->t('Rendered as @mode', ['@mode' => isset($view_modes[$view_mode]) ? $view_modes[$view_mode] : $view_mode]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Do nothing. Our field formatters are unique in that they are not meant
    // to generate HTML on their own, but only signal to the Serialization API
    // the fate of this field in the FBIA document.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, NormalizerInterface $normalizer, $langcode = NULL) {
    // Need to call parent::prepareView() to populate the entities since it's
    // not otherwise getting called.
    $this->prepareView([$items->getEntity()->id() => $items]);

    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $items */
    $view_mode = $this->getSetting('view_mode');

    foreach ($this->getEntitiesToView($items, $langcode) as $entity) {
      $normalizer->normalize($entity, 'fbia', [
        'instant_article' => $article,
        'view_mode' => $view_mode,
      ]);
    }
  }

}
