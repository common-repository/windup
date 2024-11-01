<?php
/*
Plugin Name: Windup
Plugin URI: http://log-this.com/2011/01/wordpress-plugin-zajawki-postow/
Description: Make sumaries of post at main site automaticaly. Just type number of letters, or words to cut your post. You can still use &lt;!--more--&gt;, and excerpt;
Version: 1.0
Author: Logos
Author URI: http://log-this.com
License: GPLv2
*/

/*  Copyright 2011  Logos  (email : logosexe@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
define('Windup_Version', '1.0');


class windup
{
	var $defaultsettings = array();

function __construct() {
		if ( ! function_exists( 'esc_html' ) )
			return;
		
			add_action('the_content', array(&$this,'wind'),0);


			add_action( 'admin_init',array(&$this, 'register_setting') );
			add_action( 'admin_menu',array(&$this, 'register_settings_page') );
			add_filter( 'plugin_action_links',array(&$this, 'settings_link'),10, 2 );

		$this->defaultsettings = array(
			'windlength'=> 400,
			'cut' => 'letters',
			'moretext'=>'Read more...'
		);
		
			}


	function settings_link( $links , $file) {
		static $this_plugin;

		if( empty($this_plugin) )
			$this_plugin = plugin_basename(__FILE__);

		if ( $file == $this_plugin )
			$links[] = '<a href="' . admin_url( 'options-general.php?page=windup' ) . '">' . __( 'Settings', 'windup' ) . '</a>';

		return $links;	
}

	
	function register_settings_page() {
		add_options_page( 'Windup', 'Windup', 'manage_options', 'windup', array(&$this, 'settings_page') );
	}

	function register_setting() {
		register_setting( 'windup_settings', 'windup_settings', array(&$this, 'validate_settings') );
	}



	function validate_settings( $settings ) {

		if ( !empty($_POST['windup-defaults']) ) {
			$settings = $this->defaultsettings;
		} else {
		
		
		}
		return $settings;
	}


	function wind()
	{
		if((!is_single() AND !is_page()) OR is_archive() OR is_category())
		{
			global $post;

			$link = get_post_permalink($id);
			$ln = get_option('windup_settings');
			$exc = $post->post_excerpt;
			if($exc!='')
			{
				echo $exc,'... '.'<a href="'.$link.'">'.$ln['moretext'].'</a>';
			}
			else
			{
				$cont = get_the_content();
				

				$more = strpos($post->post_content,'<!--more-->');
				if($more != 0)
				{

					echo substr($cont,0,$more),'... '.'<a href="'.$link.'">'.$ln['moretext'].'</a>';
				}
				else
				{
						
					$imgstart = strpos($cont, '<img');
					$imgend = strpos($cont, '/>');
					$img = $imgstart + $imgend;

					if($ln['cut']=='letters')
					{
						if(strlen($cont)<$ln['windlength'])
						{
							echo $cont;
						}
						else
						{
							$cont = substr($cont,0,$img+$ln['windlength']);
							echo $cont.'... '.'<a href="'.$link.'">'.$ln['moretext'].'</a>';
						}

					}
					else
					{
						$words = explode(' ', $cont);
						$wordperimg = substr($cont, $imgstart, $imgend);
						$wordperimg = explode(' ',$wordperimg);
						$imgsize = count($wordperimg);
						array_splice($words, $ln['windlength']+$imgsize);

						if(sizeof($words)<$ln['windlength'])
						{
							$cont = implode(' ', $words);
							echo $cont;
						}
						else
						{
							$cont = implode(' ', $words);
							echo $cont.'... '.'<a href="'.$link.'">'.$ln['moretext'].'</a>';
						}

					}
				}

			}
		}
		else
		{
			return get_the_content();

		}
	}


	function settings_page() {?>
	<div class="wrap">
	<?php if ( function_exists('screen_icon') ) screen_icon(); ?>
	<h2><?php _e( 'Windup Settings', 'windup' ); ?> v 1.0</h2>
	<p>Check youre theme. In main loop, post have to be displayed by the_content(), otherwise it won't work.</p>
	<p>NOTE: You can still use &lt;!--more--&gt;tag, and manual excerpt.</p>
	<form method="post" action="options.php">
	<?php settings_fields('windup_settings');
	$ln = get_option('windup_settings');
	?>

	<table class="form-table">
		<tr>
			<td style="width:240px">
			By what would you like to cut your posts?
			</td>
<!--		</tr>
		<tr>-->
		<td>
		<select name="windup_settings[cut]" id="windup_cut">
			<option value="word" <?php if($ln['cut']=='word') echo'selected="selected"';?>>word</option>
			<option value="letters" <?php if($ln['cut']=='letters') echo'selected="selected"';?>>letters</option>
		</select>
		</td>
		</tr>
		<tr>
			<td>
				At how many units?
			</td>
			<td><input name="windup_settings[windlength]" id="windup_length" value="<?php echo $ln['windlength'];?>"></td>
		</tr>
		<tr>
			<td>
				What will be your more text?
			</td>
			<td><input name="windup_settings[moretext]" id="windup_moretext" value="<?php $ln = get_option('windup_settings'); echo $ln['moretext'];?>"></td>
		</tr>
		

	</table>

		<p>
<?php

			echo '<input type="submit" name="windup-submit" class="button-primary" value="' . __( 'Save Changes') . '" />' . "\n";
			echo '<input type="submit" name="windup-defaults"  class="button-primary" value="' . __( 'Reset to Defaults', 'syntaxhighlighter' ) . '" />' . "\n";

?>
	</p>
	</form>
	<?php
	}

	


}



add_action( 'init', 'windup' );

function windup() {
	global $windup;
	$windup = new windup();
}
