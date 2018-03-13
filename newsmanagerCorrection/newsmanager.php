<?php
/*
Plugin Name: News Manager
Description: Meilleur plugin de newletter !
Version: 1.0.0
Author: Moi
*/


/**
 * Création d'une classe pour gérer notre plugin
 */
class NewsmanagerPlugin{

    
    /**
     * Constructeur de la classe qui nous sert surtout à enregistrer les différentes actions et initialisations de notre plugin
     */
    public function __construct(){

        // Inclusion des fichiers du plugin
        require_once( plugin_dir_path(__FILE__) . '/newsmanager-widget.php' );
        require_once( plugin_dir_path(__FILE__) . '/newsmanager-adminpage.php' );

        // La méthode 'addNewEmail' de la classe $this (NewsmanagerPlugin) sera executée à chaque fois qu'une page wordpress sera chargée (donc tous le temps)
        add_action('wp_loaded', array($this, 'addNewEmail'));

        // La classe 'NewsmanagerWidget' sera enregistrée comme étant un nouveau widget dans wordpress
        add_action('widgets_init', function(){
            register_widget('NewsmanagerWidget');
        });
        
        // Instanciation de la classe 'NewsmanagerAdmin' qui va créer la page d'administration
        new NewsmanagerAdmin();

        // Création d'un shortcode [newsmanager_form] qui fera appel à la méthode 'shortcodeAction'
        add_shortcode('newsmanager_form', array($this, 'shortcodeAction'));
    }
    

    /**
     * Méthode qui sera executée à l'activation du plugin (elle doit être statique sinon elle sera inaccessible pour wordpress)
     */
    public static function pluginActivation(){
        // On se connecte en BDD et on crée la table wp_newsmanager_emails si elle n'existe pas
        global $wpdb;
        $wpdb->query('CREATE TABLE IF NOT EXISTS '. $wpdb->prefix .'newsmanager_emails (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(320) NOT NULL)');
    }
    

    /**
     * Méthode qui sera executée à la suppression du plugin (elle doit être statique sinon elle sera inaccessible pour wordpress)
     */
    public static function uninstall(){
        // On se connecte en BDD et on supprime la table wp_newsmanager_emails si elle existe
        global $wpdb;
        $wpdb->query('DROP TABLE IF EXISTS '.$wpdb->prefix.'newsmanager_emails');
    }
    

    /**
     * Méthode qui sera executée à chaque chargement de page (le widget pouvant être présent sur toutes les pages du site on teste donc l'existence de $_POST['email] de partout et pas sur une page spécifique)
     */
    public function addNewEmail(){
        
        // Si $_POST['news_manager_email'] existe
        if(isset($_POST['news_manager_email'])){
            
            // Si l'email est une adresse email valide
            if(filter_var($_POST['news_manager_email'], FILTER_VALIDATE_EMAIL)){
                
                // On importe $wpdb dans la fonction
                global $wpdb;
                
                // On récupère en BDD le nombre de fois que l'email est présente (si 1 alors elle est déjà inscrite, si 0 alors elle n'est pas encore inscrite)
                $checkIfExist = $wpdb->get_var(
                    $wpdb->prepare(
                        'SELECT count(*) FROM '.$wpdb->prefix.'newsmanager_emails WHERE email = %s', $_POST['news_manager_email']
                    )
                );
                
                // Si l'email n'a pas été trouvé ($checkIfExist est donc égal à 0)
                if($checkIfExist == 0){
                    
                    // On insère en BDD la nouvelle adresse email
                    $wpdb->query(
                        $wpdb->prepare(
                            'INSERT INTO '.$wpdb->prefix.'newsmanager_emails(email) VALUES(%s)', $_POST['news_manager_email']
                        )
                    );
                    
                    // on affiche un message de succès
                    $this->displayMsg('Vous êtes bien inscrit !');
                    
                } else {
                    // l'email a été trouvé dans la BDD donc on affiche un message d'erreur
                    $this->displayMsg('Email déjà inscrite !');
                    
                }
                
            } else {
                // L'adresse email n'étant pas valide, on affiche un message d'erreur
                $this->displayMsg('Adresse email invalide !');
                
            }
            
        }
        
    }


    /**
     * Méthode permettant d'afficher un message au visiteur (avec le message à afficher en paramètre)
     */
    public function displayMsg($msg){
        
        // On ajoute une balise script pour afficher le message avec un alert javascript une fois le DOM chargé
        add_action('wp_enqueue_scripts',function() use ($msg){  // use($msg) permet d'importer la variable $msg dans la fonction anonyme (sinon on y aurait pas accès pour le echo)
            ?>
            <script>
            document.addEventListener('DOMContentLoaded', function(){
                alert('<?php echo $msg; ?>');
            });
            </script>
            <?php
        });
    }

    /**
     * Méthode du shortcode permettant d'afficher notre widget
     */
    public function shortcodeAction(){
        the_widget('NewsmanagerWidget');
    }
    
}


// La méthode 'pluginActivation' de la classe 'NewsmanagerPlugin' sera executée à chaque fois que le plugin sera activé
register_activation_hook(__FILE__, array('NewsmanagerPlugin', 'pluginActivation'));
// La méthode 'uninstall' de la classe 'NewsmanagerPlugin' sera executée à chaque fois que le plugin sera supprimé
register_uninstall_hook(__FILE__, array('NewsmanagerPlugin', 'uninstall'));


// On instancie notre classe pour charger tout le plugin
add_action( 'plugins_loaded', function(){
    new NewsmanagerPlugin();
} );
