<?php

namespace Cf7MobileNotification\Settings;


use Cf7MobileNotification\Constants\Constants;
use Cf7MobileNotification\Updater\Installer;

class PluginSettings
{

	/**
	 * @var PluginSettings
	 */
	private static $_instance = null;


	/**
	 * DataRepository constructor.
	 */
	private function __construct()
	{

	}

	/**
	 * @return PluginSettings
	 */
	public static function getInstance()
	{
		if (!PluginSettings::$_instance) {
			PluginSettings::$_instance = new PluginSettings();
		}

		return PluginSettings::$_instance;
	}

	function redirect_after_activation()
	{
		if (get_option(Constants::OPTION_ACTIVATION_REDIRECT, false)) {
			delete_option(Constants::OPTION_ACTIVATION_REDIRECT);
			$option_url = admin_url('options-general.php?page=cf7_mobile_notification');
			wp_redirect($option_url);
		}
	}

	public function register_settings()
	{
		add_option(Constants::PLUGIN_CODE . "_settings", __("Plugin Options", Constants::PLUGIN_LANGUAGE_DOMAIN));
		register_setting(Constants::PLUGIN_CODE . "_options_group", Constants::PLUGIN_CODE . "_settings");
	}

	public function register_options_page()
	{
		add_options_page(__("CF7 Mobile App Options", Constants::PLUGIN_LANGUAGE_DOMAIN), 'CF7 Mobile App', 'manage_options', Constants::PLUGIN_CODE, array($this, 'option_page'));
	}

	public function register_settings_link($links)
	{
		$option_url = admin_url('options-general.php?page=cf7_mobile_notification');
		$settings_link = "<a href='$option_url'>Settings</a>";
		array_unshift($links, $settings_link);
		return $links;
	}

	/**
	 * Function that shows the setting page. Displays the QR code to be framed with the smartphone.
	 * It also allows you to generate a new secret code that will be replaced by the previous one.
	 */
	public function option_page()
	{
		$url = get_site_url();

		if (isset($_GET["renew"])) {
			global $wpdb;
			update_option(Constants::OPTION_SECRET_KEY, Installer::getInstance()->generate_string());
			//Removes all device token
			$table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICES;
			$wpdb->query("DELETE FROM $table_name WHERE 1;");

			$table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICES_FORMS;
			$wpdb->query("DELETE FROM $table_name WHERE 1;");

			//Removes all device notifications
			$table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICE_NOTIFICATION;
			$wpdb->query("DELETE FROM $table_name WHERE 1;");
		}

		$secret = get_option(Constants::OPTION_SECRET_KEY);

		$obj = new \StdClass();
		$obj->url = $url;
		$obj->secret = $secret;

		echo "<div style='display: flex;width: 100%;flex-direction: column;align-items: center;'>";
		echo "<h1 style='margin-top:40px'>" . __("CF7 Mobile App", Constants::PLUGIN_LANGUAGE_DOMAIN) . "</h1>";
		echo "<p style='font-size: 16px;margin-top: 40px; text-align: center'>" . __("Frame the QR code with your smartphone to add this site to the <br><strong>\"CF7 Database & Contact Manager for Wordpress\"</strong> App.", Constants::PLUGIN_LANGUAGE_DOMAIN) . "</p>";
		echo "<a target='_blank' href='https://play.google.com/store/apps/details?id=com.dacalleg.wpcontactmanagement'><img src='https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png' style='width: 250px;margin-bottom:40px'></a>";
		echo "<img src='https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . json_encode($obj) . "&choe=UTF-8'>";
		echo "<div style='margin-top: 20px'>".$secret."</div>";
		echo "<div style='margin-top:30px'><a href='" . admin_url('options-general.php?page=cf7_mobile_notification&renew=true') . "'>" . __("Replace API Key With New One", Constants::PLUGIN_LANGUAGE_DOMAIN) . "</a></div>";
		echo "</div>";
	}

}
