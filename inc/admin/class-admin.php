<?php

namespace wp_combine_style\Inc\Admin;

use http\Exception\BadQueryStringException;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       http://hparsa.ir
 * @since      1.0.0
 *
 * @author    Hosien Parsa
 */
class Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.1.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * The text domain of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_text_domain The text domain of this plugin.
     */
    private $plugin_text_domain;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     * @param string $plugin_text_domain The text domain of this plugin.
     * @since       1.0.0
     */

    /**
     * combine style
     */


    private $css_in_db;


    public function __construct($plugin_name, $version, $plugin_text_domain)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->plugin_text_domain = $plugin_text_domain;
    }
    /*
     * minify content css
     */
    public function minify_css($content)
    {
        // Normalize whitespace
        $content = preg_replace( '/\s+/', ' ', $content );
        // Remove spaces before and after comment
        $content = preg_replace( '/(\s+)(\/\*(.*?)\*\/)(\s+)/', '$2', $content );
        // preserved with /*! ... */ or /** ... */
        $content = preg_replace( '~/\*(?![\!|\*])(.*?)\*/~', '', $content );
        // Remove ; before }
        $content = preg_replace( '/;(?=\s*})/', '', $content );
        // Remove space after , : ; { } */ >
        $content = preg_replace( '/(,|:|;|\{|}|\*\/|>) /', '$1', $content );
        // Remove space before , ; { } ( ) >
        $content = preg_replace( '/ (,|;|\{|}|\(|\)|>)/', '$1', $content );
        // Strips leading 0 on decimal values (converts 0.5px into .5px)
        $content = preg_replace( '/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $content );
        // Strips units if value is 0 (converts 0px to 0)
        $content = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $content );
        // Converts all zeros value into short-hand
        $content = preg_replace( '/0 0 0 0/', '0', $content );
        // Shortern 6-character hex color codes to 3-character where possible
        $content = preg_replace( '/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3(?!([a-f0-9][a-f0-9]))/i', '#\1\2\3', $content );
        return $content;
    }
    /*
     * recursive handles
     */
    private function add_deps_recursive($handle)
    {
        if (empty($handle)) {
            return;
        }
        global $wp_styles;
        if (!empty($wp_styles->registered[$handle]->deps)) {
            foreach ($wp_styles->registered[$handle]->deps as $dep) {
                if (! isset($this->css_in_db[$wp_styles->registered[$dep]->handle])) {
                    $this->css_in_db[$wp_styles->registered[$dep]->handle] = array(
                        'src' => ABSPATH . wp_make_link_relative($wp_styles->registered[$dep]->src),
                        'handle' => $wp_styles->registered[$dep]->handle,
                        'extera' => $wp_styles->registered[$dep]->extra,
                        'deps' => $wp_styles->registered[$dep]->deps
                    );
                }
                $this->add_deps_recursive($dep);
            }
        }
    }
        /*
         * start to combine style
         * load combine style or create styles
         */
    public function list_styles_path()
    {
        $user_id = get_current_user_id();
        $plugin_settings = get_option('combine_plugin_settings');
        if($plugin_settings['plugin_status'] != 1){
            return;
        }
        WP_Filesystem();
        global $wp_styles;
        global $wp_filesystem;
        $path_save_content = '/combine_styles/wp-admin/';
        // chehck to exist dir
        if(!is_dir(wp_upload_dir()['basedir'].$path_save_content)){
            wp_mkdir_p(wp_upload_dir()['basedir'] .$path_save_content);
        }
        // get option handles
        $get_option_combine_style = get_option('combine_style_handles');
        if($get_option_combine_style ==  false){
            $empty_array = [];
            add_option('combine_style_handles', $empty_array);
        }
        // get all styles
        foreach($wp_styles->queue as $handle) {
            if (!isset($this->css_in_db[$wp_styles->registered[$handle]->handle])) {
                $this->css_in_db[$wp_styles->registered[$handle]->handle] = array(
                    'src'       =>ABSPATH.wp_make_link_relative($wp_styles->registered[$handle]->src),
                    'handle'    =>$wp_styles->registered[$handle]->handle,
                    'extera'    =>$wp_styles->registered[$handle]->extra,
                    'deps'      =>$wp_styles->registered[$handle]->deps
                );
                $this->add_deps_recursive($handle);
            }
        }
        foreach ($this->css_in_db as $replace){
            $hanlde = $replace['handle'];
            if(!empty($replace['extera'])){
                $src_ext = $replace['src'];
                if($replace['extera']['conditional']){
//                    continue;
                }elseif(!empty($replace['extera']['rtl'])  && !empty($replace['extera']['suffix'])){
                    $path_replace = str_replace('.min.css', '-rtl.min.css', $src_ext);
                }elseif (!empty($replace['extera']['rtl'])){
                    $path_replace = str_replace('.css', '-rtl.css', $src_ext);
                }elseif (!empty($replace['extera']['suffix'])){
                    $path_replace = str_replace('.css', '.min.css', $src_ext);
                }else{
                    $path_replace = $src_ext;
                }
                $extera_style[$hanlde] = str_replace($src_ext,$path_replace,$replace);
            }else{
                $other_styles[$hanlde] = $replace;
            }
        }
        $this->css_in_db = array_merge($extera_style,$other_styles);
        $handles_dequeu = array_keys($this->css_in_db);
        // insert new handles into option
        $diff_styles = array_diff($handles_dequeu, $get_option_combine_style);
        if(!empty($diff_styles)){
            $get_option_combine_style = array_merge($get_option_combine_style,$diff_styles);
            update_option('combine_style_handles',$get_option_combine_style);
        }
        foreach ($get_option_combine_style as $kay=>$value){
            if (in_array($value, $handles_dequeu)) {
                $name_style += pow($kay,2);
            }
        }
        $name_style = $user_id."_".dechex($name_style);
        $path = wp_upload_dir()['basedir'].$path_save_content.$name_style . ".min.css";
        // check to exeist file
        if(file_exists($path)){
            wp_enqueue_style('combine_styles_enqueue', wp_upload_dir()['baseurl'].$path_save_content. $name_style.".min.css");
            wp_dequeue_style($handles_dequeu);
            wp_deregister_style($handles_dequeu);
            return;
        }
        // if dosent exeist start to create new style
        $open_file = fopen($path, 'w');
        foreach ($this->css_in_db as $combine){
            if(preg_match('/(?=url\()([^\)]*)(?=\).*)/', $this->minify_css($wp_filesystem->get_contents($combine['src'])))){
                $src_repl = chop($combine['src'],basename($combine['src']));
                $src_repl = str_replace(ABSPATH,'//ov2.ir/', $src_repl);
                $pattern_url = '/(?<=url\()([^\)]*)(?=\).*)/';
                $write_file = preg_replace_callback($pattern_url, function ($input) use ($src_repl) {
                    if(strpos($input[0],'data:') == false){
                        if(substr( $input[0], 0,3 ) === "../") {
                            return '"'.$src_repl.$input[0] .'"';
                        }
                        elseif(substr( $input[0], 0,1 ) === "'"){
                            $trim = trim($input[0],"'");
                            return '"'.$src_repl.$trim .'"';
                        }
                        elseif(substr( $input[0], 0,1 ) === '"'){
                            $trim = trim($input[0],'"');
                            return '"'.$src_repl.$trim .'"';
                        }
                    }
                    return $input[0];
                }, $this->minify_css($wp_filesystem->get_contents($combine['src'])) );
            }else{
                $write_file = $this->minify_css($wp_filesystem->get_contents($combine['src']));
            }
            fwrite($open_file,$write_file);
        }
        // send content to fix urls
        fclose($open_file);
        wp_enqueue_style('combine_styles_enqueue', wp_upload_dir()['baseurl'].$path_save_content. $name_style.".min.css");
        wp_dequeue_style($handles_dequeu);
        wp_deregister_style($handles_dequeu);
        return;
    }
    /*
    * The plugin is deleted when each plugin is added
    */
    public function delete_styles()
    {
        $path_save_content = '/combine_styles/wp-admin/';
        $delete_combine_style = list_files(wp_upload_dir()['basedir'].$path_save_content);
        foreach ($delete_combine_style as $path_delete_combine_style) {
            wp_delete_file($path_delete_combine_style);
        }
        delete_option('combine_style_handles');
    }

    /*
    *  function form to delete combine styles
    */
    public function form_delete_combine_style()
    {
        $path_save_content = '/combine_styles/wp-admin/';
        $delete_combine_style = list_files(wp_upload_dir()['basedir'].$path_save_content);
        foreach ($delete_combine_style as $path_delete_combine_style) {
            wp_delete_file($path_delete_combine_style);
        }
        delete_option('combine_style_handles');
        wp_redirect(add_query_arg(delete_style, '1', admin_url('admin.php?page=combine_style_admin_panel_form')));
    }

    public function combine_style__success()
    {
        if (isset($_GET['delete_style']) || $_GET['delete_style'] == 1) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>حافظه کش با موفقیت پاک شد.</strong></p></div>
            <?php
        } else if(isset($_GET['set_settings']) || $_GET['set_settings'] == 1){
            ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>تنظیمات با موفقیت ذخیره شد</strong></p></div>
            <?php
        }
    }

    /*
      *  function form to set settings
      */
    public function combine_style_admin_panel_form()
    {
        $plugin_settings = get_option('combine_plugin_settings');
        $path_save_content = '/combine_styles/wp-admin/';
        $delete_combine_style = list_files(wp_upload_dir()['basedir'].$path_save_content);
        if(!empty($delete_combine_style)){
            foreach ($delete_combine_style as $path_delete_combine_style) {
                $time_modify = filemtime($path_delete_combine_style);
                $time = time() - $time_modify;
                if($time > $plugin_settings['time_clean_style']){
                    wp_delete_file($path_delete_combine_style);
                }
            }
        }
        if (array_key_exists('savechanges', $_POST)) {
            $combine_plugin_settings = array(
                'time_clean_style' => intval(sanitize_text_field($_POST['timedeletestyle'])),
                'plugin_status' => intval(sanitize_text_field($_POST['pluginstatus']))
            );
            update_option('combine_plugin_settings', $combine_plugin_settings);
        }
        wp_redirect(add_query_arg(set_settings, '1', admin_url('admin.php?page=combine_style_admin_panel_form')));
    }

    public function combine_style_menu()
    {
        add_menu_page(
            'کش استایل پنل ادمین',
            'پاکسازی کش',
            'manage_options',
            'combine_style_admin_panel_form',
            array($this, 'display_combine_style_settings')
        );
    }

    public function display_combine_style_settings()
    {
        $plugin_settings = get_option('combine_plugin_settings');
        include_once('views/html_wp_combine_style_admin_display.php');
    }

}
