<?php

class Joe_Front {
	function __construct() {
		//Front only
		if (is_admin()) {
			return;
		}
	}
}