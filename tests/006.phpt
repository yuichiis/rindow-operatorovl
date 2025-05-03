--TEST--
operation undefined function test (PHP 8.2+)
--SKIPIF--
<?php
if (!extension_loaded('rindow_opoverride')) {
	echo 'skip';
}
if (version_compare(PHP_VERSION, '8.2.0', '<')) echo 'skip Requires PHP 8.2+';
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
Error
Invalid callback TestClass::__add, class TestClass does not have a method "__add"