dnl config.m4 for rindow_operatorovl extension
AC_INIT([rindow_operatorovl], [0.1.0])
dnl PHP_INIT_BUILD_SYSTEM()
AC_CONFIG_SRCDIR([rindow_operatorovl.c])
PHP_ARG_ENABLE([rindow_operatorovl],
  [whether to enable rindow_operatorovl support],
  [--enable-rindow_operatorovl    Enable rindow_operatorovl support],
  [yes])
if test "$PHP_RINDOW_OPERATOROVL" != "no"; then
  AC_DEFINE(HAVE_RINDOW_OPERATOROVL, 1, [ Whether rindow_operatorovl support is enabled ])
  RINDOW_OPERATOROVL_SOURCES="\
    rindow_operatorovl.c \
    src/Operand.c \
  "
  PHP_NEW_EXTENSION(rindow_operatorovl, $RINDOW_OPERATOROVL_SOURCES, $ext_shared)
fi
