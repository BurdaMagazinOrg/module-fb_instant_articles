<?php
/**
 * @file
 * Functions for FBIA formatter output.
 */

/**
 * Formatter for standard elements.
 */
function fb_instant_articles_field_format_standard_element($items, $wrapper_tag = 'p', $class = '') {
  $element = array();

  if (!empty($class)) {
    $class = ' class="' . $class . '"';
  }

  foreach ($items as $delta => $item) {
    $element[$delta]['#markup'] = '<' . $wrapper_tag . $class . '>' . $item['safe_value'] . '</' . $wrapper_tag . '>';
  }
  return $element;
}

/**
 * Formatter for the Ad element.
 */
function fb_instant_articles_field_format_ad_element($items, $settings) {
  $element = array();

  foreach ($items as $delta => $item) {
    $attributes = "";

    if (!empty($settings['height'])) {
      $attributes .= ' height="' . $settings['height'] . '"';
    }
    if (!empty($settings['width'])) {
      $attributes .= ' width="' . $settings['width'] . '"';
    }
    switch ($settings['source']) {
      case 'embed':
        $element[$delta]['#markup'] = '<figure class="op-ad"><iframe ' . $attributes . '>' . $item['safe_value'] . '</iframe></figure>';
        break;

      default:
        $element[$delta]['#markup'] = '<figure class="op-ad"><iframe src="' . $item['safe_value'] . '"' . $attributes . ' /></figure>';
        break;
    }
  }
  return $element;
}

/**
 * Formatter for the Analytics element.
 */
function fb_instant_articles_field_format_analytics_element($items, $settings) {
  $element = array();
  foreach ($items as $delta => $item) {
    switch ($settings['source']) {
      case 'embed':
        $element[$delta]['#markup'] = '<figure class="op-tracker"><iframe>' . $item['safe_value'] . '</iframe></figure>';
        break;

      default:
        $element[$delta]['#markup'] = '<figure class="op-tracker"><iframe src="' . $item['safe_value'] . '" /></figure>';
        break;

    }
  }
  return $element;
}

/**
 * Formatter for the Image element.
 */
function fb_instant_articles_field_format_image_element($items, $settings) {
  $element = array();

  foreach ($items as $delta => $item) {
    $image_settings = array(
      'style_name' => $settings['style'],
      'path' => $item['uri'],
    );
    $image = theme('image_style', $image_settings);

    $caption = "";
    if (!empty($settings['caption'])) {
      $caption = '<figcaption>' . $item['alt'] . '</figcaption>';
    }

    $data_feedback = array();
    $data_feedback_attribute = '';
    if (!empty($settings['likes'])) {
      $data_feedback[] = 'fb:likes';
    }
    if (!empty($settings['comments'])) {
      $data_feedback[] = 'fb:comments';
    }
    if (!empty($data_feedback)) {
      $data_feedback_attribute = ' data-feedback="' . implode(',', $data_feedback) . '"';
    }

    $data_mode_fullscreen = '';
    if (!empty($settings['fullscreen'])) {
      $data_mode_fullscreen = ' data-mode="fullscreen"';
    }

    $output = '<figure' . $data_feedback_attribute . $data_mode_fullscreen . '>' . $image . $caption . '</figure>';
    $element[$delta]['#markup'] = $output;
  }

  return $element;
}

/**
 * Formatter for the Interactive element.
 */
function fb_instant_articles_field_format_interactive_element($items, $settings) {
  $element = array();

  foreach ($items as $delta => $item) {
    $attributes = "";

    if (!empty($settings['height'])) {
      $attributes .= ' height="' . $settings['height'] . '"';
    }
    if (!empty($settings['width'])) {
      $attributes .= ' class="' . $settings['width'] . '"';
    }

    $element[$delta]['#markup'] = '<figure class="op-interactive"><iframe src="' . $item['safe_value'] . '"' . $attributes . ' /></figure>';
  }
  return $element;
}

/**
 * Formatter for the List element.
 */
function fb_instant_articles_field_format_list_element($items, $settings) {
  $element = array();

  if (empty($items) || count($items) == 0) {
    return $element;
  }

  $list_type = !empty($settings['list_type']) ? $settings['list_type'] : 'ol';

  $list_markup = '<' . $list_type . '>';

  foreach ($items as $delta => $item) {
    $list_markup .= '<li>' . $item['value'] . '</li>';
  }

  $list_markup .= '</' . $list_type . '>';

  $element[$delta]['#markup'] = $list_markup;
  return $element;
}

/**
 * Formatter for the Video element.
 */
function fb_instant_articles_field_format_video_element($items, $settings) {
  $element = array();

  foreach ($items as $delta => $item) {
    $element[$delta]['#markup'] = '<figure><video><source src="' . file_create_url($item['uri']) . '" type="' . $item['filemime'] . '" /></video></figure>';
  }
  return $element;
}

/**
 * Formatter for the Social element.
 */
function fb_instant_articles_field_format_social_element($items, $settings) {
  $element = array();

  foreach ($items as $delta => $item) {
    $element[$delta]['#markup'] = '<figure class="op-social"><iframe>' . $item['safe_value'] . '</iframe></figure>';
  }

  return $element;
}
