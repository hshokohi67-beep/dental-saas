<?php declare(strict_types = 1);

// phpinternal-PHPStan\BetterReflection\Reflection\ReflectionClass-datetime
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v3-6.73.0.5-dev-master@e4f5f6c-8.4-',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\InternalLocatedSource',
      'data' => 
      array (
        'name' => 'datetime',
        'filename' => 'phpstorm-stubs:date/date_c.stub',
        'extensionName' => 'date',
        'aliasName' => NULL,
      ),
    ),
    'namespace' => NULL,
    'name' => 'DateTime',
    'shortName' => 'DateTime',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * Representation of date and time.
 * @link https://php.net/manual/en/class.datetime.php
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 8,
    'endLine' => 429,
    'startColumn' => 5,
    'endColumn' => 5,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
      0 => 'DateTimeInterface',
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
          'datetime' => 
          array (
            'name' => 'datetime',
            'default' => 
            array (
              'code' => '\'now\'',
              'attributes' => 
              array (
                'startLine' => 41,
                'endLine' => 41,
                'startTokenPos' => 62,
                'startFilePos' => 1725,
                'endTokenPos' => 62,
                'endFilePos' => 1729,
              ),
            ),
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
                      'startLine' => 40,
                      'endLine' => 40,
                      'startTokenPos' => 40,
                      'startFilePos' => 1659,
                      'endTokenPos' => 46,
                      'endFilePos' => 1677,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 40,
                      'endLine' => 40,
                      'startTokenPos' => 52,
                      'startFilePos' => 1689,
                      'endTokenPos' => 52,
                      'endFilePos' => 1690,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 40,
            'endLine' => 41,
            'startColumn' => 13,
            'endColumn' => 36,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'timezone' => 
          array (
            'name' => 'timezone',
            'default' => 
            array (
              'code' => '\\null',
              'attributes' => 
              array (
                'startLine' => 43,
                'endLine' => 43,
                'startTokenPos' => 92,
                'startFilePos' => 1898,
                'endTokenPos' => 92,
                'endFilePos' => 1901,
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
                      'name' => 'DateTimeZone',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'DateTimeZone|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 42,
                      'endLine' => 42,
                      'startTokenPos' => 68,
                      'startFilePos' => 1798,
                      'endTokenPos' => 74,
                      'endFilePos' => 1827,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'DateTimeZone\'',
                    'attributes' => 
                    array (
                      'startLine' => 42,
                      'endLine' => 42,
                      'startTokenPos' => 80,
                      'startFilePos' => 1839,
                      'endTokenPos' => 80,
                      'endFilePos' => 1852,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 42,
            'endLine' => 43,
            'startColumn' => 13,
            'endColumn' => 46,
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
            'name' => 'JetBrains\\PhpStorm\\Internal\\PhpStormStubsElementAvailable',
            'isRepeated' => false,
            'arguments' => 
            array (
              'from' => 
              array (
                'code' => '\'8.3\'',
                'attributes' => 
                array (
                  'startLine' => 38,
                  'endLine' => 38,
                  'startTokenPos' => 26,
                  'startFilePos' => 1548,
                  'endTokenPos' => 26,
                  'endFilePos' => 1552,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * (PHP 8 &gt;=8.3.0)<br/>
 * @link https://php.net/manual/en/datetime.construct.php
 * @param string $datetime [optional]
 * <p>A date/time string. Valid formats are explained in {@link https://php.net/manual/en/datetime.formats.php Date and Time Formats}.</p>
 * <p>
 * Enter <b>now</b> here to obtain the current time when using
 * the <em>$timezone</em> parameter.
 * </p>
 * @param null|DateTimeZone $timezone [optional] <p>
 * A {@link https://php.net/manual/en/class.datetimezone.php DateTimeZone} object representing the
 * timezone of <em>$datetime</em>.
 * </p>
 * <p>
 * If <em>$timezone</em> is omitted,
 * the current timezone will be used.
 * </p>
 * <blockquote><p><b>Note</b>:
 * </p><p>
 * The <em>$timezone</em> parameter
 * and the current timezone are ignored when the
 * <em>$time</em> parameter either
 * is a UNIX timestamp (e.g. <em>@946684800</em>)
 * or specifies a timezone
 * (e.g. <em>2010-01-28T15:00:00+02:00</em>).
 * </p> <p></p></blockquote>
 * @throws DateMalformedStringException Emits Exception in case of an error.
 */',
        'startLine' => 38,
        'endLine' => 46,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      '__wakeup' => 
      array (
        'name' => '__wakeup',
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
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
          1 => 
          array (
            'name' => 'Deprecated',
            'isRepeated' => false,
            'arguments' => 
            array (
              'since' => 
              array (
                'code' => '\'8.5\'',
                'attributes' => 
                array (
                  'startLine' => 54,
                  'endLine' => 54,
                  'startTokenPos' => 112,
                  'startFilePos' => 2238,
                  'endTokenPos' => 112,
                  'endFilePos' => 2242,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * The __wakeup handler
 * @return void Initializes a DateTime object.
 * @link https://php.net/manual/en/datetime.wakeup.php
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 53,
        'endLine' => 57,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'format' => 
      array (
        'name' => 'format',
        'parameters' => 
        array (
          'format' => 
          array (
            'name' => 'format',
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
                      'startLine' => 117,
                      'endLine' => 117,
                      'startTokenPos' => 154,
                      'startFilePos' => 7808,
                      'endTokenPos' => 160,
                      'endFilePos' => 7826,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 117,
                      'endLine' => 117,
                      'startTokenPos' => 166,
                      'startFilePos' => 7838,
                      'endTokenPos' => 166,
                      'endFilePos' => 7839,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 117,
            'endLine' => 118,
            'startColumn' => 13,
            'endColumn' => 26,
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
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Pure',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '\\true',
                'attributes' => 
                array (
                  'startLine' => 114,
                  'endLine' => 114,
                  'startTokenPos' => 136,
                  'startFilePos' => 7649,
                  'endTokenPos' => 136,
                  'endFilePos' => 7652,
                ),
              ),
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
 * Returns date formatted according to given format.
 * @param string $format The format of the outputted date string. See the formatting options
 * below. There are also several predefined date constants that may be used instead, so for
 * example DATE_RSS contains the format string \'D, d M Y H:i:s\'. The following characters are
 * recognized in the format parameter string format character Description Example returned
 * values Day --- --- d Day of the month, 2 digits with leading zeros 01 to 31 D A textual
 * representation of a day, three letters Mon through Sun j Day of the month without leading
 * zeros 1 to 31 l (lowercase \'L\') A full textual representation of the day of the week Sunday
 * through Saturday N ISO 8601 numeric representation of the day of the week 1 (for Monday)
 * through 7 (for Sunday) S English ordinal suffix for the day of the month, 2 characters st,
 * nd, rd or th. Works well with j w Numeric representation of the day of the week 0 (for
 * Sunday) through 6 (for Saturday) z The day of the year (starting from 0) 0 through 365 Week
 * --- --- W ISO 8601 week number of year, weeks starting on Monday Example: 42 (the 42nd week
 * in the year) Month --- --- F A full textual representation of a month, such as January or
 * March January through December m Numeric representation of a month, with leading zeros 01
 * through 12 M A short textual representation of a month, three letters Jan through Dec n
 * Numeric representation of a month, without leading zeros 1 through 12 t Number of days in the
 * given month 28 through 31 Year --- --- L Whether it\'s a leap year 1 if it is a leap year, 0
 * otherwise. o ISO 8601 week-numbering year. This has the same value as Y, except that if the
 * ISO week number (W) belongs to the previous or next year, that year is used instead.
 * Examples: 1999 or 2003 X An expanded full numeric representation of a year, at least 4
 * digits, with - for years BCE, and + for years CE. Examples: -0055, +0787, +1999, +10191 x An
 * expanded full numeric representation if required, or a standard full numeral representation
 * if possible (like Y). At least four digits. Years BCE are prefixed with a -. Years beyond
 * (and including) 10000 are prefixed by a +. Examples: -0055, 0787, 1999, +10191 Y A full
 * numeric representation of a year, at least 4 digits, with - for years BCE. Examples: -0055,
 * 0787, 1999, 2003, 10191 y A two digit representation of a year Examples: 99 or 03 Time ---
 * --- a Lowercase Ante meridiem and Post meridiem am or pm A Uppercase Ante meridiem and Post
 * meridiem AM or PM B Swatch Internet time 000 through 999 g 12-hour format of an hour without
 * leading zeros 1 through 12 G 24-hour format of an hour without leading zeros 0 through 23 h
 * 12-hour format of an hour with leading zeros 01 through 12 H 24-hour format of an hour with
 * leading zeros 00 through 23 i Minutes with leading zeros 00 to 59 s Seconds with leading
 * zeros 00 through 59 u Microseconds. Note that date will always generate 000000 since it takes
 * an int parameter, whereas DateTimeInterface::format does support microseconds if an object of
 * type DateTimeInterface was created with microseconds. Example: 654321 v Milliseconds. Same
 * note applies as for u. Example: 654 Timezone --- --- e Timezone identifier Examples: UTC,
 * GMT, Atlantic/Azores I (capital i) Whether or not the date is in daylight saving time 1 if
 * Daylight Saving Time, 0 otherwise. O Difference to Greenwich time (GMT) without colon between
 * hours and minutes Example: +0200 P Difference to Greenwich time (GMT) with colon between
 * hours and minutes Example: +02:00 p The same as P, but returns Z instead of +00:00 (available
 * as of PHP 8.0.0) Examples: Z or +02:00 T Timezone abbreviation, if known; otherwise the GMT
 * offset. Examples: EST, MDT, +05 Z Timezone offset in seconds. The offset for timezones west
 * of UTC is always negative, and for those east of UTC is always positive. -43200 through 50400
 * Full Date/Time --- --- c ISO 8601 date. Only compatible with the non-expanded format (up to
 * year 9999). Later dates will result in an invalid string. For later dates and expanded
 * format, see x and X. 2004-02-12T15:19:21+00:00 r RFC 2822/RFC 5322 formatted date Example:
 * Thu, 21 Dec 2000 16:01:07 +0200 U Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
 * See also time Unrecognized characters in the format string will be printed as-is. The Z
 * format will always return 0 when using gmdate. Since this function only accepts int
 * timestamps the u format character is only useful when using the date_format function with
 * user based timestamps created with date_create.
 * @return string Returns the formatted date string on success.
 * @link https://php.net/manual/en/datetime.format.php
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 114,
        'endLine' => 121,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'modify' => 
      array (
        'name' => 'modify',
        'parameters' => 
        array (
          'modifier' => 
          array (
            'name' => 'modifier',
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
                      'startLine' => 135,
                      'endLine' => 135,
                      'startTokenPos' => 228,
                      'startFilePos' => 8847,
                      'endTokenPos' => 234,
                      'endFilePos' => 8865,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 135,
                      'endLine' => 135,
                      'startTokenPos' => 240,
                      'startFilePos' => 8877,
                      'endTokenPos' => 240,
                      'endFilePos' => 8878,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 135,
            'endLine' => 136,
            'startColumn' => 13,
            'endColumn' => 28,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\PhpStormStubsElementAvailable',
            'isRepeated' => false,
            'arguments' => 
            array (
              'from' => 
              array (
                'code' => '\'8.3\'',
                'attributes' => 
                array (
                  'startLine' => 131,
                  'endLine' => 131,
                  'startTokenPos' => 191,
                  'startFilePos' => 8576,
                  'endTokenPos' => 191,
                  'endFilePos' => 8580,
                ),
              ),
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
                'code' => '[\'8.4\' => \'DateTime\']',
                'attributes' => 
                array (
                  'startLine' => 133,
                  'endLine' => 133,
                  'startTokenPos' => 202,
                  'startFilePos' => 8700,
                  'endTokenPos' => 208,
                  'endFilePos' => 8720,
                ),
              ),
              'default' => 
              array (
                'code' => '\'static|false\'',
                'attributes' => 
                array (
                  'startLine' => 133,
                  'endLine' => 133,
                  'startTokenPos' => 214,
                  'startFilePos' => 8732,
                  'endTokenPos' => 214,
                  'endFilePos' => 8745,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * Alter the timestamp of a DateTime object by incrementing or decrementing
 * in a format accepted by strtotime().
 * @param string $modifier A date/time string. Valid formats are explained in <a href="https://secure.php.net/manual/en/datetime.formats.php">Date and Time Formats</a>.
 * @return static|false Returns the DateTime object for method chaining or FALSE on failure.
 * @throws DateMalformedStringException
 * @link https://php.net/manual/en/datetime.modify.php
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 131,
        'endLine' => 139,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'add' => 
      array (
        'name' => 'add',
        'parameters' => 
        array (
          'interval' => 
          array (
            'name' => 'interval',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'DateInterval',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 148,
            'endLine' => 148,
            'startColumn' => 29,
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
            'name' => 'DateTime',
            'isIdentifier' => false,
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
 * Adds an amount of days, months, years, hours, minutes and seconds to a DateTime object
 * @param DateInterval $interval A DateInterval object
 * @return static Returns the modified DateTime object for method chaining.
 * @link https://php.net/manual/en/datetime.add.php
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 147,
        'endLine' => 150,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'createFromImmutable' => 
      array (
        'name' => 'createFromImmutable',
        'parameters' => 
        array (
          'object' => 
          array (
            'name' => 'object',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'DateTimeImmutable',
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
            'startColumn' => 52,
            'endColumn' => 77,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
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
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.2\' => \'static\']',
                'attributes' => 
                array (
                  'startLine' => 162,
                  'endLine' => 162,
                  'startTokenPos' => 287,
                  'startFilePos' => 10181,
                  'endTokenPos' => 293,
                  'endFilePos' => 10199,
                ),
              ),
              'default' => 
              array (
                'code' => '\'DateTime\'',
                'attributes' => 
                array (
                  'startLine' => 162,
                  'endLine' => 162,
                  'startTokenPos' => 299,
                  'startFilePos' => 10211,
                  'endTokenPos' => 299,
                  'endFilePos' => 10220,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * Returns new DateTime instance encapsulating the given DateTimeImmutable object
 * @link https://php.net/manual/en/datetime.createfromimmutable.php
 * @param DateTimeImmutable $object The immutable DateTimeImmutable object that needs to be
 * converted to a mutable version. This object is not modified, but instead a new DateTime
 * instance is created containing the same date, time, and timezone information.
 * @return DateTime Returns a new DateTime instance.
 * @since 7.3
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 161,
        'endLine' => 165,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'sub' => 
      array (
        'name' => 'sub',
        'parameters' => 
        array (
          'interval' => 
          array (
            'name' => 'interval',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'DateInterval',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 175,
            'endLine' => 175,
            'startColumn' => 29,
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
            'name' => 'DateTime',
            'isIdentifier' => false,
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
 * Subtracts an amount of days, months, years, hours, minutes and seconds from a DateTime object
 * @param DateInterval $interval A DateInterval object
 * @return static Returns the modified DateTime object for method chaining.
 * @link https://php.net/manual/en/datetime.sub.php
 * @throws DateInvalidOperationException
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 174,
        'endLine' => 177,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'getTimezone' => 
      array (
        'name' => 'getTimezone',
        'parameters' => 
        array (
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
                  'name' => 'DateTimeZone',
                  'isIdentifier' => false,
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
 * Get the TimeZone associated with the DateTime
 * @return DateTimeZone|false Returns a DateTimeZone object on success or false on failure.
 * @link https://php.net/manual/en/datetime.gettimezone.php
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 184,
        'endLine' => 187,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'setTimezone' => 
      array (
        'name' => 'setTimezone',
        'parameters' => 
        array (
          'timezone' => 
          array (
            'name' => 'timezone',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'DateTimeZone',
                'isIdentifier' => false,
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
                    'code' => '[\'8.0\' => \'DateTimeZone\']',
                    'attributes' => 
                    array (
                      'startLine' => 198,
                      'endLine' => 198,
                      'startTokenPos' => 383,
                      'startFilePos' => 11933,
                      'endTokenPos' => 389,
                      'endFilePos' => 11957,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 198,
                      'endLine' => 198,
                      'startTokenPos' => 395,
                      'startFilePos' => 11969,
                      'endTokenPos' => 395,
                      'endFilePos' => 11970,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 198,
            'endLine' => 199,
            'startColumn' => 13,
            'endColumn' => 34,
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
            'name' => 'DateTime',
            'isIdentifier' => false,
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
 * Set the TimeZone associated with the DateTime
 * @param DateTimeZone $timezone A DateTimeZone object representing the desired time zone.
 * @return static Returns the DateTime object for method chaining. The underlying point-in-time
 * is not changed when calling this method.
 * @link https://php.net/manual/en/datetime.settimezone.php
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 196,
        'endLine' => 202,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'getOffset' => 
      array (
        'name' => 'getOffset',
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
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Returns the timezone offset
 * @return int Returns the timezone offset in seconds from UTC on success.
 * @link https://php.net/manual/en/datetime.getoffset.php
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 209,
        'endLine' => 212,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'setTime' => 
      array (
        'name' => 'setTime',
        'parameters' => 
        array (
          'hour' => 
          array (
            'name' => 'hour',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
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
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 225,
                      'endLine' => 225,
                      'startTokenPos' => 449,
                      'startFilePos' => 13072,
                      'endTokenPos' => 455,
                      'endFilePos' => 13087,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 225,
                      'endLine' => 225,
                      'startTokenPos' => 461,
                      'startFilePos' => 13099,
                      'endTokenPos' => 461,
                      'endFilePos' => 13100,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 225,
            'endLine' => 226,
            'startColumn' => 13,
            'endColumn' => 21,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'minute' => 
          array (
            'name' => 'minute',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
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
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 227,
                      'endLine' => 227,
                      'startTokenPos' => 473,
                      'startFilePos' => 13193,
                      'endTokenPos' => 479,
                      'endFilePos' => 13208,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 227,
                      'endLine' => 227,
                      'startTokenPos' => 485,
                      'startFilePos' => 13220,
                      'endTokenPos' => 485,
                      'endFilePos' => 13221,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 227,
            'endLine' => 228,
            'startColumn' => 13,
            'endColumn' => 23,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'second' => 
          array (
            'name' => 'second',
            'default' => 
            array (
              'code' => '0',
              'attributes' => 
              array (
                'startLine' => 230,
                'endLine' => 230,
                'startTokenPos' => 519,
                'startFilePos' => 13374,
                'endTokenPos' => 519,
                'endFilePos' => 13374,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
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
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 229,
                      'endLine' => 229,
                      'startTokenPos' => 497,
                      'startFilePos' => 13316,
                      'endTokenPos' => 503,
                      'endFilePos' => 13331,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 229,
                      'endLine' => 229,
                      'startTokenPos' => 509,
                      'startFilePos' => 13343,
                      'endTokenPos' => 509,
                      'endFilePos' => 13344,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 229,
            'endLine' => 230,
            'startColumn' => 13,
            'endColumn' => 27,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'microsecond' => 
          array (
            'name' => 'microsecond',
            'default' => 
            array (
              'code' => '0',
              'attributes' => 
              array (
                'startLine' => 233,
                'endLine' => 233,
                'startTokenPos' => 557,
                'startFilePos' => 13593,
                'endTokenPos' => 557,
                'endFilePos' => 13593,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
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
                'name' => 'JetBrains\\PhpStorm\\Internal\\PhpStormStubsElementAvailable',
                'isRepeated' => false,
                'arguments' => 
                array (
                  'from' => 
                  array (
                    'code' => '\'7.1\'',
                    'attributes' => 
                    array (
                      'startLine' => 231,
                      'endLine' => 231,
                      'startTokenPos' => 528,
                      'startFilePos' => 13456,
                      'endTokenPos' => 528,
                      'endFilePos' => 13460,
                    ),
                  ),
                ),
              ),
              1 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 232,
                      'endLine' => 232,
                      'startTokenPos' => 535,
                      'startFilePos' => 13530,
                      'endTokenPos' => 541,
                      'endFilePos' => 13545,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 232,
                      'endLine' => 232,
                      'startTokenPos' => 547,
                      'startFilePos' => 13557,
                      'endTokenPos' => 547,
                      'endFilePos' => 13558,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 231,
            'endLine' => 233,
            'startColumn' => 13,
            'endColumn' => 32,
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
            'name' => 'DateTime',
            'isIdentifier' => false,
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
 * Sets the current time of the DateTime object to a different time.
 * @param int $hour Hour of the time.
 * @param int $minute Minute of the time.
 * @param int $second Second of the time.
 * @param int $microsecond Added since 7.1
 * @return static Returns the modified DateTime object for method chaining.
 * @link https://php.net/manual/en/datetime.settime.php
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 223,
        'endLine' => 236,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'setDate' => 
      array (
        'name' => 'setDate',
        'parameters' => 
        array (
          'year' => 
          array (
            'name' => 'year',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
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
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 248,
                      'endLine' => 248,
                      'startTokenPos' => 584,
                      'startFilePos' => 14224,
                      'endTokenPos' => 590,
                      'endFilePos' => 14239,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 248,
                      'endLine' => 248,
                      'startTokenPos' => 596,
                      'startFilePos' => 14251,
                      'endTokenPos' => 596,
                      'endFilePos' => 14252,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 248,
            'endLine' => 249,
            'startColumn' => 13,
            'endColumn' => 21,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'month' => 
          array (
            'name' => 'month',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
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
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 250,
                      'endLine' => 250,
                      'startTokenPos' => 608,
                      'startFilePos' => 14345,
                      'endTokenPos' => 614,
                      'endFilePos' => 14360,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 250,
                      'endLine' => 250,
                      'startTokenPos' => 620,
                      'startFilePos' => 14372,
                      'endTokenPos' => 620,
                      'endFilePos' => 14373,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 250,
            'endLine' => 251,
            'startColumn' => 13,
            'endColumn' => 22,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'day' => 
          array (
            'name' => 'day',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
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
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 252,
                      'endLine' => 252,
                      'startTokenPos' => 632,
                      'startFilePos' => 14467,
                      'endTokenPos' => 638,
                      'endFilePos' => 14482,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 252,
                      'endLine' => 252,
                      'startTokenPos' => 644,
                      'startFilePos' => 14494,
                      'endTokenPos' => 644,
                      'endFilePos' => 14495,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 252,
            'endLine' => 253,
            'startColumn' => 13,
            'endColumn' => 20,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'DateTime',
            'isIdentifier' => false,
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
 * Sets the current date of the DateTime object to a different date.
 * @param int $year Year of the date.
 * @param int $month Month of the date.
 * @param int $day Day of the date.
 * @return static Returns the modified DateTime object for method chaining.
 * @link https://php.net/manual/en/datetime.setdate.php
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 246,
        'endLine' => 256,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'setISODate' => 
      array (
        'name' => 'setISODate',
        'parameters' => 
        array (
          'year' => 
          array (
            'name' => 'year',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
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
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 268,
                      'endLine' => 268,
                      'startTokenPos' => 677,
                      'startFilePos' => 15219,
                      'endTokenPos' => 683,
                      'endFilePos' => 15234,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 268,
                      'endLine' => 268,
                      'startTokenPos' => 689,
                      'startFilePos' => 15246,
                      'endTokenPos' => 689,
                      'endFilePos' => 15247,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 268,
            'endLine' => 269,
            'startColumn' => 13,
            'endColumn' => 21,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'week' => 
          array (
            'name' => 'week',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
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
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 270,
                      'endLine' => 270,
                      'startTokenPos' => 701,
                      'startFilePos' => 15340,
                      'endTokenPos' => 707,
                      'endFilePos' => 15355,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 270,
                      'endLine' => 270,
                      'startTokenPos' => 713,
                      'startFilePos' => 15367,
                      'endTokenPos' => 713,
                      'endFilePos' => 15368,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 270,
            'endLine' => 271,
            'startColumn' => 13,
            'endColumn' => 21,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'dayOfWeek' => 
          array (
            'name' => 'dayOfWeek',
            'default' => 
            array (
              'code' => '1',
              'attributes' => 
              array (
                'startLine' => 273,
                'endLine' => 273,
                'startTokenPos' => 747,
                'startFilePos' => 15522,
                'endTokenPos' => 747,
                'endFilePos' => 15522,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
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
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 272,
                      'endLine' => 272,
                      'startTokenPos' => 725,
                      'startFilePos' => 15461,
                      'endTokenPos' => 731,
                      'endFilePos' => 15476,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 272,
                      'endLine' => 272,
                      'startTokenPos' => 737,
                      'startFilePos' => 15488,
                      'endTokenPos' => 737,
                      'endFilePos' => 15489,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 272,
            'endLine' => 273,
            'startColumn' => 13,
            'endColumn' => 30,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'DateTime',
            'isIdentifier' => false,
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
 * Set a date according to the ISO 8601 standard - using weeks and day offsets rather than specific dates.
 * @param int $year Year of the date.
 * @param int $week Week of the date.
 * @param int $dayOfWeek Offset from the first day of the week.
 * @return static Returns the modified DateTime object for method chaining.
 * @link https://php.net/manual/en/datetime.setisodate.php
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 266,
        'endLine' => 276,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'setTimestamp' => 
      array (
        'name' => 'setTimestamp',
        'parameters' => 
        array (
          'timestamp' => 
          array (
            'name' => 'timestamp',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
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
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 287,
                      'endLine' => 287,
                      'startTokenPos' => 774,
                      'startFilePos' => 16205,
                      'endTokenPos' => 780,
                      'endFilePos' => 16220,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 287,
                      'endLine' => 287,
                      'startTokenPos' => 786,
                      'startFilePos' => 16232,
                      'endTokenPos' => 786,
                      'endFilePos' => 16233,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 287,
            'endLine' => 288,
            'startColumn' => 13,
            'endColumn' => 26,
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
            'name' => 'DateTime',
            'isIdentifier' => false,
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
 * Sets the date and time based on a Unix timestamp.
 * @param int $timestamp Unix timestamp representing the date. Setting timestamps outside the
 * range of integer is possible by using DateTimeImmutable::modify with the @ format.
 * @return static Returns the modified DateTime object for method chaining.
 * @link https://php.net/manual/en/datetime.settimestamp.php
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 285,
        'endLine' => 291,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'getTimestamp' => 
      array (
        'name' => 'getTimestamp',
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
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Gets the Unix timestamp.
 * @return int Returns the Unix timestamp representing the date.
 * @link https://php.net/manual/en/datetime.gettimestamp.php
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 298,
        'endLine' => 301,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'diff' => 
      array (
        'name' => 'diff',
        'parameters' => 
        array (
          'targetObject' => 
          array (
            'name' => 'targetObject',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'DateTimeInterface',
                'isIdentifier' => false,
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
                    'code' => '[\'8.0\' => \'DateTimeInterface\']',
                    'attributes' => 
                    array (
                      'startLine' => 312,
                      'endLine' => 312,
                      'startTokenPos' => 840,
                      'startFilePos' => 17314,
                      'endTokenPos' => 846,
                      'endFilePos' => 17343,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 312,
                      'endLine' => 312,
                      'startTokenPos' => 852,
                      'startFilePos' => 17355,
                      'endTokenPos' => 852,
                      'endFilePos' => 17356,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 312,
            'endLine' => 313,
            'startColumn' => 13,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'absolute' => 
          array (
            'name' => 'absolute',
            'default' => 
            array (
              'code' => '\\false',
              'attributes' => 
              array (
                'startLine' => 315,
                'endLine' => 315,
                'startTokenPos' => 886,
                'startFilePos' => 17533,
                'endTokenPos' => 886,
                'endFilePos' => 17537,
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
                      'startLine' => 314,
                      'endLine' => 314,
                      'startTokenPos' => 864,
                      'startFilePos' => 17471,
                      'endTokenPos' => 870,
                      'endFilePos' => 17487,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 314,
                      'endLine' => 314,
                      'startTokenPos' => 876,
                      'startFilePos' => 17499,
                      'endTokenPos' => 876,
                      'endFilePos' => 17500,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 314,
            'endLine' => 315,
            'startColumn' => 13,
            'endColumn' => 34,
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
            'name' => 'DateInterval',
            'isIdentifier' => false,
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
 * Returns the difference between two DateTime objects represented as a DateInterval.
 * @param DateTimeInterface $targetObject The date to compare to.
 * @param bool $absolute [optional] Whether to return absolute difference.
 * @return DateInterval The DateInterval object representing the difference between the two dates.
 * @link https://php.net/manual/en/datetime.diff.php
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 310,
        'endLine' => 318,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'createFromFormat' => 
      array (
        'name' => 'createFromFormat',
        'parameters' => 
        array (
          'format' => 
          array (
            'name' => 'format',
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
                      'startLine' => 332,
                      'endLine' => 332,
                      'startTokenPos' => 925,
                      'startFilePos' => 18453,
                      'endTokenPos' => 931,
                      'endFilePos' => 18471,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 332,
                      'endLine' => 332,
                      'startTokenPos' => 937,
                      'startFilePos' => 18483,
                      'endTokenPos' => 937,
                      'endFilePos' => 18484,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 332,
            'endLine' => 333,
            'startColumn' => 13,
            'endColumn' => 26,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'datetime' => 
          array (
            'name' => 'datetime',
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
                      'startLine' => 334,
                      'endLine' => 334,
                      'startTokenPos' => 949,
                      'startFilePos' => 18582,
                      'endTokenPos' => 955,
                      'endFilePos' => 18600,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 334,
                      'endLine' => 334,
                      'startTokenPos' => 961,
                      'startFilePos' => 18612,
                      'endTokenPos' => 961,
                      'endFilePos' => 18613,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 334,
            'endLine' => 335,
            'startColumn' => 13,
            'endColumn' => 28,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'timezone' => 
          array (
            'name' => 'timezone',
            'default' => 
            array (
              'code' => '\\null',
              'attributes' => 
              array (
                'startLine' => 337,
                'endLine' => 337,
                'startTokenPos' => 997,
                'startFilePos' => 18813,
                'endTokenPos' => 997,
                'endFilePos' => 18816,
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
                      'name' => 'DateTimeZone',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'DateTimeZone|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 336,
                      'endLine' => 336,
                      'startTokenPos' => 973,
                      'startFilePos' => 18713,
                      'endTokenPos' => 979,
                      'endFilePos' => 18742,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'DateTimeZone\'',
                    'attributes' => 
                    array (
                      'startLine' => 336,
                      'endLine' => 336,
                      'startTokenPos' => 985,
                      'startFilePos' => 18754,
                      'endTokenPos' => 985,
                      'endFilePos' => 18767,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 336,
            'endLine' => 337,
            'startColumn' => 13,
            'endColumn' => 46,
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
                  'name' => 'DateTime',
                  'isIdentifier' => false,
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
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\PhpStormStubsElementAvailable',
            'isRepeated' => false,
            'arguments' => 
            array (
              'from' => 
              array (
                'code' => '\'8.0\'',
                'attributes' => 
                array (
                  'startLine' => 330,
                  'endLine' => 330,
                  'startTokenPos' => 909,
                  'startFilePos' => 18330,
                  'endTokenPos' => 909,
                  'endFilePos' => 18334,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * Parse a string into a new DateTime object according to the specified format
 * @param string $format Format accepted by date().
 * @param string $datetime String representing the time.
 * @param null|DateTimeZone $timezone A DateTimeZone object representing the desired time zone.
 * @return DateTime|false Returns a new DateTime instance or false on failure.
 * @link https://php.net/manual/en/datetime.createfromformat.php
 * @throws ValueError when the datetime contains NULL-bytes.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 329,
        'endLine' => 340,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'getLastErrors' => 
      array (
        'name' => 'getLastErrors',
        'parameters' => 
        array (
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
            'name' => 'JetBrains\\PhpStorm\\ArrayShape',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '["warning_count" => "int", "warnings" => "string[]", "error_count" => "int", "errors" => "string[]"]',
                'attributes' => 
                array (
                  'startLine' => 347,
                  'endLine' => 347,
                  'startTokenPos' => 1015,
                  'startFilePos' => 19169,
                  'endTokenPos' => 1042,
                  'endFilePos' => 19268,
                ),
              ),
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
 * Returns an array of warnings and errors found while parsing a date/time string
 * @return array|false
 * @link https://php.net/manual/en/datetime.getlasterrors.php
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 347,
        'endLine' => 351,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      '__set_state' => 
      array (
        'name' => '__set_state',
        'parameters' => 
        array (
          'array' => 
          array (
            'name' => 'array',
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
            'startLine' => 360,
            'endLine' => 360,
            'startColumn' => 44,
            'endColumn' => 55,
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
            'name' => 'static',
            'isIdentifier' => false,
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
 * The __set_state handler
 * @link https://php.net/manual/en/datetime.set-state.php
 * @param array $array <p>Initialization array.</p>
 * @return DateTime <p>Returns a new instance of a DateTime object.</p>
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 359,
        'endLine' => 362,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'createFromInterface' => 
      array (
        'name' => 'createFromInterface',
        'parameters' => 
        array (
          'object' => 
          array (
            'name' => 'object',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'DateTimeInterface',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 372,
            'endLine' => 372,
            'startColumn' => 52,
            'endColumn' => 77,
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
            'name' => 'DateTime',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Returns new DateTime object encapsulating the given DateTimeInterface object
 * @link https://php.net/manual/en/datetime.createfrominterface.php
 * @param DateTimeInterface $object The DateTimeInterface object that needs to be converted to a
 * mutable version. This object is not modified, but instead a new DateTime object is created
 * containing the same date, time, and timezone information.
 * @return static Returns a new DateTime instance.
 * @since 8.0
 */',
        'startLine' => 372,
        'endLine' => 374,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      '__serialize' => 
      array (
        'name' => '__serialize',
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
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\PhpStormStubsElementAvailable',
            'isRepeated' => false,
            'arguments' => 
            array (
              'from' => 
              array (
                'code' => '\'8.2\'',
                'attributes' => 
                array (
                  'startLine' => 383,
                  'endLine' => 383,
                  'startTokenPos' => 1125,
                  'startFilePos' => 20849,
                  'endTokenPos' => 1125,
                  'endFilePos' => 20853,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * Serialize a DateTime
 *
 * The __serialize() handler.
 *
 * @link https://php.net/manual/en/datetime.serialize.php
 * @return array The serialized representation of the DateTime object.
 */',
        'startLine' => 383,
        'endLine' => 386,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      '__unserialize' => 
      array (
        'name' => '__unserialize',
        'parameters' => 
        array (
          'data' => 
          array (
            'name' => 'data',
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
            'startLine' => 396,
            'endLine' => 396,
            'startColumn' => 39,
            'endColumn' => 49,
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
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\PhpStormStubsElementAvailable',
            'isRepeated' => false,
            'arguments' => 
            array (
              'from' => 
              array (
                'code' => '\'8.2\'',
                'attributes' => 
                array (
                  'startLine' => 395,
                  'endLine' => 395,
                  'startTokenPos' => 1152,
                  'startFilePos' => 21241,
                  'endTokenPos' => 1152,
                  'endFilePos' => 21245,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * Unserialize an Datetime
 *
 * The __unserialize() handler.
 *
 * @link https://php.net/manual/en/datetime.unserialize.php
 * @param array $data The serialized DateTime.
 */',
        'startLine' => 395,
        'endLine' => 398,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'createFromTimestamp' => 
      array (
        'name' => 'createFromTimestamp',
        'parameters' => 
        array (
          'timestamp' => 
          array (
            'name' => 'timestamp',
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
                      'name' => 'int',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'float',
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
            'startLine' => 408,
            'endLine' => 408,
            'startColumn' => 52,
            'endColumn' => 71,
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
            'name' => 'static',
            'isIdentifier' => false,
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
 * Creates an instance from a Unix timestamp
 * @link https://php.net/manual/en/datetime.createfromtimestamp.php
 * @since 8.4
 * @throws \\DateRangeError If the timestamp is outside the range [PHP_INT_MIN, PHP_INT_MAX], a
 * DateRangeError is thrown.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 407,
        'endLine' => 410,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'getMicrosecond' => 
      array (
        'name' => 'getMicrosecond',
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
        ),
        'docComment' => '/**
 * Gets the microsecond part of the Unix timestamp
 * @link https://php.net/manual/en/datetimeinterface.getmicrosecond.php
 * @since 8.4
 */',
        'startLine' => 416,
        'endLine' => 418,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
        'aliasName' => NULL,
      ),
      'setMicrosecond' => 
      array (
        'name' => 'setMicrosecond',
        'parameters' => 
        array (
          'microsecond' => 
          array (
            'name' => 'microsecond',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 426,
            'endLine' => 426,
            'startColumn' => 40,
            'endColumn' => 55,
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
            'name' => 'static',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Sets microsecond part of the time
 * @link https://php.net/manual/en/datetime.setmicrosecond.php
 * @since 8.4
 * @throws \\DateRangeError If the microsecond is outside the range [0, 999999], a DateRangeError
 * is thrown.
 */',
        'startLine' => 426,
        'endLine' => 428,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTime',
        'implementingClassName' => 'DateTime',
        'currentClassName' => 'DateTime',
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