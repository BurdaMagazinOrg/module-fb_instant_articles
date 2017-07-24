<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface;
use Drupal\fb_instant_articles\Regions;
use Drupal\file\Plugin\Field\FieldFormatter\GenericFileFormatter;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Video;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'fbia_video' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_video",
 *   label = @Translation("FBIA Video"),
 *   field_types = {
 *     "file",
 *   }
 * )
 */
class VideoFormatter extends GenericFileFormatter implements InstantArticleFormatterInterface, ContainerFactoryPluginInterface {

  /**
   * Base settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, ConfigFactoryInterface $config_factory) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->config = $config_factory->get('fb_instant_articles.settings');
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
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'presentation' => '',
      'likes' => FALSE,
      'comments' => FALSE,
      'controls' => FALSE,
      'autoplay' => TRUE,
      'feed_cover' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['presentation'] = [
      '#type' => 'select',
      '#title' => $this->t('Presentation'),
      '#default_value' => $this->getSetting('presentation'),
      '#options' => [
        Video::ASPECT_FIT => $this->presentationLabel(Video::ASPECT_FIT),
        Video::ASPECT_FIT_ONLY => $this->presentationLabel(Video::ASPECT_FIT_ONLY),
        Video::FULLSCREEN => $this->presentationLabel(Video::FULLSCREEN),
        Video::NON_INTERACTIVE => $this->presentationLabel(Video::NON_INTERACTIVE),
      ],
      '#empty_option' => $this->t('None'),
    ];
    $element['likes'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Facebook likes'),
      '#default_value' => $this->getSetting('likes'),
    ];
    $element['comments'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Facebook comments'),
      '#default_value' => $this->getSetting('comments'),
    ];
    $element['controls'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable player controls'),
      '#default_value' => $this->getSetting('controls'),
    ];
    $element['autoplay'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable autoplay'),
      '#default_value' => $this->getSetting('autoplay'),
    ];
    $element['feed_cover'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Newsfeed cover'),
      '#description' => $this->t('Enable this setting to use this video as the cover when the article is shown in the newsfeed. If set, the first video in a multivalue field is used as the cover.'),
      '#default_value' => $this->getSetting('feed_cover'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if ($presentation = $this->getSetting('presentation')) {
      $summary[] = $this->t('Presentation: @presentation', ['@presentation' => $this->presentationLabel($presentation)]);
    }
    if ($this->getSetting('likes')) {
      $summary[] = $this->t('Enable Facebook likes');
    }
    if ($this->getSetting('comments')) {
      $summary[] = $this->t('Enable Facebook comments');
    }
    if ($this->getSetting('controls')) {
      $summary[] = $this->t('Show controls');
    }
    if ($this->getSetting('autoplay')) {
      $summary[] = $this->t('Autoplay');
    }
    if ($this->getSetting('feed_cover')) {
      $summary[] = $this->t('Newsfeed cover');
    }
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
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, $langcode = NULL) {
    // Need to call parent::prepareView() to populate the entities since it's
    // not otherwise getting called.
    $this->prepareView([$items->getEntity()->id() => $items]);

    /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $items */
    if (empty($videos = $this->getEntitiesToView($items, $langcode))) {
      // Early opt-out if the field is empty.
      return;
    }

    // Prepare the header region if we're adding to the header.
    if ($region === Regions::REGION_HEADER) {
      $header = $article->getHeader();
      if (!$header) {
        $header = Header::create();
        $article->withHeader($header);
      }
    }

    /** @var \Drupal\file\FileInterface[] $videos */
    foreach ($videos as $delta => $video) {
      // Build the Video Element using configured settings.
      $video_uri = file_create_url($video->getFileUri());
      // Use the canonical URL override if set.
      if ($canonical_url = $this->config->get('canonical_url_override')) {
        $video_uri = preg_replace('~^https?://.*?(?=/)~', rtrim($canonical_url, '/'), $video_uri);
      }
      $video = Video::create();
      $video->withURL($video_uri);
      if ($presentation = $this->getSetting('presentation')) {
        $video->withPresentation($presentation);
      }
      if ($this->getSetting('likes')) {
        $video->enableLike();
      }
      else {
        $video->disableLike();
      }
      if ($this->getSetting('comments')) {
        $video->enableComments();
      }
      else {
        $video->disableComments();
      }
      if ($this->getSetting('controls')) {
        $video->enableControls();
      }
      else {
        $video->disableControls();
      }
      if ($this->getSetting('autoplay')) {
        $video->enableAutoplay();
      }
      else {
        $video->disableAutoplay();
      }
      // If this field is marked as the Newsfeed cover, use only the first video
      // in a multivalue field as the Newsfeed cover.
      if ($this->getSetting('feed_cover') && $delta === 0) {
        $video->enableFeedCover();
      }
      else {
        $video->disableFeedCover();
      }
      // Finally add the video to the article, either as the cover, or in the
      // content of the article per the $region param.
      if ($region === Regions::REGION_HEADER) {
        $header->withCover($video);
      }
      else {
        $article->addChild($video);
      }
    }
  }

  /**
   * Given a presentation machine name, return the label.
   *
   * @param string $presentation
   *   Presentation type name.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   String label.
   */
  protected function presentationLabel($presentation) {
    switch ($presentation) {
      case Video::ASPECT_FIT:
        $label = $this->t('Fit');
        break;

      case Video::ASPECT_FIT_ONLY:
        $label = $this->t('Fit only');
        break;

      case Video::FULLSCREEN:
        $label = $this->t('Fullscreen');
        break;

      case Video::NON_INTERACTIVE:
        $label = $this->t('Non-interactive');
        break;

      default:
        $label = $this->t('None');
        break;
    }
    return $label;
  }

}
