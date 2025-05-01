cmake_minimum_required(VERSION 3.15) # PHPのビルドシステムに合わせて調整
project(rindow_operatorovl C)

# PHP開発パッケージのパス (CMake実行時に -D オプションで指定)
set(PHP_SDK_DIR "" CACHE PATH "Path to the PHP Development package (e.g., php-8.x.x-devel-vs16-x64)")

if(NOT EXISTS "${PHP_SDK_DIR}/include/main/php.h")
    message(FATAL_ERROR "PHP Development package not found or invalid path: ${PHP_SDK_DIR}")
endif()

# インクルードパスの設定
include_directories(
    "${PHP_SDK_DIR}/include"
    "${PHP_SDK_DIR}/include/main"
    "${PHP_SDK_DIR}/include/TSRM"
    "${PHP_SDK_DIR}/include/Zend"
    # 外部ライブラリのインクルードパスがあれば追加
    "${CMAKE_CURRENT_SOURCE_DIR}"
)

# ライブラリパスの設定 (VSビルドの場合 .lib ファイルのある場所)
link_directories("${PHP_SDK_DIR}/lib")

# 拡張機能のソースファイル
add_library(rindow_operatorovl SHARED # SHAREDでDLLを生成
    rindow_operatorovl.c
    src/Operand.c
    # 他のソースファイル...
)

# Windowsビルドに必要なプリプロセッサ定義を追加
target_compile_definitions(rindow_operatorovl PRIVATE
    ZEND_WIN32          # Zend Engine for Windows
    PHP_WIN32           # PHP for Windows
    WINDOWS             # Generic Windows define
    ZTS=1               # Thread Safe build (php8ts.lib をリンクするため)
                        # NTS版(php8.lib)の場合は ZTS=0 または定義しない
    _WINDOWS            # Common MSVC Windows define
    ZEND_DEBUG=0        # ★★★ これを追加 (リリースビルド想定) ★★★
    COMPILE_DL_RINDOW_OPERATOROVL=1
    # 必要に応じて他の定義を追加 (例: DEBUGビルド用など)
)
# PHPライブラリへのリンク (PHPバージョンとTS/NTSを確認)
if(EXISTS "${PHP_SDK_DIR}/lib/php8ts.lib")
    set(PHP_LIB php8ts) # Thread Safe
elseif(EXISTS "${PHP_SDK_DIR}/lib/php8.lib")
    set(PHP_LIB php8)   # Non Thread Safe
else()
    message(FATAL_ERROR "Cannot find php8ts.lib or php8.lib in ${PHP_SDK_DIR}/lib")
endif()
target_link_libraries(rindow_operatorovl PRIVATE ${PHP_LIB})

# 外部ライブラリへのリンクがあれば追加
# target_link_libraries(rindow_operatorovl PRIVATE some_external_lib)

# 出力ファイル名を php_*.dll にする
set_target_properties(rindow_operatorovl PROPERTIES PREFIX "php_")
set_target_properties(rindow_operatorovl PROPERTIES SUFFIX ".dll")

# PHP実行可能ファイルを見つける (システムパス or 環境変数 PHPRC を使う)
find_program(PHP_EXECUTABLE php)
if(NOT PHP_EXECUTABLE)
    message(FATAL_ERROR "Cannot find PHP executable 'php'. Make sure it's in the PATH.")
endif()

# run-tests.php のパス (php-src を展開した場所などを指定)
set(PHP_RUN_TESTS_SCRIPT "${PHP_SDK_DIR}/script/run-tests.php" CACHE FILEPATH "Path to run-tests.php")
if(NOT EXISTS "${PHP_RUN_TESTS_SCRIPT}")
    message(WARNING "run-tests.php not found at ${PHP_RUN_TESTS_SCRIPT}. PHPT tests will be skipped.")
    # run-tests.php が見つからない場合はテストを追加しないようにするなどの処理も可能
endif()

