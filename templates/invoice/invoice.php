<?php
$pathToLogo = 'https://halalmeatexpress.nl/wp-content/plugins/halalmeat-automailer/templates/card/logo.png';


require_once( BASE_PATH . 'DbQuery.php' );
require_once( BASE_PATH . 'Helper.php' );

ob_start();
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <title>Document</title>
    </head>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .pdf {
            font-family: "Montserrat", sans-serif;
            padding: 40px 0px 162px 0px;
            width: 7.5in;
            margin: 0px auto;
            line-height: 1.5;

        }
        .neechyaja
        {
            page-break-before: always;
        }
        .pdf__header::after,
        .pdf__description::after,
        .pdf__table_header::after,
        .pdf__table_body::after,
        .pdf__price::after {
            content: "";
            display: table;
            clear: both;
        }
        .pdf__header {
            margin-bottom: 25px;
        }
        .pdf__col_left {
            float: left;
            width: 50%;
        }
        .pdf__col_right {
            float: right;
            width: 300px;
        }
        .pdf img {
            filter: brightness(0);
            margin-left: 18px;
            width: 25%;
        }
        .pdf__col p,
        p.pdf__col {
            font-size: 12px;
        }
        .pdf__heading.small {
            font-size: 13px;
        }
        .pdf__heading.big {
            margin-bottom: 10px;
            font-size: 20px;
            text-transform: uppercase;
        }
        .pdf__table {
            margin-top: 30px;
            padding-bottom: 50px;
            page-break-inside: avoid;
        }
        .pdf__table_header {
            color: white;
            background-color: black;
            padding: 7px 10px;
        }
        .pdf__table_item {
            float: left;
            width: 198px;
            margin-right: 20px;
        }
        .pdf__table_product {
            float: left;
            margin-right: 20px;
        }
        .pdf__table_amount {
            float: right;
            width: 198px;
        }
        .pdf__table .pdf__heading {
            font-weight: 600;
        }
        .pdf__table_body {
            font-size: 12px;
            page-break-inside: avoid;
            page-break-before: avoid;
        }
        .pdf__table_product span {
            font-size: 12px;
            font-weight: 700;
        }
        .pdf__table_body {
            border-bottom: 2px solid #cdcdcd;
            padding: 5px;
        }
        .pdf__price {
            border-top: 2px solid #cdcdcd;
            border-bottom: 2px solid #cdcdcd;
            width: 534px;
            margin: 0px 0 0 auto;
            padding: 8px;
        }
        .pdf__price * {
            font-size: 13px;
        }
        .pdf__price h3 {
            float: left;
        }
        .pdf__price p {
            float: right;
        }
        .pdf__footer {
            margin-top: 100px;
            border-top: 1px solid #cdcdcd;
            text-align: center;
            padding: 20px 40px 0px;
            font-size: 13px;
            page-break-inside: avoid;
        }
    </style>
    <body>
	<?php
	$i=0;
	foreach($wooOrdersObjArray as $singleOrder)
	{
		$i++

?>
        <div class="pdf <?php if($i > 1){echo "neechyaja"; } ?>">
            <div class="pdf__header">
                <img class="pdf__col pdf__col_left" src="<?=$pathToLogo;?>" alt="Logo">

                <div class="pdf__col pdf__col_right">
                    <h3 class="pdf__heading small">Halal Meat Express</h3>
                    <p>
                        Klantenservice@halalmeatexpress.nl <br>
                        Ingelandenweg 1 <br>
                        1069 WE Amsterdam <br>
                        KVK 83679316
                    </p>
                </div>
            </div>

            <h2 class="pdf__heading big">Pakbon</h2>

            <div class="pdf__description">
                <p class="pdf__col pdf__col_left">
					<?=$singleOrder->get_billing_first_name() . ' ' . $singleOrder->get_billing_last_name();?> <br>
					<?=$singleOrder->get_shipping_address_2()?> <br>
					<?=$singleOrder->get_shipping_address_1()?> <br>
					<?=$singleOrder->get_billing_phone()?>
                </p>
                <p class="pdf__col pdf__col_right">
                    Bestelnummer: <?=$singleOrder->get_billing_phone()?> <br>
                    Besteldatum: <?=$singleOrder->get_billing_phone()?> <br>
                    Verzendmethode: <?=$singleOrder->get_shipping_method()?> <br>
                </p>
            </div>

            <div class="pdf__table">
                <div class="pdf__table_header">
                    <div class="pdf__table_item pdf__heading small">Artikelnummer</div>
                    <div class="pdf__table_product pdf__heading small">Product</div>
                    <div class="pdf__table_amount pdf__heading small">Hoeveelheid</div>
                </div>
				<?php
				foreach($singleOrder->get_items() as $item_id => $item )
				{

					?>
                    <div class="pdf__table_body">
                        <div class="pdf__table_item">
							<?=$item->get_name()?> <br>
                            500 Gram
                        </div>
                        <div class="pdf__table_product">
							<?=$item->get_name()?> <br>
                            500 gram <br>
                            <span>SKU:  <?=$item->get_product_id()?></span>
                        </div>
                        <div class="pdf__table_amount">
							<?=$item->get_quantity();?>
                        </div>
                    </div>
					<?php
				}
				?>
            </div>
            <div class="pdf__price">
                <h3>Totaal aantal</h3>
                <p><?=$item->get_total()?></p>
            </div>
            <div class="pdf__footer">
                Op alle overeenkomsten tussen u en Halalkoe B.V. zijn de algemene voorwaarden van Halalkoe B.V van toepassing, zie www.halalkoe.nl/algemene-voorwaarden
            </div>
        </div>
		<?php
}
	?>
    ?>
    </body>
    </html>
<?php
$output = ob_get_contents();
ob_end_clean();
return $output;
?>