<?php

namespace QbCloud\Cloudflare;

use Cloudflare\API\Endpoints\EndpointException;
use QbCloud\Cloudflare\Cloudflare\Api;
use QbCloud\Cloudflare\Support\Helpers;

class Cloudflare extends Api
{
    use Helpers;

    public static $adapter;

    /**
     * 认证
     */
    public function __construct()
    {
        self::$adapter = self::adapter();
    }

    /**
     * 获取用户详情
     * https://developers.cloudflare.com/api/operations/user-user-details
     * @return false|array
     */
    public function getUserDetails()
    {
        $user = self::user(self::$adapter);
        $detail = $user->getUserDetails();
        if (!isset($detail->id)) return false;
        return json_decode(json_encode($detail), true);
    }

    /**
     * 根据域名获取站点ID
     * @param $domain
     * @param null $zone
     * @return string
     * @throws EndpointException
     */
    public function getZoneId($domain, $zone = null): string
    {
        if (!$zone) $zone = self::zone(self::$adapter);
        try {
            return $zone->getZoneID($domain);
        } catch (EndpointException $e) {
            throw new EndpointException('Zone error.');
        }
    }

    /**
     * 获取站点
     * https://developers.cloudflare.com/api/operations/zones-get
     * @param string $domain
     * @param int $page
     * @param int $size
     * @return array
     */
    public function listZones(string $domain = '', int $page = 1, int $size = 20): array
    {
        $page = request('page') ? (int)request('page') : $page;
        $size = request('size') ? (int)request('size') : $size;
        $zone = self::zone(self::$adapter);
        $result = $zone->listZones($domain, '', $page, $size);
        return json_decode(json_encode($result), true);
    }

    /**
     * 批量创建站点
     * https://developers.cloudflare.com/api/operations/zones-post
     * 注意: 添加的站点必须是顶级域名、单次不应超过20个、一小时不应超过100个
     * @param array $domains // 顶级域名
     * @return array
     * @throws EndpointException
     */
    public function createZones(array $domains): array
    {
        if (count($domains) > 20) {
            throw new EndpointException('No more than 20 at a time.');
        }
        $success = $errors = [];
        $zone = self::zone(self::$adapter);
        foreach ($domains as $domain) {
            if (!self::isTopDomain($domain)) {
                $errors[] = ['name' => $domain, 'message' => 'Domain error.'];
                continue;
            }
            try {
                $zone->getZoneID($domain);
                $errors[] = ['name' => $domain, 'message' => 'Domain already exists.'];
                continue;
            } catch (EndpointException $e) {
                // 域名未被添加
            }
            try {
                $result = $zone->addZone($domain, false, config('cloudflare.account_id'));
                $success[] = [
                    'id' => $result->id,
                    'name' => $result->name,
                    'type' => $result->type ?? false,
                    'status' => $result->status ?? false,
                    'name_servers' => $result->name_servers ?: [],
                    'original_name_servers' => $result->original_name_servers ?: [],
                    'created_on' => $result->created_on
                ];
            } catch (\Exception $e) {
                $errors[] = ['name' => $domain, 'message' => $e->getMessage()];
            }
        }
        return ['success' => $success, 'errors' => $errors];
    }

    /**
     * 批量删除站点
     * https://developers.cloudflare.com/api/operations/zones-0-delete
     * @param array $domains // 顶级域名
     * @return array
     */
    public function deleteZones(array $domains): array
    {
        $success = $errors = [];
        $zone = self::zone(self::$adapter);
        foreach ($domains as $domain) {
            if (!self::isTopDomain($domain)) {
                $errors[] = ['name' => $domain, 'message' => 'Domain error.'];
                continue;
            }
            try {
                $zoneId = $zone->getZoneID($domain);
                $result = $zone->deleteZone($zoneId);
                if ($result) {
                    $success[] = ['id' => $zoneId, 'name' => $domain, 'message' => null];
                    continue;
                }
                $errors[] = ['id' => $zoneId, 'name' => $domain, 'message' => 'Unknown error.'];
            } catch (EndpointException $e) {
                $errors[] = ['name' => $domain, 'message' => $e->getMessage()];
            }
        }
        return ['success' => $success, 'errors' => $errors];
    }

    /**
     * 清除站点所有缓存内容
     * https://developers.cloudflare.com/api/operations/zones-0-delete
     * @param string $domain // 顶级域名
     * @return bool
     * @throws EndpointException
     */
    public function purgeAllCache(string $domain): bool
    {
        $zone = self::zone(self::$adapter);
        $zoneId = $this->getZoneId($domain, $zone);
        return $zone->cachePurgeEverything($zoneId);
    }

