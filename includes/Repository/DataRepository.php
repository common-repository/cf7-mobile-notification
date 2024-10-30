<?php

namespace Cf7MobileNotification\Repository;

use Cf7MobileNotification\Constants\Constants;
use Cf7MobileNotification\Notification\Events;
use WPCF7_ContactForm;
use WPCF7_Submission;

class DataRepository
{

    /**
     * @var DataRepository
     */
    private static $_instance = null;

    /**
     * DataRepository constructor.
     */
    private function __construct()
    {

    }

    /**
     * @return DataRepository
     */
    public static function getInstance()
    {
        if (!DataRepository::$_instance) {
            DataRepository::$_instance = new DataRepository();
        }

        return DataRepository::$_instance;
    }


    /**
     * Function that stores the device token in the device tokens lists
     * @param $id
     * @param $token
     */
    public function storeDeviceToken($id, $token)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICES;

        $row = $wpdb->get_row("SELECT id from $table_name WHERE id = '$id'", ARRAY_A);

        if (isset($row) && array_key_exists("id", $row)) {
            $wpdb->update($table_name,
                array(
                    'token' => $token
                ), array('id' => $id));
        } else {
            $wpdb->insert($table_name,
                array(
                    'id' => $id,
                    'token' => $token
                ));
        }

        $posts = get_posts(array(
            'post_type' => 'wpcf7_contact_form',
            'numberposts' => -1
        ));

