<?php

namespace Cf7MobileNotification\Updater;


use Cf7MobileNotification\Constants\Constants;

class Installer
{

	/**
	 * @var Installer
	 */
	private static $_instance = null;

	/**
	 * Utils constructor.
	 */
	private function __construct()
	{

	}

	/**
	 * Helper function that generates a random string, used to generate a api key for the site
	 * @param int $strength
	 * @return string
	 */
	public function generate_string($strength = 16)
	{
		$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$input_length = strlen($permitted_chars);
		$random_string = '';
		for ($i = 0; $i < $strength; $i++) {
			$random_character = $permitted_chars[mt_rand(0, $input_length - 1)];
			$random_string .= $random_character;
		}

		return $random_string;
	}

	/**
	 * @return Installer
	 */
	public static function getInstance()
	{
		if (!Installer::$_instance)
			Installer::$_instance = new Installer();

		return Installer::$_instance;
	}

	/**
	 * Function that verifies the presence of updates in the db and generates the secret key if not present
	 */
	public function update_db_check()
	{
		$current_db_version = get_option(Constants::OPTION_DB_VERSION);
		if (Constants::DATABASE_VERSION != $current_db_version) {
			$this->install();
			update_option(Constants::OPTION_DB_VERSION, Constants::DATABASE_VERSION);
		}
		$secret = get_option(Constants::OPTION_SECRET_KEY);
		if (!$secret)
			update_option(Constants::OPTION_SECRET_KEY, $this->generate_string());
	}

	/**
	 * Function that creates or update database tables
	 */
	public function install()
	{
		global $wpdb;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$charset_collate = $wpdb->get_charset_collate();

		$table_name = $wpdb->prefix . Constants::SQL_TABLE_FORMS_DATA;

		$sql = "CREATE TABLE $table_name (
			`id` VARCHAR(100) NOT NULL,
			`submission_date` DATETIME,
			`form_id` INT NOT NULL,
			`subject` VARCHAR(300) NOT NULL,
			`message` TEXT NOT NULL,
			`data` TEXT NOT NULL,
			`read` TINYINT(1),
			`contacted` TINYINT(1),
			KEY `form_index` (`form_id`) USING BTREE,
			PRIMARY KEY (`id`)
		); $charset_collate;";

		dbDelta($sql);

		$table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICES;

		$sql = "CREATE TABLE $table_name (
			`id` VARCHAR(100) NOT NULL,
			`token` VARCHAR(300) NOT NULL,
			KEY `token_index` (`token`) USING BTREE,
			PRIMARY KEY (`id`)
		); $charset_collate;";

		dbDelta($sql);

		$table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICES_FORMS;

		$sql = "CREATE TABLE $table_name (
			`device` VARCHAR(100) NOT NULL,
			`form` INT NOT NULL,
			PRIMARY KEY (`device`, `form`)
		); $charset_collate;";

		dbDelta($sql);
		$table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICE_NOTIFICATION;

		$sql = "CREATE TABLE $table_name (
			`device` VARCHAR(100) NOT NULL,
			`message` VARCHAR(100) NOT NULL,
			PRIMARY KEY (`device`, `message`)
		); $charset_collate;";

		dbDelta($sql);
	}


}
