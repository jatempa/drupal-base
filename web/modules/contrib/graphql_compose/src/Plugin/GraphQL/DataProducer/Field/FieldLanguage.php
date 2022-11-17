<?php

namespace Drupal\graphql_compose\Plugin\GraphQL\DataProducer\Field;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the language of an entity.
 *
 * @DataProducer(
 *   id = "field_language",
 *   name = @Translation("Entity language"),
 *   description = @Translation("Returns the entity language."),
 *   produces = @ContextDefinition("language",
 *     label = @Translation("Language")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     )
 *   }
 * )
 */
class FieldLanguage extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return \Drupal\Core\Language\LanguageInterface
   */
  public function resolve(EntityInterface $entity) {
    return [
      'id' => $entity->language()->getId(),
      'name' => $entity->language()->getName(),
      'direction' => $entity->language()->getDirection(),
    ];
  }

}
