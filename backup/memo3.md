







# ... (ワークフローの on, jobs, build, strategy, name) ...

steps:
  # 3.1 Setup MSVC build environment (Windows)
  - name: Setup MSVC build environment (Windows)
    if: runner.os == 'Windows'
    uses: ilammy/msvc-dev-cmd@v1
    # Default architecture is x64, which is common

  # 3.2 Download and Setup PHP SDK & Source (Windows)

  # ★★★ Windows Build Environment Setup End ★★★

  # 4. Get Extension version from header
  - name: Get Extension version from header
    id: ext_version
    # ... (sed command as before) ...
    shell: bash # Keep using bash for sed

  # 5. Build extension (Linux)
  - name: Build extension (Linux)
    if: runner.os == 'Linux'
    # ... (Linux build steps) ...

  # 5. Build extension (Windows) - Now using the prepared SDK environment
  - name: Build extension (Windows)
    if: runner.os == 'Windows'
    run: |
      REM Ensure PATH includes MSVC tools (should be set by msvc-dev-cmd action)
      REM PHP SDK PATH should be set by previous step using GITHUB_PATH or add-path

      REM Navigate to PHP source directory
      cd "%PHP_SRC_PATH%"

      REM Regenerate configuration to include the extension
      buildconf --force

      REM Configure the build (adjust paths and options if needed)
      REM Ensure config.w32 is used, enable the extension
      configure --disable-all --enable-cli --enable-rindow_operatorovl=yes

      REM Build the specific DLL target
      nmake php_rindow_operatorovl.dll
    shell: cmd

  # 6. Run tests (Linux)
  # ...

  # 6. Run tests (Windows) - Requires careful path setup
  # - name: Run tests (Windows)
  #   if: runner.os == 'Windows'
  #   run: |
  #     cd "%PHP_SRC_PATH%"
  #     REM Run tests for the specific extension
  #     nmake test TESTS=ext/rindow_operatorovl/tests/*.phpt
  #   env:
  #     NO_INTERACTION: 1
  #     REPORT_EXIT_STATUS: 1
  #   shell: cmd

  # ... (Linux DEB packaging steps) ...

  # --- Windows (.dll) Artifact Steps ---
  # (Determine DLL Artifact Info - search path needs update)
  - name: Determine DLL Artifact Info (Windows)
    if: runner.os == 'Windows'
    id: dll_artifact_info
    run: |
      # Search within the built PHP source tree
      $phpSrcPath = $env:PHP_SRC_PATH
      # Common output dir (adjust based on actual build: x64/Release_NTS or similar)
      $buildOutputDir = Join-Path $phpSrcPath "x64\Release_NTS" # Adjust architecture/config if needed
      $dllPath = Join-Path $buildOutputDir "php_rindow_operatorovl.dll"

      if (-not (Test-Path $dllPath)) {
        echo "Warning: DLL not found at expected path: $dllPath"
        echo "Searching entire PHP source tree ($phpSrcPath)..."
        $foundDll = (Get-ChildItem -Path $phpSrcPath -Recurse -Filter php_rindow_operatorovl.dll -ErrorAction SilentlyContinue | Select-Object -First 1).FullName
        if ($foundDll) {
           echo "Found DLL at: $foundDll"
           $dllPath = $foundDll
        } else {
          echo "Error: DLL search failed in $phpSrcPath"
          exit 1
        }
      }

      # Create a more descriptive artifact name
      $phpVersion = "${{ matrix.php }}"
      $extVersion = "${{ steps.ext_version.outputs.version }}"
      # Assuming x64 NTS build, adjust if matrix includes TS or x86
      $dllName = "php_rindow_operatorovl-$extVersion-php$phpVersion-windows-x64-nts.dll"
      echo "DLL path: $dllPath"
      echo "Artifact name: $dllName"
      echo "path=$dllPath" >> $env:GITHUB_OUTPUT
      echo "name=$dllName" >> $env:GITHUB_OUTPUT
    shell: pwsh

  # ... (Upload DLL artifact steps) ...