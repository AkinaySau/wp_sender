<?php
/**
 * Plugin Name: Sender
 * Plugin URI: http://a-sau.ru
 * Description: Плагин для отправки заявок. E-mail на который будет
 * отправляться почта хранится в мета поле mail-for-send
 * Version: 1.0.1
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
		add_action( 'wp_ajax_nopriv_' . self::ACTION, [
			__CLASS__,
			'sendMail',
		] );
	}

	/**
	 * Вывод доп поля в админке для email
	 */
	private static final function addOption() {
		add_action( 'admin_menu', function () {
			register_setting( 'general', self::OPTION_NAME );

			// добавляем поле
			add_settings_field( 'sau' . self::OPTION_NAME, __( 'Email', self::OPTION_THEME_LANG ), function ( $val ) {
				$id = $val['id'];
				echo '
					<input
						class="regular-text ltr"
						type="email"
						required
						name="' . self::OPTION_NAME . '"
						id="' . $id . '"
						value="' . esc_attr( get_option( self::OPTION_NAME ) ) . '"
					/>
					<p class="description">' . __( 'For request (action for ajax "sau_send_mail")', self::OPTION_THEME_LANG ) . '</p>
					';
			}, 'general', 'default', array(
				'id'          => 'sau' . self::OPTION_NAME,
				'option_name' => self::OPTION_NAME,
			) );
		} );
	}

	/**
	 * Подключение перевода
	 */
	private static final function addTranslate() {
		add_action( 'init', function () {
			load_plugin_textdomain( self::OPTION_THEME_LANG, false, dirname( plugin_basename( __FILE__ ) ) . '/l10n' );
		} );
	}

	/**
	 * Добавление параметров на страницу
	 */
	private static function addData() {
		$variables = array(
			'sau_sender_ajax_url' => admin_url( 'admin-ajax.php' ) . '?action=' . self::ACTION,
			'is_mobile'           => wp_is_mobile(),
		);
		/**
		 * Обработка данных для генерации переменных в js на странице
		 * todo: нужен фильтр
		 */
//		do_action( 'sau_sender_page_data', $variables );

		add_action( 'wp_head', function () use ( $variables ) {
			if ( is_array( $variables ) ) {
				echo '<script type="text/javascript">window.wp_data = ', json_encode( $variables ), ';</script>';
			}
		} );

	}

	private static function setAdd() {
		self::$data = $_POST;

		if ( isset(self::$data['formData']) && is_string( self::$data['formData'] ) ) {
			self::$data['formData'] = json_decode( self::$data['formData'] );
		}

		/**
		 * Событие для обработки пришедших данных
		 * TODO: нужен ещё и фильтр
		 */
		//		do_action( 'sau_sender_data_send', self::$data );
	}

	/**
	 * Обработка ajax
	 */
	public static function sendMail() {
		/**
		 * Событие начала генерации отправки письма
		 */
		do_action( 'sau_sender_before_send_mail', self::$data );

		$data = self::$data;

		$email   = get_option( self::OPTION_NAME, get_option( 'admin_email', '' ) );
		$subject = ( $data['thm'] ?? __( 'Form', self::OPTION_THEME_LANG ) ) . " " . __( 'from the website', self::OPTION_THEME_LANG ) . " " . $_SERVER['SERVER_NAME'];
		if ( empty( $email ) ) {
			wp_send_json_error( __( 'Empty email', self::OPTION_THEME_LANG ) );
			wp_die();
		}
		$msg = '<table>';

		$msg .= self::getRowsO( $data['formData'] );
		$msg .= self::getRowsA( $data['formData'] );

		$msg .= '</table>';

		if ( wp_mail( $email, $subject, $msg, 'Content-type: text/html;' ) ) {
			/**
			 * Событие после удачной отправки письма
			 */
			do_action( 'sau_sender_after_success_send_mail', self::$data );

			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
		wp_die();
	}

	protected static function getRowsO( $data ) {
		$rows = '';
		if ( count( $data ) && is_object( $data ) ) {
			foreach ( $data as $key => $v ) {
				$rows .= "<tr><td>" . ( $v->title ?? $key ) . ": </td><td>{$v->value}</td></tr>";
			}
		}

		return $rows;
	}

	protected static function getRowsA( $data ) {
		$rows = '';
		if ( count( $data ) && is_array( $data ) ) {
			foreach ( $data as $key => $v ) {
				$rows .= "<tr><td>" . ( $v['title'] ?? $key ) . ": </td><td>{$v['value']}</td></tr>";
			}
		}

		return $rows;
	}
}
