<?php
namespace App\Service;

use GuzzleHttp\Client;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FileService
{
    const JSON_ENCODE_SETTINGS = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;

    /**
     * @param string $path
     * @return bool
     */
    public static function checkFileExist(string $path): bool
    {
        return file_exists(trim($path, '"'));
    }

    /**
     * @param string $url
     * @param array|null $params
     * @return bool
     */
    public static function checkUrl(string $url, ?array $params = []): bool
    {
        $client = new Client([
            'verify' => false,
        ]);

        try {
            $client->head($url, [
                'headers' => $params
            ]);
            return true;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $e->getCode() === 403;
        }
    }

    /**
     * @param array $data
     * @param string $path
     * @return bool
     */
    public static function saveJson(array $data, string $path): bool
    {
        try {
            $json_file = fopen($path, 'wb');
            if ($json_file === false) {
                return false;
            }
            fwrite($json_file, json_encode($data['data'], self::JSON_ENCODE_SETTINGS));
            fclose($json_file);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function deleteFolder(string $path): bool
    {
        if (self::checkFileExist($path)) {
            try {
                $it = new RecursiveDirectoryIterator($path);
                $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($files as $file) {
                    $filename = $file->getFilename();
                    if ($filename === '.' || $filename === '..') {
                        continue;
                    }
                    if (is_link($file)) {
                        unlink($file);
                    }
                    if ($file->isDir()) {
                        rmdir($file->getRealPath());
                    } else {
                        unlink($file->getRealPath());
                    }
                }
                rmdir($path);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }
}