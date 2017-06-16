<?php

namespace Drupal\fb_instant_articles;

/**
 * Ad type constants.
 */
final class AdTypes {

  /**
   * No ads will automatically be inserted into Instant Articles.
   */
  const AD_TYPE_NONE = 'none';

  /**
   * Use Facebook Audience Network for ads.
   */
  const AD_TYPE_FBAN = 'fban';

  /**
   * Use an IFrame as the method of placing ads into Instant Articles.
   */
  const AD_TYPE_SOURCE_URL = 'source_url';

  /**
   * Use a custom embed code for ads placed into Instant Articles.
   */
  const AD_TYPE_EMBED_CODE = 'embed_code';

}
