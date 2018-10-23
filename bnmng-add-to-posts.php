<?php
/*
Plugin Name: bnmng Add to Posts
Description: Add custom content to ever post
Version 1.0
Author: bnmng
Author URI: https://bnmng.com
Text Domain: bnmng-add-to-posts
Licence: GPL2
 */

function brecho ( $toecho ) {
        echo $toecho . '<br/>' . "\n";
}

function bnmng_add_to_posts($content) {
        $option_name = 'bnmng_add_to_posts';
        if(is_single() ) {
                $options = get_option( $option_name );
                $instances = count( $options );
                $add_to_beginning = '';
                $add_to_end = '';
                $post_category_ids = array_column( get_the_category(), 'term_id' );
                for( $i = 0; $i < $instances; $i++ ) {
                        $add_to_post = false;
                        $opt_category = stripslashes( $options[ $i ]['category'] );
                        if( $opt_category > 0 ) {
                                if( in_array( $opt_category, $post_category_ids ) ) {
                                        $add_to_post = true;
                                }
                        } else {
                                $add_to_post = true;
                        }
                        if( $add_to_post ) {
                                $add_to_beginning .= ( stripslashes( $options[ $i ][ 'at_beginning' ] ) );
                                $add_to_end = ( stripslashes( $options[ $i ][ 'at_end' ] ) ) . $add_to_end;
                        }
                }
                $content = $add_to_beginning . $content . $add_to_end;
        }
        return $content;
}
add_filter( 'the_content', 'bnmng_add_to_posts' );

function bnmng_add_to_posts_menu() {
        add_options_page( 'bnmng Add to Posts Options', 'bnmng Add to Posts', 'manage_options', 'bnmng-add-to-posts', 'bnmng_add_to_posts_options' );
}
add_action( 'admin_menu', 'bnmng_add_to_posts_menu' );

