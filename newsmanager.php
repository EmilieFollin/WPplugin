<?php
/*
 * Plugin Name: News Manager
 * Description: Plugin de newsletter !
 * Version: 0.0.1
 * Author: Emilieee
 */

class NewsmanagerPlugin{
    public function __construct()
    {
        require_once (plugin_dir_path(__FILE__) . '/newsmanager-widget.php');
        require_once (plugin_dir_path(__FILE__) . '/newsmanager-adminpage.php');
        add_action('widgets_init', function (){
            register_widget('NewsmanagerWidget');
        });

        add_action('wp_loaded', array($this, 'addNewEmail'));

        new NewsmanagerAdmin();

        add_shortcode('newsmanager_form', array($this, 'shortcodeAction'));
    }

    public static function pluginActivation() {
        global $wpdb;
        $wpdb->query('CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'newsmanager_emails (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(320) NOT NULL)');
    }

    public static function plugin_uninstall(){
        global $wpdb;
        $wpdb->query('DROP TABLE IF EXISTS '. $wpdb->prefix .'newsmanager_emails');
    }


    public function addNewEmail(){

        if(isset($_POST['news_manager_email'])){

            if(filter_var($_POST['news_manager_email'], FILTER_VALIDATE_EMAIL)){

               global $wpdb;
               $checkIfExist = $wpdb->get_var(
                   $wpdb->prepare( 'SELECT COUNT(*) FROM '. $wpdb->prefix. 'newsmanager_emails WHERE email = %s ', $_POST['news_manager_email'])
               );


               if($checkIfExist == 0) {
                   global $wpdb;
                   $wpdb->query( $wpdb->prepare(
                       'INSERT INTO ' . $wpdb->prefix. 'newsmanager_emails	( email ) VALUES ( %s )', $_POST['news_manager_email']));
                   $this->displayMsg('Vous êtes bien inscrit');

               };

            } else {
                 // afficher erreur
                $this->displayMsg('adresse email deja inscrite');
            }
        } else {
            $this->displayMsg('adresse email invalide');
        }

    }

    public function displayMsg($msg){
        add_action('wp_enqueue_scripts', function()use($msg){
            ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    alert('<?php echo $msg; ?>');
                });
            </script>
            <?php

        });
    }

    public function shortcodeAction(){
        the_widget('NewsmanagerWidget');
    }


}


register_activation_hook(__FILE__, array('NewsmanagerPlugin', 'pluginActivation'));
register_uninstall_hook(__FILE__, array('NewsmanagerPlugin', 'plugin_uninstall'));
add_action('plugins_loaded', function () {
    new NewsmanagerPlugin();
});

// NewsmanagerPlugin::displayMsg('test');

