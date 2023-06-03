<?php

use pdeans\Utilities\Miva;

if (!function_exists('miva_deserialize_array')) {
    /**
     * Deserialize Miva data into an associative array
     */
    function miva_deserialize_array(string $mv_serialized_str): array
    {
        return Miva::deserialize($mv_serialized_str);
    }
}

if (!function_exists('miva_generate_code')) {
    /**
     * Generate a Miva code value (ex: category code, product code, etc.)
     */
    function miva_generate_code(
        string $subject,
        int $max_length = 50,
        string $separator = '-',
        string $case = 'lowercase'
    ): string {
        return Miva::createCode($subject, $max_length, $separator, $case);
    }
}

if (!function_exists('miva_generate_login')) {
    /**
     * Create a customer login from an email address
     */
    function miva_generate_login(string $email, int $max_length = 50): string
    {
        return Miva::createLoginFromEmail($email, $max_length);
    }
}
