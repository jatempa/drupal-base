<?php

declare(strict_types = 1);

namespace Drupal\graphql_compose\GraphQL\Payload;

use Drupal\graphql_compose\GraphQL\ViolationInterface;

/**
 * Response interface used for GraphQL responses.
 */
interface PayloadInterface {

  /**
   * Adds the violation.
   *
   * @param \Drupal\graphql_compose\GraphQL\ViolationInterface $violation
   *   A violation.
   *
   * @return $this
   *   This payload.
   */
  public function addViolation(ViolationInterface $violation): self;

  /**
   * Adds multiple violations.
   *
   * @param array $violations
   *   An array of violations.
   *
   * @return $this
   *   This payload.
   */
  public function addViolations(array $violations): self;

  /**
   * Gets the violations.
   *
   * @return \Drupal\graphql_compose\GraphQL\ViolationInterface[]
   *   The Violations.
   */
  public function getViolations(): array;

}
