<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\DataProducer\Payload;

use Drupal\graphql\Plugin\DataProducerPluginCachingInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\graphql_compose\GraphQL\Payload\PayloadInterface;
use Drupal\graphql_compose\GraphQL\ViolationInterface;

/**
 * Returns the violations in a payload.
 *
 * @DataProducer(
 *   id = "payload_violations",
 *   name = @Translation("Payload Violations"),
 *   description = @Translation("Returns the violations from a payload."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Violations")
 *   ),
 *   consumes = {
 *     "payload" = @ContextDefinition("any",
 *       label = @Translation("Payload")
 *     )
 *   }
 * )
 */
class PayloadViolations extends DataProducerPluginBase implements DataProducerPluginCachingInterface {

  /**
   * Resolves the value for this data producer.
   *
   * @param \Drupal\graphql_compose\GraphQL\Payload\PayloadInterface $payload
   *   The payload to return violations for..
   *
   * @return null|\Drupal\graphql_compose\GraphQL\ViolationInterface[]
   *   The violations for this payload or null if there are none.
   */
  public function resolve(PayloadInterface $payload) {
    // Explicitly turn empty arrays into NULL so that clients can perform an
    // is_null check to figure out if there are errors.
    $violations = $payload->getViolations();
    return empty($violations) ? NULL : array_map(fn (ViolationInterface $v) => $v->jsonSerialize(), $violations);
  }

}
