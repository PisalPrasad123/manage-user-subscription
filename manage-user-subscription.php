<?php
/*
Plugin Name: Manage user subscription
Plugin URI: http://vlad.nastasiu.com/
Description: Manage user subscription only by admins. Shows start/end date to users.
Version: 1.0
Author: Vlad Nastasiu
Author URI: http://vlad.nastasiu.com
License: GPL
*/

// general variables
global $wpdb;
$newSubscriptionSubject = 'You have a new subscription';
$updateSubscriptionSubject = 'Your subscription has been updated';

// Add funcitons
require_once('functions.php');

// General contants
define('SUBSCRIPTION_TABLE', $wpdb->prefix.'mist_readers');

// Add wordpress action hooks
register_activation_hook(__FILE__, "check_table");

// admin hooks
add_action("edit_user_profile", "MUS_show_fields_admin");
add_action("edit_user_profile_update", "MUS_save_fields");

// general user hooks
add_action("profile_personal_options", "MUS_show_fields_user");

/*
  create table if it does not exist, add users to that table.
*/
function check_table() {
  global $wpdb;

  $checkTable = $wpdb->query("SHOW TABLES LIKE '".SUBSCRIPTION_TABLE."'");
  if (!$checkTable) {
    // create table
    $wpdb->query("CREATE TABLE IF NOT EXISTS `".SUBSCRIPTION_TABLE."` (
      `ID` bigint(20) NOT NULL auto_increment,
      `user_id` bigint(20) unsigned NOT NULL default '0',
      `user_read_start` datetime NOT NULL default '0000-00-00 00:00:00',
      `user_read_end` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (`ID`),
      KEY `USER_ID` (`user_id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    // import users
    $existingUsers = $wpdb->get_col("SELECT ID FROM $wpdb->users");
    foreach ($existingUsers as $userID) {
      $wpdb->insert(
        SUBSCRIPTION_TABLE,
        array(
          'user_id' => $userID
        )
      );
    }
  }
}

/*
  show fields of user for admin
*/
function MUS_show_fields_admin($user) {
  global $wpdb;

  // radio fields
  $sendEmailFields = array(array('label'=>'NEW subscription','checked'=>false),array('label'=>'UPDATE subscription','checked'=>false),array('label'=>'nevermind','checked'=>true));

  // grab user info
  $userDates = $wpdb->get_row("SELECT user_read_start, user_read_end FROM ".SUBSCRIPTION_TABLE." WHERE user_id = $user->ID");
  $userReadStart = explode(' ', $userDates->user_read_start);
  $userReadEnd = explode(' ', $userDates->user_read_end);

  // print form
  echo addJs(array('date','mus')).
  '<h3>User subscription details</h3>'.
  '<table class="form-table" id="musForm">
      <tbody>'
        .inputData('date','User read start',$userReadStart[0])
        .inputData('date','User read end',$userReadEnd[0])
        .inputData('textarea','Email for NEW subscription','',array('description'=>'You can use [DATASTART], [DATAEND], [NUMEPERSOANA] or [USERNAME] and the content will be updated accordingly.'))
        .inputData('textarea','Email for UPDATE subscription','',array('description'=>'You can use [DATASTART], [DATAEND], [NUMEPERSOANA] or [USERNAME] and the content will be updated accordingly.'))
        .inputData('radio','Send email? ',$sendEmailFields).'
      </tbody>
    </table>';
}

/*
  show fields for regular user
*/
function MUS_show_fields_user($user) {
  global $wpdb;

  // grab user info
  $userDates = $wpdb->get_row("SELECT user_read_start, user_read_end FROM ".SUBSCRIPTION_TABLE." WHERE user_id = $user->ID");
  $userReadStart = explode(' ', $userDates->user_read_start);
  $userReadEnd = explode(' ', $userDates->user_read_end);

  echo
    addJs(array('date','global')).
    '<h3>User subscription details</h3>'.
    '<table class="form-table">
      <tbody>'
        .inputData('date','User read start',$userReadStart[0],array('readonly'))
        .inputData('date','User read end',$userReadEnd[0],array('readonly')).'
      </tbody>
    </table>';
}

/*
  update profile by admin
*/
function MUS_save_fields($user_id) {
  global $wpdb, $current_user,
  $newSubscriptionSubject,
  $updateSubscriptionSubject;

  // get user info
  $user = get_userdata($user_id);
  get_currentuserinfo();

  // read form data
  $userReadStart = $_POST['user_read_start'];
  $userReadEnd = $_POST['user_read_end'];
  $sendEmail = $_POST['send_email_'];

  // update fields
  $wpdb->query("UPDATE ".SUBSCRIPTION_TABLE." SET user_read_start = '$userReadStart 00:00:00', user_read_end = '$userReadEnd 12:59:59' WHERE user_id = {$user_id}");

  // email stuff
  add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
  $headers = 'From: '.get_bloginfo('name').' <'.$current_user->user_email.'>' . "\r\n";
  $customFields = array(
    '[DATASTART]',
    '[DATAEND]',
    '[NUMEPERSOANA]',
    '[USERNAME]',
    "\n",
    "\n\r"
  );
  $userCustomFields = array(
    $userReadStart,
    $userReadEnd,
    $user->user_firstname.' '.$user->user_lastname,
    $user->user_login,
    '<br/>',
    '<br/>'
  );

  // send email
  switch ($sendEmail) {
    case 'new_subscription':
      $message = $_POST['email_for_new_subscription'];
      $message = str_replace($customFields, $userCustomFields, $message);
      wp_mail($current_user->user_email, $newSubscriptionSubject, $message, $headers);
      break;
    case 'update_subscription':
      $message = $_POST['email_for_update_subscription'];
      $message = str_replace($customFields, $userCustomFields, $message);
      wp_mail($current_user->user_email, $updateSubscriptionSubject, $message, $headers);
      break;
    
    default:
      # nothing happens
      break;
  }
}