<?php

namespace Drupal\graphql_compose\GraphQL\QueryHelper;

use GraphQL\Deferred;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use Drupal\graphql_compose\GraphQL\ConnectionQueryHelperBase;
use Drupal\graphql_compose\Wrappers\Cursor;
use Drupal\graphql_compose\Wrappers\Edge;

/**
 * Load nodes.
 */
class TaxonomyQueryHelper extends ConnectionQueryHelperBase {

  /**
   * The key that is used for filtering.
   */
  protected string $vocabulary;

  /**
   * Create a new connection query helper.
   *
   * @param string $sort_key
   *   The key that is used for sorting.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Drupal entity type manager.
   * @param \Drupal\graphql\GraphQL\Buffers\EntityBuffer $graphql_entity_buffer
   *   The GraphQL entity buffer.
   * @param string $vocabulary
   *   The vocabulary to Query.
   */
  public function __construct(
    string $sort_key,
    EntityTypeManagerInterface $entity_type_manager,
    EntityBuffer $graphql_entity_buffer,
    string $vocabulary
  ) {

    parent::__construct($sort_key, $entity_type_manager, $graphql_entity_buffer);
    $this->vocabulary = $vocabulary;
  }

  /**
   * Set the vocabulary to Query.
   * @param string $vocabulary
   *   The vocabulary to Query.
   */
  public function setVocabulary(string $vocabulary) {
    $this->vocabulary = $vocabulary;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery() : QueryInterface {
    return $this->entityTypeManager->getStorage('taxonomy_term')
      ->getQuery()
      ->currentRevision()
      ->accessCheck(TRUE)
      ->condition('vid', $this->vocabulary);
  }

  /**
   * {@inheritdoc}
   */
  public function getCursorObject(string $cursor) : ?Cursor {
    $cursor_object = Cursor::fromCursorString($cursor);

    return !is_null($cursor_object) && $cursor_object->isValidFor($this->sortKey, 'taxonomy_term')
      ? $cursor_object
      : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getIdField() : string {
    return 'tid';
  }

  /**
   * {@inheritdoc}
   */
  public function getSortField() : string {
    switch ($this->sortKey) {
      // @TODO use weight instead 
      case 'CREATED_AT':
        return 'weight';

      default:
        throw new \InvalidArgumentException("Unsupported sortKey for sorting '{$this->sortKey}'");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAggregateSortFunction() : ?string {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getLoaderPromise(array $result) : SyncPromise {
    // In case of no results we create a callback the returns an empty array.
    if (empty($result)) {
      $callback = static fn () => [];
    }
    // Otherwise we create a callback that uses the GraphQL entity buffer to
    // ensure the entities for this query are only loaded once. Even if the
    // results are used multiple times.
    else {
      $buffer = \Drupal::service('graphql.buffer.entity');
      $callback = $buffer->add('taxonomy_term', array_values($result));
    }

    return new Deferred(
      function () use ($callback) {
        return array_map(
          fn (Term $entity) => new Edge(
            $entity,
            new Cursor('taxonomy_term', $entity->id(), $this->sortKey, $this->getSortValue($entity))
          ),
          $callback()
        );
      }
    );
  }

  /**
   * Get the value for an entity based on the sort key for this connection.
   *
   * @param Drupal\taxonomy\Entity\Term $term
   *   The participant entity for the user in this conversation.
   *
   * @return mixed
   *   The sort value.
   */
  protected function getSortValue(Term $term) {
    switch ($this->sortKey) {
      // @TODO use weight instead
      case 'CREATED_AT':
        return $term->getWeight();

      default:
        throw new \InvalidArgumentException("Unsupported sortKey for pagination '{$this->sortKey}'");
    }
  }

}
