<?php

namespace zangsilu\mailerqueue;


use Redis;
use Yii;
use yii\base\InvalidConfigException;
use yii\swiftmailer\Mailer;
use yii\web\ServerErrorHttpException;

class MailerQueue extends Mailer
{
    public $messageClass = 'zangsilu\mailerqueue\Message';
    public $key = 'mailerMessage';
    public $redisDB = 1;
    public $aa = 1;


    /**
     * 发送队列中的邮件
     * @return bool
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     */
    public function process()
    {
        /**
         * @var $redis Redis
         */
        $redis = Yii::$app->redis;
        if (empty($redis)) {
            throw new InvalidConfigException('redis not found in config.');
        }
        if ($redis->select($this->redisDB) && $messages = $redis->lrange($this->key, 0, -1)) {
            $messageObj = new Message;
            foreach ($messages as $message) {
                $message = json_decode($message, true);

                if (empty($message) || !$this->setMessage($messageObj, $message)) {
                    throw new ServerErrorHttpException('message error');
                }
                if ($messageObj->send()) {
                    $redis->lrem($this->key,1,  json_encode($message));
                }
            }
        }
        return true;
    }

    /**
     * 给Message对象赋值
     * @param $messageObj
     * @param $message
     * @var $messageObj Message
     *
     * @return bool
     */
    public function setMessage($messageObj, $message)
    {
        if (empty($messageObj)) {
            return false;
        }
        if (!empty($message['from']) && !empty($message['to'])) {
            $messageObj->setFrom($message['from'])->setTo($message['to']);
            if (!empty($message['cc'])) {
                $messageObj->setCc($message['cc']);
            }
            if (!empty($message['bcc'])) {
                $messageObj->setBcc($message['bcc']);
            }
            if (!empty($message['reply_to'])) {
                $messageObj->setReplyTo($message['reply_to']);
            }
            if (!empty($message['charset'])) {
                $messageObj->setCharset($message['charset']);
            }
            if (!empty($message['subject'])) {
                $messageObj->setSubject($message['subject']);
            }
            if (!empty($message['html_body'])) {
                $messageObj->setHtmlBody($message['html_body']);
            }
            if (!empty($message['text_body'])) {
                $messageObj->setTextBody($message['text_body']);
            }
            return $messageObj;
        }
        return false;
    }

}