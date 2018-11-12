
## 导出曲子数据脚本

## 功能描述

* **错误日志记录** - 目录错误，歌曲导出错误，上传超时错误，执行异常

* **断点续传** - 上传失败后记录上传断点，继续上传

* **xml 文件转成 .plist**

* **单目录上传** - 单个目录上传 ``-dir="demo/"``

* **横屏和竖屏上传** - 区分横屏和竖屏

* **安卓数据生成** - 安卓端数据生成


##Compose 包
```
"require": {
        "aliyuncs/oss-sdk-php": "dev-master",
        "vlucas/phpdotenv": "^2.5@dev"
}
```

## PHP版本要求

PHP 7.0+ 


## Get Started

### Install via composer

Add Medoo to composer.json configuration file.
```
$ composer require catfan/Medoo
```

And update the composer
```
$ composer update
```

```php
// If you installed via composer, just use this code to require autoloader on the top of your projects.
require 'vendor/autoload.php';

// Using Medoo namespace
use Medoo\Medoo;

// Initialize
$database = new Medoo([
    'database_type' => 'mysql',
    'database_name' => 'name',
    'server' => 'localhost',
    'username' => 'your_username',
    'password' => 'your_password'
]);

// Enjoy
$database->insert('account', [
    'user_name' => 'foo',
    'email' => 'foo@bar.com'
]);

$data = $database->select('account', [
    'user_name',
    'email'
], [
    'user_id' => 50
]);

echo json_encode($data);

// [
//     {
//         "user_name" : "foo",
//         "email" : "foo@bar.com",
//     }
// ]
```

## Contribution Guides

For most of time, Medoo is using develop branch for adding feature and fixing bug, and the branch will be merged into master branch while releasing a public version. For contribution, submit your code to the develop branch, and start a pull request into it.

On develop branch, each commits are started with `[fix]`, `[feature]` or `[update]` tag to indicate the change.

Keep it simple and keep it clear.

## License

Medoo is under the MIT license.

## Links

* Official website: [https://medoo.in](https://medoo.in)

* Documentation: [https://medoo.in/doc](https://medoo.in/doc)