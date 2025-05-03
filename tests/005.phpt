--TEST--
operation right side test
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

try {
    echo "1+obj=".strval((1+$obj))."\n";
} catch(Throwable $e) {
    echo get_class($e)."\n";
    echo $e->getMessage()."\n";
}
?>
--EXPECT--
TypeError
Unsupported operand types: int + TestClass