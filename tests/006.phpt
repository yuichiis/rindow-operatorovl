--TEST--
operation undefined function test
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
	protected $value;

	public function __construct($value)
	{
		$this->value = $value;
	}

	public function value()
	{
		return $this->value;
	}

	public function __toString()
	{
		return strval($this->value);
	}
}

$obj = new TestClass(1);

try {
    echo "obj+1=".strval(($obj+1))."\n";
} catch(Throwable $e) {
    echo get_class($e)."\n";
    echo $e->getMessage()."\n";
}
?>
--EXPECT--
TypeError
Failed calling TestClass::__add()