<?php 
function acc_shortcode() {
	
	echo '<div class="shortcode-wrapper">';
	
	echo '<div class="test-php">This text is echoed by and php snippet, and colored by a css snippet</div>';

	echo '<button id="test-btn">Click me</button>';

	echo '<div class="hidden-text" style="display: none;">The display of this text, is changed by a javascript snippet</div>';
	
	echo '</div>';
	
}
add_shortcode('acc_shortcode', 'acc_shortcode');