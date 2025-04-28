--TEST--
Check if rindow_operatorovl is loaded
--SKIPIF--
<?php
if (!extension_loaded('rindow_operatorovl')) {
	echo 'skip';
}
?>
--FILE--
<?php
echo 'The extension "rindow_operatorovl" is available';
echo phpversion('rindow_operatorovl');
?>
--EXPECT--
The extension "rindow_operatorovl" is available
