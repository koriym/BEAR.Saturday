<?php
/**
 * This file is part of the BEAR.Saturday package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * リソースリクエスト実行
 *
 * リソースリクエストを実行するクラスです。
 * URIによってどの方法で実行するかををfacotryで判断しています。
 */
class BEAR_Resource_Execute extends BEAR_Factory
{
    /**
     * HTTPリソース
     */
    const FORMAT_HTTP = 'Http';

    /**
     * RO（クラス）リソース
     */
    const FORMAT_RO = 'Ro';

    /**
     * スタティックファイルリソース
     */
    const FORMAT_FILE = 'File';

    /**
     * ソケットリソース
     */
    const FORMAT_SOCKET = 'Socket';

    /**
     * ページリソース
     */
    const FORMAT_PAGE = 'Page';

    /**
     * ファクトリー
     *
     * URIによってリソースリクエスト実行クラスを確定して
     * インジェクションオブジェクトを生成します
     *
     * @throws BEAR_Resource_Execute_Exception
     *
     * @return BEAR_Resource_Execute_Interface
     */
    public function factory()
    {
        // モック
        if (isset($this->_config['options']['mock']) && $this->_config['options']['mock']) {
            return BEAR::factory('BEAR_Resource_Execute_Mock', $this->_config);
        }
        // パス情報も見て実行ファイルを決定
        $url = parse_url($this->_config['uri']);
        $path = pathinfo($this->_config['uri']);
        // スタティックバリューファイル　file:///var/data/data.ymlなど
        if (isset($url['scheme']) && ($url['scheme'] === 'file')) {
            $exeConfig = $this->_config;
            $exeConfig['url'] = $url;
            $exeConfig['path'] = $path;
            $exeConfig['file'] = $url['path'];
            $format = self::FORMAT_FILE;

            return BEAR::factory('BEAR_Resource_Execute_' . $format, $exeConfig);
        }
        if (isset($path['filename']) && ! (isset($url['host']))) {
            return self::_localResourceExecute($this->_config['uri']);
        }
        $executer = _BEAR_APP_HOME . '/App/Resource/Execute/' . ucwords($url['scheme']) . '.php';
        $isExecuterExists = file_exists($executer);
        if ($isExecuterExists) {
            $class = 'App_Resource_Execute_' . ucwords($url['scheme']);

            return BEAR::factory($class, $this->_config);
        }
        switch (true) {
            case isset($url['scheme']) && ($url['scheme'] == 'http' || $url['scheme'] == 'https'):
                $format = self::FORMAT_HTTP;

                break;
            case isset($url['scheme']) && $url['scheme'] == 'socket':
                $format = self::FORMAT_SOCKET;

                break;
            case isset($url['scheme']) && $url['scheme'] == 'page':
                $format = self::FORMAT_PAGE;

                break;
            default:
                $msg = 'URI is not valid.';
                $info = ['uri' => $this->_config['uri']];

                throw $this->_exception($msg, compact('info'));
        }

        return BEAR::factory('BEAR_Resource_Execute_' . $format, $this->_config);
    }

    /**
     * ローカルリソースの実行
     *
     * ローカルリソースファイル(Ro, Function, スタティックファイル等）を実行します。
     *
     * @param string $uri
     *
     * @throws BEAR_Resource_Exception
     *
     * @return stdClass
     */
    private function _localResourceExecute($uri)
    {
        $file = _BEAR_APP_HOME . '/App/Ro/' . $uri . '.php';
        if (file_exists($file)) {
            include_once $file;
            $resourcePathName = 'App_Ro_' . str_replace('/', '_', $uri);
            switch (true) {
                case class_exists($resourcePathName, false):
                    $this->_config['class'] = $resourcePathName;
                    $format = 'Ro';

                    break;
                case function_exists($resourcePathName):
                    // @deprecated
                    $this->_config['function'] = $resourcePathName;
                    $format = 'Function';

                    break;
                default:
                    $msg = 'Mismatch resource class/function error.（ファイル名とクラス/関数名がミスマッチです。)';
                    $info = [
                        'resource name' => $resourcePathName,
                        'resource file' => $file
                    ];

                    throw $this->_exception(
                        $msg,
                        [
                            'code' => BEAR::CODE_BAD_REQUEST,
                            'info' => $info
                        ]
                    );
            }
        } else {
            $file = _BEAR_APP_HOME . '/App/Ro/' . $uri;
            if (file_exists($file)) {
                $this->_config['file'] = $file;
                $format = self::FORMAT_FILE;
            } else {
                throw $this->_exception(
                    "Resource file[{$file}] is not exists.",
                    ['info' => ['uri' => $uri, 'file' => $file]]
                );
            }
        }
        $obj = BEAR::factory('BEAR_Resource_Execute_' . $format, $this->_config);

        return $obj;
    }
}
