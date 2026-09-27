<?php declare(strict_types = 1);

// phpinternal-PHPStan\BetterReflection\Reflection\ReflectionClass-resourcebundle
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v3-6.73.0.5-dev-master@e4f5f6c-8.4-',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\InternalLocatedSource',
      'data' => 
      array (
        'name' => 'ResourceBundle',
        'filename' => 'phpstorm-stubs:intl/intl.stub',
        'extensionName' => 'intl',
        'aliasName' => NULL,
      ),
    ),
    'namespace' => NULL,
    'name' => 'ResourceBundle',
    'shortName' => 'ResourceBundle',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * Localized software products often require sets of data that are to be customized depending on
 * current locale, e.g.: messages, labels, formatting patterns. ICU resource mechanism allows to
 * define sets of resources that the application can load on locale basis, while accessing them in
 * unified locale-independent fashion.
 *
 * This class implements access to ICU resource data files. These files are binary data arrays which
 * ICU uses to store the localized data. ICU resource bundle can hold simple resources and complex
 * resources. Complex resources are containers which can be either integer-indexed or string-indexed
 * (just like PHP arrays). Simple resources can be of the following types: string, integer, binary
 * data field or integer array. ResourceBundle supports direct access to the data through array
 * access pattern and iteration via foreach, as well as access via class methods. The result will be
 * PHP value for simple resources and ResourceBundle object for complex ones. All resources are
 * read-only.
 *
 * @link https://php.net/manual/en/class.resourcebundle.php
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 21,
    'endLine' => 158,
    'startColumn' => 5,
    'endColumn' => 5,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
      0 => 'IteratorAggregate',
      1 => 'Countable',
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
      '__construct' => 
      array (
        'name' => '__construct',
        'parameters' => 
        array (
          'locale' => 
          array (
            'name' => 'locale',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 35,
                      'endLine' => 35,
                      'startTokenPos' => 37,
                      'startFilePos' => 1957,
                      'endTokenPos' => 43,
                      'endFilePos' => 1980,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 35,
                      'endLine' => 35,
                      'startTokenPos' => 49,
                      'startFilePos' => 1992,
                      'endTokenPos' => 49,
                      'endFilePos' => 1993,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 35,
            'endLine' => 36,
            'startColumn' => 13,
            'endColumn' => 31,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'bundle' => 
          array (
            'name' => 'bundle',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 37,
                      'endLine' => 37,
                      'startTokenPos' => 63,
                      'startFilePos' => 2096,
                      'endTokenPos' => 69,
                      'endFilePos' => 2119,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 37,
                      'endLine' => 37,
                      'startTokenPos' => 75,
                      'startFilePos' => 2131,
                      'endTokenPos' => 75,
                      'endFilePos' => 2132,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 37,
            'endLine' => 38,
            'startColumn' => 13,
            'endColumn' => 31,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'fallback' => 
          array (
            'name' => 'fallback',
            'default' => 
            array (
              'code' => '\\true',
              'attributes' => 
              array (
                'startLine' => 40,
                'endLine' => 40,
                'startTokenPos' => 111,
                'startFilePos' => 2297,
                'endTokenPos' => 111,
                'endFilePos' => 2300,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'bool',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
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
                    'code' => '[\'8.0\' => \'bool\']',
                    'attributes' => 
                    array (
                      'startLine' => 39,
                      'endLine' => 39,
                      'startTokenPos' => 89,
                      'startFilePos' => 2235,
                      'endTokenPos' => 95,
                      'endFilePos' => 2251,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 39,
                      'endLine' => 39,
                      'startTokenPos' => 101,
                      'startFilePos' => 2263,
                      'endTokenPos' => 101,
                      'endFilePos' => 2264,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 39,
            'endLine' => 40,
            'startColumn' => 13,
            'endColumn' => 33,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Pure',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Create a resource bundle
 *
 * Creates a resource bundle.
 *
 * @link https://www.php.net/manual/en/resourcebundle.create.php
 * @param string $locale <p>Locale for which the resources should be loaded (locale name, e.g. en_CA).</p>
 * @param string $bundle <p>The directory where the data is stored or the name of the .dat file.</p>
 * @param bool $fallback [optional] <p>Whether locale should match exactly or fallback to parent locale is allowed.</p>
 */',
        'startLine' => 33,
        'endLine' => 43,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'ResourceBundle',
        'implementingClassName' => 'ResourceBundle',
        'currentClassName' => 'ResourceBundle',
        'aliasName' => NULL,
      ),
      'create' => 
      array (
        'name' => 'create',
        'parameters' => 
        array (
          'locale' => 
          array (
            'name' => 'locale',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 62,
                      'endLine' => 62,
                      'startTokenPos' => 137,
                      'startFilePos' => 3242,
                      'endTokenPos' => 143,
                      'endFilePos' => 3265,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 62,
                      'endLine' => 62,
                      'startTokenPos' => 149,
                      'startFilePos' => 3277,
                      'endTokenPos' => 149,
                      'endFilePos' => 3278,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 62,
            'endLine' => 63,
            'startColumn' => 13,
            'endColumn' => 31,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'bundle' => 
          array (
            'name' => 'bundle',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 64,
                      'endLine' => 64,
                      'startTokenPos' => 163,
                      'startFilePos' => 3381,
                      'endTokenPos' => 169,
                      'endFilePos' => 3404,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 64,
                      'endLine' => 64,
                      'startTokenPos' => 175,
                      'startFilePos' => 3416,
                      'endTokenPos' => 175,
                      'endFilePos' => 3417,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 64,
            'endLine' => 65,
            'startColumn' => 13,
            'endColumn' => 31,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'fallback' => 
          array (
            'name' => 'fallback',
            'default' => 
            array (
              'code' => '\\true',
              'attributes' => 
              array (
                'startLine' => 67,
                'endLine' => 67,
                'startTokenPos' => 211,
                'startFilePos' => 3582,
                'endTokenPos' => 211,
                'endFilePos' => 3585,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'bool',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
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
                    'code' => '[\'8.0\' => \'bool\']',
                    'attributes' => 
                    array (
                      'startLine' => 66,
                      'endLine' => 66,
                      'startTokenPos' => 189,
                      'startFilePos' => 3520,
                      'endTokenPos' => 195,
                      'endFilePos' => 3536,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 66,
                      'endLine' => 66,
                      'startTokenPos' => 201,
                      'startFilePos' => 3548,
                      'endTokenPos' => 201,
                      'endFilePos' => 3549,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 66,
            'endLine' => 67,
            'startColumn' => 13,
            'endColumn' => 33,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
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
                  'name' => 'ResourceBundle',
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
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * (PHP &gt;= 5.3.2, PECL intl &gt;= 2.0.0)<br/>
 * Create a resource bundle
 * @link https://php.net/manual/en/resourcebundle.create.php
 * @param string $locale <p>
 * Locale for which the resources should be loaded (locale name, e.g. en_CA).
 * </p>
 * @param string $bundle <p>
 * The directory where the data is stored or the name of the .dat file.
 * </p>
 * @param bool $fallback [optional] <p>
 * Whether locale should match exactly or fallback to parent locale is allowed.
 * </p>
 * @return ResourceBundle|null <b>ResourceBundle</b> object or <b>null</b> on error.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 60,
        'endLine' => 70,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => NULL,
        'declaringClassName' => 'ResourceBundle',
        'implementingClassName' => 'ResourceBundle',
        'currentClassName' => 'ResourceBundle',
        'aliasName' => NULL,
      ),
      'get' => 
      array (
        'name' => 'get',
        'parameters' => 
        array (
          'index' => 
          array (
            'name' => 'index',
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
                      'name' => 'int',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.4\' => \'string|int\']',
                    'attributes' => 
                    array (
                      'startLine' => 89,
                      'endLine' => 89,
                      'startTokenPos' => 262,
                      'startFilePos' => 4695,
                      'endTokenPos' => 268,
                      'endFilePos' => 4717,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 89,
                      'endLine' => 89,
                      'startTokenPos' => 274,
                      'startFilePos' => 4729,
                      'endTokenPos' => 274,
                      'endFilePos' => 4730,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 89,
            'endLine' => 90,
            'startColumn' => 13,
            'endColumn' => 29,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'fallback' => 
          array (
            'name' => 'fallback',
            'default' => 
            array (
              'code' => '\\true',
              'attributes' => 
              array (
                'startLine' => 92,
                'endLine' => 92,
                'startTokenPos' => 310,
                'startFilePos' => 4893,
                'endTokenPos' => 310,
                'endFilePos' => 4896,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'bool',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
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
                    'code' => '[\'8.0\' => \'bool\']',
                    'attributes' => 
                    array (
                      'startLine' => 91,
                      'endLine' => 91,
                      'startTokenPos' => 288,
                      'startFilePos' => 4831,
                      'endTokenPos' => 294,
                      'endFilePos' => 4847,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 91,
                      'endLine' => 91,
                      'startTokenPos' => 300,
                      'startFilePos' => 4859,
                      'endTokenPos' => 300,
                      'endFilePos' => 4860,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 91,
            'endLine' => 92,
            'startColumn' => 13,
            'endColumn' => 33,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Pure',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
          2 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.4\' => \'ResourceBundle|array|string|int|null\']',
                'attributes' => 
                array (
                  'startLine' => 87,
                  'endLine' => 87,
                  'startTokenPos' => 236,
                  'startFilePos' => 4530,
                  'endTokenPos' => 242,
                  'endFilePos' => 4578,
                ),
              ),
              'default' => 
              array (
                'code' => '\'mixed\'',
                'attributes' => 
                array (
                  'startLine' => 87,
                  'endLine' => 87,
                  'startTokenPos' => 248,
                  'startFilePos' => 4590,
                  'endTokenPos' => 248,
                  'endFilePos' => 4596,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * (PHP &gt;= 5.3.2, PECL intl &gt;= 2.0.0)<br/>
 * Get data from the bundle
 * @link https://php.net/manual/en/resourcebundle.get.php
 * @param string|int $index <p>
 * Data index, must be string or integer.
 * </p>
 * @param bool $fallback Whether locale should match exactly or fallback to parent locale is
 * allowed.
 * @return mixed the data located at the index or <b>NULL</b> on error. Strings, integers and binary data strings
 * are returned as corresponding PHP types, integer array is returned as PHP array. Complex types are
 * returned as <b>ResourceBundle</b> object.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 85,
        'endLine' => 95,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'ResourceBundle',
        'implementingClassName' => 'ResourceBundle',
        'currentClassName' => 'ResourceBundle',
        'aliasName' => NULL,
      ),
      'count' => 
      array (
        'name' => 'count',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Pure',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * (PHP &gt;= 5.3.2, PECL intl &gt;= 2.0.0)<br/>
 * Get number of elements in the bundle
 * @link https://php.net/manual/en/resourcebundle.count.php
 * @return int<0,max> number of elements in the bundle.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 103,
        'endLine' => 107,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'ResourceBundle',
        'implementingClassName' => 'ResourceBundle',
        'currentClassName' => 'ResourceBundle',
        'aliasName' => NULL,
      ),
      'getLocales' => 
      array (
        'name' => 'getLocales',
        'parameters' => 
        array (
          'bundle' => 
          array (
            'name' => 'bundle',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 121,
                      'endLine' => 121,
                      'startTokenPos' => 361,
                      'startFilePos' => 6031,
                      'endTokenPos' => 367,
                      'endFilePos' => 6049,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 121,
                      'endLine' => 121,
                      'startTokenPos' => 373,
                      'startFilePos' => 6061,
                      'endTokenPos' => 373,
                      'endFilePos' => 6062,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 121,
            'endLine' => 122,
            'startColumn' => 13,
            'endColumn' => 26,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
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
                  'name' => 'false',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * (PHP &gt;= 5.3.2, PECL intl &gt;= 2.0.0)<br/>
 * Get supported locales
 * @link https://php.net/manual/en/resourcebundle.locales.php
 * @param string $bundle <p>
 * Path of ResourceBundle for which to get available locales, or
 * empty string for default locales list.
 * </p>
 * @return array|false the list of locales supported by the bundle.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 119,
        'endLine' => 125,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => NULL,
        'declaringClassName' => 'ResourceBundle',
        'implementingClassName' => 'ResourceBundle',
        'currentClassName' => 'ResourceBundle',
        'aliasName' => NULL,
      ),
      'getErrorCode' => 
      array (
        'name' => 'getErrorCode',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Pure',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * (PHP &gt;= 5.3.2, PECL intl &gt;= 2.0.0)<br/>
 * Get bundle\'s last error code.
 * @link https://php.net/manual/en/resourcebundle.geterrorcode.php
 * @return int error code from last bundle object call.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 133,
        'endLine' => 137,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'ResourceBundle',
        'implementingClassName' => 'ResourceBundle',
        'currentClassName' => 'ResourceBundle',
        'aliasName' => NULL,
      ),
      'getErrorMessage' => 
      array (
        'name' => 'getErrorMessage',
        'parameters' => 
        array (
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
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Pure',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * (PHP &gt;= 5.3.2, PECL intl &gt;= 2.0.0)<br/>
 * Get bundle\'s last error message.
 * @link https://php.net/manual/en/resourcebundle.geterrormessage.php
 * @return string error message from last bundle object\'s call.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 145,
        'endLine' => 149,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'ResourceBundle',
        'implementingClassName' => 'ResourceBundle',
        'currentClassName' => 'ResourceBundle',
        'aliasName' => NULL,
      ),
      'getIterator' => 
      array (
        'name' => 'getIterator',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Iterator',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Pure',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * @return Iterator
 * @since 8.0
 */',
        'startLine' => 154,
        'endLine' => 157,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'ResourceBundle',
        'implementingClassName' => 'ResourceBundle',
        'currentClassName' => 'ResourceBundle',
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