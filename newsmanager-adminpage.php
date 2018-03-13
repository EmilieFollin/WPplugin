<?php
class NewsmanagerAdmin{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'addNewsmanagerMenu'));
    }

    public function addNewsmanagerMenu(){
        $hookID = add_menu_page(
            'Page d\'administration de newsmanager',
            'Newsletter',
            'manage_options',
            'newsmanageradminpage',
            array($this, 'displaymenu'),
            'dashicons-email-alt', //https://developer.wordpress.org/resource/dashicons
            1
        );
        add_action('load'. $hookID, array($this, 'sendEmails'));
    }

    public function sendEmails(){

        if(isset($_POST['subject']) AND isset($_POST['content'])){
            if(!preg_match('#^.{3,100}$#', $_POST['subject'])){
                $error = true;
                $this->displayAdminNotice('sujet incorrect');
            }
            if(!preg_match('#^.{3,20000}$#', $_POST['content'])){
                $error = true;
                $this->displayAdminNotice('contenu incorrect');
            }
            if(!isset($error)){
                global $wpdb;
                $emails = $wpdb->get_results('SELECT email FROM '.$wpdb->prefix. 'newsmanager_emails');
                $sender = 'noreply@localhost.fr';
                $headers = array('From: ' .$sender);

                foreach($emails as $email) {
                    wp_mail(
                        $email->email,
                        $_POST['subject'],
                        $_POST['content'],
                        $headers);
                }
            }
            $this->displayAdminNotice('Email envoyé !', 'notice-success');
        }


    }

    public function displayAdminNotice($msg, $class = 'notice-error'){
        add_action('admin_notices', function() use($msg, $class){
            ?>
            <div class="notice" <?php echo $class?> >
                <p> <?php echo $msg ?></p>
            </div>
            <?php
        });
    }



    public function displayMenu(){
        ?>

        <h1><?php echo get_admin_page_title(); ?></h1>
        <p>Email dans la base de donnée : <?php echo $this->getCountEmail() ?></p>


        <form action="" method="POST">
            <p>
                <label for="subject"></label>
                <input type="text" name="subject" placeholder="sujet du mail" id="subject">
            </p>
            <p>
                <label for="content"></label>
                <textarea name="content" id="content" cols="50" rows="10" placeholder="contenu du mail"></textarea>
            </p>

            <?php submit_button('Envoyez') ?>
        </form>

<?php

    }

    public function getCountEmail() {
        global $wpdb;
        $countEmail = $wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix. 'newsmanager_emails');
        return $countEmail;
    }

}