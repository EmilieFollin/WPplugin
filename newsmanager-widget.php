<?php

class NewsmanagerWidget extends  WP_Widget{

    public function __construct(){
        $option = array(
            'classname' => 'newsmanagerwidgetclass',
            'description' => 'Affichage du formulaire de newsletter'
        );

        parent::__construct('newsmanagerwidget', 'Newsletter', $options);
    }

    public function widget($args, $instance){
        echo $args['before_widget'];
        echo $args['before_title'];


        echo apply_filters('widget_title', $instance['title']);

        echo $args ['after_title'];

      ?>

        <p>Subscribe to our weekly Newsletter and stay tuned.</p>

        <form action="" method="post">
            <label for="newsmanageremail"></label>
            <input id="newsmanageremail" name="news_manager_email" type="text">
            <input type="submit">
        </form>
        <br>

<?php

        echo $args['after_widget'];

    }
    public function form($instance){
        if(isset($instance['title'])){
            $title= $instance['title'];
        }else {
            $title ="";
        }
        ?>
        <p>
            <label for="<?php echo$this->get_field_id('title'); ?>">Titre :</label>
            <input id="<?php echo$this->get_field_id('title'); ?>" type="text" value="<?php echo $title ?>" name="<?php echo $this->get_field_name('title') ?>">
        </p>
        <?php
    }
}