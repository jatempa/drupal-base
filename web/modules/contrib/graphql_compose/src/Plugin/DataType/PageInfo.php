<?php

namespace Drupal\graphql_compose\Plugin\DataType;

use Drupal\Core\TypedData\Plugin\DataType\Map;

/**
 * PageInfo Type.
 *
 * Provides information about a GraphQL paged result set ("Connection").
 *
 * @DataType(
 *   id = "page_info",
 *   label = @Translation("Page Info"),
 *   definition_class = "\Drupal\graphql_compose\TypedData\Definition\PageInfoDefinition"
 * )
 */
class PageInfo extends Map {}
