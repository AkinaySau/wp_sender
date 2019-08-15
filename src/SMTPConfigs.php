<?php

namespace Sau\WP\Plugin\SendMail;

use Sau\WP\SettingsAPI\Fields\EmailField;
use Sau\WP\SettingsAPI\Fields\PageListField;
use Sau\WP\SettingsAPI\Fields\PasswordField;
use Sau\WP\SettingsAPI\Fields\SelectField;
use Sau\WP\SettingsAPI\Fields\SettingsField;
use Sau\WP\SettingsAPI\Fields\TextField;
use Sau\WP\SettingsAPI\SettingsGroup;

class SMTPConfigs
{

    private $smtp_encryption;
    private $smtp_host;
    private $smtp_port;
    private $smtp_user;
    private $smtp_password;
    private $smtp_from_mail;
    private $smtp_from_name;
    private $smtp_to;


    public function registerOptions()
    {
        $pages = new SettingsGroup('send_mail', 'Custom page', 'general',null);
        foreach ($this->getFields() as $field) {
            $pages->addField($field);
        }
    }

    /**
     * @return SettingsField[]
     */
    public function getFields(): array
    {
        return [
            new TextField(
                'smtp_host',
                __('Host', SEND_MAIL_TRANSLATE_DOMAIN),
                null,
                sprintf(__('Yandex default value: %s', SEND_MAIL_TRANSLATE_DOMAIN), 'smtp.yandex.ru')
            ),


            new TextField(
                'smtp_port',
                __('Port', SEND_MAIL_TRANSLATE_DOMAIN),
                null,
                sprintf(__('Yandex default value: %s', SEND_MAIL_TRANSLATE_DOMAIN), '465')
            ),
            new SelectField(
                'smtp_encryption', __('Encryption', SEND_MAIL_TRANSLATE_DOMAIN), null, [
                null  => __('No encryption', SEND_MAIL_TRANSLATE_DOMAIN),
                'ssl' => 'SSL',
                'tls' => 'TLS',
            ], null, sprintf(__('Yandex default value: %s', SEND_MAIL_TRANSLATE_DOMAIN), 'SSL')
            ),


            new TextField('smtp_user', __('User', SEND_MAIL_TRANSLATE_DOMAIN)),
            new PasswordField('smtp_password', __('Password', SEND_MAIL_TRANSLATE_DOMAIN)),

            new EmailField('smtp_from_mail', __('From(email)', SEND_MAIL_TRANSLATE_DOMAIN)),
            new EmailField('smtp_from_name', __('From(name)', SEND_MAIL_TRANSLATE_DOMAIN)),
            new EmailField('smtp_to', __('To', SEND_MAIL_TRANSLATE_DOMAIN)),
        ];
    }

    public function getHost()
    {

        if ($this->smtp_host) {
            return $this->smtp_host;
        }

        $this->smtp_host = get_option('smtp_host');

        return $this->smtp_host;
    }

    public function getPort()
    {

        if ($this->smtp_port) {
            return $this->smtp_port;
        }

        $this->smtp_port = get_option('smtp_port');

        return $this->smtp_port;
    }

    public function getEncryption()
    {
        if ($this->smtp_encryption) {
            return $this->smtp_encryption;
        }

        $this->smtp_encryption = get_option('smtp_encryption', null);

        return $this->smtp_encryption;
    }

    public function getUser()
    {
        if ($this->smtp_user) {
            return $this->smtp_user;
        }

        $this->smtp_user = get_option('smtp_user', null);

        return $this->smtp_user;

    }

    public function getPassword()
    {
        if ($this->smtp_password) {
            return $this->smtp_password;
        }

        $this->smtp_password = get_option('smtp_password', null);

        return $this->smtp_password;

    }

    public function getFromMail()
    {
        if ($this->smtp_from_mail) {
            return $this->smtp_from_mail;
        }

        $this->smtp_from_mail = get_option('smtp_from_mail', null);

        return $this->smtp_from_mail;
    }

    public function getFromName()
    {
        if ($this->smtp_from_name) {
            return $this->smtp_from_name;
        }

        $this->smtp_from_name = get_option('smtp_from_name', null);

        return $this->smtp_from_name;
    }

    public function getTo()
    {
        if ($this->smtp_to) {
            return $this->smtp_to;
        }

        $this->smtp_to = get_option('smtp_to', null);

        return $this->smtp_to;
    }
}
