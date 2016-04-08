<?php
/*
Plugin Name: Rel=nofollow plugin
Plugin URI: https://github.com/3tern1ty/wordpress-nofollow-links-plugin
Description: Add rel=nofollow to all domain, except selected. Access via Wordpress admin "Settings" -> "rel=nofollow"
Author name: 3tern1ty
Version: 1.0
Author URI: http://bluemountainfengshui.org
*/

/*

The MIT License (MIT)

Copyright (c) 2016 3tern1ty

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

*/

function az_rel_nofollow_plugin_install(){
  add_option('az-nofollow-domains');
  update_option('az-nofollow-domains', get_bloginfo('url'));
}
register_activation_hook(__FILE__, 'az_rel_nofollow_plugin_install');


function az_rel_nofollow_plugin_remove(){
  delete_option('az-nofollow-domains');
}
register_uninstall_hook(__FILE__, 'az_rel_nofollow_plugin_remove');


function az_rel_nofollow_option_page(){
  add_options_page('Add rel="nofollow" to links', 'rel=nofollow', 'manage_options', basename(__FILE__), 'az_rel_nofollow_display');
}
add_action('admin_menu', 'az_rel_nofollow_option_page');

function az_rel_nofollow_display(){
  if(isset($_POST['submit'])){
    if(function_exists('current_user_can') && !current_user_can('manage_options')){
      die('Are u hacker?');
    }
    if (function_exists('check_admin_referer') ) {
      check_admin_referer('az_rel_nofollow_nonce');
    }
    update_option('az-nofollow-domains', esc_textarea($_POST['az-nofollow-domains']));
  }
  ?>
    <div class="wrap">
      <h1>Add domain to exclude rel="nofollow"</h1>
      <p>Enter domain names here. Each domain shoul starts from new line</p>
      <form  name="az_rel_nofollow_form" class="az_rel_nofollow_form" action="<?php echo $_SERVER['PHP_SELF'];?>?page=<?php echo basename(__FILE__);?>" method="post">
        <?php
          if (function_exists ('wp_nonce_field') )
          {
              wp_nonce_field('az_rel_nofollow_nonce');
          }
        ?>
        <textarea cols="50" rows="12" name="az-nofollow-domains"><?php echo get_option('az-nofollow-domains'); ?></textarea>
        <p class="submit">
           <input type="submit" name="submit" class="button button-primary button-large" value="<?php _e('Save Changes') ?>" />
        </p>
      </form>
    </div>

  <?php
}

function az_rel_nofolow_conten_implimentation($content){
  $domains = preg_split("/[\s,]+/", get_option('az-nofollow-domains'));
  $pattern = '~\<a\s(href=[\'|\"]((?!';
  for ($i=0; $i < count($domains); $i++) {
    $pattern .= '(' . $domains[$i] . ')';
    if($i < ( count($domains) - 1 ) ) {
      $pattern .= '|';
    }
  }
  $pattern .= ')[а-яА-Яa-zA-Z0-9\_\:\/\.\-\'\"\s=\;\+]{0,}))>~';

  $content = preg_replace($pattern, '<a $1 rel="nofollow">', $content );

  return $content;

}
add_filter('the_content', 'az_rel_nofolow_conten_implimentation');


?>
