<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

define('HOSTNAME', 'localhost');
define('USERNAME', 'root');
define('PASSWORD', '121586');
define('DATABASE', 'nelsoft');
define('DMP_DATABASE', 'dangar99_dmp_feu_ohd');
define('BACKUP_DIR', './myBackups' ) ;


define('SCRIPTS','scripts/');
define('SERVICES','scripts/');
define('CSS','assets/css/');
define('FONTS','assets/fonts/');
define('IMG','assets/img/');
define('JS','assets/js/');
define('BOWER','assets/bower_components/');

/* ENCRYPTION AND DECRYPTION*/
define('SALT', 'T!S!D!T_33_HELLO_WORLD');
define('KEY', 'TSDT_THE_BEST');

//USER PERMISSION//

define('ADMIN',100);

//BRANCH_SETTINGS
define('VIEW_BRANCH',101);
define('ADD_BRANCH',102);
define('EDIT_BRANCH',103);
define('DELETE_BRANCH',104);

//USER_SETTINGS
define('VIEW_USER',110);
define('ADD_USER',111);
define('EDIT_USER',112);
define('DELETE_USER',113);

//SHIPPER_SETTINGS
define('VIEW_SHIPPER',119);
define('ADD_SHIPPER',120);
define('EDIT_SHIPPER',121);
define('DELETE_SHIPPER',122);

//SEGREGATION_SETTINGS
define('VIEW_SEGREGATION_SETTINGS',128);
define('ADD_SEGREGATION_SETTINGS',129);
define('EDIT_SEGREGATION_SETTINGS',130);
define('DELETE_SEGREGATION_SETTINGS',131);

define('VIEW_SEGREGATION_DETAIL_SETTINGS',132);
define('ADD_SEGREGATION_DETAIL_SETTINGS',133);
define('EDIT_SEGREGATION_DETAIL_SETTINGS',134);
define('DELETE_SEGREGATION_DETAIL_SETTINGS',135);

//EMAIL_SETTINGS
define('VIEW_EMAIL_SETTINGS',137);
define('EDIT_EMAIL_SETTINGS',138);

//SMS_SETTINGS
define('VIEW_SMS_SETINGS',144);
define('EDIT_SMS_SETTINGS',145);

//RATE_ADJUST_SETTINGS
define('VIEW_RATE_ADJUST',151);
define('ADD_RATE_ADJUST',152);
define('EDIT_RATE_ADJUST',153);
define('DELETE_RATE_ADJUST',154);

//MUNICIPAL_SETTINGS_SETTINGS
define('VIEW_MUNICIPAL_SETTINGS',160);
define('ADD_MUNICIPAL_SETTINGS',161);
define('EDIT_MUNICIPAL_SETTINGS',162);
define('DELETE_MUNICIPAL_SETTINGS',163);

//WAYBILL
define('VIEW_WAYBILL',169);
define('ADD_WAYBILL',170);
define('EDIT_WAYBILL',171);
define('DELETE_WAYBILL',172);
define('IMPORT_EXCEL',173);
define('EXPORT_WAYBILL_EXCEL',174);
define('PULLOUT_WAYBILL',175);

//WAYBILL_LOGS
define('VIEW_WAYBILL_LOGS',181);
define('ADD_WAYBILL_LOGS',182);
define('EDIT_WAYBILL_LOGS',183);
define('DELETE_WAYBILL_LOGS',184);

//WAYBILL_TRACK
define('VIEW_WAYBILL_TRACK',191);

//SEGREGATION
define('VIEW_SEGREGATION',192);
define('ADD_SEGREGATION',193);
define('EDIT_SEGREGATION',194);
define('DELETE_SEGREGATION',195);
define('AUTO_SEGREGATE',196);
define('AUTO_DISPATCH',197);

//DISPATCH
define('VIEW_DISPATCH',203);
define('ADD_DISPATCH',204);
define('EDIT_DISPATCH',205);
define('DELETE_DISPATCH',206);
define('PRINT_TRACKING_SHEET',207);
define('PRINT_MANIFEST',208);
define('PRINT_RUN_SHEET',209);
define('SEND_SMS',210);

//BILLING_STATEMENT
define('VIEW_BILLING_STATEMENT',216);
define('EXPORT_BILLING_EXCEL',217);
define('MAIL_BILLING',218);
define('EXPORT_BILLING_PDF',223);

//STATEMENT_OF_ACCOUT
define('VIEW_SOC',219);
define('EXPORT_SOC_PDF',220);
define('MAIL_SOC',221);
define('SOC_ADD_ON',222);

//DELIVERY WARNING
define('VIEW_WARNING',228);

define('VIEW_RECEIVE', 229);
define('EDIT_RECEIVE', 230);

/* End of file constants.php */
/* Location: ./application/config/constants.php */