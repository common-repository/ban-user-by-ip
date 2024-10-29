<?php
/*
Plugin Name: Ban User By IP
Plugin URI: http://www.danycode.com/ban-user-by-ip/
Description: Ban User By IP is a simple plugin that allows you to ban who you want by inserting the user IP Address.
Version: 1.06
Author: Danilo Andreini
Author URI: http://www.danycode.com
License: GPLv2 or later
*/

/*  Copyright 2012  Danilo Andreini (email : andreini.danilo@gmail.com)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

add_action( 'init', 'bubi_check_banned_ip' ); //check if user ip address exists in the database - YES - ban it - NO - do nothing

function bubi_menu() {
	add_options_page( 'Ban User By IP', 'Ban User By IP', 'manage_options', 'bubi_options', 'bubi_options' );
}
add_action( 'admin_menu', 'bubi_menu' );

function bubi_options(){//admin menu
	//add row
	if(isset($_POST['newip'])){
		bubi_add_row($_POST['newip'],$_POST['note']);
	}
	
	//delete row
	if(isset($_POST['deleteid'])){
		bubi_delete_row($_POST['deleteid']);
	}	
		
	//form
	echo '<h3>Ban User By IP</h3>';
	echo '<p class="bubi-red">WARNING! Do not enter your IP address or you will be banned.<br />(You can recover by deleting the plugin via FTP or by altering *prefix*_bubi_table)</p>';
	echo '<p>Ask for support at <a target="_blank" href="http://www.danycode.com/ban-user-by-ip/">Ban User By IP Official Page</a></p>';	
	echo '<p class="bubi-head">Add a new IP address to your ban list.</p>';
	echo '<form action="" method="post">';
	echo '<span>IP to Ban</span><input type="text" name="newip" maxlength="20">';
	echo '<span>Optional Note</span><input type="text" name="note" maxlength="20">';
	echo '<input type="submit" value="Add">';
	echo '</form>';
			
	bubi_show_banned();//show banned ip
}

//writing in frontend head
function bubi_front_head()
{
echo '<link rel="stylesheet" type="text/css" media="all" href="'.WP_PLUGIN_URL.'/ban-user-by-ip/css/style.css" />';
}
add_action( 'wp_head', 'bubi_front_head' );

//writing in backend head
function bubi_admin_head()
{
echo '<link rel="stylesheet" type="text/css" media="all" href="'.WP_PLUGIN_URL.'/ban-user-by-ip/css/style.css" />';
}
add_action( 'admin_head', 'bubi_admin_head' );

register_activation_hook(WP_PLUGIN_DIR.'/ban-user-by-ip/main.php','bubi_create_table');//table creation during the plugin activation
function bubi_create_table(){
	global $wpdb;$table_name=$wpdb->prefix . "bubi_table";
	$sql = "CREATE TABLE $table_name (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  ip VARCHAR(20) DEFAULT '' NOT NULL,
	  note VARCHAR(20) DEFAULT '' NOT NULL,
	  UNIQUE KEY id (id)
	);";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}

function bubi_add_row($ip,$note){//add a new banned ip address
	//check the ip
	$valid_ip = filter_var($ip, FILTER_VALIDATE_IP);
	if(!$valid_ip){
		echo '<p class="bubi-message">This IS NOT a valid IP address</p>';
	}else{
		//save
		if(preg_match('/[^a-z_, .?\-0-9]/i',$note)){$note="-";}
		if(strlen($note)==0){$note="-";}
		global $wpdb;$table_name=$wpdb->prefix . "bubi_table";
		$sql = "INSERT INTO $table_name SET ip='$valid_ip',note='$note'";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}

function bubi_delete_row($id){//delete a banned ip address
	global $wpdb;$table_name=$wpdb->prefix . "bubi_table";
	$sql = "DELETE FROM $table_name WHERE id='$id'";
	$wpdb->query($sql);
}

function bubi_show_banned() {//show all the banned ip address
	global $wpdb;
	$table_name=$wpdb->prefix . "bubi_table";
	$bubi_tables = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
	if(count($bubi_tables)>0){
			echo '<div class="bubi-separator"></div>';
			echo '<div class="bubi-list clearfix">';
			echo '<div class="bubi-c1 bubi-head">IP to Ban</div><div class="bubi-c2 bubi-head">Optional Note</div><div class="bubi-c3 bubi-head">Action</div>';
			foreach ( $bubi_tables as $bubi_table ) 
			{		
				echo '<form action="" method="post">';
				echo '<div class="bubi-c1">'.$bubi_table['ip'].'</div>';
				echo '<div class="bubi-c2">'.$bubi_table['note'].'</div>';
				echo '<input type="hidden" name="deleteid" value="'.$bubi_table['id'].'">';
				echo '<div class="bubi-c3"><input type="submit" value="delete"></div>';
				echo '</form>';
			}
			echo '</div>';				
	}

}

function bubi_check_banned_ip(){//check if user ip address exists in the database		
	global $wpdb;	
	$user_ip=$_SERVER['REMOTE_ADDR'];
	$table_name = $wpdb->prefix . "bubi_table";
	$bubi_tables = $wpdb->get_results("SELECT * FROM $table_name WHERE ip='$user_ip'", ARRAY_A);
	if(count($bubi_tables)>0){
			//ban message
			echo "Your IP Address has been banned";exit();
	}
}

?>