        foreach ($posts as $post) {
            $this->enableFormNotification($id, $post->ID);
        }
    }


    /**
     * Return the list of CF7 forms on the website
     * @param $device_id
     * @return array
     */
    public function getForms($device_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICES_FORMS;
        $forms_with_notification = $wpdb->get_results("SELECT form FROM $table_name WHERE device = '$device_id';", ARRAY_A);
        $notification_ids = array();
        foreach ($forms_with_notification as $row)
            $notification_ids[] = $row["form"];

        $posts = get_posts(array(
            'post_type' => 'wpcf7_contact_form',
            'numberposts' => -1
        ));
        $ret = array();
        foreach ($posts as $form) {
            $obj = new \StdClass();
            $obj->id = $form->ID;
            $obj->title = $form->post_title;
            $obj->notification = in_array($form->ID, $notification_ids);
            $obj->lastcontact = $this->getLastContactTime($form->ID);
            $obj->unread = $this->getUnreadCount($form->ID);
            $obj->count = $this->getContactNumber($form->ID);
            $ret[] = $obj;
        }
        return $ret;
    }

    /**
     * Return a form from his ID
     * @param $device_id
     * @param $form_id
     * @return \StdClass|null
     */
    public function getSingleForm($device_id, $form_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICES_FORMS;
        $forms_with_notification = $wpdb->get_results("SELECT form FROM $table_name WHERE device = '$device_id';", ARRAY_A);
        $notification_ids = array();
        foreach ($forms_with_notification as $row)
            $notification_ids[] = $row["form"];

        $form = $this->getFormById($form_id, $notification_ids);
        return $form;
    }

    private function getFormById($form_id, $notification_ids = array())
    {
        $form = get_post($form_id);
        if ($form) {
            $obj = new \StdClass();
            $obj->id = $form->ID;
            $obj->title = $form->post_title;
            $obj->notification = in_array($form->ID, $notification_ids);
            $obj->lastcontact = $this->getLastContactTime($form->ID);
            $obj->unread = $this->getUnreadCount($form->ID);
            $obj->count = $this->getContactNumber($form->ID);
            return $obj;
        }
        return null;
    }


    private function getLastContactTime($form)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Constants::SQL_TABLE_FORMS_DATA;
        $row = $wpdb->get_row("SELECT MIN(TIMESTAMPDIFF(SECOND,`submission_date`,NOW())) as `time` FROM $table_name WHERE `form_id`= $form;", ARRAY_A);
        if ($row["time"] != null) {
            return intval($row["time"]);
        } else {
            return false;
        }
    }

    private function getContactNumber($form)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Constants::SQL_TABLE_FORMS_DATA;
        $row = $wpdb->get_row("SELECT COUNT(*) as `count` FROM $table_name WHERE `form_id`= $form;", ARRAY_A);
        return intval($row['count']);
    }

    private function getUnreadCount($form)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Constants::SQL_TABLE_FORMS_DATA;
        $row = $wpdb->get_row("SELECT COUNT(*) as `count` FROM $table_name WHERE `form_id`= $form AND `read` = 0;", ARRAY_A);
        return intval($row['count']);
    }

    /**
     * Adds a device to the list of devices that will be notified when a contact is submitted from the form
     * @param $device_id
     * @param $form_id
     */
    public function enableFormNotification($device_id, $form_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICES_FORMS;
        $wpdb->replace($table_name, array('device' => $device_id, 'form' => $form_id));
    }

    /**
     * Removes a device from list of devices that will be notified when a contact is submitted from the form
     * @param $device_id
     * @param $form_id
     */
    public function disableFormNotification($device_id, $form_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICES_FORMS;
        $wpdb->delete($table_name, array('device' => $device_id, 'form' => $form_id));
    }

    /**
     * Function that returns a list of device token stored in database
     * @return array|object|null
     */
    public function getDeviceTokens()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICES;
        $results = $wpdb->get_results("
			SELECT
			`token`
			from $table_name WHERE 1", ARRAY_A);
        return $results;
    }

    /**
     * Function that removes device token and it's preferences from database
     * @param $id
     */
    public function removeDeviceToken($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICES;

        $wpdb->delete($table_name, array('id' => $id));
    }

    /**
     * Function that updates notification preferences for a device
     * @param $id
     * @param $array
     */
    public function updateNotificationPreferences($id, $array)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICES;

        $wpdb->update($table_name, $array, array('id' => $id));
    }

    /**
     * Set the "read" flag in the message
     * @param $id
     * @param $state
     */
    public function setReadState($id, $state)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Constants::SQL_TABLE_FORMS_DATA;

        $wpdb->update($table_name, array('read' => $state), array('id' => $id));
    }

    /**
     * Set the "contacted" flag in the message
     * @param $id
     * @param $state
     */
    public function setContactedState($id, $state)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Constants::SQL_TABLE_FORMS_DATA;

        $wpdb->update($table_name, array('contacted' => $state), array('id' => $id));
    }

    /**
     * Processes the contact coming from CF7, inserts it in the db and sends the notifications to the devices
     * @param $form_tag
     */
    function before_send_mail($form_tag)
    {

        global $wpdb;
        $table_name = $wpdb->prefix . Constants::SQL_TABLE_FORMS_DATA;
        $wpcf7 = WPCF7_ContactForm::get_current();
        $mail = $wpcf7->prop('mail');
        $subject = wpcf7_mail_replace_tags($mail["subject"]);
        $body = wpcf7_mail_replace_tags($mail["body"]);
        $upload_dir = wp_upload_dir();
        $upload_dirname = $upload_dir['basedir'] . '/' . Constants::PLUGIN_UPLOADS_DIR;
        $time_now = time();

        if (!file_exists($upload_dirname)) {
            wp_mkdir_p($upload_dirname);
        }

        $form = WPCF7_Submission::get_instance();

        if ($form) {

            $black_list = array('_wpcf7', '_wpcf7_version', '_wpcf7_locale', '_wpcf7_unit_tag',
                '_wpcf7_is_ajax_call', 'cfdb7_name', '_wpcf7_container_post', '_wpcf7cf_hidden_group_fields',
                '_wpcf7cf_hidden_groups', '_wpcf7cf_visible_groups', '_wpcf7cf_options', 'g-recaptcha-response');

            $data = $form->get_posted_data();
            $files = $form->uploaded_files();
            $uploaded_files = array();

            foreach ($files as $file_key => $file) {
                $file_path = $upload_dirname . '/' . $time_now . '-' . basename($file);
                $uploaded_files[$file_key] = $file_path;
                copy($file, $file_path);
            }

            $form_data = array();
            foreach ($data as $key => $d) {

                $matches = array();
                preg_match('/^_.*$/m', $key, $matches);

                if (!in_array($key, $black_list) && empty($matches[0])) {

                    $tmpD = $d;

                    if (!is_array($d)) {

                        $bl = array('\"', "\'", '/', '\\', '"', "'");
                        $wl = array('&quot;', '&#039;', '&#047;', '&#092;', '&quot;', '&#039;');

                        $tmpD = str_replace($bl, $wl, $tmpD);
                    }

                    foreach ($files as $key_file => $value) {
                        if ($key_file == $key) {
                            if (array_key_exists($key, $uploaded_files)) {
                                $tmpD = $this->abs_path_to_url($uploaded_files[$key]);
                                $key = "FILE_" . $key;
                            }
                        }
                    }
                    $form_data[$key] = $tmpD;
                }
            }

            $form_post_id = $form_tag->id();
            $form_value = json_encode($form_data);
            $form_date = current_time('Y-m-d H:i:s');

            $data = array(
                'id' => wp_generate_uuid4(),
                'form_id' => $form_post_id,
                'subject' => $subject,
                'message' => $body,
                'data' => $form_value,
                'submission_date' => $form_date,
                'read' => 0,
                'contacted' => 0
            );

            $wpdb->insert($table_name, $data);
            $this->addNotifications($data['id'], $data['form_id']);

            $data['title'] = $wpcf7->title();
            Events::getInstance()->onSubmission($data);
        }

    }

    /**
     * Return the list of notifications (messages not read and sent via push notification to the device)
     * @param $device
     * @return array
     */
    public function getMessageNotifications($device)
    {
        global $wpdb;
        $form_data = $wpdb->prefix . Constants::SQL_TABLE_FORMS_DATA;
        $device_notification = $wpdb->prefix . Constants::SQL_TABLE_DEVICE_NOTIFICATION;
        $ret = array();
        $results = $wpdb->get_results(
            "SELECT FD.`id`,FD.`subject`,FD.`form_id`,FD.`message`,FD.`data`,TIMESTAMPDIFF(SECOND,FD.`submission_date`,NOW()) as `time`,FD.`read`,FD.`contacted`
					FROM $device_notification DN INNER JOIN $form_data FD on (DN.`message` = FD.`id`)
					WHERE DN.`device`= '$device';", ARRAY_A);
        foreach ($results as $row) {
            $obj = new \StdClass();
            $obj->id = $row['id'];
            $obj->subject = $row['subject'];
            $obj->message = $row['message'];
            $obj->data = json_decode($row['data']);
            $obj->time = intval($row['time']);
            $obj->read = $row['read'] != 0;
            $obj->contacted = $row['contacted'] != 0;
            $obj->form = $this->getSingleForm($device, $row['form_id']);
            $ret[] = $obj;
        }
        return $ret;
    }

    /**
     * Removes a notification
     * @param $message_id
     */
    public function removeNotification($message_id, $device)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICE_NOTIFICATION;
        $wpdb->delete($table_name, array(
            'message' => $message_id,
            'device' => $device
        ));
    }


    private function addNotifications($message_id, $form_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICE_NOTIFICATION;
        $device_ids = $this->getDeviceIdsSubscribedToForm($form_id);

        foreach ($device_ids as $id) {
            $wpdb->insert($table_name, array(
                'device' => $id,
                'message' => $message_id
            ));
        }
    }

    private function getDeviceIdsSubscribedToForm($form_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICES_FORMS;

        $ret = array();
        $results = $wpdb->get_results("SELECT device FROM $table_name WHERE form = $form_id;", ARRAY_A);
        foreach ($results as $result) {
            $ret[] = $result['device'];
        }
        return $ret;
    }


    /**
     * When a new CF7 form is added all devices are put in the list of devices that will receive the form notifications
     * @param $post_ID
     * @param $post
     * @param $update
     */
    public function onNewFormAdded($post_ID, $post, $update)
    {
        if (!$update)
            if (get_post_type($post_ID) === "wpcf7_contact_form") {
                global $wpdb;
                $table_name = $wpdb->prefix . Constants::SQL_TABLE_DEVICES;
                $results = $wpdb->get_results("SELECT id from $table_name;", ARRAY_A);
                foreach ($results as $result) {
                    $this->enableFormNotification($result['id'], $post_ID);
                }
            }
    }

    /**
     * Return the list of requests received for that form
     * @param $form
     * @return array
     */
    public function getFormData($form)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Constants::SQL_TABLE_FORMS_DATA;
        $results = $wpdb->get_results("SELECT `id`,`subject`,`message`,`data`,TIMESTAMPDIFF(SECOND,`submission_date`,NOW()) as `time`,`read`,`contacted` FROM $table_name WHERE `form_id`= $form;", ARRAY_A);
        $ret = array();
        foreach ($results as $row) {
            $obj = new \StdClass();
            $obj->id = $row['id'];
            $obj->subject = $row['subject'];
            $obj->message = $row['message'];
            $obj->data = json_decode($row['data']);
            $obj->time = intval($row['time']);
            $obj->read = $row['read'] != 0;
            $obj->contacted = $row['contacted'] != 0;
            $ret[] = $obj;
        }
        return $ret;
    }

    /**
     * Returns a single request from his id
     * @param $record_id
     * @return \StdClass|null
     */
    public function getSingleRecord($record_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Constants::SQL_TABLE_FORMS_DATA;
        $row = $wpdb->get_row("SELECT `id`,`subject`,`message`,`data`,TIMESTAMPDIFF(SECOND,`submission_date`,NOW()) as `time`,`read`,`contacted` FROM $table_name WHERE `id`= '$record_id';", ARRAY_A);
        if ($row) {
            $obj = new \StdClass();
            $obj->id = $row['id'];
            $obj->subject = $row['subject'];
            $obj->message = $row['message'];
            $obj->data = json_decode($row['data']);
            $obj->time = $row['time'];
            $obj->read = $row['read'] != 0;
            $obj->contacted = $row['contacted'] != 0;
            return $obj;
        }
        return null;
    }

    private function abs_path_to_url($path = '')
    {
        $url = str_replace(
            wp_normalize_path(untrailingslashit(ABSPATH)),
            site_url(),
            wp_normalize_path($path)
        );
        return esc_url_raw($url);
    }
}
