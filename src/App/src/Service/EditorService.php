<?php
namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class EditorService
{
    /**
     * @param array $data
     * @param array $config
     * @return bool
     */
    public static function runAppLocal(array $data, array $config): bool
    {
        $command = implode(' ', [
            $config['filepath']['editor_path'],
            implode(' ', $config['filepath']['editor_params']),
            implode(' ', $data),
        ]);

        try {
            if (0 === stripos(PHP_OS, 'WIN')) {
                pclose(popen('start /B "" ' . $command, 'r'));
            } else {
                pclose(popen($command . ' &', 'r'));
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param array $data
     * @param array $config
     * @return bool
     */
    public static function runAppRemote(array $data, array $config): bool
    {
        $client = new Client([
            'verify' => false,
        ]);
        try {
            $response = $client->post($config['filepath']['editor_path'], [
                'headers'   => $config['filepath']['editor_params'],
                'body'      => $data,
            ]);
            return $response->getStatusCode() === 200;
        } catch (ClientException $e) {
            return false;
        }
    }
}