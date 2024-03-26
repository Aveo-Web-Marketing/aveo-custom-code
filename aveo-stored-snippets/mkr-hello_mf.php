<?php
function mkrfunc() {
	$output = 'hello mf!';
	echo $output;
}
add_shortcode('hello_mf', 'mkrfunc');