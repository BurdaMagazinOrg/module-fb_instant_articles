<?php

namespace Drupal\fb_instant_articles;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Registers the fbia_rss format as an application/rss+xml response.
 */
class FbInstantArticlesServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->has('http_middleware.negotiation') && is_a($container->getDefinition('http_middleware.negotiation')
      ->getClass(), '\Drupal\Core\StackMiddleware\NegotiationMiddleware', TRUE)
    ) {
      $container->getDefinition('http_middleware.negotiation')
        ->addMethodCall('registerFormat', [
          'fbia_rss',
          ['application/rss+xml'],
        ]);
    }
  }

}
