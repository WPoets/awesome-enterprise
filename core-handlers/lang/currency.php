<?php
namespace aw2\currency;

function has_valid_decimals($number) {
    $number = (string)$number; // Convert to string to handle all cases uniformly
    $parts = explode('.', $number);
    
    // If there's no decimal part, it's valid (0 decimal places)
    if (count($parts) === 1) {
        return true;
    }
    
    // If there's more than one decimal point, it's invalid
    if (count($parts) > 2) {
        return false;
    }
    
    // Check if there are 0-2 decimal places
    return strlen($parts[1]) <= 2;
}

\aw2_library::add_service('currency.is.currency', 'Check if a value is a valid currency (float with 0-2 decimal places)', ['func'=>'is_currency', 'namespace'=>__NAMESPACE__]);
function is_currency($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'is_currency'));
    
    return is_float($main) && has_valid_decimals($main);
}

\aw2_library::add_service('currency.get', 'Get a currency value from the environment', ['func'=>'get', 'namespace'=>__NAMESPACE__]);
function get($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null,
        'default' => null
    ), $atts, 'currency_get'));
    
    $value = \aw2_library::get($main, $atts, $content);
    
    if($value === null && $default !== null) {
        $value = $default;
    }
    
    if(!is_float($value)) {
        throw new \InvalidArgumentException('currency.get: The retrieved value must be a float.');
    }
    
    if(!has_valid_decimals($value)) {
        throw new \InvalidArgumentException('currency.get: The currency value must have 0-2 decimal places.');
    }
    
    return $value;
}

\aw2_library::add_service('currency.create', 'Create a currency value', ['func'=>'create', 'namespace'=>__NAMESPACE__]);
function create($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'currency_create'));
    
    if(!is_numeric($main)) {
        throw new \InvalidArgumentException('currency.create: main must be a float value. Use currency: prefix for typecasting.');
    }
    $main=(float)$main;
    if(!has_valid_decimals($main)) {
        throw new \InvalidArgumentException('currency.create: The currency value must have 0-2 decimal places.');
    }
    
    return $main;
}

\aw2_library::add_service('currency.display', 'Display currency value with 2 decimal places', ['func'=>'display', 'namespace'=>__NAMESPACE__]);
function display($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array(
        'main' => null
		), $atts, 'currency_display'));
    
    if(!is_float($main)) {
        throw new \InvalidArgumentException('currency.display: main must be a float value. Use currency: prefix for typecasting.');
    }
    
    return number_format($main,2, '.', ',');
}

\aw2_library::add_service('currency.rupees.words', 'Convert Rupees to words using Indian numbering system', ['func'=>'rupees_words', 'namespace'=>__NAMESPACE__]);
function rupees_words($atts, $content=null, $shortcode=null) {
    extract(\aw2_library::shortcode_atts(array('main' => null), $atts, 'rupees_words'));

    if(!is_float($main)) {
        throw new \InvalidArgumentException('currency.rupees.words: main must be a float value. Use currency: prefix for typecasting.');
    }

    $amount = round($main, 2);
    $whole = floor($amount);
    $fraction = round(($amount - $whole) * 100);

    $words = '';

    if ($whole > 0) {
        $words = convert_to_words($whole);
        $words .= ($whole == 1) ? " Rupee" : " Rupees";
    }

    if ($fraction > 0) {
        if ($whole > 0) {
            $words .= " and ";
        }
        $words .= convert_to_words($fraction) . " Paise";
    }

    if ($whole == 0 && $fraction == 0) {
        $words = "Zero Rupees";
    }

    return ucfirst(trim($words));
}

function convert_to_words($number) {
    $words = array(
        0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
        30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
        80 => 'Eighty', 90 => 'Ninety'
    );

    $scales = array(
        10000000 => 'Crore',
        100000 => 'Lakh',
        1000 => 'Thousand',
        100 => 'Hundred',
    );

    if ($number == 0) {
        return 'Zero';
    }

    $result = '';

    foreach ($scales as $scale => $name) {
        if ($number >= $scale) {
            $quotient = floor($number / $scale);
            $result .= convert_to_words($quotient) . ' ' . $name;
            if ($quotient > 1 && $name != 'Hundred') {
                $result .= 's';
            }
            $result .= ' ';
            $number %= $scale;
        }
    }

    if ($number > 0) {
        if ($number < 21) {
            $result .= $words[$number];
        } else {
            $tens = floor($number / 10) * 10;
            $units = $number % 10;
            $result .= $words[$tens];
            if ($units) {
                $result .= '-' . $words[$units];
            }
        }
    }

    return trim($result);
}

