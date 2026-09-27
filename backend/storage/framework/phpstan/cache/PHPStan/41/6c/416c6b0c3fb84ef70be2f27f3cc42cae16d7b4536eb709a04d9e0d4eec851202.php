<?php declare(strict_types = 1);

// osfsl-C:/Projects/dental-final/backend/vendor/composer/../laravel/framework/src/Illuminate/Validation/Rule.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Illuminate\Validation\Rule
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-14033e82970ead452f4ae6393017f3352ecd5687c1527cbcb8f494f870c6aad5-8.4-6.73.0.5',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Illuminate\\Validation\\Rule',
        'filename' => 'C:/Projects/dental-final/backend/vendor/composer/../laravel/framework/src/Illuminate/Validation/Rule.php',
      ),
    ),
    'namespace' => 'Illuminate\\Validation',
    'name' => 'Illuminate\\Validation\\Rule',
    'shortName' => 'Rule',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 30,
    'endLine' => 371,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
      0 => 'Illuminate\\Support\\Traits\\Macroable',
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
    ),
    'immediateMethods' => 
    array (
      'can' => 
      array (
        'name' => 'can',
        'parameters' => 
        array (
          'ability' => 
          array (
            'name' => 'ability',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 41,
            'endLine' => 41,
            'startColumn' => 32,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'arguments' => 
          array (
            'name' => 'arguments',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => true,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 41,
            'endLine' => 41,
            'startColumn' => 42,
            'endColumn' => 54,
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
 * Get a can constraint builder instance.
 *
 * @param  string  $ability
 * @param  mixed  ...$arguments
 * @return \\Illuminate\\Validation\\Rules\\Can
 */',
        'startLine' => 41,
        'endLine' => 44,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
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
            'startLine' => 54,
            'endLine' => 54,
            'startColumn' => 33,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'rules' => 
          array (
            'name' => 'rules',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 54,
            'endLine' => 54,
            'startColumn' => 45,
            'endColumn' => 50,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'defaultRules' => 
          array (
            'name' => 'defaultRules',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 54,
                'endLine' => 54,
                'startTokenPos' => 192,
                'startFilePos' => 1977,
                'endTokenPos' => 193,
                'endFilePos' => 1978,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 54,
            'endLine' => 54,
            'startColumn' => 53,
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
 * Apply the given rules if the given condition is truthy.
 *
 * @param  callable|bool  $condition
 * @param  \\Illuminate\\Contracts\\Validation\\ValidationRule|\\Illuminate\\Contracts\\Validation\\InvokableRule|\\Illuminate\\Contracts\\Validation\\Rule|\\Closure|array|string  $rules
 * @param  \\Illuminate\\Contracts\\Validation\\ValidationRule|\\Illuminate\\Contracts\\Validation\\InvokableRule|\\Illuminate\\Contracts\\Validation\\Rule|\\Closure|array|string  $defaultRules
 * @return \\Illuminate\\Validation\\ConditionalRules
 */',
        'startLine' => 54,
        'endLine' => 57,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
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
            'startLine' => 67,
            'endLine' => 67,
            'startColumn' => 35,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'rules' => 
          array (
            'name' => 'rules',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 67,
            'endLine' => 67,
            'startColumn' => 47,
            'endColumn' => 52,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'defaultRules' => 
          array (
            'name' => 'defaultRules',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 67,
                'endLine' => 67,
                'startTokenPos' => 236,
                'startFilePos' => 2680,
                'endTokenPos' => 237,
                'endFilePos' => 2681,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 67,
            'endLine' => 67,
            'startColumn' => 55,
            'endColumn' => 72,
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
 * Apply the given rules if the given condition is falsy.
 *
 * @param  callable|bool  $condition
 * @param  \\Illuminate\\Contracts\\Validation\\ValidationRule|\\Illuminate\\Contracts\\Validation\\InvokableRule|\\Illuminate\\Contracts\\Validation\\Rule|\\Closure|array|string  $rules
 * @param  \\Illuminate\\Contracts\\Validation\\ValidationRule|\\Illuminate\\Contracts\\Validation\\InvokableRule|\\Illuminate\\Contracts\\Validation\\Rule|\\Closure|array|string  $defaultRules
 * @return \\Illuminate\\Validation\\ConditionalRules
 */',
        'startLine' => 67,
        'endLine' => 70,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'array' => 
      array (
        'name' => 'array',
        'parameters' => 
        array (
          'keys' => 
          array (
            'name' => 'keys',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 78,
                'endLine' => 78,
                'startTokenPos' => 274,
                'startFilePos' => 2963,
                'endTokenPos' => 274,
                'endFilePos' => 2966,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 78,
            'endLine' => 78,
            'startColumn' => 34,
            'endColumn' => 45,
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
 * Get an array rule builder instance.
 *
 * @param  array|null  $keys
 * @return \\Illuminate\\Validation\\Rules\\ArrayRule
 */',
        'startLine' => 78,
        'endLine' => 81,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'arrayKeys' => 
      array (
        'name' => 'arrayKeys',
        'parameters' => 
        array (
          'keys' => 
          array (
            'name' => 'keys',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 89,
            'endLine' => 89,
            'startColumn' => 38,
            'endColumn' => 42,
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
 * Get an array keys rule builder instance.
 *
 * @param  \\Illuminate\\Contracts\\Support\\Arrayable|array|string  $keys
 * @return \\Illuminate\\Validation\\Rules\\ArrayKeys
 */',
        'startLine' => 89,
        'endLine' => 92,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'forEach' => 
      array (
        'name' => 'forEach',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 100,
            'endLine' => 100,
            'startColumn' => 36,
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
 * Create a new nested rule set.
 *
 * @param  callable  $callback
 * @return \\Illuminate\\Validation\\NestedRules
 */',
        'startLine' => 100,
        'endLine' => 103,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'unique' => 
      array (
        'name' => 'unique',
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
            'startLine' => 112,
            'endLine' => 112,
            'startColumn' => 35,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'column' => 
          array (
            'name' => 'column',
            'default' => 
            array (
              'code' => '\'NULL\'',
              'attributes' => 
              array (
                'startLine' => 112,
                'endLine' => 112,
                'startTokenPos' => 368,
                'startFilePos' => 3822,
                'endTokenPos' => 368,
                'endFilePos' => 3827,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 112,
            'endLine' => 112,
            'startColumn' => 43,
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
 * Get a unique constraint builder instance.
 *
 * @param  string  $table
 * @param  string  $column
 * @return \\Illuminate\\Validation\\Rules\\Unique
 */',
        'startLine' => 112,
        'endLine' => 115,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'exists' => 
      array (
        'name' => 'exists',
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
            'startLine' => 124,
            'endLine' => 124,
            'startColumn' => 35,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'column' => 
          array (
            'name' => 'column',
            'default' => 
            array (
              'code' => '\'NULL\'',
              'attributes' => 
              array (
                'startLine' => 124,
                'endLine' => 124,
                'startTokenPos' => 405,
                'startFilePos' => 4124,
                'endTokenPos' => 405,
                'endFilePos' => 4129,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 124,
            'endLine' => 124,
            'startColumn' => 43,
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
 * Get an exists constraint builder instance.
 *
 * @param  string  $table
 * @param  string  $column
 * @return \\Illuminate\\Validation\\Rules\\Exists
 */',
        'startLine' => 124,
        'endLine' => 127,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'in' => 
      array (
        'name' => 'in',
        'parameters' => 
        array (
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
            'startLine' => 135,
            'endLine' => 135,
            'startColumn' => 31,
            'endColumn' => 37,
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
 * Get an in rule builder instance.
 *
 * @param  \\Illuminate\\Contracts\\Support\\Arrayable|\\UnitEnum|array|string  $values
 * @return \\Illuminate\\Validation\\Rules\\In
 */',
        'startLine' => 135,
        'endLine' => 138,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'notIn' => 
      array (
        'name' => 'notIn',
        'parameters' => 
        array (
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
            'startLine' => 146,
            'endLine' => 146,
            'startColumn' => 34,
            'endColumn' => 40,
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
 * Get a not_in rule builder instance.
 *
 * @param  \\Illuminate\\Contracts\\Support\\Arrayable|\\UnitEnum|array|string  $values
 * @return \\Illuminate\\Validation\\Rules\\NotIn
 */',
        'startLine' => 146,
        'endLine' => 149,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'requiredIf' => 
      array (
        'name' => 'requiredIf',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 157,
            'endLine' => 157,
            'startColumn' => 39,
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
 * Get a required_if rule builder instance.
 *
 * @param  (\\Closure(): bool)|bool  $callback
 * @return \\Illuminate\\Validation\\Rules\\RequiredIf
 */',
        'startLine' => 157,
        'endLine' => 160,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'requiredUnless' => 
      array (
        'name' => 'requiredUnless',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
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
            'startColumn' => 43,
            'endColumn' => 51,
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
 * Get a required_unless rule builder instance.
 *
 * @param  (\\Closure(): bool)|bool|null  $callback
 * @return \\Illuminate\\Validation\\Rules\\RequiredUnless
 */',
        'startLine' => 168,
        'endLine' => 171,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'excludeIf' => 
      array (
        'name' => 'excludeIf',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 179,
            'endLine' => 179,
            'startColumn' => 38,
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
 * Get an exclude_if rule builder instance.
 *
 * @param  (\\Closure(): bool)|bool  $callback
 * @return \\Illuminate\\Validation\\Rules\\ExcludeIf
 */',
        'startLine' => 179,
        'endLine' => 182,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'excludeUnless' => 
      array (
        'name' => 'excludeUnless',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 190,
            'endLine' => 190,
            'startColumn' => 42,
            'endColumn' => 50,
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
 * Get an exclude_unless rule builder instance.
 *
 * @param  (\\Closure(): bool)|bool  $callback
 * @return \\Illuminate\\Validation\\Rules\\ExcludeUnless
 */',
        'startLine' => 190,
        'endLine' => 193,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'prohibitedIf' => 
      array (
        'name' => 'prohibitedIf',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 201,
            'endLine' => 201,
            'startColumn' => 41,
            'endColumn' => 49,
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
 * Get a prohibited_if rule builder instance.
 *
 * @param  (\\Closure(): bool)|bool  $callback
 * @return \\Illuminate\\Validation\\Rules\\ProhibitedIf
 */',
        'startLine' => 201,
        'endLine' => 204,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'prohibitedUnless' => 
      array (
        'name' => 'prohibitedUnless',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 212,
            'endLine' => 212,
            'startColumn' => 45,
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
        'docComment' => '/**
 * Get a prohibited_unless rule builder instance.
 *
 * @param  (\\Closure(): bool)|bool  $callback
 * @return \\Illuminate\\Validation\\Rules\\ProhibitedUnless
 */',
        'startLine' => 212,
        'endLine' => 215,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'date' => 
      array (
        'name' => 'date',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get a date rule builder instance.
 *
 * @return \\Illuminate\\Validation\\Rules\\Date
 */',
        'startLine' => 222,
        'endLine' => 225,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'dateTime' => 
      array (
        'name' => 'dateTime',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Validation\\Rules\\Date',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get a datetime rule builder instance.
 */',
        'startLine' => 230,
        'endLine' => 233,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'email' => 
      array (
        'name' => 'email',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get an email rule builder instance.
 *
 * @return \\Illuminate\\Validation\\Rules\\Email
 */',
        'startLine' => 240,
        'endLine' => 243,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'enum' => 
      array (
        'name' => 'enum',
        'parameters' => 
        array (
          'type' => 
          array (
            'name' => 'type',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 251,
            'endLine' => 251,
            'startColumn' => 33,
            'endColumn' => 37,
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
 * Get an enum rule builder instance.
 *
 * @param  class-string  $type
 * @return \\Illuminate\\Validation\\Rules\\Enum
 */',
        'startLine' => 251,
        'endLine' => 254,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'file' => 
      array (
        'name' => 'file',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get a file rule builder instance.
 *
 * @return \\Illuminate\\Validation\\Rules\\File
 */',
        'startLine' => 261,
        'endLine' => 264,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'imageFile' => 
      array (
        'name' => 'imageFile',
        'parameters' => 
        array (
          'allowSvg' => 
          array (
            'name' => 'allowSvg',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 272,
                'endLine' => 272,
                'startTokenPos' => 790,
                'startFilePos' => 7694,
                'endTokenPos' => 790,
                'endFilePos' => 7698,
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
            'startColumn' => 38,
            'endColumn' => 54,
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
 * Get an image file rule builder instance.
 *
 * @param  bool  $allowSvg
 * @return \\Illuminate\\Validation\\Rules\\ImageFile
 */',
        'startLine' => 272,
        'endLine' => 275,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'dimensions' => 
      array (
        'name' => 'dimensions',
        'parameters' => 
        array (
          'constraints' => 
          array (
            'name' => 'constraints',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 283,
                'endLine' => 283,
                'startTokenPos' => 823,
                'startFilePos' => 7974,
                'endTokenPos' => 824,
                'endFilePos' => 7975,
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
            'startLine' => 283,
            'endLine' => 283,
            'startColumn' => 39,
            'endColumn' => 61,
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
 * Get a dimensions rule builder instance.
 *
 * @param  array  $constraints
 * @return \\Illuminate\\Validation\\Rules\\Dimensions
 */',
        'startLine' => 283,
        'endLine' => 286,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'string' => 
      array (
        'name' => 'string',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get a string rule builder instance.
 *
 * @return \\Illuminate\\Validation\\Rules\\StringRule
 */',
        'startLine' => 293,
        'endLine' => 296,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'numeric' => 
      array (
        'name' => 'numeric',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get a numeric rule builder instance.
 *
 * @return \\Illuminate\\Validation\\Rules\\Numeric
 */',
        'startLine' => 303,
        'endLine' => 306,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'anyOf' => 
      array (
        'name' => 'anyOf',
        'parameters' => 
        array (
          'rules' => 
          array (
            'name' => 'rules',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 316,
            'endLine' => 316,
            'startColumn' => 34,
            'endColumn' => 39,
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
 * Get an "any of" rule builder instance.
 *
 * @param  array  $rules
 * @return \\Illuminate\\Validation\\Rules\\AnyOf
 *
 * @throws \\InvalidArgumentException
 */',
        'startLine' => 316,
        'endLine' => 319,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'contains' => 
      array (
        'name' => 'contains',
        'parameters' => 
        array (
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
            'startLine' => 327,
            'endLine' => 327,
            'startColumn' => 37,
            'endColumn' => 43,
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
 * Get a contains rule builder instance.
 *
 * @param  \\Illuminate\\Contracts\\Support\\Arrayable|\\UnitEnum|array|string  $values
 * @return \\Illuminate\\Validation\\Rules\\Contains
 */',
        'startLine' => 327,
        'endLine' => 330,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'doesntContain' => 
      array (
        'name' => 'doesntContain',
        'parameters' => 
        array (
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
            'startLine' => 338,
            'endLine' => 338,
            'startColumn' => 42,
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
 * Get a "does not contain" rule builder instance.
 *
 * @param  \\Illuminate\\Contracts\\Support\\Arrayable|\\UnitEnum|array|string  $values
 * @return \\Illuminate\\Validation\\Rules\\DoesntContain
 */',
        'startLine' => 338,
        'endLine' => 341,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
        'aliasName' => NULL,
      ),
      'compile' => 
      array (
        'name' => 'compile',
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
            'startLine' => 351,
            'endLine' => 351,
            'startColumn' => 36,
            'endColumn' => 45,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'rules' => 
          array (
            'name' => 'rules',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 351,
            'endLine' => 351,
            'startColumn' => 48,
            'endColumn' => 53,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'data' => 
          array (
            'name' => 'data',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 351,
                'endLine' => 351,
                'startTokenPos' => 994,
                'startFilePos' => 9647,
                'endTokenPos' => 994,
                'endFilePos' => 9650,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 351,
            'endLine' => 351,
            'startColumn' => 56,
            'endColumn' => 67,
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
 * Compile a set of rules for an attribute.
 *
 * @param  string  $attribute
 * @param  array  $rules
 * @param  array|null  $data
 * @return object|\\stdClass
 */',
        'startLine' => 351,
        'endLine' => 370,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Validation',
        'declaringClassName' => 'Illuminate\\Validation\\Rule',
        'implementingClassName' => 'Illuminate\\Validation\\Rule',
        'currentClassName' => 'Illuminate\\Validation\\Rule',
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