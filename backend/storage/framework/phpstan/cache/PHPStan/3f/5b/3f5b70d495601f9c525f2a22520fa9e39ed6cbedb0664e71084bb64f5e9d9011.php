<?php declare(strict_types = 1);

// osfsl-C:/Projects/dental-final/backend/vendor/composer/../laravel/framework/src/Illuminate/Database/Eloquent/Relations/BelongsToMany.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Illuminate\Database\Eloquent\Relations\BelongsToMany
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-c0b281d943d2aff9b0200e18c1e9949f864acd341fb791eca4cae887488574dc-8.4-6.73.0.5',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'filename' => 'C:/Projects/dental-final/backend/vendor/composer/../laravel/framework/src/Illuminate/Database/Eloquent/Relations/BelongsToMany.php',
      ),
    ),
    'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
    'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
    'shortName' => 'BelongsToMany',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @template TRelatedModel of \\Illuminate\\Database\\Eloquent\\Model
 * @template TDeclaringModel of \\Illuminate\\Database\\Eloquent\\Model
 * @template TPivotModel of \\Illuminate\\Database\\Eloquent\\Relations\\Pivot = \\Illuminate\\Database\\Eloquent\\Relations\\Pivot
 * @template TAccessor of string = \'pivot\'
 *
 * @extends \\Illuminate\\Database\\Eloquent\\Relations\\Relation<TRelatedModel, TDeclaringModel, \\Illuminate\\Database\\Eloquent\\Collection<int, TRelatedModel&object{pivot: TPivotModel}>>
 *
 * @todo use TAccessor when PHPStan bug is fixed: https://github.com/phpstan/phpstan/issues/12756
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 33,
    'endLine' => 1771,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\Relation',
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
      0 => 'Illuminate\\Database\\Eloquent\\Relations\\Concerns\\InteractsWithDictionary',
      1 => 'Illuminate\\Database\\Eloquent\\Relations\\Concerns\\InteractsWithPivotTable',
      2 => 'Illuminate\\Database\\Eloquent\\Relations\\Concerns\\SupportsPivotInverseRelations',
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'table' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'name' => 'table',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The intermediate table for the relation.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 42,
        'endLine' => 42,
        'startColumn' => 5,
        'endColumn' => 21,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'foreignPivotKey' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'name' => 'foreignPivotKey',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The foreign key of the parent model.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 49,
        'endLine' => 49,
        'startColumn' => 5,
        'endColumn' => 31,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'relatedPivotKey' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'name' => 'relatedPivotKey',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The associated key of the relation.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 56,
        'endLine' => 56,
        'startColumn' => 5,
        'endColumn' => 31,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'parentKey' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'name' => 'parentKey',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The key name of the parent model.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 63,
        'endLine' => 63,
        'startColumn' => 5,
        'endColumn' => 25,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'relatedKey' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'name' => 'relatedKey',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The key name of the related model.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 70,
        'endLine' => 70,
        'startColumn' => 5,
        'endColumn' => 26,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'relationName' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'name' => 'relationName',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The "name" of the relationship.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 77,
        'endLine' => 77,
        'startColumn' => 5,
        'endColumn' => 28,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'pivotColumns' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'name' => 'pivotColumns',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 84,
            'endLine' => 84,
            'startTokenPos' => 173,
            'startFilePos' => 2467,
            'endTokenPos' => 174,
            'endFilePos' => 2468,
          ),
        ),
        'docComment' => '/**
 * The pivot table columns to retrieve.
 *
 * @var array<string|\\Illuminate\\Contracts\\Database\\Query\\Expression>
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 84,
        'endLine' => 84,
        'startColumn' => 5,
        'endColumn' => 33,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'pivotWheres' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'name' => 'pivotWheres',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 91,
            'endLine' => 91,
            'startTokenPos' => 185,
            'startFilePos' => 2597,
            'endTokenPos' => 186,
            'endFilePos' => 2598,
          ),
        ),
        'docComment' => '/**
 * Any pivot table restrictions for where clauses.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 91,
        'endLine' => 91,
        'startColumn' => 5,
        'endColumn' => 32,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'pivotWhereIns' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'name' => 'pivotWhereIns',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 98,
            'endLine' => 98,
            'startTokenPos' => 197,
            'startFilePos' => 2731,
            'endTokenPos' => 198,
            'endFilePos' => 2732,
          ),
        ),
        'docComment' => '/**
 * Any pivot table restrictions for whereIn clauses.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 98,
        'endLine' => 98,
        'startColumn' => 5,
        'endColumn' => 34,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'pivotWhereNulls' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'name' => 'pivotWhereNulls',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 105,
            'endLine' => 105,
            'startTokenPos' => 209,
            'startFilePos' => 2869,
            'endTokenPos' => 210,
            'endFilePos' => 2870,
          ),
        ),
        'docComment' => '/**
 * Any pivot table restrictions for whereNull clauses.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 105,
        'endLine' => 105,
        'startColumn' => 5,
        'endColumn' => 36,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'pivotValues' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'name' => 'pivotValues',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 112,
            'endLine' => 112,
            'startTokenPos' => 221,
            'startFilePos' => 2993,
            'endTokenPos' => 222,
            'endFilePos' => 2994,
          ),
        ),
        'docComment' => '/**
 * The default values for the pivot columns.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 112,
        'endLine' => 112,
        'startColumn' => 5,
        'endColumn' => 32,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'withTimestamps' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'name' => 'withTimestamps',
        'modifiers' => 1,
        'type' => NULL,
        'default' => 
        array (
          'code' => 'false',
          'attributes' => 
          array (
            'startLine' => 119,
            'endLine' => 119,
            'startTokenPos' => 233,
            'startFilePos' => 3132,
            'endTokenPos' => 233,
            'endFilePos' => 3136,
          ),
        ),
        'docComment' => '/**
 * Indicates if timestamps are available on the pivot table.
 *
 * @var bool
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 119,
        'endLine' => 119,
        'startColumn' => 5,
        'endColumn' => 35,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'pivotCreatedAt' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'name' => 'pivotCreatedAt',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The custom pivot table column for the created_at timestamp.
 *
 * @var string|null
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 126,
        'endLine' => 126,
        'startColumn' => 5,
        'endColumn' => 30,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'pivotUpdatedAt' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'name' => 'pivotUpdatedAt',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The custom pivot table column for the updated_at timestamp.
 *
 * @var string|null
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 133,
        'endLine' => 133,
        'startColumn' => 5,
        'endColumn' => 30,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'using' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'name' => 'using',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The class name of the custom pivot model to use for the relationship.
 *
 * @var class-string<TPivotModel>
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 140,
        'endLine' => 140,
        'startColumn' => 5,
        'endColumn' => 21,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'accessor' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'name' => 'accessor',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\'pivot\'',
          'attributes' => 
          array (
            'startLine' => 147,
            'endLine' => 147,
            'startTokenPos' => 265,
            'startFilePos' => 3733,
            'endTokenPos' => 265,
            'endFilePos' => 3739,
          ),
        ),
        'docComment' => '/**
 * The name of the accessor to use for the "pivot" relationship.
 *
 * @var TAccessor
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 147,
        'endLine' => 147,
        'startColumn' => 5,
        'endColumn' => 34,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
    ),
    'immediateMethods' => 
    array (
      '__construct' => 
      array (
        'name' => '__construct',
        'parameters' => 
        array (
          'query' => 
          array (
            'name' => 'query',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Database\\Eloquent\\Builder',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 162,
            'endLine' => 162,
            'startColumn' => 9,
            'endColumn' => 22,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'parent' => 
          array (
            'name' => 'parent',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Database\\Eloquent\\Model',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 163,
            'endLine' => 163,
            'startColumn' => 9,
            'endColumn' => 21,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'table' => 
          array (
            'name' => 'table',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 164,
            'endLine' => 164,
            'startColumn' => 9,
            'endColumn' => 14,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
          'foreignPivotKey' => 
          array (
            'name' => 'foreignPivotKey',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 165,
            'endLine' => 165,
            'startColumn' => 9,
            'endColumn' => 24,
            'parameterIndex' => 3,
            'isOptional' => false,
          ),
          'relatedPivotKey' => 
          array (
            'name' => 'relatedPivotKey',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 166,
            'endLine' => 166,
            'startColumn' => 9,
            'endColumn' => 24,
            'parameterIndex' => 4,
            'isOptional' => false,
          ),
          'parentKey' => 
          array (
            'name' => 'parentKey',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 167,
            'endLine' => 167,
            'startColumn' => 9,
            'endColumn' => 18,
            'parameterIndex' => 5,
            'isOptional' => false,
          ),
          'relatedKey' => 
          array (
            'name' => 'relatedKey',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 168,
            'endLine' => 168,
            'startColumn' => 9,
            'endColumn' => 19,
            'parameterIndex' => 6,
            'isOptional' => false,
          ),
          'relationName' => 
          array (
            'name' => 'relationName',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 169,
                'endLine' => 169,
                'startTokenPos' => 306,
                'startFilePos' => 4403,
                'endTokenPos' => 306,
                'endFilePos' => 4406,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 169,
            'endLine' => 169,
            'startColumn' => 9,
            'endColumn' => 28,
            'parameterIndex' => 7,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create a new belongs to many relationship instance.
 *
 * @param  \\Illuminate\\Database\\Eloquent\\Builder<TRelatedModel>  $query
 * @param  TDeclaringModel  $parent
 * @param  string|class-string<TRelatedModel>  $table
 * @param  string  $foreignPivotKey
 * @param  string  $relatedPivotKey
 * @param  string  $parentKey
 * @param  string  $relatedKey
 * @param  string|null  $relationName
 */',
        'startLine' => 161,
        'endLine' => 179,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'resolveTableName' => 
      array (
        'name' => 'resolveTableName',
        'parameters' => 
        array (
          'table' => 
          array (
            'name' => 'table',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 187,
            'endLine' => 187,
            'startColumn' => 41,
            'endColumn' => 46,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Attempt to resolve the intermediate table name from the given string.
 *
 * @param  string  $table
 * @return string
 */',
        'startLine' => 187,
        'endLine' => 204,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'addConstraints' => 
      array (
        'name' => 'addConstraints',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set the base constraints on the relation query.
 *
 * @return void
 */',
        'startLine' => 211,
        'endLine' => 218,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'performJoin' => 
      array (
        'name' => 'performJoin',
        'parameters' => 
        array (
          'query' => 
          array (
            'name' => 'query',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 226,
                'endLine' => 226,
                'startTokenPos' => 552,
                'startFilePos' => 5818,
                'endTokenPos' => 552,
                'endFilePos' => 5821,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 226,
            'endLine' => 226,
            'startColumn' => 36,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set the join clause for the relation query.
 *
 * @param  \\Illuminate\\Database\\Eloquent\\Builder<TRelatedModel>|null  $query
 * @return $this
 */',
        'startLine' => 226,
        'endLine' => 241,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'addWhereConstraints' => 
      array (
        'name' => 'addWhereConstraints',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set the where clause for the relation query.
 *
 * @return $this
 */',
        'startLine' => 248,
        'endLine' => 255,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'addEagerConstraints' => 
      array (
        'name' => 'addEagerConstraints',
        'parameters' => 
        array (
          'models' => 
          array (
            'name' => 'models',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 258,
            'endLine' => 258,
            'startColumn' => 41,
            'endColumn' => 53,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/** @inheritDoc */',
        'startLine' => 258,
        'endLine' => 267,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'initRelation' => 
      array (
        'name' => 'initRelation',
        'parameters' => 
        array (
          'models' => 
          array (
            'name' => 'models',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 270,
            'endLine' => 270,
            'startColumn' => 34,
            'endColumn' => 46,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'relation' => 
          array (
            'name' => 'relation',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 270,
            'endLine' => 270,
            'startColumn' => 49,
            'endColumn' => 57,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/** @inheritDoc */',
        'startLine' => 270,
        'endLine' => 277,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'match' => 
      array (
        'name' => 'match',
        'parameters' => 
        array (
          'models' => 
          array (
            'name' => 'models',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 280,
            'endLine' => 280,
            'startColumn' => 27,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'results' => 
          array (
            'name' => 'results',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Database\\Eloquent\\Collection',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 280,
            'endLine' => 280,
            'startColumn' => 42,
            'endColumn' => 68,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'relation' => 
          array (
            'name' => 'relation',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 280,
            'endLine' => 280,
            'startColumn' => 71,
            'endColumn' => 79,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/** @inheritDoc */',
        'startLine' => 280,
        'endLine' => 309,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'buildDictionary' => 
      array (
        'name' => 'buildDictionary',
        'parameters' => 
        array (
          'results' => 
          array (
            'name' => 'results',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Database\\Eloquent\\Collection',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 317,
            'endLine' => 317,
            'startColumn' => 40,
            'endColumn' => 66,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Build model dictionary keyed by the relation\'s foreign key.
 *
 * @param  \\Illuminate\\Database\\Eloquent\\Collection<int, TRelatedModel>  $results
 * @return array<array<array-key, TRelatedModel>>
 */',
        'startLine' => 317,
        'endLine' => 341,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'getPivotClass' => 
      array (
        'name' => 'getPivotClass',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the class being used for pivot models.
 *
 * @return class-string<TPivotModel>
 */',
        'startLine' => 348,
        'endLine' => 351,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'using' => 
      array (
        'name' => 'using',
        'parameters' => 
        array (
          'class' => 
          array (
            'name' => 'class',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 363,
            'endLine' => 363,
            'startColumn' => 27,
            'endColumn' => 32,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Specify the custom pivot model to use for the relationship.
 *
 * @template TNewPivotModel of \\Illuminate\\Database\\Eloquent\\Relations\\Pivot
 *
 * @param  class-string<TNewPivotModel>  $class
 * @return $this
 *
 * @phpstan-this-out static<TRelatedModel, TDeclaringModel, TNewPivotModel, TAccessor>
 */',
        'startLine' => 363,
        'endLine' => 368,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'as' => 
      array (
        'name' => 'as',
        'parameters' => 
        array (
          'accessor' => 
          array (
            'name' => 'accessor',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 380,
            'endLine' => 380,
            'startColumn' => 24,
            'endColumn' => 32,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Specify the custom pivot accessor to use for the relationship.
 *
 * @template TNewAccessor of string
 *
 * @param  TNewAccessor  $accessor
 * @return $this
 *
 * @phpstan-this-out static<TRelatedModel, TDeclaringModel, TPivotModel, TNewAccessor>
 */',
        'startLine' => 380,
        'endLine' => 385,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'wherePivot' => 
      array (
        'name' => 'wherePivot',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 396,
            'endLine' => 396,
            'startColumn' => 32,
            'endColumn' => 38,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'operator' => 
          array (
            'name' => 'operator',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 396,
                'endLine' => 396,
                'startTokenPos' => 1219,
                'startFilePos' => 10951,
                'endTokenPos' => 1219,
                'endFilePos' => 10954,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 396,
            'endLine' => 396,
            'startColumn' => 41,
            'endColumn' => 56,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'value' => 
          array (
            'name' => 'value',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 396,
                'endLine' => 396,
                'startTokenPos' => 1226,
                'startFilePos' => 10966,
                'endTokenPos' => 1226,
                'endFilePos' => 10969,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 396,
            'endLine' => 396,
            'startColumn' => 59,
            'endColumn' => 71,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'boolean' => 
          array (
            'name' => 'boolean',
            'default' => 
            array (
              'code' => '\'and\'',
              'attributes' => 
              array (
                'startLine' => 396,
                'endLine' => 396,
                'startTokenPos' => 1233,
                'startFilePos' => 10983,
                'endTokenPos' => 1233,
                'endFilePos' => 10987,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 396,
            'endLine' => 396,
            'startColumn' => 74,
            'endColumn' => 89,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set a where clause for a pivot table column.
 *
 * @param  (\\Closure(\\Illuminate\\Database\\Eloquent\\Builder<TPivotModel>): mixed)|string|\\Illuminate\\Contracts\\Database\\Query\\Expression  $column
 * @param  mixed  $operator
 * @param  mixed  $value
 * @param  string  $boolean
 * @return $this
 */',
        'startLine' => 396,
        'endLine' => 420,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'wherePivotBetween' => 
      array (
        'name' => 'wherePivotBetween',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 431,
            'endLine' => 431,
            'startColumn' => 39,
            'endColumn' => 45,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'values' => 
          array (
            'name' => 'values',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 431,
            'endLine' => 431,
            'startColumn' => 48,
            'endColumn' => 60,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'boolean' => 
          array (
            'name' => 'boolean',
            'default' => 
            array (
              'code' => '\'and\'',
              'attributes' => 
              array (
                'startLine' => 431,
                'endLine' => 431,
                'startTokenPos' => 1415,
                'startFilePos' => 12058,
                'endTokenPos' => 1415,
                'endFilePos' => 12062,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 431,
            'endLine' => 431,
            'startColumn' => 63,
            'endColumn' => 78,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'not' => 
          array (
            'name' => 'not',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 431,
                'endLine' => 431,
                'startTokenPos' => 1422,
                'startFilePos' => 12072,
                'endTokenPos' => 1422,
                'endFilePos' => 12076,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 431,
            'endLine' => 431,
            'startColumn' => 81,
            'endColumn' => 92,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set a "where between" clause for a pivot table column.
 *
 * @param  string|\\Illuminate\\Contracts\\Database\\Query\\Expression  $column
 * @param  array  $values
 * @param  string  $boolean
 * @param  bool  $not
 * @return $this
 */',
        'startLine' => 431,
        'endLine' => 434,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'orWherePivotBetween' => 
      array (
        'name' => 'orWherePivotBetween',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 443,
            'endLine' => 443,
            'startColumn' => 41,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'values' => 
          array (
            'name' => 'values',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 443,
            'endLine' => 443,
            'startColumn' => 50,
            'endColumn' => 62,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set a "or where between" clause for a pivot table column.
 *
 * @param  string|\\Illuminate\\Contracts\\Database\\Query\\Expression  $column
 * @param  array  $values
 * @return $this
 */',
        'startLine' => 443,
        'endLine' => 446,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'wherePivotNotBetween' => 
      array (
        'name' => 'wherePivotNotBetween',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 456,
            'endLine' => 456,
            'startColumn' => 42,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'values' => 
          array (
            'name' => 'values',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 456,
            'endLine' => 456,
            'startColumn' => 51,
            'endColumn' => 63,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'boolean' => 
          array (
            'name' => 'boolean',
            'default' => 
            array (
              'code' => '\'and\'',
              'attributes' => 
              array (
                'startLine' => 456,
                'endLine' => 456,
                'startTokenPos' => 1509,
                'startFilePos' => 12882,
                'endTokenPos' => 1509,
                'endFilePos' => 12886,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 456,
            'endLine' => 456,
            'startColumn' => 66,
            'endColumn' => 81,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set a "where pivot not between" clause for a pivot table column.
 *
 * @param  string|\\Illuminate\\Contracts\\Database\\Query\\Expression  $column
 * @param  array  $values
 * @param  string  $boolean
 * @return $this
 */',
        'startLine' => 456,
        'endLine' => 459,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'orWherePivotNotBetween' => 
      array (
        'name' => 'orWherePivotNotBetween',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 468,
            'endLine' => 468,
            'startColumn' => 44,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'values' => 
          array (
            'name' => 'values',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 468,
            'endLine' => 468,
            'startColumn' => 53,
            'endColumn' => 65,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set a "or where not between" clause for a pivot table column.
 *
 * @param  string|\\Illuminate\\Contracts\\Database\\Query\\Expression  $column
 * @param  array  $values
 * @return $this
 */',
        'startLine' => 468,
        'endLine' => 471,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'wherePivotIn' => 
      array (
        'name' => 'wherePivotIn',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 482,
            'endLine' => 482,
            'startColumn' => 34,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'values' => 
          array (
            'name' => 'values',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 482,
            'endLine' => 482,
            'startColumn' => 43,
            'endColumn' => 49,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'boolean' => 
          array (
            'name' => 'boolean',
            'default' => 
            array (
              'code' => '\'and\'',
              'attributes' => 
              array (
                'startLine' => 482,
                'endLine' => 482,
                'startTokenPos' => 1592,
                'startFilePos' => 13680,
                'endTokenPos' => 1592,
                'endFilePos' => 13684,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 482,
            'endLine' => 482,
            'startColumn' => 52,
            'endColumn' => 67,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'not' => 
          array (
            'name' => 'not',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 482,
                'endLine' => 482,
                'startTokenPos' => 1599,
                'startFilePos' => 13694,
                'endTokenPos' => 1599,
                'endFilePos' => 13698,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 482,
            'endLine' => 482,
            'startColumn' => 70,
            'endColumn' => 81,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set a "where in" clause for a pivot table column.
 *
 * @param  string|\\Illuminate\\Contracts\\Database\\Query\\Expression  $column
 * @param  mixed  $values
 * @param  string  $boolean
 * @param  bool  $not
 * @return $this
 */',
        'startLine' => 482,
        'endLine' => 487,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'orWherePivot' => 
      array (
        'name' => 'orWherePivot',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 497,
            'endLine' => 497,
            'startColumn' => 34,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'operator' => 
          array (
            'name' => 'operator',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 497,
                'endLine' => 497,
                'startTokenPos' => 1658,
                'startFilePos' => 14223,
                'endTokenPos' => 1658,
                'endFilePos' => 14226,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 497,
            'endLine' => 497,
            'startColumn' => 43,
            'endColumn' => 58,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'value' => 
          array (
            'name' => 'value',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 497,
                'endLine' => 497,
                'startTokenPos' => 1665,
                'startFilePos' => 14238,
                'endTokenPos' => 1665,
                'endFilePos' => 14241,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 497,
            'endLine' => 497,
            'startColumn' => 61,
            'endColumn' => 73,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set an "or where" clause for a pivot table column.
 *
 * @param  (\\Closure(\\Illuminate\\Database\\Eloquent\\Builder<TPivotModel>): mixed)|string|\\Illuminate\\Contracts\\Database\\Query\\Expression  $column
 * @param  mixed  $operator
 * @param  mixed  $value
 * @return $this
 */',
        'startLine' => 497,
        'endLine' => 500,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'withPivotValue' => 
      array (
        'name' => 'withPivotValue',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 513,
            'endLine' => 513,
            'startColumn' => 36,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'value' => 
          array (
            'name' => 'value',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 513,
                'endLine' => 513,
                'startTokenPos' => 1706,
                'startFilePos' => 14722,
                'endTokenPos' => 1706,
                'endFilePos' => 14725,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 513,
            'endLine' => 513,
            'startColumn' => 45,
            'endColumn' => 57,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set a where clause for a pivot table column.
 *
 * In addition, new pivot records will receive this value.
 *
 * @param  string|\\Illuminate\\Contracts\\Database\\Query\\Expression|array<string, string>  $column
 * @param  mixed  $value
 * @return $this
 *
 * @throws \\InvalidArgumentException
 */',
        'startLine' => 513,
        'endLine' => 530,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'orWherePivotIn' => 
      array (
        'name' => 'orWherePivotIn',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 539,
            'endLine' => 539,
            'startColumn' => 36,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'values' => 
          array (
            'name' => 'values',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 539,
            'endLine' => 539,
            'startColumn' => 45,
            'endColumn' => 51,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set an "or where in" clause for a pivot table column.
 *
 * @param  string  $column
 * @param  mixed  $values
 * @return $this
 */',
        'startLine' => 539,
        'endLine' => 542,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'wherePivotNotIn' => 
      array (
        'name' => 'wherePivotNotIn',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 552,
            'endLine' => 552,
            'startColumn' => 37,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'values' => 
          array (
            'name' => 'values',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 552,
            'endLine' => 552,
            'startColumn' => 46,
            'endColumn' => 52,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'boolean' => 
          array (
            'name' => 'boolean',
            'default' => 
            array (
              'code' => '\'and\'',
              'attributes' => 
              array (
                'startLine' => 552,
                'endLine' => 552,
                'startTokenPos' => 1875,
                'startFilePos' => 15793,
                'endTokenPos' => 1875,
                'endFilePos' => 15797,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 552,
            'endLine' => 552,
            'startColumn' => 55,
            'endColumn' => 70,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set a "where not in" clause for a pivot table column.
 *
 * @param  string|\\Illuminate\\Contracts\\Database\\Query\\Expression  $column
 * @param  mixed  $values
 * @param  string  $boolean
 * @return $this
 */',
        'startLine' => 552,
        'endLine' => 555,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'orWherePivotNotIn' => 
      array (
        'name' => 'orWherePivotNotIn',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 564,
            'endLine' => 564,
            'startColumn' => 39,
            'endColumn' => 45,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'values' => 
          array (
            'name' => 'values',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 564,
            'endLine' => 564,
            'startColumn' => 48,
            'endColumn' => 54,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set an "or where not in" clause for a pivot table column.
 *
 * @param  string  $column
 * @param  mixed  $values
 * @return $this
 */',
        'startLine' => 564,
        'endLine' => 567,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'wherePivotNull' => 
      array (
        'name' => 'wherePivotNull',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 577,
            'endLine' => 577,
            'startColumn' => 36,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'boolean' => 
          array (
            'name' => 'boolean',
            'default' => 
            array (
              'code' => '\'and\'',
              'attributes' => 
              array (
                'startLine' => 577,
                'endLine' => 577,
                'startTokenPos' => 1950,
                'startFilePos' => 16480,
                'endTokenPos' => 1950,
                'endFilePos' => 16484,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 577,
            'endLine' => 577,
            'startColumn' => 45,
            'endColumn' => 60,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'not' => 
          array (
            'name' => 'not',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 577,
                'endLine' => 577,
                'startTokenPos' => 1957,
                'startFilePos' => 16494,
                'endTokenPos' => 1957,
                'endFilePos' => 16498,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 577,
            'endLine' => 577,
            'startColumn' => 63,
            'endColumn' => 74,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set a "where null" clause for a pivot table column.
 *
 * @param  string|\\Illuminate\\Contracts\\Database\\Query\\Expression  $column
 * @param  string  $boolean
 * @param  bool  $not
 * @return $this
 */',
        'startLine' => 577,
        'endLine' => 582,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'wherePivotNotNull' => 
      array (
        'name' => 'wherePivotNotNull',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 591,
            'endLine' => 591,
            'startColumn' => 39,
            'endColumn' => 45,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'boolean' => 
          array (
            'name' => 'boolean',
            'default' => 
            array (
              'code' => '\'and\'',
              'attributes' => 
              array (
                'startLine' => 591,
                'endLine' => 591,
                'startTokenPos' => 2013,
                'startFilePos' => 16928,
                'endTokenPos' => 2013,
                'endFilePos' => 16932,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 591,
            'endLine' => 591,
            'startColumn' => 48,
            'endColumn' => 63,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set a "where not null" clause for a pivot table column.
 *
 * @param  string|\\Illuminate\\Contracts\\Database\\Query\\Expression  $column
 * @param  string  $boolean
 * @return $this
 */',
        'startLine' => 591,
        'endLine' => 594,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'orWherePivotNull' => 
      array (
        'name' => 'orWherePivotNull',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 603,
            'endLine' => 603,
            'startColumn' => 38,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'not' => 
          array (
            'name' => 'not',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 603,
                'endLine' => 603,
                'startTokenPos' => 2051,
                'startFilePos' => 17275,
                'endTokenPos' => 2051,
                'endFilePos' => 17279,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 603,
            'endLine' => 603,
            'startColumn' => 47,
            'endColumn' => 58,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set a "or where null" clause for a pivot table column.
 *
 * @param  string|\\Illuminate\\Contracts\\Database\\Query\\Expression  $column
 * @param  bool  $not
 * @return $this
 */',
        'startLine' => 603,
        'endLine' => 606,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'orWherePivotNotNull' => 
      array (
        'name' => 'orWherePivotNotNull',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 614,
            'endLine' => 614,
            'startColumn' => 41,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set a "or where not null" clause for a pivot table column.
 *
 * @param  string|\\Illuminate\\Contracts\\Database\\Query\\Expression  $column
 * @return $this
 */',
        'startLine' => 614,
        'endLine' => 617,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'orderByPivot' => 
      array (
        'name' => 'orderByPivot',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 626,
            'endLine' => 626,
            'startColumn' => 34,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'direction' => 
          array (
            'name' => 'direction',
            'default' => 
            array (
              'code' => '\\SortDirection::Ascending',
              'attributes' => 
              array (
                'startLine' => 626,
                'endLine' => 626,
                'startTokenPos' => 2117,
                'startFilePos' => 17950,
                'endTokenPos' => 2119,
                'endFilePos' => 17973,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 626,
            'endLine' => 626,
            'startColumn' => 43,
            'endColumn' => 79,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Add an "order by" clause for a pivot table column.
 *
 * @param  string|\\Illuminate\\Contracts\\Database\\Query\\Expression  $column
 * @param  SortDirection|\'asc\'|\'desc\'  $direction
 * @return $this
 */',
        'startLine' => 626,
        'endLine' => 629,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'orderByPivotDesc' => 
      array (
        'name' => 'orderByPivotDesc',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 637,
            'endLine' => 637,
            'startColumn' => 38,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Add an "order by desc" clause for a pivot table column.
 *
 * @param  string|\\Illuminate\\Contracts\\Database\\Query\\Expression  $column
 * @return $this
 */',
        'startLine' => 637,
        'endLine' => 640,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'findOrNew' => 
      array (
        'name' => 'findOrNew',
        'parameters' => 
        array (
          'id' => 
          array (
            'name' => 'id',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 653,
            'endLine' => 653,
            'startColumn' => 31,
            'endColumn' => 33,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'columns' => 
          array (
            'name' => 'columns',
            'default' => 
            array (
              'code' => '[\'*\']',
              'attributes' => 
              array (
                'startLine' => 653,
                'endLine' => 653,
                'startTokenPos' => 2194,
                'startFilePos' => 18899,
                'endTokenPos' => 2196,
                'endFilePos' => 18903,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 653,
            'endLine' => 653,
            'startColumn' => 36,
            'endColumn' => 51,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Find a related model by its primary key or return a new instance of the related model.
 *
 * @param  mixed  $id
 * @param  array  $columns
 * @return (
 *     $id is (\\Illuminate\\Contracts\\Support\\Arrayable<array-key, mixed>|array<mixed>)
 *     ? \\Illuminate\\Database\\Eloquent\\Collection<int, TRelatedModel&object{pivot: TPivotModel}>
 *     : TRelatedModel&object{pivot: TPivotModel}
 * )
 */',
        'startLine' => 653,
        'endLine' => 660,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'firstOrNew' => 
      array (
        'name' => 'firstOrNew',
        'parameters' => 
        array (
          'attributes' => 
          array (
            'name' => 'attributes',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 669,
                'endLine' => 669,
                'startTokenPos' => 2260,
                'startFilePos' => 19373,
                'endTokenPos' => 2261,
                'endFilePos' => 19374,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 669,
            'endLine' => 669,
            'startColumn' => 32,
            'endColumn' => 53,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'values' => 
          array (
            'name' => 'values',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 669,
                'endLine' => 669,
                'startTokenPos' => 2272,
                'startFilePos' => 19401,
                'endTokenPos' => 2273,
                'endFilePos' => 19402,
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
                      'name' => 'Closure',
                      'isIdentifier' => false,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'array',
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
            'startLine' => 669,
            'endLine' => 669,
            'startColumn' => 56,
            'endColumn' => 81,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the first related model record matching the attributes or instantiate it.
 *
 * @param  array  $attributes
 * @param  (\\Closure(): array)|array  $values
 * @return TRelatedModel&object{pivot: TPivotModel}
 */',
        'startLine' => 669,
        'endLine' => 676,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'firstOrCreate' => 
      array (
        'name' => 'firstOrCreate',
        'parameters' => 
        array (
          'attributes' => 
          array (
            'name' => 'attributes',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 687,
                'endLine' => 687,
                'startTokenPos' => 2350,
                'startFilePos' => 19998,
                'endTokenPos' => 2351,
                'endFilePos' => 19999,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 687,
            'endLine' => 687,
            'startColumn' => 35,
            'endColumn' => 56,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'values' => 
          array (
            'name' => 'values',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 687,
                'endLine' => 687,
                'startTokenPos' => 2362,
                'startFilePos' => 20026,
                'endTokenPos' => 2363,
                'endFilePos' => 20027,
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
                      'name' => 'Closure',
                      'isIdentifier' => false,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'array',
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
            'startLine' => 687,
            'endLine' => 687,
            'startColumn' => 59,
            'endColumn' => 84,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'joining' => 
          array (
            'name' => 'joining',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 687,
                'endLine' => 687,
                'startTokenPos' => 2372,
                'startFilePos' => 20047,
                'endTokenPos' => 2373,
                'endFilePos' => 20048,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 687,
            'endLine' => 687,
            'startColumn' => 87,
            'endColumn' => 105,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'touch' => 
          array (
            'name' => 'touch',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 687,
                'endLine' => 687,
                'startTokenPos' => 2380,
                'startFilePos' => 20060,
                'endTokenPos' => 2380,
                'endFilePos' => 20063,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 687,
            'endLine' => 687,
            'startColumn' => 108,
            'endColumn' => 120,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the first record matching the attributes. If the record is not found, create it.
 *
 * @param  array  $attributes
 * @param  (\\Closure(): array)|array  $values
 * @param  array  $joining
 * @param  bool  $touch
 * @return TRelatedModel&object{pivot: TPivotModel}
 */',
        'startLine' => 687,
        'endLine' => 702,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'createOrFirst' => 
      array (
        'name' => 'createOrFirst',
        'parameters' => 
        array (
          'attributes' => 
          array (
            'name' => 'attributes',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 715,
                'endLine' => 715,
                'startTokenPos' => 2539,
                'startFilePos' => 21163,
                'endTokenPos' => 2540,
                'endFilePos' => 21164,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 715,
            'endLine' => 715,
            'startColumn' => 35,
            'endColumn' => 56,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'values' => 
          array (
            'name' => 'values',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 715,
                'endLine' => 715,
                'startTokenPos' => 2551,
                'startFilePos' => 21191,
                'endTokenPos' => 2552,
                'endFilePos' => 21192,
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
                      'name' => 'Closure',
                      'isIdentifier' => false,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'array',
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
            'startLine' => 715,
            'endLine' => 715,
            'startColumn' => 59,
            'endColumn' => 84,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'joining' => 
          array (
            'name' => 'joining',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 715,
                'endLine' => 715,
                'startTokenPos' => 2561,
                'startFilePos' => 21212,
                'endTokenPos' => 2562,
                'endFilePos' => 21213,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 715,
            'endLine' => 715,
            'startColumn' => 87,
            'endColumn' => 105,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'touch' => 
          array (
            'name' => 'touch',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 715,
                'endLine' => 715,
                'startTokenPos' => 2569,
                'startFilePos' => 21225,
                'endTokenPos' => 2569,
                'endFilePos' => 21228,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 715,
            'endLine' => 715,
            'startColumn' => 108,
            'endColumn' => 120,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Attempt to create the record. If a unique constraint violation occurs, attempt to find the matching record.
 *
 * @param  array  $attributes
 * @param  (\\Closure(): array)|array  $values
 * @param  array  $joining
 * @param  bool  $touch
 * @return TRelatedModel&object{pivot: TPivotModel}
 *
 * @throws \\Illuminate\\Database\\UniqueConstraintViolationException
 */',
        'startLine' => 715,
        'endLine' => 730,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'updateOrCreate' => 
      array (
        'name' => 'updateOrCreate',
        'parameters' => 
        array (
          'attributes' => 
          array (
            'name' => 'attributes',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 741,
            'endLine' => 741,
            'startColumn' => 36,
            'endColumn' => 52,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'values' => 
          array (
            'name' => 'values',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 741,
                'endLine' => 741,
                'startTokenPos' => 2779,
                'startFilePos' => 22307,
                'endTokenPos' => 2780,
                'endFilePos' => 22308,
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
                      'name' => 'Closure',
                      'isIdentifier' => false,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'array',
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
            'startLine' => 741,
            'endLine' => 741,
            'startColumn' => 55,
            'endColumn' => 80,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'joining' => 
          array (
            'name' => 'joining',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 741,
                'endLine' => 741,
                'startTokenPos' => 2789,
                'startFilePos' => 22328,
                'endTokenPos' => 2790,
                'endFilePos' => 22329,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 741,
            'endLine' => 741,
            'startColumn' => 83,
            'endColumn' => 101,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'touch' => 
          array (
            'name' => 'touch',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 741,
                'endLine' => 741,
                'startTokenPos' => 2797,
                'startFilePos' => 22341,
                'endTokenPos' => 2797,
                'endFilePos' => 22344,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 741,
            'endLine' => 741,
            'startColumn' => 104,
            'endColumn' => 116,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create or update a related record matching the attributes, and fill it with values.
 *
 * @param  array  $attributes
 * @param  (\\Closure(): array)|array  $values
 * @param  array  $joining
 * @param  bool  $touch
 * @return TRelatedModel&object{pivot: TPivotModel}
 */',
        'startLine' => 741,
        'endLine' => 750,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'find' => 
      array (
        'name' => 'find',
        'parameters' => 
        array (
          'id' => 
          array (
            'name' => 'id',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 763,
            'endLine' => 763,
            'startColumn' => 26,
            'endColumn' => 28,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'columns' => 
          array (
            'name' => 'columns',
            'default' => 
            array (
              'code' => '[\'*\']',
              'attributes' => 
              array (
                'startLine' => 763,
                'endLine' => 763,
                'startTokenPos' => 2897,
                'startFilePos' => 23106,
                'endTokenPos' => 2899,
                'endFilePos' => 23110,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 763,
            'endLine' => 763,
            'startColumn' => 31,
            'endColumn' => 46,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Find a related model by its primary key.
 *
 * @param  mixed  $id
 * @param  array  $columns
 * @return (
 *     $id is (\\Illuminate\\Contracts\\Support\\Arrayable<array-key, mixed>|array<mixed>)
 *     ? \\Illuminate\\Database\\Eloquent\\Collection<int, TRelatedModel&object{pivot: TPivotModel}>
 *     : (TRelatedModel&object{pivot: TPivotModel})|null
 * )
 */',
        'startLine' => 763,
        'endLine' => 772,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'findSole' => 
      array (
        'name' => 'findSole',
        'parameters' => 
        array (
          'id' => 
          array (
            'name' => 'id',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 784,
            'endLine' => 784,
            'startColumn' => 30,
            'endColumn' => 32,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'columns' => 
          array (
            'name' => 'columns',
            'default' => 
            array (
              'code' => '[\'*\']',
              'attributes' => 
              array (
                'startLine' => 784,
                'endLine' => 784,
                'startTokenPos' => 3003,
                'startFilePos' => 23801,
                'endTokenPos' => 3005,
                'endFilePos' => 23805,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 784,
            'endLine' => 784,
            'startColumn' => 35,
            'endColumn' => 50,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Find a sole related model by its primary key.
 *
 * @param  mixed  $id
 * @param  array  $columns
 * @return TRelatedModel&object{pivot: TPivotModel}
 *
 * @throws \\Illuminate\\Database\\Eloquent\\ModelNotFoundException<TRelatedModel>
 * @throws \\Illuminate\\Database\\MultipleRecordsFoundException
 */',
        'startLine' => 784,
        'endLine' => 789,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'findMany' => 
      array (
        'name' => 'findMany',
        'parameters' => 
        array (
          'ids' => 
          array (
            'name' => 'ids',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 798,
            'endLine' => 798,
            'startColumn' => 30,
            'endColumn' => 33,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'columns' => 
          array (
            'name' => 'columns',
            'default' => 
            array (
              'code' => '[\'*\']',
              'attributes' => 
              array (
                'startLine' => 798,
                'endLine' => 798,
                'startTokenPos' => 3063,
                'startFilePos' => 24287,
                'endTokenPos' => 3065,
                'endFilePos' => 24291,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 798,
            'endLine' => 798,
            'startColumn' => 36,
            'endColumn' => 51,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Find multiple related models by their primary keys.
 *
 * @param  \\Illuminate\\Contracts\\Support\\Arrayable|array  $ids
 * @param  array  $columns
 * @return \\Illuminate\\Database\\Eloquent\\Collection<int, TRelatedModel&object{pivot: TPivotModel}>
 */',
        'startLine' => 798,
        'endLine' => 809,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'findOrFail' => 
      array (
        'name' => 'findOrFail',
        'parameters' => 
        array (
          'id' => 
          array (
            'name' => 'id',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 824,
            'endLine' => 824,
            'startColumn' => 32,
            'endColumn' => 34,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'columns' => 
          array (
            'name' => 'columns',
            'default' => 
            array (
              'code' => '[\'*\']',
              'attributes' => 
              array (
                'startLine' => 824,
                'endLine' => 824,
                'startTokenPos' => 3158,
                'startFilePos' => 25121,
                'endTokenPos' => 3160,
                'endFilePos' => 25125,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 824,
            'endLine' => 824,
            'startColumn' => 37,
            'endColumn' => 52,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Find a related model by its primary key or throw an exception.
 *
 * @param  mixed  $id
 * @param  array  $columns
 * @return (
 *     $id is (\\Illuminate\\Contracts\\Support\\Arrayable<array-key, mixed>|array<mixed>)
 *     ? \\Illuminate\\Database\\Eloquent\\Collection<int, TRelatedModel&object{pivot: TPivotModel}>
 *     : TRelatedModel&object{pivot: TPivotModel}
 * )
 *
 * @throws \\Illuminate\\Database\\Eloquent\\ModelNotFoundException<TRelatedModel>
 */',
        'startLine' => 824,
        'endLine' => 839,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'findOr' => 
      array (
        'name' => 'findOr',
        'parameters' => 
        array (
          'id' => 
          array (
            'name' => 'id',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 855,
            'endLine' => 855,
            'startColumn' => 28,
            'endColumn' => 30,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'columns' => 
          array (
            'name' => 'columns',
            'default' => 
            array (
              'code' => '[\'*\']',
              'attributes' => 
              array (
                'startLine' => 855,
                'endLine' => 855,
                'startTokenPos' => 3303,
                'startFilePos' => 26151,
                'endTokenPos' => 3305,
                'endFilePos' => 26155,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 855,
            'endLine' => 855,
            'startColumn' => 33,
            'endColumn' => 48,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'callback' => 
          array (
            'name' => 'callback',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 855,
                'endLine' => 855,
                'startTokenPos' => 3315,
                'startFilePos' => 26179,
                'endTokenPos' => 3315,
                'endFilePos' => 26182,
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
                      'name' => 'Closure',
                      'isIdentifier' => false,
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
            'startLine' => 855,
            'endLine' => 855,
            'startColumn' => 51,
            'endColumn' => 75,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Find a related model by its primary key or call a callback.
 *
 * @template TValue
 *
 * @param  mixed  $id
 * @param  (\\Closure(): TValue)|list<string>|string  $columns
 * @param  (\\Closure(): TValue)|null  $callback
 * @return (
 *     $id is (\\Illuminate\\Contracts\\Support\\Arrayable<array-key, mixed>|array<mixed>)
 *     ? \\Illuminate\\Database\\Eloquent\\Collection<int, TRelatedModel&object{pivot: TPivotModel}>|TValue
 *     : (TRelatedModel&object{pivot: TPivotModel})|TValue
 * )
 */',
        'startLine' => 855,
        'endLine' => 876,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'firstWhere' => 
      array (
        'name' => 'firstWhere',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 887,
            'endLine' => 887,
            'startColumn' => 32,
            'endColumn' => 38,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'operator' => 
          array (
            'name' => 'operator',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 887,
                'endLine' => 887,
                'startTokenPos' => 3473,
                'startFilePos' => 27025,
                'endTokenPos' => 3473,
                'endFilePos' => 27028,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 887,
            'endLine' => 887,
            'startColumn' => 41,
            'endColumn' => 56,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'value' => 
          array (
            'name' => 'value',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 887,
                'endLine' => 887,
                'startTokenPos' => 3480,
                'startFilePos' => 27040,
                'endTokenPos' => 3480,
                'endFilePos' => 27043,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 887,
            'endLine' => 887,
            'startColumn' => 59,
            'endColumn' => 71,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'boolean' => 
          array (
            'name' => 'boolean',
            'default' => 
            array (
              'code' => '\'and\'',
              'attributes' => 
              array (
                'startLine' => 887,
                'endLine' => 887,
                'startTokenPos' => 3487,
                'startFilePos' => 27057,
                'endTokenPos' => 3487,
                'endFilePos' => 27061,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 887,
            'endLine' => 887,
            'startColumn' => 74,
            'endColumn' => 89,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Add a basic where clause to the query, and return the first result.
 *
 * @param  \\Closure|string|array  $column
 * @param  mixed  $operator
 * @param  mixed  $value
 * @param  string  $boolean
 * @return (TRelatedModel&object{pivot: TPivotModel})|null
 */',
        'startLine' => 887,
        'endLine' => 890,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'first' => 
      array (
        'name' => 'first',
        'parameters' => 
        array (
          'columns' => 
          array (
            'name' => 'columns',
            'default' => 
            array (
              'code' => '[\'*\']',
              'attributes' => 
              array (
                'startLine' => 898,
                'endLine' => 898,
                'startTokenPos' => 3529,
                'startFilePos' => 27358,
                'endTokenPos' => 3531,
                'endFilePos' => 27362,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 898,
            'endLine' => 898,
            'startColumn' => 27,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Execute the query and get the first result.
 *
 * @param  array  $columns
 * @return (TRelatedModel&object{pivot: TPivotModel})|null
 */',
        'startLine' => 898,
        'endLine' => 903,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'firstOrFail' => 
      array (
        'name' => 'firstOrFail',
        'parameters' => 
        array (
          'columns' => 
          array (
            'name' => 'columns',
            'default' => 
            array (
              'code' => '[\'*\']',
              'attributes' => 
              array (
                'startLine' => 913,
                'endLine' => 913,
                'startTokenPos' => 3591,
                'startFilePos' => 27809,
                'endTokenPos' => 3593,
                'endFilePos' => 27813,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 913,
            'endLine' => 913,
            'startColumn' => 33,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Execute the query and get the first result or throw an exception.
 *
 * @param  array  $columns
 * @return TRelatedModel&object{pivot: TPivotModel}
 *
 * @throws \\Illuminate\\Database\\Eloquent\\ModelNotFoundException<TRelatedModel>
 */',
        'startLine' => 913,
        'endLine' => 920,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'firstOr' => 
      array (
        'name' => 'firstOr',
        'parameters' => 
        array (
          'columns' => 
          array (
            'name' => 'columns',
            'default' => 
            array (
              'code' => '[\'*\']',
              'attributes' => 
              array (
                'startLine' => 931,
                'endLine' => 931,
                'startTokenPos' => 3660,
                'startFilePos' => 28345,
                'endTokenPos' => 3662,
                'endFilePos' => 28349,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 931,
            'endLine' => 931,
            'startColumn' => 29,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'callback' => 
          array (
            'name' => 'callback',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 931,
                'endLine' => 931,
                'startTokenPos' => 3672,
                'startFilePos' => 28373,
                'endTokenPos' => 3672,
                'endFilePos' => 28376,
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
                      'name' => 'Closure',
                      'isIdentifier' => false,
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
            'startLine' => 931,
            'endLine' => 931,
            'startColumn' => 47,
            'endColumn' => 71,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Execute the query and get the first result or call a callback.
 *
 * @template TValue
 *
 * @param  (\\Closure(): TValue)|list<string>  $columns
 * @param  (\\Closure(): TValue)|null  $callback
 * @return (TRelatedModel&object{pivot: TPivotModel})|TValue
 */',
        'startLine' => 931,
        'endLine' => 944,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'getResults' => 
      array (
        'name' => 'getResults',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/** @inheritDoc */',
        'startLine' => 947,
        'endLine' => 952,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'get' => 
      array (
        'name' => 'get',
        'parameters' => 
        array (
          'columns' => 
          array (
            'name' => 'columns',
            'default' => 
            array (
              'code' => '[\'*\']',
              'attributes' => 
              array (
                'startLine' => 955,
                'endLine' => 955,
                'startTokenPos' => 3807,
                'startFilePos' => 28896,
                'endTokenPos' => 3809,
                'endFilePos' => 28900,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 955,
            'endLine' => 955,
            'startColumn' => 25,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/** @inheritDoc */',
        'startLine' => 955,
        'endLine' => 980,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'shouldSelect' => 
      array (
        'name' => 'shouldSelect',
        'parameters' => 
        array (
          'columns' => 
          array (
            'name' => 'columns',
            'default' => 
            array (
              'code' => '[\'*\']',
              'attributes' => 
              array (
                'startLine' => 988,
                'endLine' => 988,
                'startTokenPos' => 3958,
                'startFilePos' => 30088,
                'endTokenPos' => 3960,
                'endFilePos' => 30092,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 988,
            'endLine' => 988,
            'startColumn' => 37,
            'endColumn' => 58,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the select columns for the relation query.
 *
 * @param  array  $columns
 * @return array
 */',
        'startLine' => 988,
        'endLine' => 995,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'aliasedPivotColumns' => 
      array (
        'name' => 'aliasedPivotColumns',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the pivot columns for the relation.
 *
 * "pivot_" is prefixed at each column for easy removal later.
 *
 * @return array
 */',
        'startLine' => 1004,
        'endLine' => 1014,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'paginate' => 
      array (
        'name' => 'paginate',
        'parameters' => 
        array (
          'perPage' => 
          array (
            'name' => 'perPage',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1025,
                'endLine' => 1025,
                'startTokenPos' => 4103,
                'startFilePos' => 31145,
                'endTokenPos' => 4103,
                'endFilePos' => 31148,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1025,
            'endLine' => 1025,
            'startColumn' => 30,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'columns' => 
          array (
            'name' => 'columns',
            'default' => 
            array (
              'code' => '[\'*\']',
              'attributes' => 
              array (
                'startLine' => 1025,
                'endLine' => 1025,
                'startTokenPos' => 4110,
                'startFilePos' => 31162,
                'endTokenPos' => 4112,
                'endFilePos' => 31166,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1025,
            'endLine' => 1025,
            'startColumn' => 47,
            'endColumn' => 62,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'pageName' => 
          array (
            'name' => 'pageName',
            'default' => 
            array (
              'code' => '\'page\'',
              'attributes' => 
              array (
                'startLine' => 1025,
                'endLine' => 1025,
                'startTokenPos' => 4119,
                'startFilePos' => 31181,
                'endTokenPos' => 4119,
                'endFilePos' => 31186,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1025,
            'endLine' => 1025,
            'startColumn' => 65,
            'endColumn' => 82,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'page' => 
          array (
            'name' => 'page',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1025,
                'endLine' => 1025,
                'startTokenPos' => 4126,
                'startFilePos' => 31197,
                'endTokenPos' => 4126,
                'endFilePos' => 31200,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1025,
            'endLine' => 1025,
            'startColumn' => 85,
            'endColumn' => 96,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get a paginator for the "select" statement.
 *
 * @param  int|null  $perPage
 * @param  array  $columns
 * @param  string  $pageName
 * @param  int|null  $page
 * @return \\Illuminate\\Pagination\\LengthAwarePaginator<int, TRelatedModel&object{pivot: TPivotModel}>
 */',
        'startLine' => 1025,
        'endLine' => 1032,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'simplePaginate' => 
      array (
        'name' => 'simplePaginate',
        'parameters' => 
        array (
          'perPage' => 
          array (
            'name' => 'perPage',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1043,
                'endLine' => 1043,
                'startTokenPos' => 4207,
                'startFilePos' => 31820,
                'endTokenPos' => 4207,
                'endFilePos' => 31823,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1043,
            'endLine' => 1043,
            'startColumn' => 36,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'columns' => 
          array (
            'name' => 'columns',
            'default' => 
            array (
              'code' => '[\'*\']',
              'attributes' => 
              array (
                'startLine' => 1043,
                'endLine' => 1043,
                'startTokenPos' => 4214,
                'startFilePos' => 31837,
                'endTokenPos' => 4216,
                'endFilePos' => 31841,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1043,
            'endLine' => 1043,
            'startColumn' => 53,
            'endColumn' => 68,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'pageName' => 
          array (
            'name' => 'pageName',
            'default' => 
            array (
              'code' => '\'page\'',
              'attributes' => 
              array (
                'startLine' => 1043,
                'endLine' => 1043,
                'startTokenPos' => 4223,
                'startFilePos' => 31856,
                'endTokenPos' => 4223,
                'endFilePos' => 31861,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1043,
            'endLine' => 1043,
            'startColumn' => 71,
            'endColumn' => 88,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'page' => 
          array (
            'name' => 'page',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1043,
                'endLine' => 1043,
                'startTokenPos' => 4230,
                'startFilePos' => 31872,
                'endTokenPos' => 4230,
                'endFilePos' => 31875,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1043,
            'endLine' => 1043,
            'startColumn' => 91,
            'endColumn' => 102,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Paginate the given query into a simple paginator.
 *
 * @param  int|null  $perPage
 * @param  array  $columns
 * @param  string  $pageName
 * @param  int|null  $page
 * @return \\Illuminate\\Contracts\\Pagination\\Paginator<int, TRelatedModel&object{pivot: TPivotModel}>
 */',
        'startLine' => 1043,
        'endLine' => 1050,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'cursorPaginate' => 
      array (
        'name' => 'cursorPaginate',
        'parameters' => 
        array (
          'perPage' => 
          array (
            'name' => 'perPage',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1061,
                'endLine' => 1061,
                'startTokenPos' => 4311,
                'startFilePos' => 32514,
                'endTokenPos' => 4311,
                'endFilePos' => 32517,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1061,
            'endLine' => 1061,
            'startColumn' => 36,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'columns' => 
          array (
            'name' => 'columns',
            'default' => 
            array (
              'code' => '[\'*\']',
              'attributes' => 
              array (
                'startLine' => 1061,
                'endLine' => 1061,
                'startTokenPos' => 4318,
                'startFilePos' => 32531,
                'endTokenPos' => 4320,
                'endFilePos' => 32535,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1061,
            'endLine' => 1061,
            'startColumn' => 53,
            'endColumn' => 68,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'cursorName' => 
          array (
            'name' => 'cursorName',
            'default' => 
            array (
              'code' => '\'cursor\'',
              'attributes' => 
              array (
                'startLine' => 1061,
                'endLine' => 1061,
                'startTokenPos' => 4327,
                'startFilePos' => 32552,
                'endTokenPos' => 4327,
                'endFilePos' => 32559,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1061,
            'endLine' => 1061,
            'startColumn' => 71,
            'endColumn' => 92,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'cursor' => 
          array (
            'name' => 'cursor',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1061,
                'endLine' => 1061,
                'startTokenPos' => 4334,
                'startFilePos' => 32572,
                'endTokenPos' => 4334,
                'endFilePos' => 32575,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1061,
            'endLine' => 1061,
            'startColumn' => 95,
            'endColumn' => 108,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Paginate the given query into a cursor paginator.
 *
 * @param  int|null  $perPage
 * @param  array  $columns
 * @param  string  $cursorName
 * @param  string|null  $cursor
 * @return \\Illuminate\\Contracts\\Pagination\\CursorPaginator<int, TRelatedModel&object{pivot: TPivotModel}>
 */',
        'startLine' => 1061,
        'endLine' => 1068,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'chunk' => 
      array (
        'name' => 'chunk',
        'parameters' => 
        array (
          'count' => 
          array (
            'name' => 'count',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1077,
            'endLine' => 1077,
            'startColumn' => 27,
            'endColumn' => 32,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'callable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1077,
            'endLine' => 1077,
            'startColumn' => 35,
            'endColumn' => 52,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Chunk the results of the query.
 *
 * @param  int  $count
 * @param  callable  $callback
 * @return bool
 */',
        'startLine' => 1077,
        'endLine' => 1084,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'chunkById' => 
      array (
        'name' => 'chunkById',
        'parameters' => 
        array (
          'count' => 
          array (
            'name' => 'count',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1095,
            'endLine' => 1095,
            'startColumn' => 31,
            'endColumn' => 36,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'callable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1095,
            'endLine' => 1095,
            'startColumn' => 39,
            'endColumn' => 56,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'column' => 
          array (
            'name' => 'column',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1095,
                'endLine' => 1095,
                'startTokenPos' => 4500,
                'startFilePos' => 33584,
                'endTokenPos' => 4500,
                'endFilePos' => 33587,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1095,
            'endLine' => 1095,
            'startColumn' => 59,
            'endColumn' => 72,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'alias' => 
          array (
            'name' => 'alias',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1095,
                'endLine' => 1095,
                'startTokenPos' => 4507,
                'startFilePos' => 33599,
                'endTokenPos' => 4507,
                'endFilePos' => 33602,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1095,
            'endLine' => 1095,
            'startColumn' => 75,
            'endColumn' => 87,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Chunk the results of a query by comparing numeric IDs.
 *
 * @param  int  $count
 * @param  callable  $callback
 * @param  string|null  $column
 * @param  string|null  $alias
 * @return bool
 */',
        'startLine' => 1095,
        'endLine' => 1098,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'chunkByIdDesc' => 
      array (
        'name' => 'chunkByIdDesc',
        'parameters' => 
        array (
          'count' => 
          array (
            'name' => 'count',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1109,
            'endLine' => 1109,
            'startColumn' => 35,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'callable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1109,
            'endLine' => 1109,
            'startColumn' => 43,
            'endColumn' => 60,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'column' => 
          array (
            'name' => 'column',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1109,
                'endLine' => 1109,
                'startTokenPos' => 4553,
                'startFilePos' => 34016,
                'endTokenPos' => 4553,
                'endFilePos' => 34019,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1109,
            'endLine' => 1109,
            'startColumn' => 63,
            'endColumn' => 76,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'alias' => 
          array (
            'name' => 'alias',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1109,
                'endLine' => 1109,
                'startTokenPos' => 4560,
                'startFilePos' => 34031,
                'endTokenPos' => 4560,
                'endFilePos' => 34034,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1109,
            'endLine' => 1109,
            'startColumn' => 79,
            'endColumn' => 91,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Chunk the results of a query by comparing IDs in descending order.
 *
 * @param  int  $count
 * @param  callable  $callback
 * @param  string|null  $column
 * @param  string|null  $alias
 * @return bool
 */',
        'startLine' => 1109,
        'endLine' => 1112,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'eachById' => 
      array (
        'name' => 'eachById',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'callable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1123,
            'endLine' => 1123,
            'startColumn' => 30,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'count' => 
          array (
            'name' => 'count',
            'default' => 
            array (
              'code' => '1000',
              'attributes' => 
              array (
                'startLine' => 1123,
                'endLine' => 1123,
                'startTokenPos' => 4611,
                'startFilePos' => 34462,
                'endTokenPos' => 4611,
                'endFilePos' => 34465,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1123,
            'endLine' => 1123,
            'startColumn' => 50,
            'endColumn' => 62,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'column' => 
          array (
            'name' => 'column',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1123,
                'endLine' => 1123,
                'startTokenPos' => 4618,
                'startFilePos' => 34478,
                'endTokenPos' => 4618,
                'endFilePos' => 34481,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1123,
            'endLine' => 1123,
            'startColumn' => 65,
            'endColumn' => 78,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'alias' => 
          array (
            'name' => 'alias',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1123,
                'endLine' => 1123,
                'startTokenPos' => 4625,
                'startFilePos' => 34493,
                'endTokenPos' => 4625,
                'endFilePos' => 34496,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1123,
            'endLine' => 1123,
            'startColumn' => 81,
            'endColumn' => 93,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Execute a callback over each item while chunking by ID.
 *
 * @param  callable  $callback
 * @param  int  $count
 * @param  string|null  $column
 * @param  string|null  $alias
 * @return bool
 */',
        'startLine' => 1123,
        'endLine' => 1132,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'orderedChunkById' => 
      array (
        'name' => 'orderedChunkById',
        'parameters' => 
        array (
          'count' => 
          array (
            'name' => 'count',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1144,
            'endLine' => 1144,
            'startColumn' => 38,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'callable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1144,
            'endLine' => 1144,
            'startColumn' => 46,
            'endColumn' => 63,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'column' => 
          array (
            'name' => 'column',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1144,
                'endLine' => 1144,
                'startTokenPos' => 4750,
                'startFilePos' => 35202,
                'endTokenPos' => 4750,
                'endFilePos' => 35205,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1144,
            'endLine' => 1144,
            'startColumn' => 66,
            'endColumn' => 79,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'alias' => 
          array (
            'name' => 'alias',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1144,
                'endLine' => 1144,
                'startTokenPos' => 4757,
                'startFilePos' => 35217,
                'endTokenPos' => 4757,
                'endFilePos' => 35220,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1144,
            'endLine' => 1144,
            'startColumn' => 82,
            'endColumn' => 94,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
          'descending' => 
          array (
            'name' => 'descending',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 1144,
                'endLine' => 1144,
                'startTokenPos' => 4764,
                'startFilePos' => 35237,
                'endTokenPos' => 4764,
                'endFilePos' => 35241,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1144,
            'endLine' => 1144,
            'startColumn' => 97,
            'endColumn' => 115,
            'parameterIndex' => 4,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Chunk the results of a query by comparing IDs in a given order.
 *
 * @param  int  $count
 * @param  callable  $callback
 * @param  string|null  $column
 * @param  string|null  $alias
 * @param  SortDirection|bool  $descending
 * @return bool
 */',
        'startLine' => 1144,
        'endLine' => 1157,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'each' => 
      array (
        'name' => 'each',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'callable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1166,
            'endLine' => 1166,
            'startColumn' => 26,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'count' => 
          array (
            'name' => 'count',
            'default' => 
            array (
              'code' => '1000',
              'attributes' => 
              array (
                'startLine' => 1166,
                'endLine' => 1166,
                'startTokenPos' => 4887,
                'startFilePos' => 35891,
                'endTokenPos' => 4887,
                'endFilePos' => 35894,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1166,
            'endLine' => 1166,
            'startColumn' => 46,
            'endColumn' => 58,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Execute a callback over each item while chunking.
 *
 * @param  callable  $callback
 * @param  int  $count
 * @return bool
 */',
        'startLine' => 1166,
        'endLine' => 1175,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'lazy' => 
      array (
        'name' => 'lazy',
        'parameters' => 
        array (
          'chunkSize' => 
          array (
            'name' => 'chunkSize',
            'default' => 
            array (
              'code' => '1000',
              'attributes' => 
              array (
                'startLine' => 1183,
                'endLine' => 1183,
                'startTokenPos' => 4976,
                'startFilePos' => 36409,
                'endTokenPos' => 4976,
                'endFilePos' => 36412,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1183,
            'endLine' => 1183,
            'startColumn' => 26,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Query lazily, by chunks of the given size.
 *
 * @param  int  $chunkSize
 * @return \\Illuminate\\Support\\LazyCollection<int, TRelatedModel&object{pivot: TPivotModel}>
 */',
        'startLine' => 1183,
        'endLine' => 1190,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'lazyById' => 
      array (
        'name' => 'lazyById',
        'parameters' => 
        array (
          'chunkSize' => 
          array (
            'name' => 'chunkSize',
            'default' => 
            array (
              'code' => '1000',
              'attributes' => 
              array (
                'startLine' => 1200,
                'endLine' => 1200,
                'startTokenPos' => 5037,
                'startFilePos' => 36944,
                'endTokenPos' => 5037,
                'endFilePos' => 36947,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1200,
            'endLine' => 1200,
            'startColumn' => 30,
            'endColumn' => 46,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'column' => 
          array (
            'name' => 'column',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1200,
                'endLine' => 1200,
                'startTokenPos' => 5044,
                'startFilePos' => 36960,
                'endTokenPos' => 5044,
                'endFilePos' => 36963,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1200,
            'endLine' => 1200,
            'startColumn' => 49,
            'endColumn' => 62,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'alias' => 
          array (
            'name' => 'alias',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1200,
                'endLine' => 1200,
                'startTokenPos' => 5051,
                'startFilePos' => 36975,
                'endTokenPos' => 5051,
                'endFilePos' => 36978,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1200,
            'endLine' => 1200,
            'startColumn' => 65,
            'endColumn' => 77,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Query lazily, by chunking the results of a query by comparing IDs.
 *
 * @param  int  $chunkSize
 * @param  string|null  $column
 * @param  string|null  $alias
 * @return \\Illuminate\\Support\\LazyCollection<int, TRelatedModel&object{pivot: TPivotModel}>
 */',
        'startLine' => 1200,
        'endLine' => 1213,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'lazyByIdDesc' => 
      array (
        'name' => 'lazyByIdDesc',
        'parameters' => 
        array (
          'chunkSize' => 
          array (
            'name' => 'chunkSize',
            'default' => 
            array (
              'code' => '1000',
              'attributes' => 
              array (
                'startLine' => 1223,
                'endLine' => 1223,
                'startTokenPos' => 5151,
                'startFilePos' => 37710,
                'endTokenPos' => 5151,
                'endFilePos' => 37713,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1223,
            'endLine' => 1223,
            'startColumn' => 34,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'column' => 
          array (
            'name' => 'column',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1223,
                'endLine' => 1223,
                'startTokenPos' => 5158,
                'startFilePos' => 37726,
                'endTokenPos' => 5158,
                'endFilePos' => 37729,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1223,
            'endLine' => 1223,
            'startColumn' => 53,
            'endColumn' => 66,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'alias' => 
          array (
            'name' => 'alias',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1223,
                'endLine' => 1223,
                'startTokenPos' => 5165,
                'startFilePos' => 37741,
                'endTokenPos' => 5165,
                'endFilePos' => 37744,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1223,
            'endLine' => 1223,
            'startColumn' => 69,
            'endColumn' => 81,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Query lazily, by chunking the results of a query by comparing IDs in descending order.
 *
 * @param  int  $chunkSize
 * @param  string|null  $column
 * @param  string|null  $alias
 * @return \\Illuminate\\Support\\LazyCollection<int, TRelatedModel&object{pivot: TPivotModel}>
 */',
        'startLine' => 1223,
        'endLine' => 1236,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'cursor' => 
      array (
        'name' => 'cursor',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get a lazy collection for the given query.
 *
 * @return \\Illuminate\\Support\\LazyCollection<int, TRelatedModel&object{pivot: TPivotModel}>
 */',
        'startLine' => 1243,
        'endLine' => 1250,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'prepareQueryBuilder' => 
      array (
        'name' => 'prepareQueryBuilder',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Prepare the query builder for query execution.
 *
 * @return \\Illuminate\\Database\\Eloquent\\Builder<TRelatedModel>
 */',
        'startLine' => 1257,
        'endLine' => 1260,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'hydratePivotRelation' => 
      array (
        'name' => 'hydratePivotRelation',
        'parameters' => 
        array (
          'models' => 
          array (
            'name' => 'models',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1268,
            'endLine' => 1268,
            'startColumn' => 45,
            'endColumn' => 57,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Hydrate the pivot table relationship on the models.
 *
 * @param  array<int, TRelatedModel>  $models
 * @return void
 */',
        'startLine' => 1268,
        'endLine' => 1280,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'migratePivotAttributes' => 
      array (
        'name' => 'migratePivotAttributes',
        'parameters' => 
        array (
          'model' => 
          array (
            'name' => 'model',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Database\\Eloquent\\Model',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1288,
            'endLine' => 1288,
            'startColumn' => 47,
            'endColumn' => 58,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the pivot attributes from a model.
 *
 * @param  TRelatedModel  $model
 * @return array
 */',
        'startLine' => 1288,
        'endLine' => 1304,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'touchIfTouching' => 
      array (
        'name' => 'touchIfTouching',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * If we\'re touching the parent model, touch.
 *
 * @return void
 */',
        'startLine' => 1311,
        'endLine' => 1320,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'touchingParent' => 
      array (
        'name' => 'touchingParent',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if we should touch the parent on sync.
 *
 * @return bool
 */',
        'startLine' => 1327,
        'endLine' => 1330,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'guessInverseRelation' => 
      array (
        'name' => 'guessInverseRelation',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Attempt to guess the name of the inverse of the relation.
 *
 * @return string
 */',
        'startLine' => 1337,
        'endLine' => 1340,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'touch' => 
      array (
        'name' => 'touch',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Touch all of the related models for the relationship.
 *
 * E.g.: Touch all roles associated with this user.
 *
 * @return void
 */',
        'startLine' => 1349,
        'endLine' => 1365,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'allRelatedIds' => 
      array (
        'name' => 'allRelatedIds',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get all of the IDs for the related models.
 *
 * @return \\Illuminate\\Support\\Collection<int, int|string>
 */',
        'startLine' => 1372,
        'endLine' => 1375,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'save' => 
      array (
        'name' => 'save',
        'parameters' => 
        array (
          'model' => 
          array (
            'name' => 'model',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Database\\Eloquent\\Model',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1385,
            'endLine' => 1385,
            'startColumn' => 26,
            'endColumn' => 37,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'pivotAttributes' => 
          array (
            'name' => 'pivotAttributes',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 1385,
                'endLine' => 1385,
                'startTokenPos' => 5823,
                'startFilePos' => 42494,
                'endTokenPos' => 5824,
                'endFilePos' => 42495,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1385,
            'endLine' => 1385,
            'startColumn' => 40,
            'endColumn' => 66,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'touch' => 
          array (
            'name' => 'touch',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 1385,
                'endLine' => 1385,
                'startTokenPos' => 5831,
                'startFilePos' => 42507,
                'endTokenPos' => 5831,
                'endFilePos' => 42510,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1385,
            'endLine' => 1385,
            'startColumn' => 69,
            'endColumn' => 81,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Save a new model and attach it to the parent model.
 *
 * @param  TRelatedModel  $model
 * @param  array  $pivotAttributes
 * @param  bool  $touch
 * @return TRelatedModel&object{pivot: TPivotModel}
 */',
        'startLine' => 1385,
        'endLine' => 1392,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'saveQuietly' => 
      array (
        'name' => 'saveQuietly',
        'parameters' => 
        array (
          'model' => 
          array (
            'name' => 'model',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Database\\Eloquent\\Model',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1402,
            'endLine' => 1402,
            'startColumn' => 33,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'pivotAttributes' => 
          array (
            'name' => 'pivotAttributes',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 1402,
                'endLine' => 1402,
                'startTokenPos' => 5890,
                'startFilePos' => 42990,
                'endTokenPos' => 5891,
                'endFilePos' => 42991,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1402,
            'endLine' => 1402,
            'startColumn' => 47,
            'endColumn' => 73,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'touch' => 
          array (
            'name' => 'touch',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 1402,
                'endLine' => 1402,
                'startTokenPos' => 5898,
                'startFilePos' => 43003,
                'endTokenPos' => 5898,
                'endFilePos' => 43006,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1402,
            'endLine' => 1402,
            'startColumn' => 76,
            'endColumn' => 88,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Save a new model without raising any events and attach it to the parent model.
 *
 * @param  TRelatedModel  $model
 * @param  array  $pivotAttributes
 * @param  bool  $touch
 * @return TRelatedModel&object{pivot: TPivotModel}
 */',
        'startLine' => 1402,
        'endLine' => 1407,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'saveMany' => 
      array (
        'name' => 'saveMany',
        'parameters' => 
        array (
          'models' => 
          array (
            'name' => 'models',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1418,
            'endLine' => 1418,
            'startColumn' => 30,
            'endColumn' => 36,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'pivotAttributes' => 
          array (
            'name' => 'pivotAttributes',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 1418,
                'endLine' => 1418,
                'startTokenPos' => 5967,
                'startFilePos' => 43574,
                'endTokenPos' => 5968,
                'endFilePos' => 43575,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1418,
            'endLine' => 1418,
            'startColumn' => 39,
            'endColumn' => 65,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Save an array of new models and attach them to the parent model.
 *
 * @template TContainer of \\Illuminate\\Support\\Collection<array-key, TRelatedModel>|array<array-key, TRelatedModel>
 *
 * @param  TContainer  $models
 * @param  array  $pivotAttributes
 * @return TContainer
 */',
        'startLine' => 1418,
        'endLine' => 1427,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'saveManyQuietly' => 
      array (
        'name' => 'saveManyQuietly',
        'parameters' => 
        array (
          'models' => 
          array (
            'name' => 'models',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1438,
            'endLine' => 1438,
            'startColumn' => 37,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'pivotAttributes' => 
          array (
            'name' => 'pivotAttributes',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 1438,
                'endLine' => 1438,
                'startTokenPos' => 6048,
                'startFilePos' => 44206,
                'endTokenPos' => 6049,
                'endFilePos' => 44207,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1438,
            'endLine' => 1438,
            'startColumn' => 46,
            'endColumn' => 72,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Save an array of new models without raising any events and attach them to the parent model.
 *
 * @template TContainer of \\Illuminate\\Support\\Collection<array-key, TRelatedModel>|array<array-key, TRelatedModel>
 *
 * @param  TContainer  $models
 * @param  array  $pivotAttributes
 * @return TContainer
 */',
        'startLine' => 1438,
        'endLine' => 1443,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'create' => 
      array (
        'name' => 'create',
        'parameters' => 
        array (
          'attributes' => 
          array (
            'name' => 'attributes',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 1453,
                'endLine' => 1453,
                'startTokenPos' => 6109,
                'startFilePos' => 44650,
                'endTokenPos' => 6110,
                'endFilePos' => 44651,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1453,
            'endLine' => 1453,
            'startColumn' => 28,
            'endColumn' => 49,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'joining' => 
          array (
            'name' => 'joining',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 1453,
                'endLine' => 1453,
                'startTokenPos' => 6119,
                'startFilePos' => 44671,
                'endTokenPos' => 6120,
                'endFilePos' => 44672,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1453,
            'endLine' => 1453,
            'startColumn' => 52,
            'endColumn' => 70,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'touch' => 
          array (
            'name' => 'touch',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 1453,
                'endLine' => 1453,
                'startTokenPos' => 6127,
                'startFilePos' => 44684,
                'endTokenPos' => 6127,
                'endFilePos' => 44687,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1453,
            'endLine' => 1453,
            'startColumn' => 73,
            'endColumn' => 85,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create a new instance of the related model.
 *
 * @param  array  $attributes
 * @param  array  $joining
 * @param  bool  $touch
 * @return TRelatedModel&object{pivot: TPivotModel}
 */',
        'startLine' => 1453,
        'endLine' => 1467,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'createMany' => 
      array (
        'name' => 'createMany',
        'parameters' => 
        array (
          'records' => 
          array (
            'name' => 'records',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'iterable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1476,
            'endLine' => 1476,
            'startColumn' => 32,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'joinings' => 
          array (
            'name' => 'joinings',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 1476,
                'endLine' => 1476,
                'startTokenPos' => 6225,
                'startFilePos' => 45516,
                'endTokenPos' => 6226,
                'endFilePos' => 45517,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1476,
            'endLine' => 1476,
            'startColumn' => 51,
            'endColumn' => 70,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create an array of new instances of the related models.
 *
 * @param  iterable  $records
 * @param  array  $joinings
 * @return array<int, TRelatedModel&object{pivot: TPivotModel}>
 */',
        'startLine' => 1476,
        'endLine' => 1487,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'createQuietly' => 
      array (
        'name' => 'createQuietly',
        'parameters' => 
        array (
          'attributes' => 
          array (
            'name' => 'attributes',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 1497,
                'endLine' => 1497,
                'startTokenPos' => 6317,
                'startFilePos' => 46109,
                'endTokenPos' => 6318,
                'endFilePos' => 46110,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1497,
            'endLine' => 1497,
            'startColumn' => 35,
            'endColumn' => 56,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'joining' => 
          array (
            'name' => 'joining',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 1497,
                'endLine' => 1497,
                'startTokenPos' => 6327,
                'startFilePos' => 46130,
                'endTokenPos' => 6328,
                'endFilePos' => 46131,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1497,
            'endLine' => 1497,
            'startColumn' => 59,
            'endColumn' => 77,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'touch' => 
          array (
            'name' => 'touch',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 1497,
                'endLine' => 1497,
                'startTokenPos' => 6335,
                'startFilePos' => 46143,
                'endTokenPos' => 6335,
                'endFilePos' => 46146,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1497,
            'endLine' => 1497,
            'startColumn' => 80,
            'endColumn' => 92,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create a new instance of the related model without raising any events and attach it to the parent model.
 *
 * @param  array  $attributes
 * @param  array  $joining
 * @param  bool  $touch
 * @return TRelatedModel&object{pivot: TPivotModel}
 */',
        'startLine' => 1497,
        'endLine' => 1500,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'createManyQuietly' => 
      array (
        'name' => 'createManyQuietly',
        'parameters' => 
        array (
          'records' => 
          array (
            'name' => 'records',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'iterable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1509,
            'endLine' => 1509,
            'startColumn' => 39,
            'endColumn' => 55,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'joinings' => 
          array (
            'name' => 'joinings',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 1509,
                'endLine' => 1509,
                'startTokenPos' => 6389,
                'startFilePos' => 46612,
                'endTokenPos' => 6390,
                'endFilePos' => 46613,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1509,
            'endLine' => 1509,
            'startColumn' => 58,
            'endColumn' => 77,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create an array of new instances of the related models without raising any events and attach them to the parent model.
 *
 * @param  iterable  $records
 * @param  array  $joinings
 * @return array<int, TRelatedModel&object{pivot: TPivotModel}>
 */',
        'startLine' => 1509,
        'endLine' => 1512,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'getRelationExistenceQuery' => 
      array (
        'name' => 'getRelationExistenceQuery',
        'parameters' => 
        array (
          'query' => 
          array (
            'name' => 'query',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Database\\Eloquent\\Builder',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1515,
            'endLine' => 1515,
            'startColumn' => 47,
            'endColumn' => 60,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'parentQuery' => 
          array (
            'name' => 'parentQuery',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Database\\Eloquent\\Builder',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1515,
            'endLine' => 1515,
            'startColumn' => 63,
            'endColumn' => 82,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'columns' => 
          array (
            'name' => 'columns',
            'default' => 
            array (
              'code' => '[\'*\']',
              'attributes' => 
              array (
                'startLine' => 1515,
                'endLine' => 1515,
                'startTokenPos' => 6444,
                'startFilePos' => 46833,
                'endTokenPos' => 6446,
                'endFilePos' => 46837,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1515,
            'endLine' => 1515,
            'startColumn' => 85,
            'endColumn' => 100,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/** @inheritDoc */',
        'startLine' => 1515,
        'endLine' => 1524,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'getRelationExistenceQueryForSelfJoin' => 
      array (
        'name' => 'getRelationExistenceQueryForSelfJoin',
        'parameters' => 
        array (
          'query' => 
          array (
            'name' => 'query',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Database\\Eloquent\\Builder',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1534,
            'endLine' => 1534,
            'startColumn' => 58,
            'endColumn' => 71,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'parentQuery' => 
          array (
            'name' => 'parentQuery',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Database\\Eloquent\\Builder',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1534,
            'endLine' => 1534,
            'startColumn' => 74,
            'endColumn' => 93,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'columns' => 
          array (
            'name' => 'columns',
            'default' => 
            array (
              'code' => '[\'*\']',
              'attributes' => 
              array (
                'startLine' => 1534,
                'endLine' => 1534,
                'startTokenPos' => 6541,
                'startFilePos' => 47612,
                'endTokenPos' => 6543,
                'endFilePos' => 47616,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1534,
            'endLine' => 1534,
            'startColumn' => 96,
            'endColumn' => 111,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Add the constraints for a relationship query on the same table.
 *
 * @param  \\Illuminate\\Database\\Eloquent\\Builder<TRelatedModel>  $query
 * @param  \\Illuminate\\Database\\Eloquent\\Builder<TDeclaringModel>  $parentQuery
 * @param  mixed  $columns
 * @return \\Illuminate\\Database\\Eloquent\\Builder<TRelatedModel>
 */',
        'startLine' => 1534,
        'endLine' => 1545,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'take' => 
      array (
        'name' => 'take',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1553,
            'endLine' => 1553,
            'startColumn' => 26,
            'endColumn' => 31,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Alias to set the "limit" value of the query.
 *
 * @param  int  $value
 * @return $this
 */',
        'startLine' => 1553,
        'endLine' => 1556,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'limit' => 
      array (
        'name' => 'limit',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1564,
            'endLine' => 1564,
            'startColumn' => 27,
            'endColumn' => 32,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set the "limit" value of the query.
 *
 * @param  int  $value
 * @return $this
 */',
        'startLine' => 1564,
        'endLine' => 1581,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'getExistenceCompareKey' => 
      array (
        'name' => 'getExistenceCompareKey',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the key for comparing against the parent key in "has" query.
 *
 * @return string
 */',
        'startLine' => 1588,
        'endLine' => 1591,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'withTimestamps' => 
      array (
        'name' => 'withTimestamps',
        'parameters' => 
        array (
          'createdAt' => 
          array (
            'name' => 'createdAt',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1600,
                'endLine' => 1600,
                'startTokenPos' => 6816,
                'startFilePos' => 49270,
                'endTokenPos' => 6816,
                'endFilePos' => 49273,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1600,
            'endLine' => 1600,
            'startColumn' => 36,
            'endColumn' => 52,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'updatedAt' => 
          array (
            'name' => 'updatedAt',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 1600,
                'endLine' => 1600,
                'startTokenPos' => 6823,
                'startFilePos' => 49289,
                'endTokenPos' => 6823,
                'endFilePos' => 49292,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1600,
            'endLine' => 1600,
            'startColumn' => 55,
            'endColumn' => 71,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Specify that the pivot table has creation and update timestamps.
 *
 * @param  string|null|false  $createdAt
 * @param  string|null|false  $updatedAt
 * @return $this
 */',
        'startLine' => 1600,
        'endLine' => 1613,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'createdAt' => 
      array (
        'name' => 'createdAt',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the name of the "created at" column.
 *
 * @return string
 */',
        'startLine' => 1620,
        'endLine' => 1623,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'updatedAt' => 
      array (
        'name' => 'updatedAt',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the name of the "updated at" column.
 *
 * @return string
 */',
        'startLine' => 1630,
        'endLine' => 1633,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'getForeignPivotKeyName' => 
      array (
        'name' => 'getForeignPivotKeyName',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the foreign key for the relation.
 *
 * @return string
 */',
        'startLine' => 1640,
        'endLine' => 1643,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'getQualifiedForeignPivotKeyName' => 
      array (
        'name' => 'getQualifiedForeignPivotKeyName',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the fully-qualified foreign key for the relation.
 *
 * @return string
 */',
        'startLine' => 1650,
        'endLine' => 1653,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'getRelatedPivotKeyName' => 
      array (
        'name' => 'getRelatedPivotKeyName',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the "related key" for the relation.
 *
 * @return string
 */',
        'startLine' => 1660,
        'endLine' => 1663,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'getQualifiedRelatedPivotKeyName' => 
      array (
        'name' => 'getQualifiedRelatedPivotKeyName',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the fully-qualified "related key" for the relation.
 *
 * @return string
 */',
        'startLine' => 1670,
        'endLine' => 1673,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'getParentKeyName' => 
      array (
        'name' => 'getParentKeyName',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the parent key for the relationship.
 *
 * @return string
 */',
        'startLine' => 1680,
        'endLine' => 1683,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'getQualifiedParentKeyName' => 
      array (
        'name' => 'getQualifiedParentKeyName',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the fully-qualified parent key name for the relation.
 *
 * @return string
 */',
        'startLine' => 1690,
        'endLine' => 1693,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'getRelatedKeyName' => 
      array (
        'name' => 'getRelatedKeyName',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the related key for the relationship.
 *
 * @return string
 */',
        'startLine' => 1700,
        'endLine' => 1703,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'getQualifiedRelatedKeyName' => 
      array (
        'name' => 'getQualifiedRelatedKeyName',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the fully-qualified related key name for the relation.
 *
 * @return string
 */',
        'startLine' => 1710,
        'endLine' => 1713,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'getTable' => 
      array (
        'name' => 'getTable',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the intermediate table for the relationship.
 *
 * @return string
 */',
        'startLine' => 1720,
        'endLine' => 1723,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'getRelationName' => 
      array (
        'name' => 'getRelationName',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the relationship name for the relationship.
 *
 * @return string
 */',
        'startLine' => 1730,
        'endLine' => 1733,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'getPivotAccessor' => 
      array (
        'name' => 'getPivotAccessor',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the name of the pivot accessor for this relationship.
 *
 * @return TAccessor
 */',
        'startLine' => 1740,
        'endLine' => 1743,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'getPivotColumns' => 
      array (
        'name' => 'getPivotColumns',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the pivot columns for this relationship.
 *
 * @return array<string|\\Illuminate\\Contracts\\Database\\Query\\Expression>
 */',
        'startLine' => 1750,
        'endLine' => 1753,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'aliasName' => NULL,
      ),
      'qualifyPivotColumn' => 
      array (
        'name' => 'qualifyPivotColumn',
        'parameters' => 
        array (
          'column' => 
          array (
            'name' => 'column',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1761,
            'endLine' => 1761,
            'startColumn' => 40,
            'endColumn' => 46,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Qualify the given column name by the pivot table.
 *
 * @param  string|\\Illuminate\\Contracts\\Database\\Query\\Expression  $column
 * @return string|\\Illuminate\\Contracts\\Database\\Query\\Expression
 */',
        'startLine' => 1761,
        'endLine' => 1770,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Eloquent\\Relations',
        'declaringClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'implementingClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'currentClassName' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
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