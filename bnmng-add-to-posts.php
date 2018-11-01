<?php
/*
Plugin Name: bnmng Add to Posts
Description: Add text to displayed post content
Version 1.0
Author: bnmng
Author URI: https://bnmng.com
Text Domain: bnmng-add-to-posts
Licence: GPL2
*/

/*This is just for troubleshooting*/
if( !function_exists( 'bnmng_echo' ) ) {
function bnmng_echo ( $verb, $line='' ) {
	echo '<pre>';
	if ( $line > '' ) {
		echo $line;
	}
	echo $verb,  "\n", '</pre>';
}
}

if( !function_exists( 'bnmng_assign_category_lineage' ) ) {
function bnmng_assign_category_lineage( $categories ) {

	foreach( $categories as &$category ) {
		if( $category->parent == 0 ) {
			$category->lineage = $category->name . '[' . $category->term_id . ']';
		} else {
			$found_parent = false;
			foreach( $categories as $find_parent ) {
				if( $find_parent->term_id == $category->parent ) {
					$found_parent = true;
					break;
				}
			}
			if( !$found_parent ) {
				$category->lineage = $category->name . '[' . $category-term_id . ']';
			}
		}
	}
	unset ( $category );

	$more_to_check = true;
	while ( $more_to_check ) {
		$more_to_check = false;
		foreach( $categories as &$category ) {
			if( !$category->lineage > '' ) {
				$more_to_check = true;
			} else {
				foreach( $categories as &$find_child ) {
					if( $find_child->parent == $category->term_id ) {
						$find_child->lineage = $category->lineage . '_' . $find_child->name . '[' . $find_child->term_id . ']';
						$find_child->prefix = $category->prefix . '-';
					}
				}
			}
		}
		unset ( $category );
	}

	array_multisort( array_column( $categories, 'lineage' ), SORT_ASC, $categories );

	return $categories;

}
}

function bnmng_add_to_posts($content) {

	$post = get_post();
	
	$post_category_ids = array_column( get_the_category(), 'term_id' );

	$option_name = 'bnmng_add_to_posts';
	$options = get_option( $option_name );
	$add_to_beginning = '';
	$add_to_end = '';
	$qty_instances = count( $options['instances'] );
	for( $each_instance = 0; $each_instance < $qty_instances; $each_instance++ ) {

		if( $options['instances'][ $each_instance ]['singular'] && !is_singular() ) {
			continue;
		}

		if( !in_array( $post->post_type, $options['instances'][ $each_instance ]['post_types'] ) ) {
			continue;
		}
	
		if( is_object_in_taxonomy( $post->post_type, 'category' ) ) {
			$opt_categories = $options['instances'][ $each_instance ]['categories'];
			foreach( $opt_categories as $opt_category ) {
				if( !in_array( $opt_category, $post_category_ids ) ) {
					continue 2;
				}
			}
		}
		
		if( post_type_supports( $post->post_type, 'author' ) ) {
			if( $options['instances'][ $each_instance ]['author'] > 0 ) {
				if( !($options['instances'][ $each_instance ]['author'] == get_the_author_meta( 'ID' ) ) ) {
					continue;
				}
			}
		}

		$add_to_beginning .= ( stripslashes( $options['instances'][ $each_instance ][ 'at_beginning' ] ) );
		$add_to_end = ( stripslashes( $options['instances'][ $each_instance ][ 'at_end' ] ) ) . $add_to_end;


	}
	$content = $add_to_beginning . $content . $add_to_end;
	return $content;
}


add_filter( 'the_content', 'bnmng_add_to_posts' );

function bnmng_add_to_posts_menu() {
		add_options_page( 'Add to Posts Options', 'Add to Posts', 'manage_options', 'bnmng-add-to-posts', 'bnmng_add_to_posts_options' );
}
add_action( 'admin_menu', 'bnmng_add_to_posts_menu' );

