## 主要用途
使用Laravel 6.x 以上在Cloudflare上批量创建站点、批量删除站点、批量添加DNS记录、快速删除所有DNS记录、修改SSL类型、清除站点所有缓存内容。

## 用法
``` bash
composer require qbcloud/laravel-cloudflare
```

发布配置文件：
``` bash
php artisan vendor:publish --provider="QbCloud\Cloudflare\Providers\CloudflareServiceProvider"
```

```php
// config/cloudflare.php

return [
    'email' => 'xxx@gmail.com', // Cloudflare Username
    'global_key' => 'globalxxoo', // Cloudflare Global API Key
    'account_id' => 'bbxxkk', // Cloudflare Account ID
    'ip_address' => null // 默认记录值  用于添加DNS记录
];
```

```php
use QbCloud\Cloudflare\Facades\GeoIp;

// 批量创建站点
GeoIp::createZones(['xxx.com','xxx.net']);

// 批量删除站点
GeoIp::deleteZones(['xxx.com','xxx.net']);

// 清除站点所有缓存内容
GeoIp::purgeAllCache('xxx.com');

// 批量添加DNS记录
GeoIp::createRecords('xxx.com', ['@', 'www', 'test'], '127.0.0.1', 'A');

// 删除一条DNS记录
GeoIp::deleteRecords('xxx.com', 'test');

// 删除所有DNS记录
GeoIp::deleteAllRecords('xxx.com');

// 修改SSL类型
GeoIp::updateSSLSetting('xxx.com', 'full');

// 或者
use QbCloud\Cloudflare\Cloudflare;

$cloudflare = new GeoIp();
$cloudflare->createZones(['xxx.com','xxx.net']);
```

### 官方文档

[Cloudflare API](https://developers.cloudflare.com/api/operations/zones-get)\
[Cloudflare SDK](https://github.com/cloudflare/cloudflare-php)
