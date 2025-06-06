<?php

class RandomGenerator {
    // LCG state
    private static $lcg_s1 = 1;
    private static $lcg_s2 = 1;
    private static $lcg_seeded = false;
    
    // MT19937 constants
    const MT_N = 624;
    const MT_M = 397;
    const PHP_MT_RAND_MAX = 2147483647; // (1<<31) - 1
    
    // MT19937 state
    private static $mt_state = [];
    private static $mt_left = 0;
    private static $mt_next = 0;
    private static $mt_rand_is_seeded = false;
    
    /**
     * MODMULT macro implementation
     * Original: #define MODMULT(a, x0, c, m, s) q = s / a; s = x0 * (s - a * q) - c * q; if (s < 0) s += m
     */
    private static function modmult($a, $x0, $c, $m, &$s) {
        $q = intval($s / $a);
        $s = $x0 * ($s - $a * $q) - $c * $q;
        if ($s < 0) {
            $s += $m;
        }
    }
    
    /**
     * LCG seed initialization
     */
    private static function lcg_seed() {
        $tv_sec = time();
        $tv_usec = (int)((microtime(true) - $tv_sec) * 1000000);
        self::$lcg_s1 = $tv_sec ^ (~$tv_usec);
        self::$lcg_s2 = posix_getpid() ^ (($tv_usec ^ ($tv_usec >> 1)) | 1);
        self::$lcg_seeded = true;
    }
    
    /**
     * Combined Linear Congruential Generator
     */
    public static function php_combined_lcg() {
        if (!self::$lcg_seeded) {
            self::lcg_seed();
        }
        
        self::modmult(53668, 40014, 12211, 2147483563, self::$lcg_s1);
        self::modmult(52774, 40692, 3791, 2147483399, self::$lcg_s2);
        
        $z = self::$lcg_s1 - self::$lcg_s2;
        if ($z < 1) {
            $z += 2147483562;
        }
        
        return $z * 4.656613e-10;
    }
    
    /**
     * Generate seed using LCG
     */
    private static function generate_seed() {
        return ((int)(time() * posix_getpid())) ^ ((int)(1000000.0 * self::php_combined_lcg()));
    }
    
    /**
     * Convert to unsigned 32-bit integer
     */
    private static function toUint32($value) {
        // Handle negative values and ensure 32-bit range
        if ($value < 0) {
            $value = $value & 0x7FFFFFFF;
            $value = $value | 0x80000000;
        }
        return $value & 0xFFFFFFFF;
    }
    
    /**
     * MT19937 helper functions
     */
    private static function hiBit($u) {
        return ($u & 0x80000000) ? 0x80000000 : 0;
    }
    
    private static function loBit($u) {
        return ($u & 0x00000001) ? 1 : 0;
    }
    
    private static function loBits($u) {
        return $u & 0x7FFFFFFF;
    }
    
    private static function mixBits($u, $v) {
        return self::hiBit($u) | self::loBits($v);
    }
    
    private static function twist($m, $u, $v) {
        $y = self::mixBits($u, $v);
        $mag01 = self::loBit($u) ? 0x9908b0df : 0;
        return self::toUint32($m ^ ($y >> 1) ^ $mag01);
    }
    
    /**
     * Initialize MT19937 with seed
     */
    private static function php_mt_initialize($seed) {
        self::$mt_state = array_fill(0, self::MT_N, 0);
        
        // Ensure seed is treated as unsigned 32-bit
        $seed = self::toUint32($seed);
        self::$mt_state[0] = $seed;
        
        for ($i = 1; $i < self::MT_N; $i++) {
            $prev = self::$mt_state[$i - 1];
            // This is the key part - the multiplication must handle overflow correctly
            $temp = 1812433253 * ($prev ^ ($prev >> 30)) + $i;
            self::$mt_state[$i] = self::toUint32($temp);
        }
    }
    
