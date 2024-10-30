<?php

namespace Cf7MobileNotification\Notification;

use Cf7MobileNotification\Constants\Constants;
use Cf7MobileNotification\Repository\DataRepository;

class Events
{

	/**
	 * @var Events
	 */
	private static $_instance = null;

	/**
	 * Events constructor.
	 */
	private function __construct()
	{

	}

	/**
	 * @return Events
	 */
	public static function getInstance()
	{
		if (!Events::$_instance) {
			Events::$_instance = new Events();
		}

		return Events::$_instance;
	}

	/**
	 * Launched when a request is sent from a form. Send notifications to the devices that need to receive them
	 * @param $data
	 */
	public function onSubmission($data)
	{
		global $wpdb;
		$form_id = $data['form_id'];
		$table_devices = $wpdb->prefix . Constants::SQL_TABLE_DEVICES;
		$table_form_devices = $wpdb->prefix . Constants::SQL_TABLE_DEVICES_FORMS;

		$tokens = array();
		$results = $wpdb->get_results("SELECT token FROM $table_form_devices DF INNER JOIN $table_devices D ON (D.id = DF.device) WHERE DF.form = $form_id;", ARRAY_A);

		foreach ($results as $row){
			$tokens[] = $row['token'];
		}

		$notificator = new PushNotificator($tokens);
		$notificator->sendNotification($data['title'], $data['subject'], array('form_id' => strval($form_id), 'record_id' => $data['id']));
	}
}
