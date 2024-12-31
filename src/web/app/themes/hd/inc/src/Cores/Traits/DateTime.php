<?php

namespace Cores\Traits;

\defined('ABSPATH') || die;

trait DateTime
{
    // -------------------------------------------------------------

    /**
     * @param $time_string
     *
     * @return bool
     */
    public function isFutureTime($time_string): bool
    {
        $time_converted = self::convertDatetimeFormat($time_string, 'U');
        if (false === $time_converted) {
            return false;
        }

        $current = current_time('U', 0);

        return $time_converted >= $current;
    }

    // -------------------------------------------------------------

    /**
     * Humanizes the time difference between two timestamps.
     *
     * @param mixed $post Optional. The post-ID to get the time from. Default is null.
     * @param false|int|string $from Optional. The starting timestamp. Default is null.
     * @param false|int|string $to Optional. The ending timestamp. Default is current time.
     *
     * @return string The human-readable time difference.
     */
    public static function humanizeTime(
        mixed $post = null,
        false|int|string $from = false,
        false|int|string $to = false
    ): string {
        $_ago = __('ago', TEXT_DOMAIN);

        if (empty($to)) {
            $to = current_time('U', 0);
        }
        if (empty($from) && $post) {
            $from = get_the_time('U', $post);
        }

        // If $from is still empty, return an empty string or handle accordingly
        if (empty($from)) {
            return '';
        }

        $diff  = (int) abs($to - $from);
        $since = human_time_diff($from, $to);
        $since .= ' ' . $_ago;

        return apply_filters('humanize_time_filter', $since, $diff, $from, $to);
    }

    // --------------------------------------------------

    /**
     * Calculates the ISO 8601 duration between two date-time strings.
     *
     * @param string $date_time_1 First date-time string.
     * @param string $date_time_2 Second date-time string.
     *
     * @throws \Exception If the date-time strings are invalid.
     *
     * @return string ISO 8601 duration string.
     */
    public static function isoDuration(string $date_time_1, string $date_time_2): string
    {
        // Create DateTime objects
        $_date_time_1 = new \DateTime($date_time_1);
        $_date_time_2 = new \DateTime($date_time_2);

        // Calculate the interval
        $interval = $_date_time_1->diff($_date_time_2);

        // Start building the ISO duration string
        $isoDuration = 'P';
        $isoDuration .= ($interval->y > 0) ? $interval->y . 'Y' : '';
        $isoDuration .= ($interval->m > 0) ? $interval->m . 'M' : '';
        $isoDuration .= ($interval->d > 0) ? $interval->d . 'D' : '';

        // Check if there are any time components to add
        $timePart = 'T';
        $timePart .= ($interval->h > 0) ? $interval->h . 'H' : '';
        $timePart .= ($interval->i > 0) ? $interval->i . 'M' : '';
        $timePart .= ($interval->s > 0) ? $interval->s . 'S' : '';

        // If there are no time components, reset the time part
        if ($timePart === 'T') {
            $timePart = 'T0S'; // Indicates zero duration in time
        }

        return $isoDuration . $timePart;
    }

    // -------------------------------------------------------------

    /**
     * Converts a date in the site's timezone to UTC with an optional format.
     *
     * @param int|string $date_string Date string or timestamp in the site's timezone.
     * @param string $format Output format: 'timestamp', 'U', DateTime::ATOM, 'Y-m-d H:i:s', etc.
     *
     * @return false|int|string Formatted date string in UTC, timestamp, or false on failure.
     */
    public static function convertToUTC(int|string $date_string, string $format = 'Y-m-d H:i:s'): false|int|string
    {
        // Handle timestamp input
        if (self::isInteger($date_string)) {
            $date_string = '@' . $date_string;
        }

        // Create `DateTime object` in the site's timezone
        $datetime = date_create($date_string, wp_timezone());

        if (false === $datetime) {
            return false;
        }

        // Return `timestamp` if a format is `timestamp` or `U`
        if ('timestamp' === $format || 'U' === $format) {
            return $datetime->getTimestamp();
        }

        // Standardize `mysql` format option
        if ('mysql' === $format) {
            $format = 'Y-m-d H:i:s';
        }

        // Convert to UTC and return in `specified format`
        return $datetime->setTimezone(new \DateTimeZone('UTC'))->format($format);
    }

