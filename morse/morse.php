<?php
$morseCode = array(
    'A' => '.-',     'B' => '-...',   'C' => '-.-.',   'D' => '-..',
    'E' => '.',      'F' => '..-.',   'G' => '--.',    'H' => '....',
    'I' => '..',     'J' => '.---',   'K' => '-.-',    'L' => '.-..',
    'M' => '--',     'N' => '-.',     'O' => '---',    'P' => '.--.',
    'Q' => '--.-',   'R' => '.-.',    'S' => '...',    'T' => '-',
    'U' => '..-',    'V' => '...-',   'W' => '.--',    'X' => '-..-',
    'Y' => '-.--',   'Z' => '--..',
    '0' => '-----',  '1' => '.----',  '2' => '..---',  '3' => '...--',
    '4' => '....-',  '5' => '.....',  '6' => '-....',  '7' => '--...',
    '8' => '---..',  '9' => '----.',
    ' ' => '/'
);

// Encode the input text to Morse code
function encodeToMorse($text) {
    global $morseCode;
    $encodedText = '';
    $text = strtoupper($text);
    $length = strlen($text);
    for ($i = 0; $i < $length; $i++) {
        $char = $text[$i];
        if (isset($morseCode[$char])) {
            $encodedText .= $morseCode[$char] . ' ';
        }
    }
    return trim($encodedText);
}

// Decode the Morse code to plain text
function decodeFromMorse($morse) {
    global $morseCode;
    $decodedText = '';
    $morse = trim($morse);
    $words = explode('/', $morse);
    foreach ($words as $word) {
        $characters = explode(' ', $word);
        foreach ($characters as $character) {
            $key = array_search($character, $morseCode);
            if ($key !== false) {
                $decodedText .= $key;
            }
        }
        $decodedText .= ' ';
    }
    return trim($decodedText);
}

echo encodeToMorse("HELLO WORLD");
echo "<br><br>";
echo decodeFromMorse(".... . .-.. .-.. --- / .-- --- .-. .-.. -..");
?>