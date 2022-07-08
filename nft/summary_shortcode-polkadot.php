<?php
//Code to work with Acala to transfer aUSD via Polkadot.js.

//glob.php is where we store our global constants (variables in large caps). You can create your own constants
require_once('glob.php');

$payment_wallet = '';
$new_balance = 0;
$min_payment = 0;

function summary_shortcode( $attr ) {

    global $payment_wallet, $new_balance, $min_payment;

    $output = '';
    $form_id = FORM_ID_FOR_SUMMARY;
    $total_count = 0;
    $contract_address = '';
    $payment_wallet = '';
    $new_balance = 0;
    $min_payment = 0;
    $user_id = '52'; //Enter your own user ID here

    //Polkadot.js payer's wallet for demo purposes.
    $wallet_address = '5HBf26yVWCYyL1JB...vSHaChcNHjL7T';

    $search_criteria = array(
        'status'        => 'active',
        'field_filters' => array(
            'mode'  => 'all',
            array(
                'key'       => '1',
                'operator'  => 'is',
                'value'     => $wallet_address
            )
        )
    );

    $sorting = array( 'key' => '15', 'direction' => 'DESC' );

	//This is where you specify your data. Feel free to change below to your values.
    $contract_address = $result[0]['18'];
    $payment_wallet = $result[0]['17'];
    $new_balance = $result[0]['5'];
    $min_payment = $result[0]['6'];

    if ( !empty( $result ) ) {

        $output .= '<div class="container">
                        <h4 class="sub-header">' . esc_html__( 'Account Summary', 'card' ) . '</h4>';
        $output .= '<div class="table-responsive">
                        <table class="table table-striped">
                            <tbody>
                                <tr class="d-flex">
                                    <td class="col">' . esc_html__( 'Account', 'card' ) . '</td>
                                    <td class="col text-wrap wallet">' . $result[0]['1'] . '</td>
                                </tr>
                                <tr class="d-flex">
                                    <td class="col">' . esc_html__( 'Previous Balance', 'card' ) . '</td>
                                    <td class="col text-wrap">' . $result[0]['8'] . '</td>
                                </tr>
                                <tr class="d-flex">
                                    <td class="col">' . esc_html__('Payment, Credits', 'card' ) . '</td>
                                    <td class="col text-wrap">' . $result[0]['11']. '</td>
                                </tr>
                                <tr class="d-flex">
                                    <td class="col">' . esc_html__('Purchases', 'card' ) . '</td>
                                    <td class="col text-wrap">' . $result[0]['12']. '</td>
                                </tr>
                                <tr class="d-flex">
                                    <td class="col">' . esc_html__('Fees Charged', 'card' ) . '</td>
                                    <td class="col text-wrap">' . $result[0]['13']. '</td>
                                </tr>
                                <tr class="d-flex">
                                    <td class="col">' . esc_html__('Interest Charged', 'card' ) . '</td>
                                    <td class="col text-wrap">' . $result[0]['14']. '</td>
                                </tr>
                                <tr class="d-flex">
                                    <td class="col">' . esc_html__('New Balance', 'card' ) . '</td>
                                    <td class="col text-wrap">' . $result[0]['5']. '</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>';
        $output .= '</div>';

        $output .= '<div class="row my-4"></div>';
        
        $output .= '<div class="container">
                        <h4 class="sub-header">' . esc_html__( 'Payment Information', 'card' ) . '</h4>';
        $output .= '<div class="table-responsive">
                        <table class="table table-striped">
                            <tbody>
                                <tr class="d-flex">
                                    <td class="col">' . esc_html__( 'Statement Period', 'card' ) . '</td>
                                    <td class="col">' . $result[0]['3'] . '</td>
                                </tr>
                                <tr class="d-flex">
                                    <td class="col">' . esc_html__( 'Statement Date', 'card' ) . '</td>
                                    <td class="col">' . date('F j, Y', strtotime( $result[0]['15'] ) ) . '</td>
                                </tr>
                                <tr class="d-flex">
                                    <td class="col">' . esc_html__( 'New Balance', 'card' ) . '</td>
                                    <td class="col">' . $result[0]['5'] . '<span class="px-2"></span><button type="button" class="btn btn-xs btn-outline-dark" onclick="sendPayment(\'' . $contract_address . '\', \'' . $payment_wallet . '\', \'' . $new_balance . '\');">' . esc_html__( 'Pay New Balance', 'card' ) . '</button></td>
                                </tr>
                                <tr class="d-flex">
                                    <td class="col">' . esc_html__( 'Minimum Payment', 'card' ) . '</td>
                                    <td class="col">' . $result[0]['6'] . '<span class="px-2"></span><button type="button" class="btn btn-xs btn-outline-dark" onclick="sendPayment(\'' . $contract_address . '\', \'' . $payment_wallet . '\', \'' . $min_payment . '\');">' . esc_html__( 'Pay Min Payment', 'card' ) . '</button></td>
                                </tr>
                                <tr class="d-flex">
                                    <td class="col">' . esc_html__( 'Payment Due Date', 'card' ) . '</td>
                                    <td class="col">' . date('F j, Y', strtotime( $result[0]['16'] ) ) . '</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>';
        $output .= '</div>';

    } else {
        $output .= '<div class="alert alert-info" role="alert">' . esc_html__( 'Sorry, your summary is not yet available', 'card' ) . '</div>';
    }
    
    ?>

    <!--Modal UI to display payment results-->
    <div id="paymentModal" class="modal fade" data-backdrop="false" role="dialog" >
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel"><?php esc_html_e( 'Transfer crypto to payment wallet', 'card' ); ?></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div id="payment-results" class="modal-body modal-text">
                    <div class="spinner" role="status">
                    </div>
                    <div class="text-center">
                        <?php esc_html_e( 'Please wait...', 'card' ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ethers/5.5.3/ethers.umd.min.js"></script>

    <!--Include below to allow payment using Polkadot.js-->
    <script src="https://unpkg.com/@polkadot/util/bundle-polkadot-util.js"></script>
    <script src="https://unpkg.com/@polkadot/util-crypto/bundle-polkadot-util-crypto.js"></script>
    <script src="https://unpkg.com/@polkadot/types/bundle-polkadot-types.js"></script>
    <script src="https://unpkg.com/@polkadot/api/bundle-polkadot-api.js"></script>
	<script src="https://unpkg.com/@polkadot/extension-dapp@0.44.1/bundle-polkadot-extension-dapp.js"></script>

    <script type="text/javascript">

        async function sendPayment( contractAddress, toAddress, paymentAmount ) {
         
			 const { WsProvider, ApiPromise } = polkadotApi;
			 const wsProvider = new WsProvider('wss://node-6870830370282213376.rz.onfinality.io/ws?apikey=0f273197-e4d5-45e2-b23e-03b015cb7000');
			 const polkadot = await ApiPromise.create({ provider: wsProvider });
		  
			const { web3Accounts, web3Enable, web3FromSource, web3FromAddress } = polkadotExtensionDapp ;
			const { stringToHex } = polkadotUtil;
			
			// this call fires up the authorization popup
			const extensions = await web3Enable('CryptCard');

			if (extensions.length === 0) {
				// no extension installed, or the user did not accept the authorization
				// in this case we should inform the use and give a link to the extension
				return;
			}

			// we are now informed that the user has at least one extension and that we
			// will be able to show and use accounts
			const allAccounts = await web3Accounts();

			// `account` is of type InjectedAccountWithMeta 
			// We arbitrarily select the first account returned from the above snippet
			const account = allAccounts[0];
			
			//Mel to send token
			// the address we use to use for signing, as injected
			const SENDER = account.address;

			// finds an injector for an address
			const injector = await web3FromAddress(SENDER);

            //Convert aUSD amount to 12 decimal places
            const paymentAmountBigNum = ethers.utils.parseUnits(String(paymentAmount), 12);

			const transfer = polkadot.tx.currencies.transfer(toAddress, { Token: 'AUSD' }, paymentAmountBigNum);

		   // Sign and send the transaction using our account
		   const hash = await transfer.signAndSend(SENDER, { signer: injector.signer });

		   console.log('Transfer sent with hash', hash.toHex());

            //Display the payment pop-up
            jQuery("#paymentModal").modal();
            jQuery("#paymentModal").modal("show");


        }

    </script>

    <?php

    return $output;
}

?>