素晴らしい `.deb` パッケージ作成スクリプトですね！ GitHub Actions のワークフローを修正して、ビルド後にこのスクリプトを実行し、生成された `.deb` ファイルをアップロードするように変更しましょう。

以下に修正したワークフロー (`.github/workflows/build.yml`) を示します。

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
    # .deb パッケージを作成するため、Ubuntu 環境に限定
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        # os: [ubuntu-latest] # Ubuntu のみに限定したので matrix から削除
        php: ['8.1', '8.2', '8.3'] # ビルド対象のPHPバージョン

    name: Build and Package on Ubuntu for PHP ${{ matrix.php }}

    steps:
    # 1. リポジトリのコードをチェックアウト
    - name: Checkout code
      uses: actions/checkout@v4

    # 2. 指定したPHPバージョンとビルドツールをセットアップ
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ matrix.php }}
        extensions: none # この拡張自体をビルドするため、他の拡張は不要
        tools: phpize, php-config # phpize と php-config コマンドを使えるようにする
      env:
        COMPOSER_TOKEN: ${{ secrets.GITHUB_TOKEN }} # composerを使わないなら不要

    # 3. ビルドとパッケージングに必要な依存パッケージをインストール (Ubuntuの場合)
    - name: Install build and packaging dependencies (Ubuntu)
      run: |
        sudo apt-get update
        # build-essential: コンパイラ等, dpkg-dev: dpkg-debを含む, fakeroot: root権限なしでファイル所有者を偽装
        sudo apt-get install -y build-essential dpkg-dev fakeroot

    # 4. 拡張機能のバージョンを取得 (php_rindow_operatorovl.hから取得) - AC_INITなしの場合
    - name: Get Extension version from header
      id: ext_version
      run: |
        # php_rindow_operatorovl.h から #define PHP_RINDOW_OPERATOROVL_VERSION "..." の行を探し、ダブルクォートの中身を抽出
        VERSION=$(grep '#define PHP_RINDOW_OPERATOROVL_VERSION' php_rindow_operatorovl.h | cut -d '"' -f 2)
        # 念のためバージョンが取得できたかチェック
        if [[ -z "$VERSION" ]]; then
          echo "Error: Could not extract version from php_rindow_operatorovl.h"
          exit 1
        fi
        echo "version=${VERSION}" >> $GITHUB_OUTPUT
        echo "Extracted version: ${VERSION}"

    # 5. PHP拡張をビルド
    - name: Build extension
      run: |
        # setup-php が設定した phpize を使用 (例: phpize8.3)
        phpize
        # config.m4 に enable オプションがある場合は有効にする
        ./configure --enable-rindow_operatorovl
        make clean
        make # modules/rindow_operatorovl.so が生成される

    # ---- ここからパッケージングステップ ----

    # 6. パッケージングスクリプトに実行権限を付与
    - name: Make packaging script executable
      run: chmod +x packaging.sh

    # 7. DEBパッケージを作成
    - name: Create DEB package
      run: ./packaging.sh ${{ matrix.php }} # packaging.sh にPHPバージョンを引数として渡す

    # 8. 生成されたDEBファイルのパスと名前を決定
    - name: Determine DEB Artifact Info
      id: deb_artifact_info
      run: |
        # dpkg-deb は通常 <package>_<version>_<architecture>.deb という名前で出力する
        # packaging.sh 内で control ファイルに設定した Package 名を取得
        PACKAGE_NAME=$(grep '^Package:' pkgwork/DEBIAN/control | cut -d' ' -f2)
        # バージョンは前のステップで取得済み
        VERSION="${{ steps.ext_version.outputs.version }}"
        # アーキテクチャを取得 (GitHub ActionsのUbuntu Runnerは通常amd64)
        ARCHITECTURE=$(dpkg --print-architecture)
        # DEBファイル名を構築
        DEB_FILENAME="${PACKAGE_NAME}_${VERSION}_${ARCHITECTURE}.deb"
        # スクリプトはカレントディレクトリに .deb を出力する想定
        if [[ ! -f "$DEB_FILENAME" ]]; then
          echo "Error: Expected DEB file '$DEB_FILENAME' not found in current directory!"
          # 存在しない場合に備えて、実際の .deb ファイルを表示してみる
          ls -l *.deb || true
          exit 1
        fi
        echo "path=${DEB_FILENAME}" >> $GITHUB_OUTPUT
        echo "name=${DEB_FILENAME}" >> $GITHUB_OUTPUT
        echo "Built DEB artifact: ${DEB_FILENAME}"

    # 9. DEBパッケージをGitHub ActionsのArtifactsとしてアップロード
    - name: Upload DEB artifact
      uses: actions/upload-artifact@v4
      with:
        name: ${{ steps.deb_artifact_info.outputs.name }} # DEBファイル名を使用
        path: ${{ steps.deb_artifact_info.outputs.path }} # DEBファイルのパスを使用
        retention-days: 7 # 保存期間 (デフォルトは90日)

    # 10. (リリース時のみ) DEBパッケージをリリースのAssetsとしてアップロード
    - name: Upload Release Asset (DEB)
      if: github.event_name == 'release' # 'release'イベントでトリガーされた場合のみ実行
      uses: actions/upload-release-asset@v1
      env:
        GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }} # リリースへのアップロード権限を持つトークン
      with:
        upload_url: ${{ github.event.release.upload_url }} # アップロード先URL (自動設定)
        asset_path: ${{ steps.deb_artifact_info.outputs.path }} # アップロードするファイルのパス
        asset_name: ${{ steps.deb_artifact_info.outputs.name }} # リリースでのファイル名
        asset_content_type: application/vnd.debian.binary-package # DEBパッケージのMIMEタイプ
