<?php
/**
 * Plugin Name: Sender
 * Plugin URI: http://a-sau.ru
 * Description: Плагин для отправки заявок. E-mail на который будет отправляться почта хранится в мета поле mail-for-send
 * Version: 1.0
 * Author: Akinay Sau
 * Author URI: http://a-sau.ru
 * License: MTI
 */


SauSender::init();

class SauSender {
	const OPTION_NAME = 'email_for_send';
	const OPTION_THEME_LANG = 'sau_sender';
	const ACTION = 'sau_send_mail';
	public static $data;

	public static function init() {
		self::addOption();
		self::addTranslate();
		self::addData();
		self::setAdd();
		add_action( 'wp_ajax_' . self::ACTION, [ __CLASS__, 'sendMail' ] );
		add_action( 'wp_ajax_nopriv_' . self::ACTION, [ __CLASS__, 'sendMl' ] );
	}

	/**
	 * Вывод доп поля в админке для email
	 */
	private static final function addOption() {
		add_action(
			'admin_menu', function () {
			register_setting( 'general', self::OPTION_NAME );

			// добавляем поле
			add_settings_field(
				'sau' . self::OPTION_NAME,
				__( 'Email', self::OPTION_THEME_LANG ),
				function ( $val ) {
					$id = $val['id'];
					echo '
					<input
						class="regular-text ltr"
						type="email"
						required="required"
						name="' . self::OPTION_NAME . '"
						id="' . $id . '"
						value="' . esc_attr( get_option( self::OPTION_NAME ) ) . '"
					/>
					<p class="description">' . __(
							'For request (action for "ajax sau_send_mail")', self::OPTION_THEME_LANG
						) . '</p>
					';
				},
				'general',
				'default',
				array(
					'id'          => 'sau' . self::OPTION_NAME,
					'option_name' => self::OPTION_NAME
				)
			);
		}
		);
	}

	/**
	 * Подключение перевода
	 */
	private static final function addTranslate() {
		add_action(
			'init', function () {
			load_plugin_textdomain( self::OPTION_THEME_LANG, false, dirname( plugin_basename( __FILE__ ) ) . '/l10n' );
		}
		);
	}

	/**
	 * Добавление параметров на страницу
	 */
	private static function addData() {
		add_action(
			'wp_head', function () {
			$variables = array(
				'sau_sender_ajax_url' => admin_url( 'admin-ajax.php' ) . '?action=' . self::ACTION,
				'is_mobile'           => wp_is_mobile()
			);
			echo '<script type="text/javascript">window.wp_data = ', json_encode( $variables ), ';</script>';
		}
		);

	}

	private static function setAdd() {
		self::$data = $_POST;
	}

	/**
	 * Обработка ajax
	 */
	public static function sendMail() {
		$data    = self::$data;
		$email   = get_option( self::OPTION_NAME, get_option( 'admin_email', '' ) );
		$subject = ( $data['thm']??__( 'Form', self::OPTION_THEME_LANG ) ) . " " . __(
				'from the website', self::OPTION_THEME_LANG
			) . " " . $_SERVER['SERVER_NAME'];
		if ( empty( $email ) ) {
			wp_send_json_error( __( 'Empty email', self::OPTION_THEME_LANG ) );
			wp_die();
		}
		$msg = '<table>';
		if ( count( $data['formData'] ) ) {
			foreach ( $data['formData'] as $v ) {
				$msg .= "<tr><td>{$v['title']}: </td><td>{$v['value']}</td></tr>";
			}
		}
		$msg .= '</table>';

		if ( ! wp_mail( $email, $subject, $msg, 'Content-type: text/html;' ) ) {
			wp_send_json_error();
		} else {
			wp_send_json_success();
		}
		wp_die();
	}
}