    /**
     * Reload/generate next batch of MT19937 numbers
     */
    private static function php_mt_reload() {
        $state = &self::$mt_state;
        
        // Generate N words at one time
        $p = 0;
        
        // First 227 values
        for ($i = self::MT_N - self::MT_M; $i--; $p++) {
            $state[$p] = self::twist($state[$p + self::MT_M], $state[$p], $state[$p + 1]);
        }
        
        // Middle 396 values
        for ($i = self::MT_M; --$i; $p++) {
            $state[$p] = self::twist($state[$p + (self::MT_M - self::MT_N)], $state[$p], $state[$p + 1]);
        }
        
        // Last value
        $state[$p] = self::twist($state[$p + (self::MT_M - self::MT_N)], $state[$p], $state[0]);
        
        self::$mt_left = self::MT_N;
        self::$mt_next = 0;
    }
    
    /**
     * Seed the MT19937 generator
     */
    public static function mt_srand($seed = null) {
        if ($seed === null) {
            $seed = self::generate_seed();
        }
        
        self::php_mt_initialize($seed);
        self::php_mt_reload();
        self::$mt_rand_is_seeded = true;
    }
    
    /**
     * Generate next MT19937 random number (internal)
     */
    private static function php_mt_rand() {
        if (self::$mt_left == 0) {
            self::php_mt_reload();
        }
        
        self::$mt_left--;
        
        $s1 = self::$mt_state[self::$mt_next++];
        $s1 ^= ($s1 >> 11);
        $s1 ^= ($s1 << 7) & 0x9d2c5680;
        $s1 ^= ($s1 << 15) & 0xefc60000;
        $s1 ^= ($s1 >> 18);
        
        return self::toUint32($s1);
    }
    
    /**
     * Generate MT19937 random number (public interface)
     */
    public static function mt_rand($min = null, $max = null) {
        if ($min !== null && $max !== null) {
            if ($max < $min) {
                trigger_error("mt_rand(): max($max) is smaller than min($min)", E_USER_WARNING);
                return false;
            }
        }
        
        if (!self::$mt_rand_is_seeded) {
            self::mt_srand();
        }
        
        // Important: The C code shifts right by 1 to ensure positive number
        $number = self::php_mt_rand() >> 1;
        
        if ($min !== null && $max !== null) {
            // RAND_RANGE implementation
            // Original: (min + (zend_long)((double)(max - min + 1.0) * (number / (PHP_MT_RAND_MAX + 1.0))))
            $range = $max - $min;
            $number = $min + (int)((double)($range + 1.0) * ($number / (self::PHP_MT_RAND_MAX + 1.0)));
        }
        
        return $number;
    }
    
    /**
     * Get the maximum random value
     */
    public static function mt_getrandmax() {
        return self::PHP_MT_RAND_MAX;
    }
}

/**
 * Drop-in replacement for PHP's mt_rand()
 * 
 * @param int|null $min Minimum value (optional)
 * @param int|null $max Maximum value (optional)
 * @return int|false Random number or false on error
 */
function mt_rand_compat($min = null, $max = null) {
    // Handle the different calling patterns to match PHP's mt_rand()
    if ($min === null && $max === null) {
        // No arguments: return number between 0 and mt_getrandmax()
        return RandomGenerator::mt_rand();
    } elseif ($max === null) {
        // One argument: treat it as max, with min = 0
        return RandomGenerator::mt_rand(0, $min);
    } else {
        // Two arguments: use as min and max
        return RandomGenerator::mt_rand($min, $max);
    }
}

/**
 * Drop-in replacement for PHP's mt_srand()
 * 
 * @param int|null $seed Seed value (optional, uses automatic seed if null)
 * @return void
 */
function mt_srand_compat($seed = null) {
    RandomGenerator::mt_srand($seed);
}

$specialCharactersAllowed = array("#", "!", "@", "_", "?");
$letters =array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o",
    "p","q","r","s","t","u","v","w","x","y","z");
$numbers = array("0","1","2","3","4","5","6","7","8","9");

