<?php
//$pathToCSS  = 'https://halalmeatexpress.nl/wp-content/plugins/halalmeat-automailer/templates/card/style.css';
$pathToLogo = 'https://halalmeatexpress.nl/wp-content/plugins/halalmeat-automailer/templates/card/logo.png';
//$pathToLogo = 'https://blog.idrsolutions.com/wp-content/uploads/2017/02/JPEG-1-300x236.png';
require_once( BASE_PATH . 'DbQuery.php' );
require_once( BASE_PATH . 'Helper.php' );

ob_start();
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <!-- META -->
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap"
              rel="stylesheet">

        <title>card</title>
    </head>
    <style>
        * {
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
        }

        @page {
            size: 89mm 36mm portrait !important;
            margin-left: 20px !important;
        }

        *,
        .card {
            font-family: "Montserrat", sans-serif;
        }

        .neechyaja {
            page-break-before: always;
        }

        .card {
            margin: 5px !important;
            border: 1px solid #000 !important;
            padding: 8px !important;
            /* padding: 28px 22px 28px 22px !important;
			margin: 25px auto !important; */

        }

        .card::after,
        .card::before {
            display: table;
            content: " ";
        }

        .card::after {
            clear: both;
        }

        .card h2 {
            font-weight: 700 !important;
            line-height: 1;
        }

        .card span {
            display: block !important;
            font-weight: 500 !important;
            font-size: 15px !important;
            line-height: 1;
        }

        .card .logo img {
            padding-top: 10px !important;
        }

        .text-box {
            float: left;
            width: 70%;
        }

        .logo {
            float: left;
            width: 30%;
        }

        .logo img {
            width: 100%;
        }
    </style>

    <body>
    <header>
		<?php
		$counter = 0;
		foreach($orders as $singleOrder)
		{
			$counter++;
			?>
            <div class="card <?php if( $counter != 1){echo "neechyaja";}?>">

                <div class="text-box">
                    <h2><?=$singleOrder['billing_fullname'];?></h2>
                    <span><?=$singleOrder['billing_address_1'];?></span>
                    <span><?=$singleOrder['billing_postcode'];?></span>
                    <span><?=$singleOrder['billing_city'];?></span>

                </div>

                <div class="logo">
                    <img src="<?=$pathToLogo;?>" />

                </div>
            </div>
			<?php
		}
		?>

    </header>
    </body>

    </html>
<?php
$output = ob_get_contents();
ob_end_clean();
return $output;
?>