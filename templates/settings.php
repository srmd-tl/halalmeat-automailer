<?php
require_once BASE_PATH . '/DbQuery.php';
$db = new DbQuery();
//butcher and logistics email
if ( ! empty( $_POST ) && $_POST['butcher_email'] ) {

	$db->insertEmail( $_POST['butcher_email'], $_POST['logistics_email'] );
}
///smptp configs
if ( ! empty( $_POST ) && key_exists( 'smtp_username' ,$_POST) && $_POST['smtp_username']
     && key_exists( 'smtp_password' ,$_POST) && $_POST['smtp_password']
     && key_exists( 'smtp_port' ,$_POST) && $_POST['smtp_port']
     && key_exists( 'smtp_security' ,$_POST) && $_POST['smtp_security']
     && key_exists( 'smtp_host' ,$_POST) && $_POST['smtp_host']
) {
	$db->insertSmtpConfigs($_POST['smtp_host'], $_POST['smtp_username'], $_POST['smtp_password'],  $_POST['smtp_port'],  $_POST['smtp_security']);
}
//sender info
if ( ! empty( $_POST )
     && key_exists( 'sender_email' ,$_POST) && $_POST['sender_email']
     && key_exists( 'sender_name' ,$_POST) && $_POST['sender_name']
) {

	$db->insertSenderInfo( $_POST['sender_email'], $_POST['sender_name'] );
}
//time configs
if ( ! empty( $_POST )
     && key_exists( 'preorder_time' ,$_POST) && $_POST['preorder_time']
     && key_exists( 'order_time' ,$_POST) && $_POST['order_time']
) {

	$db->insertOrderTime( $_POST['preorder_time'], $_POST['order_time'] );
}

$data = $db->getSetting();
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Setting</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    </head>
    <body>

    <div class="container">
        <h2>Email setting</h2>
        <form action="" method="POST">
            <!--Emails-->
            <div class="form-group">
                <label for="email">Butcher:</label>
                <input type="email" class="form-control" id="butcher_email" placeholder="Enter butcher email"
                       name="butcher_email" value="<?= $data ? current( $data['butcherEmail'] ) : null ?>">
            </div>
            <div class="form-group">
                <label for="pwd">Logistics:</label>
                <input type="email" class="form-control" id="logistics_email" placeholder="Enter logistics email"
                       name="logistics_email" value="<?= $data ? current( $data['logisticsEmail'] ) : null ?>">
            </div>
            <!--Sender info-->
            <div class="form-group">
                <label for="email">Sender email:</label>
                <input type="email" class="form-control" id="sender_email" placeholder="Enter sender email"
                       name="sender_email"
                       value="<?= $data && key_exists( 'sender_email', $data ) ? current( $data['sender_email'] ) : null ?>">
            </div>
            <div class="form-group">
                <label for="email">Sender name:</label>
                <input type="text" class="form-control" id="sender_name" placeholder="Enter sender name"
                       name="sender_name"
                       value="<?= $data && key_exists( 'sender_name', $data ) ? current( $data['sender_name'] ) : null ?>">
            </div>
            <!--Smtp configs -->
            <div class="form-group">
                <label for="pwd">Smtp host:</label>
                <input type="text" class="form-control" id="smtp_host" placeholder="Enter smtp host (smtp.gmail.com)"
                       name="smtp_host"
                       value="<?= $data && key_exists( 'smtp_host', $data ) ? current( $data['smtp_host'] ) : null ?>">
            </div>
            <div class="form-group">
                <label for="pwd">Smtp username:</label>
                <input type="text" class="form-control" id="smtp_username"
                       placeholder="Enter smtp username (myemail@email.com)"
                       name="smtp_username"
                       value="<?= $data && key_exists( 'smtp_username', $data ) ? current( $data['smtp_username'] ) : null ?>">
            </div>
            <div class="form-group">
                <label for="pwd">Smtp password:</label>
                <input type="password" class="form-control" id="smtp_password" placeholder="Enter smtp password"
                       name="smtp_password"
                       value="<?= $data && key_exists( 'smtp_password', $data ) ? current( $data['smtp_password'] ) : null ?>">
            </div>
            <div class="form-group">
                <label for="pwd">Smtp port:</label>
                <input type="text" class="form-control" id="smtp_port" placeholder="Enter smtp port"
                       name="smtp_port"
                       value="<?= $data && key_exists( 'smtp_port', $data ) ? current( $data['smtp_port'] ) : null ?>">
            </div>
            <div class="form-group">
                <label for="pwd">Smtp secure:</label>
                <select name="smtp_security">
                    <option value="ssl" <?= $data && key_exists( 'smtp_security', $data ) && current( $data['smtp_security'] ) == 'ssl' ? 'selected' : null ?>>
                        SSL
                    </option>
                    <option value="tls" <?= $data && key_exists( 'smtp_security', $data ) && current( $data['smtp_security'] ) == 'tls' ? 'selected' : null ?>>
                        TLS
                    </option>
                </select>
            </div>
            <!--Time setting-->
            <div class="form-group">
                <label for="pwd">Pre Order schedule:</label>
                <input type="time" class="form-control" id="pre_order_time" placeholder="Enter pre order time"
                       name="pre_order_time"
                       value="<?= $data && key_exists( 'preorder_time', $data ) ? current( $data['preorder_time'] ) : null ?>">
            </div>
            <div class="form-group">
                <label for="pwd">Order time:</label>
                <input type="time" class="form-control" id="order_time" placeholder="Enter order time"
                       name="order_time"
                       value="<?= $data && key_exists( 'order_time', $data ) ? current( $data['order_time'] ) : null ?>">
            </div>
            <button type="submit" class="btn btn-default">Submit</button>
        </form>
    </div>

    </body>
    </html>

<?php
