<?php declare(strict_types = 1);

// odsl-C:/Projects/dental-final/backend/vendor/composer/../laravel/framework/src/Illuminate/Foundation/helpers.php-PHPStan\BetterReflection\Reflection\ReflectionFunction-abort_unless
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.73.0.5-8.4-84a94be4f68fd0f749a87f05a7cb1b78ddc07c7d2121b56c6f05ff6ef80d34c6',
   'data' => 
  array (
    'name' => 'abort_unless',
    'parameters' => 
    array (
      'boolean' => 
      array (
        'name' => 'boolean',
        'default' => NULL,
        'type' => NULL,
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 101,
        'endLine' => 101,
        'startColumn' => 27,
        'endColumn' => 34,
        'parameterIndex' => 0,
        'isOptional' => false,
      ),
      'code' => 
      array (
        'name' => 'code',
        'default' => NULL,
        'type' => NULL,
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 101,
        'endLine' => 101,
        'startColumn' => 37,
        'endColumn' => 41,
        'parameterIndex' => 1,
        'isOptional' => false,
      ),
      'message' => 
      array (
        'name' => 'message',
        'default' => 
        array (
          'code' => '\'\'',
          'attributes' => 
          array (
            'startLine' => 101,
            'endLine' => 101,
            'startTokenPos' => 468,
            'startFilePos' => 3921,
            'endTokenPos' => 468,
            'endFilePos' => 3922,
          ),
        ),
        'type' => NULL,
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 101,
        'endLine' => 101,
        'startColumn' => 44,
        'endColumn' => 56,
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
            'startLine' => 101,
            'endLine' => 101,
            'startTokenPos' => 477,
            'startFilePos' => 3942,
            'endTokenPos' => 478,
            'endFilePos' => 3943,
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
        'startLine' => 101,
        'endLine' => 101,
        'startColumn' => 59,
        'endColumn' => 77,
        'parameterIndex' => 3,
        'isOptional' => true,
      ),
    ),
    'returnsReference' => false,
    'returnType' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
      'data' => 
      array (
        'name' => 'void',
        'isIdentifier' => true,
      ),
    ),
    'attributes' => 
    array (
    ),
    'docComment' => '/**
 * Throw an HttpException with the given data unless the given condition is true.
 *
 * @param  bool  $boolean
 * @param  \\Symfony\\Component\\HttpFoundation\\Response|\\Illuminate\\Contracts\\Support\\Responsable|int  $code
 * @param  string  $message
 *
 * @throws \\Symfony\\Component\\HttpKernel\\Exception\\HttpException
 * @throws \\Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException
 */',
    'startLine' => 101,
    'endLine' => 106,
    'startColumn' => 5,
    'endColumn' => 5,
    'couldThrow' => false,
    'isClosure' => false,
    'isGenerator' => false,
    'isVariadic' => false,
    'isStatic' => false,
    'namespace' => NULL,
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'abort_unless',
        'filename' => 'C:/Projects/dental-final/backend/vendor/composer/../laravel/framework/src/Illuminate/Foundation/helpers.php',
      ),
    ),
  ),
));