<?php
function random_string($length)
{
	   $string = "";
	      $chars = "abcdefghijklmanopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	      $size = strlen($chars);
	         for ($i = 0; $i < $length; $i++) {
			        $string .= $chars[rand(0, $size - 1)];
				   }
	         return $string;
}

function random_digits($length)
{
  $string = "";
  $chars = "0123456789";
  $size = strlen($chars);

  for ($i = 0; $i < $length; $i++)
  {
    $string .= $chars[random_int(0, $size - 1)];
  }

  return $string;
}
?>
