<section class='addi-bnpn'>
    <div class='addi-bnpn-container'>
        <div class='addi-bnpn-content'>
            <div class='addi-bnpn-header'>
                <img src='<?php echo plugins_url( '../../assets/addi-pse-logo.svg' , __FILE__ ); ?>' />
                <span>Compra como tú prefieres</span>
            </div>
            <span class='addi-bnpn-content-description'>
                Con Addi tienes más formas de pagar. 
            </span>
            <div class='addi-bnpn-card-content'>
                <div class='addi-bnpn-content-card'>
                    <div class='addi-bnpn-content-card-title'>
                        <span>
                            Crédito
                        </span>
                        <figure id="refresh-icon">
                            <img id="refresh-icon-image" alt="currency" src='<?php echo plugins_url( '../../assets/currency-exchange.png' , __FILE__ ); ?>' />
                        </figure>
                    </div>
                    <summary class="addi-bnpn-content-card-summary">
                        Sólo necesitas tu cédula y WhatsApp para pagar en cuotas.  
                    </summary>
                </div>
                <div class='addi-bnpn-content-card'>
                    <div class='addi-bnpn-content-card-title'>
                        <span>
                            Débito con PSE
                        </span>
                        <figure>
                            <img id="pse-icon" alt="pse" src='<?php echo plugins_url( '../../assets/pse.png' , __FILE__ ); ?>' />
                        </figure>
                    </div>
                    <summary class="addi-bnpn-content-card-summary">
                        <span>Sólo debes tener una cuenta bancaria, Nequi o Daviplata para comprar. <span>
                        <?php
                            if (isset($bnpn_discount) && $bnpn_discount > 0) {
                                echo "<span class='addi-bnpn-content-card-discount'>" .
                                    ($bnpn_discount * 100). "% de dcto. por tiempo limitado<span>";
                            }
                        ?>
                    </summary>
                </div>
            </div>
        </div>
    </div>
    <div class='addi-bnpn-footer'>
        Haz clic en "Paga con Addi" para finalizar el pago.
    </div>
</section>