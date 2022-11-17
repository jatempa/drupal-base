<?php

namespace Drupal\graphql_compose\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;

class QueryUserDataProducer extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    $userTypes = \Drupal::service('graphql_compose.datamanager')->getDefinitions('user');
    foreach ($userTypes as $userType) {
      $this->derivatives[$userType['id']] = $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