function bnmng_add_to_posts_options() {
        if( !current_user_can( 'manage_options' ) ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        $intro = '';
        $intro .= '<p>This plugin adds text to the beginning and end of a post when the post is displayed in singular view. ';
        $intro .= 'It does not alter the content of the post that is saved in the database.</p>';
        $intro .= '<p>HTML and shortcodes may be used.  Tags opened in the beginning can be closed at the end. ';
        $intro .= 'End of post content is added in reverse order for proper nesting. </p>';

        $option_name = 'bnmng_add_to_posts';
        $text_domain = 'bnmng-add-to-posts';
        $instance_label = 'Instance %1$d';
        $delete_label = 'Delete this Instance';
        $delete_help = '';
        $move_label = 'Move';
        $move_help = '';
        $category_label = 'Category';
        $category_help = 'The category of posts to have the following text added';
        $beginning_label = 'Add to Beginning of Post';
        $beginning_help = 'The text to add at the beginning of the post.  If you use HTML, ensure the valididy of your code.  Tags can be closed at the end of post';
        $end_label = 'Add to End of Post';
        $end_help = 'The text to add at the end of the post.  If you use HTML, ensure the valididy of your code.';
        $controlname_pat = $option_name . '[%1$d][%2$s]';
        $dropdown_pat = 'name=' . $controlname_pat . '&selected=%3$s&show_option_all=[any category]&echo=0';
        $control_pat = '<textarea name="' . $controlname_pat . '" id="' . $controlname_pat . '">%3$s</textarea>';

        $i = 0;
        $form_output = '';
        if( isset($_POST['submit'] ) ) {
                $options = [];
                $post_instances = count($_POST[ $option_name ] );
                for( $p = 0; $p < $post_instances; $p++ ) {
                        $delete_instance = false;
                        if( isset( $_POST[ $option_name ][ $p ]['delete']  ) ) {
                                $delete_instance = true;
                        } elseif ( !( $_POST[ $option_name ][$p ]['category'] > 0 || $_POST[ $option_name ][ $p ][ 'at_beginning' ] > '' || $_POST[ $option_name ][ $p ][ 'at_end'
                                $delete_instance = true;
                        }
                        if ( $delete_instance ) {
                        } else {
                                $options[$i]['category']=$_POST[ $option_name ][ $p ]['category'];
                                $options[$i]['at_beginning']=$_POST[ $option_name ][ $p ]['at_beginning'];
                                $options[$i]['at_end']=$_POST[ $option_name ][ $p ]['at_end'];
                                $i++;
                        }
                }
                update_option( $option_name,  $options );
       $options = get_option( $option_name );
        $instances = count($options);
        for ( $i = 0; $i < $instances; $i++ ) {
                $form_output .= '<tr><th colspan="2">' . sprintf($instance_label, ( $i + 1 ) ) . '</th></tr>';
                $form_output .= '<tr><th>' . $delete_label . '</th><td><div><input type="checkbox" name="' . sprintf( $controlname_pat, $i ,'delete' ) . '"></div><div class="' .
                $form_output .= '<tr><th>' . $move_label . '</th><td><div>';
                if( $i > 0 ) {
                        $form_output .= '<button type="button" id="btn_' . $option_name . '_move_up_' . $i . '" onclick="' . $option_name . '_move(' . $i . ', \'up\')">Up</button
                }
                $form_output .= '<button type="button" id="btn_' . $option_name . '_move_down_' . $i . '" onclick="' . $option_name . '_move(' . $i . ', \'down\')">Down</button>'
                $form_output .= '</div><div class="' . $text_domain . '-help">' . $move_help . '</div></td></tr>';
                $form_output .= '<tr><th>' . $category_label . '</th><td><div>' . wp_dropdown_categories( sprintf( $dropdown_pat, $i, 'category', stripslashes( $options[$i]['cate
                $form_output .= '<tr><th>' . $beginning_label . '</th><td><div>' . sprintf( $control_pat, $i, 'at_beginning', stripslashes( $options[$i]['at_beginning'] ) ) . '</
                $form_output .= '<tr><th>' . $end_label . '</th><td><div>' . sprintf( $control_pat, $i, 'at_end', stripslashes( $options[$i]['at_end'] ) ) . '</div><div class="'
        }
        $form_output .= '<tr><th colspan="2">' . sprintf( $instance_label, ( $i + 1 ) ) . '</th></tr>';
        if( $i > 0 ) {
                $form_output .= '<tr><th>' . $move_label . '</th><td><div>';
                $form_output .= '<button type="button" id="btn_' . $option_name . '_move_up_' . $i . '" onclick="' . $option_name . '_move(' . $i . ', \'up\')">Up</button>';
                $form_output .= '</div><div class="' . $text_domain . '-help">' . $move_help . '</div></td></tr>';
        }
        $form_output .= '<tr><th>' . $category_label . '</th><td><div>' . wp_dropdown_categories( sprintf( $dropdown_pat, $i, 'category', '' ) ) . '</div><div class="' . $text_do
        $form_output .= '<tr><th>' . $beginning_label . '</th><td><div>' . sprintf( $control_pat, $i, 'at_beginning', '' ) . '</div><div class="' . $text_domain . '-help">' . $be
        $form_output .= '<tr><th>' . $end_label . '</th><td><div>' . sprintf( $control_pat, $i, 'at_end', '' ) . '</div><div class="' . $text_domain . '-help">' . $end_help . '</

        echo '<div class = "wrap">';
        echo $intro;
        echo '<form method = "POST" action="?page=' . $text_domain . '">';
        echo '<table class="form-table ' . $text_domain . '">';
        echo $form_output;
        echo '</table>';
        submit_button();
        echo '</form>';
        echo '</div>';
}
add_action('admin_head-settings_page_bnmng-add-to-posts', 'bnmng_admin_style');

function bnmng_admin_style() {
        $text_domain = 'bnmng-add-to-posts';
        $style='
    table.' . $text_domain . ' textarea {
        width:100%;
    }
';
        echo '<style>' . $style . '</style>';
}
add_action('admin_head-settings_page_bnmng-add-to-posts', 'bnmng_admin_script');

function bnmng_admin_script() {
        $function_prefix = 'bnmng_add_to_posts';
        $script='
        function ' . $function_prefix . '_move( i, d ) {
            var temp_category = document.getElementById("' . $function_prefix . '[" + i + "][category]").value;
            var temp_at_beginning = document.getElementById("' . $function_prefix . '[" + i + "][at_beginning]").value;
            var temp_at_end = document.getElementById("' . $function_prefix . '[" + i + "][at_end]").value;

            if( d == "up" ) {
                var j = i - 1;
            } else {
                var j = i + 1;
            }

            document.getElementById("' . $function_prefix . '[" + i + "][category]").value = document.getElementById("' . $function_prefix . '[" + j + "][category]").value;
            document.getElementById("' . $function_prefix . '[" + i + "][at_beginning]").value = document.getElementById("' . $function_prefix . '[" + j + "][at_beginning]").valu
            document.getElementById("' . $function_prefix . '[" + i + "][at_end]").value = document.getElementById("' . $function_prefix . '[" + j + "][at_end]").value;

            document.getElementById("' . $function_prefix . '[" + j + "][category]").value = temp_category;
            document.getElementById("' . $function_prefix . '[" + j + "][at_beginning]").value = temp_at_beginning;
            document.getElementById("' . $function_prefix . '[" + j + "][at_end]").value = temp_at_end;

        }
';
        echo '<script type="text/javascript">' . $script . '</script>';

}