$specialCharactersAllowedRegex="[";
foreach($specialCharactersAllowed as $c)
    $specialCharactersAllowedRegex.=$c;
$specialCharactersAllowedRegex.="]";

function isPassValid($pass, $minLength, $maxLength)
{
    global $specialCharactersAllowedRegex;
    
    // Replicate old buggy php7 behaviour
    // used to be: !($pass < $minLength) and !($pass > $maxLength);
    $validLength = ((float)$pass) >= $minLength and ((float)$pass) <= $maxLength;
    $specialCharacterCount = preg_match_all("/".$specialCharactersAllowedRegex."/", $pass)>0;
    $lowercaseCount = preg_match_all("/[a-z]/", $pass)>0;
    $uppercaseCount = preg_match_all("/[A-Z]/", $pass)>0;
    $digitCount = preg_match_all("/[0-9]/", $pass)>0;
    $valid = $validLength && $specialCharacterCount && $lowercaseCount && $uppercaseCount && $digitCount;
    return $valid;
}


function getPassHash($pass1, $pass2)
{
    return md5($pass1 . md5($pass2));
}

function loadWords()
{
    return file("dictionary.txt");
}

function capitaliseRandom($pass)
{
    $strout="";
    $strlen = strlen($pass);
    for( $i = 0; $i < $strlen; ++$i)
    {
        $char = $pass[$i];
        //Skip characters that are difficult to read if capitalised:
        if($char=="i")
            $strout.=$char;
        else
            $strout.= mt_rand_compat(0,5) < 1 ? strtoupper($char):$char;
    }
    return $strout;
}

function generatePassword($idHash, $passHash, $minLength, $maxLength, $noDict)
{
    //print("id: $idHash, pass: $passHash, min: $minLength, max: $maxLength, noDict: $noDict<br>");   
    global $specialCharactersAllowed, $letters, $numbers, $letters;

    $h = md5($idHash . $passHash);
    $seed = intval(substr($h, 0, 7), 16) ^
            intval(substr($h, 8, 7), 16) ^
            intval(substr($h, 16, 7), 16) ^
            intval(substr($h, 24, 7), 16);

    $words = null;

    mt_srand_compat($seed);

    if($noDict)
        $words = $letters;
    else
        $words = loadWords();

    $wordLen = count($words);
    $pass=null;
    $max_retries = 40000;

    do
    {
        $pass="";
        $retries=0;
        $longestPass = $pass;

        while(true)
        {
            $typeToInsert = mt_rand_compat(0,20);
            $previousPass = $pass;

            //Determine what we should insert.
            if($typeToInsert<3)
                $pass .= $numbers[mt_rand_compat()%count($numbers)];
            else if($typeToInsert<5)
                $pass .= $specialCharactersAllowed[mt_rand_compat()%count($specialCharactersAllowed)];
            else
                $pass .= trim($words[mt_rand_compat()%count($words)]);
            //If the word is bigger than it should be, try again!
            if(strlen($pass)>$maxLength)
            {
                $pass = $previousPass;
                ++$retries;
            }
            //If we found a bigger password on the last iteration, update it.
            else if(strlen($pass)>strlen($longestPass))
                $longestPass = $pass;

            //If we had too many retries, quit! The current password should be good enough.
            if($retries>20)
                break;
        }

        //Add random capitalisations
        $pass = capitaliseRandom($pass);

        //Only quit if this password satisfies important properties. Otherwise retry.
    } while(!isPassValid($pass, $minLength, $maxLength) and ($max_retries--) > 0);

    return $pass;
}

function testRandom() {
    // Test the random number generator
    $results = [];
    mt_srand_compat(123);
    array_push($results, "md5('test') = " . md5('test'));
    array_push($results, "mt_rand() = " . mt_rand_compat());
    array_push($results, "mt_rand(1, 10) = " . mt_rand_compat(1, 10));
    array_push($results, "mt_rand(100, 200) = " . mt_rand_compat(100, 200));

    // returns string
    return implode(" ", $results);
}

?>
