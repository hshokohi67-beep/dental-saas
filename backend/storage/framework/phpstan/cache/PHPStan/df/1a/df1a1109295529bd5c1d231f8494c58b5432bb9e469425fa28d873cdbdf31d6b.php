<?php declare(strict_types = 1);

// osfsl-C:/Projects/dental-final/backend/vendor/composer/../laravel/framework/src/Illuminate/Http/Resources/ConditionallyLoadsAttributes.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Illuminate\Http\Resources\ConditionallyLoadsAttributes
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-3fb410061f6996c53ccdc37073e2eb2e18b63bd29c426c9202830a56857baada-8.4-6.73.0.5',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'filename' => 'C:/Projects/dental-final/backend/vendor/composer/../laravel/framework/src/Illuminate/Http/Resources/ConditionallyLoadsAttributes.php',
      ),
    ),
    'namespace' => 'Illuminate\\Http\\Resources',
    'name' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
    'shortName' => 'ConditionallyLoadsAttributes',
    'isInterface' => false,
    'isTrait' => true,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 10,
    'endLine' => 456,
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
      'cachedPreserveKeysAttributes' => 
      array (
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'name' => 'cachedPreserveKeysAttributes',
        'modifiers' => 18,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 17,
            'endLine' => 17,
            'startTokenPos' => 43,
            'startFilePos' => 386,
            'endTokenPos' => 44,
            'endFilePos' => 387,
          ),
        ),
        'docComment' => '/**
 * The cached preserve keys attribute values.
 *
 * @var array<class-string, bool>
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 17,
        'endLine' => 17,
        'startColumn' => 5,
        'endColumn' => 56,
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
      'filter' => 
      array (
        'name' => 'filter',
        'parameters' => 
        array (
          'data' => 
          array (
            'name' => 'data',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 25,
            'endLine' => 25,
            'startColumn' => 31,
            'endColumn' => 35,
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
 * Filter the given data, removing any optional values.
 *
 * @param  array  $data
 * @return array
 */',
        'startLine' => 25,
        'endLine' => 51,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'mergeData' => 
      array (
        'name' => 'mergeData',
        'parameters' => 
        array (
          'data' => 
          array (
            'name' => 'data',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 62,
            'endLine' => 62,
            'startColumn' => 34,
            'endColumn' => 38,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'index' => 
          array (
            'name' => 'index',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 62,
            'endLine' => 62,
            'startColumn' => 41,
            'endColumn' => 46,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'merge' => 
          array (
            'name' => 'merge',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 62,
            'endLine' => 62,
            'startColumn' => 49,
            'endColumn' => 54,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
          'numericKeys' => 
          array (
            'name' => 'numericKeys',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 62,
            'endLine' => 62,
            'startColumn' => 57,
            'endColumn' => 68,
            'parameterIndex' => 3,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Merge the given data in at the given index.
 *
 * @param  array  $data
 * @param  int  $index
 * @param  array  $merge
 * @param  bool  $numericKeys
 * @return array
 */',
        'startLine' => 62,
        'endLine' => 74,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'removeMissingValues' => 
      array (
        'name' => 'removeMissingValues',
        'parameters' => 
        array (
          'data' => 
          array (
            'name' => 'data',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 82,
            'endLine' => 82,
            'startColumn' => 44,
            'endColumn' => 48,
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
 * Remove the missing values from the filtered data.
 *
 * @param  array  $data
 * @return array
 */',
        'startLine' => 82,
        'endLine' => 110,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'when' => 
      array (
        'name' => 'when',
        'parameters' => 
        array (
          'condition' => 
          array (
            'name' => 'condition',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 120,
            'endLine' => 120,
            'startColumn' => 29,
            'endColumn' => 38,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
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
            'startLine' => 120,
            'endLine' => 120,
            'startColumn' => 41,
            'endColumn' => 46,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => 'new \\Illuminate\\Http\\Resources\\MissingValue()',
              'attributes' => 
              array (
                'startLine' => 120,
                'endLine' => 120,
                'startTokenPos' => 634,
                'startFilePos' => 3463,
                'endTokenPos' => 636,
                'endFilePos' => 3478,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 120,
            'endLine' => 120,
            'startColumn' => 49,
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
 * Retrieve a value if the given "condition" is truthy.
 *
 * @param  bool  $condition
 * @param  mixed  $value
 * @param  mixed  $default
 * @return \\Illuminate\\Http\\Resources\\MissingValue|mixed
 */',
        'startLine' => 120,
        'endLine' => 127,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'unless' => 
      array (
        'name' => 'unless',
        'parameters' => 
        array (
          'condition' => 
          array (
            'name' => 'condition',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 137,
            'endLine' => 137,
            'startColumn' => 28,
            'endColumn' => 37,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
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
            'startLine' => 137,
            'endLine' => 137,
            'startColumn' => 40,
            'endColumn' => 45,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => 'new \\Illuminate\\Http\\Resources\\MissingValue()',
              'attributes' => 
              array (
                'startLine' => 137,
                'endLine' => 137,
                'startTokenPos' => 701,
                'startFilePos' => 3925,
                'endTokenPos' => 703,
                'endFilePos' => 3940,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 137,
            'endLine' => 137,
            'startColumn' => 48,
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
 * Retrieve a value if the given "condition" is falsy.
 *
 * @param  bool  $condition
 * @param  mixed  $value
 * @param  mixed  $default
 * @return \\Illuminate\\Http\\Resources\\MissingValue|mixed
 */',
        'startLine' => 137,
        'endLine' => 142,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'merge' => 
      array (
        'name' => 'merge',
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
            'startLine' => 150,
            'endLine' => 150,
            'startColumn' => 30,
            'endColumn' => 35,
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
 * Merge a value into the array.
 *
 * @param  mixed  $value
 * @return \\Illuminate\\Http\\Resources\\MergeValue|mixed
 */',
        'startLine' => 150,
        'endLine' => 153,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'mergeWhen' => 
      array (
        'name' => 'mergeWhen',
        'parameters' => 
        array (
          'condition' => 
          array (
            'name' => 'condition',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 163,
            'endLine' => 163,
            'startColumn' => 34,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
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
            'startLine' => 163,
            'endLine' => 163,
            'startColumn' => 46,
            'endColumn' => 51,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => 'new \\Illuminate\\Http\\Resources\\MissingValue()',
              'attributes' => 
              array (
                'startLine' => 163,
                'endLine' => 163,
                'startTokenPos' => 800,
                'startFilePos' => 4628,
                'endTokenPos' => 802,
                'endFilePos' => 4643,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 163,
            'endLine' => 163,
            'startColumn' => 54,
            'endColumn' => 80,
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
 * Merge a value if the given condition is truthy.
 *
 * @param  bool  $condition
 * @param  mixed  $value
 * @param  mixed  $default
 * @return \\Illuminate\\Http\\Resources\\MergeValue|mixed
 */',
        'startLine' => 163,
        'endLine' => 170,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'mergeUnless' => 
      array (
        'name' => 'mergeUnless',
        'parameters' => 
        array (
          'condition' => 
          array (
            'name' => 'condition',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 180,
            'endLine' => 180,
            'startColumn' => 36,
            'endColumn' => 45,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
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
            'startLine' => 180,
            'endLine' => 180,
            'startColumn' => 48,
            'endColumn' => 53,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => 'new \\Illuminate\\Http\\Resources\\MissingValue()',
              'attributes' => 
              array (
                'startLine' => 180,
                'endLine' => 180,
                'startTokenPos' => 877,
                'startFilePos' => 5128,
                'endTokenPos' => 879,
                'endFilePos' => 5143,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 180,
            'endLine' => 180,
            'startColumn' => 56,
            'endColumn' => 82,
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
 * Merge a value unless the given condition is truthy.
 *
 * @param  bool  $condition
 * @param  mixed  $value
 * @param  mixed  $default
 * @return \\Illuminate\\Http\\Resources\\MergeValue|mixed
 */',
        'startLine' => 180,
        'endLine' => 185,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'attributes' => 
      array (
        'name' => 'attributes',
        'parameters' => 
        array (
          'attributes' => 
          array (
            'name' => 'attributes',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 193,
            'endLine' => 193,
            'startColumn' => 35,
            'endColumn' => 45,
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
 * Merge the given attributes.
 *
 * @param  array  $attributes
 * @return \\Illuminate\\Http\\Resources\\MergeValue
 */',
        'startLine' => 193,
        'endLine' => 198,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'whenHas' => 
      array (
        'name' => 'whenHas',
        'parameters' => 
        array (
          'attribute' => 
          array (
            'name' => 'attribute',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 208,
            'endLine' => 208,
            'startColumn' => 29,
            'endColumn' => 38,
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
                'startLine' => 208,
                'endLine' => 208,
                'startTokenPos' => 986,
                'startFilePos' => 5894,
                'endTokenPos' => 986,
                'endFilePos' => 5897,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 208,
            'endLine' => 208,
            'startColumn' => 41,
            'endColumn' => 53,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => 'new \\Illuminate\\Http\\Resources\\MissingValue()',
              'attributes' => 
              array (
                'startLine' => 208,
                'endLine' => 208,
                'startTokenPos' => 993,
                'startFilePos' => 5911,
                'endTokenPos' => 995,
                'endFilePos' => 5926,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 208,
            'endLine' => 208,
            'startColumn' => 56,
            'endColumn' => 82,
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
 * Retrieve an attribute if it exists on the resource.
 *
 * @param  string  $attribute
 * @param  mixed  $value
 * @param  mixed  $default
 * @return \\Illuminate\\Http\\Resources\\MissingValue|mixed
 */',
        'startLine' => 208,
        'endLine' => 217,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'whenNull' => 
      array (
        'name' => 'whenNull',
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
            'startLine' => 226,
            'endLine' => 226,
            'startColumn' => 33,
            'endColumn' => 38,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => 'new \\Illuminate\\Http\\Resources\\MissingValue()',
              'attributes' => 
              array (
                'startLine' => 226,
                'endLine' => 226,
                'startTokenPos' => 1086,
                'startFilePos' => 6454,
                'endTokenPos' => 1088,
                'endFilePos' => 6469,
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
            'startColumn' => 41,
            'endColumn' => 67,
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
 * Retrieve a model attribute if it is null.
 *
 * @param  mixed  $value
 * @param  mixed  $default
 * @return \\Illuminate\\Http\\Resources\\MissingValue|mixed
 */',
        'startLine' => 226,
        'endLine' => 231,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'whenNotNull' => 
      array (
        'name' => 'whenNotNull',
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
            'startLine' => 240,
            'endLine' => 240,
            'startColumn' => 36,
            'endColumn' => 41,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => 'new \\Illuminate\\Http\\Resources\\MissingValue()',
              'attributes' => 
              array (
                'startLine' => 240,
                'endLine' => 240,
                'startTokenPos' => 1155,
                'startFilePos' => 6873,
                'endTokenPos' => 1157,
                'endFilePos' => 6888,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 240,
            'endLine' => 240,
            'startColumn' => 44,
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
 * Retrieve a model attribute if it is not null.
 *
 * @param  mixed  $value
 * @param  mixed  $default
 * @return \\Illuminate\\Http\\Resources\\MissingValue|mixed
 */',
        'startLine' => 240,
        'endLine' => 245,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'whenAppended' => 
      array (
        'name' => 'whenAppended',
        'parameters' => 
        array (
          'attribute' => 
          array (
            'name' => 'attribute',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 255,
            'endLine' => 255,
            'startColumn' => 37,
            'endColumn' => 46,
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
                'startLine' => 255,
                'endLine' => 255,
                'startTokenPos' => 1226,
                'startFilePos' => 7333,
                'endTokenPos' => 1226,
                'endFilePos' => 7336,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 255,
            'endLine' => 255,
            'startColumn' => 49,
            'endColumn' => 61,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => 'new \\Illuminate\\Http\\Resources\\MissingValue()',
              'attributes' => 
              array (
                'startLine' => 255,
                'endLine' => 255,
                'startTokenPos' => 1233,
                'startFilePos' => 7350,
                'endTokenPos' => 1235,
                'endFilePos' => 7365,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 255,
            'endLine' => 255,
            'startColumn' => 64,
            'endColumn' => 90,
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
 * Retrieve an accessor when it has been appended.
 *
 * @param  string  $attribute
 * @param  mixed  $value
 * @param  mixed  $default
 * @return \\Illuminate\\Http\\Resources\\MissingValue|mixed
 */',
        'startLine' => 255,
        'endLine' => 262,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'whenLoaded' => 
      array (
        'name' => 'whenLoaded',
        'parameters' => 
        array (
          'relationship' => 
          array (
            'name' => 'relationship',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 272,
            'endLine' => 272,
            'startColumn' => 35,
            'endColumn' => 47,
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
                'startLine' => 272,
                'endLine' => 272,
                'startTokenPos' => 1322,
                'startFilePos' => 7895,
                'endTokenPos' => 1322,
                'endFilePos' => 7898,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 272,
            'endLine' => 272,
            'startColumn' => 50,
            'endColumn' => 62,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => 'new \\Illuminate\\Http\\Resources\\MissingValue()',
              'attributes' => 
              array (
                'startLine' => 272,
                'endLine' => 272,
                'startTokenPos' => 1329,
                'startFilePos' => 7912,
                'endTokenPos' => 1331,
                'endFilePos' => 7927,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 272,
            'endLine' => 272,
            'startColumn' => 65,
            'endColumn' => 91,
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
 * Retrieve a relationship if it has been loaded.
 *
 * @param  string  $relationship
 * @param  mixed  $value
 * @param  mixed  $default
 * @return \\Illuminate\\Http\\Resources\\MissingValue|mixed
 */',
        'startLine' => 272,
        'endLine' => 293,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'whenCounted' => 
      array (
        'name' => 'whenCounted',
        'parameters' => 
        array (
          'relationship' => 
          array (
            'name' => 'relationship',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 303,
            'endLine' => 303,
            'startColumn' => 33,
            'endColumn' => 45,
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
                'startLine' => 303,
                'endLine' => 303,
                'startTokenPos' => 1466,
                'startFilePos' => 8668,
                'endTokenPos' => 1466,
                'endFilePos' => 8671,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 303,
            'endLine' => 303,
            'startColumn' => 48,
            'endColumn' => 60,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => 'new \\Illuminate\\Http\\Resources\\MissingValue()',
              'attributes' => 
              array (
                'startLine' => 303,
                'endLine' => 303,
                'startTokenPos' => 1473,
                'startFilePos' => 8685,
                'endTokenPos' => 1475,
                'endFilePos' => 8700,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 303,
            'endLine' => 303,
            'startColumn' => 63,
            'endColumn' => 89,
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
 * Retrieve a relationship count if it exists.
 *
 * @param  string  $relationship
 * @param  mixed  $value
 * @param  mixed  $default
 * @return \\Illuminate\\Http\\Resources\\MissingValue|mixed
 */',
        'startLine' => 303,
        'endLine' => 324,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'whenAggregated' => 
      array (
        'name' => 'whenAggregated',
        'parameters' => 
        array (
          'relationship' => 
          array (
            'name' => 'relationship',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 336,
            'endLine' => 336,
            'startColumn' => 36,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
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
            'startLine' => 336,
            'endLine' => 336,
            'startColumn' => 51,
            'endColumn' => 57,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'aggregate' => 
          array (
            'name' => 'aggregate',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 336,
            'endLine' => 336,
            'startColumn' => 60,
            'endColumn' => 69,
            'parameterIndex' => 2,
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
                'startLine' => 336,
                'endLine' => 336,
                'startTokenPos' => 1653,
                'startFilePos' => 9641,
                'endTokenPos' => 1653,
                'endFilePos' => 9644,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 336,
            'endLine' => 336,
            'startColumn' => 72,
            'endColumn' => 84,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => 'new \\Illuminate\\Http\\Resources\\MissingValue()',
              'attributes' => 
              array (
                'startLine' => 336,
                'endLine' => 336,
                'startTokenPos' => 1660,
                'startFilePos' => 9658,
                'endTokenPos' => 1662,
                'endFilePos' => 9673,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 336,
            'endLine' => 336,
            'startColumn' => 87,
            'endColumn' => 113,
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
 * Retrieve a relationship aggregated value if it exists.
 *
 * @param  string  $relationship
 * @param  string  $column
 * @param  string  $aggregate
 * @param  mixed  $value
 * @param  mixed  $default
 * @return \\Illuminate\\Http\\Resources\\MissingValue|mixed
 */',
        'startLine' => 336,
        'endLine' => 357,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'whenExistsLoaded' => 
      array (
        'name' => 'whenExistsLoaded',
        'parameters' => 
        array (
          'relationship' => 
          array (
            'name' => 'relationship',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 367,
            'endLine' => 367,
            'startColumn' => 38,
            'endColumn' => 50,
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
                'startLine' => 367,
                'endLine' => 367,
                'startTokenPos' => 1849,
                'startFilePos' => 10574,
                'endTokenPos' => 1849,
                'endFilePos' => 10577,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 367,
            'endLine' => 367,
            'startColumn' => 53,
            'endColumn' => 65,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => 'new \\Illuminate\\Http\\Resources\\MissingValue()',
              'attributes' => 
              array (
                'startLine' => 367,
                'endLine' => 367,
                'startTokenPos' => 1856,
                'startFilePos' => 10591,
                'endTokenPos' => 1858,
                'endFilePos' => 10606,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 367,
            'endLine' => 367,
            'startColumn' => 68,
            'endColumn' => 94,
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
 * Retrieve a relationship existence check if it exists.
 *
 * @param  string  $relationship
 * @param  mixed  $value
 * @param  mixed  $default
 * @return \\Illuminate\\Http\\Resources\\MissingValue|mixed
 */',
        'startLine' => 367,
        'endLine' => 384,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'whenPivotLoaded' => 
      array (
        'name' => 'whenPivotLoaded',
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
            'startLine' => 394,
            'endLine' => 394,
            'startColumn' => 40,
            'endColumn' => 45,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
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
            'startLine' => 394,
            'endLine' => 394,
            'startColumn' => 48,
            'endColumn' => 53,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => 'new \\Illuminate\\Http\\Resources\\MissingValue()',
              'attributes' => 
              array (
                'startLine' => 394,
                'endLine' => 394,
                'startTokenPos' => 2009,
                'startFilePos' => 11393,
                'endTokenPos' => 2011,
                'endFilePos' => 11408,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 394,
            'endLine' => 394,
            'startColumn' => 56,
            'endColumn' => 82,
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
 * Execute a callback if the given pivot table has been loaded.
 *
 * @param  string  $table
 * @param  mixed  $value
 * @param  mixed  $default
 * @return \\Illuminate\\Http\\Resources\\MissingValue|mixed
 */',
        'startLine' => 394,
        'endLine' => 397,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'whenPivotLoadedAs' => 
      array (
        'name' => 'whenPivotLoadedAs',
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
            'startLine' => 408,
            'endLine' => 408,
            'startColumn' => 42,
            'endColumn' => 50,
            'parameterIndex' => 0,
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
            'startLine' => 408,
            'endLine' => 408,
            'startColumn' => 53,
            'endColumn' => 58,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
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
            'startLine' => 408,
            'endLine' => 408,
            'startColumn' => 61,
            'endColumn' => 66,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => 'new \\Illuminate\\Http\\Resources\\MissingValue()',
              'attributes' => 
              array (
                'startLine' => 408,
                'endLine' => 408,
                'startTokenPos' => 2055,
                'startFilePos' => 11871,
                'endTokenPos' => 2057,
                'endFilePos' => 11886,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 408,
            'endLine' => 408,
            'startColumn' => 69,
            'endColumn' => 95,
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
 * Execute a callback if the given pivot table with a custom accessor has been loaded.
 *
 * @param  string  $accessor
 * @param  string  $table
 * @param  mixed  $value
 * @param  mixed  $default
 * @return \\Illuminate\\Http\\Resources\\MissingValue|mixed
 */',
        'startLine' => 408,
        'endLine' => 415,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'hasPivotLoaded' => 
      array (
        'name' => 'hasPivotLoaded',
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
            'startLine' => 423,
            'endLine' => 423,
            'startColumn' => 39,
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
 * Determine if the resource has the specified pivot table loaded.
 *
 * @param  string  $table
 * @return bool
 */',
        'startLine' => 423,
        'endLine' => 426,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'hasPivotLoadedAs' => 
      array (
        'name' => 'hasPivotLoadedAs',
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
            'startLine' => 435,
            'endLine' => 435,
            'startColumn' => 41,
            'endColumn' => 49,
            'parameterIndex' => 0,
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
            'startLine' => 435,
            'endLine' => 435,
            'startColumn' => 52,
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
        'docComment' => '/**
 * Determine if the resource has the specified pivot table loaded with a custom accessor.
 *
 * @param  string  $accessor
 * @param  string  $table
 * @return bool
 */',
        'startLine' => 435,
        'endLine' => 440,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'aliasName' => NULL,
      ),
      'transform' => 
      array (
        'name' => 'transform',
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
            'startLine' => 450,
            'endLine' => 450,
            'startColumn' => 34,
            'endColumn' => 39,
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
            'startLine' => 450,
            'endLine' => 450,
            'startColumn' => 42,
            'endColumn' => 59,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => 'new \\Illuminate\\Http\\Resources\\MissingValue()',
              'attributes' => 
              array (
                'startLine' => 450,
                'endLine' => 450,
                'startTokenPos' => 2199,
                'startFilePos' => 13014,
                'endTokenPos' => 2201,
                'endFilePos' => 13029,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 450,
            'endLine' => 450,
            'startColumn' => 62,
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
 * Transform the given value if it is present.
 *
 * @param  mixed  $value
 * @param  callable  $callback
 * @param  mixed  $default
 * @return mixed
 */',
        'startLine' => 450,
        'endLine' => 455,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Http\\Resources',
        'declaringClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'implementingClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
        'currentClassName' => 'Illuminate\\Http\\Resources\\ConditionallyLoadsAttributes',
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