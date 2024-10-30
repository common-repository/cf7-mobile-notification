<?php

namespace Cf7MobileNotification\Hooks;


use Cf7MobileNotification\Api\RestAPI;
use Cf7MobileNotification\Constants\Constants;
use Cf7MobileNotification\Updater\Installer;
use Cf7MobileNotification\Repository\DataRepository;
use Cf7MobileNotification\Settings\PluginSettings;


class Hooks
{

	/**
	 * @var Hooks
	 */
	private static $_instance = null;

	/**
	 * Utils constructor.
	 */
	private function __construct()
	{

	}

	/**
	 * @return Hooks
	 */
	public static function getInstance()
	{
		if (!Hooks::$_instance)
			Hooks::$_instance = new Hooks();

		return Hooks::$_instance;
	}

	public function loadHook()
	{
		add_action('plugins_loaded', array(Installer::getInstance(), 'update_db_check'));
		add_action("wpcf7_before_send_mail", array(DataRepository::getInstance(), "before_send_mail"));

		add_action('admin_init', array(PluginSettings::getInstance(), 'register_settings'));
		add_action('admin_init', array(PluginSettings::getInstance(), 'redirect_after_activation'));
		add_action('admin_menu', array(PluginSettings::getInstance(), 'register_options_page'));

		add_action('save_post', array(DataRepository::getInstance(), 'onNewFormAdded'), 10, 3);

		add_filter('plugin_action_links_' . Constants::PLUGIN_NAME . "/" . Constants::PLUGIN_NAME . ".php", array(PluginSettings::getInstance(), 'register_settings_link'));
		add_action('rest_api_init', function () {

			/************* IP Hooks ************/

			register_rest_route(
				Constants::API_NAMESPACE . "/" . Constants::API_VERSION,
				'/' . Constants::API_ROUTE_STORE_DEVICE_TOKEN,
				array(
					'methods' => 'POST',
					'callback' => array(RestAPI::getInstance(), Constants::API_FUNCTION_STORE_DEVICE_TOKEN),
				));

			register_rest_route(
				Constants::API_NAMESPACE . "/" . Constants::API_VERSION,
				'/' . Constants::API_ROUTE_REMOVE_DEVICE_TOKEN,
				array(
					'methods' => 'POST',
					'callback' => array(RestAPI::getInstance(), Constants::API_FUNCTION_REMOVE_DEVICE_TOKEN),
				));

			register_rest_route(
				Constants::API_NAMESPACE . "/" . Constants::API_VERSION,
				'/' . Constants::API_ROUTE_GET_FORMS,
				array(
					'methods' => 'POST',
					'callback' => array(RestAPI::getInstance(), Constants::API_FUNCTION_GET_FORMS),
				));

			register_rest_route(
				Constants::API_NAMESPACE . "/" . Constants::API_VERSION,
				'/' . Constants::API_ROUTE_GET_FORM_DATA,
				array(
					'methods' => 'POST',
					'callback' => array(RestAPI::getInstance(), Constants::API_FUNCTION_GET_FORM_DATA),
				));

			register_rest_route(
				Constants::API_NAMESPACE . "/" . Constants::API_VERSION,
				'/' . Constants::API_ROUTE_ENABLE_NOTIFICATION,
				array(
					'methods' => 'POST',
					'callback' => array(RestAPI::getInstance(), Constants::API_FUNCTION_ENABLE_NOTIFICATION),
				));

			register_rest_route(
				Constants::API_NAMESPACE . "/" . Constants::API_VERSION,
				'/' . Constants::API_ROUTE_DISABLE_NOTIFICATION,
				array(
					'methods' => 'POST',
					'callback' => array(RestAPI::getInstance(), Constants::API_FUNCTION_DISABLE_NOTIFICATION),
				));

			register_rest_route(
				Constants::API_NAMESPACE . "/" . Constants::API_VERSION,
				'/' . Constants::API_ROUTE_SET_READ_STATE,
				array(
					'methods' => 'POST',
					'callback' => array(RestAPI::getInstance(), Constants::API_FUNCTION_SET_READ_STATE),
				));

			register_rest_route(
				Constants::API_NAMESPACE . "/" . Constants::API_VERSION,
				'/' . Constants::API_ROUTE_SET_CONTACTED_STATE,
				array(
					'methods' => 'POST',
					'callback' => array(RestAPI::getInstance(), Constants::API_FUNCTION_SET_CONTACTED_STATE),
				));

			register_rest_route(
				Constants::API_NAMESPACE . "/" . Constants::API_VERSION,
				'/' . Constants::API_ROUTE_GET_SINGLE_FORM,
				array(
					'methods' => 'POST',
					'callback' => array(RestAPI::getInstance(), Constants::API_FUNCTION_GET_SINGLE_FORM),
				));

			register_rest_route(
				Constants::API_NAMESPACE . "/" . Constants::API_VERSION,
				'/' . Constants::API_ROUTE_GET_SINGLE_RECORD,
				array(
					'methods' => 'POST',
					'callback' => array(RestAPI::getInstance(), Constants::API_FUNCTION_GET_SINGLE_RECORD),
				));

			register_rest_route(
				Constants::API_NAMESPACE . "/" . Constants::API_VERSION,
				'/' . Constants::API_ROUTE_GET_MESSAGE_NOTIFICATIONS,
				array(
					'methods' => 'POST',
					'callback' => array(RestAPI::getInstance(), Constants::API_FUNCTION_GET_MESSAGE_NOTIFICATIONS),
				));

			register_rest_route(
				Constants::API_NAMESPACE . "/" . Constants::API_VERSION,
				'/' . Constants::API_ROUTE_REMOVE_MESSAGE_NOTIFICATIONS,
				array(
					'methods' => 'POST',
					'callback' => array(RestAPI::getInstance(), Constants::API_FUNCTION_REMOVE_MESSAGE_NOTIFICATIONS),
				));
		});
	}

}
