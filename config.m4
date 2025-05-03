dnl config.m4 for extension rindow_opoverride

PHP_ARG_ENABLE([rindow_opoverride],
  [whether to enable rindow_opoverride support],
  [AS_HELP_STRING([--enable-rindow_opoverride],
    [Enable rindow_opoverride support])],
  [yes])

if test "$PHP_RINDOW_OPOVERRIDE" != "no"; then
  AC_DEFINE(HAVE_RINDOW_OPOVERRIDE, 1, [ Have rindow_opoverride support ])

  RINDOW_OPOVERRIDE_SOURCES="\
    rindow_opoverride.c \
    src/Operatable.c \
  "
  PHP_NEW_EXTENSION(rindow_opoverride, $RINDOW_OPOVERRIDE_SOURCES, $ext_shared)
fi