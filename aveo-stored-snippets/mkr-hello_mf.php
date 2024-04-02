<?php
function mkrfunc() {
	$output = 'hello mf 3!';
	echo $output;
}
add_shortcode('hello_mf','mkrfunc');