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
?>
--EXPECT--
The extension "rindow_operatorovl" is available
