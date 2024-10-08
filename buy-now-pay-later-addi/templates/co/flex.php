<div class='addi_description_container flex'>
    <div style='padding: 20px;'>
        <div class="flex-header">
            <div class="logo">
                <img src='<?php echo plugins_url( '../../assets/ADDI_logo.png' , __FILE__ ); ?>'>
                <?php
                if ($discount) {
                    $discount_msg = "% de descuento";
                    echo "<div class='discount-container-flex'><p class='discount-label-flex'>" .
                        ($discount * 100) . $discount_msg . "</p></div>";
                }
                ?>
            </div>
            <b class="title">Cuotas que se adaptan a ti</b>
        </div>
        <div class='mini-frame addi-co-description-block flex'>
            <img style=' height: 28px;width: 28px;' src='<?php echo plugins_url( '../../assets/icons01.svg' , __FILE__ ); ?>'>
            <span class="addi-co-description-detail">Paga en cuotas <b>desde 0% de interés.*</span>
        </div>
        <div class='mini-frame addi-co-description-block with-margin flex'>
            <img style=' height: 28px;width: 28px;' src='<?php echo plugins_url( '../../assets/icons04.svg' , __FILE__ ); ?>'>
            <span class="addi-co-description-detail">Para compras más grandes, <b>paga hasta en 24 cuotas.*</b></span>
        </div>
        <div class='mini-frame addi-co-description-block with-margin flex'>
            <img style=' height: 28px;width: 28px;' src='<?php echo plugins_url( '../../assets/icons02.svg' , __FILE__ ); ?>'>
            <span class="addi-co-description-detail">Solo necesitas <b>tu cédula y WhatsApp</b> para pedir tu crédito.</span>
        </div>
        <div class='mini-frame addi-co-description-footer with-margin flex'>

            <span class="addi-co-description-detail">Haz clic en <b>"Pagar con Addi"</b> para continuar.</span>
        </div>
        <div class='mini-frame addi-co-description-footer with-margin flex'>
            <span class="addi-co-description-detail">
                *Sujeto a criterios de elegibilidad. La opción de 0% está sujeta a <a href="https://co.addi.com/tyc-0-interes">T&C</a> de Addi.</span>
        </div>
    <?php
    if ($min_amount && $total < intval($min_amount_int)) {
        echo "<div class='constraint-container-flex'>Solo disponible para compras superiores a $" .
            $min_amount . "</div>";
    }

    if ($max_amount && $total > intval($max_amount_int)) {
        echo "<div class='constraint-container-flex'>Solo disponible para compras menores a $".
            $max_amount . "</div>";
    }
    ?>
    </div>

</div>
