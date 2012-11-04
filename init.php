<?php
/*
Plugin Name:    Custom Category Templates
Description:    Create and define custom templates for category views just like you do for custom page templates.
Author:         Hassan Derakhshandeh
Version:        0.2
Author URI:     http://tween.ir/


		* 	Copyright (C) 2011  Hassan Derakhshandeh
		*	http://tween.ir/
		*	hassan.derakhshandeh@gmail.com

		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation; either version 2 of the License, or
		(at your option) any later version.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Custom_Category_Templates {

	var $template;

	function __construct() {
		if( is_admin() ) {
			add_action( 'category_add_form_fields', array( &$this, 'add_template_option') );
			add_action( 'category_edit_form_fields', array( &$this, 'edit_template_option') );
			add_action( 'created_category', array( &$this, 'save_option' ), 10, 2 );
			add_action( 'edited_category', array( &$this, 'save_option' ), 10, 2 );
			add_action( 'delete_category', array( &$this, 'delete_option' ) );
		} else {
			add_filter( 'category_template', array( &$this, 'category_template' ) );
		}
	}

	function category_template( $template ) {
		$category_templates = get_option( 'category_templates', array() );
		$category = get_queried_object();
		$id = $category->term_id;
		$tmpl = locate_template( $category_templates[$id] );
		if( isset( $category_templates[$id] ) && 'default' !== $category_templates[$id] && '' !== $tmpl ) {
			$this->template = $category_templates[$id];
			add_filter( 'body_class', array( &$this, 'body_class' ) );
			return $tmpl;
		}
		return $template;
	}

	function body_class( $classes ) {
		$template = sanitize_html_class( str_replace( '.', '-', $this->template ) );
		$classes[] = 'category-template-' . $template;
		return $classes;
	}

	function save_option( $term_id ) {
		if( isset( $_POST['template'] ) ) {
			$template = trim( $_POST['template'] );
			$category_templates = get_option( 'category_templates', array() );
			if( 'default' == $template ) {
				unset( $category_templates[$term_id] );
			} else {
				$category_templates[$term_id] = $template;
			}
			update_option( 'category_templates', $category_templates );
		}
	}

	function add_template_option() { ?>
		<div class="form-field">
			<label for="template"><?php _e('Template'); ?></label>
			<select name="template" id="template" class="postform">
				<option value='default'><?php _e('Default Template'); ?></option>
				<?php $this->category_templates_dropdown() ?>
			</select>
			<!--<p class="description"><?php _e('Template file to render category archive.'); ?></p>-->
		</div>
	<?php }

	function edit_template_option() {
		$id = $_REQUEST['tag_ID'];
		$templates = get_option( 'category_templates' );
		$template = $templates[$id];
		?>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="template"><?php _e('Template'); ?></label>
			</th>
			<td>
				<select name="template" id="template" class="postform">
					<option value='default'><?php _e('Default Template'); ?></option>
					<?php $this->category_templates_dropdown( $template ) ?>
				</select>
				<!--<p class="description"><?php _e('Template file to render category archive.'); ?></p>-->
			</td>
		</tr>
	<?php }

	function delete_option( $term_id ) {
		$category_templates = get_option( 'category_templates', array() );
		if( isset( $category_templates[$term_id] ) ) {
			unset( $category_templates[$term_id] );
			update_option( 'category_templates', $category_templates );
		}
	}

	/**
	 * Generate the options for the category templates list
	 *
	 * @since 0.1
	 * @return void
	 */
	function category_templates_dropdown( $default = null ) {
		$templates = array_flip( $this->get_category_templates() );
		ksort( $templates );
		foreach( array_keys( $templates ) as $template )
			: if ( $default == $templates[$template] )
				$selected = " selected='selected'";
			else
				$selected = '';
		echo "\n\t<option value='".$templates[$template]."' $selected>$template</option>";
		endforeach;
	}

	/**
	 * Get a list of Category Templates available in the current theme
	 *
	 * @since 0.1
	 * @return array Key is the template name, value is the filename of the template
	 */
	function get_category_templates( $template = null ) {
		$category_templates = array();
		$theme = wp_get_theme( $template );
		$files = (array) $theme->get_files( 'php', 1 );

		foreach ( $files as $file => $full_path ) {
			if ( ! preg_match( '|Category Template:(.*)$|mi', file_get_contents( $full_path ), $header ) )
				continue;
			$category_templates[ $file ] = _cleanup_header_comment( $header[1] );
		}

		if ( $theme->parent() )
			$category_templates += $this->get_category_templates( $theme->get_template() );

		return $category_templates;
	}
}
$custom_category_templates = new Custom_Category_Templates();