Rindow Operator Overloading PHP extension
=========================================
Provides operator overloading functionality in PHP programs.

Instead of modifying the whole PHP, overload only the operators that work on a particular object class by letting the class inherit.

By implementing the method corresponding to the operator in the object class, you have a user-defined type that can be used in the operation expression.


Requirements
============

- PHP8.0 or later
- Windows 10/Visual Studio 2019 or later
- Ubuntu 22.04 LTS or later

Note
====
Due to PHP restrictions, only the following operators are eligible.

Due to the PHP function, assignment operators such as "++" are replaced with normal binary operators and executed, and the value cannot be updated directly.

- ADD(+)            __add
- SUB(-)            __sub
- MUL(*)            __mul
- DIV(/)            __div
- MOD(%),           __mod
- POW(**),          __pow
- SL(<<),           __sl
- SR(>>),           __sr
- CONCAT(.),        __concat
- BW_OR(|),         __bw_or
- BW_AND(&),        __bw_and
- BW_XOR(^),        __bw_xor
- BW_NOT(~),        __bw_not
- BOOL_XOR(xor),    __bool_xor

How to install pre-build binaries
=================================
### Download from github

Download binary for your environment from Release page.

- https://github.com/rindow/rindow-operatorovl/releases

### Install for Linux

Install the deb file with the apt command
```shell
$ sudo apt install ./rindow-operatorovl-phpX.X_X.X.X_amd64.deb
```

### Install for Windows

- Extract the zip file
- Copy DLL file to the PHP extension directory.
- Add the "extension=rindow_operatorovl" entry to php.ini

How to build from source code on Windows
========================================

### Install Visual Studio for windows
Developing PHP extensions from php8.0 or later.

- Install Microsoft Visual Studio 2019 or later installer
- If you are building a PHP Extension for PHP8.0 through PHP8.3 with Visual Studio 2022, install the MSVC v142 - VS 2019 C++ build Tool option.

### php sdk and devel-pack binaries for windows

- If you want to build extensions for PHP 8.0, You have to use php-sdk release 2.2.0. It supports vs16.
- For PHP 8.0, Download the php-sdk from https://github.com/microsoft/php-sdk-binary-tools/releases/tag/php-sdk-2.2.0
- Extract to c:\php-sdk
- Download target dev-pack from https://windows.php.net/downloads/releases/
- Extract to /path/to/php-devel-pack-x.x.x-Win32-Vxxx-x64/

### Setup environment variables to build

Open Visual Studio Command Prompt for VS for the target PHP version(see stepbystepbuild.)

Set the Visual Studio environment variables.
```shell
C:\visual\studio\path>vcvars64
```
If you are using the "MSVC v142 - VS 2019 C++ build Tool option", run it with the option.
```shell
C:\visual\studio\path>vcvars64 -vcvars_ver=14.2
```

Set PHP-SDK execute path.
```shell
C:\tmp>PATH %PATH%;C:\php-sdk\msys2\usr\bin
```

### Build

```shell
$ cd /path/to/here
$ /path/to/php-devel-pack-x.x.x-Win32-VXXX-x64/phpize.bat
$ configure --enable-rindow_operatorovl --with-prefix=/path/to/php-installation-path
$ nmake clean
$ nmake
$ nmake test
```

### Install from built directory

- Copy the php extension binary(.dll) to the php/ext directory from here/arch/Releases_XX/php_rindow_operatorovl.dll
- Add the "extension=php_rindow_operatorovl" entry to php.ini


Sample Code
===========
```php
<?php
use Rindow\OperatorOvl\Operand;

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

class TestArray extends Operand
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
assert(strval($backup)=='[1,1]');                     // **** CAUTION ****
assert(spl_object_id($arr1)!=spl_object_id($backup)); // **** CAUTION ****

```