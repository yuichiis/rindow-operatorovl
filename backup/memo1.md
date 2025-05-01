なるほど！ PHP ソースコード全体ではなく、**PHP Development Package (php-devel-pack)** を使うというのは、拡張機能のビルドにおいては非常に賢明で効率的なアプローチです！ファイルサイズも小さく、必要なヘッダーやライブラリが含まれています。

ご提示の `php-devel-pack-8.3.20-nts-Win32-vs16-x64.zip` のようなファイルを使う前提で、ワークフローの Windows ビルド環境セットアップ部分を書き直しましょう。

このアプローチでは、PHP SDK は不要で、必要なのは **Development Package** と **Visual Studio ビルド環境** のみになります。`phpize` や `configure` の代わりに、直接 `nmake` を使う形になります (config.w32 もこの場合は使いません)。

**修正後のワークフロー:**

```yaml
name: Build PHP Extension and Package

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]
  release:
    types: [ created ]

jobs:
  build:
    runs-on: ${{ matrix.os }}
    strategy:
      fail-fast: false
      matrix:
        os: [ubuntu-latest, windows-latest]
        php: ['8.1', '8.2', '8.3']
        # Windows ビルド用の設定 (NTS/TS, Arch)
        # 'php_arch' はダウンロード URL に使用
        # 'vs_ver' は VS 環境設定とダウンロード URL に使用
        # 'thread_safety' は成果物名に使用
        include:
          - os: windows-latest
            arch: x64 # または x86
            php_arch: x64
            vs_ver: vs16 # PHP 8.1-8.3 は vs16
            thread_safety: nts # または ts
          # 必要なら他の組み合わせ (例: TS, x86) を追加
          # - os: windows-latest
          #   arch: x86
          #   php_arch: win32 # php-devel-pack URL では x86 ではなく Win32
          #   vs_ver: vs16
          #   thread_safety: nts

    # ジョブ名をより詳細に
    name: Build on ${{ matrix.os }} (${{ matrix.arch }}/${{ matrix.thread_safety }}) for PHP ${{ matrix.php }}

    steps:
    # 1. Checkout code
    - name: Checkout code
      uses: actions/checkout@v4

    # 2. Setup PHP runtime (Windowsでは不要かもしれないが、バージョン取得用に残す)
    - name: Setup PHP Runtime
      uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ matrix.php }}
        extensions: none
      env:
        COMPOSER_TOKEN: ${{ secrets.GITHUB_TOKEN }}

    # --- Linux Steps ---
    - name: Install build and packaging dependencies (Ubuntu)
      if: runner.os == 'Linux'
      # ... (省略: 以前と同じ) ...
    - name: Get Extension version from header (Linux)
      if: runner.os == 'Linux'
      id: ext_version_linux
      # ... (省略: 以前と同じ sed コマンド) ...
      shell: bash
    - name: Build extension (Linux)
      if: runner.os == 'Linux'
      # ... (省略: phpize, configure, make) ...
    - name: Run tests (Linux)
      if: runner.os == 'Linux'
      # ... (省略: make test) ...
    - name: Package and Upload DEB (Linux)
      if: runner.os == 'Linux'
      # ... (省略: packaging.sh, upload artifact, upload release asset) ...

    # ★★★ Windows Build using php-devel-pack Start ★★★
    # 3. Setup MSVC build environment (Windows)
    - name: Setup MSVC build environment (Windows)
      if: runner.os == 'Windows'
      uses: ilammy/msvc-dev-cmd@v1
      with:
        arch: ${{ matrix.arch }} # マトリックスからアーキテクチャを指定 (x64 or x86)
        # toolset: '14.29' # VS2019 のバージョンを細かく指定する場合 (通常不要)

    # 4. Download and Setup PHP Development Pack (Windows)
    - name: Download and Setup PHP Development Pack (Windows)
      if: runner.os == 'Windows'
      run: |
        # Get full PHP version (e.g., 8.1.27)
        try {
          $phpFullVersion = php -r "echo PHP_VERSION;"
        } catch {
          Write-Warning "Could not get PHP_VERSION automatically. Check available devel packs."
          # php-devel-pack は特定のバージョンが必要
          # ここでエラーにするか、固定バージョンを使うか要検討
          Write-Error "Cannot proceed without knowing the exact PHP patch version for devel pack."
          exit 1
        }

        $arch = "${{ matrix.php_arch }}" # x64 or Win32 for URL
        $ts = "${{ matrix.thread_safety }}" # nts or ts
        $vs = "${{ matrix.vs_ver }}"     # vs16 for PHP 8.1-8.3

        $develPackFileName = "php-devel-pack-$phpFullVersion-$ts-Win32-$vs-$arch.zip" # Windows は常に Win32 表記？ 確認要
        # php-devel-pack-8.3.20-nts-Win32-vs16-x64.zip のパターンに合わせる
        # $develPackFileName = "php-devel-pack-$phpFullVersion-$ts-Win32-$vs-$arch.zip"
        # ↑ このWin32部分は固定かもしれないので、実際のファイル名を確認
        # 実際のファイル名: php-devel-pack-8.3.6-nts-Win32-vs16-x64.zip
        # => Win32 は固定で、アーキテクチャは最後の x64/x86 で指定する
        $develPackFileName = "php-devel-pack-$phpFullVersion-$ts-Win32-$vs-${{ matrix.arch }}.zip"

        $develPackUrl = "https://windows.php.net/downloads/releases/$develPackFileName"
        $develPackDir = "C:\php-devel-pack"

        echo "PHP Full Version: $phpFullVersion"
        echo "Devel Pack URL: $develPackUrl"
        echo "Devel Pack Dir: $develPackDir"

        # Create directory
        New-Item -ItemType Directory -Force -Path $develPackDir

        # Download and extract Devel Pack
        echo "Downloading PHP Development Pack..."
        try {
          curl.exe -L -f -o php-devel-pack.zip $develPackUrl
        } catch {
          Write-Error "Failed to download PHP devel pack from $develPackUrl. Check PHP version/arch/ts/vs combination."
          # List available releases for debugging
          # curl.exe -L https://windows.php.net/downloads/releases/ | Select-String -Pattern "php-devel-pack-.*.zip"
          exit 1
        }
        Expand-Archive -Path php-devel-pack.zip -DestinationPath $develPackDir -Force
        Remove-Item php-devel-pack.zip

        # Copy extension source into a subdirectory ( mimicking ext structure is helpful for includes)
        $extSrcPath = $env:GITHUB_WORKSPACE
        $extBuildDir = Join-Path $develPackDir "ext\rindow_operatorovl" # ext/サブディレクトリにコピー
        echo "Copying extension source to $extBuildDir"
        New-Item -ItemType Directory -Force -Path (Split-Path $extBuildDir -Parent)
        Copy-Item -Path "$extSrcPath\*" -Destination $extBuildDir -Recurse -Force

        # Add paths as environment variables for the job
        echo "PHP_DEVEL_PATH=$develPackDir" >> $env:GITHUB_ENV
        echo "PHP_EXT_BUILD_PATH=$extBuildDir" >> $env:GITHUB_ENV
      shell: pwsh

    # 5. Get Extension version from header (Windows)
    - name: Get Extension version from header (Windows)
      if: runner.os == 'Windows'
      id: ext_version_windows
      run: |
        # Note: This step now runs inside the PHP_EXT_BUILD_PATH context if build is done there
        # If running from workspace root: sed -n '...' php_rindow_operatorovl.h
        # If running from $PHP_EXT_BUILD_PATH: sed -n '...' php_rindow_operatorovl.h
        cd $env:PHP_EXT_BUILD_PATH # Move to the extension directory for context
        VERSION=$(sed -n 's/^ *# *define PHP_RINDOW_OPERATOROVL_VERSION *"\([^"]*\)".*/\1/p' php_rindow_operatorovl.h)
        if [[ -z "$VERSION" ]]; then
          echo "Error: Could not extract version from php_rindow_operatorovl.h in $env:PHP_EXT_BUILD_PATH"
          exit 1
        fi
        echo "version=${VERSION}" >> $env:GITHUB_OUTPUT
        echo "Extracted version: ${VERSION}"
      shell: bash # Use bash for sed

    # 6. Build extension (Windows using nmake with php-devel-pack)
    - name: Build extension (Windows)
      if: runner.os == 'Windows'
      run: |
        REM Navigate to the extension source directory within the devel pack structure
        cd "%PHP_EXT_BUILD_PATH%"

        REM nmake requires a Makefile. Kphpize or manual creation needed?
        REM php-devel-pack doesn't include phpize. We need a Makefile.
        REM Option 1: Manually create a simple Makefile.nmake
        REM Option 2: Try to adapt Linux Makefile generation? Unlikely to work directly.
        REM Option 3: Use CMake if project supports it (recommended for cross-platform C).

        REM Assuming a manual Makefile.nmake exists for php-devel-pack build
        REM If Makefile.nmake doesn't exist, this will fail.
        REM We need to provide a way to generate or include a Windows-specific Makefile.

        REM Example assuming Makefile.nmake exists:
        REM nmake /F Makefile.nmake

        echo "ERROR: Building with php-devel-pack requires a specific Makefile (e.g., Makefile.nmake)."
        echo "phpize/configure are not available in php-devel-pack."
        echo "Please provide a suitable Makefile for nmake."
        exit 1 # Fail the build until a Makefile is provided

        REM --- If a Makefile.nmake is provided, the below might work ---
        REM nmake /F Makefile.nmake clean
        REM nmake /F Makefile.nmake
      shell: cmd

    # 7. Run tests (Windows)
    # ... (Test execution would also depend on the build method and Makefile) ...

    # 8. Determine DLL Artifact Info (Windows)
    - name: Determine DLL Artifact Info (Windows)
      if: runner.os == 'Windows'
      id: dll_artifact_info
      run: |
        # Adjust search path based on where nmake outputs the DLL
        # Assuming nmake outputs to a standard location relative to PHP_EXT_BUILD_PATH
        # e.g., x64/Release_NTS/php_rindow_operatorovl.dll within PHP_EXT_BUILD_PATH
        $extBuildPath = $env:PHP_EXT_BUILD_PATH
        $arch = "${{ matrix.arch }}"
        $tsName = "${{ matrix.thread_safety.ToUpper() }}" # NTS or TS
        # Common output dir pattern (adjust if needed)
        $buildOutputDir = Join-Path $extBuildPath "$arch\Release_$tsName"
        $dllPath = Join-Path $buildOutputDir "php_rindow_operatorovl.dll"

        if (-not (Test-Path $dllPath)) {
           echo "Warning: DLL not found at expected path: $dllPath"
           echo "Searching entire build directory ($extBuildPath)..."
           $foundDll = (Get-ChildItem -Path $extBuildPath -Recurse -Filter php_rindow_operatorovl.dll -ErrorAction SilentlyContinue | Select-Object -First 1).FullName
           if ($foundDll) {
              echo "Found DLL at: $foundDll"
              $dllPath = $foundDll
           } else {
             echo "Error: DLL search failed in $extBuildPath after build."
             exit 1
           }
        }

        # Artifact name
        $phpVersion = "${{ matrix.php }}"
        $extVersion = "${{ steps.ext_version_windows.outputs.version }}"
        $dllName = "php_rindow_operatorovl-$extVersion-php$phpVersion-windows-$arch-$($ts.ToLower()).dll"
        echo "DLL path: $dllPath"
        echo "Artifact name: $dllName"
        echo "path=$dllPath" >> $env:GITHUB_OUTPUT
        echo "name=$dllName" >> $env:GITHUB_OUTPUT
      shell: pwsh

    # 9. Upload DLL artifact (Windows)
    # ... (省略: 以前と同じ) ...

    # 10. Upload Release Asset (DLL) (Windows)
    # ... (省略: 以前と同じ) ...

    # ★★★ End Windows Build ★★★
```

