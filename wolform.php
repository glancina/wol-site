<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
  <head>
    <meta http-equiv = "Content-Type" content = "text/html; charset = UTF-8" />
    <title>Wake the dead</title>
    <style type="text/css">
      li {list-style-type: none; }
    </style>
  </head>
  <body>
    <!--
    -->

    <h3>Wake the dead</h3>
    <form action="wolprocess.php" method="post" id="frm_wol">
      <ul>
      <?php
      // print each entree type as line items with a radio button selection
      foreach( $entree as $key => $value ) {
        echo "<li>\n";
        printf("<input type=\"radio\" name=\"entree\" value=\"%s\" />\n", $key);
        printf("%s &mdash; \$%.2f\n", $entree[$key]['name'], $entree[$key]['price']);
        echo "</li>\n";
      }
      ?>
      </ul>
      <input type="submit" value="Order" />
    </form>
    <p>
      <a href="http://validator.w3.org/check?uri=referer">
        <img src="http://www.w3.org/Icons/valid-xhtml10"
          alt="Valid XHTML 1.0 Strict" />
      </a>
    </p>

    <p>
      <a href="http://jigsaw.w3.org/css-validator/check/referer">
	<img src="http://jigsaw.w3.org/css-validator/images/vcss"
	  alt="Valid CSS" />
      </a>
    </p>

  </body>
</html>
