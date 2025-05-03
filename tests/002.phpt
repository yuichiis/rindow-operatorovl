--TEST--
New Class with Operator Basic test
--SKIPIF--
<?php
if (!extension_loaded('rindow_opoverride')) {
	echo 'skip';
}
?>
--FILE--
<?php
use Rindow\OpOverride\Operatable;

class TestClass extends Operatable
{
	public function ping()
	{
		echo "The extension rindow_opoverride is loaded and working!";
	} 
}

$obj = new TestClass();
$obj->ping();
?>
--EXPECT--
The extension rindow_opoverride is loaded and working!
