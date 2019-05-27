<?php

/* Making Pie
 * Menu: Área com menu personalizado via admin
 * v0.1: Criado
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

?>

<nav class="lpccp-menu-navegation">

  <div class="container">

    <?php
        /* Verifica se funçao existe e se existe conteúdo
         * v0.1 : Criado
         * v0.3 : Substituido por função de classe $lpccp para melhor controle
        */
        $lpccp->landingpage_lpccp_check_exist('menu-navigation');
    ?>

  </div><!-- container -->

</nav><!-- Menu Personlizado  -->
