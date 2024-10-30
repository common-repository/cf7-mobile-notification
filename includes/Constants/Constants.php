<?php

namespace Cf7MobileNotification\Constants;


class Constants
{
	const PLUGIN_VERSION = "1.0";
	const DATABASE_VERSION = "1.3";
	const PLUGIN_CODE = "cf7_mobile_notification";
	const PLUGIN_NAME = "cf7-mobile-notification";

	const PLUGIN_LANGUAGE_DOMAIN = "cf7_mobile_notification";

	const OPTION_DB_VERSION = self::PLUGIN_CODE . "_db_version";
	const OPTION_SECRET_KEY = self::PLUGIN_CODE . "_secret_key";
	const OPTION_ACTIVATION_REDIRECT = self::PLUGIN_CODE . "_activation_redirect";

	const SQL_TABLE_FORMS_DATA = self::PLUGIN_CODE . "_forms_data";
	const SQL_TABLE_DEVICES = self::PLUGIN_CODE . "_devices";
	const SQL_TABLE_DEVICES_FORMS = self::PLUGIN_CODE . "_devices_forms";
	const SQL_TABLE_DEVICE_NOTIFICATION =  self::PLUGIN_CODE . "_device_notifications";

	const API_NAMESPACE = "cf7_mobile_notification";
	const API_VERSION = "v1";

	const PLUGIN_UPLOADS_DIR = self::PLUGIN_CODE . "_uploads";

	const API_ROUTE_STORE_DEVICE_TOKEN = "store-device-token";
	const API_FUNCTION_STORE_DEVICE_TOKEN = "storeDeviceToken";

	const API_ROUTE_REMOVE_DEVICE_TOKEN = "remove-device-token";
	const API_FUNCTION_REMOVE_DEVICE_TOKEN = "removeDeviceToken";

	const API_ROUTE_GET_FORMS = "get-forms";
	const API_FUNCTION_GET_FORMS = "getForms";

	const API_ROUTE_GET_FORM_DATA = "get-form-data";
	const API_FUNCTION_GET_FORM_DATA = "getFormData";

	const API_ROUTE_ENABLE_NOTIFICATION = "enable-notification";
	const API_FUNCTION_ENABLE_NOTIFICATION = "enableNotification";

	const API_ROUTE_DISABLE_NOTIFICATION = "disable-notification";
	const API_FUNCTION_DISABLE_NOTIFICATION = "disableNotification";

	const API_ROUTE_SET_READ_STATE = "set-read-state";
	const API_FUNCTION_SET_READ_STATE = "setReadState";

	const API_ROUTE_SET_CONTACTED_STATE = "set-contacted-state";
	const API_FUNCTION_SET_CONTACTED_STATE = "setContactedState";

	const API_ROUTE_GET_SINGLE_FORM = "get-single-form";
	const API_FUNCTION_GET_SINGLE_FORM = "getSingleForm";

	const API_ROUTE_GET_SINGLE_RECORD = "get-single-record";
	const API_FUNCTION_GET_SINGLE_RECORD = "getSingleRecord";

	const API_ROUTE_GET_MESSAGE_NOTIFICATIONS = "get-message-notification";
	const API_FUNCTION_GET_MESSAGE_NOTIFICATIONS = "getMessageNotifications";

	const API_ROUTE_REMOVE_MESSAGE_NOTIFICATIONS = "remove-message-notification";
	const API_FUNCTION_REMOVE_MESSAGE_NOTIFICATIONS = "removeMessageNotifications";

}