# .phpt ファイルがあるディレクトリ (例: プロジェクト内の tests ディレクトリ)
set(PHPT_TEST_DIR "${CMAKE_CURRENT_SOURCE_DIR}/tests")
if(NOT IS_DIRECTORY "${PHPT_TEST_DIR}")
   message(WARNING "PHPT test directory not found at ${PHPT_TEST_DIR}. PHPT tests will be skipped.")
endif()

# run-tests.php とテストディレクトリが存在する場合のみテストを追加
if(EXISTS "${PHP_RUN_TESTS_SCRIPT}" AND IS_DIRECTORY "${PHPT_TEST_DIR}")

    # ★ PHP実行可能ファイルのディレクトリを取得 ★
    get_filename_component(PHP_EXECUTABLE_DIRECTORY ${PHP_EXECUTABLE} DIRECTORY)

    # ★ 環境変数 PATH に PHP のディレクトリを追加 ★
    #    (Windowsではセミコロン区切り)
    set(ENV{PATH} "${PHP_EXECUTABLE_DIRECTORY};$ENV{PATH}")

    # ビルドされた DLL のパスを取得 (Release/Debug など構成に依存)
    # CMAKE_CFG_INTDIR は Visual Studio などのマルチ構成ジェネレータで使われる
    # $CONFIG は CTest が実行時に解決してくれる
    if(CMAKE_CFG_INTDIR)
      set(PHP_EXTENSION_PATH "${CMAKE_BINARY_DIR}/${CMAKE_CFG_INTDIR}/php_rindow_operatorovl.dll")
      set(PHP_EXTENSION_PATH_CTEST "$<TARGET_FILE_DIR:rindow_operatorovl>/php_rindow_operatorovl.dll") # CTest用のパス (より推奨)
    else()
      set(PHP_EXTENSION_PATH "${CMAKE_BINARY_DIR}/php_rindow_operatorovl.dll") # シングル構成用
      set(PHP_EXTENSION_PATH_CTEST "$<TARGET_FILE_DIR:rindow_operatorovl>/php_rindow_operatorovl.dll")
    endif()

    set(PHP_EXTENSION_PATH_CTEST "$<TARGET_FILE_DIR:rindow_operatorovl>/php_rindow_operatorovl.dll")
    # ★★★ 一時 ini ファイルを作成 ★★★
    # set(PHP_INI_CONTENT "extension=${PHP_EXTENSION_PATH_CTEST}")
    # string(REPLACE "/" "\\" PHP_INI_CONTENT_WIN ${PHP_INI_CONTENT})
    # set(PHP_INI_PATH "${CMAKE_BINARY_DIR}/php-test.ini")
    # file(WRITE ${PHP_INI_PATH} "[PHP]\n${PHP_INI_CONTENT_WIN}\n")

    # 環境変数 TEST_PHP_EXECUTABLE を設定 (run-tests.php が参照するため)
    set(ENV{TEST_PHP_EXECUTABLE} ${PHP_EXECUTABLE})

    add_test(
        NAME PhptTests # テスト名 (任意)
        # 実行するコマンド
        COMMAND ${PHP_EXECUTABLE} ${PHP_RUN_TESTS_SCRIPT}
                # オプション:
                -p ${PHP_EXECUTABLE}                     # 使用するPHPバイナリ
                #-c ${PHP_INI_PATH}       # ★ -c で ini ファイルを指定 ★
                -d extension=${PHP_EXTENSION_PATH_CTEST} # ビルドした拡張をロード
                #-q                                      # Quietモード (出力抑制)
                -n                                      # エラーがあっても中断しない
                --show-diff                             # 失敗時に差分を表示
                --offline                               # report.php を叩かない
                ${PHPT_TEST_DIR}                        # テストファイルのあるディレクトリ
        WORKING_DIRECTORY ${CMAKE_BINARY_DIR} # または ${PHPT_TEST_DIR} など適切な場所
    )

    # 失敗時に出力する情報を増やす設定 (任意)
    set_tests_properties(PhptTests PROPERTIES FAIL_REGULAR_EXPRESSION "FAIL|ERROR")

endif()


enable_testing()


# インストール設定 (任意)
# install(TARGETS rindow_operatorovl DESTINATION "ext") # PHPのextフォルダに相当する場所へ

