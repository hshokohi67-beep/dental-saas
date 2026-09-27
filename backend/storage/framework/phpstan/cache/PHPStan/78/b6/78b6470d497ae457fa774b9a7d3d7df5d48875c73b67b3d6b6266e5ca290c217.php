<?php declare(strict_types = 1);

// phpinternal-PHPStan\BetterReflection\Reflection\ReflectionClass-generator
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v3-6.73.0.5-dev-master@e4f5f6c-8.4-',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\InternalLocatedSource',
      'data' => 
      array (
        'name' => 'Generator',
        'filename' => 'phpstorm-stubs:standard/_types.stub',
        'extensionName' => 'standard',
        'aliasName' => NULL,
      ),
    ),
    'namespace' => NULL,
    'name' => 'Generator',
    'shortName' => 'Generator',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 32,
    'docComment' => '/**
 * Generator objects are returned from generators, cannot be instantiated via new.
 * @link https://php.net/manual/en/class.generator.php
 * @link https://wiki.php.net/rfc/generators
 *
 * @template-covariant TKey
 * @template-covariant TYield
 * @template TSend
 * @template-covariant TReturn
 *
 * @template-implements Iterator<TKey, TYield>
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 17,
    'endLine' => 103,
    'startColumn' => 5,
    'endColumn' => 5,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
      0 => 'Iterator',
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
      'rewind' => 
      array (
        'name' => 'rewind',
        'parameters' => 
        array (
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
 * Throws an exception if the generator is currently after the first yield.
 * @link https://php.net/manual/en/generator.rewind.php
 * @return void No value is returned.
 */',
        'startLine' => 24,
        'endLine' => 26,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'Generator',
        'implementingClassName' => 'Generator',
        'currentClassName' => 'Generator',
        'aliasName' => NULL,
      ),
      'valid' => 
      array (
        'name' => 'valid',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Returns false if the generator has been closed, true otherwise.
 * @link https://php.net/manual/en/generator.valid.php
 * @return bool Returns false if the iterator has been closed. Otherwise returns true.
 */',
        'startLine' => 32,
        'endLine' => 34,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'Generator',
        'implementingClassName' => 'Generator',
        'currentClassName' => 'Generator',
        'aliasName' => NULL,
      ),
      'current' => 
      array (
        'name' => 'current',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'mixed',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Returns whatever was passed to yield or null if nothing was passed or the generator is already closed.
 * @link https://php.net/manual/en/generator.current.php
 * @return TYield|null Returns the yielded value.
 */',
        'startLine' => 40,
        'endLine' => 42,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'Generator',
        'implementingClassName' => 'Generator',
        'currentClassName' => 'Generator',
        'aliasName' => NULL,
      ),
      'key' => 
      array (
        'name' => 'key',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'mixed',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.0\' => \'mixed\']',
                'attributes' => 
                array (
                  'startLine' => 48,
                  'endLine' => 48,
                  'startTokenPos' => 81,
                  'startFilePos' => 1778,
                  'endTokenPos' => 87,
                  'endFilePos' => 1795,
                ),
              ),
              'default' => 
              array (
                'code' => '\'string|float|int|bool|null\'',
                'attributes' => 
                array (
                  'startLine' => 48,
                  'endLine' => 48,
                  'startTokenPos' => 93,
                  'startFilePos' => 1807,
                  'endTokenPos' => 93,
                  'endFilePos' => 1834,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * Returns the yielded key or, if none was specified, an auto-incrementing key or null if the generator is already closed.
 * @link https://php.net/manual/en/generator.key.php
 * @return TKey|null Returns the yielded key.
 */',
        'startLine' => 48,
        'endLine' => 51,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'Generator',
        'implementingClassName' => 'Generator',
        'currentClassName' => 'Generator',
        'aliasName' => NULL,
      ),
      'next' => 
      array (
        'name' => 'next',
        'parameters' => 
        array (
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
 * Resumes the generator (unless the generator is already closed).
 * @link https://php.net/manual/en/generator.next.php
 * @return void No value is returned.
 */',
        'startLine' => 57,
        'endLine' => 59,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'Generator',
        'implementingClassName' => 'Generator',
        'currentClassName' => 'Generator',
        'aliasName' => NULL,
      ),
      'send' => 
      array (
        'name' => 'send',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'mixed',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 67,
            'endLine' => 67,
            'startColumn' => 30,
            'endColumn' => 41,
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
            'name' => 'mixed',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Sets the return value of the yield expression and resumes the generator (unless the generator is already closed).
 * @link https://php.net/manual/en/generator.send.php
 * @param TSend $value Value to send into the generator. This value will be the return value
 * of the yield expression the generator is currently at.
 * @return TYield|null Returns the yielded value.
 */',
        'startLine' => 67,
        'endLine' => 69,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'Generator',
        'implementingClassName' => 'Generator',
        'currentClassName' => 'Generator',
        'aliasName' => NULL,
      ),
      'throw' => 
      array (
        'name' => 'throw',
        'parameters' => 
        array (
          'exception' => 
          array (
            'name' => 'exception',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Throwable',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 75,
            'endLine' => 75,
            'startColumn' => 31,
            'endColumn' => 51,
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
            'name' => 'mixed',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Throws an exception at the current suspension point in the generator.
 * @param Throwable $exception
 * @return TYield
 */',
        'startLine' => 75,
        'endLine' => 77,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'Generator',
        'implementingClassName' => 'Generator',
        'currentClassName' => 'Generator',
        'aliasName' => NULL,
      ),
      'getReturn' => 
      array (
        'name' => 'getReturn',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'mixed',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Returns whatever was passed to return or null if nothing.
 * Throws an exception if the generator is still valid.
 * @link https://wiki.php.net/rfc/generator-return-expressions
 * @return TReturn Returns the generator\'s return value once it has finished executing.
 * @since 7.0
 */',
        'startLine' => 85,
        'endLine' => 87,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'Generator',
        'implementingClassName' => 'Generator',
        'currentClassName' => 'Generator',
        'aliasName' => NULL,
      ),
      '__wakeup' => 
      array (
        'name' => '__wakeup',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Serialize callback
 * Throws an exception as generators can\'t be serialized.
 * @link https://php.net/manual/en/generator.wakeup.php
 * @return void No value is returned.
 */',
        'startLine' => 94,
        'endLine' => 96,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'Generator',
        'implementingClassName' => 'Generator',
        'currentClassName' => 'Generator',
        'aliasName' => NULL,
      ),
      '__debugInfo' => 
      array (
        'name' => '__debugInfo',
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
 * @since 8.4
 */',
        'startLine' => 100,
        'endLine' => 102,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'Generator',
        'implementingClassName' => 'Generator',
        'currentClassName' => 'Generator',
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