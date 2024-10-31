<?php
function rentmy_checkout_payment_template()
{

    $payment_gateways = $GLOBALS['rm_payment_gateways']['data'];
    if (isset($_POST['rentmy-checkout'])) {
        //  $order_details = null;
        //  $checkout_complete = new RentMy_Checkout();
        //  $order_details = $checkout_complete->doCheckout();
    }

    if (!empty($GLOBALS['payment_labels'])) {
        $payment_label = $GLOBALS['payment_labels'];
    }
    if (!empty($GLOBALS['checkout_labels'])) {
        $checkout_label = $GLOBALS['checkout_labels'];
    }
?>
    <div class="rentmy-plugin-manincontent">
        <div class="rentmy-checkout-payment">
            <ul class="rentmy-progressbar">
                <li>
                    <a class="btn btn-circle">1</a><br>
                    <?php echo $checkout_label['step_one']; ?>
                </li>
                <li>
                    <a class="btn btn-circle">2</a><br>
                    <?php echo $checkout_label['step_two']; ?>
                </li>
                <li class="active">
                    <a class="btn btn-circle">3</a><br>
                    <?php echo $checkout_label['step_three']; ?>
                </li>
                <li>
                    <a class="btn btn-circle">4</a><br>
                    <?php echo $checkout_label['step_four']; ?>
                </li>
            </ul>


            <div class="payment-type">
                <ul>
                    <?php foreach ($payment_gateways as $gateway) { ?>
                        <?php if ($gateway['type'] == 'online') { ?>
                            <li data-id="credit-card">
                                <input type="hidden" class="rm_payment_gateway_name" value="<?php echo $gateway['name']; ?>" />
                                <input type="hidden" class="rm_payment_gateway_id" value="<?php echo $gateway['id']; ?>" />
                                <?php if ($gateway['name'] == 'Stripe') { ?>
                                    <input type="hidden" class="rm_stripe_key" value="<?php echo $gateway['config']['publishable_key']; ?>" />
                                <?php } ?>
                                <img src="<?php echo plugin_dir_url(__DIR__); ?>assets/images/card.png">
                                <span><?php echo $payment_label['title_credit_card']; ?></span>
                            </li>
                        <?php }
                    }
                    foreach ($payment_gateways as $gateway) {
                        if ($gateway['type'] != 'online') { ?>
                            <li data-id="others" data-type="<?php echo $gateway['type']; ?>" data-ispaid="<?php echo $gateway['config']['is_paid']; ?>">
                                <input type="hidden" class="rm_payment_gateway_name" value="<?php echo $gateway['name']; ?>" />
                                <input type="hidden" class="rm_payment_gateway_id" value="<?php echo $gateway['id']; ?>" />
                                <img src="<?php echo plugin_dir_url(__DIR__); ?>assets/images/pay.png">
                                <span class="rentmy-payment-label-top"><?php echo ucwords($gateway['name']); ?></span>
                            </li>
                    <?php }
                    } ?>
                </ul>
            </div>
            <br>
            <div class="rm-others-payment-container payment-container" class="rentmy-hidden">

                <form id="checkout-others-<?php echo !empty($_GET['step']) ? $_GET['step'] : null; ?>" class="" action="" method="post">
                    <div class="checkout-header">
                        <h2 class="rentmy-payment-label-bottom">Pay Later</h2>
                    </div>

                    <div class="offline-minimum-payment">
                        <?php rentmy_minimum_payment_option(); ?>
                    </div>

                    <div class="rentmy-form-group rentmy-note-group">
                        <label for="">Note</label>
                        <input required type="text" class="rentmy-input-text" name="note" id="rm_payment_note">
                    </div>

                    <div class="rentmy-form-group-100  checkout-back-continue">
                        <a class="back-continue-btn back-btn" name="rentmy-checkout" href="<?php echo home_url('/rentmy-checkout/?step=fulfillment'); ?>"><?php echo $checkout_label['btn_back']; ?></a>
                        <input type="submit" class="back-continue-btn checkout-continue-btn" id="rentmy-btn-checkout-others" data-succeredirect="<?php echo home_url('/rentmy-checkout/?step=complete-order'); ?>" data-step="<?php echo !empty($_GET['step']) ? $_GET['step'] : null; ?>" name="rentmy-checkout" value="<?php echo $checkout_label['btn_continue']; ?>" />

                    </div>
                </form>
            </div>

            <div class="rm-card-payment-container payment-container" class="rentmy-hidden">

                <form id="checkout-<?php echo !empty($_GET['step']) ? $_GET['step'] : null; ?>" class="" action="" method="post" onsubmit="return false;">
                    <div class="checkout-header">
                        <h2><?php echo $payment_label['title_credit_card']; ?></h2>
                    </div>

                    <?php rentmy_minimum_payment_option(); ?>

                    <div class="rentmy-form-row">
                        <div class="form-group">
                            <label><?php echo $payment_label['lbl_name']; ?>*</label>
                            <input class="form-control" id="rm_cardName" name="rm_cardName" placeholder="Name on Card " type="text" autocomplete="cc-name">
                        </div>
                        <div id="card-element" style="display: none;"></div>
                        <div id="other-card-element" style="display:none;">
                            <div class="form-group">
                                <label for="card-number"><?php echo $payment_label['lbl_card_number']; ?>*</label>
                                <input class="form-control" id="rm_cardNo" name="rm_cardNo" inputmode="numeric" type="text" autocomplete="cc-number">

                            </div>
                            <div class="expiration-date-group">
                                <label class="" for=""><?php echo $payment_label['lbl_expiration_data']; ?>*</label>
                                <div class="form-group ">
                                    <select class="form-control" id="rm_expireMonth" name="rm_expireMonth" autocomplete="cc-exp-month">
                                        <option>-Select Month-</option>
                                        <?php for ($i = 1; $i <= 12; $i++) { ?>
                                            <option value="<?php echo ($i > 9) ? $i : '0' . $i; ?>"><?php echo ($i > 9) ? $i : '0' . $i; ?><?php echo ' ' . date('F', strtotime('2020-' . $i . '-01')); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <select class="form-control" id="rm_expireYear" name="rm_expireYear" autocomplete="cc-exp-year">
                                        <option>-Select Year-</option>
                                        <?php for ($i = 0; $i <= 15; $i++) { ?>
                                            <option value="<?php echo (19 + $i); ?>"><?php echo (2019 + $i); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="cvv_number"><?php echo $payment_label['lbl_cvv']; ?>*</label>
                                <input class="form-contro" id="rm_cvv" name="rm_cvv" maxlength="4" autocomplete="cc-csc" inputmode="numeric" placeholder="CVV Number " type="text">
                            </div>
                        </div>

                    </div>
                    <div class="rentmy-form-group-100  checkout-back-continue">
                        <a class="back-continue-btn back-btn" name="rentmy-checkout" href="<?php echo home_url('/rentmy-checkout/?step=fulfillment'); ?>"><?php echo $payment_label['btn_back']; ?></a>
                        <input type="submit" class="back-continue-btn checkout-continue-btn" id="rentmy-btn-checkout-payment" data-succeredirect="<?php echo home_url('/rentmy-checkout/?step=complete-order'); ?>" data-step="<?php echo !empty($_GET['step']) ? $_GET['step'] : null; ?>" name="rentmy-checkout" value="<?php echo $payment_label['btn_continue']; ?>" />
                    </div>
                </form>
            </div>

        </div>
    </div>
    <?php
}


function rentmy_minimum_payment_option()
{

    if (!empty($GLOBALS['payment_labels'])) {
        $payment_label = $GLOBALS['payment_labels'];
    }

    $cart_data = $GLOBALS['rm_cart']['data'];
    $partial_amount = $GLOBALS['rm_configs']['payments']['booking'];
    $currency = $GLOBALS['rm_configs']['currency_format'];
    $total_amount = $GLOBALS['rm_cart']['data']['total'];
    $payment_type = $GLOBALS['rm_configs']['payments']['type'];
    $percentage_or_fixed = ($payment_type == 'percent') ? (($partial_amount / 100) * $total_amount) : $partial_amount;
    $placeholder = str_replace('%amount%', $currency['symbol'] . number_format($percentage_or_fixed, 2), $payment_label['lbl_minimum_payment_placeholder']);
    $uniq_id_val = uniqid();

    if (!empty($partial_amount) || $partial_amount != 0) :


        if ($payment_type == 'percent' && $partial_amount == 100) {
            return;
        }

        if ($cart_data['booking'] >= $cart_data['total']) {
            return;
        }

    ?>

        <br>
        <h6>
            <?php
            if ($payment_type == 'percent') {
                echo str_replace('%amount%', $partial_amount . '%', $payment_label['lbl_minimum_payment']);
            } else {
                echo str_replace('%amount%', $currency['symbol'] . $partial_amount, $payment_label['lbl_minimum_payment']);
            }
            ?>
        </h6>
        <div class="custom-control custom-radio">
            <input checked class="partial-payment-switch custom-control-input" id="amountType1_<?php echo $uniq_id_val; ?>" name="amountType" type="radio" value="fullAmount" data-amount="<?php echo $total_amount; ?>">
            <label class="custom-control-label" for="amountType1_<?php echo $uniq_id_val; ?>"><?php echo $payment_label['lbl_pay_full_amount']; ?></label>
        </div>

        <div class="custom-control custom-radio">
            <input class="partial-payment-switch custom-control-input" id="amountType2_<?php echo $uniq_id_val; ?>" name="amountType" type="radio" value="minimumAmount" data-amount="<?php echo number_format($percentage_or_fixed, 2); ?>">
            <label class="custom-control-label" for="amountType2_<?php echo $uniq_id_val; ?>">
                <?php $partial_pay_now = !empty($total_amount) ? $GLOBALS['RentMy']::currency($percentage_or_fixed, 'pre', 'amount', 'post') : ''; ?>
                <?php echo str_replace('%amount%', ' ' . $partial_pay_now . ' ', $payment_label['lbl_pay_min_amount']) ?>
            </label>
        </div>


        <div class="input-group mb-3">
            <label for="amount-pay"><b><?php echo $payment_label['lbl_amount_to_pay']; ?></b></label>
            <div class="input-group-prepend"><span class="input-group-text" id=""> <?php echo $currency['symbol']; ?> </span></div>
            <input autocomplete="off" class="form-control tamount" id="tamount" name="t_amount" numberonly="" placeholder="<?php echo $placeholder; ?>" required="" type="number" maxlength="15" name="t_amount" payable="<?php echo (float) number_format($percentage_or_fixed, 2); ?>" min="<?php echo (float) number_format($percentage_or_fixed, 2); ?>" max="<?php echo (float) $total_amount; ?>" value="<?php echo $total_amount; ?>">
            <div class="input-group-append"><span class="input-group-text" id="table-cost-addon"> <?php echo $currency['code']; ?> </span>
            </div>
        </div>
        <p class="partial-payment-error" style="color:red; width: 100%"></p>

<?php
    endif;
}
