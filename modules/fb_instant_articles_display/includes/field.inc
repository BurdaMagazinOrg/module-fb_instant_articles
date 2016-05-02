<?php

/**
 * @file
 * Field module functionality for Facebook Instant Articles Display module.
 */

/**
 * Implements hook_field_formatter_view().
 */
function fb_instant_articles_display_field_formatter_view($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
  // Grab the InstantArticle object we're going to be building up. If it's not
  // present, it means that a site admin selected an FB IA field formatter for
  // a non-FB IA content type.
  if (!isset($entity->fb_instant_article_wrapper)) {
    return FALSE;
  }

  $article = $entity->fb_instant_article_wrapper->getArticle();
  list(, , $bundle) = entity_extract_ids($entity_type, $entity);
  $layout_settings = fb_instant_articles_display_get_layout_settings($entity_type, $bundle);
  \Drupal\fb_instant_articles_display\EntityFieldMapper::create($layout_settings, $article)
    ->map($field, $instance, $langcode, $items, $display);
}

/**
 * Implements hook_field_formatter_info().
 */
function fb_instant_articles_display_field_formatter_info() {
  $formats = array();
  // Header only elements
  $formats['fbia_subtitle_formatter'] = array(
    'label' => t('FBIA Subtitle'),
    'field types' => array('text', 'text_long', 'text_with_summary'),
  );
  $formats['fbia_kicker_formatter'] = array(
    'label' => t('FBIA Kicker'),
    'field types' => array('text', 'text_long', 'text_with_summary'),
  );
  $formats['fbia_blockquote_formatter'] = array(
    'label' => t('FBIA Blockquote'),
    'field types' => array('text', 'text_long', 'text_with_summary'),
  );
  $formats['fbia_pullquote_formatter'] = array(
    'label' => t('FBIA Pullquote'),
    'field types' => array('text', 'text_long', 'text_with_summary'),
  );
  $formats['fbia_social_formatter'] = array(
    'label' => t('FBIA Social Embed'),
    'field types' => array('text', 'text_long', 'text_with_summary'),
  );
  $formats['fbia_credits_formatter'] = array(
    'label' => t('FBIA Credits'),
    'field types' => array('text', 'text_long', 'text_with_summary'),
  );
  $formats['fbia_copyright_formatter'] = array(
    'label' => t('FBIA Copyright'),
    'field types' => array('text', 'text_long', 'text_with_summary'),
  );
  $formats['fbia_author_formatter'] = array(
    'label' => t('FBIA Author'),
    'field types' => array('text', 'text_with_summary', 'list_text'),
  );
  $formats['fbia_video_formatter'] = array(
    'label' => t('FBIA Video'),
    'field types' => array('file'),
  );
  $formats['fbia_ad_formatter'] = array(
    'label' => t('FBIA Ad'),
    'field types' => array('text', 'text_long', 'text_with_summary'),
    'settings'  => array(
      'height' => '50',
      'width' => '320',
      'source' => 'url',
    ),
  );
  $formats['fbia_analytics_formatter'] = array(
    'label' => t('FBIA Analytics'),
    'field types' => array('text', 'text_long', 'text_with_summary'),
    'settings'  => array(
      'source' => 'url',
    ),
  );
  $formats['fbia_image_formatter'] = array(
    'label' => t('FBIA Image'),
    'field types' => array('image'),
    'settings'  => array(
      'style' => 'medium',
      'caption' => '',
      'likes' => '',
      'comments' => '',
      'fullscreen' => '',
    ),
  );
  $formats['fbia_interactive_formatter'] = array(
    'label' => t('FBIA Interactive'),
    'field types' => array('text', 'text_with_summary'),
    'settings'  => array(
      'height' => '50',
      'width' => 'no-margin',
    ),
  );
  $formats['fbia_list_formatter'] = array(
    'label' => t('FBIA List'),
    'field types' => array('list_text', 'list_integer', 'list_float'),
    'settings'  => array(
      'list_type' => 'ol',
    ),
  );
  $formats['fbia_transformer_formatter'] = array(
    'label' => t('FBIA Transfomer'),
    'field types' => array('text', 'text_long', 'text_with_summary'),
  );
  return $formats;
}

/**
 * Implements hook_field_formatter_settings_form().
 */
