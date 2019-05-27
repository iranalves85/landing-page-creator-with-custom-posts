<?php

/*
    Class - Admin Page configuration
    @since 0.1
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class landingpage_LPCCP_options{

    //variaveis
    protected $response;

    function __construct(){

        //Var Values
        define('SETTINGS_SLUG', 'landingpage-lpccp-plugin' );
        define('OPTION_NAME', 'lpccp_pages_enabled' );

        //Hooks
        add_action('admin_notices', array($this, 'lpccp_notices') );
    }

    /* Register a menu in Settings
    *  Registrando menu na aba de "configuração"
    *  @since 0.1
    */
    public function lpccp_add_admin_menu(){

        add_options_page(
            _x('Landing Page Creator With Custom Posts Options', '', 'landingpage-creator-with-custom-posts'),
            _x('Landing Page Creator With Custom Posts', '','landingpage-creator-with-custom-posts'),
            'manage_options',
            SETTINGS_SLUG,
            array(
                $this, 'lpccp_options_page'
            )
        );
    }

    /* Register a link in Plugin List Page
    *  Registrando links personalizado para a página listagem de plugins
    *  @return string $links      current html
    *  @since 0.1
    */
    public function lpccp_add_plugin_links( $links ) {
        $links = array_merge( array(
          '<a href="' . esc_url( admin_url( '/options-general.php?page=' . SETTINGS_SLUG ) ) . '">' . _x( 'Configuração', 'landingpage-creator-with-custom-posts' ) . '</a>'
        ), $links );
        return $links;
    }

    /* Defining Settings Page
    *  Definindo a página de configurações
    *  @since 0.1
    */
    public function lpccp_options_page() {

            if ( !current_user_can( 'manage_options' ) )  {
                    wp_die( _x( 'Você não tem permissão para acessar essa página.', '', 'landingpage-creator-with-custom-posts' ) );
            }

            //array com valores definidos
            $page_list = $this->lpccp_inputs_enabled();

    ?>
            <div class="wrap">

                <div class="about">
                    <h1><?= _x('Landing Page Creator With Custom Posts', '', 'landingpage-creator-with-custom-posts'); ?> - v<?= VERSION ?></h1>

                    <div style="display: block;padding:10px;background-color: #deebf1;position:relative;">

                        <p style="margin-top:0px;"><?= _x('Desenvolvido por','', 'landingpage-creator-with-custom-posts'); ?> Iran Alves [https://github.com/iranalves85], <?= _x('obrigado por usar meu plugin!', '', 'landingpage-creator-with-custom-posts'); ?> <?= _x('Se esse plugin o ajudou de alguma forma, me pague um café ou avalie o plugin no repositório de plugins Wordpress, agradeço imensamente.','','landingpage-creator-with-custom-posts'); ?> <strong><?= _x('Wordpress é amor!','', 'landingpage-creator-with-custom-posts') ?></strong>

                          <span style="color: #999;">
                              <small class=""><?= _x('Procura um desenvolvedor para seu projeto?','','landingpage-creator-with-custom-posts'); ?> <strong>iranjosealves@gmail.com</strong> | <a target="_blank" href="https://makingpie.com.br">makingpie.com.br</a></small>
                          </span>

                        </p>

                        <a href="https://goo.gl/dN6U3T"
                           target="_blank" class="button button-primary link">
                        <?= _x('Doar','','landingpage-creator-with-custom-posts'); ?>
                        </a>
                        <a href="https://wordpress.org/support/plugin/landing-page-creator-with-custom-posts/reviews/#new-post" target="_blank" class="button link">
                        <?= _x('Avalie meu plugin','','landingpage-creator-with-custom-posts'); ?>
                        </a>
                    </div>

                </div><!-- about -->

                <div class="configuration">
                    <h1><?= _x('Configuração', '','landingpage-creator-with-custom-posts'); ?></h1>
                    <h2><?= _x('Habilitar "Landing Page" em páginas nativas.', '', 'landingpage-creator-with-custom-posts') ?></h2>
                    <p><?= _x('Em qual páginas habilitar?', '','landingpage-creator-with-custom-posts') ?></p>

                    <form name="lpccp-enable-pages" method="post" action="">
                        <table class="wp-list-table widefat fixed striped pages">
                           <thead>
                               <tr>
                                   <td><?= _x('Selecionar', '','landingpage-creator-with-custom-posts'); ?></td>
                                   <td><?= _x('Título', '','landingpage-creator-with-custom-posts'); ?></td>
                               </tr>
                           </thead>
                       <?php
                              echo $page_list;
                       ?>
                           <tfoot>
                               <tr>
                                   <td><?= _x('Selecionar', '', 'landingpage-creator-with-custom-posts'); ?></td>
                                   <td><?= _x('Título', '','landingpage-creator-with-custom-posts'); ?></td>
                               </tr>
                           </tfoot>
                        </table>

                        <p class="inline-edit-save">
                            <input type="hidden" name="action" value="lpccp-enable-pages" />
                            <?php wp_nonce_field('lpccp-enable-pages'); ?>
                            <input type="submit" value="<?= _x('Salvar','','landingpage-creator-with-custom-posts'); ?>" class="button button-primary save alignright" />
                        </p>

                    </form>

                </div><!-- configuration -->

            </div><!-- wrap -->

<?php

    }

    /*
      Adding data in BD on save form.
      Função que insere os dados no wordpress para armazenar e usar posteriormente
      @return string            Message to show on save
      @since 0.1
    */
    protected function lpccp_update_values(){

        $option_name = OPTION_NAME;

        //Verifica se existe dados enviados
        $request = ( isset($_POST) && is_array($_POST) )? $_POST : false ;

        //Se falso, para execeção
        if(!$request ):
            return;
        endif;

        //Valida o formulário de envio das configurações
        if(!wp_verify_nonce($request['_wpnonce'], 'lpccp-enable-pages')):
            return;
            die();
        endif;

        //Array para inserir
        $to_options = $request['lpccp-enable-item'];

        //Retorna valor armazendo se houver
        $option_in_wp = ( get_option( $option_name ) );

        //Se não retornar array de dados no banco
        //adicionar valores atuais
        if(!is_array($option_in_wp)):
          $compare = $to_options;
        else:
          //Verifica a diferenças nos arrays
          $compare = array_diff_assoc($to_options, $option_in_wp);
        endif;

        //Se a opção existir no BD
        if ( $option_in_wp !== false ) {
            //Atualiza valores
            $status = update_option( $option_name, maybe_serialize($compare), 'yes');

        }
        else {
            //Insere valores
            $status = add_option( $option_name, maybe_serialize($compare), null, 'no' );
        }

        return "<div id='notice' class='updated fade'><p>" . _x('Configurações salvas.', '', 'landingpage-creator-with-custom-posts') . "</p></div>";

    }

    /* Default values to show on inputs in table
    *  Valores padrões a exibir nos inputs do formulário
    *  @return string $html     Table estruture and form in html
    * @since 0.1
    */
    protected function lpccp_inputs_enabled(){

        $option_name = OPTION_NAME;

        //Deserilizar array
        $options_values = maybe_unserialize(get_option($option_name) );
        $options_flip = (is_array($options_values))? array_flip($options_values) : array();

        //Retorna as páginas
        $pages = get_pages();
        $html = "";

        //Checa e adicionar valor do banco
        foreach ( $pages as $page => $value ) :

            $checked = ( array_key_exists( $value->ID, $options_flip) )? 'checked="checked"' : '';

            $id     = (string) $value->ID;
            $title  = (string) $value->post_title;

            $html   .= '<tr>
                            <td>
                                <input type="checkbox" name="lpccp-enable-item[]" value="'
                                . $id .'" ' . $checked . '/>
                            </td>
                            <th>
                                <a target="_blank" href="' .  get_permalink($id) . '">'
                                    . $title . '</a>
                            </th>
                        </tr>';

        endforeach;

        return $html;

    }

    /* Send data, verify errors and show message
    *  Função que submete os dados e mostra mensagem de erro
    *  @since 0.1
    */
    public function lpccp_notices(){

        //Submete os campos para BD
        $this->response = $this->lpccp_update_values();
        $responseData = $this->response;

        if( function_exists('empty') ){
            /*For PHP 5.6>= */
            if( !empty($responseData) ):
                echo $responseData;
            endif;
        }
        else{
            /*For PHP 5.4<=*/            
            if( $responseData ):
                echo $responseData;
            endif;
        }

        
    }//lpccp_notices

}
