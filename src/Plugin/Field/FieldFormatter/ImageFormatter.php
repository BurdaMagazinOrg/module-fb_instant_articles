<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fb_instant_articles\Plugin\Field\InstantArticleFormatterInterface;
use Drupal\fb_instant_articles\Regions;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter as DrupalImageFormatter;
use Facebook\InstantArticles\Elements\Caption;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\Image;
use Facebook\InstantArticles\Elements\InstantArticle;

/**
 * Plugin implementation of the 'fbia_image' formatter.
 *
 * @FieldFormatter(
 *   id = "fbia_image",
 *   label = @Translation("FBIA Image"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ImageFormatter extends DrupalImageFormatter implements InstantArticleFormatterInterface {

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
  public static function defaultSettings() {
    return [
      'image_style' => '',
      'caption' => '',
      'likes' => FALSE,
      'comments' => FALSE,
      'presentation' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['image_style'] = [
      '#title' => $this->t('Image style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('image_style'),
      '#empty_option' => t('None (original image)'),
      '#options' => image_style_options(),
    ];
    $element['caption'] = [
      '#type' => 'checkbox',
      '#description' => $this->t('The caption uses the alt text of the image field.'),
      '#title' => $this->t('Enable caption'),
      '#default_value' => $this->getSetting('caption'),
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
    $element['presentation'] = [
      '#type' => 'select',
      '#title' => $this->t('Presentation'),
      '#default_value' => $this->getSetting('presentation'),
      '#options' => [
        Image::ASPECT_FIT => $this->presentationLabel(Image::ASPECT_FIT),
        Image::ASPECT_FIT_ONLY => $this->presentationLabel(Image::ASPECT_FIT_ONLY),
        Image::FULLSCREEN => $this->presentationLabel(Image::FULLSCREEN),
        Image::NON_INTERACTIVE => $this->presentationLabel(Image::NON_INTERACTIVE),
      ],
      '#empty_option' => $this->t('None'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $image_styles = image_style_options(FALSE);
    // Unset possible 'No defined styles' option.
    unset($image_styles['']);
    // Styles could be lost because of enabled/disabled modules that defines
    // their styles in code.
    $image_style_setting = $this->getSetting('image_style');
    if (isset($image_styles[$image_style_setting])) {
      $summary[] = t('Image style: @style', ['@style' => $image_styles[$image_style_setting]]);
    }
    else {
      $summary[] = t('Original image');
    }
    if ($this->getSetting('caption')) {
      $summary[] = $this->t('Use alt text as caption');
    }
    if ($this->getSetting('likes')) {
      $summary[] = $this->t('Enable Facebook likes');
    }
    if ($this->getSetting('comments')) {
      $summary[] = $this->t('Enable Facebook comments');
    }
    if ($presentation = $this->getSetting('presentation')) {
      $summary[] = $this->t('Presentation: @presentation', ['@presentation' => $this->presentationLabel($presentation)]);
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewInstantArticle(FieldItemListInterface $items, InstantArticle $article, $region, $langcode = NULL) {
    // Need to call parent::prepareView() to populate the entities since it's
    // not otherwise getting called.
    $this->prepareView([$items->getEntity()->id() => $items]);

    /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $items */
    if (empty($images = $this->getEntitiesToView($items, $langcode))) {
      // Early opt-out if the field is empty.
      return;
    }

    /** @var \Drupal\image\ImageStyleInterface $image_style */
    $image_style = $this->imageStyleStorage->load($this->getSetting('image_style'));
    /** @var \Drupal\file\FileInterface[] $images */
    foreach ($images as $delta => $image) {
      $image_uri = $image->getFileUri();
      $url = $image_style ? $image_style->buildUrl($image_uri) : file_create_url($image_uri);
      $article_image = Image::create()
        ->withURL($url);
      if ($this->getSetting('caption') && isset($image->_referringItem) && ($caption = $image->_referringItem->alt)) {
        $article_image->withCaption(
          Caption::create()
            ->appendText($caption)
        );
      }
      if ($this->getSetting('likes')) {
        $article_image->enableLike();
      }
      if ($this->getSetting('comments')) {
        $article_image->enableComments();
      }
      if ($presentation = $this->getSetting('presentation')) {
        $article_image->withPresentation($presentation);
      }
      // Images can either go in the header as the cover image, or in the body.
      if ($region === Regions::REGION_HEADER) {
        $header = $article->getHeader();
        if (!$header) {
          $header = Header::create();
          $article->withHeader($header);
        }
        $header->withCover($article_image);
      }
      else {
        $article->addChild($article_image);
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
      case Image::ASPECT_FIT:
        $label = $this->t('Fit');
        break;

      case Image::ASPECT_FIT_ONLY:
        $label = $this->t('Fit only');
        break;

      case Image::FULLSCREEN:
        $label = $this->t('Fullscreen');
        break;

      case Image::NON_INTERACTIVE:
        $label = $this->t('Non-interactive');
        break;

      default:
        $label = $this->t('None');
        break;
    }
    return $label;
  }

}