    /**
     * 获取DNS记录
     * https://developers.cloudflare.com/api/operations/zones-get
     * @param string $domain
     * @param int $page
     * @param int $size
     * @return array
     * @throws EndpointException
     */
    public function listRecords(string $domain, int $page = 1, int $size = 20): array
    {
        $page = request('page') ? (int)request('page') : $page;
        $size = request('size') ? (int)request('size') : $size;
        $dns = self::dns(self::$adapter);
        $zoneId = $this->getZoneId($domain);
        $result = $dns->listRecords($zoneId, '', '', '', $page, $size);
        return json_decode(json_encode($result), true);
    }


    /**
     * 批量创建域名DNS记录
     * https://developers.cloudflare.com/api/operations/dns-records-for-a-zone-create-dns-record
     * 注意：当前只能添加 A、AAAA、CNAME 记录
     * @param string $domain // 顶级域名
     * @param array $prefixes // 主机记录/前缀
     * @param string $address // 记录值/IP地址
     * @param string $type // 记录类型
     * @return array
     * @throws EndpointException
     */
    public function createRecords(string $domain, array $prefixes, string $address = '', string $type = 'A'): array
    {
        if (!self::isTopDomain($domain)) {
            throw new EndpointException('Domain error.');
        }
        if (!in_array($type, ['A', 'AAAA', 'CNAME'])) {
            throw new EndpointException('Type error.');
        }
        $address = $address ?: config('cloudflare.ip_address');
        if (empty($address)) {
            throw new EndpointException('The DNS record value is incorrect.');
        }
        $zoneId = $this->getZoneId($domain);
        $dns = self::dns(self::$adapter);
        $success = $errors = [];
        foreach ($prefixes as $prefix) {
            $name = $prefix !== '@' ? "$prefix.$domain" : '@';
            $result = $dns->addRecord($zoneId, $type, $name, $address);
            $item = ['zone_id' => $zoneId, 'name' => $name, 'type' => $type, 'prefix' => $prefix, 'address' => $address];
            if ($result) {
                $success[] = $item;
                continue;
            }
            $errors[] = $item;
        }
        return ['success' => $success, 'errors' => $errors];
    }


    /**
     * 删除一条DNS记录
     * https://developers.cloudflare.com/api/operations/dns-records-for-a-zone-delete-dns-record
     * @param string $domain // 顶级域名
     * @param string $prefix // 主机记录/前缀
     * @return bool
     * @throws EndpointException
     */
    public function deleteRecords(string $domain, string $prefix = ''): bool
    {
        $name = $prefix ? "$prefix.$domain" : $domain;
        if (!self::isTopDomain($name)) {
            throw new EndpointException('Domain error.');
        }
        $zoneId = $this->getZoneId($domain);
        $dns = self::dns(self::$adapter);
        $recordId = $dns->getRecordID($zoneId, '', $name);
        $dns->deleteRecord($zoneId, $recordId);
        return true;
    }

    /**
     * 删除所有DNS记录
     * @param string $domain // 顶级域名
     * @return bool
     * @throws EndpointException
     */
    public function deleteAllRecords(string $domain): bool
    {
        if (!self::isTopDomain($domain)) {
            throw new EndpointException('Domain error.');
        }
        $zoneId = $this->getZoneId($domain);
        $dns = self::dns(self::$adapter);
        for ($i = 1; $i <= 100; $i++) {
            $result = $dns->listRecords($zoneId);
            $items = $result->result;
            $meta = $result->result_info;
            foreach ($items as $record) {
                $dns->deleteRecord($record->zone_id, $record->id);
            }
            if ($meta->per_page !== $meta->count) {
                break;
            }
        }
        return true;
    }

    /**
     * 获取SSL设置
     * https://developers.cloudflare.com/api/operations/zone-settings-get-ssl-setting
     * SSL Type: off flexible full strict
     * @param $domain // 顶级域名
     * @return string|false
     * @throws EndpointException
     */
    public function getSSLSetting($domain)
    {
        try {
            $zoneId = $this->getZoneId($domain);
            $ssl = self::ssl(self::$adapter);
            return $ssl->getSSLSetting($zoneId);
        } catch (EndpointException $e) {
            throw new EndpointException('Zone error.');
        }
    }

    /**
     * 修改SSL设置
     * https://developers.cloudflare.com/api/operations/zone-settings-change-ssl-setting
     * SSL Type: off flexible full strict
     * @param string $domain // 顶级域名
     * @param string $type // off flexible full strict
     * @return bool
     * @throws EndpointException
     */
    public function updateSSLSetting(string $domain, string $type): bool
    {
        if (!in_array($type, ['off', 'flexible', 'full', 'strict'])) {
            throw new EndpointException('Type error.');
        }
        try {
            $zoneId = $this->getZoneId($domain);
            $ssl = self::ssl(self::$adapter);
            return $ssl->updateSSLSetting($zoneId, $type);
        } catch (EndpointException $e) {
            throw new EndpointException('Zone error.');
        }
    }
}
