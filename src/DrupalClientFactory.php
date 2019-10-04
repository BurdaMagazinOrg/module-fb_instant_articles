<?php

namespace Drupal\fb_instant_articles;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Drupal\fb_instant_articles\Normalizer\InstantArticleContentEntityNormalizer;

/**
 * Factory class to create a \Drupal\fb_instant_articles\DrupalClient.
 */
class DrupalClientFactory {

  /**
   * Create an instance of DrupalClient.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Symfony\Component\Serializer\Normalizer\NormalizerInterface $serializer
   *   Serializer service. Note that we are type hinting to the
   *   NormalizerInterface, b/c that is the functionality we actually want to
   *   use from the Serializer.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger channel.
   * @param \Drupal\fb_instant_articles\Normalizer\InstantArticleContentEntityNormalizer $ia_normalizer
   *   Instant article normalizer object.
   *
   * @return \Drupal\fb_instant_articles\DrupalClient
   *   Instance of DrupalClient.
   *
   * @throws \Drupal\fb_instant_articles\MissingApiCredentialsException
   * @throws \Facebook\Exceptions\FacebookSDKException
   */
  public static function create(ConfigFactoryInterface $config_factory, NormalizerInterface $serializer, LoggerChannelInterface $logger, InstantArticleContentEntityNormalizer $ia_normalizer) {
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

    $client->setSerializer($serializer);
    $client->setLogger($logger);
    $client->setIaNormalizer($ia_normalizer);

    return $client;
  }

}
