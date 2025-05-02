--TEST--
operation Basic test
--SKIPIF--
<?php
if (!extension_loaded('rindow_operatorovl')) {
	echo 'skip';
}
?>
--FILE--
<?php
use Rindow\OperatorOvl\Operatable;

class TestClass extends Operatable
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

	public function __add($arg)
	{
		if($arg instanceof TestClass) {
			$value = $arg->value();
		} else {
			$value = $arg;
		}
		return new self($this->value+$value);
	}

	public function __toString()
	{
		return strval($this->value);
	}
}

$obj = new TestClass(1);
$obj2 = new TestClass(3);
$b = new stdClass();

assert(strval($obj+1)=="2");
assert(strval($obj+$obj2)=="4");

echo "SUCCESS";
?>
--EXPECT--
SUCCESS