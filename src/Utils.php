<?php

namespace AntoninDeniau\Fuz;

class Utils {
    public static function shortenHash($data) {
	    $res = "";
	    $len = strlen($data) / 2;
	    for ($i = 0; $i < $len; $i++) {
	        $res .= $data[$i] ^ $data[$len + $i];
	    }

	    return $res;
	}

    public static function hash($data)
    {
        $data = sha1($data, true);
        return bin2hex(Utils::shortenHash(Utils::shortenHash($data)));
    }

    public static function hammingDist($a, $b)
    {
        return count(array_diff_assoc(str_split($a), str_split($b)));
    }
}
