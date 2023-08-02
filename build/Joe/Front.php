<?php

class Joe_v1_2_Front {
	function __construct() {
		//Front only
		if(is_admin()) {
			return;
		}
	}
}