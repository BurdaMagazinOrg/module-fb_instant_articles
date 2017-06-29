<?php

namespace Drupal\fb_instant_articles;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Factory class to create a \Drupal\fb_instant_articles\DrupalClient.
 */
class DrupalClientFactory {

  /**
   * Create an instance of DrupalClient.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   *
   * @return \Drupal\fb_instant_articles\DrupalClient
   *   Instance of DrupalClient.
   *
   * @throws \Drupal\fb_instant_articles\MissingApiCredentialsException
   */
  public static function create(ConfigFactoryInterface $config_factory) {
    $config = $config_factory->get('fb_instant_articles.settings');

    $app_id = $config->get('app_id');
    $app_secret = $config->get('app_secret');
    $access_token = $config->get('access_token');

    if (empty($app_id) || empty($app_secret) || empty($access_token)) {
      throw new MissingApiCredentialsException('The Facebook Instant Articles API has not been configured yet.');
    }

    $client = DrupalClient::create(
      $config->get('app_id'),
      $config->get('app_secret'),
      $config->get('access_token'),
      $config->get('page_id'),
      $config->get('development_mode') ? TRUE : FALSE
    );

    return $client;
  }

}
