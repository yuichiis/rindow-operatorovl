--TEST--
Check if rindow_opoverride is loaded
--SKIPIF--
<?php
if (!extension_loaded('rindow_opoverride')) {
	echo 'skip';
}
?>
--FILE--
<?php
echo 'The extension "rindow_opoverride" is available';
?>
--EXPECT--
The extension "rindow_opoverride" is available