function bnmng_add_to_posts_options() {
	if( !current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	$intro = '';
	$intro .= '<p>This plugin adds text to the beginning and end of a post when the post is displayed. ';
	$intro .= 'It does not alter the post in the database.</p>';
	$intro .= '<p>HTML and shortcodes may be used.  Tags opened in the beginning can be closed at the end. ';
	$intro .= 'End of post content is added in reverse order for proper nesting. </p>';

	$option_name = 'bnmng_add_to_posts';
	$text_domain = 'bnmng-add-to-posts';
	$instance_label = 'Instance %1$d';
	$delete_label = 'Delete this Instance';
	$delete_help = '';
	$move_label = 'Move';
	$move_help = '';
	$post_types_label = 'Post Types';
	$post_types_help = 'The type of posts to have the text added. You can add post types to this list using the Additional Post Types box below.  ';
	$post_types_help .= 'This plugin should work as expected for posts and pages.  Other post types should be tested.   ';
	$singular_label = 'Singular View Only';
	$singular_help = 'If checked, only add text to single-post views.  Otherwise, add to single-posts and lists';
	$categories_label = 'Categories';
	$categories_help = 'The categories of posts to have the text added.  If more than one is selected, text will be added <em>only</em> to posts of <em>all</em> selected categories.';
	$categories_help .= 'Categories only apply to posts of type "post".';
	$author_label = 'Author';
	$author_help = 'The author of posts to have the text added.';
	$beginning_label = 'Add to Beginning of Post';
	$beginning_help = 'The text to add at the beginning of the post.  If you use HTML, ensure the valididy of your code.  Tags can be closed at the end of post';
	$end_label = 'Add to End of Post';
	$end_help = 'The text to add at the end of the post.  If you use HTML, ensure the valididy of your code.';
	$controlname_pat = $option_name . '[instances][%1$d][%2$s]';
	$controlid_pat = $option_name . '_%1$d_%2$s';
	$global_controlname_pat = $option_name . '[%1$s]';
	$global_controlid_pat = $option_name . '_%1$s';
	
	$all_categories = bnmng_assign_category_lineage( get_categories( array( 'hide_empty'=>0) ) );

	$all_authors = get_users();

	$each_instance = 0;
	$form_output = '';
	if( isset($_POST['submit'] ) ) {
		$options = [];
		$qty_post_instances = count($_POST[ $option_name ]['instances'] );
		for( $each_post_instance = 0; $each_post_instance < $qty_post_instances; $each_post_instance++ ) {
			$save_instance = true;
			if( isset( $_POST[ $option_name ]['instances'][ $each_post_instance ]['delete']  ) ) {
				$save_instance = false;
			} elseif ( !( $_POST[ $option_name ]['instances'][ $each_post_instance ]['at_beginning'] > '' || $_POST[ $option_name ]['instances'][ $each_post_instance ]['at_end'] > '' ) ) {
				$save_instance = false;
			}
			if ( $save_instance ) {
					$options['instances'][ $each_instance ]['categories']=$_POST[ $option_name ]['instances'][ $each_post_instance ]['categories'];
					$options['instances'][ $each_instance ]['author']=$_POST[ $option_name ]['instances'][ $each_post_instance ]['author'];
					$options['instances'][ $each_instance ]['post_types']=$_POST[ $option_name ]['instances'][ $each_post_instance ]['post_types'];
					$options['instances'][ $each_instance ]['singular']=$_POST[ $option_name ]['instances'][ $each_post_instance ]['singular'];
					$options['instances'][ $each_instance ]['at_beginning']=$_POST[ $option_name ]['instances'][ $each_post_instance ]['at_beginning'];
					$options['instances'][ $each_instance ]['at_end']=$_POST[ $option_name ]['instances'][ $each_post_instance ]['at_end'];
					$each_instance++;
			}
		}
		$options['additional_post_types']=$_POST[ $option_name ]['additional_post_types'];
		update_option( $option_name,  $options );
	}
	$options = get_option( $option_name );

	$additional_post_types = explode(';',$options['additional_post_types']);

	for( $each_post_type = 0; $each_post_type < count( $additional_post_types ); $each_post_type++  ) {
		$additional_post_types[ $each_post_type ] = trim( $additional_post_types[ $each_post_type ] );
		if ( $additional_post_types[ $each_post_type ] == '' ) {
			unset( $additional_post_types[ $each_post_type ] );
		}
	}

	$available_post_types = array_merge( ['post','page'], $additional_post_types );
	$post_types_size = min( count( $available_post_types ), 3 );
	
	$qty_instances = count( $options['instances'] ) ;

	for ( $each_instance = 0; $each_instance < $qty_instances; $each_instance++ ) {

		$form_output .= '<div id="div_instance_' . $each_instance . '">' . "\n";
		$form_output .= '<table class="form-table ' . $text_domain . '">' . "\n";
		$form_output .= '<tr><th colspan="2">' . sprintf($instance_label, ( $each_instance + 1 ) ) . '</th></tr>' . "\n";
		$form_output .= '<tr><th>' . $delete_label . '</th><td><div><input type="checkbox" id="' . sprintf( $controlid_pat, $each_instance, 'delete' ) . '" name="' . sprintf( $controlname_pat, $each_instance ,'delete' ) . '"></div><div class="' . $text_domain . '-help">' . $delete_help . '</div></td></tr>' . "\n";
		$form_output .= '<tr><th>' . $move_label . '</th><td><div>' . "\n";
		if( $each_instance > 0 ) {
				$form_output .= '<button type="button" id="' . sprintf( $controlid_pat, $each_instance, 'move_up' ) . '" onclick="' . $option_name . '_move(' . $each_instance . ', \'up\')">Up</button>' . "\n";
		}
		$form_output .= '<button type="button" id="' . sprintf( $controlid_pat, $each_instance, 'move_down' ) . '" onclick="' . $option_name . '_move(' . $each_instance . ', \'down\')">Down</button>' . "\n";
		$form_output .= '</div><div class="' . $text_domain . '-help">' . $move_help . '</div></td></tr>' . "\n";

		$form_output .= '<tr><th>' . $post_types_label . '</th><td><div>';
		$form_output .= '<select id="' . sprintf( $controlid_pat, $each_instance, 'post_types' ) .  '" name="' . sprintf( $controlname_pat, $each_instance, 'post_types' ) . '[]" multiple="multiple" size="' . $post_types_size . '" >';
		foreach( $available_post_types as $post_type ) {
			$form_output .= '<option value="' . $post_type . '"';
			if( in_array( $post_type, $options['instances'][ $each_instance ]['post_types'] ) ) {
				$form_output .= 'selected="selected"';
			}
			$form_output .= '>' . $post_type . '</option>' . "\n";
		}
		$form_output .= '</select></div><div class="' . $text_domain . '-help">' . $post_types_help . '</div></td></tr>' . "\n";
			
		$form_output .= '<tr><th>' . $singular_label . '</th><td><div>';
		$form_output .= '<input type="checkbox" id="' . sprintf( $controlid_pat, $each_instance, 'singular' ) .  '" name="' . sprintf( $controlname_pat, $each_instance, 'singular' ) . '"';
		if( $options['instances'][ $each_instance ]['singular'] ) {
			$form_output .= 'checked="true"';
		}
		$form_output .= '>' . "\n";
		$form_output .= '</select></div><div class="' . $text_domain . '-help">' . $singular_help . '</div></td></tr>' . "\n";

		$form_output .= '<tr><th>' . $categories_label . '</th><td><div>';
		$form_output .= '<select id="' . sprintf( $controlid_pat, $each_instance, 'categories' ) . '" name="' . sprintf( $controlname_pat, $each_instance, 'categories') . '[]" multiple="multiple" size="3" >';
		foreach( $all_categories as $category ) {
			$form_output .= '<option value="' . $category->term_id . '"';
			if( in_array( $category->term_id, $options['instances'][ $each_instance ]['categories'] ) ) {
				$form_output .= 'selected="selected"';
			}
			$form_output .= '>' . $category->prefix .  $category->name . '</option>' . "\n";
		}
		$form_output .= '</select></div><div class="' . $text_domain . '-help">' . $categories_help . '</div></td></tr>' . "\n";

		$form_output .= '<tr><th>' . $author_label . '</th><td><div>';
		$form_output .= '<select id="' . sprintf( $controlid_pat, $each_instance, 'author' ) . '" name="' . sprintf( $controlname_pat, $each_instance, 'author') . '" size="3" >';
		$form_output .= '<option value="0">[any author]</option>' . "\n";
		foreach( $all_authors as $author ) {
			$form_output .= '<option value="' . $author->ID . '"';
			if( $author->ID == $options['instances'][ $each_instance ]['author'] ) {
				$form_output .= 'selected="selected"';
			}
			$form_output .= '>' . $author->display_name . '</option>' . "\n";
		}
		$form_output .= '</select></div><div class="' . $text_domain . '-help">' . $author_help . '</div></td></tr>' . "\n";

		$form_output .= '<tr><th>' . $beginning_label . '</th><td><div><textarea id="' . sprintf( $controlid_pat, $each_instance, 'at_beginning' ) . '" name="' . sprintf( $controlname_pat, $each_instance, 'at_beginning' ) . '">' . stripslashes( $options['instances'][ $each_instance ]['at_beginning'] ) . '</textarea></div><div class="' . $text_domain . '-help">' . $beginning_help . '</div></td></tr>' . "\n";
		$form_output .= '<tr><th>' . $end_label . '</th><td><div><textarea id="' . sprintf( $controlid_pat, $each_instance, 'at_end' ) . '" name="' . sprintf( $controlname_pat, $each_instance, 'at_end' ) . '">' . stripslashes( $options['instances'][ $each_instance ]['at_end'] ) . '</textarea></div><div class="' . $text_domain . '-help">' . $end_help . '</div></td></tr>' . "\n";
		$form_output .= '</table>' . "\n";
		$form_output .= '</div>' . "\n";
	}
	$form_output .= '<div id="div_instance_' . $each_instance . '">';
	$form_output .= '<table class="form-table ' . $text_domain . '">' . "\n";
	$form_output .= '<tr><th colspan="2">New Instance</th></tr>' . "\n";
	if( $each_instance > 0 ) {
			$form_output .= '<tr><th>' . $move_label . '</th><td><div>' . "\n";
			$form_output .= '<button type="button" id="' . sprintf( $controlid_pat, $each_instance, 'move_up' ) . '" onclick="' . $option_name . '_move(' . $each_instance . ', \'up\')">Up</button>' . "\n";
			$form_output .= '</div><div class="' . $text_domain . '-help">' . $move_help . '</div></td></tr>' . "\n";
	}

	$form_output .= '<tr><th>' . $post_types_label . '</th><td><div>';
	$form_output .= '<select id="' . sprintf( $controlid_pat, $each_instance, 'post_types' ) . '" name="' . sprintf( $controlname_pat, $each_instance, 'post_types' ) . '[]" multiple="multiple" size="' . $post_types_size . '" >';
	foreach ( $available_post_types as $post_type ) {
		$form_output .= '<option value="' . $post_type . '"';
		if ( $post_type == 'post' ) {
			$form_output .= 'selected="selected"';
		}
		$form_output .= '>' . $post_type . '</option>' . "\n";
	}
	$form_output .= '</select></div><div class="' . $text_domain . '-help">' . $post_types_help . '</div></td></tr>' . "\n";

	$form_output .= '<tr><th>' . $singular_label . '</th><td><div>';
	$form_output .= '<input type="checkbox" id="' . sprintf( $controlid_pat, $each_instance, 'singular' ) . '" name="' . sprintf( $controlname_pat, $each_instance, 'singular' ) . '" checked="true" >';
	$form_output .= '</select></div><div class="' . $text_domain . '-help">' . $singular_help . '</div></td></tr>' . "\n";

	$form_output .= '<tr><th>' . $categories_label . '</th><td><div>';
	$form_output .= '<select id="' . sprintf( $controlid_pat, $each_instance, 'categories' ) . '" name="' . sprintf( $controlname_pat, $each_instance, 'categories') . '[]" multiple="multiple" size="3" >';
	foreach ( $all_categories as $category ) {
		$form_output .= '<option value="' . $category->term_id . '"';
		if ( $category->term_id == '0' ) {
			$form_output .= 'selected="selected"';
		}
		$form_output .= '>' . $category->prefix . $category->name . '</option>' . "\n";
	}
	$form_output .= '</select></div><div class="' . $text_domain . '-help">' . $categories_help . '</div></td></tr>' . "\n";

	$form_output .= '<tr><th>' . $author_label . '</th><td><div>';
	$form_output .= '<select id="' . sprintf( $controlid_pat, $each_instance, 'author' ) . '" name="' . sprintf( $controlname_pat, $each_instance, 'author') . '" size="3" >';
	$form_output .= '<option value="0">[any author]</option>' . "\n";
	foreach ( $all_authors as $author ) {
		$form_output .= '<option value="' . $author->ID . '"';
		$form_output .= '>' . $author->display_name . '</option>' . "\n";
	}
	$form_output .= '</select></div><div class="' . $text_domain . '-help">' . $author_help . '</div></td></tr>' . "\n";

	$form_output .= '<tr><th>' . $beginning_label . '</th><td><div><textarea id="' . sprintf( $controlid_pat, $each_instance, 'at_beginning' ) .'" name="' . sprintf( $controlname_pat, $each_instance, 'at_beginning' ) . '"></textarea></div><div class="' . $text_domain . '-help">' . $beginning_help . '</div></td></tr>' . "\n";
	$form_output .= '<tr><th>' . $end_label . '</th><td><div><textarea id="' . sprintf( $controlid_pat, $each_instance, 'at_end' ) . '" name="' . sprintf( $controlname_pat, $each_instance, 'at_end' ) . '"></textarea></div><div class="' . $text_domain . '-help">' . $end_help . '</div></td></tr>' . "\n";
	$form_output .= '</table>' . "\n";
	$form_output .= '</div>' . "\n";

	$form_output .= '<div id="global">' . "\n";
	$form_output .= '<table class="form-table ' . $text_domain . '">' . "\n";
	$form_output .= '<tr><th colspan="2">Global Settings</th></tr>' . "\n";
	$form_output .= '<tr><th>Additional Post Types</th><td><div><input id="' . sprintf( $global_controlid_pat, 'additional_post_types' ) . '" name="' . sprintf( $global_controlname_pat, 'additional_post_types' ) . '" value="' . stripslashes( $options['additional_post_types'] ) . '"></div><div class="' . $text_domain . '-help">List additional post types to be made available in the Post Types selections.  Separate the items with semicolons(;). This plugin may not work correctly for all additional post types.</div></td></tr>' . "\n";
	$form_output .= '</table>' . "\n";
	$form_ouptut .= '</div>' . "\n";

	echo '<div class = "wrap">' . "\n";
	echo $intro;
	echo '<form method = "POST" action="?page=' . $text_domain . '">' . "\n";
	echo $form_output;
	submit_button();
	echo '</form>' . "\n";
	echo '</div>' . "\n";
}
add_action('admin_head-settings_page_bnmng-add-to-posts', 'bnmng_admin_style');

function bnmng_admin_style() {
		$text_domain = 'bnmng-add-to-posts';
		$style='
	table.' . $text_domain . ' {
		border: 1px solid black;
	}
	table.' . $text_domain . ' th {
		padding-left: 1em;
	}
	table.' . $text_domain . ' textarea {
		width:100%;
	}
';
		echo '<style>' . $style . '</style>';
}
add_action('admin_head-settings_page_bnmng-add-to-posts', 'bnmng_admin_script');

function bnmng_admin_script() {
		$option_name = 'bnmng_add_to_posts';
		$script='
		
		function ' . $option_name . '_move( instance, direction ) {
			var store=[];

			store["post_types_selected"] = [];
			var post_types_options = document.getElementById("' . $option_name . '_" + instance + "_post_types").options;
			for( i=0; i < post_types_options.length; i++ ) {
				if( post_types_options[i].selected ) {
					store["post_types_selected"].push( post_types_options[i].value );
				}
			}

			store["categories_selected"] = [];
			var categories_options = document.getElementById("' . $option_name . '_" + instance + "_categories").options;
			for( i=0; i < categories_options.length; i++ ) {
				if( categories_options[i].selected ) {
					store["categories_selected"].push( categories_options[i].value);
				}
			}


			store["author"] = document.getElementById("' . $option_name . '_" + instance + "_author").checked;
			store["singular"] = document.getElementById("' . $option_name . '_" + instance + "_singular").checked;
			
			store["at_beginning"] = document.getElementById("' . $option_name . '_" + instance + "_at_beginning").value;
			store["at_end"] = document.getElementById("' . $option_name . '_" + instance + "_at_end").value;

			if( direction == "up" ) {
				var other_instance = instance - 1;
			} else {
				var other_instance = instance + 1;
			}

			
			var instance_post_types_options = document.getElementById("' . $option_name . '_" + instance + "_post_types").options;
			var other_instance_post_types_options = document.getElementById("' . $option_name . '_" + other_instance + "_post_types").options;
			for( i=0; i < instance_post_types_options.length; i++ ) {
				instance_post_types_options[i].selected = false;;
				for ( o=0; o < other_instance_post_types_options.length; o++) {
					if( instance_post_types_options[i].value == other_instance_post_types_options[o].value && other_instance_post_types_options[o].selected ) {
						instance_post_types_options[i].selected = true;
					}
				}
			}
			var instance_categories_options = document.getElementById("' . $option_name . '_" + instance + "_categories").options;
			var other_instance_categories_options = document.getElementById("' . $option_name . '_" + other_instance + "_categories").options;
			for( i=0; i < instance_categories_options.length; i++ ) {
				instance_categories_options[i].selected = false;;
				for ( o=0; o < other_instance_categories_options.length; o++) {
					if( instance_categories_options[i].value == other_instance_categories_options[o].value && other_instance_categories_options[o].selected ) {
						instance_categories_options[i].selected = true;
					}
				}
			}

			document.getElementById("' . $option_name . '_" + instance + "_author").checked = document.getElementById("' . $option_name . '_" + other_instance + "_author").checked;
			document.getElementById("' . $option_name . '_" + instance + "_singular").checked = document.getElementById("' . $option_name . '_" + other_instance + "_singular").checked;

			document.getElementById("' . $option_name . '_" + instance + "_at_beginning").value = document.getElementById("' . $option_name . '_" + other_instance + "_at_beginning").value;
			document.getElementById("' . $option_name . '_" + instance + "_at_end").value = document.getElementById("' . $option_name . '_" + other_instance + "_at_end").value;


			var other_instance_post_types_options = document.getElementById("' . $option_name . '_" + other_instance + "_post_types").options;
			for( o=0; o < other_instance_post_types_options.length; o++ ) {
				other_instance_post_types_options[o].selected = false;
				for ( i=0; i < store["post_types_selected"].length; i++) {
					if( other_instance_post_types_options[o].value == store["post_types_selected"][i] ) {
						other_instance_post_types_options[o].selected = true;
					}
				}
			}
			
			var other_instance_categories_options = document.getElementById("' . $option_name . '_" + other_instance + "_categories").options;
			for( o=0; o < other_instance_categories_options.length; o++ ) {
				other_instance_categories_options[o].selected = false;
				for ( i=0; i < store["categories_selected"].length; i++) {
					if( other_instance_categories_options[o].value == store["categories_selected"][i] ) {
						other_instance_categories_options[o].selected = true;
					}
				}
			}

			document.getElementById("' . $option_name . '_" + other_instance + "_author").checked = store["author"];
			document.getElementById("' . $option_name . '_" + other_instance + "_singular").checked = store["singular"];

			document.getElementById("' . $option_name . '_" + other_instance + "_at_beginning").value = store["at_beginning"];
			document.getElementById("' . $option_name . '_" + other_instance + "_at_end").value = store["at_end"];
	}

';
		echo '<script type="text/javascript">' . $script . '</script>';

}
