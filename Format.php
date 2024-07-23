<?php

namespace Northrook\HTML;

use Northrook\HTML\Formatter\FormatterFunctionsTrait;
use Northrook\HTML\Formatter\Newline;
use Northrook\HTML\Formatter\Purpose;
use Northrook\HTML\Element\Attributes;
use Northrook\Minify;
use function Northrook\escapeHtmlText;
use const Northrook\EMPTY_STRING;

/**
 * All static functions _must_ return string.
 */
class Format
{
    use FormatterFunctionsTrait;

    public static function title(
        string  $string,
        Purpose $type,
    ) : string {

        $string = trim( $string );

        $string = match ( $type ) {
            Purpose::Title     => ucfirst( $string ), // Limit to `<title>`
            Purpose::Paragraph => ucfirst( $string ), // Limit to recommended paragraph length
            default            => $string
        };

        return escapeHtmlText( $string );
    }

    public static function nl2p( string $string ) : string {
        return Format::newline( $string, Newline::Paragraph );
    }

    public static function nl2s( string $string ) : string {
        return Format::newline( $string, Newline::Span );
    }

    public static function newline( string $string, Newline $strategy = Newline::Auto ) : string {

        // Trim the provided string.
        $string = \trim( $string );

        // Bail early if the string was empty, null, or nothing but whitespace.
        if ( !$string ) {
            return EMPTY_STRING;
        }

        // Explode the string into lines.
        $lines = Format::explodeLinebreaks( $string );

        return match ( $strategy ) {
            Newline::Paragraph => Format::implodeWrap( $lines, 'p' ),
            Newline::Span      => Format::implodeWrap( $lines, 'span' ),
            Newline::Auto      => count( $lines ) === 1
                ? '<span>' . trim( $string ) . '</span>'
                : Format::implodeWrap( $lines, 'p' ),
        };
    }

    /**
     * @param string  $string
     * @param array   $attributes
     *
     * @return string
     */
    public static function backtickCodeTags( string $string, array $attributes = [] ) : string {

        // TODO: Code highlighter | Use tempest/highlight
        return \preg_replace_callback(
            '/`(\S.*?)`/m',
            static fn ( $matches ) => new Element( 'code', $attributes, escapeHtmlText( $matches[ 1 ] ) ),
            $string,
        );
    }
}