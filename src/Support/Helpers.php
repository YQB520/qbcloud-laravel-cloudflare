<?php

namespace QbCloud\Cloudflare\Support;

trait Helpers
{
    /**
     * 常用的域名后缀
     */
    private static $suffix = 'com\.cn|net\.cn|org\.cn|gov\.cn|la|mobi|org|site|top|so|me|tv|hk|com|cn|net|info|xyz|icu|shop|club|cc|vip|ltd|ink|cloud|ren|life|bond|online|fun|tech|xin|space|group|store|live';

    /**
     * 检查是否为正常的域名
     * 是-true 否-false
     * @param string $domain
     * @return bool
     */
    public static function isDomain(string $domain): bool
    {
        $suffix = self::$suffix;
        return (bool)preg_match("/^[a-zA-Z0-9]+\.?[a-zA-Z0-9]+\.($suffix)$/", $domain);
    }

    /**
     * 检查是否为顶级域名
     * 是-true 否-false
     * @param $domain
     * @return bool
     */
    public static function isTopDomain($domain): bool
    {
        $suffix = self::$suffix;
        return (bool)preg_match("/^[a-zA-Z0-9]+\.($suffix)$/", $domain);
    }
}