**重要な変更点と課題:**

1.  **Windows マトリックス:** `include` を使って、OS、アーキテクチャ (`arch`, `php_arch`)、Visual Studio バージョン (`vs_ver`)、スレッドセーフティ (`thread_safety`) を指定するようにしました。これにより、ダウンロード URL やビルド設定を正確に制御できます。
2.  **SDK セットアップ → Devel Pack セットアップ:** PHP SDK と PHP ソース全体のダウンロード/セットアップ処理を、PHP Development Pack のダウンロード/セットアップ処理に置き換えました。
    *   **完全な PHP バージョン:** `php-devel-pack` はパッチバージョンまで正確に一致する必要があるため、`php -r "echo PHP_VERSION;"` で取得する部分が重要になります。これが失敗する場合、ワークフローで対象とする PHP の**完全なバージョン**を特定する方法（例: マトリックスで指定）が必要です。
    *   **ファイル名のパターン:** `php-devel-pack` のファイル名規則に合わせて URL を構築しています（`Win32` が固定でアーキテクチャが末尾に来るなど）。
3.  **ビルドステップ (課題):**
    *   **`php-devel-pack` には `phpize.bat` や `configure.bat` が含まれていません。** ビルドには `nmake` を直接使う必要があります。
    *   **`nmake` は `Makefile` を必要とします。** Linux 用に `phpize` が生成する `Makefile` はそのままでは使えません。
    *   **解決策:** Windows (`nmake`) 用の `Makefile` (慣例的に `Makefile.nmake` という名前) を**手動で作成し、リポジトリに含める**必要があります。この `Makefile.nmake` は、`php-devel-pack` のヘッダー/ライブラリを参照し、適切なコンパイラフラグ (config.w32 で定義していたようなもの) を設定してソースファイルをコンパイルし、DLL をリンクするように記述します。これは `config.w32` の内容を `nmake` 形式に書き直す作業に近いです。
    *   **代替案:** クロスプラットフォームビルドシステム **CMake** を導入する。CMake であれば、Linux (`Makefile`) と Windows (`Visual Studio Solution` または `NMake Makefiles`) の両方のビルドファイルを生成でき、プラットフォーム間の差異を吸収できます。これはよりモダンで推奨される方法ですが、`CMakeLists.txt` の学習と作成が必要です。
