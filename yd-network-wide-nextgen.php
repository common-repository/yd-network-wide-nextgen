<?php
/**
 * @package YD Network-wide NextGen
 * @author Yann Dubois
 * @version 0.1.0
 */

/*
 Plugin Name: YD Network-wide NextGen
 Plugin URI: http://http://www.yann.com/en/wp-plugins/yd-network-wide-nextgen
 Description: Network-wide NGG galleries. Similar to SWT for NGG. 
 Version: 0.1.0
 Author: Yann Dubois
 Author URI: http://www.yann.com/
 License: GPL2
 */

/**
 * @copyright 2010  Yann Dubois  ( email : yann _at_ abc.fr )
 *
 *  Original development of this plugin was kindly funded by http://www.wellcom.fr/
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 Revision 0.1.0 [beta1]:
 - Original beta release
 */

/** Class includes **/

include_once( 'inc/yd-widget-framework.inc.php' );	// standard framework VERSION 20110118-02 or better

/** **/
$junk = new YD_Plugin( 
	array(
		'name' 				=> 'YD Network-wide NextGen',
		'version'			=> '0.1.0',
		'has_option_page'	=> false,
		'option_page_title' => 'YD Network-wide NextGen',
		'op_donate_block'	=> false,
		'op_credit_block'	=> false,
		'op_support_block'	=> false,
		'has_toplevel_menu'	=> false,
		'has_shortcode'		=> false,
		'has_widget'		=> false,
		'widget_class'		=> '',
		'has_cron'			=> false,
		'crontab'			=> array(
			//'daily'			=> array( 'YD_MiscWidget', 'daily_update' ),
			//'hourly'		=> array( 'YD_MiscWidget', 'hourly_update' )
		),
		'has_stylesheet'	=> false,
		'stylesheet_file'	=> 'css/yd.css',
		'has_translation'	=> false,
		'translation_domain'=> 'ydnwngg', // must be copied in the widget class!!!
		'translations'		=> array(
			array( 'English', 'Yann Dubois', 'http://www.yann.com/' ),
			array( 'French', 'Yann Dubois', 'http://www.yann.com/' )
		),		
		'initial_funding'	=> array( 'Wellcom', 'http://www.wellcom.fr' ),
		'additional_funding'=> array(),
		'form_blocks'		=> array(
		),
		'option_field_labels'=>array(
		),
		'option_defaults'	=> array(
		),
		'form_add_actions'	=> array(
		),
		'has_cache'			=> false,
		'option_page_text'	=> 'Bonjour.',
		'backlinkware_text' => '<!-- Network Wide NGG Plugin by YD -->',
		'plugin_file'		=> __FILE__,	
		'has_activation_notice'	=> false,
		'activation_notice' => '',
		'form_method'		=> 'post'
 	)
);

add_action( 'ngg_created_new_gallery', array( 'YD_nwNGG', 'replicate_gallery' ), 10 );
add_action( 'ngg_added_new_image', array( 'YD_nwNGG', 'replicate_image' ), 10 );

/**
 * 
 * You must specify a unique class name
 * to avoid collision with other plugins...
 * 
 */
class YD_nwNGG {
	const MASTER = 1;
	
	function replicate_gallery( $id ) {
		global $wpdb;
		$gallery = nggdb::find_gallery( $id );
		$title = $gallery->title;
		//$nggpath = preg_replace( '|/blogs.dir/(\d+)/files/|', '/blogs.dir/'. self::MASTER . '/files/', $gallery->path );
		//non! gardons le chemin de la galerie d'origine comme ça pas besoin de dupliquer les images !!
		$nggpath = $gallery->path;
		$user_ID = $gallery->author;
		switch_to_blog( self::MASTER );
		$wpdb->nggallery = $wpdb->prefix . 'ngg_gallery';
		echo 'path: ' . $nggpath . '<br/>';
		//echo 'Gallery: ' . $wpdb->nggallery . '<br/>';
		//echo 'prefix: ' . $wpdb->prefix . '<br/>';
		$galleryID = nggdb::add_gallery( $title, $nggpath, '', 0, 0, $user_ID );
		restore_current_blog();
		$wpdb->nggallery = $wpdb->prefix . 'ngg_gallery';
	}
	function replicate_image( $img ) {
		global $wpdb;
		$imagetmp = nggdb::find_image( $img['id'] );
		$gallery = nggdb::find_gallery( $imagetmp->galleryid );
		$picture = $imagetmp->filename;
		$alttext = $imagetmp->alttext;
		//$original_path = $gallery->path;
		switch_to_blog( self::MASTER );
		$wpdb->nggallery = $wpdb->prefix . 'ngg_gallery';
		$wpdb->nggpictures = $wpdb->prefix . 'ngg_pictures';
		$query = "
			SELECT GID 
			FROM $wpdb->nggallery 
			WHERE 
				title='$gallery->title'
			AND path='$gallery->path' 
			AND author='$gallery->author' 
			LIMIT 1;
		";
		$galleryID = $wpdb->get_var( $query );
		$pic_id = nggdb::add_image( $galleryID, $picture, '', $alttext );
		//$gallery = nggdb::find_gallery( $galleryID );
		//$destination_path = $gallery->path;
		/**
		copy( 
			dirname( WP_CONTENT_DIR ) . '/' . $original_path . '/' . $picture,
			dirname( WP_CONTENT_DIR ) . '/' . $destination_path . '/' . $picture
		);
		**/
		restore_current_blog();
		$wpdb->nggallery = $wpdb->prefix . 'ngg_gallery';
		$wpdb->nggpictures = $wpdb->prefix . 'ngg_pictures';
	}
	
	/**
    function add_gallery( $title = '', $path = '', $description = '', $pageid = 0, $previewpic = 0, $author = 0  ) {
        global $wpdb;
       
        echo 'Gallery: ' . $wpdb->nggallery . '<br/>';
        // slug must be unique, we use the title for that        
        $slug = nggdb::get_unique_slug( sanitize_title( $title ), 'gallery' );
		
        // Note : The field 'name' is deprecated, it's currently kept only for compat reason with older shortcodes, we copy the slug into this field
		if ( false === $wpdb->query( $wpdb->prepare("INSERT INTO $wpdb->nggallery (name, slug, path, title, galdesc, pageid, previewpic, author) 
													 VALUES (%s, %s, %s, %s, %s, %d, %d, %d)", $slug, $slug, $path, $title, $description, $pageid, $previewpic, $author ) ) ) {
			return false;
		}
		
		$galleryID = (int) $wpdb->insert_id;
         
		//and give me the new id		
		return $galleryID;
    }
    **/
}
?>