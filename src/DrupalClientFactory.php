<?php

namespace Drupal\fb_instant_articles;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Factory class to create a \Drupal\fb_instant_articles\DrupalClient.
 */
class DrupalClientFactory {

  /**
   * Create an instance of DrupalClient.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   Serializer service.
   *
   * @return \Drupal\fb_instant_articles\DrupalClient
   *   Instance of DrupalClient.
   */
  public static function create(ConfigFactoryInterface $config_factory, SerializerInterface $serializer) {
    $config = $config_factory->get('fb_instant_articles.settings');

    $client = DrupalClient::create(
      $config->get('app_id'),
      $config->get('app_secret'),
      $config->get('access_token'),
      $config->get('page_id'),
      $config->get('development_mode') ?? FALSE
    );
    $client->setSerializer($serializer);

    return $client;
  }

}
