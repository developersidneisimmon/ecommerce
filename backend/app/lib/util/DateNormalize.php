<?php

/**
 * Description of DateNormalize
 *
 * @author sidneisimmon
 */
class DateNormalize {

    private static $date;

    public static function createDateFromFormat($date, $fromFormat, $toFormat) {       
        self::$date = DateTime::createFromFormat($fromFormat, $date);
        return self::$date->format($toFormat);
    }

}
