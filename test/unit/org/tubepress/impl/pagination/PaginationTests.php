<?php
require dirname(__FILE__) . '/../../../../PhpUnitLoader.php';
require_once 'DiggStylePaginationServiceTest.php';

class PaginationTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite("TubePress Pagination Tests");
		$suite->addTestSuite('org_tubepress_impl_pagination_DiggStylePaginationServiceTest');
		return $suite;
	}
}

