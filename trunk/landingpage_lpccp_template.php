<?php

/* Template Name: Landing Page
 * Template para construção de landingpages
 * Making Pie
 * Menu: Área com menu personalizado via admin
 * v0.1: Criado
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


/**
* Using ACF to return data from admin and defining default header
* Utilizando plugin "ACF" retornamos dados via admin e definindo se exibe header padrão do template Wordpress
* @since 0.1
* v0.1 : Criado
*/

$pathFile = PLUGIN_DIR . 'templates-parts/'; //Caminho dos arquivos de templates de seção
$lpccp->landingpage_lpccp_show_default_section('header'); //Função de mostrar header

?>

    <main class="landingpage-lpccp main-content">

        <?php

            //Looping
            if( have_posts() ):

                while( have_posts() ): the_post();

                    /**
                     * Add content after body
                     * Inserção de conteúdo depois do body
                     * @since 0.1
                     * v0.1 : Criado
                     * v0.3.3: Substituido por função de classe $lpccp para melhor controle
                     */
                    $lpccp->landingpage_lpccp_check_exist('code-after-body'); 

                    /**
                     * Verify functions exists and include template for navigation
                     * Verifica se funçao existe e se existe conteúdo "navigation"
                     * @since 0.1
                     * v0.1 : Criado
                     * v0.3.3 : Corrigido a verificação de variavel com suporte a PHP 5.4
                     */
                    if( function_exists('get_field') ){

                       /* Verifica se função retorna conteúdo, se não para aplicação*/
                       $nav_content = get_field('menu-navigation');
                        //Renderiza o menu de navegação
                       ( $nav_content )? include_once $pathFile . 'navigation.php' : '';

                       /* Retorna array de posts associados a página */
                       $sections = get_field('sections');
                       if($lpccp->landingpage_lpccp_empty($sections) ){
                            return die();
                       }

                       /**
                        * Loop of section array and show the content and style defined
                        * Percorre array de posts de seção e mostra conteúdo e estilo definido 
                        * @since 0.1
                        * v0.3.3: Correção
                        */
                       foreach ( $sections as $key => $post ){

                        $secao = array(
                            'id' => $post->ID,
                            'content' => $post->post_content,
                            'qtd' => get_field('qtd-columns', $post->ID)
                        );

                    ?>

                        <section
                            id="<?php echo esc_attr($post->post_name); ?>"
                            <?php $lpccp->landingpage_lpccp_background_atributes($secao['id'], 'lpccp-column section-'.$secao['qtd'].'-columns'); ?>>
                    
                                <div class="container">
                            
                                    <div class="post-content">
                                        <?php
                                            /* Exibir Conteúdo */
                                            echo apply_filters('the_content', $secao['content']);
                                        ?>
                                    </div><!-- post-content -->
                            
                                    <div class="row">

                                        <?php 
                                            $i = 1;
                                            while($i <= $secao['qtd']): //Qtd de colunas
                                        ?>
                                                <div class="block col-sm-<?= (string) 12 / $secao['qtd'] ?>">                            
                                                    <?php $lpccp->landingpage_lpccp_check_exist('column-'.$i); ?>                                    
                                                </div><!-- post-content -->
                                        <?php
                                            $i++;
                                            endwhile;
                                        ?>
                                        
                                    </div><!-- row -->
                            
                                </div><!-- container -->
                            
                            </section><!-- contato -->

                    <?php 
                       }
                        
                    } //if

                endwhile;

                wp_reset_query();
                wp_reset_postdata();

            endif;

        ?>

    </main><!-- .template-main -->

<?php

$lpccp->landingpage_lpccp_show_default_section('footer'); //Imprimir o footer
