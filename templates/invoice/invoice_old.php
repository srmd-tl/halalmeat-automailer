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
  <title>Document</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>
  <div class="container-big">
      <?php
      foreach($wooOrdersObjArray as $singleOrder)
      {

      ?>
    <div class="container">
      <div class="header">
        <div class="header-logo">
          <img src="<?=$pathToLogo;?>" alt="logo">
        </div>
        <div class="header-content">
          <h3>Halal Meat Express</h3>
          <p>Ingelandenweg 1
          1069 WE Amsterdam
          KVK 83679316<p>
        </div>
      </div>

      <div class="main">
        <h2 class="slip-heading">PAKBON</h2>
        <div class="main-content">
          <div class="main-left">
              <?=$singleOrder->get_billing_first_name() . ' ' . $singleOrder->get_billing_last_name();?><br/>
              <?=$singleOrder->get_shipping_address_2()?><br/>
              <?=$singleOrder->get_shipping_address_1()?><br/>
              <?=$singleOrder->get_billing_phone()?><br/>
          </div>
          <div class="main-right">Bestelnummer: <?=$singleOrder->get_billing_phone()?><br/>

            Besteldatum: <?=$singleOrder->get_billing_phone()?><br/>
            Verzendmethode:<?=$singleOrder->get_shipping_method()?><br/>

          </div>
        </div>
      </div>

      <div class="table-main">
        <div class="table">
          <div class="table-heading">
            <h3 class="item-number">Artikelnummer</h3>
            <h3 class="item-name">Product</h3>
            <h3 class="quantity">Hoeveelheid</h3>
          </div>
          <div class="table-content">
              <?php
              foreach($singleOrder->get_items() as $item_id => $item )
              {

              ?>
            <div class="items">
              <p class="item-number"><?=$item->get_name()?></p>
              <p class="item-name">
                  <?=$item->get_name()?>
                  <span>SKU: <?=$item->get_product_id()?></span></p>
              <p class="quantity"><?=$item->get_quantity();?></p>
            </div>
                  <?php
              }
                  ?>
          </div>
        </div>
      </div>

      <div class="total-price">
        <h3>Totaal aantal</h3> <p> <?=$item->get_total()?></p>
      </div>
    </div>
          <?php
      }
          ?>
  </div>
</body>
<footer>
  <div class="container">
    <div class="footer-content">
    Op alle overeenkomsten tussen u en Halalkoe B.V. zijn de algemene voorwaarden van Halalkoe B.V van toepassing, zie www.halalkoe.nl/algemene-voorwaarden
    </div>
  </div>
</footer>
</html>
<?php
$output = ob_get_contents();
ob_end_clean();
return $output;
?>