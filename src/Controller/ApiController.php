<?php

namespace Drupal\fb_instant_articles\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller to handle Facebook login callback.
 */
class ApiController extends ControllerBase {

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Creates a new \Drupal\fb_instant_articles\Controller\ApiController.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Handle Facebook login callback.
   */
  public function facebookLogin() {
    $config = $this->configFactory->getEditable('fb_instant_articles.settings');
    $fb = new Facebook([
      'app_id' => $config->get('app_id'),
      'app_secret' => $config->get('app_secret'),
      'default_graph_version' => 'v2.5',
    ]);
    $helper = $fb->getRedirectLoginHelper();

    // Grab the user access token based on callback response and report back an
    // error if we weren't able to get one.
    try {
      $access_token = $helper->getAccessToken();

      if ($access_token == NULL) {
        $error_msg = $this->t('We failed to authenticate your Facebook account with this module. Please try again.');
        drupal_set_message($error_msg, 'error');
      }
      else {
        // Confirm that the person granted the necessary permissions before
        // proceeding.
        $permissions = $fb->get('/me/permissions', $access_token)
          ->getGraphEdge();
        $rejected_permissions = [];
        foreach ($permissions as $permission) {
          if ($permission->getField('status') != 'granted') {
            $rejected_permissions[] = $permission->getField('permission');
          }
        }
        if (!empty($rejected_permissions)) {
          $error_msg = $this->t('You did not grant the following required permissions in the Facebook authentication process: @permissions. Please try again.', ['@permissions' => implode(', ', $rejected_permissions)]);
          drupal_set_message($error_msg, 'error');
        }
        else {
          // Store this user access token to the database.
          $config
            ->set('access_token', $access_token->getValue())
            ->save();
          drupal_set_message('Facebook authentication was successful. Access token obtained.');
        }
      }
    }
    catch (FacebookResponseException $e) {
      $error_msg = $this->t('We received the following error while attempting to authenticate your Facebook account: @error', ['@error' => $e->getMessage()]);
      drupal_set_message($error_msg, 'error');
    }
    catch (FacebookSDKException $e) {
      $error_msg = $this->t('We received the following error while attempting to authenticate your Facebook account: @error', ['@error' => $e->getMessage()]);
      drupal_set_message($error_msg, 'error');
    }

    return new RedirectResponse(Url::fromRoute('fb_instant_articles.api_settings_form')->toString());
  }

}