function fb_instant_articles_display_field_formatter_settings_form($field, $instance, $view_mode, $form, &$form_state) {

  $display = $instance['display'][$view_mode];
  $settings = $display['settings'];
  $element = array();

  switch ($display['type']) {

    case 'fbia_ad_formatter':
      $element['source'] = array(
        '#type' => 'select',
        '#title' => t('Source'),
        '#description' => t('Add your ad specifying the URL or embed the full unescaped HTML.'),
        '#default_value'  => $settings['source'],
        '#options' => array(
          'url'  => t('Ad URL'),
          'embed' => t('Embedded HTML'),
        ),
      );
      $element['height'] = array(
        '#type' => 'textfield',
        '#title' => t('Height'),
        '#description' => t('Height of the iframe element.'),
        '#default_value' => $settings['height'],
      );
      $element['width'] = array(
        '#type' => 'textfield',
        '#title' => t('Width'),
        '#description' => t('Width of the iframe element.'),
        '#default_value' => $settings['width'],
      );
      break;

    case 'fbia_interactive_formatter':
      $element['height'] = array(
        '#type' => 'textfield',
        '#title' => t('Height'),
        '#description' => t('The height of your interactive graphic.'),
        '#default_value' => $settings['height'],
      );

      $element['width'] = array(
        '#type' => 'select',
        '#title' => t('Width'),
        '#description' => t('The width of your interactive graphic.'),
        '#default_value' => $settings['width'],
        '#options' => array(
          'no-margin' => t('no-margin'),
          'column-width' => t('column-width'),
        ),
      );
      break;

    case 'fbia_list_formatter':
      $element['list_type'] = array(
        '#type' => 'select',
        '#title' => t('List Type'),
        '#description' => t('Choose list type.'),
        '#default_value' => $settings['list_type'],
        '#options' => array(
          'ol' => t('Ordered'),
          'ul' => t('Unordered'),
        ),
      );
      break;

    case 'fbia_image_formatter':
      $image_styles = image_style_options(FALSE, PASS_THROUGH);
      $element['style'] = array(
        '#title' => t('Image style'),
        '#type' => 'select',
        '#default_value' => $settings['style'],
        '#empty_option' => t('None (original image)'),
        '#options' => $image_styles,
      );
      $element['caption'] = array(
        '#type' => 'checkbox',
        '#description' => t('The caption uses the alt text of the image field.'),
        '#title' => t('Enable caption.'),
        '#default_value' => $settings['caption'],
      );
      $element['likes'] = array(
        '#type' => 'checkbox',
        '#title' => t('Enable Facebook Likes. (data-feedback)'),
        '#default_value' => $settings['likes'],
      );
      $element['comments'] = array(
        '#type' => 'checkbox',
        '#title' => t('Enable Facebook Comments. (data-feedback)'),
        '#default_value' => $settings['comments'],
      );
      $element['fullscreen'] = array(
        '#type' => 'checkbox',
        '#title' => t('Enable Fullscreen.'),
        '#default_value' => $settings['fullscreen'],
      );
      break;

    case 'fbia_analytics_formatter':
      $element['source'] = array(
        '#type' => 'select',
        '#title' => t('Source'),
        '#description' => t('Add your tracker specifying the URL or embed the full unescaped HTML.'),
        '#default_value'  => $settings['source'],
        '#options' => array(
          'url' => t('Ad URL'),
          'embed' => t('Embedded HTML'),
        ),
      );
      break;

  }

  return $element;
}

/**
 * Implements hook_field_formatter_settings_summary().
 */
function fb_instant_articles_display_field_formatter_settings_summary($field, $instance, $view_mode) {
  $display = $instance['display'][$view_mode];
  $settings = $display['settings'];
  $summary = '';
  switch ($display['type']) {
    case 'fbia_ad_formatter':
      $summary = t('Ad field - set the Source, iFrame height (@height) and width (@width).', array(
        '@height' => $settings['height'],
        '@width' => $settings['width'],
      ));
      break;

    case 'fbia_interactive_formatter':
      $summary = t('Interactive field - set iFrame height (@height) and width (@width).', array(
        '@height' => $settings['height'],
        '@width' => $settings['width'],
      ));
      break;

    case 'fbia_analytics_formatter':
      $summary = t('Analytics field - set the Source of the iframe. (@source)', array(
        '@source' => $settings['source'],
      ));
      break;

    case 'fbia_image_formatter':
      $image_styles = image_style_options(FALSE, PASS_THROUGH);
      // Unset possible 'No defined styles' option.
      unset($image_styles['']);
      // Styles could be lost because of enabled/disabled modules that defines
      // their styles in code.
      if (isset($image_styles[$settings['style']])) {
        $summary = t('Image style: @style', array('@style' => $image_styles[$settings['style']]));
      }
      else {
        $summary = t('Original image');
      }
      break;

    case 'fbia_list_formatter':
      $summary = t('List field - Type:(@list_type)', array(
        '@list_type' => $settings['list_type'],
      ));
      break;
  }
  return $summary;
}
