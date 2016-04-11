<?php

/**
 * @file
 * Contains \Drupal\fb_instant_articles_display\DrupalInstantArticleDisplay.
 */

namespace Drupal\fb_instant_articles;

abstract class AdTypes {
  const FB_INSTANT_ARTICLES_AD_TYPE_NONE = 'None';
  const FB_INSTANT_ARTICLES_AD_TYPE_FBAN = 'Facebook Audience Network';
  const FB_INSTANT_ARTICLES_AD_TYPE_SOURCE_URL = 'Source URL';
  const FB_INSTANT_ARTICLES_AD_TYPE_EMBED_CODE = 'Embed Code';
}
