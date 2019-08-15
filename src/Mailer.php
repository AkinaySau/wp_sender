<?php


namespace Sau\WP\Plugin\SendMail;


use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use Swift_Transport;
use WP_REST_Request;

class Mailer
{
    /**
     * @var SMTPConfigs
     */
    private $configs;
    private $data;

    public function __construct(SMTPConfigs $configs)
    {
        $this->configs = $configs;
    }

    public function send(WP_REST_Request $data)
    {
        $this->data = $data;
        $transport  = $this->getTransport();

        $mailer = new Swift_Mailer($transport);

        $message = $this->getMessage();
        $result  = $mailer->send($message);

        return $result;
    }

    /**
     * @return Swift_Transport
     */
    private function getTransport(): Swift_Transport
    {
        $options = array(
            "ssl" => array(
                "verify_peer"      => false,
                "verify_peer_name" => false,
            ),
        );

        $host       = $this->configs->getHost();
        $port       = $this->configs->getPort();
        $encryption = $this->configs->getEncryption();

        $transport = new Swift_SmtpTransport($host, $port ?: 25, $encryption ?: null);
        $transport->setUsername($this->configs->getUser())
                  ->setPassword($this->configs->getPassword())
                  ->setStreamOptions($options);

        return $transport;
    }

    /**
     * @return Swift_Message
     */
    public function getMessage(): Swift_Message
    {
        $subject = sprintf(
            '%1$s %2$s',
            __('The message from the website:', SEND_MAIL_TRANSLATE_DOMAIN),
            $_SERVER[ 'HTTP_HOST' ]
        );
        $subject = apply_filters('sau_send_mail__subject_messages', $subject);

        $message = new Swift_Message($subject);
        $message->setFrom($this->configs->getFromMail(), $this->configs->getFromName());

        $bodyMessages = $this->getBodyMessages();
        $bodyMessages = apply_filters('sau_send_mail__body_messages', $bodyMessages, $this->data);

        $message->setTo($this->configs->getTo())
                ->setBody($bodyMessages, 'text/html', 'utf-8');

        return $message;
    }

    public function getBodyMessages(): string
    {
        $body = 'Тестовое сообщение';

        return $body;
    }
}
