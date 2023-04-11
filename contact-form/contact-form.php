<?php
/*
Plugin Name: Contact Form Plugin
Description: A plugin that allows you to add a contact form to your website
Version: 1.0
Author: Bassam
*/

// Register activation and deactivation hooks
register_activation_hook( __FILE__, 'create_contact_form_table' );
register_deactivation_hook( __FILE__, 'delete_contact_form_table' );

// Function to create the wp_contact_form table
function create_contact_form_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'contact_form';

    $sql = "CREATE TABLE $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        message text NOT NULL,
        date_sent datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

// Function to delete the wp_contact_form table
function delete_contact_form_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'contact_form';
    $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}

// Function to add the contact form shortcode
function contact_form_shortcode() {
    // Add code to generate the contact form HTML
    $output = '';
    
    // Check if the form has been submitted
    if ( isset( $_POST['submit_contact_form'] ) ) {
        // Process the form submission
        process_contact_form();
        // Display a success message
        $output .= '<p class="contact-form-success">Thank you for contacting us!</p>';
    }
    
    // Generate the HTML for the contact form
    $output .= '<form id="contact-form" method="post">';
    $output .= '<label for="contact-form-name">Name:</label>';
    $output .= '<input type="text" id="contact-form-name" name="contact-form-name" required>';
    $output .= '<label for="contact-form-email">Email:</label>';
    $output .= '<input type="email" id="contact-form-email" name="contact-form-email" required>';
    $output .= '<label for="contact-form-message">Message:</label>';
    $output .= '<textarea id="contact-form-message" name="contact-form-message" required></textarea>';
    $output .= '<input type="submit" name="submit_contact_form" value="Send">';
    $output .= '</form>';
    
    return $output;    
}

add_shortcode( 'contact_form', 'contact_form_shortcode' );

// Function to process the contact form submission
function process_contact_form() {
    if ( isset( $_POST['submit_contact_form'] ) ) {
      $name = sanitize_text_field( $_POST['contact-form-name'] );
      $email = sanitize_email( $_POST['contact-form-email'] );
      $message = sanitize_textarea_field( $_POST['contact-form-message'] );
      $to = get_option( 'admin_email' );
      $headers = "From: $name <$email>" . "\r\n";
      $sent = wp_mail( $to, $message, $headers );
  
      global $wpdb;
      $table_name = $wpdb->prefix . 'contact_form';
  
      $wpdb->insert(
        $table_name,
        array(
          'name' => $name,
          'email' => $email,
          'message' => $message,
          'date_sent' => current_time('mysql')
        ),
        array(
          '%s',
          '%s',
          '%s',
          '%s',
          '%s',
          '%s'
        )
      );
    }
}

  

// Add the admin menu item
function add_contact_form_menu() {
    add_menu_page( 
        'Contact Form Responses',  // The text to be displayed in the browser window for this page
        'Contact Form',  // The text to be displayed for this menu item
        'manage_options',  // The capability required to access this menu item
        'contact_form',  // The slug name to refer to this menu item
        'contact_form_responses_page'  // The function to be called to display the page content
    );
}



// Function to display the contact form responses page
function contact_form_responses_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'contact_form';
    $results = $wpdb->get_results( "SELECT * FROM $table_name" );
    
    echo '<div class="wrap"><h1>Contact Form Responses</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>ID</th><th>Subject</th><th>Name</th><th>First Name</th><th>Email</th><th>Message</th><th>Date Sent</th></tr></thead>';
    echo '<tbody>';
    foreach ( $results as $result ) {
        echo '<tr>';
        echo '<td>' . esc_html( $result->id ) . '</td>';
        echo '<td>' . esc_html( $result->subject ) . '</td>';
        echo '<td>' . esc_html( $result->name ) . '</td>';
        echo '<td>' . esc_html( $result->first_name ) . '</td>';
        echo '<td>' . esc_html( $result->email ) . '</td>';
        echo '<td>' . esc_html( $result->message ) . '</td>';
        echo '<td>' . esc_html( $result->date_sent ) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}


add_action( 'admin_menu', 'add_contact_form_menu' );


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
<style>
 form,p{
width:70%;
margin:0 auto !important;

 }   
</style>    
</body>
</html>