<?php
/**
 * Copyright 2006, 2007, 2008, 2009 Eric D. Hough (http://ehough.com)
 * 
 * This file is part of TubePress (http://tubepress.org)
 * 
 * TubePress is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * TubePress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with TubePress.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Attempts to load a class file based on the class name
 *
 * @param string $className The name of the class to load
 * 
 * @return void
 */
function tubepress_classloader($className)
{
    /* already have the class or interface? bail */
    if (class_exists($className, false) || interface_exists($className, false)) {
        return;
    }
    
    /*
     * replace all underscores with the directory separator and add ".class.php"
     * e.g. "org_tubepress_package_MyClass" becomes "org/tubepress/package/MyClass.class.php"
     */
    $fileName = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.class.php';
    
    /* piece together the absolute file name */
    $currentDir = dirname(__FILE__) . "/../classes/";
    $absPath = $currentDir . $fileName;
    
    /* include the file if it exists */
    if (file_exists($absPath)) {
        include $absPath;    
    }
}

/*
 * register it as a class loader if PHP >= 5.1.2, otherwise
 * we just have to register it as *the* classloader (bad!)
 */
if (version_compare(PHP_VERSION, '5.1.2', '>=')) {
	spl_autoload_register("tubepress_classloader");
} else {
	function __autoload($className) {
		return tubepress_classloader($className);
	}
}
?>