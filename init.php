<?php
/*
Plugin Name:	Custom Category Templates
Description:	Create and define custom templates for category views just like you do for custom page templates.
Author:			Hassan Derakhshandeh
Version:		0.1
Author URI:		http://tween.ir/


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

	private $textdomain;
	private $template;

	function Custom_Category_Templates() {
		add_action( 'category_add_form_fields', array( &$this, 'add_template_option') );
		add_action( 'category_edit_form_fields', array( &$this, 'edit_template_option') );
		add_action( 'created_category', array( &$this, 'save_option' ), 10, 2 );
		add_action( 'edited_category', array( &$this, 'save_option' ), 10, 2 );
		add_filter( 'category_template', array( &$this, 'category_template' ) );
	}

	function category_template( $template ) {
		$category_templates = get_option( 'category_templates' );
		$category = get_queried_object();
		$id = $category->term_id;
		$this->template = $category_templates[$id];
		if( $this->template && $this->template !== 'default' ) {
			$template = locate_template( $this->template );
			add_filter( 'body_class', array( &$this, 'body_class' ) );
		}
		return $template;
	}

	function body_class( $classes ) {
		$classes[] = 'category-template-' . $this->template;
		return $classes;
	}

	function save_option( $term_id ) {
		if( isset( $_POST['template'] ) ) {
			$template = $_POST['template'];
			$category_templates = get_option( 'category_templates' );
			$category_templates[$term_id] = $_POST['template'];
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

	/**
	 * {@internal Missing Short Description}}
	 *
	 * @since 1.5.0
	 *
	 * @param unknown_type $default
	 */
	function category_templates_dropdown( $default = null ) {
		$templates = $this->get_category_templates();
		ksort( $templates );
		foreach (array_keys( $templates ) as $template )
			: if ( $default == $templates[$template] )
				$selected = " selected='selected'";
			else
				$selected = '';
		echo "\n\t<option value='".$templates[$template]."' $selected>$template</option>";
		endforeach;
	}

	/**
	 * Get the Page Templates available in this theme
	 *
	 * @since 1.5.0
	 *
	 * @return array Key is the template name, value is the filename of the template
	 */
	function get_category_templates() {
		$themes = get_themes();
		$theme = get_current_theme();
		$templates = $themes[$theme]['Template Files'];
		$category_templates = array();

		if ( is_array( $templates ) ) {
			$base = array( trailingslashit(get_template_directory()), trailingslashit(get_stylesheet_directory()) );

			foreach ( $templates as $template ) {
				$basename = str_replace($base, '', $template);

				// don't allow template files in subdirectories
				if ( false !== strpos($basename, '/') )
					continue;

				if ( 'functions.php' == $basename )
					continue;

				$template_data = implode( '', file( $template ));

				$name = '';
				if ( preg_match( '|Category Template:(.*)$|mi', $template_data, $name ) )
					$name = _cleanup_header_comment($name[1]);

				if ( !empty( $name ) ) {
					$category_templates[trim( $name )] = $basename;
				}
			}
		}

		return $category_templates;
	}
}
new Custom_Category_Templates();