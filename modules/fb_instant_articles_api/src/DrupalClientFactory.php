<?php

namespace Drupal\fb_instant_articles_api;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Factory class to create a \Drupal\fb_instant_articles_api\DrupalClient.
 */
class DrupalClientFactory {

  /**
   * Create an instance of DrupalClient.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   *
   * @return \Drupal\fb_instant_articles_api\DrupalClient
   *   Instance of DrupalClient.
   */
  public static function create(ConfigFactoryInterface $config_factory) {
    $baseConfig = $config_factory->get('fb_instant_articles.base_settings');
    $apiConfig = $config_factory->get('fb_instant_articles_api.settings');

    return DrupalClient::create(
      $apiConfig->get('app_id'),
      $apiConfig->get('app_secret'),
      $apiConfig->get('access_token'),
      $baseConfig->get('page_id'),
      $apiConfig->get('development_mode') ?? FALSE
    );
  }

}
