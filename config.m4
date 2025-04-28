dnl config.m4 for extension rindow_operatorovl

dnl Comments in this file start with the string 'dnl'.
dnl Remove where necessary.

dnl If your extension references something external, use 'with':

PHP_ARG_WITH([rindow_operatorovl],
  [for rindow_operatorovl support],
  [AS_HELP_STRING([--with-rindow_operatorovl],
    [Include rindow_operatorovl support])])

dnl Otherwise use 'enable':

PHP_ARG_ENABLE([rindow_operatorovl],
  [whether to enable rindow_operatorovl support],
  [AS_HELP_STRING([--enable-rindow_operatorovl],
    [Enable rindow_operatorovl support])],
  [no])

if test "$PHP_RINDOW_OPERATOROVL" != "no"; then
  dnl Write more examples of tests here...

  dnl PHP_SUBST(EXTSKEL_SHARED_LIBADD)

  dnl In case of no dependencies
  AC_DEFINE(HAVE_RINDOW_OPERATOROVL, 1, [ Have rindow_operatorovl support ])

  RINDOW_OPERATOROVL_SOURCES="\
    rindow_operatorovl.c \
    src/Operand.c \
  "
  PHP_NEW_EXTENSION(rindow_operatorovl, $RINDOW_OPERATOROVL_SOURCES, $ext_shared)
fi