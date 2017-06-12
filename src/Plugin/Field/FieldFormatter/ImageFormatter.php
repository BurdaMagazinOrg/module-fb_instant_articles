<?php

namespace Drupal\fb_instant_articles\Plugin\Field\FieldFormatter;
use Drupal\Core\Form\FormStateInterface;
use Facebook\InstantArticles\Elements\Image;

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
class ImageFormatter extends FormatterBase {

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
    ] + parent::defaultSettings();
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
    $image_styles = image_style_options();
    if ($image_style = $this->getSetting('image_style')) {
      $summary[] = $this->t('Image style: @style', ['@style' => $image_styles[$image_style]]);
    }
    else {
      $summary[] = $this->t('Original image');
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
