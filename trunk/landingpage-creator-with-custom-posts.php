<?php

/**
* Plugin Name: Landing Page Creator With Custom Posts
* Description: Create landing pages using custom posts for each section
* Version: 0.3.3
* Author: Iran Alves
* Author URI: makingpie.com.br
* License: GPL2
* Copyright (C) 2019 Iran
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if( !class_exists('landingpage_LPCCP') ):

/**
 * Landing Page With Custom Posts - Main Class
 * @since 0.1
 */
class landingpage_LPCCP{

    //Array com as keys válidas
    protected   $valid_fields_keys;
    protected   $lpccp_pages_ids;
    private     $videos_url;
    private static $acfRequired = '5.0.0';

    function __construct(){

        //defines constants
        define('PLUGIN_DIR', plugin_dir_path(__FILE__) );
        define('ACF_LITE', true );
        define('VERSION', '0.3.3');

        //Send HTTP header
        header('Content-Type: application/json');

        //Current Local language
        $locale = apply_filters( 'plugin_locale', get_locale(),
                'landingpage-creator-with-custom-posts' );

        //Load ".mo" file of current language
        load_textdomain( 'landingpage-creator-with-custom-posts',
                PLUGIN_DIR . '/language/' . $locale . '.mo' );

        //Verify ACF Version, stop or continue execution
        if( $this->landingpage_lpccp_acf_version() ){
            
            //includes classes
            include_once PLUGIN_DIR . '/landingpage_lpccp_admin.php';

            //Define values in vars
            $this->valid_fields_keys = $this->landingpage_lpccp_fields_keys();
            $this->lpccp_pages_ids = maybe_unserialize( get_option('lpccp_pages_enabled') );

            //Instance of class "landingpage_LPCCP_options"
            $options = new landingpage_LPCCP_options();

            //Hooks e Filters
            //Verifica group fields existem ou no
            add_action('init', array($this, 'landingpage_lpccp_init'), 20);
            
            //carregar template
            add_action('single_template', array($this, 'landingpage_lpccp_post_template' ));
            add_action('page_template',  array($this, 'landingpage_lpccp_page_template' ));
            
            //Mudar cor de background - Legado
            add_action('admin_head-post.php', array($this, 'landingpage_lpccp_color_background'));
            //Suporte ao Gutenberg: Wordpress >= 5
            add_filter('block_editor_settings', array($this, 'landingpage_lpccp_gutenberg_editor_color_background'), 10, 2);

            //Carregar scripts no head da página
            add_action('wp_head', array($this, 'landingpage_lpccp_scripts_init_head' ));
            
            //Carregar scripts no footer da página
            add_action('wp_footer', array($this, 'landingpage_lpccp_scripts_init_footer'));

            /*Admin Config*/
            //Add menu page in options
            add_action( 'admin_menu', array( $options, 'lpccp_add_admin_menu') );
            //Add link na listagem de plugins
            add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array($options, 'lpccp_add_plugin_links') );
        }        

    }

    /** 
    *   Initiate
    *   Iniciar
    *   @since 0.1
    */
    public function landingpage_lpccp_init(){

        //Verifica se campos não existirem, senão retorna erro
        if( ! $this->landingpage_lpccp_fields_keys_exist() ):

            //Registrando custom posts types
            $this->landingpage_lpccp_custom_post_type();

            //Registrar grupos e campos personalizados
            $this->landingpage_lpccp_register_fields();

        endif;
    }

    /** 
     * Verify ACF Version and stop or continue execution
     * @return boolean
     * @since 0.3.2
    */
    public function landingpage_lpccp_acf_version(){

        /**
         * Detect plugin. For use on Front End only.
         */
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        //Acf paths - url and local
        $acfPath   = 'advanced-custom-fields/acf.php';
        $acfDir    = plugin_dir_path(WP_PLUGIN_DIR) . plugin_basename('plugins/advanced-custom-fields') . '/acf.php';

        $acfStatus = false; //Status ACF ative

        //If plugin doenst installed, register notice error
        $error = (!file_exists($acfDir))? '<div class="error"><p>' . __('The plugin `Advanced Custom Fields` is not installed in your Wordpress. This plugin is required to use <b>Landing Page Creator With Custom Posts</b> plugin.', 'wc_qsti') .'</p></div>' : null; //Error message
        
        // Verify ACF plugin is active and updated to the required version
        if ( is_plugin_active($acfPath) ) {
            
            //Path plugin file
            $acf_path = $acfDir;
            //Plugin data
            $acf_plugin_data = get_plugin_data( $acf_path, false, false);
            //Extract version in plugin data
            $acf_current_version = $acf_plugin_data['Version'];
            //Format version
            $acf_version = (int) preg_replace('/[.]/', '', $acf_current_version);
            //ACF status active
            $acfStatus = true;

            // CF7 drops the ending ".0" for new major releases (e.g. Version 4.0 instead of 4.0.0...which would make the above version "40")
            // We need to make sure this value has a digit in the 100s place.
            if ( $acf_version < 100 ) {
                $acf_version = $acf_version * 10;
            }

            // If ACF version is < acfRequired
            if ( $acf_version < self::$acfRequired ) {
                $error = '<div class="error"><p><strong>'. __('Warning:', 'wc_qsti') . '</strong> '. __('Landing Page Creator With Custom Posts: ','wc_qtsi') . $acf_current_version .  __('. Your `Advanced Custom Fields` plugin is outdated. Upgrade your plugin to last version.', 'wc_qsti') .'</p></div>';
                $acfStatus = false;
            }

        }
        // If it's not installed and activated, throw an error
        else {
            $error .= '<div class="error"><p>' . __('The plugin `Advanced Custom Fields` is not activated in your Wordpress. Please, install to use <b>Landing Page Creator With Custom Posts</b> plugin.', 'wc_qsti') .'</p></div>';
            $acfStatus = false;
        }

        //If error was registered show in notices admin
        if(!is_null($error)){
            $GLOBALS['lpccp_error'] = $error;
            add_action('admin_notices', function(){ 
                global $lpccp_error;
                echo $lpccp_error;
            });
        }
          
        //return status
        return $acfStatus;
    }

    /**   
     * Keys for ACF Groups
     *  Chaves de grupos ACF
     *  @return array
     *  @since 0.1
     */
    protected function landingpage_lpccp_fields_keys(){

        //Array com group fields e suas keys
        $default = array(
                'acf_lpccp_add-class'     => 'field_58cb0a5f05d23',
                'acf_lpccp_background'    => 'field_58ab07c73b82a',
                'acf_lpccp_add-cod'       => 'field_589bcd731cda2',
                'acf_lpccp_landingpage'   => 'field_58a3a6b756992',
                'acf_lpccp_col-2x'        => 'field_589bda7408f7a',
                'acf_lpccp_col-3x'        => 'field_589bdb55a99a8'
            );

        //Retorna possibilitando hook
        //return
        return array_merge( apply_filters('lpccp_add_fields_keys', (array) $default ) );
    }


    /** 
    *  Verify type post to add filter that change background-color TinyMCE Editor on save
    *  Verifica o tipo de post e adicina cor de backround ao editor TinyMCE
    *   @since 0.1
    */
    public function landingpage_lpccp_color_background(){

        $post_types = array( 'section-lpccp' => '' );

        if( is_admin() && array_key_exists( get_post_type(), $post_types ) ):
            
            //Legado: Suporte ao TinyMCE: Wordpress < 5
            add_filter('tiny_mce_before_init', array($this, 'landingpage_lpccp_tinymce_live_edit'));

        endif;
    }

    /** 
    *  Verify type post to add filter that change background-color Gutenberg on save
    *  Verifica o tipo de post e adicina cor de backround ao editor Gutenberg
    *   @since 0.3.3
    */
    public function landingpage_lpccp_gutenberg_editor_color_background( $editor_settings, $post ){
        
        /*Cores personalizadas*/
        $bgColor    = ( function_exists('get_field') )? get_field('background-color', $post->ID) : '#FFFFFF' ;
        $FontColor  = ( function_exists('get_field') )? get_field('color', $post->ID) : 'initial';

        $styles = 'body.editor-styles-wrapper { background-color: ' . $bgColor . ';} body.editor-styles-wrapper .editor-rich-text{ color: '. $FontColor . ' }';
        
        $editor_settings['styles'][]['css'] = $styles;

        return $editor_settings;
    }


    /** 
     * Change background color and text color of TinyMCE Editor
     * Altera cor de background e texto do editor TinyMCE
     * @return array    styles of Editor
     * @since 0.1
     */
    public function landingpage_lpccp_tinymce_live_edit( $mceInit ) {

        /*Cores personalizadas*/
        $bgColor    = ( function_exists('get_field') )? get_field('background-color', get_the_ID()) : '#FFFFFF' ;
        $FontColor  = ( function_exists('get_field') )? get_field('color', get_the_ID()) : 'initial';

        $styles = 'body.mce-content-body, .edit-post-visual-editor.editor-styles-wrapper { background-color: ' . $bgColor . ';color: '. $FontColor . ' }';

        if ( isset( $mceInit['content_style'] ) ) {
            $mceInit['content_style'] .= ' ' . $styles . ' ';
        } else {
            $mceInit['content_style'] = $styles . ' ';
        }
        return $mceInit;
    }

    /**  
     * Register custom posts
     * Registrando custom posts
     * @since 0.1
     */
    public function landingpage_lpccp_custom_post_type(){

        $landingpage_icon = plugin_dir_url(__FILE__) . "assets/images/landingpage-icon.png";
        $section_icon = plugin_dir_url(__FILE__) . 'assets/images/section-icon.png';

        /*Plugin CPT*/
        register_post_type( 'landingpage-lpccp',
                $this->args(_x('Landing Pages','','landingpage-creator-with-custom-posts'), _x('Landing Page','','landingpage-creator-with-custom-posts'), 'landingpage-lpccp', $landingpage_icon ) );

        register_post_type( 'section-lpccp',
                $this->args(_x('Seções','','landingpage-creator-with-custom-posts'), _x('Seção','','landingpage-creator-with-custom-posts'), 'section-lpccp', $section_icon ) );

    }

    /**
    *  Args Model to Custom Posts
    *  Modelo de argumentos para custom posts
    *  @since 0.1
    */
    protected function args($plural, $singular, $slug, $menuIcon){
        return (array) array(
            'labels'              => $this->label($plural, $singular),
            'public'              => false,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'exclude_from_search' => true,
            'show_in_nav_menus'   => true,
            'rewrite'             => array( 'slug' => $slug ),
            'capability_type'     => 'page',
            'has_archive'         => false,
            'hierarchical'        => false,
            'menu_position'       => 10,
            'menu_icon'           => $menuIcon,
            'supports'            => array(
                                    'title', 'editor', 'author'
                                    ),
            'show_in_rest'          => true,
            'exclude_from_search'   => true
        );
    }

    /** 
    *  Register names and labels of custom posts
    *  Registrar nome e labels de custom posts
    *  @since 0.1
    */
    protected function label($plural, $singular, $menu_name = '' ){

        $add_new = _x( 'Adicionar Novo', '', 'landingpage-creator-with-custom-posts' );

        return (array) array(
            'name'               => $plural,
            'singular_name'      => $singular,
            'menu_name'          => $plural,
            'name_admin_bar'     => $singular,
            'add_new'            => $add_new,
            'add_new_item'       => $add_new,
            'new_item'           => $add_new,
            'edit_item'          => _x( 'Editar', '', 'landingpage-creator-with-custom-posts' ),
            'view_item'          => _x( 'Ver', '', 'landingpage-creator-with-custom-posts' ),
            'all_items'          => _x( 'Ver Todos', '', 'landingpage-creator-with-custom-posts' ),
            'search_items'       => _x( 'Procurar', '', 'landingpage-creator-with-custom-posts' ),
            'parent_item_colon'  => _x( 'Pais:', '', 'landingpage-creator-with-custom-posts' ),
            'not_found'          => _x( 'Não encontrado.', '', 'landingpage-creator-with-custom-posts' ),
            'not_found_in_trash' => _x( 'Não encontrado na lixeira.', '', 'landingpage-creator-with-custom-posts' )
        );
    }

    /** 
     * Load template for post type 'landingpage-lpccp'
     * Carrega template para custom post "landingpage-lpccp"
     * @return string   template path
     * @since 0.1
     */
    public function landingpage_lpccp_post_template($single_template) {

        if ( get_post_type() == 'landingpage-lpccp' ) {

            $single_template = PLUGIN_DIR . 'landingpage_lpccp_template.php';

        }
        return $single_template;

    }//landingpage_lpccp_page_template()

    /** 
     * Load template for pages selected in Settings
     * Template para páginas selecionadas nas opções
     * @return string   template path
     * @since 0.1
     */
    public function landingpage_lpccp_page_template($page_template) {
        if ( !$this->landingpage_lpccp_empty($this->lpccp_pages_ids) && is_page($this->lpccp_pages_ids) ) {

            $page_template = PLUGIN_DIR . 'landingpage_lpccp_template.php';

        }
        return $page_template;

    }//landingpage_lpccp_page_template()


    /**
     * Load scripts and assets for pages and custom posts
     *  Carrega scripts e materiais para páginas e custom posts
     *  @since 0.1
     */
    public function landingpage_lpccp_scripts_init_head() {

      //Se var com páginas de ID's for nulo, retorna função
      if(! is_array($this->lpccp_pages_ids) || is_null($this->lpccp_pages_ids)):
        return;
      endif;
        
      $id = (string) get_the_ID();

      if( get_post_type() == 'landingpage-lpccp' ||
              in_array( $id, $this->lpccp_pages_ids, TRUE) ){

            /**
            * Inserção de conteúdo antes de abrir a tag 'body'
            * Utilizando plugin "ACF" retornamos dados via admin
            * @since 0.1
            * 
            * v0.3.3: Substituido por função de classe $lpccp para melhor controle
            */
            $this->landingpage_lpccp_check_exist('before-open-body');

        }

    }//landingpage_lpccp_scripts_init_head()

    /**
     * Scripts adicionados antes da tag de fechamento body em "Pagina-inicial"
     * @since 0.1
     */
    public function landingpage_lpccp_scripts_init_footer() {
        
        //Se var com páginas de ID's for nulo, retorna função
      if(! is_array($this->lpccp_pages_ids) || is_null($this->lpccp_pages_ids)):
        return;
      endif;

        $id = (string) get_the_ID();

        //Verifica pagina se habilitada como "Landing Page"
        if( get_post_type() == 'landingpage-lpccp' ||
                in_array( $id, $this->lpccp_pages_ids, TRUE) ){

            //Css Template Page
            wp_enqueue_style( 'landingpage-lpccp-css', plugin_dir_url(__FILE__) . 'assets/css/lpccp.min.css' );

            //Bootstrap (Javascript)
            wp_enqueue_script( 'landingpage-lpccp-js', plugin_dir_url(__FILE__) . '/assets/js/lpccp.js', array('jquery'), '0.1' );


            /* Inserção de conteúdo antes de fechar tag 'body'
             * Utilizando plugin "ACF" retornamos dados via admin
             * @since 0.1
             * 
             * v0.3.3: Substituido por função de classe $lpccp para melhor controle
            */
            $this->landingpage_lpccp_check_exist('after-open-body');

        }

    }//landingpage_lpccp_scripts_init_footer()

    /**
     * Load defaults header or footer
     * Carrega cabeçalho ou footer padrão
     * @since   0.1
     * @param string    $section    name of section (head ou footer) to apply
     */
    public function landingpage_lpccp_show_default_section( $section ){

        /* Verifica os valores iniciais para função funcionar corretamente */
        if( !function_exists('get_field') || !is_string($section) || $section != ( 'header' || 'footer' ) ):
            return;
            die();
        endif;

        /* Se opção for 'true', exibe as seções padrões do template */
        if( get_field('show-default-header') == 'true' ):
            return ($section == 'header')? get_header() : get_footer();
            die();
        endif;

        if( $section == 'header' ):
    ?>

        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

        <?php wp_head(); ?>

        </head>

        <body <?php body_class(); ?>>

            <div id="page" class="site">

    <?php
        else:
    ?>
            </div><?php  wp_footer(); ?></body></html>

    <?php
        endif;

    }

    /**
     * Draw a media as background (image or video)
     *  Renderizar uma mídia como background (imagem ou video)
     * @since   0.1
     * @param array     $media      Media values as format, name, mime_type and etc
     * @param string    $post_ID    Post ID
     * @return array                array with url media for image or classes for video
     * 
     *  v0.3.3: Alterado suporte a arquivos do tipo de vídeo 
     */
    protected function landingpage_lpccp_background_media($media, $post_ID){

        //Se variavel não corresponder, para execução de código
        if( $media == false || !is_array($media) ):
            return;
            die();
        endif;

        //Suporte de imagens permitidas e 
        $return['image'] = $media['url'];        

        return $return;
        die();

    }

    /** 
     * Add array javascript var for video in html
     * Adicionar um array javascript para video no html
     * @since 0.1
     * 
     * v0.3.3: Função não invocada devido a suporte a "background videos" removido
     */
    public function landingpage_lpccp_add_video_url(){
        
        //If var is null, return function
        if(is_null($this->videos_url)){
            return;
        }
        
        $html = "<script type='text/javascript'> var videoURL = {";        
        foreach ($this->videos_url as $key => $value) {
            $html .= "post_" . $key . " : '" . $value ."',";
        }
        $html .= "};</script>";

        echo( $html );
    }

    /**   
     * Define background parameter and atributes
     * Define de parametros e attributos para background
     * @since   0.1
     * @param   number  $post_ID            Post ID to find meta_values
     * @param   string  $definedClass       Current element classes
     * @return  string                      String with styles and classes inline
     */
    protected function landingpage_lpccp_get_background_atributes( $post_ID, $definedClass ){

        //Verifica $post_id é tipo número
        if(! is_numeric($post_ID)):
            $error = new WP_Error('ID_error', _x("O 'ID' informado não é um número.", '', 'landingpage-creator-with-custom-posts'), $post_ID);
            return $error;
            die();
        endif;

        //Atributos padrões de background
        $defaultAtributes = array(  'background-color'      => '',  'background-image'       => '',
                                    'background-size'       => '',  'background-position-x'  => '',
                                    'background-position-y' => '',  'background-repeat'      => '',
                                    'color'                 => '');

        //Array vazio para ser preenchido
        $atributes = array();

        //Verifica cor definida e atribui a variavel
        foreach ($defaultAtributes as $key => $value) {
            if( function_exists('get_field') ):
                
                //Retorna conteudo ACF
                $var_content = get_field($key, $post_ID); 

                //Verifica se vazio, se sim pula para proxima iteração
                ( $this->landingpage_lpccp_empty($var_content) )? next($defaultAtributes) : '';

                $atributes[$key] = ($key == 'background-image')?
                    $this->landingpage_lpccp_background_media(get_field($key, $post_ID), $post_ID) : get_field($key, $post_ID);
            else:
                continue;
            endif;
        }

        //Hook para incrementar arrays com mais atributos
        //key   => é o nome do parametro
        //value => é o valor do parametro
        $atributes = array_merge( apply_filters('lpccp_add_background_atributes', (array) $atributes ));

        //Variavel para renderizar html
        $style  = "style=";

        //Hook para adicionar classes via código
        $class  = esc_attr( apply_filters('lpccp_add_class', $definedClass));

        //Construção dos atributos para renderizar corretamente
        foreach ($atributes as $key => $value) {

            //Se vazio, segue para próximo item
            if ( $this->landingpage_lpccp_empty($value) ):
                continue;
            endif;

            //Propriedades
            $style .= $key . ':';

            //Atributos
            if( $key == 'background-image' && is_array($value) ):
                ( array_key_exists('class', $value) )? $class .= ' ' . esc_attr($value['class']) : $style .= "url(" .(string) $value['image'] . ")";
            else:
                $style .= (string) $value;
            endif;

            //Fechando atributo
            $style .= ';';

        }

        //Assimila string de classes cadastradas com a atual
        if( function_exists('get_field') ):

            $class .= ' ' . esc_attr( get_field('custom_class', $post_ID) );

        endif;

        //Mesclando as classes
        $class = "class='" . $class . "'";

        //Retorna resultado
        $html = $class . " " . esc_attr($style);

        return $html;

    }

    /**  
     * Print background atributes
     * Exibe os attributos de background
     * @since   0.1
     * @param   number  $post_ID            Post ID to find meta_values
     * @param   string  $definedClass       Current element classes
     * @return  string                      Print aributes for background
     */
    public function landingpage_lpccp_background_atributes($post_ID, $definedClass){
        echo $this->landingpage_lpccp_get_background_atributes( $post_ID, $definedClass );
    }

    /** 
    *  Verify existing custom fields.
    *  Verifica se os campos existem.
    *  @return bool
    *  @since 0.1
    */
   protected function landingpage_lpccp_fields_keys_exist(){

        //Verifica se classe ACF 4.4.1 existe
        if( class_exists('acf_local')) {
            //Versão classe ACF > 5
            $acf_groups = new acf_local(); //Instanciando classe de grupo ACF 5           
        } elseif ( class_exists('acf_field_group') ){
            $acf_groups = new acf_field_group(); //Instanciando classe de grupos ACF
        } else {
            $acf_groups = null;
        }

        //If 'null' acf plugin is not installed, stop executing
        if(is_null($acf_groups)){
            return false;
        }

        $field_groups = $acf_groups->get_field_groups(); //Retorna grupos existentes

        //If boolean 'false' or empty array
        if(!$field_groups || count($field_groups) <= 0){
                return false;
        }

       //Verificando cada grupo
       foreach ($field_groups as $group => $item ):

            //Retornar object post
            $post = get_post($item['id']);

            //Comparamos slugs para verificar a existência do post
            if( array_key_exists($post->post_name, $this->valid_fields_keys) ):

                $error = new WP_Error();
                $error->add('001', _x('Já existe um grupo com esse nome no banco de dados.','' , 'landingpage-creator-with-custom-posts'));
                return true;
                die();

            endif;

       endforeach;

       //Se não foi criado a instancia de objeto WP_Error
       if(!isset($error)):
            return false;
       endif;

   }

   /**
    *  Estruture custom fields and register then.
    *  Estrutura de campos personalizados e registrar para uso.
    *  @since 0.1
    */
   protected function landingpage_lpccp_register_fields(){

       if( function_exists("register_field_group") )
       {

            //Regras de localização
            $location = $this->landingpage_lpccp_construct_location();

            //LandingPages
            $args_landingpage = array (
                'id'    => 'acf_lpccp_landing-page',
                'title' => _x('Landing Page', '','landingpage-creator-with-custom-posts'),
                'fields' => array (
                        array (
                                'key' => 'field_'. 'lpccp_acf_landing-page' . 0,
                                'label' => _x('Mostrar header?', '', 'landingpage-creator-with-custom-posts'),
                                'name' => 'show-default-header',
                                'type' => 'select',
                                'instructions' => _x('Este campo permite mostrar/esconder o header padrão do template instalado.', '', 'landingpage-creator-with-custom-posts'),
                                'required' => 1,
                                'choices' => array (
                                        'true' =>   _x('Sim', '','landingpage-creator-with-custom-posts' ),
                                        'false' =>  _x('Não', '','landingpage-creator-with-custom-posts' ),
                                ),
                                'default_value' => '',
                                'allow_null' => 0,
                                'multiple' => 0,
                        ),
                        array (
                                'key' => 'field_'. 'lpccp_acf_landing-page' . 1,
                                'label' => _x('Menu personalizado para página', '', 'landingpage-creator-with-custom-posts'),
                                'name' => 'menu-navigation',
                                'type' => 'wysiwyg',
                                'instructions' => _x('Criar uma lista com links de forma que seja como um menu personalizado para página.', '', 'landingpage-creator-with-custom-posts'),
                                'default_value' => '',
                                'toolbar' => 'full',
                                'media_upload' => 'no',
                        ),
                        array (
                                'key' => 'field_'. 'lpccp_acf_landing-page' . 2,
                                'label' => _x('Seções a exibir', '', 'landingpage-creator-with-custom-posts'),
                                'name' => 'sections',
                                'type' => 'relationship',
                                'return_format' => 'object',
                                'post_type' => array (
                                        0 => 'section-lpccp'
                                ),
                                'taxonomy' => array (
                                        0 => 'all',
                                ),
                                'filters' => array (
                                        0 => 'post_type',
                                ),
                                'result_elements' => array (
                                        0 => 'post_type',
                                        1 => 'post_title',
                                ),
                                'max' => '',
                        ),
                ),
                'location' => array (
                        array (
                                array (
                                        'param' => 'post_type',
                                        'operator' => '==',
                                        'value' => 'landingpage-lpccp',
                                        'order_no' => 0,
                                        'group_no' => 0,
                                )
                        )
                ),
                'options' => array (
                        'position' => 'normal',
                        'layout' => 'default',
                        'hide_on_screen' => array (
                                0 => 'permalink',
                                1 => 'the_content',
                                2 => 'excerpt',
                                3 => 'custom_fields',
                                4 => 'discussion',
                                5 => 'comments',
                                6 => 'slug',
                                7 => 'author',
                                8 => 'format',
                                9 => 'send-trackbacks'
                        ),
                ),
                'menu_order' => 0,
        );

        //Adicionando regras para exibição
        $args_landingpage['location'] = array_merge($args_landingpage['location'], $location);
        register_field_group($args_landingpage);

        $args_codigo_adicional = array (
                'id' => 'acf_lpccp_add-cod',
                'title' => _x('Código Adicional', '', 'landingpage-creator-with-custom-posts'),
                'fields' => array (
                        array (
                                'key' => 'field_' . 'lpccp_acf_add-cod' . 0,
                                'label' => _x('Tag > head', '', 'landingpage-creator-with-custom-posts'),
                                'name' => 'before-open-body',
                                'type' => 'textarea',
                                'instructions' => _x('Insira o código para ser inserido dentro da tag "head" do html.', '', 'landingpage-creator-with-custom-posts'),
                                'default_value' => '',
                                'placeholder' => '',
                                'maxlength' => '',
                                'rows' => '',
                                'formatting' => 'html',
                        ),
                        array (
                                'key' => 'field_' . 'lpccp_acf_codigo-adicional' . 1,
                                'label' => _x('Tag > body [superior]', '', 'landingpage-creator-with-custom-posts'),
                                'name' => 'code-after-body',
                                'type' => 'textarea',
                                'instructions' => _x('Insira o código para ser inserido após a abertura da tag body.', '', 'landingpage-creator-with-custom-posts'),
                                'default_value' => '',
                                'placeholder' => '',
                                'maxlength' => '',
                                'rows' => '',
                                'formatting' => 'html',
                        ),
                        array (
                                'key' => 'field_' . 'lpccp_acf_codigo-adicional' . 2,
                                'label' => _x('Tag > body [inferior]', '', 'landingpage-creator-with-custom-posts'),
                                'name' => 'after-close-body',
                                'type' => 'textarea',
                                'instructions' => _x('Insira o código para ser inserido antes do fechamento da tag body.', '', 'landingpage-creator-with-custom-posts'),
                                'default_value' => '',
                                'placeholder' => '',
                                'maxlength' => '',
                                'rows' => '',
                                'formatting' => 'html',
                        ),
                ),
                'location' => array (
                        array (
                                array (
                                        'param' => 'post_type',
                                        'operator' => '==',
                                        'value' => 'landingpage-lpccp',
                                        'order_no' => 0,
                                        'group_no' => 0,
                                ),
                        )
                ),
                'options' => array (
                        'position' => 'normal',
                        'layout' => 'default'
                ),
                'menu_order' => 1,
        ); //Código Adicional

        //Adicionando regras para exibição
        $args_codigo_adicional['location'] = array_merge($args_codigo_adicional['location'], $location);
        register_field_group($args_codigo_adicional);

        /* Sections */
        register_field_group(array (
                'id' => 'acf_lpccp_add-class',
                'title' => _x('Classes', '', 'landingpage-creator-with-custom-posts'),
                'fields' => array (
                        array (
                                'key' => 'field_' . 'lpccp_acf_add-class' . 0,
                                'label' => _x('Adicione classes', '', 'landingpage-creator-with-custom-posts'),
                                'name' => 'custom_class',
                                'type' => 'text',
                                'instructions' => _x('Adicione suas próprias classes para maior personalização. Separe as classes com espaço (ex: classe1 classe2).', '','landingpage-creator-with-custom-posts'),
                                'default_value' => '',
                                'placeholder' => _x('Adicione sua classes para o elemento.', '' , 'landingpage-creator-with-custom-posts'),
                                'prepend' => '',
                                'append' => '',
                                'formatting' => 'none',
                                'maxlength' => '',
                        ),
                ),
                'location' => array (
                        array (
                                array (
                                        'param' => 'post_type',
                                        'operator' => '==',
                                        'value' => 'section-lpccp',
                                        'order_no' => 0,
                                        'group_no' => 0,
                                ),
                        )
                ),
                'options' => array (
                        'position' => 'side',
                        'layout' => 'default'
                ),
                'menu_order' => 0,
        )); // Adicionar Classes

        register_field_group(array (
                'id' => 'acf_lpccp_background',
                'title' => _x('Background', '', 'landingpage-creator-with-custom-posts'),
                'fields' => array (
                        array (
                                'key' => 'field_' . 'acf_lpccp_background' . 0,
                                'label' => _x('Cor do Fundo', '', 'landingpage-creator-with-custom-posts'),
                                'name' => 'background-color',
                                'type' => 'color_picker',
                                'instructions' => _x('Selecione uma cor para o background.', '', 'landingpage-creator-with-custom-posts'),
                                'default_value' => '',
                        ),
                        array (
                                'key' => 'field_' . 'acf_lpccp_background' . 1,
                                'label' => _x('Cor do Texto', '', 'landingpage-creator-with-custom-posts'),
                                'name' => 'color',
                                'type' => 'color_picker',
                                'instructions' => _x('Selecione uma cor padrão para o texto.', '', 'landingpage-creator-with-custom-posts'),
                                'default_value' => '',
                        ),
                        array (
                                'key' => 'field_' . 'acf_lpccp_background' . 2,
                                'label' => _x('Imagem para fundo', '','landingpage-creator-with-custom-posts'),
                                'name' => '',
                                'type' => 'tab',
                        ),
                        array (
                                'key' => 'field_' . 'lpccp_acf_background' . 3,
                                'label' => _x('Imagem', '','landingpage-creator-with-custom-posts'),
                                'name' => 'background-image',
                                'type' => 'file',
                                'instructions' => _x('Selecione uma Imagem como background.', '', 'landingpage-creator-with-custom-posts'),
                                'save_format' => 'object',
                                'library' => 'all',
                        ),
                        array (
                                'key' => 'field_' . 'lpccp_acf_background' . 4,
                                'label' => _x('Tamanho', '', 'landingpage-creator-with-custom-posts'),
                                'name' => 'background-size',
                                'type' => 'select',
                                'choices' => array (
                                        'cover' => _x('Cobrir','','landingpage-creator-with-custom-posts'),
                                        'contain' => _x('Conteúdo','','landingpage-creator-with-custom-posts'),
                                        'auto' => _x('Auto', '','landingpage-creator-with-custom-posts'),
                                ),
                                'default_value' => 'auto : ' . _x('Auto', '', 'landingpage-creator-with-custom-posts'),
                                'allow_null' => 0,
                                'multiple' => 0,
                        ),
                        array (
                                'key' => 'field_' . 'lpccp_acf_background' . 5,
                                'label' => _x('Horizontal', '','landingpage-creator-with-custom-posts'),
                                'name' => 'background-position-x',
                                'type' => 'select',
                                'choices' => array (
                                        'left' => _x('Esquerda', '', 'landingpage-creator-with-custom-posts'),
                                        'center' => _x('Centro', '','landingpage-creator-with-custom-posts'),
                                        'right' => _x('Direita', '','landingpage-creator-with-custom-posts'),
                                ),
                                'default_value' => 'center : ' . _x('Centro', '', 'landingpage-creator-with-custom-posts'),
                                'allow_null' => 0,
                                'multiple' => 0,
                        ),
                        array (
                                'key' => 'field_' . 'lpccp_acf_background' . 6,
                                'label' => _x('Vertical', '','landingpage-creator-with-custom-posts'),
                                'name' => 'background-position-y',
                                'type' => 'select',
                                'choices' => array (
                                        'top' => _x('Topo', '','landingpage-creator-with-custom-posts'),
                                        'center' => _x('Centro', '', 'landingpage-creator-with-custom-posts'),
                                        'bottom' => _x('Inferior', '','landingpage-creator-with-custom-posts'),
                                ),
                                'default_value' => 'center : ' . _x('Centro', '','landingpage-creator-with-custom-posts'),
                                'allow_null' => 0,
                                'multiple' => 0,
                        ),
                        array (
                                'key' => 'field_' . 'lpccp_acf_background' . 7,
                                'label' => _x('Repetição', '', 'landingpage-creator-with-custom-posts'),
                                'name' => 'background-repeat',
                                'type' => 'select',
                                'choices' => array (
                                        'repeat' => _x('Repetir', '', 'landingpage-creator-with-custom-posts'),
                                        'repeat-x' => _x('Repetir Horizontalmente', '','landingpage-creator-with-custom-posts'),
                                        'repeat-y' => _x('Repetir Verticalmente', '', 'landingpage-creator-with-custom-posts'),
                                        'no-repeat' => _x('Não Repetir', '', 'landingpage-creator-with-custom-posts'),
                                        'inherit' => _x('Padrão', '','landingpage-creator-with-custom-posts'),
                                ),
                                'default_value' => 'inherit : ' . _x('Padrão', '', 'landingpage-creator-with-custom-posts'),
                                'allow_null' => 0,
                                'multiple' => 0,
                        ),
                ),
                'location' => array (
                        array (
                                array (
                                        'param' => 'post_type',
                                        'operator' => '==',
                                        'value' => 'section-lpccp',
                                        'order_no' => 0,
                                        'group_no' => 0,
                                ),
                        )
                ),
                'options' => array (
                        'position' => 'side',
                        'layout' => 'default',
                        'hide_on_screen' => array() 
                ),
                'menu_order' => 0,
        )); //ACF Background

        /**
        * Argumentos para criação de colunas de conteúdo
        * @since 0.1
        */
        $col_args = array (
                'id' => 'acf_lpccp_columns',
                'title' => _x('Landing Page Creator With Custom Posts', '','landingpage-creator-with-custom-posts'),
                'fields' => array (
                        array (
                                'key'    => 'field_lpccp_acf_columns-0',
                                'label'  => _x('Layout', '','landingpage-creator-with-custom-posts'),
                                'name'   => 'qtd-columns',
                                'layout' => 'horizontal',
                                'type'   => 'radio',
                                'choices' => array (
                                        '1' => _x('Uma coluna', '', 'landingpage-creator-with-custom-posts'),
                                        '2' => _x('Duas colunas', '', 'landingpage-creator-with-custom-posts'),
                                        '3' => _x('Três colunas', '','landingpage-creator-with-custom-posts'),
                                        '4' => _x('Quatro colunas', '','landingpage-creator-with-custom-posts'),
                                ),
                                'default_value' => '1',
                                'allow_null' => 0,
                                'multiple' => 0,
                        )

                ),
                'location' => array (
                        array (
                                array (
                                    'param' => 'post_type',
                                    'operator' => '==',
                                    'value' => 'section-lpccp',
                                    'order_no' => 0,
                                    'group_no' => 0,
                                ),
                        ),
                ),
                'options' => array (
                        'position' => 'normal',
                        'layout' => 'default',
                        'hide_on_screen' => array (
                                0 => 'permalink',
                                1 => 'the_content',
                                2 => 'excerpt',
                                3 => 'custom_fields',
                                4 => 'discussion',
                                5 => 'comments',
                                6 => 'revisions',
                                7 => 'slug',
                                8 => 'author',
                                9 => 'format',
                                10 => 'featured_image',
                                11 => 'categories',
                                12 => 'tags',
                                13 => 'send-trackbacks',
                        ),
                ),
                'menu_order' => 0,
        ); // Columns

        //Juntando arrays de argumentos
        $col_args['fields'] = array_merge($col_args['fields'],
                            $this->landingpage_lpccp_construct_columns() );

        //Registrando os campos
        register_field_group($col_args);

       }
    }

    /**
     * Function to construct columns and exibition rules
    *  Função para contruir as colunas e regras de exibição
    *  @since 0.1
    */
    protected function landingpage_lpccp_construct_location(){

        $location = array();

        if( is_array( $this->lpccp_pages_ids) ):

            foreach ($this->lpccp_pages_ids as $key => $value):
                $location[] = array(
                    array (
                        'param'     => 'page',
                        'operator'  => '==',
                        'value'     => $value,
                        'order_no'  => 0,
                        'group_no'  => 1,
                ));
            endforeach;

        endif;

        return $location;
    }

    /**
     * Estruture repetead values in custom fields.
    *  Estrutura para valores de campos personalizados repetidos.
    *  @since 0.1
    */
    protected function landingpage_lpccp_construct_columns(){

        $columns_array = []; //Inicializado variavel
        $i = 0; //Interação
        $field = (string) 1; //ID do field
        $col = 1; //Coluna atual

        //Adiciona regras
        function conditional($cont){
            $array = array();
            for ($index = 0; $index < $cont; $index++) {
                $array[$index] = array(
                    'field' => 'field_lpccp_acf_columns-0',
                    'operator' => '!=',
                    'value' => (string) $index + 1
                );
            }
            return $array;
        }

        while($i < 8):

            $columns_array[$i] = array (
                'key' => 'field_' . 'lpccp_acf_columns-' . $field,
                'label' => $col . 'ª ' . _x('Coluna', '', 'landingpage-creator-with-custom-posts'),
                'name' => 'tab-col',
                'type' => 'tab'
            );

            if($col > 1):

                $columns_array[$i]['conditional_logic'] = array(
                    'status' => 1,
                    'allorany' => 'all',
                    'rules' => conditional($col - 1)
                );

            endif;


            $i++; //incrementa
            $field++; //incrementa

            $columns_array[$i] = array (
                'key'               => 'field_' . 'acf_columns-' . $field,
                'label'             => _x('Coluna', '', 'landingpage-creator-with-custom-posts'),
                'name'              => 'column-' . $col,
                'type'              => 'wysiwyg',
                'default_value'     => '',
                'toolbar'           => 'full',
                'media_upload'      => 'yes',
            );

            $i++; //incrementa
            $col++; //incrementa
            $field++; //incrementa

        endwhile;

        return $columns_array;
   }

   /**
    *  Verify if exist function and data then print values .
    *  Verifica se existe função e dados assim imprime conteúdo.
    *  @since 0.3
    * 
    * v0.3: Criado
    */
    public function landingpage_lpccp_check_exist($contentIdentify){

        /* Verifica se função ACF está implementado*/
        if( !function_exists('get_field') ){
            return false;
        }

        /* Retorna conteúdo registrado em ACF*/
        $var_content = get_field($contentIdentify);

        //Verifica se existe conteúdo e Imprime dados
        ( !$this->landingpage_lpccp_empty($var_content) )? the_field($contentIdentify) : '';      
   }

    /**  
    *  Verify if var is empty, compatibility with PHP 5.4<= .
    *  Verifica se variavel é vazia, compatibilidade com PHP 5.4<=.
    *  @since 0.3
    *
    *  v0.3.3: Alterado lógica devido a erro de verificação com construtor 'empty'
    */
    public function landingpage_lpccp_empty($var) {

        /* Compatibilidade com PHP 5.4> */    
        //Verifica se existe conteúdo    
        $empty = true;

        /* Se array ou objeto, conta quantidade de itens */
        if(is_array($var) || is_object($var)){
            return (count($var) > 0)? false : true;
        }

        //Int
        if(is_int($var)){
            return ($var > 0)? false : true;
        }

        //String
        if(is_string($var)){
            return ($var != "")? false : true;
        }

        //Nulo
        if(is_null($var)){
            return true;
        }

        return $empty;
    }

}

    /**
     * Verify instance of class has initiate.
    *  Verifica se instancia da classe for instanciada.
    *  @since 0.1
    */
    function lpccp()
    {
        global $lpccp;

        if( !isset($lpccp) )
        {
            $lpccp = new landingpage_LPCCP();
        }

        return $lpccp;
    }

    lpccp();

endif; //class_exists
