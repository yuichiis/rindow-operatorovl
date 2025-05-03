/* rindow_opoverride extension for PHP */

#ifdef HAVE_CONFIG_H
# include "config.h"
#endif

#include "php.h"
#include "ext/standard/info.h"
#include "php_rindow_opoverride.h"


/* For compatibility with older PHP versions */
#ifndef ZEND_PARSE_PARAMETERS_NONE
#define ZEND_PARSE_PARAMETERS_NONE() \
    ZEND_PARSE_PARAMETERS_START(0, 0) \
    ZEND_PARSE_PARAMETERS_END()
#endif


/* {{{ PHP_RINIT_FUNCTION
 */
PHP_RINIT_FUNCTION(rindow_opoverride)
{
#if defined(ZTS) && defined(COMPILE_DL_RINDOW_OPOVERRIDE)
    ZEND_TSRMLS_CACHE_UPDATE();
#endif

    return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION
 */
PHP_MINFO_FUNCTION(rindow_opoverride)
{
    php_info_print_table_start();
    php_info_print_table_header(2, "rindow operator overloading support", "enabled");
    php_info_print_table_row(2, "Version", PHP_RINDOW_OPOVERRIDE_VERSION);
    php_info_print_table_end();
}
/* }}} */


/* {{{ PHP_MINIT_FUNCTION
 */
PHP_MINIT_FUNCTION(rindow_opoverride)
{
    php_rindow_opoverride_operatable_init_ce(INIT_FUNC_ARGS_PASSTHRU);
    return SUCCESS;
}
/* }}} */

/* {{{ rindow_opoverride_module_entry
 */
zend_module_entry rindow_opoverride_module_entry = {
    STANDARD_MODULE_HEADER,
    "rindow_opoverride",           /* Extension name */
    NULL,                           /* zend_function_entry */
    PHP_MINIT(rindow_opoverride),  /* PHP_MINIT - Module initialization */
    NULL,                           /* PHP_MSHUTDOWN - Module shutdown */
    PHP_RINIT(rindow_opoverride),  /* PHP_RINIT - Request initialization */
    NULL,                           /* PHP_RSHUTDOWN - Request shutdown */
    PHP_MINFO(rindow_opoverride),  /* PHP_MINFO - Module info */
    PHP_RINDOW_OPOVERRIDE_VERSION, /* Version */
    STANDARD_MODULE_PROPERTIES
};
/* }}} */

#ifdef COMPILE_DL_RINDOW_OPOVERRIDE
# ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
# endif
ZEND_GET_MODULE(rindow_opoverride)
#endif
