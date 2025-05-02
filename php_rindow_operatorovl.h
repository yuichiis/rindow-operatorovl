/* rindow_operatorovl extension for PHP */

#ifndef PHP_RINDOW_OPERATOROVL_H
# define PHP_RINDOW_OPERATOROVL_H

extern zend_module_entry rindow_operatorovl_module_entry;
# define phpext_rindow_operatorovl_ptr &rindow_operatorovl_module_entry

# define PHP_RINDOW_OPERATOROVL_VERSION "0.1.0"

# if defined(ZTS) && defined(COMPILE_DL_RINDOW_OPERATOROVL)
ZEND_TSRMLS_CACHE_EXTERN()
# endif

/* {{{ class Rindow\OperatorOvl\Operatable */
// object structures
typedef struct {
    zend_object std;
} php_rindow_operatorovl_operatable_t;

static inline php_rindow_operatorovl_operatable_t* php_rindow_operatorovl_operatable_fetch_object(zend_object* obj)
{
	return (php_rindow_operatorovl_operatable_t*) ((char*) obj - XtOffsetOf(php_rindow_operatorovl_operatable_t, std));
}
extern void php_rindow_operatorovl_operatable_init_ce(INIT_FUNC_ARGS);
/* }}} */

#define Z_RINDOW_OPERATOROVL_OPERATABLE_OBJ_P(zv) (php_rindow_operatorovl_operatable_fetch_object(Z_OBJ_P(zv)))

#endif	/* PHP_RINDOW_OPERATOROVL_H */
