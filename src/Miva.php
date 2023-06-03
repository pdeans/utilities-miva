<?php

namespace pdeans\Utilities;

/**
 * Collection of helper methods specific to the Miva Merchant platform
 */
class Miva
{
    /**
     * Generate a Miva code value (ex: category code, product code, etc.)
     */
    public static function createCode(
        string $subject,
        int $max_length = 50,
        string $separator = '-',
        string $case = ''
    ): string {
        $code  = $subject;
        $case  = mb_strtolower($case);
        $flips = [];

        if ($separator === '-') {
            $flips[] = '_';
        } elseif ($separator === '_') {
            $flips[] = '-';
        } else {
            array_push($flips, '-', '_');
        }

        // Convert all flips into separator
        foreach ($flips as $flip) {
            $code = preg_replace('/[' . preg_quote($flip) . ']+/u', $separator, $code);
        }

        // Remove all characters that are not the separator, letters, numbers, or whitespace
        $code = preg_replace('/[^' . preg_quote($separator) . '\pL\pN\s]+/u', '', $code);

        // Replace all separator characters and whitespace by a single separator
        $code = preg_replace('/[' . preg_quote($separator) . '\s]+/u', $separator, $code);

        // Limit to max length setting
        $code = substr($code, 0, $max_length);

        // Set code case if provided
        if ($case === 'lowercase') {
            $code = mb_strtolower($code);
        } elseif ($case === 'uppercase') {
            $code = mb_strtoupper($code);
        }

        return trim($code, $separator);
    }

    /**
     * Create a customer login from an email address
     * @return string
     */
    public static function createLoginFromEmail(string $email, int $max_length = 50): string
    {
        return substr(preg_replace('/[^[:alnum:]]/u', '', strstr($email, '@', true)), 0, $max_length);
    }

    /**
     * Deserialize Miva data into an associative array
     */
    public static function deserialize(string $serialized_str): array
    {
        $result = [];

        foreach (explode(',', $serialized_str) as $item) {
            // These values are for checking if the array key is Miva array key.
            // Reset these to null for each loop iteration.
            $mv_array_key   = null;
            $mv_array_index = null;

            // The first ':' is always deadweight, we can go ahead and trim it off
            $item = ltrim($item, ':');

            // Split each item by the ':' delimiter and store it into a new array
            $col_sep_arr = explode(':', $item);

            // If there is only one item left in the colon separated array,
            // we can go ahead and map it to the final result
            if (count($col_sep_arr) === 1) {
                // Split the item by the '=' delimiter ([0] = key, [1] = value)
                $target_arr = explode('=', $col_sep_arr[0]);

                // Check if the key is a Miva array
                $mv_array_parts = self::isMivaArray($target_arr[0], true);

                // The key is a Miva array
                if (!empty($mv_array_parts)) {
                    $mv_array_key   = $mv_array_parts[1];
                    $mv_array_index = (int)$mv_array_parts[2];

                    // The key has a name and index (Ex: example[1]).
                    if ($mv_array_key && $mv_array_index) {
                        // Decode url-encoding and set the value of the array element
                        $result[$mv_array_key][($mv_array_index - 1)] = urldecode($target_arr[1]);
                        continue;
                    } elseif (!$mv_array_key && $mv_array_index) { // The key has an index only (Ex: [1]).
                        // Decode url-encoding and set the value of the array element
                        $result[($mv_array_index - 1)] = urldecode($target_arr[1]);
                        continue;
                    }
                }

                // Decode url-encoding and set the value of the array element
                $result[$target_arr[0]] = urldecode($target_arr[1]);
            } else {
                $target_item = array_pop($col_sep_arr);
                $target_arr  = explode('=', $target_item);
                $target_val  = urldecode($target_arr[1]);

                // Push the target item's key back onto the colon separated array
                array_push($col_sep_arr, $target_arr[0]);

                // Create a reference of the results array to keep track of its current dimension
                $result_ref = &$result;

                // Loop through the colon separated array, which should now only contain the keys
                // to map to the proper dimension of the results array
                while ($key = array_shift($col_sep_arr)) {
                    // Check if the key is a Miva array
                    $mv_array_parts = self::isMivaArray($key, true);

                    // The key is a Miva array
                    if ($mv_array_parts) {
                        $mv_array_key   = $mv_array_parts[1];
                        $mv_array_index = (int)$mv_array_parts[2];

                        // The key has a name and index (Ex: example[1])
                        if ($mv_array_key && $mv_array_index) {
                            // Rename the key to the Miva array key minus the index
                            $key = $mv_array_key;
                            $res_array_index = ($mv_array_index - 1);

                            // If the key doesn't already exist in the results array, add it as a layer
                            // by setting the key to an empty array and the array index that follows it
                            if (!isset($result_ref[$key])) {
                                $result_ref[$key] = [];
                                $result_ref[$key][$res_array_index] = [];
                            }

                            // Move the reference index deeper and continue to the next key
                            $result_ref = &$result_ref[$key][$res_array_index];
                            continue;
                        } elseif (!$mv_array_key && $mv_array_index) {
                            // Move the reference to point to the index and continue to the next key
                            $result_ref = &$result_ref[($mv_array_index - 1)];
                            continue;
                        }
                    }

                    // If the key doesn't already exist in the results array, add it as a layer
                    // by setting the key to an empty array
                    if (!isset($result_ref[$key])) {
                        $result_ref[$key] = [];
                    }

                    // Move the reference deeper...
                    $result_ref = &$result_ref[$key];
                }

                // Now that we have our dimensions set, assign value of this dimension to the
                // target value that we stored earlier
                $result_ref = $target_val;
            }
        }

        // ...and voila, return the results
        return $result;
    }

    /**
     * Determine if subject string is a Miva array
     */
    protected static function isMivaArray(string $subject, bool $return_matches = false): bool|array
    {
        preg_match('/(\w+)?\[(\d+)\]/', $subject, $matches);

        if ($return_matches) {
            return $matches;
        }

        return !empty($matches);
    }
}
