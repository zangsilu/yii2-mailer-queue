# yii2-mailer-queue


> 使用队列异步发送邮件`v1.0.0`

## 环境

- PHP >= 5.4
- yiisoft/yii2-redis >= 2.0.0
- [composer](https://getcomposer.org/)

## 安装

```shell
composer require zangsilu/yii2-mailer-queue
```

## 使用
- 1：配置文件配置
```php
<?php
'components'   => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '127.0.0.1',
            'port' => 6379,
            'database' => 0,
        ],
        /* 邮件发送设置 */
        'mailer'       => [
            'class'            => 'zangsilu\mailerqueue\MailerQueue',
            'redisDB'=>1,//使用redis1号库存储邮件队列
            'useFileTransport' => false, //必须改为false,true只是生成邮件在runtime文件夹下，不发邮件
            'transport'        => [
                'class'      => 'Swift_SmtpTransport',
                'host'       => 'smtp.163.com',
                'username'   => '',
                'password'   => '',
                'port'       => '465',//端口25对应tls协议 端口465对应ssl协议
                'encryption' => 'ssl',
            ],
        ],
    ];
```
- 2：创建控制台指令
```php
<?php

namespace app\commands;


use Yii;
use yii\console\Controller;

class MailerQueueController extends Controller
{

    /**
     * 发送redis队列中的邮件
     * php yii mailer-queue/send
     */
    public function actionSend()
    {
        $mailer = Yii::$app->mailer;
        $mailer->process();
        echo '发送完毕!';
    }

}
```

- 3：将指令加入lunux定时任务(每分钟检测一次)
```shell
crontab -e
*/1 * * * * php yii mailer-queue/send > ./log/mailer-send.log
```

## 帮助

- 意见、BUG反馈： https://github.com/zangsilu/yii2-mailer-queue/issues

## 支持

- 官方网址： http://blog2.pl39.com/
- composer： https://getcomposer.org/

## License

MIT

