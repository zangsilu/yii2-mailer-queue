<?php

namespace zangsilu\mailerqueue;

use Swift_Mime_Attachment;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class Message
 *
 * @package zangsilu\mailerqueue
 */
class Message extends \yii\swiftmailer\Message
{
    /**
     * 将邮件加入队列
     * @return int
     * @throws InvalidConfigException
     */
    public function queue()
    {
        /**
         * 判断redis服务器是否可以使用
         * @var $redis \Redis
         */
        $redis = Yii::$app->redis;
        if(empty($redis)){
            throw new InvalidConfigException('redis not found in config.');
        }

        $mailer = Yii::$app->mailer;

        $redisDB = empty($mailer->redisDB) ? 1 : $mailer->redisDB;
        $redis->select($redisDB);
        if (empty($mailer)){
            throw new InvalidConfigException('mailer not found in config.');
        }

        /**
         * 重新组装message
         */
        $message = [];
        $message['from'] = array_keys($this->from);
        $message['to'] = array_keys($this->getTo());
        $message['cc'] = array_keys(empty($this->getCc()) ? [] : $this->getCc());
        $message['bcc'] = array_keys(empty($this->getBcc()) ? [] : $this->getBcc());
        $message['reply_to'] = array_keys(empty($this->getReplyTo()) ? [] : $this->getReplyTo());
        $message['charset'] = array_keys(empty($this->getCharset()) ? [] : $this->getCharset());
        $message['subject'] = is_array($this->getSubject()) ? array_keys($this->getSubject()) :$this->getSubject();
        $parts = $this->getSwiftMessage()->getChildren();
        if (!is_array($parts) || !sizeof($parts)) {
            $parts = [$this->getSwiftMessage()];
        }
        foreach ($parts as $part) {
            if (!$part instanceof Swift_Mime_Attachment) {
                switch($part->getContentType()) {
                    case 'text/html':
                        $message['html_body'] = $part->getBody();
                        break;
                    case 'text/plain':
                        $message['text_body'] = $part->getBody();
                        break;
                }
                if (!$message['charset']) {
                    $message['charset'] = $part->getCharset();
                }
            }
        }
        return $redis->rpush('mailerMessage', json_encode($message));


    }
}