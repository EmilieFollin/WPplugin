<?php

/**
 * Classe que nous créons pour gérer la construction de la page d'administration backoffice de notre plugin
 */
class NewsmanagerAdmin{
    

    /**
     * On se sert du constructeur pour enregistrer la nouvelle page admin auprès de wordpress
     */
    public function __construct(){
        add_action('admin_menu', array($this, 'addNewsmanagerMenu'));
    }

    
    /**
     * Méthode appelée par le constructeur pour ajouter une nouvelle page admin, avec ses paramètres
     */
    public function addNewsmanagerMenu(){
        
        // On stocke dans $hookID l'identifiant de notre page pour pouvoir plus tard créer des actions uniquement sur cette page (comme un traitement de formulaire par exemple)
        $hookID = add_menu_page(
            'Page d\'administration de Newsmanager',    // Titre de la page
            'Newsletter',                               // Titre de l'onglet du menu à gauche
            'manage_options',                           // Niveau admin requis pour accèder à cette page
            'newsmanageradminpage',                     // Nom de référence de la page (doit être unique)
            array($this, 'displayMenu'),                // Méthode appelée pour l'affichage html de la page
            'dashicons-email-alt',                      // Icon de l'onglet du menu à gauche. Liste icons : https://developer.wordpress.org/resource/dashicons/
            4                                           // Ordre de positionnement de l'onglet dans la liste à gauche
        );
        
        // On demande à wordpress d'appeler la méthode 'sendEmails à chaque chargement de notre page admin uniquement
        add_action('load-'.$hookID, array($this, 'sendEmails'));
    }

    
    /**
     * Méthode gérant l'affichage de notre page admin
     */
    public function displayMenu(){
        
        ?>
        <h1><?php echo get_admin_page_title() // Affichera le titre de la page créé à la ligne 24 ?></h1>
        <hr>
        <p>Emails dans la base de données : <?php echo $this->getCountEmail() // On affiche le nombre d'adresse email en BDD en appelant la méthode getCountEmail() que nous avons créé ?></p>
        
        <!-- Formulaire avec deux champs pour envoyer un email à toutes les adresses en BDD
        En réalité wordpress inclu un système pour générer des formulaires, ce qui nous évite de devoir le faire manuellement. Je vous laisse regarder ici pour voir comment : https://codex.wordpress.org/Creating_Options_Pages -->
        
        <form action="" method="POST">
            <p>
                <label for="subject">Sujet : </label><br>
                <input name="subject" id="subject" type="text" placeholder="Sujet du mail">
            </p>
            <p>
                <label for="content">Contenu : </label><br>
                <textarea name="content" id="content" cols="30" rows="10" placeholder="Contenu..."></textarea>
            </p>
            <?php submit_button('Envoyer'); // Afficher un bouton submit stylisé par wordpress?>
        </form>
        <?php
    }


    /**
     * Méthode permettant d'afficher un message à l'administrateur (avec en premier paramètre le message à afficher et en second paramètre la class CSS a lui appliquer (notice-error pour une erreur ou notice-success pour un succès))
     */
    public function displayAdminNotice($msg, $class = 'notice-error'){
        add_action('admin_notices', function() use($msg, $class){   // use($msg, $class) permet d'importer les variables $msg et $class dans la fonction anonyme (sinon on y aurait pas accès pour les echo)
            ?>
            <div class="notice <?php echo $class; ?> is-dismissible">
                <p><?php echo $msg; ?></p>
            </div>
            <?php
        });
    }
    

    /**
     * Méthode appelée à chaque chargement de notre page admin uniquement, permettant de vérifier si le formulaire a été rempli
     */
    public function sendEmails(){
        
        // Si $_POST['subject'] et $_POST['content'] existent, alors on traite le formulaire, sinon on fait rien
        if(isset($_POST['subject']) AND isset($_POST['content'])){
            
            // Si subject n'est pas valide, on affiche une erreur et on initialise $error à true
            if(!preg_match('#^.{3,100}$#', $_POST['subject'])){
                
                $error = true;
                $this->displayAdminNotice('Sujet invalide !');
                
            }
            
            // Si content n'est pas valide, on affiche une erreur et on initialise $error à true
            if(!preg_match('#^.{3,20000}$#', $_POST['content'])){
                
                $error = true;
                $this->displayAdminNotice('Contenu invalide !');
            }
            
            // Si $error n'existe pas (et donc aucun champ n'est invalide car $error n'est pas égal à true)
            if(!isset($error)){
                
                // On importe $wpdb de l'espace global
                global $wpdb;
                
                // On récupère en BDD toutes les adresses emails et on les stocke dans $emails
                $emails = $wpdb->get_results("SELECT email FROM " . $wpdb->prefix . "newsmanager_emails");
                $sender = 'noreply@localhost.fr';   // On déclare l'adresse email qui sera marquée comme expéditrice des emails (peut être amélioré en demandant sur la page admin quelle adresse doit être utilisée, voir récupérer et utiliser celle de l'administrateur qui a validé le formulaire)
                $headers = array('From: '.$sender);
                
                // Pour chaque email récupéré, on envoi un email
                foreach($emails as $email){
                    $send = wp_mail($email->email, $_POST['subject'], $_POST['content'], $headers);
                    /*
                    fonction wp_mail créée par wordpress permet d'envoyer des emails avec ces paramètres :
                    1 : adresse du destinataire
                    2 : Objet du mail
                    3 : Contenu du mail
                    4 : Les headers du mail (from, reply-to, etc..)
                    */
                }
                
                // On affiche un message de succès
                $this->displayAdminNotice('La newsletter a bien été envoyée !', 'notice-success');
                
            }
            
        }
    }

    
    /**
     * Méthode qui récupère en BDD le nombre d'adresse email stocké et le retourne
     */
    public function getCountEmail(){
        global $wpdb;
        
        return $wpdb->get_var('SELECT COUNT(*) as total FROM ' . $wpdb->prefix . 'newsmanager_emails');
    }
    
}