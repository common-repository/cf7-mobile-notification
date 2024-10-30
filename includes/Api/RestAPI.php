<?php
/**
 * API.php
 * User: Daniele Callegaro <daniele.callegaro.90@gmail.com>
 * Created: 07/07/20
 */

namespace Cf7MobileNotification\Api;


use Cf7MobileNotification\Constants\Constants;
use Cf7MobileNotification\Repository\DataRepository;
use WP_REST_Request;

/**
 * Contains a list of Api Available
 * Class RestAPI
 * @package Cf7MobileNotification\Api
 */
class RestAPI
{
	/**
	 * @var RestAPI
	 */
	private static $_instance = null;


	/**
	 * RestAPI constructor.
	 */
	private function __construct()
	{
	}

	/**
	 * @return RestAPI
	 */
	public static function getInstance()
	{
		if (!RestAPI::$_instance)
			RestAPI::$_instance = new RestAPI();

		return RestAPI::$_instance;
	}

	/**
	 * Stores the token of the device needed to send notifications
	 * @param WP_REST_Request $request
	 * @return \StdClass|\WP_REST_Response
	 */
	public function storeDeviceToken(WP_REST_Request $request)
	{
		if (!$this->validateSecret($request))
			return $this->NotFound404();

		$obj = $this->getJson($request);
		DataRepository::getInstance()->storeDeviceToken($obj->id, $obj->token);

		return $this->OK();
	}

	/**
	 * Removes the token of the device needed to send notifications
	 * @param WP_REST_Request $request
	 * @return \StdClass|\WP_REST_Response
	 */
	public function removeDeviceToken(WP_REST_Request $request)
	{
		if (!$this->validateSecret($request))
			return $this->NotFound404();

		$obj = $this->getJson($request);
		DataRepository::getInstance()->removeDeviceToken($obj->id);

		return $this->OK();
	}


	/**
	 * Returns the website form list in json format
	 * @param WP_REST_Request $request
	 * @return array|\WP_REST_Response
	 */
	public function getForms(WP_REST_Request $request)
	{
		if (!$this->validateSecret($request))
			return $this->NotFound404();

		$obj = $this->getJson($request);
		return DataRepository::getInstance()->getForms($obj->device);
	}

	/**
	 * Returns all saved requests of a form in json format
	 * @param WP_REST_Request $request
	 * @return array|\WP_REST_Response
	 */
	public function getFormData(WP_REST_Request $request)
	{
		if (!$this->validateSecret($request))
			return $this->NotFound404();

		$obj = $this->getJson($request);
		return DataRepository::getInstance()->getFormData($obj->form);
	}

	/**
	 * Enables receiving notifications of a given form for a given device
	 * @param WP_REST_Request $request
	 * @return \StdClass|\WP_REST_Response
	 */
	public function enableNotification(WP_REST_Request $request)
	{
		if (!$this->validateSecret($request))
			return $this->NotFound404();

		$obj = $this->getJson($request);
		DataRepository::getInstance()->enableFormNotification($obj->device, $obj->form);

		return $this->OK();
	}

	/**
	 * Disables receiving notifications of a given form for a given device
	 * @param WP_REST_Request $request
	 * @return \StdClass|\WP_REST_Response
	 */
	public function disableNotification(WP_REST_Request $request)
	{
		if (!$this->validateSecret($request))
			return $this->NotFound404();

		$obj = $this->getJson($request);
		DataRepository::getInstance()->disableFormNotification($obj->device, $obj->form);

		return $this->OK();
	}

	/**
	 * Sets the read/unread status for a given request
	 * @param WP_REST_Request $request
	 * @return \StdClass|\WP_REST_Response
	 */
	public function setReadState(WP_REST_Request $request)
	{
		if (!$this->validateSecret($request))
			return $this->NotFound404();

		$obj = $this->getJson($request);
		DataRepository::getInstance()->setReadState($obj->id, $obj->state);

		return $this->OK();
	}

	/**
	 * Sets the status contacted/not contacted for a given request
	 * @param WP_REST_Request $request
	 * @return \StdClass|\WP_REST_Response
	 */
	public function setContactedState(WP_REST_Request $request)
	{
		if (!$this->validateSecret($request))
			return $this->NotFound404();

		$obj = $this->getJson($request);
		DataRepository::getInstance()->setContactedState($obj->id, $obj->state);

		return $this->OK();
	}


	/**
	 * Returns a single form in json format
	 * @param WP_REST_Request $request
	 * @return \StdClass|\WP_REST_Response|null
	 */
	public function getSingleForm(WP_REST_Request $request)
	{
		if (!$this->validateSecret($request))
			return $this->NotFound404();

		$obj = $this->getJson($request);
		return DataRepository::getInstance()->getSingleForm($obj->device, $obj->form);
	}

	/**
	 * Returns a single request in json format
	 * @param WP_REST_Request $request
	 * @return \StdClass|\WP_REST_Response|null
	 */
	public function getSingleRecord(WP_REST_Request $request)
	{
		if (!$this->validateSecret($request))
			return $this->NotFound404();

		$obj = $this->getJson($request);
		return DataRepository::getInstance()->getSingleRecord($obj->id);
	}

	/**
	 * Returns a list of notification for a given device
	 * @param WP_REST_Request $request
	 * @return array|\WP_REST_Response
	 */
	public function getMessageNotifications(WP_REST_Request $request)
	{
		if (!$this->validateSecret($request))
			return $this->NotFound404();

		$obj = $this->getJson($request);
		return DataRepository::getInstance()->getMessageNotifications($obj->device);
	}

	/**
	 * Remove a notification for a given device
	 * @param WP_REST_Request $request
	 * @return \StdClass|\WP_REST_Response
	 */
	public function removeMessageNotifications(WP_REST_Request $request)
	{
		if (!$this->validateSecret($request))
			return $this->NotFound404();

		$obj = $this->getJson($request);
		DataRepository::getInstance()->removeNotification($obj->message, $obj->device);
		return $this->OK();
	}



	/**
	 * Function that returns a successful response(200)
	 * @return \StdClass
	 */
	private function OK()
	{
		$ret = new \StdClass();
		$ret->status = "OK";
		return $ret;
	}

	/**
	 * Function that returns an unsuccessful response(500)
	 * @return \WP_REST_Response
	 */
	private function KO($message = "")
	{
		$ret = new \StdClass();
		$ret->status = "KO";
		$ret->message = $message;
		return new \WP_REST_Response($ret, 500);
	}

	/**
	 * Function that returns Not Found(404)
	 * @return \WP_REST_Response
	 */
	private function NotFound404()
	{
		return new \WP_REST_Response(null, 404);
	}


	/**
	 * Function that processes the invated json in the body of the response
	 * @param WP_REST_Request $request
	 * @param bool $associative
	 * @return mixed
	 */
	private function getJson(WP_REST_Request $request, $associative = false)
	{
		$json = $request->get_body();
		return json_decode($json, $associative);
	}

	/**
	 * Function that verifies that the secret site key sent in each request is valid.
	 * @param WP_REST_Request $request
	 * @return bool
	 */
	private function validateSecret(WP_REST_Request $request)
	{
		$obj = $this->getJson($request);
		return $obj->secret == get_option(Constants::OPTION_SECRET_KEY);
	}
}
