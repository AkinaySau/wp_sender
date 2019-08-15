<?php
/**
 * Plugin Name: SMTP Send mail
 * Plugin URI: http://sauri.pw/
 * Description: The plugin is designed to aggregate mail requests and send them to the specified email addresses
 * Version: 1.0
 * Author: Akinay Sau (Vladimir Mogilev) <akinay.sau@gmail.com>
 * Author URI: http://sauri.pw/
 * License: A "Slug" license name e.g. GPL2
 * Text Domain: send-mail
 * Domain Path: /l10n
 */

########################################################################################################################
use Sau\WP\Plugin\SendMail\Mailer;
use Sau\WP\Plugin\SendMail\SMTPConfigs;

include_once __DIR__.'/vendor/autoload.php';

define('SEND_MAIL_TRANSLATE_DOMAIN', 'send-mail');
define('SEND_MAIL_PLUGIN_DIR', __DIR__);
define('SEND_MAIL_REST_NAMESPACE', '/sau/send-mail/v1/');

__('SMTP Send mail');
__('The plugin is designed to aggregate mail requests and send them to the specified email addresses');

########################################################################################################################


//Enable Translate
add_action(
    'plugins_loaded',
    function () {
        load_plugin_textdomain(SEND_MAIL_TRANSLATE_DOMAIN, false, dirname(plugin_basename(__FILE__)).'/l10n/');
    }
);

$configs = new SMTPConfigs();
add_action(
    'init',
    function () use ($configs) {
        //Options registered
        $configs->registerOptions();
        //js variable for render in page header
        add_action(
            'wp_head',
            function () {
                $url = get_rest_url(null, SEND_MAIL_REST_NAMESPACE).'send';
                printf('<script type="text/javascript">window.send_mail_url = \'%s\';</script>', $url);
            }
        );

    }
);
add_action(
    'rest_api_init',
    function () use ($configs) {
        register_rest_route(
            SEND_MAIL_REST_NAMESPACE,
            'send',
            [
                [
                    'methods'  => WP_REST_Server::EDITABLE,
                    'callback' => function (WP_REST_Request $request) use ($configs) {
                        try {
                            $mailer = new Mailer($configs);
                            $data   = [
                                'request' => $request->get_body_params(),
                            ];
                            $mailer->send($request);

                            return new WP_REST_Response($data, 200);
                        } catch (Exception $exception) {
                            return new WP_REST_Response(
                                [
                                    'error' => $exception->getMessage(),
                                    'trace' => $exception->getTrace(),
                                ], 400
                            );

                        }

                    },
                ],
            ]
        );

    }
);


