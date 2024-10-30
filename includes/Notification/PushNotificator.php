<?php

namespace Cf7MobileNotification\Notification;

/**
 * Class that define a notify service for push notification
 * Class PushNotificator
 * @package Cf7MobileNotification\Notification
 */
class PushNotificator extends Notificator
{
	private $devices;

	/**
	 * PushNotification constructor.
	 * @param $devices
	 */
	public function __construct($devices)
	{
		$this->devices = $devices;
	}

	/**
	 * Send a notification via google cloud notification(GCM)
	 * @param $title
	 * @param $message
	 * @param $data
	 * @param $website_url
	 */
	protected function send($title, $message, $data, $website_url)
	{
		$data['tokens'] = $this->devices;

		if(count($data['tokens'])) {
			$args = array(
				'body' => json_encode($data),
				'headers' => array(
					'Content-Type' => 'application/json'
				),
			);
			wp_remote_post('https://us-central1-contact-for-wordpress.cloudfunctions.net/sendNotification', $args);
		}
	}
}