4.  **DLL パス:** `nmake` が DLL をどこに出力するかに合わせて、`Determine DLL Artifact Info` ステップの検索パスを調整しました。

**次のアクション:**

1.  **PHP 完全バージョンの特定:** ワークフローが対象とする PHP 8.1, 8.2, 8.3 の**具体的なパッチバージョン** (例: 8.1.27, 8.2.20, 8.3.8 など) を決め、それに対応する `php-devel-pack` が存在するか確認してください。マトリックスで `php: ['8.1.27', '8.2.20', '8.3.8']` のように指定するのが一番確実かもしれません。
2.  **`Makefile.nmake` の作成:** これが最も重要です。`nmake` でビルドするための `Makefile.nmake` を作成し、リポジトリに追加してください。PHP Extension の Windows ビルドに関するドキュメントや、他の拡張機能の例 (php-src/ext 内など) が参考になるはずです。`config.w32` の内容（特にコンパイラフラグ）を反映させる必要があります。
3.  **(オプション) CMake の検討:** 長期的に見れば、CMake を導入するのが Windows/Linux 両対応には最も堅牢な方法です。

まずは、手順1と2（特に **`Makefile.nmake` の作成**）に取り組んでみてください。それができれば、提示したワークフローでビルドが成功する可能性が高まります。
