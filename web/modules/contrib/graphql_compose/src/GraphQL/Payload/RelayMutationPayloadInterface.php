<?php

declare(strict_types = 1);

namespace Drupal\graphql_compose\GraphQL\Payload;

/**
 * Response interface used for GraphQL responses.
 */
interface RelayMutationPayloadInterface extends PayloadInterface {

  /**
   * Set the client mutation identifier.
   *
   * @param string|null $client_mutation_id
   *   The unique identifier for the mutation.
   *
   * @return $this
   *   The modified payload.
   *
   * @see \Drupal\graphql_compose\Wrappers\RelayMutationInputInterface::getClientMutationId()
   */
  public function setClientMutationId(?string $client_mutation_id) : self;

  /**
   * Get the client mutation identifier.
   *
   * @see \Drupal\graphql_compose\Wrappers\RelayMutationInputInterface::getClientMutationId()
   */
  public function getClientMutationId() : ?string;

}
