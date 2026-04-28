<?php

namespace alcamo\time;

use alcamo\exception\Unsupported;

/**
 * @brief Posix date/time format
 *
 * Provides an equivalent PHP format, if possible, as well as a human-readable
 * representation and the length of the result, if fixed.
 *
 * @date Last reviewed 2026-02-25
 */
class PosixFormat
{
    /**
     * @brief Map of Posix format specifiers to PHP format specifiers and text
     * of appropriate length, if fixed
     */
    public const POSIX_FORMAT_SPECS_MAP = [
        /* year */
        '%G' => [ 'o', 'GGGG'  ],
        '%Y' => [ 'Y', 'YYYY'  ],
        '%y' => [ 'y', 'yy'    ],

        /* month */
        '%B' => [ 'F', '*'     ],
        '%b' => [ 'M', 'bbb'   ],
        '%h' => [ 'M', 'hhh'   ],
        '%m' => [ 'm', 'mm'    ],

        /* week */
        '%V' => [ 'W', 'VV'    ],

        /* day */
        '%A' => [ 'l', '*'     ],
        '%a' => [ 'D', 'aaa'   ],
        '%d' => [ 'd', 'dd'    ],
        '%u' => [ 'N', 'u'     ],
        '%w' => [ 'w', 'w'     ],

        /* hour */
        '%H' => [ 'H', 'HH'    ],
        '%I' => [ 'h', 'II'    ],
        '%p' => [ 'A', 'pp'    ],
        '%P' => [ 'a', 'PP'    ],

        /* minute */
        '%M' => [ 'i', 'MM'    ],

        /* second */
        '%S' => [ 's', 'SS'    ],
        '%s' => [ 'U', '*'     ],

        /* timezone */
        '%z' => [ 'O', 'zzzzz' ],
        '%Z' => [ 'T', '*'     ],

        /* composite */
        '%D' => [ 'm/d/y',   'mm/dd/yy'    ],
        '%F' => [ 'Y-m-d',   'YYYY-MM-DD'  ],
        '%r' => [ 'h:i:s A', 'II:MM:SS pp' ],
        '%R' => [ 'H:i',     'HH:MM'       ],
        '%T' => [ 'H:i:s',   'HH:MM:SS'    ],

        /* characters */
        '%n'  => [ "\n", 'n' ],
        '%t'  => [ "\t", 't' ]
    ];

    private static $posixSpecToPhpSpec_;
    private static $posixSpecToText_ = [ '%%' => '%' ];

    private $posixFormat_; ///< string
    private $phpFormat_;   ///< string
    private $text_;        ///< string
    private $length_;      ///< int

    public function __construct(string $posixFormat)
    {
        if (!isset(self::$posixSpecToPhpSpec_)) {
            self::$posixSpecToPhpSpec_ = [];

            foreach (self::POSIX_FORMAT_SPECS_MAP as $posixSpec => $data) {
                [
                    self::$posixSpecToPhpSpec_[$posixSpec],
                    self::$posixSpecToText_[$posixSpec]
                ] = $data;
            }
        }

        $this->posixFormat_ = $posixFormat;

        $phpFormat = strtr(
            /* Escape all characters which are not part of a format
             * specifier:
             * 1. escape all alphabetic characters
             * 2. replace all double %% by \x01
             * 3. replace %\ to % to not escape characters which are format
             *    specifiers; step 2 ensures that letters after double %%
             *    remain escaped
             * 4. replace \x01 by %% again (some lines later below).
             */
            strtr(
                preg_replace('/([A-Z])/', '\\\\$1', $posixFormat),
                [
                    '%%' => "\x01",
                    '%\\' => '%'
                ]
            ),
            self::$posixSpecToPhpSpec_
        );

        if (strpos($phpFormat, '%') !== false) {
            /** @throw alcamo::exception::Unsupported if the format contains
             *  an unsupported format specifier. */
            throw (new Unsupported())->setMessageContext(
                [
                    'feature' =>
                        'Posix format specifier %'
                        . $phpFormat[strpos($phpFormat, '%') + 1]
                ]
            );
        }

        $this->phpFormat_ = strtr($phpFormat, [ "\x01" => '%' ]);

        $this->text_ = strtr($posixFormat, self::$posixSpecToText_);

        if (strpos($this->text_, '*') === false) {
            $this->length_ = strlen($this->text_);
        }
    }

    public function __toString(): string
    {
        return $this->posixFormat_;
    }

    public function getPhpFormat(): string
    {
        return $this->phpFormat_;
    }

    /**
     * @brief Textual representation
     *
     * Fixed-length elements are represented by repetetion of the Posix format
     * specifier character, variable-length elements by asterisk. Useful for
     * human readers and to compute the length of the result, if fixed.
     */
    public function getText(): string
    {
        return $this->text_;
    }

    /// Length of result, if fixed
    public function getLength(): ?int
    {
        return $this->length_;
    }

    public function applyTo(\DateTimeInterface $dateTime): string
    {
        return $dateTime->format($this->phpFormat_);
    }
}
