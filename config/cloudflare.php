<?php

/**
 * !!!如果报SSL错误请禁用验证SSL证书
 * \vendor\cloudflare\sdk\src\Adapter\Guzzle.php
 * 84行左右 替换成↓↓↓
 * 'headers' => $headers, 'verify' => false, // 禁用验证SSL证书
 */

return [
    'email' => 'xxx@gmail.com', // Cloudflare Username
    'global_key' => 'globalxxoo', // Cloudflare Global API Key
    'account_id' => 'bbxxkk', // Cloudflare Account ID
    'ip_address' => null // 默认记录值  用于添加DNS记录
];
