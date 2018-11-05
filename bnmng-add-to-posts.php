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


if( !function_exists( 'bnmng_assign_taxonomy_lineage' ) ) {
function bnmng_assign_taxonomy_lineage( $terms ) {

	foreach( $terms as &$term ) {
		
		if( $term->parent == 0 ) {
			$term->lineage = $term->name . '[' . $term->term_id . ']';
		} else {
			$found_parent = false;
			foreach( $terms as $find_parent ) {
				if( $find_parent->term_id == $term->parent ) {
					$found_parent = true;
					break;
				}
			}
			if( !$found_parent ) {
				$term->lineage = $term->name . '[' . $term->term_id . ']';
			}
		}
	}
	unset ( $term );

	$more_to_check = true;
	while ( $more_to_check ) {
		$more_to_check = false;
		foreach( $terms as &$term ) {
			if( !$term->lineage > '' ) {
				$more_to_check = true;
			} else {
				foreach( $terms as &$find_child ) {
					if( $find_child->parent == $term->term_id ) {
						$find_child->lineage = $term->lineage . '_' . $find_child->name . '[' . $find_child->term_id . ']';
						$find_child->prefix = $term->prefix . '-';
					}
				}
			}
		}
		unset ( $term );
	}

	array_multisort( array_column( $terms, 'lineage' ), SORT_ASC, $terms );

	return $terms;

}
}

