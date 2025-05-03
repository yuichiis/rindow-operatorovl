--TEST--
operation method name test
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
		return "__add(".$arg.")";
	}

	public function __sub($arg)
	{
		return "__sub(".$arg.")";
	}

	public function __mul($arg)
	{
		return "__mul(".$arg.")";
	}

	public function __div($arg)
	{
		return "__div(".$arg.")";
	}

	public function __mod($arg)
	{
		return "__mod(".$arg.")";
	}

	public function __pow($arg)
	{
		return "__pow(".$arg.")";
	}

	public function __sl($arg)
	{
		return "__sl(".$arg.")";
	}

	public function __sr($arg)
	{
		return "__sr(".$arg.")";
	}

	public function __concat($arg)
	{
		return "__concat(".$arg.")";
	}

	public function __bw_or($arg)
	{
		return "__bw_or(".$arg.")";
	}

	public function __bw_and($arg)
	{
		return "__bw_and(".$arg.")";
	}

	public function __bw_xor($arg)
	{
		return "__bw_xor(".$arg.")";
	}

	public function __bw_not()
	{
		assert(func_num_args()==0);
		return "__bw_not()";
	}

	public function __bool_xor($arg)
	{
		return "__bool_xor(".$arg.")";
	}

	public function __toString()
	{
		return "__toString()";
	}
}

$obj = new TestClass(1);

assert(($obj+2)=='__add(2)');
assert(($obj-2)=='__sub(2)');
assert(($obj*2)=='__mul(2)');
assert(($obj/2)=='__div(2)');
assert(($obj%2)=='__mod(2)');
assert(($obj**2)=='__pow(2)');
assert(($obj."2")=='__concat(2)');
assert(($obj<<2)=='__sl(2)');
assert(($obj>>2)=='__sr(2)');
assert(($obj|2)=='__bw_or(2)');
assert(($obj&2)=='__bw_and(2)');
assert(($obj^2)=='__bw_xor(2)');
assert((~$obj)=='__bw_not()');
assert(($obj xor 2)=='__bool_xor(2)');
assert((-$obj)=='__mul(-1)');
assert((+$obj)=='__mul(1)');

$tmpobj = $obj;
assert(($tmpobj++)=='__toString()');
assert($tmpobj=='__add(1)');

$tmpobj = $obj;
assert((++$tmpobj)=='__add(1)');
assert($tmpobj=='__add(1)');

$tmpobj = $obj;
assert(($tmpobj+=2)=='__add(2)');
assert($tmpobj=='__add(2)');

echo "SUCCESS";

//$a = $obj+1;  	// __add  res != op1   op1:object  op2:long

//$a = 1+$obj;  	// __add  res != op1   op1:long    op2:object
//$a = $b+$obj;  	// __add  res != op1   op1:object  op2:object
//$a = $obj-1;      // __sub  res != op1   op1:object  op2:long
//$a = 1-$obj;      // __sub  res != op1   op1:long    op2:object
//$a = $obj*2;		// __mul  res != op1   op1:object  op2:long
//$a = 2*$obj;		// __mul  res != op1   op1:object  op2:long   // CAUTION
//$a = $obj/2;		// __div  res != op1   op1:object  op2:long
//$a = 2/$obj;		// __div  res != op1   op1:long    op2:object
//$a = $obj%2;		// __mod  res != op1   op1:object  op2:long
//$a = 2%$obj;		// __mod  res != op1   op1:long    op2:object
//$a = $obj**2;		// __pow  res != op1   op1:object  op2:long
//$a = 2**$obj;		// __pow  res != op1   op1:long    op2:object
//$a = $obj<<2;		// __sl  res != op1    op1:object  op2:long
//$a = 2<<$obj;		// __sl  res != op1    op1:long    op2:object
//$a = $obj>>2;		// __sr  res != op1    op1:object  op2:long
//$a = 2>>$obj;		// __sr  res != op1    op1:long    op2:object
//$a = $obj."A";	// __concat	res != op1 op1:object  op2:string
//$a = "A".$obj;	// __concat	res != op1 op1:string  op2:object
//$a = $obj|2;		// __bw_or res != op1  op1:object  op2:long
//$a = $obj&2;		// __bw_and res != op1 op1:object  op2:long
//$a = $obj^2;		// __bw_xor res != op1 op1:object  op2:long
//$a = ~$obj;		// __bw_not res != op1 op1:object  op2:null

//$obj++;       	// __add  res == op1   op1:object  op2:long // CAUTION: The evaluation result is op1
//++$obj;       	// __add  res == op1   op1:object  op2:long
//$obj += 1;		// __add  res == op1   op1:object  op2:long
//$a = -$obj;   	// __mul  res != op1   op1:object  op2:long

//$a = ($obj > 2);		// Object of class TestClass could not be converted to int
//$a = ($obj < 2);		// Object of class TestClass could not be converted to int
//$a = ($obj == 2); 	// Object of class TestClass could not be converted to int
//$a = ($obj != 2);		// Object of class TestClass could not be converted to int
//$a = ($obj and 2);	// Not called
//$a = ($obj or 2);		// Not called
//$a = ($obj xor 2);    // __bool_xor res != op1 op1:object  op2:long
//$a = !$obj;   		// Not called $a == false
//$a = ($obj == $b);   	// Not called
//$a = ($b == $obj);   	// Not called $a == false
//$a = ($obj == $obj2); // Not called $a == true
//$a = ($obj === $obj2);// Not called $a == false
//$a = ($obj > $b);   	// Not called $a == false
//$a = ($obj2 > $obj);  // Not called $a == false
//$a = ($obj < $b);   	// Not called $a == false
//$a = ($obj2 < $obj);  // Not called $a == false
//$a = ($obj === 2);   	// Not called
//$a = ($obj and $obj2);// Not called
//$a = empty($obj);		// Not called
//unset($obj);			// Not called

?>
--EXPECT--
SUCCESS