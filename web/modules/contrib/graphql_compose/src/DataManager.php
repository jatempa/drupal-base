<?php

namespace Drupal\graphql_compose;

use Drupal\Core\Render\RenderContext;
use Symfony\Component\String\Inflector\EnglishInflector;
use function Symfony\Component\String\u;

class DataManager {

  protected $entityTypeManager = NULL;
  protected $entityFieldManager = NULL;
  protected $renderer = NULL;

  protected $inflector = NULL;
  protected $settings = [];
  protected $sdl = [];
  protected $definitions = [];

  public function __construct() {
    // Inject services from container.
    $this->entityTypeManager = \Drupal::service('entity_type.manager');
    $this->entityFieldManager = \Drupal::service('entity_field.manager');
    $this->renderer = \Drupal::service('renderer');
    // Inject services from container.

    $this->inflector = new EnglishInflector();

    // @TODO Define config schemas and GUI to allow user select which data to expose
    $this->settings = [
      'generate' => [
        'queryies' => true,
        'mutations' => false,
      ],
      'user' => [
        'interface' => ['Node', 'Actor'],
        'prefix' => '', // 'User'
        'storage_type' => 'user',
        'case' => 'camel', // 'none' | 'camel' | 'snake'
        'defaults' => [
          'isUnion' => FALSE,
          'isMultiple' => FALSE,
          'isRequired' => FALSE,
        ],
        'fields' => [
          'uuid' => [
            'type' => 'uuid',
          ],
          'path' => [
            'type' => 'path',
          ],
          'created' => [
            'type' => 'created',
          ],
          'changed' => [
            'type' => 'changed',
          ],
          'name' => [
            'type' => 'entity_label',
            'label' => 'The display name of the user.',
            'description' => 'The specific format of the display name could depend on permissions of the requesting user or application.',
            'name_sdl' => 'displayName',
          ],
          'mail' => [
            'type' => 'email',
            'label' => 'The e-mail of the user.',
            'description' => 'Can be null if the user has not filled in an e-mail or if the user/application making the request is not allowed to view this user\'s e-mail.',
            'name_sdl' => 'mail',
          ],
          'status' => [
            'type' => 'user_status',
            'label' => 'The status of the user account.',
          ],
          'roles' => [
            'type' => 'user_roles',
            'label' => 'The roles that the user has.',
          ],

        ]
      ],
      'taxonomy_term' => [
        'interface' => ['Node'],
        'prefix' => 'TaxonomyTerm',
        'storage_type' => 'taxonomy_vocabulary',
        'case' => 'camel', // 'none' | 'camel' | 'snake'
        'defaults' => [
          'isUnion' => FALSE,
          'isMultiple' => FALSE,
          'isRequired' => FALSE,
        ],
        'fields' => [
          'uuid' => [
            'type' => 'uuid',
          ],
          'langcode' => [
            'type' => 'field_language',
            'description' => 'The {entity_type} language.',
          ],
          'name' => [
            'type' => 'entity_label',
            'label' => '{field_name}',
            'description' => 'The display name of the {entity_type} term.',
          ],
          'description' => [
            'type' => 'text_with_summary',
            'label' => 'Description',
          ],
          'status' => [
            'type' => 'boolean',
            'label' => 'Published status',
          ],
          'path' => [
            'type' => 'path',
          ],
          'changed' => [
            'type' => 'changed',
          ],
        ]
      ],
      'node' => [
        'interface' => ['Node'],
        'prefix' => 'Node',
        'storage_type' => 'node_type',
        'case' => 'camel', // 'none' | 'camel' | 'snake'
        'fields' => [
          'uuid' => [
            'type' => 'uuid',
          ],
          'uid' => [
            'type' => 'entity_owner',
            'description' => 'The author of the {entity_type}.',
            'name_sdl' => 'author',
          ],
          'langcode' => [
            'type' => 'field_language',
            'description' => 'The {entity_type} language.',
          ],
          'title' => [
            'type' => 'entity_label',
            'description' => 'The display title of the {entity_type}.',
          ],
          'status' => [
            'type' => 'boolean',
            'label' => 'Published status',
          ],
          'promote' => [
            'type' => 'boolean',
            'label' => 'Promoted to front page',
          ],
          'sticky' => [
            'type' => 'boolean',
            'label' => 'Sticky at top of lists',
          ],
          'body' => [
            'type' => 'text_with_summary',
          ],
          'path' => [
            'type' => 'path',
          ],
          'created' => [
            'type' => 'created',
          ],
          'changed' => [
            'type' => 'changed',
          ],
          'metatag' => [
            'type' => 'metatag',
          ],
        ],
      ],
      'media' => [
        'interface' => ['Node'],
        'prefix' => 'Media', // 'Media'
        'storage_type' => 'media_type',
        'case' => 'camel', // 'none' | 'camel' | 'snake'
        'defaults' => [
          'isUnion' => FALSE,
          'isMultiple' => FALSE,
          'isRequired' => FALSE,
        ],
        'fields' => [
          'uuid' => [
            'type' => 'uuid',
          ],
          'status' => [
            'type' => 'boolean',
            'label' => 'Published status',
          ],
          'created' => [
            'type' => 'created',
          ],
          'changed' => [
            'type' => 'changed',
          ],
        ]
      ],
      'paragraph'  => [
        'interface' => ['Node'],
        'prefix' => 'Paragraph',
        'storage_type' => 'paragraphs_type',
        'case' => 'camel', // 'none' | 'camel' | 'snake'
        'fields' => [
          'uuid' => [
            'type' => 'uuid',
          ],
          'langcode' => [
            'type' => 'field_language',
            'description' => 'The {entity_type} language.',
          ],
          'status' => [
            'type' => 'boolean',
            'label' => 'Published status',
          ],
          'created' => [
            'type' => 'created',
          ],
        ],
      ],
      // @TODO implement as formatters
      // Fields types
      'fields' => [
        'prefix' => '', // 'field_'
        'case' => 'camel', // 'none' | 'camel' | 'snake'
        // @TODO rename as formatters
        'types' => [
          'uuid' => [
            'type' => 'uuid',
            'description' => 'The unique identifier for the {entity_type}.',
            'name_sdl' => 'id',
            'type_sdl' => 'ID',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'entity_uuid',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                ],
              ],
            ]
          ],
          'entity_owner' => [
            'type' => 'entity_reference',
            'description' => 'The author of the {entity_type}.',
            'name_sdl' => 'author',
            'type_sdl' => 'User',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'entity_owner',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                ],
              ],
            ],
          ],
          'user_roles' => [
            'type' => 'entity_reference',
            'label' => 'The roles that the user has.',
            'description' => '',
            'name_sdl' => 'roles',
            'type_sdl' => 'UserRole',
            'isUnion' => FALSE,
            'isMultiple' => TRUE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'user_roles',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'user',
                    'value' => NULL,
                  ],
                ],
              ],
            ]
          ],
          'user_status' => [
            'type' => 'boolean',
            'label' => 'The status of the user account.',
            'description' => '',
            'name_sdl' => 'status',
            'type_sdl' => 'UserStatus',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'user_status',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'user',
                    'value' => NULL,
                  ],
                ],
              ],
            ]
          ],
          // @TODO validate implementation is correct
          'field_language' => [
            'type' => 'field_language',
            'description' => 'The the {entity_type} language.',
            'name_sdl' => 'langcode',
            'type_sdl' => 'Language',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'field_language',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                ],
              ],
            ]
          ],
          'entity_label' => [
            'type' => 'string',
            'description' => 'The display name of the {entity_type} term.',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'entity_label',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                ],
              ],
            ]
          ],
          'path' => [
            'type' => 'path',
            'label' => 'URL alias',
            'description' => '',
            'name_sdl' => 'path',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'entity_url',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                ],
              ],
              [
                'type' => 'dataProducer',
                'id' => 'url_path',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'url',
                    'value' => NULL,
                  ],
                ],
              ],
            ]
          ],
          'string' => [
            'type' => 'string',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'fromPath',
                'args' => [
                  'type' => 'entity:node',
                  'path' => '{field_name}.value',
                ],
              ],
            ],
          ],
          'string_long' => [
            'type' => 'string_long',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'fromPath',
                'args' => [
                  'type' => 'entity:node',
                  'path' => '{field_name}.value',
                ],
              ],
            ],
          ],
          'list_string' => [
            'type' => 'list_string',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'fromPath',
                'args' => [
                  'type' => 'entity:node',
                  'path' => '{field_name}.value',
                ],
              ],
            ],
          ],
          'boolean' => [
            'type' => 'boolean',
            'type_sdl' => 'Boolean',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'fromPath',
                'args' => [
                  'type' => 'entity:node',
                  'path' => '{field_name}.value',
                ],
              ],
            ],
          ],
          'integer' => [
            'type' => 'integer',
            'type_sdl' => 'Int',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'fromPath',
                'args' => [
                  'type' => 'entity:node',
                  'path' => '{field_name}.value',
                ],
              ],
            ],
          ],
          'float' => [
            'type' => 'float',
            'type_sdl' => 'Float',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'fromPath',
                'args' => [
                  'type' => 'entity:node',
                  'path' => '{field_name}.value',
                ],
              ],
            ],
          ],
          // @TODO Validate if a new Decimal Scalar type is needed.
          'decimal' => [
            'type' => 'decimal',
            'type_sdl' => 'Float',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'fromPath',
                'args' => [
                  'type' => 'entity:node',
                  'path' => '{field_name}.value',
                ],
              ],
            ],
          ],
          'list_integer' => [
            'type' => 'list_integer',
            'type_sdl' => 'Int',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'fromPath',
                'args' => [
                  'type' => 'entity:node',
                  'path' => '{field_name}.value',
                ],
              ],
            ],
          ],
          'list_float' => [
            'type' => 'list_float',
            'type_sdl' => 'Float',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'fromPath',
                'args' => [
                  'type' => 'entity:node',
                  'path' => '{field_name}.value',
                ],
              ],
            ],
          ],
          'link' => [
            'type' => 'link',
            'name_sdl' => 'link',
            'type_sdl' => 'Link',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'field_link',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                  [
                    'id' => 'fromValue',
                    'key' => 'field',
                    'value' => '{field_name}',
                  ]
                ],
              ],
            ],
          ],
          // @TODO fix error when adding text field and there is a paragraph or block_content with the same name
          'text' => [
            'type' => 'text',
            'type_sdl' => 'Text',
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'field_text',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                  [
                    'id' => 'fromValue',
                    'key' => 'field',
                    'value' => '{field_name}',
                  ]
                ],
              ],
            ],
          ],
          'text_long' => [
            'type' => 'text',
            'type_sdl' => 'Text',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'field_text',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                  [
                    'id' => 'fromValue',
                    'key' => 'field',
                    'value' => '{field_name}',
                  ]
                ],
              ],
            ],
          ],
          'text_with_summary' => [
            'type' => 'text',
            'type_sdl' => 'TextSummary',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'field_text_summary',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                  [
                    'id' => 'fromValue',
                    'key' => 'field',
                    'value' => '{field_name}',
                  ]
                ],
              ],
            ],
          ],
          // @TODO provide date formatters using directives
          'datetime' => [
            'type' => 'string',
            'name_sdl' => 'datetime',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'fromPath',
                'args' => [
                  'type' => 'entity:node',
                  'path' => '{field_name}.value',
                ],
              ],
            ],
          ],
          'email' => [
            'type' => 'string',
            'name_sdl' => 'email',
            'type_sdl' => 'Email',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'fromPath',
                'args' => [
                  'type' => 'entity:node',
                  'path' => '{field_name}.value',
                ],
              ],
            ],
          ],
          'telephone' => [
            'type' => 'telephone',
            'name_sdl' => 'telephone',
            'type_sdl' => 'PhoneNumber',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'fromPath',
                'args' => [
                  'type' => 'entity:node',
                  'path' => '{field_name}.value',
                ],
              ],
            ],
          ],
          'password' => [
            'type' => 'string',
            'name_sdl' => 'password',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'fromPath',
                'args' => [
                  'type' => 'entity:node',
                  'path' => '{field_name}.value',
                ],
              ],
            ],
          ],
          // @TODO provide date formatters using directives
          'created' => [
            'label' => 'Entity Authored on',
            'description' => 'An entity field containing a UNIX timestamp of when the entity has been created.',
            'name_sdl' => 'created',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'entity_created',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                ],
              ],
            ]
          ],
          // @TODO provide date formatters using directives
          'changed' => [
            'label' => 'Entity Changed on',
            'description' => 'An entity field containing a UNIX timestamp of when the entity has been changed.',
            'name_sdl' => 'changed',
            'type_sdl' => 'String',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => TRUE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'entity_changed',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                ],
              ],
            ]
          ],
          'entity_reference' => [
            'type_sdl' => '', // this should be defined by target bundle(s) as Bundle Type or Union
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => '', // 'entity_reference' or `field_first` (depending on isMultiple property)
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                  [
                    'id' => 'fromValue',
                    'key' => 'field',
                    'value' => '{field_name}',
                  ]
                ],
              ],
            ],
          ],
          // Paragraphs
          'entity_reference_revisions' => [
            'type_sdl' => '', // this should be defined by target bundle(s) as Bundle Type or Union
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => '', // 'entity_reference' or `field_first` (depending on isMultiple property)
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                  [
                    'id' => 'fromValue',
                    'key' => 'field',
                    'value' => '{field_name}',
                  ]
                ],
              ],
            ],
          ],
          // Media
          'media_image' => [
            'type_sdl' => 'MediaImage',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => '', // 'entity_reference' or `field_first` (depending on isMultiple property)
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                  [
                    'id' => 'fromValue',
                    'key' => 'field',
                    'value' => '{field_name}',
                  ],
                ]
              ]
            ]
          ],
          'image' => [
            'type_sdl' => 'Image',
            'name_sdl' => 'image',
            'isUnion' => FALSE,
            'isMultiple' => FALSE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'property_path',
                'map' => [
                  [
                    'id' => 'fromValue',
                    'key' => 'type',
                    'value' => 'entity:file',
                  ],
                  [
                    'id' => 'fromParent',
                    'key' => 'value',
                    'value' => NULL,
                  ],
                  [
                    'id' => 'fromValue',
                    'key' => 'path',
                    'value' => '{field_name}.entity',
                  ],
                ]
              ],
              [
                'type' => 'dataProducer',
                'id' => 'field_image',
                'map' => [
                  [
                    'id' => 'fromParent',
                    'key' => 'entity',
                    'value' => NULL,
                  ],
                ],
              ],

            ],
          ],
          # Metatags module
          'metatag' => [
            'type' => 'metatag',
            'type_sdl' => 'MetaTagUnion',
            'isUnion' => TRUE,
            'isMultiple' => TRUE,
            'isRequired' => FALSE,
            'producers' => [
              [
                'type' => 'dataProducer',
                'id' => 'meta_tag',
                'map' => [
                  [
                    'id' => 'fromValue',
                    'key' => 'type',
                    'value' => 'entity:node',
                  ],
                  [
                    'id' => 'fromParent',
                    'key' => 'value',
                    'value' => NULL,
                  ],
                ]
              ],
            ],
          ],
        ]
      ],
      // @TODO extract from schema definitions) 
      'types' => [
        'Actor' => [
          'type_sdl' => 'Actor',
          'isFielddable' => TRUE,
          'fields' => [
            'id' => [
                'name_sdl' => 'id',
                'type_sdl' => 'ID',
            ],
            'displayName'=> [
                'name_sdl' => 'displayName',
                'type_sdl' => 'String',
            ],
          ]
        ],

        'Language' => [
          'type_sdl' => 'Language',
          'isFielddable' => TRUE,
          'fields' => [
            'id' => [
                'name_sdl' => 'id',
                'type_sdl' => 'String',
            ],
            'name'=> [
                'name_sdl' => 'name',
                'type_sdl' => 'String',
            ],
            'direction'=> [
                'name_sdl' => 'direction',
                'type_sdl' => 'String',
            ],
          ]
        ],
        'Link' => [
          'type_sdl' => 'Link',  
          'isFielddable' => TRUE,
          'fields' => [
            'uri' => [
                'name_sdl' => 'uri',
                'type_sdl' => 'String',
            ],
            'link'=> [
                'name_sdl' => 'link',
                'type_sdl' => 'String',
            ],
            'title'=> [
                'name_sdl' => 'title',
                'type_sdl' => 'String',
            ],
          ]
        ],
        'Image' => [
          'type_sdl' => 'Image',
          'isFielddable' => TRUE,
          'fields' => [
            'url' => [
                'name_sdl' => 'url',
                'type_sdl' => 'String',
            ],
            'width'=> [
                'name_sdl' => 'width',
                'type_sdl' => 'Int',
            ],
            'height'=> [
                'name_sdl' => 'height',
                'type_sdl' => 'Int',
            ],
          ]
        ],
        'Text' => [
          'type_sdl' => 'Text',
          'isFielddable' => TRUE,
          'fields' => [
            'format' => [
                'name_sdl' => 'format',
                'type_sdl' => 'String',
            ],
            'value'=> [
                'name_sdl' => 'value',
                'type_sdl' => 'String',
            ],
            'processed'=> [
                'name_sdl' => 'processed',
                'type_sdl' => 'String',
            ],
          ]
        ],
        'TextSummary' => [
          'type_sdl' => 'TextSummary',
          'isFielddable' => TRUE,
          'fields' => [
            'format' => [
                'name_sdl' => 'format',
                'type_sdl' => 'String',
            ],
            'value'=> [
                'name_sdl' => 'value',
                'type_sdl' => 'String',
            ],
            'summary'=> [
                'name_sdl' => 'summary',
                'type_sdl' => 'String',
            ],
            'processed'=> [
                'name_sdl' => 'processed',
                'type_sdl' => 'String',
            ],
          ]
        ]
      ]
    ];

    if (\Drupal::moduleHandler()->moduleExists('media')) {
      $this->calculateEntity('media');
      $this->calculateSdl('media');
    }

    $this->calculateEntity('user');
    $this->calculateSdl('user');

    $this->calculateEntity('taxonomy_term');
    $this->calculateSdl('taxonomy_term');

    if (\Drupal::moduleHandler()->moduleExists('paragraphs')) {
      $this->calculateEntity('paragraph');
      $this->calculateSdl('paragraph');
    }

    $this->calculateEntity('node');
    $this->calculateSdl('node');
  }

  public function getSettings($type) {
    if (!array_key_exists($type, $this->settings)) {
      return [];
    }

    return $this->settings[$type];
  }

  public function getDefinitions($type) {
    if (!array_key_exists($type, $this->definitions)) {
      return [];
    }

    return $this->definitions[$type];
  }

  public function getSdlByStorage($storage, $type) {
    if (!array_key_exists($storage, $this->sdl)) {
      return NULL;
    }

    if (!array_key_exists($type, $this->sdl[$storage])) {
      return NULL;
    }

    return $this->sdl[$storage][$type];
  }

  protected function getEntitiesFromStorageType($type) {
    $entities = [];
    if ($type === 'user') {
      $entities[] = [
        'id' => 'user',
        'label' => 'Users',
        'description' => 'The GraphQL API users.',
      ];

      return $entities;  
    }

    $storage = $this->settings[$type]['storage_type'];
    foreach ( $this->entityTypeManager->getStorage($storage)->loadMultiple() as $entity) {
      $entities[] = [
        'id' => $entity->id(),
        'label' => $entity->label(),
        'description' => $entity->getDescription(),
      ];
    }

    return $entities;
  }

  protected function calculateEntity($type) {
    $entities = $this->getEntitiesFromStorageType($type);
    $this->calculateFields($type, $entities);
  }

  protected function calculateSdl($type) {
    if (!array_key_exists($type, $this->definitions)) {
      return;
    }

    $context = new RenderContext();
    $base = '';
    $extension = '';
    foreach ($this->definitions[$type] as $entity) {
      $renderableBase = [
        '#theme' => 'entity_base',
        '#entity' => $entity,
      ];

      $base .= $this->renderer->executeInRenderContext($context, function() use (&$renderableBase) {
        return $this->renderer->render($renderableBase);
      });

      $renderableExtension = [
        '#theme' => 'entity_extension',
        '#entity' => $entity,
      ];

      $extension .= $this->renderer->executeInRenderContext($context, function() use (&$renderableExtension) {
        return $this->renderer->render($renderableExtension);
      });
    }

    if ($type === 'node') {
      $renderableBase = [
        '#theme' => 'entity_base_content',
        '#entities' => $this->definitions[$type],
      ];

      $base .= $this->renderer->executeInRenderContext($context, function() use (&$renderableBase) {
        return $this->renderer->render($renderableBase);
      });

      $renderableExtension = [
        '#theme' => 'entity_extension_content',
        '#entities' => $this->definitions[$type],
      ];

      $extension .= $this->renderer->executeInRenderContext($context, function() use (&$renderableExtension) {
        return $this->renderer->render($renderableExtension);
      });
    }

    $this->sdl[$type] = [
      'base' => $base,
      'extension' => $extension,
    ];
  }

  protected function calculateFields($entityType, $entities) {
    $fieldNames = $this->settings[$entityType]['fields']?:[];
    $fieldTypes = $this->settings['fields']['types']?:[];
    $types = [];
    // @TODO provide a GUI to skip types from singularize
    $skipSingularize = [
      'cta',
      'hero_cta'
    ];

    foreach ($entities as [ 'id' => $entityId, 'label' => $entityLabel, 'description' => $entityDescription ] ) {
      $prefix = $this->settings[$entityType]['prefix'];
      $type = u($entityId)->title()->prepend($prefix)->camel()->toString();
      $fields = [];
      $unions = [];

      foreach ($this->entityFieldManager->getFieldDefinitions($entityType, $entityId) as $field) {
        if ($field instanceof BaseFieldDefinition) {
          continue;
        }

        $fieldInfo = [];

        // Core fields
        if (!u($field->getName())->startsWith('field_')) {

          if (!in_array($field->getName(), array_keys($fieldNames))) {
            continue;
          }

          $entitySDL = u($entityLabel)->camel()->title()->toString();
          if (!in_array($field->getName(), array_keys($fieldNames))) {
            continue;
          }

          $fieldInfo = $fieldNames[$field->getName()];
          $fieldInfo = array_merge($fieldTypes[$fieldInfo['type']], $fieldInfo);
        
          if (!array_key_exists('label', $fieldInfo)) {
            $fieldInfo['label'] = $field->getLabel();
          } else {
            $fieldInfo['label'] = u($fieldInfo['label'])->replace('{field_name}', $field->getName())->toString();
          }

          if (!array_key_exists('name', $fieldInfo)) {
            $fieldInfo['name'] = $field->getName();
          }
          if (!array_key_exists('description', $fieldInfo)) {
            $fieldInfo['description'] = $field->getDescription();
          } else {
            $fieldInfo['description'] = u($fieldInfo['description'])->replace('{entity_type}', $entitySDL)->toString();
          }
          if (!array_key_exists('type', $fieldInfo)) {
            $fieldInfo['type'] = $field->getType();
          }
          if (!array_key_exists('name_sdl', $fieldInfo)) {
            $fieldInfo['name_sdl'] = u($field->getName())->camel()->toString();
          }
        }

        // Custom fields
        if (u($field->getName())->startsWith('field_')) {
          if (!in_array($field->getType(), array_keys($fieldTypes))) {
            continue;
          }

          $fieldName = u($field->getName())->trimPrefix('field_')->camel()->title()->toString();
          $fieldInfo = $fieldTypes[$field->getType()];

          if ($field->getType() === 'entity_reference' || $field->getType() === 'entity_reference_revisions') {

            $handlerSettings = $field->getSetting('handler_settings');
            // @TODO fix ReusableParagraph that contains target_bundles with NULL value
            if (!$handlerSettings || $handlerSettings['target_bundles'] === NULL) {
              continue;
            }

            $targetBundles = array_keys($handlerSettings['target_bundles']);
            $targetType = $field->getSetting('target_type');

            $referenceTargetBundles = [];
            $targetBundleMapping = [];
            foreach($targetBundles as $targetBundle) {
              $targetBundleSingular = u($targetBundle)->title()->prepend($targetType)->camel()->title()->toString();
              $targetBundleSingular = in_array($targetBundle, $skipSingularize)?$targetBundleSingular:$this->inflector->singularize($targetBundleSingular)[0];
              $referenceTargetBundles[] = u($targetBundleSingular)->camel()->title()->toString();
              $targetBundleMapping[$targetBundle] = $targetBundleSingular;
            }

            if (!$referenceTargetBundles) {
              continue;
            }

            if ( !array_intersect($referenceTargetBundles, array_keys($fieldTypes)) &&
                 !array_key_exists($targetType, $this->definitions) ||
                 !array_intersect($referenceTargetBundles, array_keys($this->definitions[$targetType]))
               ) {
                continue;
            }

            if (count($referenceTargetBundles) > 1) {
              $fieldInfo['isUnion'] = TRUE;
              $unionType = u($fieldName)->prepend($type)->title()->append('Union')->toString();
              $fieldInfo['type_sdl'] = $unionType;
              $unions[$unionType]['type'] = $targetType;
              $unions[$unionType]['type_sdl'] = $unionType;
              $unions[$unionType]['target_bundles'] = $targetBundles;
              $unions[$unionType]['target_bundles_sdl'] = $referenceTargetBundles;
              $unions[$unionType]['mapping'] = $targetBundleMapping;
              $fieldInfo['producers'][0]['id'] = 'entity_reference';
            } else {
              $referenceTargetBundle = $targetType . '_' . reset($referenceTargetBundles);
              if (array_key_exists($referenceTargetBundle, $fieldTypes)) {
                $fieldInfo = $fieldTypes[$referenceTargetBundle];
              }

              $referenceTargetBundle = reset($referenceTargetBundles);
              if ( $this->definitions[$targetType][$referenceTargetBundle] ) {
                $fieldInfo['type_sdl'] = $this->definitions[$targetType][$referenceTargetBundle]['type_sdl'];
              }
              
              $fieldInfo['producers'][0]['id'] = $field->getFieldStorageDefinition()->isMultiple() ? 'entity_reference' : 'field_first';
            }
          }

          $fieldInfo['type'] = $field->getType();
          $fieldInfo['name'] = $field->getName();
          $fieldInfo['label'] = $field->getLabel();
          $fieldInfo['description'] = $field->getDescription();
          $fieldInfo['name_sdl'] = u($field->getName())->trimPrefix('field_')->camel()->toString();
          $fieldInfo['isMultiple'] = $field->getFieldStorageDefinition()->isMultiple() 
            || (array_key_exists('isUnion', $fieldInfo) && $fieldInfo['isUnion']);

          if (!$fieldInfo['producers']) {
            continue;
          }
        }

        if (!$fieldInfo) {
          continue;
        }

        $fields[$field->getName()] = $fieldInfo;
      }

      $singular = in_array($entityId, $skipSingularize)?$type:$this->inflector->singularize($type)[0];
      $plural = $this->inflector->pluralize($singular)[0];
      $typeSdl = u($singular)->camel()->title()->toString();

      $this->definitions[$entityType][$typeSdl] = [
        'id' => $entityId,
        'type' => $singular,
        'interface' => $this->settings[$entityType]['interface']?:[],
        'label' => $entityLabel,
        'description' => $entityDescription,
        'type_plural' => $plural,
        'type_sdl' => $typeSdl,
        'fields' => $fields,
        'unions' => $unions,
      ];
    }

    return;
  }

  protected function getEntities() {
    // Settings
    $types = $this->getSettings('types');
    $fields = $this->getSettings('fields');

    // Types
    $users = $this->getDefinitions('user');
    $taxonomyTerms = $this->getDefinitions('taxonomy_term');
    if (\Drupal::moduleHandler()->moduleExists('media')) {
      $medias = $this->getDefinitions('media');
    } else {
      $medias = [];
    }
    if (\Drupal::moduleHandler()->moduleExists('paragraphs')) {
      $paragraphs = $this->getDefinitions('paragraph');
    } else {
      $paragraphs = [];
    }
    $nodes = $this->getDefinitions('node');

    return [
      'types' => $types,
      'user' => $users, 
      'taxonomy_term' =>  $taxonomyTerms, 
      'media' => $medias, 
      'paragraph' => $paragraphs, 
      'node' => $nodes
    ];
  }

  protected function getMergedEntities() {
    $entities = [];
    foreach($this->getEntities() as $entityTypeId => $entity) {
      $entities = array_merge($entities, $entity);
    }

    return $entities;
  }

  public function getEntityTypes() {
    $entities = [];
    foreach($this->getEntities() as $entityTypeId => $entityTypes) {
      $types = [];
      foreach ($entityTypes as $entityType) {
        $types[] = [
          'id' => $entityType['id'],
          'type' => $entityType['type_sdl'],
          'typePlural' => u($entityType['type_plural'])->title()->toString(),
          'querySingular' => $entityType['type'],
          'queryPlural' => $entityType['type_plural'], 
        ];
      } 
      $entities[] = [
        'id' => $entityTypeId,
        'types' => $types,
      ];
    }

    return $entities;
  }

  public function getFragments() {
    $entities = $this->getMergedEntities();
    $types = $this->getSettings('types');
    foreach ($entities as $entityId => $entity) {
      foreach ($entity['fields'] as $fieldId => $field) {
        $entities[$entityId]['fields'][$fieldId]['fragment']['isFragment'] = false;
        $entities[$entityId]['fields'][$fieldId]['fragment']['isMultiple'] = false;
        if (in_array($field['type_sdl'], array_keys($types))) {
          $entities[$entityId]['fields'][$fieldId]['fragment']['isFragment'] = true;
          $entities[$entityId]['fields'][$fieldId]['fragment']['type_sdl'] = $types[$field['type_sdl']]['type_sdl'];
          continue;
        }
        if (in_array($field['type_sdl'], array_keys($entities))) {
          $entities[$entityId]['fields'][$fieldId]['fragment']['isFragment'] = true;
          $entities[$entityId]['fields'][$fieldId]['fragment']['type_sdl'] = $entities[$field['type_sdl']]['type_sdl'];
          continue;
        }
        if ( array_key_exists('isUnion', $field) && $field['isUnion'] ) {
          $entities[$entityId]['fields'][$fieldId]['fragment']['isFragment'] = true;
          $entities[$entityId]['fields'][$fieldId]['fragment']['isMultiple'] = true;
          $entities[$entityId]['fields'][$fieldId]['fragment']['type_sdl'] = $entities[$entityId]['unions'][$field['type_sdl']]['target_bundles_sdl'];
        }
      }
    }

    return $entities;
  }

  public function getFragmentsAsSdl() {
    $context = new RenderContext();
    $renderArray = [
      '#theme' => 'entity_fragments',
      '#entities' => \Drupal::service('graphql_compose.datamanager')->getFragments(),
      '#showWrappers' => FALSE,
    ];

    return \Drupal::service('renderer')->executeInRenderContext($context, function() use (&$renderArray) {
      return \Drupal::service('renderer')->render($renderArray);
    });
  }
}
