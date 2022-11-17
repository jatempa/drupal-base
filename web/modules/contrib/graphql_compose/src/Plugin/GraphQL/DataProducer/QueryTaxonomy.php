<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\DataProducer;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\graphql_compose\GraphQL\EntityConnection;
use Drupal\graphql_compose\Plugin\GraphQL\DataProducer\Entity\EntityDataProducerPluginBase;
use Drupal\graphql_compose\GraphQL\QueryHelper\TaxonomyQueryHelper;

/**
 * Queries ContentType Nodes on the platform.
 *
 * @DataProducer(
 *   id = "query_taxonomy_vocabulary",
 *   name = @Translation("Query a list of taxonomy terms"),
 *   description = @Translation("Loads the taxonomy terms."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("EntityConnection")
 *   ),
 *   consumes = {
 *     "first" = @ContextDefinition("integer",
 *       label = @Translation("First"),
 *       required = FALSE
 *     ),
 *     "after" = @ContextDefinition("string",
 *       label = @Translation("After"),
 *       required = FALSE
 *     ),
 *     "last" = @ContextDefinition("integer",
 *       label = @Translation("Last"),
 *       required = FALSE
 *     ),
 *     "before" = @ContextDefinition("string",
 *       label = @Translation("Before"),
 *       required = FALSE
 *     ),
 *     "reverse" = @ContextDefinition("boolean",
 *       label = @Translation("Reverse"),
 *       required = FALSE,
 *       default_value = FALSE
 *     ),
 *     "sortKey" = @ContextDefinition("string",
 *       label = @Translation("Sort key"),
 *       required = FALSE,
 *       default_value = "CREATED_AT"
 *     ),
 *   },
 *   deriver = "Drupal\graphql_compose\Plugin\Derivative\QueryTaxonomyDataProducer"
 * )
 */
class QueryTaxonomy extends EntityDataProducerPluginBase {

  /**
   * Resolves the request to the requested values.
   *
   * @param int|null $first
   *   Fetch the first X results.
   * @param string|null $after
   *   Cursor to fetch results after.
   * @param int|null $last
   *   Fetch the last X results.
   * @param string|null $before
   *   Cursor to fetch results before.
   * @param bool $reverse
   *   Reverses the order of the data.
   * @param string $sortKey
   *   Key to sort by.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   Cacheability metadata for this request.
   *
   * @return \Drupal\graphql_compose\GraphQL\ConnectionInterface
   *   An entity connection with results and data about the paginated results.
   */
  public function resolve(?int $first, ?string $after, ?int $last, ?string $before, bool $reverse, string $sortKey, RefinableCacheableDependencyInterface $metadata) {
    
    $query_helper = new TaxonomyQueryHelper(
      $sortKey,
      $this->entityTypeManager,
      $this->graphqlEntityBuffer,
      $this->getDerivativeId(),
    );

    $metadata->addCacheableDependency($query_helper);

    $connection = new EntityConnection($query_helper);
    $connection->setPagination($first, $after, $last, $before, $reverse);
    return $connection;
  }

}
