## 主要用途
使用Laravel 6.x 以上在Cloudflare上批量创建站点、批量删除站点、批量添加DNS记录、快速删除所有DNS记录、修改SSL类型、清除站点所有缓存内容。

## 用法
发布配置文件：
``` bash
php artisan vendor:publish --provider="QbCloud\Cloudflare\Providers\CloudflareServiceProvider"
```

```php
return [
    'email' => 'xxx@gmail.com', // Cloudflare Username
    'global_key' => 'globalxxoo', // Cloudflare Global API Key
    'account_id' => 'bbxxkk', // Cloudflare Account ID
    'ip_address' => null // 默认记录值  用于添加DNS记录
];
```
```php
use QbCloud\Cloudflare\Facades\Cloudflare;

// 批量创建站点
Cloudflare::createZones(['xxx.com','xxx.net']);

// 批量删除站点
Cloudflare::deleteZones(['xxx.com','xxx.net']);

// 清除站点所有缓存内容
Cloudflare::purgeAllCache('xxx.com');

// 批量添加DNS记录
Cloudflare::createRecords('xxx.com', ['@', 'www', 'test'], '127.0.0.1', 'A');

// 删除一条DNS记录
Cloudflare::deleteRecords('xxx.com', 'test');

// 删除所有DNS记录
Cloudflare::deleteAllRecords('xxx.com');

// 修改SSL类型
Cloudflare::updateSSLSetting('xxx.com', 'full');
```

### 官方文档

[Cloudflare API](https://developers.cloudflare.com/api/operations/zones-get)\
[Cloudflare SDK](https://github.com/cloudflare/cloudflare-php)
