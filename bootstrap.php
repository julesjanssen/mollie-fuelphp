<?php

/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Fuel Development Team
 * @license		MIT License
 * @copyright	2010 - 2012 Fuel Development Team
 * @link		http://fuelphp.com
 */

/**
 * FuelPHP Mollie package implementation.
 *
 * @author     Jules Janssen
 * @version    1.0
 * @package    Fuel
 * @subpackage Mollie
 * @link		http://julesj.nl
 */
Autoloader::add_core_namespace('Mollie');

Autoloader::add_classes(array(
	'Mollie\\Ideal'            		 => __DIR__.'/classes/ideal.php',
));