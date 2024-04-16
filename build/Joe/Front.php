<?php

class Joe_v1_3_Front {
	function __construct() {
		//Front only
		if(is_admin()) {
			return;
		}
	}
}