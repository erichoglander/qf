<?php
namespace FontAwesome {

	function icon($name, $class = "") {
		return '<span class="fa fa-'.$name.($class ? ' '.$class : '').'"></span>';
	}

}