```

**変更点の解説:**

1.  **`runs-on`:** `.deb` パッケージ作成は Ubuntu 環境で行う必要があるため、`matrix.os` を削除し、`runs-on: ubuntu-latest` に固定しました。
2.  **依存パッケージのインストール:** ステップ3で、ビルドに必要な `build-essential` に加えて、`.deb` パッケージ作成に必要な `dpkg-dev` (`dpkg-deb` コマンドを含む) と `fakeroot` をインストールするように変更しました。
3.  **バージョン取得:** `AC_INIT` を使わない構成に基づき、`php_rindow_operatorovl.h` からバージョンを取得するステップ4を残しました。
4.  **ビルドステップ:** ステップ5は変更ありません。`make` コマンドで `modules/rindow_operatorovl.so` が生成されることを前提としています。
5.  **パッケージングステップ追加 (6, 7):**
    *   ステップ6で `packaging.sh` に実行権限を与えます。
    *   ステップ7で `./packaging.sh ${{ matrix.php }}` を実行し、`.deb` パッケージを作成します。スクリプトに PHP バージョンを渡しています。
6.  **DEBファイル情報の決定 (8):**
    *   `packaging.sh` が生成する `.deb` ファイルの名前を特定するステップを追加しました。
    *   `pkgwork/DEBIAN/control` からパッケージ名 (`Package:`) を取得します。
    *   バージョンはステップ4で取得したものを使います。
    *   アーキテクチャは `dpkg --print-architecture` コマンドで取得します (通常 `amd64`)。
    *   これらを組み合わせて期待されるファイル名 (`<package>_<version>_<architecture>.deb`) を構築し、存在を確認します。
    *   ファイルパスとファイル名を `outputs` に設定し、後続のステップで利用できるようにします。
7.  **アップロードステップ変更 (9, 10):**
    *   `upload-artifact` と `upload-release-asset` アクションで、ステップ8で特定した `.deb` ファイルのパス (`${{ steps.deb_artifact_info.outputs.path }}`) と名前 (`${{ steps.deb_artifact_info.outputs.name }}`) を使うように変更しました。
    *   `upload-release-asset` の `asset_content_type` を `.deb` パッケージの適切な MIME タイプ (`application/vnd.debian.binary-package`) に変更しました。

**`packaging.sh` に関する推奨:**

*   スクリプト内の `chmod 744 ...` は、実行権限が不要な `.so` ファイルや `.ini` ファイルに対しては `chmod 644 ...` に変更することをお勧めします。セキュリティ上、不要な実行権限は避けるべきです。
*   古いPHPバージョンの判定ロジック (`if [ `echo $PHP_VERSION | awk ...`) は、現在のワークフローが PHP 8.1+ を対象としているため、不要であれば削除しても構いません。

これで、GitHub Actions で PHP 拡張をビルドし、それを `.deb` パッケージ化して成果物としてアップロードできるようになるはずです。リポジトリに `.github/workflows/build.yml` として追加し、`packaging.sh`, `conf/20-rindow_operatorovl.ini`, `debian/control` もリポジトリに含めてください。