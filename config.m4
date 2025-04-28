dnl config.m4 for extension rindow_operatorovl

PHP_ARG_ENABLE([rindow_operatorovl],
  [whether to enable rindow_operatorovl support],
  [AS_HELP_STRING([--enable-rindow_operatorovl],
    [Enable rindow_operatorovl support])],
  [yes])

if test "$PHP_RINDOW_OPERATOROVL" != "no"; then
  AC_DEFINE(HAVE_RINDOW_OPERATOROVL, 1, [ Have rindow_operatorovl support ])

  RINDOW_OPERATOROVL_SOURCES="\
    rindow_operatorovl.c \
    src/Operand.c \
  "
  PHP_NEW_EXTENSION(rindow_operatorovl, $RINDOW_OPERATOROVL_SOURCES, $ext_shared)
fi