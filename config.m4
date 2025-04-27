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

  dnl ########################################
  dnl Dependency Checks (Uncomment and modify if needed)
  dnl ########################################
  dnl If your extension depends on external libraries, add checks here.
  dnl Example using pkg-config:
  dnl PKG_CHECK_MODULES([LIBFOO], [foo >= 1.2])
  dnl PHP_EVAL_INCLINE($LIBFOO_CFLAGS)
  dnl PHP_EVAL_LIBLINE($LIBFOO_LIBS, RINDOW_OPERATOROVL_SHARED_LIBADD)
  dnl
  dnl Example checking for a header and library manually:
  dnl PHP_CHECK_HEADER([some_lib.h], [...], AC_MSG_ERROR([header missing]))
  dnl PHP_CHECK_LIBRARY([somelib], [some_function], [...], AC_MSG_ERROR([library missing or old]))

  dnl PHP_SUBST(RINDOW_OPERATOROVL_SHARED_LIBADD) dnl <-- If using external libs

  dnl ########################################
  dnl Finalize the extension build
  dnl ########################################
  PHP_NEW_EXTENSION(rindow_operatorovl,
    [rindow_operatorovl.c],
    $ext_shared)
