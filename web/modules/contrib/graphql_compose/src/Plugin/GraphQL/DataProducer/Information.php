<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\DataProducer;

use Drupal\Core\Session\AccountInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the user ID for an account instance.
 *
 * @DataProducer(
 *   id = "information",
 *   name = @Translation("GraphQL information"),
 *   description = @Translation("Returns GraphQL information."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Information")
 *   )
 * )
 */
class Information extends DataProducerPluginBase {

  /**
   * Resolves the request to the requested values.
   *
   * @return mixed
   *   The user id.
   */
  public function resolve() {

    return [
      'name' => 'GraphQL Compose',
      'description' => 'Toolkit for generating GraphQL schemas in Drupal',
      'fragments' => \Drupal::service('graphql_compose.datamanager')->getFragmentsAsSdl(),
      'entityTypes' => \Drupal::service('graphql_compose.datamanager')->getEntityTypes(),
    ];
  }

}