    // -------------------------------------------------------------

    /**
     * Converts a date in UTC to the site's timezone, with an optional format.
     *
     * @param int|string $date_string Date string or timestamp in UTC.
     * @param string $format Output format: 'timestamp', 'U', DateTime::ATOM, 'Y-m-d H:i:s', etc.
     *
     * @return false|int|string Formatted date string in the site's timezone, timestamp, or false on failure.
     */
    public static function convertFromUTC(int|string $date_string, string $format = 'Y-m-d H:i:s'): false|int|string
    {
        // Handle timestamp input
        if (self::isInteger($date_string)) {
            $date_string = '@' . $date_string;
        }

        // Create DateTime object in UTC timezone
        $datetime = date_create($date_string, new \DateTimeZone('UTC'));

        if (false === $datetime) {
            return false;
        }

        // Convert to site's timezone
        $datetime->setTimezone(wp_timezone());

        // If a format is 'timestamp' or 'U', adjust for the site's timezone offset
        if ('timestamp' === $format || 'U' === $format) {
            return $datetime->getTimestamp() + $datetime->getOffset();
        }

        // Standardize 'mysql' format option
        if ('mysql' === $format) {
            $format = 'Y-m-d H:i:s';
        }

        // Return formatted date string in the site's timezone
        return $datetime->format($format);
    }

    // -------------------------------------------------------------

    /**
     * Converts a date string or timestamp to a specified format in the site's timezone.
     *
     * @param int|string $date_string Date string or timestamp.
     * @param string $format Desired output format: 'timestamp', 'U', 'mysql', 'Y-m-d H:i:s', DateTimeInterface constants, etc.
     *
     * @return false|int|string Formatted date string, timestamp, or false on failure.
     */
    public static function convertDatetimeFormat(int|string $date_string, string $format = 'Y-m-d H:i:s'): false|int|string
    {
        // Convert timestamp to DateTime-friendly format
        if (self::isInteger($date_string)) {
            $date_string = '@' . $date_string;
        }

        // Create a DateTime object in the site's timezone
        $datetime = date_create($date_string, wp_timezone());

        if (false === $datetime) {
            return false;
        }

        // For 'timestamp' or 'U', adjust the timestamp for the site's timezone offset
        if ('timestamp' === $format || 'U' === $format) {
            return $datetime->getTimestamp() + $datetime->getOffset();
        }

        // Map 'mysql' to standard SQL datetime format
        if ('mysql' === $format) {
            $format = 'Y-m-d H:i:s';
        }

        // Return the formatted date string
        return $datetime->format($format);
    }

    // -------------------------------------------------------------

    /**
     * Calculates the time difference between the current time and a target date.
     *
     * @param string $date_string Date string in 'Y-m-d\TH:i:s' format.
     *
     * @throws \Exception
     *
     * @return array Array with time difference in days, hours, minutes, and seconds.
     */
    public static function timeDifference(string $date_string): array
    {
        // Parse target time in the site's timezone
        $targetTime = \DateTime::createFromFormat('Y-m-d\TH:i:s', $date_string, wp_timezone());

        // Return default zeroed values if the date format is invalid
        if ($targetTime === false) {
            return [
                'days'    => '00',
                'hours'   => '00',
                'minutes' => '00',
                'seconds' => '00',
            ];
        }

        $interval = (new \DateTime('now', wp_timezone()))->diff($targetTime);

        // Format and return each time unit as a two-digit string
        return [
            'days'    => str_pad($interval->format('%a'), 2, '0', STR_PAD_LEFT),
            'hours'   => str_pad($interval->format('%h'), 2, '0', STR_PAD_LEFT),
            'minutes' => str_pad($interval->format('%i'), 2, '0', STR_PAD_LEFT),
            'seconds' => str_pad($interval->format('%s'), 2, '0', STR_PAD_LEFT),
        ];
    }

    // --------------------------------------------------
}
