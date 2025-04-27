dnl config.m4 for rindow_operatorovl extension

dnl ########################################
dnl Basic Extension Information
dnl ########################################
AC_INIT([rindow_operatorovl], [0.1.0], [])
AC_CONFIG_SRCDIR([rindow_operatorovl.c])
PHP_INIT_BUILD_SYSTEM()

dnl ########################################
dnl Configure Option (--enable-rindow_operatorovl)
dnl ########################################
PHP_ARG_ENABLE([rindow_operatorovl],
  [whether to enable rindow_operatorovl support],
  [--enable-rindow_operatorovl    Enable rindow_operatorovl support],
  [yes])

dnl ########################################
dnl Build Process Actions (if extension is enabled)
dnl ########################################
dnl Define a C macro so the source code knows the extension is enabled
AC_DEFINE(HAVE_RINDOW_OPERATOROVL, 1, [ Whether rindow_operatorovl support is enabled ])

PHP_NEW_EXTENSION(rindow_operatorovl,
  [rindow_operatorovl.c],
  $ext_shared)
