<?php

/**
 * La classe 'NewsmanagerWidget' hérite de la classe 'WP_Widget' (obligatoire pour créer un nouveau widget dans Wordpress)
 */
class NewsmanagerWidget extends WP_Widget{
    
    /**
     * Le constructeur sert à enregistrer les paramètres du nouveau widget
     */
    public function __construct(){
        
        $options = array(
            'classname' => 'newsmanagerwidgetclass',    // class CSS qui sera mise sur le widget
            'description' => 'Affichage du formulaire de newsletter'    // Description du widget affichée dans le backoffice
        );
        
        //  On appel le constructeur du parent 'WP_Widget' et on lui passe en paramètre:
        // 1 : Le nom d'identification du widget
        // 2 : Le nom du widget affiché dans le backoffice
        // 3 : Un array contenant les options du widget (ici notre array $options créé juste avant)
        parent::__construct('newsmanagerwidget', 'Newsletter', $options);
    }
    
    /**
     * Cette méthode est appelée automatiquement pour afficher le corps du widget sur le frontoffice et doit obligatoirement possèder deux paramètres ($args et $instance)
     */
    public function widget($args, $instance){
        
        echo $args['before_widget'];    // On demande à wordpress d'afficher le code html standard devant chaque widget
        echo $args['before_title'];     // On demande à wordpress d'afficher le code html standard devant chaque titre de widget
        echo apply_filters('widget_title', $instance['title']);     // On affiche le titre du widget, qui peut être modifié par un filtre
        echo $args['after_title'];      // On demande à wordpress d'afficher le code html standard après chaque titre de widget
        
        // Code du formulaire HTML présent dans le widget
        ?>
        <form action="" method="POST">
            <p>
                <label for="newsmanageremail">Inscription : </label>
                <input id="newsmanageremail" type="text" name="news_manager_email">
            </p>
            <input type="submit">
        </form>
        <?php
        echo $args['after_widget'];     // On demande à wordpress d'afficher le code html standard après chaque widget
    }
    
    /**
     * Cette méthode est appelée automatiquement pour afficher le formulaire des options du widget dans le backoffice et doit obligatoirement possèder un paramètre ($instance)
     */
    public function form($instance){
        
        // Si le titre existe déjà on le met dans $title, sinon on déclare un titre de widget vide
        if(isset($instance['title'])){
            $title = $instance['title'];
        } else {
            $title = '';
        }
        
        // Formulaire des options du widget (ici on modifiera le title)
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title') ?>"><?php _e('Title:') ?></label>
            <input id="<?php echo $this->get_field_id('title') ?>" type="text" value="<?php echo $title ?>" name="<?php echo $this->get_field_name('title') ?>">
        </p>
        
        <?php
    }
    
}