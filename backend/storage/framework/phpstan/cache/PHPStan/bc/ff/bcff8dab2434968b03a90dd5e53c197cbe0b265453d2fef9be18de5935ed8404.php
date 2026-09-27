<?php declare(strict_types = 1);

// osfsl-C:/Projects/dental-final/backend/vendor/composer/../spatie/laravel-permission/src/Traits/HasAssignedModels.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Spatie\Permission\Traits\HasAssignedModels
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-d697e39a2e2aee2c3fda693506d357110e038ebde4be7f8b6d7b71918a8aa8a6-8.4-6.73.0.5',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'filename' => 'C:/Projects/dental-final/backend/vendor/composer/../spatie/laravel-permission/src/Traits/HasAssignedModels.php',
      ),
    ),
    'namespace' => 'Spatie\\Permission\\Traits',
    'name' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
    'shortName' => 'HasAssignedModels',
    'isInterface' => false,
    'isTrait' => true,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 13,
    'endLine' => 149,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
    ),
    'immediateMethods' => 
    array (
      'assignToModels' => 
      array (
        'name' => 'assignToModels',
        'parameters' => 
        array (
          'models' => 
          array (
            'name' => 'models',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'array',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'Illuminate\\Support\\Collection',
                      'isIdentifier' => false,
                    ),
                  ),
                  2 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'Illuminate\\Database\\Eloquent\\Model',
                      'isIdentifier' => false,
                    ),
                  ),
                  3 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'int',
                      'isIdentifier' => true,
                    ),
                  ),
                  4 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 21,
            'endLine' => 21,
            'startColumn' => 36,
            'endColumn' => 76,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'modelClass' => 
          array (
            'name' => 'modelClass',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 21,
                'endLine' => 21,
                'startTokenPos' => 76,
                'startFilePos' => 681,
                'endTokenPos' => 76,
                'endFilePos' => 684,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 21,
            'endLine' => 21,
            'startColumn' => 79,
            'endColumn' => 104,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'static',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Assign this role to the given models without removing existing assignments.
 *
 * @param  Model|int|string|array<int, Model|int|string>|Collection<int, Model|int|string>  $models
 * @return $this
 */',
        'startLine' => 21,
        'endLine' => 39,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Spatie\\Permission\\Traits',
        'declaringClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'implementingClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'currentClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'aliasName' => NULL,
      ),
      'removeFromModels' => 
      array (
        'name' => 'removeFromModels',
        'parameters' => 
        array (
          'models' => 
          array (
            'name' => 'models',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'array',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'Illuminate\\Support\\Collection',
                      'isIdentifier' => false,
                    ),
                  ),
                  2 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'Illuminate\\Database\\Eloquent\\Model',
                      'isIdentifier' => false,
                    ),
                  ),
                  3 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'int',
                      'isIdentifier' => true,
                    ),
                  ),
                  4 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 47,
            'endLine' => 47,
            'startColumn' => 38,
            'endColumn' => 78,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'modelClass' => 
          array (
            'name' => 'modelClass',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 47,
                'endLine' => 47,
                'startTokenPos' => 232,
                'startFilePos' => 1494,
                'endTokenPos' => 232,
                'endFilePos' => 1497,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 47,
            'endLine' => 47,
            'startColumn' => 81,
            'endColumn' => 106,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'static',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Remove this role from the given models.
 *
 * @param  Model|int|string|array<int, Model|int|string>|Collection<int, Model|int|string>  $models
 * @return $this
 */',
        'startLine' => 47,
        'endLine' => 56,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Spatie\\Permission\\Traits',
        'declaringClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'implementingClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'currentClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'aliasName' => NULL,
      ),
      'syncModels' => 
      array (
        'name' => 'syncModels',
        'parameters' => 
        array (
          'models' => 
          array (
            'name' => 'models',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'array',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'Illuminate\\Support\\Collection',
                      'isIdentifier' => false,
                    ),
                  ),
                  2 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'Illuminate\\Database\\Eloquent\\Model',
                      'isIdentifier' => false,
                    ),
                  ),
                  3 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'int',
                      'isIdentifier' => true,
                    ),
                  ),
                  4 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 64,
            'endLine' => 64,
            'startColumn' => 32,
            'endColumn' => 72,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'modelClass' => 
          array (
            'name' => 'modelClass',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 64,
                'endLine' => 64,
                'startTokenPos' => 322,
                'startFilePos' => 2067,
                'endTokenPos' => 322,
                'endFilePos' => 2070,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 64,
            'endLine' => 64,
            'startColumn' => 75,
            'endColumn' => 100,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'static',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Remove all current model associations and set the given ones.
 *
 * @param  Model|int|string|array<int, Model|int|string>|Collection<int, Model|int|string>  $models
 * @return $this
 */',
        'startLine' => 64,
        'endLine' => 79,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Spatie\\Permission\\Traits',
        'declaringClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'implementingClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'currentClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'aliasName' => NULL,
      ),
      'relationForModel' => 
      array (
        'name' => 'relationForModel',
        'parameters' => 
        array (
          'modelClass' => 
          array (
            'name' => 'modelClass',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 84,
            'endLine' => 84,
            'startColumn' => 41,
            'endColumn' => 58,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\MorphToMany',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Build a morphedByMany relation pointing to a specific model class.
 */',
        'startLine' => 84,
        'endLine' => 93,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Spatie\\Permission\\Traits',
        'declaringClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'implementingClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'currentClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'aliasName' => NULL,
      ),
      'groupModelsByMorphClass' => 
      array (
        'name' => 'groupModelsByMorphClass',
        'parameters' => 
        array (
          'models' => 
          array (
            'name' => 'models',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'array',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'Illuminate\\Support\\Collection',
                      'isIdentifier' => false,
                    ),
                  ),
                  2 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'Illuminate\\Database\\Eloquent\\Model',
                      'isIdentifier' => false,
                    ),
                  ),
                  3 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'int',
                      'isIdentifier' => true,
                    ),
                  ),
                  4 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 102,
            'endLine' => 102,
            'startColumn' => 9,
            'endColumn' => 49,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'modelClass' => 
          array (
            'name' => 'modelClass',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 103,
            'endLine' => 103,
            'startColumn' => 9,
            'endColumn' => 27,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Group the given models by class, deduplicating IDs within each class.
 *
 * @param  Model|int|string|array<int, Model|int|string>|Collection<int, Model|int|string>  $models
 * @return array<class-string, list<int|string>>
 */',
        'startLine' => 101,
        'endLine' => 119,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Spatie\\Permission\\Traits',
        'declaringClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'implementingClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'currentClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'aliasName' => NULL,
      ),
      'resolveDefaultModelClass' => 
      array (
        'name' => 'resolveDefaultModelClass',
        'parameters' => 
        array (
          'modelClass' => 
          array (
            'name' => 'modelClass',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 124,
            'endLine' => 124,
            'startColumn' => 47,
            'endColumn' => 65,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Resolve the model class to use when raw IDs are passed.
 */',
        'startLine' => 124,
        'endLine' => 129,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Spatie\\Permission\\Traits',
        'declaringClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'implementingClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'currentClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'aliasName' => NULL,
      ),
      'teamPivot' => 
      array (
        'name' => 'teamPivot',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return array<string, int|string|null>
 */',
        'startLine' => 134,
        'endLine' => 141,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Spatie\\Permission\\Traits',
        'declaringClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'implementingClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'currentClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'aliasName' => NULL,
      ),
      'newPivotQueryForRole' => 
      array (
        'name' => 'newPivotQueryForRole',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Query\\Builder',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 143,
        'endLine' => 148,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Spatie\\Permission\\Traits',
        'declaringClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'implementingClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'currentClassName' => 'Spatie\\Permission\\Traits\\HasAssignedModels',
        'aliasName' => NULL,
      ),
    ),
    'traitsData' => 
    array (
      'aliases' => 
      array (
      ),
      'modifiers' => 
      array (
      ),
      'precedences' => 
      array (
      ),
      'hashes' => 
      array (
      ),
    ),
  ),
));