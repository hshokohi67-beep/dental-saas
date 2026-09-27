<?php declare(strict_types = 1);

// osfsl-C:/Projects/dental-final/backend/vendor/composer/../laravel/framework/src/Illuminate/Contracts/Routing/ResponseFactory.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Illuminate\Contracts\Routing\ResponseFactory
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-10e033ec20e52d1c9314ed431fa1c933733af8786f243633c627ef6f00260f57-8.4-6.73.0.5',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'filename' => 'C:/Projects/dental-final/backend/vendor/composer/../laravel/framework/src/Illuminate/Contracts/Routing/ResponseFactory.php',
      ),
    ),
    'namespace' => 'Illuminate\\Contracts\\Routing',
    'name' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
    'shortName' => 'ResponseFactory',
    'isInterface' => true,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 8,
    'endLine' => 179,
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
      'make' => 
      array (
        'name' => 'make',
        'parameters' => 
        array (
          'content' => 
          array (
            'name' => 'content',
            'default' => 
            array (
              'code' => '\'\'',
              'attributes' => 
              array (
                'startLine' => 18,
                'endLine' => 18,
                'startTokenPos' => 35,
                'startFilePos' => 361,
                'endTokenPos' => 35,
                'endFilePos' => 362,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 18,
            'endLine' => 18,
            'startColumn' => 26,
            'endColumn' => 38,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'status' => 
          array (
            'name' => 'status',
            'default' => 
            array (
              'code' => '200',
              'attributes' => 
              array (
                'startLine' => 18,
                'endLine' => 18,
                'startTokenPos' => 42,
                'startFilePos' => 375,
                'endTokenPos' => 42,
                'endFilePos' => 377,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 18,
            'endLine' => 18,
            'startColumn' => 41,
            'endColumn' => 53,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'headers' => 
          array (
            'name' => 'headers',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 18,
                'endLine' => 18,
                'startTokenPos' => 51,
                'startFilePos' => 397,
                'endTokenPos' => 52,
                'endFilePos' => 398,
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
            'startLine' => 18,
            'endLine' => 18,
            'startColumn' => 56,
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
 * Create a new response instance.
 *
 * @param  array|string  $content
 * @param  int  $status
 * @param  array  $headers
 * @return \\Illuminate\\Http\\Response
 */',
        'startLine' => 18,
        'endLine' => 18,
        'startColumn' => 5,
        'endColumn' => 76,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\Routing',
        'declaringClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'implementingClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'currentClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'aliasName' => NULL,
      ),
      'noContent' => 
      array (
        'name' => 'noContent',
        'parameters' => 
        array (
          'status' => 
          array (
            'name' => 'status',
            'default' => 
            array (
              'code' => '204',
              'attributes' => 
              array (
                'startLine' => 27,
                'endLine' => 27,
                'startTokenPos' => 68,
                'startFilePos' => 609,
                'endTokenPos' => 68,
                'endFilePos' => 611,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 27,
            'endLine' => 27,
            'startColumn' => 31,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'headers' => 
          array (
            'name' => 'headers',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 27,
                'endLine' => 27,
                'startTokenPos' => 77,
                'startFilePos' => 631,
                'endTokenPos' => 78,
                'endFilePos' => 632,
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
            'startLine' => 27,
            'endLine' => 27,
            'startColumn' => 46,
            'endColumn' => 64,
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
 * Create a new "no content" response.
 *
 * @param  int  $status
 * @param  array  $headers
 * @return \\Illuminate\\Http\\Response
 */',
        'startLine' => 27,
        'endLine' => 27,
        'startColumn' => 5,
        'endColumn' => 66,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\Routing',
        'declaringClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'implementingClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'currentClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'aliasName' => NULL,
      ),
      'view' => 
      array (
        'name' => 'view',
        'parameters' => 
        array (
          'view' => 
          array (
            'name' => 'view',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 38,
            'endLine' => 38,
            'startColumn' => 26,
            'endColumn' => 30,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'data' => 
          array (
            'name' => 'data',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 38,
                'endLine' => 38,
                'startTokenPos' => 97,
                'startFilePos' => 910,
                'endTokenPos' => 98,
                'endFilePos' => 911,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 38,
            'endLine' => 38,
            'startColumn' => 33,
            'endColumn' => 42,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'status' => 
          array (
            'name' => 'status',
            'default' => 
            array (
              'code' => '200',
              'attributes' => 
              array (
                'startLine' => 38,
                'endLine' => 38,
                'startTokenPos' => 105,
                'startFilePos' => 924,
                'endTokenPos' => 105,
                'endFilePos' => 926,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 38,
            'endLine' => 38,
            'startColumn' => 45,
            'endColumn' => 57,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'headers' => 
          array (
            'name' => 'headers',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 38,
                'endLine' => 38,
                'startTokenPos' => 114,
                'startFilePos' => 946,
                'endTokenPos' => 115,
                'endFilePos' => 947,
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
            'startLine' => 38,
            'endLine' => 38,
            'startColumn' => 60,
            'endColumn' => 78,
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
 * Create a new response for a given view.
 *
 * @param  string|array  $view
 * @param  array  $data
 * @param  int  $status
 * @param  array  $headers
 * @return \\Illuminate\\Http\\Response
 */',
        'startLine' => 38,
        'endLine' => 38,
        'startColumn' => 5,
        'endColumn' => 80,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\Routing',
        'declaringClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'implementingClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'currentClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'aliasName' => NULL,
      ),
      'json' => 
      array (
        'name' => 'json',
        'parameters' => 
        array (
          'data' => 
          array (
            'name' => 'data',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 49,
                'endLine' => 49,
                'startTokenPos' => 131,
                'startFilePos' => 1213,
                'endTokenPos' => 132,
                'endFilePos' => 1214,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 49,
            'endLine' => 49,
            'startColumn' => 26,
            'endColumn' => 35,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'status' => 
          array (
            'name' => 'status',
            'default' => 
            array (
              'code' => '200',
              'attributes' => 
              array (
                'startLine' => 49,
                'endLine' => 49,
                'startTokenPos' => 139,
                'startFilePos' => 1227,
                'endTokenPos' => 139,
                'endFilePos' => 1229,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 49,
            'endLine' => 49,
            'startColumn' => 38,
            'endColumn' => 50,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'headers' => 
          array (
            'name' => 'headers',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 49,
                'endLine' => 49,
                'startTokenPos' => 148,
                'startFilePos' => 1249,
                'endTokenPos' => 149,
                'endFilePos' => 1250,
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
            'startLine' => 49,
            'endLine' => 49,
            'startColumn' => 53,
            'endColumn' => 71,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'options' => 
          array (
            'name' => 'options',
            'default' => 
            array (
              'code' => '0',
              'attributes' => 
              array (
                'startLine' => 49,
                'endLine' => 49,
                'startTokenPos' => 156,
                'startFilePos' => 1264,
                'endTokenPos' => 156,
                'endFilePos' => 1264,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 49,
            'endLine' => 49,
            'startColumn' => 74,
            'endColumn' => 85,
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
 * Create a new JSON response instance.
 *
 * @param  mixed  $data
 * @param  int  $status
 * @param  array  $headers
 * @param  int  $options
 * @return \\Illuminate\\Http\\JsonResponse
 */',
        'startLine' => 49,
        'endLine' => 49,
        'startColumn' => 5,
        'endColumn' => 87,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\Routing',
        'declaringClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'implementingClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'currentClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'aliasName' => NULL,
      ),
      'jsonp' => 
      array (
        'name' => 'jsonp',
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
            'startLine' => 61,
            'endLine' => 61,
            'startColumn' => 27,
            'endColumn' => 35,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'data' => 
          array (
            'name' => 'data',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 61,
                'endLine' => 61,
                'startTokenPos' => 175,
                'startFilePos' => 1576,
                'endTokenPos' => 176,
                'endFilePos' => 1577,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 61,
            'endLine' => 61,
            'startColumn' => 38,
            'endColumn' => 47,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'status' => 
          array (
            'name' => 'status',
            'default' => 
            array (
              'code' => '200',
              'attributes' => 
              array (
                'startLine' => 61,
                'endLine' => 61,
                'startTokenPos' => 183,
                'startFilePos' => 1590,
                'endTokenPos' => 183,
                'endFilePos' => 1592,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 61,
            'endLine' => 61,
            'startColumn' => 50,
            'endColumn' => 62,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'headers' => 
          array (
            'name' => 'headers',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 61,
                'endLine' => 61,
                'startTokenPos' => 192,
                'startFilePos' => 1612,
                'endTokenPos' => 193,
                'endFilePos' => 1613,
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
            'startLine' => 61,
            'endLine' => 61,
            'startColumn' => 65,
            'endColumn' => 83,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
          'options' => 
          array (
            'name' => 'options',
            'default' => 
            array (
              'code' => '0',
              'attributes' => 
              array (
                'startLine' => 61,
                'endLine' => 61,
                'startTokenPos' => 200,
                'startFilePos' => 1627,
                'endTokenPos' => 200,
                'endFilePos' => 1627,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 61,
            'endLine' => 61,
            'startColumn' => 86,
            'endColumn' => 97,
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
 * Create a new JSONP response instance.
 *
 * @param  string  $callback
 * @param  mixed  $data
 * @param  int  $status
 * @param  array  $headers
 * @param  int  $options
 * @return \\Illuminate\\Http\\JsonResponse
 */',
        'startLine' => 61,
        'endLine' => 61,
        'startColumn' => 5,
        'endColumn' => 99,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\Routing',
        'declaringClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'implementingClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'currentClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'aliasName' => NULL,
      ),
      'eventStream' => 
      array (
        'name' => 'eventStream',
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
                'name' => 'Closure',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 71,
            'endLine' => 71,
            'startColumn' => 33,
            'endColumn' => 49,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'headers' => 
          array (
            'name' => 'headers',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 71,
                'endLine' => 71,
                'startTokenPos' => 223,
                'startFilePos' => 1972,
                'endTokenPos' => 224,
                'endFilePos' => 1973,
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
            'startLine' => 71,
            'endLine' => 71,
            'startColumn' => 52,
            'endColumn' => 70,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'endStreamWith' => 
          array (
            'name' => 'endStreamWith',
            'default' => 
            array (
              'code' => '\'</stream>\'',
              'attributes' => 
              array (
                'startLine' => 71,
                'endLine' => 71,
                'startTokenPos' => 237,
                'startFilePos' => 2019,
                'endTokenPos' => 237,
                'endFilePos' => 2029,
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
                      'name' => 'Illuminate\\Http\\StreamedEvent',
                      'isIdentifier' => false,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                  2 => 
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
            'startLine' => 71,
            'endLine' => 71,
            'startColumn' => 73,
            'endColumn' => 126,
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
 * Create a new event stream response.
 *
 * @param  \\Closure  $callback
 * @param  array  $headers
 * @param  \\Illuminate\\Http\\StreamedEvent|string|null  $endStreamWith
 * @return \\Symfony\\Component\\HttpFoundation\\StreamedResponse
 */',
        'startLine' => 71,
        'endLine' => 71,
        'startColumn' => 5,
        'endColumn' => 128,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\Routing',
        'declaringClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'implementingClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'currentClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'aliasName' => NULL,
      ),
      'stream' => 
      array (
        'name' => 'stream',
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
            'startLine' => 81,
            'endLine' => 81,
            'startColumn' => 28,
            'endColumn' => 36,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'status' => 
          array (
            'name' => 'status',
            'default' => 
            array (
              'code' => '200',
              'attributes' => 
              array (
                'startLine' => 81,
                'endLine' => 81,
                'startTokenPos' => 256,
                'startFilePos' => 2313,
                'endTokenPos' => 256,
                'endFilePos' => 2315,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 81,
            'endLine' => 81,
            'startColumn' => 39,
            'endColumn' => 51,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'headers' => 
          array (
            'name' => 'headers',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 81,
                'endLine' => 81,
                'startTokenPos' => 265,
                'startFilePos' => 2335,
                'endTokenPos' => 266,
                'endFilePos' => 2336,
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
            'startLine' => 81,
            'endLine' => 81,
            'startColumn' => 54,
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
 * Create a new streamed response instance.
 *
 * @param  callable  $callback
 * @param  int  $status
 * @param  array  $headers
 * @return \\Symfony\\Component\\HttpFoundation\\StreamedResponse
 */',
        'startLine' => 81,
        'endLine' => 81,
        'startColumn' => 5,
        'endColumn' => 74,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\Routing',
        'declaringClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'implementingClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'currentClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'aliasName' => NULL,
      ),
      'streamJson' => 
      array (
        'name' => 'streamJson',
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
            'startLine' => 92,
            'endLine' => 92,
            'startColumn' => 32,
            'endColumn' => 36,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'status' => 
          array (
            'name' => 'status',
            'default' => 
            array (
              'code' => '200',
              'attributes' => 
              array (
                'startLine' => 92,
                'endLine' => 92,
                'startTokenPos' => 285,
                'startFilePos' => 2659,
                'endTokenPos' => 285,
                'endFilePos' => 2661,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 92,
            'endLine' => 92,
            'startColumn' => 39,
            'endColumn' => 51,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'headers' => 
          array (
            'name' => 'headers',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 92,
                'endLine' => 92,
                'startTokenPos' => 292,
                'startFilePos' => 2675,
                'endTokenPos' => 293,
                'endFilePos' => 2676,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 92,
            'endLine' => 92,
            'startColumn' => 54,
            'endColumn' => 66,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'encodingOptions' => 
          array (
            'name' => 'encodingOptions',
            'default' => 
            array (
              'code' => '15',
              'attributes' => 
              array (
                'startLine' => 92,
                'endLine' => 92,
                'startTokenPos' => 300,
                'startFilePos' => 2698,
                'endTokenPos' => 300,
                'endFilePos' => 2699,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 92,
            'endLine' => 92,
            'startColumn' => 69,
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
 * Create a new streamed JSON response instance.
 *
 * @param  array  $data
 * @param  int  $status
 * @param  array  $headers
 * @param  int  $encodingOptions
 * @return \\Symfony\\Component\\HttpFoundation\\StreamedJsonResponse
 */',
        'startLine' => 92,
        'endLine' => 92,
        'startColumn' => 5,
        'endColumn' => 91,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\Routing',
        'declaringClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'implementingClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'currentClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'aliasName' => NULL,
      ),
      'streamDownload' => 
      array (
        'name' => 'streamDownload',
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
            'startLine' => 103,
            'endLine' => 103,
            'startColumn' => 36,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 103,
                'endLine' => 103,
                'startTokenPos' => 319,
                'startFilePos' => 3055,
                'endTokenPos' => 319,
                'endFilePos' => 3058,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 103,
            'endLine' => 103,
            'startColumn' => 47,
            'endColumn' => 58,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'headers' => 
          array (
            'name' => 'headers',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 103,
                'endLine' => 103,
                'startTokenPos' => 328,
                'startFilePos' => 3078,
                'endTokenPos' => 329,
                'endFilePos' => 3079,
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
            'startLine' => 103,
            'endLine' => 103,
            'startColumn' => 61,
            'endColumn' => 79,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'disposition' => 
          array (
            'name' => 'disposition',
            'default' => 
            array (
              'code' => '\'attachment\'',
              'attributes' => 
              array (
                'startLine' => 103,
                'endLine' => 103,
                'startTokenPos' => 336,
                'startFilePos' => 3097,
                'endTokenPos' => 336,
                'endFilePos' => 3108,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 103,
            'endLine' => 103,
            'startColumn' => 82,
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
 * Create a new streamed response instance as a file download.
 *
 * @param  callable  $callback
 * @param  string|null  $name
 * @param  array  $headers
 * @param  string|null  $disposition
 * @return \\Symfony\\Component\\HttpFoundation\\StreamedResponse
 */',
        'startLine' => 103,
        'endLine' => 103,
        'startColumn' => 5,
        'endColumn' => 110,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\Routing',
        'declaringClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'implementingClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'currentClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'aliasName' => NULL,
      ),
      'download' => 
      array (
        'name' => 'download',
        'parameters' => 
        array (
          'file' => 
          array (
            'name' => 'file',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 114,
            'endLine' => 114,
            'startColumn' => 30,
            'endColumn' => 34,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'name' => 
          array (
            'name' => 'name',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 114,
                'endLine' => 114,
                'startTokenPos' => 355,
                'startFilePos' => 3440,
                'endTokenPos' => 355,
                'endFilePos' => 3443,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 114,
            'endLine' => 114,
            'startColumn' => 37,
            'endColumn' => 48,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'headers' => 
          array (
            'name' => 'headers',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 114,
                'endLine' => 114,
                'startTokenPos' => 364,
                'startFilePos' => 3463,
                'endTokenPos' => 365,
                'endFilePos' => 3464,
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
            'startLine' => 114,
            'endLine' => 114,
            'startColumn' => 51,
            'endColumn' => 69,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'disposition' => 
          array (
            'name' => 'disposition',
            'default' => 
            array (
              'code' => '\'attachment\'',
              'attributes' => 
              array (
                'startLine' => 114,
                'endLine' => 114,
                'startTokenPos' => 372,
                'startFilePos' => 3482,
                'endTokenPos' => 372,
                'endFilePos' => 3493,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 114,
            'endLine' => 114,
            'startColumn' => 72,
            'endColumn' => 98,
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
 * Create a new file download response.
 *
 * @param  \\SplFileInfo|string  $file
 * @param  string|null  $name
 * @param  array  $headers
 * @param  string|null  $disposition
 * @return \\Symfony\\Component\\HttpFoundation\\BinaryFileResponse
 */',
        'startLine' => 114,
        'endLine' => 114,
        'startColumn' => 5,
        'endColumn' => 100,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\Routing',
        'declaringClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'implementingClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'currentClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'aliasName' => NULL,
      ),
      'file' => 
      array (
        'name' => 'file',
        'parameters' => 
        array (
          'file' => 
          array (
            'name' => 'file',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 123,
            'endLine' => 123,
            'startColumn' => 26,
            'endColumn' => 30,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'headers' => 
          array (
            'name' => 'headers',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 123,
                'endLine' => 123,
                'startTokenPos' => 393,
                'startFilePos' => 3760,
                'endTokenPos' => 394,
                'endFilePos' => 3761,
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
            'startLine' => 123,
            'endLine' => 123,
            'startColumn' => 33,
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
 * Return the raw contents of a binary file.
 *
 * @param  \\SplFileInfo|string  $file
 * @param  array  $headers
 * @return \\Symfony\\Component\\HttpFoundation\\BinaryFileResponse
 */',
        'startLine' => 123,
        'endLine' => 123,
        'startColumn' => 5,
        'endColumn' => 53,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\Routing',
        'declaringClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'implementingClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'currentClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'aliasName' => NULL,
      ),
      'redirectTo' => 
      array (
        'name' => 'redirectTo',
        'parameters' => 
        array (
          'path' => 
          array (
            'name' => 'path',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 134,
            'endLine' => 134,
            'startColumn' => 32,
            'endColumn' => 36,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'status' => 
          array (
            'name' => 'status',
            'default' => 
            array (
              'code' => '302',
              'attributes' => 
              array (
                'startLine' => 134,
                'endLine' => 134,
                'startTokenPos' => 413,
                'startFilePos' => 4065,
                'endTokenPos' => 413,
                'endFilePos' => 4067,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 134,
            'endLine' => 134,
            'startColumn' => 39,
            'endColumn' => 51,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'headers' => 
          array (
            'name' => 'headers',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 134,
                'endLine' => 134,
                'startTokenPos' => 420,
                'startFilePos' => 4081,
                'endTokenPos' => 421,
                'endFilePos' => 4082,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 134,
            'endLine' => 134,
            'startColumn' => 54,
            'endColumn' => 66,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'secure' => 
          array (
            'name' => 'secure',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 134,
                'endLine' => 134,
                'startTokenPos' => 428,
                'startFilePos' => 4095,
                'endTokenPos' => 428,
                'endFilePos' => 4098,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 134,
            'endLine' => 134,
            'startColumn' => 69,
            'endColumn' => 82,
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
 * Create a new redirect response to the given path.
 *
 * @param  string  $path
 * @param  int  $status
 * @param  array  $headers
 * @param  bool|null  $secure
 * @return \\Illuminate\\Http\\RedirectResponse
 */',
        'startLine' => 134,
        'endLine' => 134,
        'startColumn' => 5,
        'endColumn' => 84,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\Routing',
        'declaringClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'implementingClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'currentClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'aliasName' => NULL,
      ),
      'redirectToRoute' => 
      array (
        'name' => 'redirectToRoute',
        'parameters' => 
        array (
          'route' => 
          array (
            'name' => 'route',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 145,
            'endLine' => 145,
            'startColumn' => 37,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'parameters' => 
          array (
            'name' => 'parameters',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 145,
                'endLine' => 145,
                'startTokenPos' => 447,
                'startFilePos' => 4424,
                'endTokenPos' => 448,
                'endFilePos' => 4425,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 145,
            'endLine' => 145,
            'startColumn' => 45,
            'endColumn' => 60,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'status' => 
          array (
            'name' => 'status',
            'default' => 
            array (
              'code' => '302',
              'attributes' => 
              array (
                'startLine' => 145,
                'endLine' => 145,
                'startTokenPos' => 455,
                'startFilePos' => 4438,
                'endTokenPos' => 455,
                'endFilePos' => 4440,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 145,
            'endLine' => 145,
            'startColumn' => 63,
            'endColumn' => 75,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'headers' => 
          array (
            'name' => 'headers',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 145,
                'endLine' => 145,
                'startTokenPos' => 462,
                'startFilePos' => 4454,
                'endTokenPos' => 463,
                'endFilePos' => 4455,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 145,
            'endLine' => 145,
            'startColumn' => 78,
            'endColumn' => 90,
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
 * Create a new redirect response to a named route.
 *
 * @param  \\BackedEnum|string  $route
 * @param  mixed  $parameters
 * @param  int  $status
 * @param  array  $headers
 * @return \\Illuminate\\Http\\RedirectResponse
 */',
        'startLine' => 145,
        'endLine' => 145,
        'startColumn' => 5,
        'endColumn' => 92,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\Routing',
        'declaringClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'implementingClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'currentClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'aliasName' => NULL,
      ),
      'redirectToAction' => 
      array (
        'name' => 'redirectToAction',
        'parameters' => 
        array (
          'action' => 
          array (
            'name' => 'action',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 156,
            'endLine' => 156,
            'startColumn' => 38,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'parameters' => 
          array (
            'name' => 'parameters',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 156,
                'endLine' => 156,
                'startTokenPos' => 482,
                'startFilePos' => 4784,
                'endTokenPos' => 483,
                'endFilePos' => 4785,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 156,
            'endLine' => 156,
            'startColumn' => 47,
            'endColumn' => 62,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'status' => 
          array (
            'name' => 'status',
            'default' => 
            array (
              'code' => '302',
              'attributes' => 
              array (
                'startLine' => 156,
                'endLine' => 156,
                'startTokenPos' => 490,
                'startFilePos' => 4798,
                'endTokenPos' => 490,
                'endFilePos' => 4800,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 156,
            'endLine' => 156,
            'startColumn' => 65,
            'endColumn' => 77,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'headers' => 
          array (
            'name' => 'headers',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 156,
                'endLine' => 156,
                'startTokenPos' => 497,
                'startFilePos' => 4814,
                'endTokenPos' => 498,
                'endFilePos' => 4815,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 156,
            'endLine' => 156,
            'startColumn' => 80,
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
 * Create a new redirect response to a controller action.
 *
 * @param  array|string  $action
 * @param  mixed  $parameters
 * @param  int  $status
 * @param  array  $headers
 * @return \\Illuminate\\Http\\RedirectResponse
 */',
        'startLine' => 156,
        'endLine' => 156,
        'startColumn' => 5,
        'endColumn' => 94,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\Routing',
        'declaringClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'implementingClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'currentClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'aliasName' => NULL,
      ),
      'redirectGuest' => 
      array (
        'name' => 'redirectGuest',
        'parameters' => 
        array (
          'path' => 
          array (
            'name' => 'path',
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
            'startColumn' => 35,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'status' => 
          array (
            'name' => 'status',
            'default' => 
            array (
              'code' => '302',
              'attributes' => 
              array (
                'startLine' => 167,
                'endLine' => 167,
                'startTokenPos' => 517,
                'startFilePos' => 5150,
                'endTokenPos' => 517,
                'endFilePos' => 5152,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 167,
            'endLine' => 167,
            'startColumn' => 42,
            'endColumn' => 54,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'headers' => 
          array (
            'name' => 'headers',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 167,
                'endLine' => 167,
                'startTokenPos' => 524,
                'startFilePos' => 5166,
                'endTokenPos' => 525,
                'endFilePos' => 5167,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 167,
            'endLine' => 167,
            'startColumn' => 57,
            'endColumn' => 69,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'secure' => 
          array (
            'name' => 'secure',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 167,
                'endLine' => 167,
                'startTokenPos' => 532,
                'startFilePos' => 5180,
                'endTokenPos' => 532,
                'endFilePos' => 5183,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 167,
            'endLine' => 167,
            'startColumn' => 72,
            'endColumn' => 85,
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
 * Create a new redirect response, while putting the current URL in the session.
 *
 * @param  string  $path
 * @param  int  $status
 * @param  array  $headers
 * @param  bool|null  $secure
 * @return \\Illuminate\\Http\\RedirectResponse
 */',
        'startLine' => 167,
        'endLine' => 167,
        'startColumn' => 5,
        'endColumn' => 87,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\Routing',
        'declaringClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'implementingClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'currentClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'aliasName' => NULL,
      ),
      'redirectToIntended' => 
      array (
        'name' => 'redirectToIntended',
        'parameters' => 
        array (
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => '\'/\'',
              'attributes' => 
              array (
                'startLine' => 178,
                'endLine' => 178,
                'startTokenPos' => 548,
                'startFilePos' => 5510,
                'endTokenPos' => 548,
                'endFilePos' => 5512,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 178,
            'endLine' => 178,
            'startColumn' => 40,
            'endColumn' => 53,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'status' => 
          array (
            'name' => 'status',
            'default' => 
            array (
              'code' => '302',
              'attributes' => 
              array (
                'startLine' => 178,
                'endLine' => 178,
                'startTokenPos' => 555,
                'startFilePos' => 5525,
                'endTokenPos' => 555,
                'endFilePos' => 5527,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 178,
            'endLine' => 178,
            'startColumn' => 56,
            'endColumn' => 68,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'headers' => 
          array (
            'name' => 'headers',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 178,
                'endLine' => 178,
                'startTokenPos' => 562,
                'startFilePos' => 5541,
                'endTokenPos' => 563,
                'endFilePos' => 5542,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 178,
            'endLine' => 178,
            'startColumn' => 71,
            'endColumn' => 83,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'secure' => 
          array (
            'name' => 'secure',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 178,
                'endLine' => 178,
                'startTokenPos' => 570,
                'startFilePos' => 5555,
                'endTokenPos' => 570,
                'endFilePos' => 5558,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 178,
            'endLine' => 178,
            'startColumn' => 86,
            'endColumn' => 99,
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
 * Create a new redirect response to the previously intended location.
 *
 * @param  string  $default
 * @param  int  $status
 * @param  array  $headers
 * @param  bool|null  $secure
 * @return \\Illuminate\\Http\\RedirectResponse
 */',
        'startLine' => 178,
        'endLine' => 178,
        'startColumn' => 5,
        'endColumn' => 101,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\Routing',
        'declaringClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'implementingClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
        'currentClassName' => 'Illuminate\\Contracts\\Routing\\ResponseFactory',
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