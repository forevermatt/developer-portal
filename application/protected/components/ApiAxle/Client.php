<?php
namespace Sil\DevPortal\components\ApiAxle;

use Sil\DevPortal\components\Exception\NotFoundException;

class Client extends BaseClient
{
    /**
     * @param string $apiName The code name of the API in question.
     * @param array $data
     * @return ApiInfo
     */
    public function createApi($apiName, $data)
    {
        $data['id'] = $apiName;
        $response = $this->api()->create($data);
        return new ApiInfo(
            $apiName,
            $this->getDataFromResponse($response)
        );
    }
    
    /**
     * @param string $keyValue
     * @param array $data
     * @return KeyInfo
     */
    public function createKey($keyValue, $data)
    {
        $data['id'] = $keyValue;
        $response = $this->key()->create($data);
        return new KeyInfo(
            $keyValue,
            $this->getDataFromResponse($response)
        );
    }
    
    /**
     * @param string $keyringName
     * @return KeyringInfo
     * @throws \Exception
     */
    public function createKeyring($keyringName)
    {
        try {
            $response = $this->keyring()->create(['id' => $keyringName]);
            return new KeyringInfo(
                $keyringName,
                $this->getDataFromResponse($response)
            );
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new \Exception(
                $e->getResponse()->getBody()->getContents(),
                1477426036,
                $e
            );
        }
    }
    
    /**
     * @param string $apiName The code name of the API in question.
     * @return boolean
     */
    public function deleteApi($apiName)
    {
        $response = $this->api()->delete(['id' => $apiName]);
        return $this->getDataFromResponse($response);
    }
    
