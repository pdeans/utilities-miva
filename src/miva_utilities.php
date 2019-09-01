<?php

use pdeans\Utilities\Miva;

if (!function_exists('miva_deserialize_array')) {
    /**
     * Deserialize Miva data into an associative array
     *
     * @param string $mv_serialized_str
     *
     * @return array
     */
    function miva_deserialize_array($mv_serialized_str)
    {
        return Miva::deserialize($mv_serialized_str);
    }
}

if (!function_exists('miva_generate_code')) {
    /**
     * Generate a Miva code value (ex: category code, product code, etc.)
     *
     * @param string  $subject
     * @param integer $max_length
     * @param string  $separator
     * @param string  $case
     *
     * @return string
     */
    function miva_generate_code($subject, $max_length = 50, $separator = '-', $case = 'lowercase')
    {
        return Miva::createCode($subject, $max_length, $separator, $case);
    }
}

if (!function_exists('miva_generate_login')) {
    /**
     * Create a customer login from an email address
     *
     * @param string  $email
     * @param integer $max_length
     *
     * @return string
     */
    function miva_generate_login($email, $max_length = 50)
    {
        return Miva::createLoginFromEmail($email, $max_length);
    }
}
