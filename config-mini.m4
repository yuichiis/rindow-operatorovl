dnl config.m4 for rindow_operatorovl extension (最小構成テスト用)
AC_INIT([rindow_operatorovl], [0.1.0])
PHP_INIT_BUILD_SYSTEM()
PHP_NEW_EXTENSION(rindow_operatorovl, [rindow_operatorovl.c], $ext_shared)
