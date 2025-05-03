/* rindow_opoverride extension for PHP */

#ifndef PHP_RINDOW_OPOVERRIDE_H
# define PHP_RINDOW_OPOVERRIDE_H

extern zend_module_entry rindow_opoverride_module_entry;
# define phpext_rindow_opoverride_ptr &rindow_opoverride_module_entry

# define PHP_RINDOW_OPOVERRIDE_VERSION "0.1.0"

# if defined(ZTS) && defined(COMPILE_DL_RINDOW_OPOVERRIDE)
ZEND_TSRMLS_CACHE_EXTERN()
# endif

/* {{{ class Rindow\OpOverride\Operatable */
// object structures
typedef struct {
    zend_object std;
} php_rindow_opoverride_operatable_t;

static inline php_rindow_opoverride_operatable_t* php_rindow_opoverride_operatable_fetch_object(zend_object* obj)
{
	return (php_rindow_opoverride_operatable_t*) ((char*) obj - XtOffsetOf(php_rindow_opoverride_operatable_t, std));
}
extern void php_rindow_opoverride_operatable_init_ce(INIT_FUNC_ARGS);
/* }}} */

#define Z_RINDOW_OPOVERRIDE_OPERATABLE_OBJ_P(zv) (php_rindow_opoverride_operatable_fetch_object(Z_OBJ_P(zv)))

#endif	/* PHP_RINDOW_OPOVERRIDE_H */
