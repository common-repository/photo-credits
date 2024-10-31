<?php

/*

Plugin Name: Photo Credits

Description: Adding Author credits to Post Images

Version: 1.0

Author: Sathya Perumal
Author URI: http://www.advitha.com
Plugin URI: http://www.advitha.com/photo-credits


*/



/**

 * Add Photographer Name and URL fields to media uploader

 *

 * @param $form_fields array, fields to include in attachment form

 * @param $post object, attachment record in database

 * @return $form_fields, modified form fields

 */

function photo_credits_scripts() {

wp_enqueue_style( 'photo-credit-style', plugins_url('/photo-credit-style.css', __FILE__) ); 	

}



add_action( 'wp_enqueue_scripts', 'photo_credits_scripts' );



function be_attachment_field_credit( $form_fields, $post ) {

	$form_fields['be-photographer-name'] = array(

		'label' => 'Photographer Name',

		'input' => 'text',

		'value' => get_post_meta( $post->ID, 'be_photographer_name', true ),

		'helps' => 'Add photographer Name - required',

	);

        $form_fields['be-photographer-website'] = array(

		'label' => 'Source Website',

		'input' => 'text',

		'value' => get_post_meta( $post->ID, 'be_photographer_website', true ),

		'helps' => 'If provided, photo source will be displayed',

	);

	$form_fields['be-photographer-url'] = array(

		'label' => 'Photographer URL',

		'input' => 'text',

		'value' => get_post_meta( $post->ID, 'be_photographer_url', true ),

		'helps' => 'Add Photographer URL - Required',

	);



	return $form_fields;

}



add_filter( 'attachment_fields_to_edit', 'be_attachment_field_credit', 10, 2 );



/**

 * Save values of Photographer Name and URL in media uploader

 *

 * @param $post array, the post data for database

 * @param $attachment array, attachment fields from $_POST form

 * @return $post array, modified post data

 */



function be_attachment_field_credit_save( $post, $attachment ) {

	if( isset( $attachment['be-photographer-name'] ) )

		update_post_meta( $post['ID'], 'be_photographer_name', $attachment['be-photographer-name'] );



	if( isset( $attachment['be-photographer-url'] ) )

                update_post_meta( $post['ID'], 'be_photographer_url', esc_url( $attachment['be-photographer-url'] ) );

        if( isset( $attachment['be-photographer-website'] ) )

		update_post_meta( $post['ID'], 'be_photographer_website', $attachment['be-photographer-website'] );



	

	return $post;

}



add_filter( 'attachment_fields_to_save', 'be_attachment_field_credit_save', 10, 2 );



add_filter( 'the_content' , 'mh_wrap_image' , 15 );

function fjarrett_get_attachment_id_by_url( $url ) {

 

	// Split the $url into two parts with the wp-content directory as the separator.

	$parse_url  = explode( parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );

 

	// Get the host of the current site and the host of the $url, ignoring www.

	$this_host = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );

	$file_host = str_ireplace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );

 

	// Return nothing if there aren't any $url parts or if the current host and $url host do not match.

	if ( ! isset( $parse_url[1] ) || empty( $parse_url[1] ) || ( $this_host != $file_host ) )

		return;

 

	// Now we're going to quickly search the DB for any attachment GUID with a partial path match.

	// Example: /uploads/2013/05/test-image.jpg

	global $wpdb;

 

	$prefix     = $wpdb->prefix;

	$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM " . $prefix . "posts WHERE guid RLIKE %s;", $parse_url[1] ) );

 

	// Returns null if no attachment is found.

	return $attachment[0];

}

 

function mh_wrap_image( $content ) {

 

// Regex to find all <img ... > tags

$mh_img_regex1 = "/\<img [^>]*src=\"([^\"]+)\"[^>]*>/";

 

// Regex to find all <a href"..."><img ... ></a> tags

$mh_img_regex2 = "/<a href=.*><img [^>]*src=\"([^\"]+)\"[^>]*><\/a>/";

 

// Populate the results into 2 arrays

preg_match_all( $mh_img_regex1 , $content, $mh_img );

preg_match_all( $mh_img_regex2 , $content, $mh_matches );

 

// The second array will be a subset of the first so go through

// each element and delete the duplicates in the first.

$i=0;

foreach ( $mh_img[0] as $mh_img_count ) {

        $i2=0;

        foreach ( $mh_matches[0] as $mh_matches_count ) {

                if ( strpos($mh_matches_count, $mh_img_count ) ){

                        unset( $mh_img[0][$i] );

                        unset( $mh_img[1][$i] );

                        $i2++;

                        break;

                }

                $i2++;

                }

        $i++;

        }

// There is almost certainly a better way to do this.

// Append the no links array to the $mh_matches array.

$i=0;

$mh_start = count( $mh_matches[0] );

foreach ( $mh_img[0] as $mh_img_count ) {

        $mh_matches[0][ $mh_start + $i ] = $mh_img_count;

        $i++;

}

$i=0;

foreach ( $mh_img[1] as $mh_img_count ) {

        $mh_matches[1][ $mh_start + $i ] = $mh_img_count;

        $i++;

}

 

// If we get any hits then put the code before and after the img tags

if ( $mh_matches ) {;

        for ( $mh_count = 0; $mh_count < count( $mh_matches[0] ); $mh_count++ )

                {

                // Old img tag

                $mh_old = $mh_matches[0][$mh_count];

 

                // Get the img URL, it's needed for the button code

                $mh_img_url = $mh_matches[1][$mh_count];

                

                // Put together the pinterest code to place before the img tag

                

 

                // Replace before the img tag in the new string

                $mh_new = '<p class="img-cont">'.$mh_old ;

                // After the img tag

                $web = "";

                $thumb_id = fjarrett_get_attachment_id_by_url($mh_img_url);

                $attach = get_post_meta( $thumb_id, 'be_photographer_url');

                $attach1 = get_post_meta( $thumb_id, 'be_photographer_name');   

                $myurl = get_post_meta( $thumb_id, 'be_photographer_website'); 

              

                if($attach[0] && $attach1[0]){

                    if($myurl[0]){

                        $web = $myurl[0].'/';

                    }

                $mh_new .= '<span class="photo-credit">'.$web.'<a href="'.$attach[0].'" target = "_blank">'.$attach1[0].'</a></span>';

                }

                $mh_new .='</p>';

                // make the substitution

                $content = str_replace( $mh_old, $mh_new , $content );

                }

        }

return $content;

}?>