function bnmng_add_to_posts($content) {

	$post = get_post();
	

	$option_name = 'bnmng_add_to_posts';
	$options = get_option( $option_name );
	$add_to_beginning = '';
	$add_to_end = '';
	$qty_instances = count( $options['instances'] );
	for( $each_instance = 0; $each_instance < $qty_instances; $each_instance++ ) {
		if( $options['instances'][ $each_instance ]['singular'] && !is_singular() ) {
			continue;
		}

		if( !( $post->post_type == $options['instances'][ $each_instance ]['post_type'] ) ) {
			continue;
		}
	
		$taxonomies = get_post_taxonomies( $post );
		foreach( $taxonomies as $taxonomy ) {
			$opt_terms = $options['instances'][ $each_instance ]['taxonomies'][ $taxonomy ];
			if( count( $opt_terms ) ) {
				$terms = get_the_terms( $post, $taxonomy );
				if( count( $terms ) ) {
					$term_ids = array_column( $terms, 'term_id' );
					foreach( $opt_terms as $opt_term ) {
						if( !in_array( $opt_term, $term_ids ) ) {
							continue 2;
						}
					}
				} else {
					continue;
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
	$move_label = 'Move or Delete';
	$move_help = '';
	$post_type_label = 'Post Type';
	$post_type_help = 'To add a new instance, select the appropriate post type and click "Save Changes"  '; 
	$post_type_help .= 'This plugin should work as expected for posts and pages, and may work for some types of posts that you add. ';
	$singular_label = 'Singular View Only';
	$singular_help = 'If checked, only add text to single-post views.  Otherwise, add to single-posts and lists';
	$taxonomies_label = '%1$s';
	$taxonomies_help = 'The %1$s of posts to have the text added.  If more than one is selected, text will be added <em>only</em> to posts of <em>all</em> selected %1$s.';
	$author_label = 'Author';
	$author_help = 'The author of posts to have the text added.';
	$beginning_label = 'Add to Beginning of Post';
	$beginning_help = 'The text to add at the beginning of the post.  If you use HTML, ensure the valididy of your code.  Tags can be closed at the end of post';
	$end_label = 'Add to End of Post';
	$end_help = 'The text to add at the end of the post.  If you use HTML, ensure the valididy of your code.';
	$new_instance_label = 'Add a new instance';
	$new_instance_help = 'To add a new instance, check this box and click "Save Changes".  ';
	$new_instance_help = 'To add for a post type other than "post", select the post type below before clicking "Save Changes".  ';
	$new_instance_help = 'To add for a post type not in the list, select "New Post Type: " and type in the name of the new post type. ';
	$controlname_pat = $option_name . '[instances][%1$d][%2$s]';
	$controlid_pat = $option_name . '_%1$d_%2$s';
	$global_controlname_pat = $option_name . '[%1$s]';
	$global_controlid_pat = $option_name . '_%1$s';
	
	$available_post_types=['post', 'page'];
	$taxonomies = array();

	$all_authors = get_users();

	$each_instance = 0;

	echo "\n";
	echo '<div class = "wrap">', "\n";
	echo '  <div class="', $text_domain, '-into">', $intro, '</div>', "\n";
	echo '  <form method = "POST" action="?page=', $text_domain, '">', "\n";

	if( isset($_POST['submit'] ) ) {
		$options = [];
		$swaps = [];
		$qty_post_instances = count($_POST[ $option_name ]['instances'] );
		for( $each_post_instance = 0; $each_post_instance < $qty_post_instances; $each_post_instance++ ) {
			$save_instance = true;
			if( $_POST[ $option_name ]['instances'][ $each_post_instance ]['move'] == 'delete' ) {
				$save_instance = false;
			}
			if ( $save_instance ) {
				$swaps[ $each_instance ] = 0;
				if( $_POST[ $option_name ]['instances'][ $each_post_instance + 1 ]['move'] == 'down' || $_POST[ $option_name ]['instances'][ $each_post_instance + 1 ]['move'] == 'up' ) {
					$swaps[ $each_instance ] = 1;
					$swaps[ $each_instance + 1 ] = -1;
				}
				
				$options['instances'][ $each_instance + $swaps[ $each_instance] ]['post_type'] = sanitize_key( $_POST[ $option_name ]['instances'][ $each_post_instance ]['post_type'] );
				if( !in_array( $options['instances'][ $each_instance + $swaps[ $each_instance ] ]['post_type'], $available_post_types ) ) {
					$available_post_types[] = $options['instances'][ $each_instance + $swaps[ $each_instance ] ]['post_type'];
				}
				$options['instances'][ $each_instance + $swaps[ $each_instance ] ]['taxonomies'] = $_POST[ $option_name ]['instances'][ $each_post_instance ]['taxonomies'];
				$options['instances'][ $each_instance + $swaps[ $each_instance ] ]['author'] = $_POST[ $option_name ]['instances'][ $each_post_instance ]['author'];
				$options['instances'][ $each_instance + $swaps[ $each_instance ] ]['singular'] = $_POST[ $option_name ]['instances'][ $each_post_instance ]['singular'];
				$options['instances'][ $each_instance + $swaps[ $each_instance ] ]['at_beginning'] = $_POST[ $option_name ]['instances'][ $each_post_instance ]['at_beginning'];
				$options['instances'][ $each_instance + $swaps[ $each_instance ] ]['at_end'] = $_POST[ $option_name ]['instances'][ $each_post_instance ]['at_end'];
				$each_instance++;
			}
		}
		if( $_POST[ $option_name ]['new_instance_post_type'] ) {
			bnmng_echo ( 'new post type=' . ( $_POST[ $option_name ]['new_post_type'] ) );
			bnmng_echo ( 'sanized new post type=' . sanitize_key( $_POST[ $option_name ]['new_post_type'] ) );
			$save_instance = true;
			if( $_POST[ $option_name ]['new_instance_post_type'] == '+' ) {
				$new_instance_post_type = $_POST[ $option_name ]['new_post_type'];
			} else {
				$new_instance_post_type = $_POST[ $option_name ]['new_instance_post_type'];
			}
			if( !($new_instance_post_type > '' && $new_instance_post_type == sanitize_key( $new_instance_post_type ) ) ) {
				$save_instance = false;
			}
			if( $save_instance ) {
				$options['instances'][ $each_instance ]['post_type']=$new_instance_post_type;
				if( !in_array( $options['instances'][ $each_instance ]['post_type'], $available_post_types ) ) {
					$available_post_types[] = $options['instances'][ $each_instance ]['post_type'];
				}
				bnmng_echo ( 'Checking if new_instance_post_type, which is ' . $new_instance_post_type . ' supports author: ' . post_type_supports( $new_instance_post_type, 'author' ) ) ;
				if( post_type_supports( $new_instance_post_type, 'author' ) ) {
					$options['instances'][ $each_instance + $swaps[ $each_instance ] ]['author'] = 0;
				}
				$options['instances'][ $each_instance + $swaps[ $each_instance ] ]['singular'] = 'Checked';
				$options['instances'][ $each_instance + $swaps[ $each_instance ] ]['at_beginning'] = '';
				$options['instances'][ $each_instance + $swaps[ $each_instance ] ]['at_end'] = '';
			}
		}
		update_option( $option_name,  $options );
	}
	$options = get_option( $option_name );

	$post_types_size = min( count( $available_post_types ), 3 );
	
	$qty_instances = count( $options['instances'] ) ;

	for ( $each_instance = 0; $each_instance < $qty_instances; $each_instance++ ) {

		echo '    <div id="div_instance_', $each_instance, '">', "\n";
		echo '      <table class="form-table ', $text_domain, '">', "\n";
		echo '        <tr>', "\n";
		echo '          <th colspan="2">', sprintf($instance_label, ( $each_instance + 1 ) ), '</th>', "\n";
		echo '        </tr>', "\n";
		echo '        <tr>', "\n";
		echo '          <th>', $move_label, '</th>', "\n";
		echo '          <td>', "\n";

		echo '            <table class="form-table ', $text_domain, '">', "\n";

		echo '               <tr>', "\n";
		echo '                 <td>none</td>', "\n";
		echo '                 <td>', "\n";
		echo '                    <input type="radio" id="', sprintf( $controlid_pat, $each_instance, 'move_none' ), '" name="', sprintf( $controlname_pat, $each_instance, 'move' ), '" value="" checked="checked" >', "\n";
		echo '                 </td>', "\n";
		echo '                 <td>Don\'t move or delete this instance</td>', "\n";
		echo '               </tr>', "\n";

		if( $each_instance > 0 ) {
			echo '               <tr>', "\n";
			echo '                 <td>up</td>', "\n";
			echo '                 <td>', "\n";
			echo '                    <input type="radio" id="', sprintf( $controlid_pat, $each_instance, 'move_up' ), '" name="', sprintf( $controlname_pat, $each_instance, 'move' ), '" value="up">', "\n";
			echo '                 </td>', "\n";
			echo '                 <td>Move this instance up</td>', "\n";
			echo '               </tr>', "\n";
		}

		if( $each_instance < ( $qty_instances - 1) ) {
			echo '               <tr>', "\n";
			echo '                 <td>down</td>', "\n";
			echo '                 <td>', "\n";
			echo '                    <input type="radio" id="', sprintf( $controlid_pat, $each_instance, 'move_down' ), '" name="', sprintf( $controlname_pat, $each_instance, 'move' ), '" value="up">', "\n";
			echo '                 </td>', "\n";
			echo '                 <td>Move this instance down</td>', "\n";
			echo '               </tr>', "\n";
		}

		echo '               <tr>', "\n";
		echo '                 <td>delete</td>', "\n";
		echo '                 <td>', "\n";
		echo '                    <input type="radio" id="', sprintf( $controlid_pat, $each_instance, 'move_delete' ), '" name="', sprintf( $controlname_pat, $each_instance, 'move' ), '" value="delete">', "\n";
		echo '                 </td>', "\n";
		echo '                 <td>Delete this instance</td>', "\n";
		echo '               </tr>', "\n";


		echo '             </table>', "\n";








		echo '            <div class="', $text_domain, '-help">', $move_help, '</div>', "\n";
		echo '          </td>', "\n";
		echo '        </tr>', "\n";

		echo '        <tr>', "\n";
		echo '          <th>', $post_type_label, '</th>', "\n";
		echo '          <td>', "\n";
		echo '            <div>';
		echo '    		  ', $options['instances'][ $each_instance ]['post_type'], "\n";
		echo '    		  ', '<input type="hidden" id="', sprintf( $controlid_pat, $each_instance, 'post_type' ), '" name="', sprintf( $controlname_pat, $each_instance, 'post_type' ), '" value="', $options['instances'][ $each_instance ]['post_type'], '">',  "\n";
		echo '            </div>', "\n";
		echo '          </td>', "\n";
		echo '        </tr>', "\n";
			
		echo '        <tr>', "\n";
		echo '          <th>', $singular_label, '</th>', "\n";
		echo '          <td>', "\n";
		echo '            <div>', "\n";
		echo '              <input type="checkbox" id="', sprintf( $controlid_pat, $each_instance, 'singular' ),  '" name="', sprintf( $controlname_pat, $each_instance, 'singular' ), '"';
		if( $options['instances'][ $each_instance ]['singular'] ) {
			echo '    checked="true"';
		}
		echo '    >', "\n";
		echo '            </div>', "\n";
		echo '            <div class="', $text_domain, '-help">', $singular_help, '</div>', "\n";
		echo '          </td>', "\n";
		echo '        </tr>', "\n";

		if( !isset( $taxonomies[ $options['instances'][ $each_instance ][ 'post_type' ] ] ) ) {
			$taxonomies[ $options['instances'][ $each_instance ][ 'post_type' ] ] = get_object_taxonomies( $options['instances'][ $each_instance ][ 'post_type' ], 'objects' ) ;
			foreach( $taxonomies[ $options['instances'][ $each_instance ][ 'post_type' ] ] as $taxonomy ) {
				$terms[ $taxonomy->name ]  = bnmng_assign_taxonomy_lineage( get_terms( $taxonomy->name, array( 'hide_empty'=>0) ) );
			}
		}
		foreach( $taxonomies[ $options['instances'][ $each_instance ][ 'post_type' ] ] AS $taxonomy ) {
			if( count( $terms[ $taxonomy->name ] ) ) {
				echo '        <tr>', "\n";
				echo '          <th>', $taxonomy->label, '</th>', "\n";
				echo '          <td>', "\n";
				echo '            <div>', "\n";
				echo '              <select>', "\n";
				foreach( $terms[ $taxonomy->name ] as $term ) {
					echo '                <option value="', $term->term_id , '">', $term->prefix . $term->name, '</option>', "\n";;
				}
				echo '              </select>', "\n";
				echo '            </div>', "\n";
				echo '            <div class="', $text_domain, '-help">', sprintf( $taxonomies_help, $taxonomy->label ), '</div>', "\n";
				echo '          </td>', "\n";
				echo '        </tr>', "\n";
			}
		}

		echo '        <tr>', "\n";
		echo '          <th>', $author_label, '</th>', "\n";
		echo '          <td>', "\n"; 
		echo '            <div>', "\n";
		echo '              <select id="', sprintf( $controlid_pat, $each_instance, 'author' ), '" name="', sprintf( $controlname_pat, $each_instance, 'author'), '" size="3" >', "\n";
		echo '                <option value="0">[any author]</option>', "\n";
		foreach( $all_authors as $author ) {
			echo '              <option value="', $author->ID, '"';
			if( $author->ID == $options['instances'][ $each_instance ]['author'] ) {
				echo '    selected="selected"';
			}
			echo '    >', $author->display_name, '</option>', "\n";
		}
		echo '              </select>', "\n";
		echo '            </div>', "\n";
		echo '            <div class="', $text_domain, '-help">', $author_help, '</div>', "\n";
		echo '          </td>', "\n";
		echo '        </tr>', "\n";

		echo '        <tr>', "\n"; 
		echo '          <th>', $beginning_label, '</th>', "\n";
		echo '          <td>', "\n";
		echo '            <div>', "\n";
		echo '              <textarea id="', sprintf( $controlid_pat, $each_instance, 'at_beginning' ), '" name="', sprintf( $controlname_pat, $each_instance, 'at_beginning' ), '">', stripslashes( $options['instances'][ $each_instance ]['at_beginning'] ), '</textarea>', "\n";
		echo '             </div>', "\n";
		echo '            <div class="', $text_domain, '-help">', $beginning_help, '</div>', "\n";
		echo '          </td>', "\n";
		echo '        </tr>', "\n";
		echo '        <tr>', "\n"; 
		echo '          <th>', $end_label, '</th>', "\n";
		echo '          <td>', "\n";
		echo '            <div>', "\n";
		echo '              <textarea id="', sprintf( $controlid_pat, $each_instance, 'at_end' ), '" name="', sprintf( $controlname_pat, $each_instance, 'at_end' ), '">', stripslashes( $options['instances'][ $each_instance ]['at_end'] ), '</textarea>', "\n";
		echo '            </div>', "\n";
		echo '            <div class="', $text_domain, '-help">', $end_help, '</div>', "\n";
		echo '          </td>', "\n";
		echo '        </tr>', "\n";
		echo '      </table>', "\n";
		echo '    </div>', "\n";
	}

    /*   'new instance' form */
	echo '    <div id="div_add_instance">', "\n";
	echo '      <table class="form-table ', $text_domain, '">', "\n";
	echo '       <tr>', "\n";
	echo '         <th>Add a New Instance</th>', "\n";
	echo '         <td>', "\n";
	echo '           <table class="form-table ', $text_domain, '">', "\n";
	echo '             <tr>', "\n";
	echo '               <td>none</td>', "\n";
	echo '               <td>', "\n";
	echo '                  <input type="radio" id="', sprintf( $global_controlid_pat, 'new_instance_post_type_none' ), '" name="', sprintf( $global_controlname_pat, 'new_instance_post_type' ), '" value=""';
	if( $qty_instances > 0 ) {
		echo ' checked="checked" ';
	}
	echo '>', "\n";
	echo '               </td>', "\n";
	echo '               <td>(Don\'t add)</td>', "\n";
	echo '             </tr>', "\n";
	foreach( $available_post_types as $post_type ) {
		echo '             <tr>', "\n";
		echo '               <td>', $post_type, '</td>', "\n";
		echo '               <td>', "\n";
		echo '                 <input type="radio" id="', sprintf( $global_controlid_pat, 'new_instance_post_type_', $post_type ), '" name="' . sprintf( $global_controlname_pat, 'new_instance_post_type' ), '" value="', $post_type,  '"';
		if( !$qty_instances > 0 && $post_type == 'post' ) {
			echo ' checked="checked" ';
		}
		echo '>', "\n";
		echo '               </td>', "\n";
		echo '               <td></td>',  "\n";
		echo '             </tr>', "\n";
	}
	echo '             <tr>', "\n";
	echo '               <td>new</td>', "\n";
	echo '               <td>', "\n"; 
	echo '                 <input type="radio" id="', sprintf( $global_controlid_pat, 'new_instance_post_type_new' ), '" name="', sprintf( $global_controlname_pat, 'new_instance_post_type' ), '" value="+">', "\n";
	echo '               </td>', "\n";
	echo '               <td>', "\n";
	echo '                 <input id="' . sprintf( $global_controlid_pat, 'new_post_type' ), '" name="', sprintf( $global_controlname_pat, 'new_post_type' ), '">', "\n";
	echo '               </td>', "\n";
	echo '             </tr>', "\n";
	echo '           </table>', "\n";
	echo '           <div class="', $text_domain, '-help">', $post_type_help, '</div>', "\n";
	echo '         </td>', "\n";
	echo '       </tr>', "\n";
	echo '      </table>', "\n";
	echo '    </div>', "\n";

	echo '    <div id="submit">', "\n";
	echo '      ', get_submit_button(), "\n";
	echo '    </div>', "\n";
	echo '  </form>', "\n";
	echo '</div>', "\n";
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
';
		echo '<script type="text/javascript">' . $script . '</script>';

}
