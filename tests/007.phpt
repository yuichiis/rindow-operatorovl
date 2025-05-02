--TEST--
array sample
--SKIPIF--
<?php
if (!extension_loaded('rindow_operatorovl')) {
	echo 'skip';
}
?>
--FILE--
<?php
use Rindow\OperatorOvl\Operatable;

class MathLib
{
    public function increment($x, float $a)
    {
        if(is_array($x)) {
            return array_map(fn($v)=>$v+$a,$x);
        } else {
            return $x+$a;
        }
    }

    public function add($x, $y)
    {
        if(is_array($x)) {
            if(is_array($y)) {
                if(count($x)!=count($y)) {
                    throw new InvalidArgumentException('unmatch value size');
                }
                return array_map(fn($a,$b)=>$a+$b,$x,$y);
            } else {
                throw new InvalidArgumentException('unmatch value type');
            }
        } else {
            if(is_array($y)) {
                throw new InvalidArgumentException('unmatch value type');
            } else {
                return $x+$y;
            }
        }
    }

    public function scale(float $a, $x)
    {
        if(is_array($x)) {
            return array_map(fn($v)=>$a*$v,$x);
        } else {
            return $x+$a;
        }
    }

    public function mul($x, $y)
    {
        if(is_array($x)) {
            if(is_array($y)) {
                if(count($x)!=count($y)) {
                    throw new InvalidArgumentException('unmatch value size');
                }
                return array_map(fn($a,$b)=>$a*$b,$x,$y);
            } else {
                throw new InvalidArgumentException('unmatch value type');
            }
        } else {
            if(is_array($y)) {
                throw new InvalidArgumentException('unmatch value type');
            } else {
                return $x*$y;
            }
        }
    }
}

class TestArray extends Operatable
{
    static protected $mathlib;

    protected $math;
	protected $value;

    static public function create($value)
    {
        if(self::$mathlib==null) {
            self::$mathlib = new MathLib();
        }
        return new self(self::$mathlib,$value);
    }

	public function __construct($math,$value)
	{
        $this->math = $math;
		$this->value = $value;
	}

	public function value()
	{
		return $this->value;
	}

	public function __add($value)
	{
        if(is_numeric($value)) {
            $newvalue = $this->math->increment($this->value,$value);
        } elseif($value instanceof self) {
            $newvalue = $this->math->add($value->value(),$this->value);
        } else {
            throw new InvalidArgumentException('unknown value type');
        }
        return new self($this->math,$newvalue);
	}

	public function __mul($value)
	{
        if(is_numeric($value)) {
            $newvalue = $this->math->scale($value,$this->value);
        } elseif($value instanceof self) {
            $newvalue = $this->math->mul($value->value(),$this->value);
        } else {
            throw new InvalidArgumentException('unknown value type');
        }
        return new self($this->math,$newvalue);
	}

	public function __toString()
	{
        if(is_array($this->value)) {
		    return '['.implode(',',$this->value).']';
        } else {
            return strval($this->value);
        }
	}
}

$arr1 = TestArray::create(1);
$arr2 = TestArray::create(2);

assert(strval($arr1+$arr2)=='3');
assert(strval($arr1+1)=='2');
assert(strval($arr1*$arr2)=='2');
assert(strval($arr2*2)=='4');

$arr1 = TestArray::create([1,2]);
$arr2 = TestArray::create([2,4]);

assert(strval($arr1+$arr2)=='[3,6]');
assert(strval($arr1+1)=='[2,3]');
assert(strval($arr1*$arr2)=='[2,8]');
assert(strval($arr2*2)=='[4,8]');

$arr1 = TestArray::create([1,1]);
$arr2 = TestArray::create([2,2]);
$arr3 = TestArray::create([3,3]);

assert(strval( -$arr1*$arr2 + $arr3*2 )=='[4,4]');  // (-1*2)+(3*2)=4

$backup = $arr1;
assert(spl_object_id($arr1)==spl_object_id($backup));

assert(strval($arr1++)=='[1,1]');
assert(strval($arr1)=='[2,2]');
assert(strval($backup)=='[1,1]'); // **** CAUTION ****
assert(spl_object_id($arr1)!=spl_object_id($backup)); // **** CAUTION ****


echo "SUCCESS";
--EXPECT--
SUCCESS