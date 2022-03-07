--TEST--
New Class with Operator Basic test
--SKIPIF--
<?php
if (!extension_loaded('rindow_operatorovl')) {
	echo 'skip';
}
?>
--FILE--
<?php
use Rindow\OperatorOvl\Operand;

class TestClass extends Operand
{
	public function ping()
	{
		echo "The extension rindow_operatorovl is loaded and working!";
	} 
}

$obj = new TestClass();
$obj->ping();
?>
--EXPECT--
The extension rindow_operatorovl is loaded and working!
