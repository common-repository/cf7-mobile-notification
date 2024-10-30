<?php

namespace Cf7MobileNotification\Notification;

/**
 * Abstract class that identifies a generic notifier service
 * Class Notificator
 * @package Cf7MobileNotification\Notification
 */
abstract class Notificator
{

	public function sendNotification($title, $message, $data = array())
	{
		$title = $_SERVER['SERVER_NAME'] . " - " . $title;
		$data["website"] = $_SERVER['SERVER_NAME'];
		$data["title"] = $title;
		$data["message"] = $message;

		$this->send($title, $message, $data, get_site_url());
	}

	protected abstract function send($title, $message, $data, $website_url);
}
