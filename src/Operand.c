#ifdef HAVE_CONFIG_H
# include "config.h"
#endif

#include "php.h"
#include "ext/standard/info.h"
#include "php_rindow_operatorovl.h"

/* ============== class "Rindow\OperatorOvl\Operand" ================ */

#define ALL_OPERATORS(PROC) \
  PROC(ADD,                 __add) \
  PROC(SUB,                 __sub) \
  PROC(MUL,                 __mul) \
  PROC(DIV,                 __div) \
  PROC(MOD,                 __mod) \
  PROC(POW,                 __pow) \
  PROC(SL,                  __sl) \
  PROC(SR,                  __sr) \
  PROC(CONCAT,              __concat) \
  PROC(BW_OR,               __bw_or) \
  PROC(BW_AND,              __bw_and) \
  PROC(BW_XOR,              __bw_xor) \
  PROC(BW_NOT,              __bw_not) \
  PROC(BOOL_XOR,            __bool_xor)


//  PROC(IS_IDENTICAL,        __is_identical)
//  PROC(IS_NOT_IDENTICAL,    __is_not_identical)
//  PROC(IS_EQUAL,            __is_equal)
//  PROC(IS_NOT_EQUAL,        __is_not_equal)
//  PROC(IS_SMALLER,          __is_smaller)
//  PROC(IS_SMALLER_OR_EQUAL, __is_smaller_or_equal)
//  PROC(SPACESHIP,           __cmp)

//  PROC(PRE_INC,             __pre_inc)
//  PROC(POST_INC,            __post_inc)
//  PROC(PRE_DEC,             __pre_dec)
//  PROC(POST_DEC,            __post_dec)

//  PROC(ASSIGN,              __assign)


/* {{{ get operator method name */
#define PROC(op, meth) static zend_string *method_name_##meth;
ALL_OPERATORS(PROC)
#undef PROC

static zend_string* operator_method_name(zend_uchar opcode)
{
    switch(opcode) {
#define PROC(op, meth) case ZEND_##op: return method_name_##meth;
ALL_OPERATORS(PROC)
#undef PROC
    default:
		return NULL;
    }
}
/* }}} */

/* {{{ operator_get_method */
static zend_bool operator_get_method(
    zend_string *method, 
    zval *obj,
    zend_fcall_info *fci,
    zend_fcall_info_cache *fcc)
{
    zend_bool is_callable;
#if !(PHP_MAJOR_VERSION == 8 && PHP_MINOR_VERSION >= 2)
    // Use the SILENT flag only for PHP 8.1 and below
    const uint32_t callable_flags = IS_CALLABLE_CHECK_SILENT;
#else
    // No flags (0) for PHP 8.2 and later
    const uint32_t callable_flags = 0;
#endif

    memset(fci, 0, sizeof(zend_fcall_info));
    fci->size = sizeof(zend_fcall_info);
    fci->object = Z_OBJ_P(obj);
    ZVAL_STR(&(fci->function_name), method);

    is_callable = zend_is_callable_ex(
            &(fci->function_name),
            fci->object,
            callable_flags,
            NULL, fcc, NULL);

    // Fail if zend_is_callable_ex returns false (method doesn't exist, is private, etc.)
    if (!is_callable) {
        return FAILURE;
    }

    /* Disallow dispatch via __call */
    if (fcc->function_handler == Z_OBJCE_P(obj)->__call) {
        return FAILURE;
    }
    if (fcc->function_handler->type == ZEND_USER_FUNCTION) {
        zend_op_array *oparray = (zend_op_array*)(fcc->function_handler);
        if (oparray->fn_flags & ZEND_ACC_CALL_VIA_TRAMPOLINE) {
            return FAILURE;
        }
    }

    return SUCCESS;
}
/* }}} */

static zend_object_handlers instance_object_handlers;

/* {{{ destractor */
static void operator_free_object(zend_object* object)
{
    php_rindow_operatorovl_operand_t* obj = php_rindow_operatorovl_operand_fetch_object(object);
    zend_object_std_dtor(&obj->std);
}
/* }}} */

/* {{{ constructor */
static zend_object* operator_create_object(zend_class_entry* class_type) /* {{{ */
{
    php_rindow_operatorovl_operand_t* intern = NULL;

    intern = (php_rindow_operatorovl_operand_t*)ecalloc(1, sizeof(php_rindow_operatorovl_operand_t) + zend_object_properties_size(class_type));

    zend_object_std_init(&intern->std, class_type);
    object_properties_init(&intern->std, class_type);

    intern->std.handlers = &instance_object_handlers;

    return &intern->std;
}
/* }}} */

/* {{{ operator handler */
static int operator_do_operation(zend_uchar opcode, zval *result, zval *op1, zval *op2) 
{
    zval op1_copy;
    zend_fcall_info fci;
    zend_fcall_info_cache fcc;
    zend_string *method = operator_method_name(opcode);
    if(method==NULL) {
        zval *tmpop = op1;
        if(Z_TYPE_P(op1)!=IS_OBJECT) {
            tmpop = op2;
        }
        php_error(E_ERROR, "unkown operator code %d on %s", (int)opcode, ZSTR_VAL(Z_OBJCE_P(tmpop)->name));
        return FAILURE;
    }

    if ((Z_TYPE_P(op1) != IS_OBJECT) ||
        operator_get_method(method, op1, &fci, &fcc)==FAILURE) {
        /* Not an overloaded call */
        if(Z_TYPE_P(op1) == IS_OBJECT) {
            zend_type_error("Undefined method %s::%s()", ZSTR_VAL(Z_OBJCE_P(op1)->name), ZSTR_VAL(method));
        }
        return FAILURE;
    }

    fci.retval = result;
    fci.params = op2;
    fci.param_count = op2 ? 1 : 0;
    if (FAILURE == zend_call_function(&fci, &fcc)) {
        zend_type_error("Failed calling %s::%s()", ZSTR_VAL(Z_OBJCE_P(op1)->name), Z_STRVAL(fci.function_name));
        if(result!=op1) {
            ZVAL_NULL(fci.retval);
        }
        return FAILURE;
    }

    return SUCCESS;
}
/* }}} */

/* {{{ Rindow\OperatorOvl\Operand function entries */
static const zend_function_entry object_method_entries[] = {
    /* clang-format off */
    PHP_FE_END
    /* clang-format on */
};
/* }}} */

/* Class Rindow\OperatorOvl\Operand {{{ */
static zend_class_entry* object_class_entry;

void php_rindow_operatorovl_operand_init_ce(INIT_FUNC_ARGS)
{
    zend_class_entry ce;

#define PROC(op, meth) \
    method_name_##meth = zend_string_init(#meth, strlen(#meth), 1);
ALL_OPERATORS(PROC)
#undef PROC

    INIT_NS_CLASS_ENTRY(ce, "Rindow\\OperatorOvl", "Operand", object_method_entries);
    object_class_entry = zend_register_internal_class(&ce);
    object_class_entry->create_object = operator_create_object;

    memcpy(&instance_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
    instance_object_handlers.offset    = 0;
    instance_object_handlers.free_obj  = operator_free_object;
    instance_object_handlers.clone_obj = NULL;
    instance_object_handlers.do_operation = operator_do_operation;

}
/* }}} */
/* ====================== end class "Operator" ====================== */
