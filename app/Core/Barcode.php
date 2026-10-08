<?php
namespace App\Core;

/**
 * High-Precision Barcode Generator for Pharmacy Labels & Invoices
 * Generates ISO/IEC 15417 compliant Code 128 barcodes (Subsets B & C)
 * Output is pure, razor-sharp vector SVG compatible with 1D/2D optical barcode scanners
 * (Honeywell, Zebra, Datalogic, Symbol/Motorola, Eyoyo, Netum, and smartphone cameras).
 */
class Barcode
{
    /**
     * Code 128 pattern table (Values 0 to 106)
     * Each string consists of bar and space widths (3 bars + 3 spaces = 11 modules, except stop = 13 modules)
     */
    private static array $patterns = [
        "212222","222122","222221","121223","121322","131222","122213","122312","132212","221213",
        "221312","231212","112232","122132","122231","113222","123122","123221","223211","221132",
        "221231","213212","223112","312131","311222","321122","321221","312212","322112","322211",
        "212123","212321","232121","111323","131123","131321","112313","132113","132311","211313",
        "231113","231311","112133","112331","132131","113123","113321","133121","313121","211331",
        "231131","213113","213311","213131","311123","311321","331121","312113","312311","332111",
        "314111","221411","431111","111224","111422","121124","121421","141122","141221","112214",
        "112412","122114","122411","142112","142211","241211","221114","413111","241112","134111",
        "111242","121142","121241","114212","124112","124211","411212","421112","421211","212141",
        "214121","412121","111143","111341","131141","114113","114311","411113","411311","113141",
        "114131","311141","411131","211412","211214","211232","2331112"
    ];

    /**
     * Encode a string into Code 128 symbol sequence including Start, Checksum, and Stop
     */
    public static function encode(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            $text = '000000';
        }

        $isNumeric = ctype_digit($text);
        $len = strlen($text);

        // Code 128-C: pairs of digits for ultra-compact high density numeric codes
        if ($isNumeric && ($len % 2 === 0)) {
            $symbols = [105]; // Start C
            $checksum = 105;
            $pos = 1;
            for ($i = 0; $i < $len; $i += 2) {
                $val = intval(substr($text, $i, 2));
                $symbols[] = $val;
                $checksum += ($pos * $val);
                $pos++;
            }
        } else {
            // Code 128-B: Standard ASCII (alphanumeric, uppercase, lowercase, punctuation)
            $symbols = [104]; // Start B
            $checksum = 104;
            $pos = 1;
            for ($i = 0; $i < $len; $i++) {
                $ascii = ord($text[$i]);
                $val = ($ascii >= 32 && $ascii <= 126) ? ($ascii - 32) : 0;
                $symbols[] = $val;
                $checksum += ($pos * $val);
                $pos++;
            }
        }

        $symbols[] = $checksum % 103; // Checksum modulo 103
        $symbols[] = 106; // Stop symbol
        return $symbols;
    }

    /**
     * Convert encoded symbols into binary string of 1 (bar) and 0 (space)
     */
    public static function toBinary(array $symbols): string
    {
        $binary = '';
        foreach ($symbols as $s) {
            if (!isset(self::$patterns[$s])) continue;
            $pattern = self::$patterns[$s];
            $isBar = true;
            for ($i = 0; $i < strlen($pattern); $i++) {
                $width = (int)$pattern[$i];
                $binary .= str_repeat($isBar ? '1' : '0', $width);
                $isBar = !$isBar;
            }
        }
        return $binary;
    }

    /**
     * Render a standard-compliant, scanner-readable SVG barcode
     *
     * @param string $text Barcode content to encode
     * @param int $barHeight Height of bars in pixels/units (default 36)
     * @param float $moduleWidth Width of narrowest single bar in pixels/units (default 1.35)
     * @param bool $showText Whether to render human-readable text below the bars in the SVG
     * @param string $barColor Hex color of the bars (default #000000)
     * @param string $bgColor Background color (default transparent or #ffffff)
     * @return string Valid SVG markup
     */
    public static function renderSvg(
        string $text,
        int $barHeight = 36,
        float $moduleWidth = 1.35,
        bool $showText = false,
        string $barColor = '#000000',
        string $bgColor = 'transparent'
    ): string {
        $cleanText = trim($text);
        if ($cleanText === '') {
            $cleanText = '000000';
        }

        $symbols = self::encode($cleanText);
        $bars = self::toBinary($symbols);

        // Standard Quiet Zone (minimum 10 modules on both sides for reliable laser gun detection)
        $quietZoneModules = 10;
        $totalModules = strlen($bars) + ($quietZoneModules * 2);
        $totalWidth = round($totalModules * $moduleWidth, 2);
        $textHeight = $showText ? 14 : 0;
        $totalHeight = $barHeight + $textHeight;

        // Group consecutive '1' bits into single SVG rects for high performance
        $rects = '';
        $barStart = null;
        for ($i = 0; $i < strlen($bars); $i++) {
            if ($bars[$i] === '1') {
                if ($barStart === null) {
                    $barStart = $i;
                }
            } else {
                if ($barStart !== null) {
                    $w = round(($i - $barStart) * $moduleWidth, 2);
                    $bx = round(($quietZoneModules + $barStart) * $moduleWidth, 2);
                    $rects .= sprintf('<rect x="%.2f" y="0" width="%.2f" height="%d" fill="%s"/>', $bx, $w, $barHeight, $barColor);
                    $barStart = null;
                }
            }
        }
        if ($barStart !== null) {
            $w = round((strlen($bars) - $barStart) * $moduleWidth, 2);
            $bx = round(($quietZoneModules + $barStart) * $moduleWidth, 2);
            $rects .= sprintf('<rect x="%.2f" y="0" width="%.2f" height="%d" fill="%s"/>', $bx, $w, $barHeight, $barColor);
        }

        $bgRect = '';
        if ($bgColor !== 'transparent' && !empty($bgColor)) {
            $bgRect = sprintf('<rect width="100%%" height="100%%" fill="%s"/>', htmlspecialchars($bgColor));
        }

        $textElement = '';
        if ($showText) {
            $textElement = sprintf(
                '<text x="%.2f" y="%d" text-anchor="middle" font-family="Courier, monospace" font-size="10" font-weight="600" fill="%s">%s</text>',
                $totalWidth / 2,
                $barHeight + 11,
                $barColor,
                htmlspecialchars($cleanText)
            );
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %.2f %d" width="100%%" height="%d" preserveAspectRatio="xMidYMid meet" style="display:block;margin:0 auto;max-width:%.2fpx;shape-rendering:crispEdges;">%s%s%s</svg>',
            $totalWidth,
            $totalHeight,
            $totalHeight,
            $totalWidth,
            $bgRect,
            $rects,
            $textElement
        );
    }
}