    /**
     * @param string $keyValue
     * @return boolean
     */
    public function deleteKey($keyValue)
    {
        try {
            $response = $this->key()->delete(['id' => $keyValue]);
            return $this->getDataFromResponse($response);
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                throw new NotFoundException(sprintf(
                    'That key (%s%s) was not found.',
                    substr($keyValue, 0, 12),
                    ((strlen($keyValue) > 12) ? '...' : '')
                ), 1477584689, $e);
            }
            throw $e;
        }
    }
    
    /**
     * @param string $keyringName
     * @return boolean
     */
    public function deleteKeyring($keyringName)
    {
        $response = $this->keyring()->delete(['id' => $keyringName]);
        return $this->getDataFromResponse($response);
    }
    
    /**
     * @param string $apiName The code name of the API in question.
     * @return ApiInfo
     */
    public function getApiInfo($apiName)
    {
        $response = $this->api()->get(['id' => $apiName]);
        return new ApiInfo(
            $apiName,
            $this->getDataFromResponse($response)
        );
    }
    
    /**
     * Get usage statistics for the specified API.
     * 
     * @param string $apiName The code name of the API in question.
     * @param integer $timeStart A Unix timestamp.
     * @param string $granularity The desired granularity (e.g. - 'second',
     *     'minute', 'hour', or 'day').
     * @return array The stats data.
     */
    public function getApiStats($apiName, $timeStart, $granularity)
    {
        $response = $this->api()->getStats([
            'id' => $apiName,
            'from' => $timeStart,
            'granularity' => $granularity,
            'format_timeseries' => false,
        ]);
        return $this->getDataFromResponse($response);
    }
    
    protected function getDataFromResponse($response)
    {
        $statusCode = $response['statusCode'];
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new \Exception(sprintf(
                'Unexpected status code (%s) in response: %s',
                $statusCode,
                var_export($response, true)
            ), 1477334316);
        }
        return $response['results'];
    }
    
    /**
     * @param string $keyValue
     * @return KeyInfo
     */
    public function getKeyInfo($keyValue)
    {
        $response = $this->key()->get(['id' => $keyValue]);
        return new KeyInfo(
            $keyValue,
            $this->getDataFromResponse($response)
        );
    }
    
    /**
     * Get usage statistics for the specified key.
     * 
     * @param string $keyValue The value of the Key whose stats are desired.
     * @param integer $timeStart A Unix timestamp.
     * @param string $granularity The desired granularity (e.g. - 'second',
     *     'minute', 'hour', or 'day').
     * @return array The stats data.
     */
    public function getKeyStats($keyValue, $timeStart, $granularity)
    {
        $response = $this->key()->getStats([
            'id' => $keyValue,
            'from' => $timeStart,
            'granularity' => $granularity,
            'format_timeseries' => false,
        ]);
        return $this->getDataFromResponse($response);
    }
    
    /**
     * @param string $keyringName
     * @return bool
     * @throws \Exception
     */
    public function keyringExists($keyringName)
    {
        try {
            $this->keyring()->get(['id' => $keyringName]);
            return true;
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                return false;
            }
            throw $e;
        }
    }
    
    /**
     * Link the specified key with the specified API (in ApiAxle).
     * 
     * @param string $keyValue The value of the key to link.
     * @param string $apiName The code name of the API to link it to.
     */
    public function linkKeyToApi($keyValue, $apiName)
    {
        try {
            $this->api()->linkKey([
                'id' => $apiName,
                'key' => $keyValue,
            ]);
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                throw new NotFoundException(sprintf(
                    'Either the key (%s) or the API (%s) was not found.',
                    $keyValue,
                    $apiName
                ), 1477428053, $e);
            }
            throw $e;
        }
    }
    
    /**
     * Link the specified key with the specified keyring (in ApiAxle).
     * 
     * @param string $keyValue
     * @param string $keyringName
     * @throws NotFoundException
     * @throws \Exception
     */
    public function linkKeyToKeyring($keyValue, $keyringName)
    {
        try {
            $this->keyring()->linkKey([
                'id' => $keyringName,
                'key' => $keyValue,
            ]);
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                throw new NotFoundException(sprintf(
                    'Either the key (%s) or the keyring (%s) was not found.',
                    $keyValue,
                    $keyringName
                ), 1477419154, $e);
            }
            throw $e;
        }
    }
    
    /**
     * Get a list of existing APIs.
     * 
     * @param int $fromIndex Integer for the index of the first API you want to
     *     see. Starts at zero.
     * @param int $toIndex Integer for the index of the last API you want to
     *     see. Starts at zero.
     * @return string[] The list of API code names.
     */
    public function listApis($fromIndex, $toIndex)
    {
        $response = $this->api()->list([
            'from' => $fromIndex,
            'to' => $toIndex,
        ]);
        return $this->getDataFromResponse($response);
    }
    
    /**
     * Get a list of existing keys.
     * 
     * @param int $fromIndex Integer for the index of the first API you want to
     *     see. Starts at zero.
     * @param int $toIndex Integer for the index of the last API you want to
     *     see. Starts at zero.
     * @return string[] The list of key values (aka. key identifiers).
     */
    public function listKeys($fromIndex, $toIndex)
    {
        $response = $this->key()->list([
            'from' => $fromIndex,
            'to' => $toIndex,
        ]);
        return $this->getDataFromResponse($response);
    }
    
    /**
     * Get a list of existing keys for the specified API.
     * 
     * @param string $apiName The code name of the API whose keys are desired.
     * @param int $fromIndex Integer for the index of the first API you want to
     *     see. Starts at zero.
     * @param int $toIndex Integer for the index of the last API you want to
     *     see. Starts at zero.
     * @return string[] The list of key values (aka. key identifiers).
     */
    public function listKeysForApi($apiName, $fromIndex = 0, $toIndex = 100)
    {
        $response = $this->api()->listKeys([
            'id' => $apiName,
            'from' => $fromIndex,
            'to' => $toIndex,
        ]);
        return $this->getDataFromResponse($response);
    }
    
    /**
     * @param string $apiName
     * @param array $data
     * @return ApiInfo
     * @throws NotFoundException
     * @throws \Exception
     */
    public function updateApi($apiName, $data)
    {
        try {
            $data['id'] = $apiName;
            $response = $this->api()->update($data);
            $responseData = $this->getDataFromResponse($response);
            return new ApiInfo(
                $apiName,
                $responseData['new']
            );
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                throw new NotFoundException(sprintf(
                    'Api "%s" not found.',
                    $apiName
                ), 1477414149, $e);
            }
            throw $e;
        }
    }
    
    /**
     * @param string $keyValue
     * @param array $data
     * @return KeyInfo
     * @throws NotFoundException
     * @throws \Exception
     */
    public function updateKey($keyValue, $data)
    {
        try {
            $data['id'] = $keyValue;
            $response = $this->key()->update($data);
            $responseData = $this->getDataFromResponse($response);
            return new KeyInfo(
                $keyValue,
                $responseData['new']
            );
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                throw new NotFoundException(sprintf(
                    'Key "%s" not found.',
                    $keyValue
                ), 1477419988, $e);
            }
            throw $e;
        }
    }
}