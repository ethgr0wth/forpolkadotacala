<?php
//Code to work with Acala to transfer aUSD via MetaMask

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

    $wallet_address = "enter user's wallet address here";

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
    <script type="text/javascript">

        async function sendPayment( contractAddress, toAddress, paymentAmount ) {

            let minABI = [
                {
                "constant": false,
                "inputs": [
                    {
                        "name": "to",
                        "type": "address"
                    },
                    {
                        "name": "value",
                        "type": "uint256"
                    }
                ],
                "name": "transfer",
                "outputs": [
                    {
                        "name": "",
                        "type": "bool"
                    }
                ],
                "payable": false,
                "stateMutability": "nonpayable",
                "type": "function"
                }
            ];

            //To bring out the Metamask fox
            try {
                const accounts = await window.ethereum.request({
                    method: "eth_requestAccounts",
                });

            } catch (error) {
                alert("<?php esc_html_e( 'Please connect your wallet', 'card' ); ?>");

                //Redirect to main page if there's no wallet
                document.location.href = "<?php echo site_url(); ?>";
            }

            // A Web3Provider wraps a standard Web3 provider, which is
            // what MetaMask injects as window.ethereum into each page
            const provider = new ethers.providers.Web3Provider(window.ethereum);

            // The MetaMask plugin also allows signing transactions to
            // send ether and pay to change state within the blockchain.
            // For this, you need the account signer...
            const signer = provider.getSigner();

            const walletAddress = await signer.getAddress();

            console.log("Payer Wallet: " + walletAddress);
            console.log("Payment Amount: " + paymentAmount);
            console.log("Chain ID: ", window.ethereum.networkVersion);

            //Display the payment pop-up
            jQuery("#paymentModal").modal();
            jQuery("#paymentModal").modal("show");

            try {
                const contract = new ethers.Contract(contractAddress, minABI, provider);
                
                // The Contract is currently connected to the Provider,
                // which is read-only. You need to connect to a Signer, so
                // that you can pay to send state-changing transactions.
                const contractWithSigner = contract.connect(signer);

                // Convert payment amount to 18 decimal places
                //const paymentAmountBigNum = ethers.utils.parseUnits(String(paymentAmount), 18);

                //Mel: Convert aUSD amount to 12 decimal places
                const paymentAmountBigNum = ethers.utils.parseUnits(String(paymentAmount), 12);

                // Send token to payment wallet
                const tx = await contractWithSigner.transfer(toAddress, paymentAmountBigNum);
                //const tx = await contractWithSigner.transfer( toAddress, paymentAmountBigNum);

                // The transaction that was sent to the network
                console.log(`Transaction hash: ${tx.hash}`);

                // Tx must wait until it is mined
                const receipt = await tx.wait();

                const blockNumber = receipt.blockNumber;
                const gasUsed = receipt.gasUsed.toString();

                console.log(`Transaction confirmed in block ${blockNumber}`);
                console.log(`Gas used: ${gasUsed}`);

                if (blockNumber) {

                    // if modal is not shown/visible then
                    if ( !jQuery('#myModal').is(':visible') ) {
                        jQuery("#paymentModal").modal("show");
                    }

                    //Display message after tokens are transferred
                    const div = document.getElementById('payment-results');

                    // Change the content
                    div.innerHTML = '<span><?php esc_html_e( 'Payment Status', 'card' ); ?>: '
                                    + '<?php esc_html_e( 'Successful', 'card' ); ?></span>'
                                    + '<div class="col-px2"></div>'
                                    + `<span><?php esc_html_e( 'Transaction Hash', 'card' ); ?>: ${tx.hash}</span>`
                                    + '<div class="col-px2"></div>'
                                    + `<span><?php esc_html_e( 'Gas Used', 'card' ); ?>: ${gasUsed}</span>`;

                } else {
                    //Display message if transfer failed
                    const div = document.getElementById('payment-results');

                    // Change the content
                    div.innerHTML = '<span><?php esc_html_e( 'Payment Status', 'card' ); ?>: '
                                    + '<?php esc_html_e( 'Failed', 'card' ); ?></span>'
                                    + '<div class="col-px2"></div>'
                                    + '<span><?php esc_html_e( 'Error. Please check the console log.', 'card' ); ?></span>';
                }

            } catch (error) {
                console.log(error);

                // if modal is not shown/visible then
                if ( !jQuery('#myModal').is(':visible') ) {
                    jQuery("#paymentModal").modal("show");
                }

                //Display message if transfer failed
                const errorDiv = document.getElementById('payment-results');
                errorDiv.innerHTML = '<span><?php esc_html_e( 'Payment Status', 'card' ); ?>: '
                                + '<?php esc_html_e( 'Failed', 'card' ); ?></span>'
                                + '<div class="col-px2"></div>'
                                + '<span><?php esc_html_e( 'Error. Please check the console log.', 'card' ); ?></span>';
            }

            //Reset the payment pop-up. In case it appeared before
            jQuery('#paymentModal').on('hidden.bs.modal', function () {

                const resetDiv = document.getElementById('payment-results');
                resetDiv.innerHTML = '<div class="spinner" role="status"></div>'
                                + '<div class="text-center"><?php esc_html_e( 'Please wait...', 'card' ); ?>'
                                + '</div>';
            });

        }

    </script>

    <?php

    return $output;
}

?>