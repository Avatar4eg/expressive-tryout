<?php
namespace App\Service;

use Zend\Filter;

class StringService
{
    /**
     * @param string $string
     * @param int $length
     * @param array $options
     * @return string
     */
    public static function clearString(string $string, int $length = 0, array $options = []): string
    {
        $defaults = [
            'ellipsis' => false,
            'pattern' => '/\W/',
        ];
        $options = array_merge($defaults, $options);

        $filter = new Filter\StripTags();
        $clean_string = $filter->filter($string);
        $filter = new Filter\StringTrim();
        $clean_string = $filter->filter($clean_string);
        if ($options['pattern'] !== '') {
            $clean_string = preg_replace($options['pattern'], '', $clean_string);
        }
        if ($length !== 0 && mb_strlen($clean_string) > $length) {
            $clean_string = mb_substr($clean_string, 0, $length);
            if ($options['ellipsis'] === true) {
                $clean_string .= ' ...';
            }
        }

        return $clean_string;
    }

    /**
     * @param array $array
     * @return array
     */
    public static function clearArray(array $array): array
    {
        foreach ($array as $key => $item) {
            if (!is_array($item)) {
                $array[$key] = self::clearString($item);
            } else {
                $array[$key] = self::clearArray($item);
            }
        }
        return $array;
    }
}