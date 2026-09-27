<?php declare(strict_types = 1);

// odsl-C:/Projects/dental-final/backend/vendor/composer/../laravel/framework/src/Illuminate/Foundation/helpers.php-PHPStan\BetterReflection\Reflection\ReflectionFunction-abort
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.73.0.5-8.4-84a94be4f68fd0f749a87f05a7cb1b78ddc07c7d2121b56c6f05ff6ef80d34c6',
   'data' => 
  array (
    'name' => 'abort',
    'parameters' => 
    array (
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
        'startLine' => 59,
        'endLine' => 59,
        'startColumn' => 20,
        'endColumn' => 24,
        'parameterIndex' => 0,
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
            'startLine' => 59,
            'endLine' => 59,
            'startTokenPos' => 276,
            'startFilePos' => 2429,
            'endTokenPos' => 276,
            'endFilePos' => 2430,
          ),
        ),
        'type' => NULL,
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 59,
        'endLine' => 59,
        'startColumn' => 27,
        'endColumn' => 39,
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
            'startLine' => 59,
            'endLine' => 59,
            'startTokenPos' => 285,
            'startFilePos' => 2450,
            'endTokenPos' => 286,
            'endFilePos' => 2451,
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
        'startLine' => 59,
        'endLine' => 59,
        'startColumn' => 42,
        'endColumn' => 60,
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
 * Throw an HttpException with the given data.
 *
 * @param  \\Symfony\\Component\\HttpFoundation\\Response|\\Illuminate\\Contracts\\Support\\Responsable|int  $code
 * @param  string  $message
 * @return never
 *
 * @throws \\Symfony\\Component\\HttpKernel\\Exception\\HttpException
 * @throws \\Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException
 * @throws \\Illuminate\\Http\\Exceptions\\HttpResponseException
 */',
    'startLine' => 59,
    'endLine' => 68,
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
        'name' => 'abort',
        'filename' => 'C:/Projects/dental-final/backend/vendor/composer/../laravel/framework/src/Illuminate/Foundation/helpers.php',
      ),
    ),
  ),
));