<?php
/**
 * This file is part of the BEAR.Saturday package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Mobileエージェントアダプター
 */
abstract class BEAR_Agent_Adapter_Mobile extends BEAR_Agent_Adapter_Default
{
    /**
     * 携帯サ絵文字ポート対応なし
     *
     * @var int
     */
    const SUPPORT_NONE = 0;

    /**
     * 携帯絵文字サポートIMG変換
     *
     * @var int
     */
    const SUPPORT_IMG = 1;

    /**
     * 携帯絵文字サポートIMG変換
     *
     * @var int
     */
    const SUPPORT_CONV = 2;

    /**
     * @var string
     */
    protected $_header;

    /**
     * @var Smarty
     */
    protected $_smarty;

    /**
     * Constructor.
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $contentType = isset($this->_config['content_type']) ? $this->_config['content_type'] : 'application/xhtml+xml';
        $this->_config['is_mobile'] = true;
        $this->_config['agent_filter'] = true;
        $this->_config['header'] = 'Content-Type: ' . $contentType . '; charset=Shift_JIS';
        $this->_config['charset'] = 'utf-8';
        $this->_config['enable_js'] = false;
        $this->_config['role'] = [BEAR_Agent::UA_MOBILE, BEAR_Agent::UA_DEFAULT];
    }

    /**
     * Inject
     */
    public function onInject()
    {
        $this->_header = BEAR::dependency('BEAR_Page_Header');
        $this->_smarty = BEAR::dependency('BEAR_Smarty', ['ua' => $this->_config['ua']]);
    }

    /**
     * UTF-8化コールバック関数
     * 親クラスの関数と引数をあわせるために使用していないパラメータ追加
     *
     * @param string &$value 文字列
     */
    public static function onUTF8(
        &$value,
        /** @noinspection PhpUnusedParameterInspection */
        $key = null,
        /** @noinspection PhpUnusedParameterInspection */
        $inputEncode = null
    ) {
        BEAR::dependency(__CLASS__)->onUTF8($value);
    }

    /**
     * UTF-8化
     *
     * @param string &$value 文字列
     *
     * @throws BEAR_Exception
     * @ignore
     */
    public function UTF8(&$value)
    {
        if (! mb_check_encoding($value, $this->_codeFromMoble)) {
            $msg = 'Illegal Submit Values';
            $info = ['value' => $value];

            throw $this->_exception(
                $msg,
                [
                    'code' => BEAR::CODE_BAD_REQUEST,
                    'info' => $info
                ]
            );
        }
        $value = mb_convert_encoding($value, 'utf-8', $this->_codeFromMoble);
        if (! mb_check_encoding($value, 'utf-8')) {
            $msg = 'Illegal UTF-8';
            $info = ['value' => $value];

            throw $this->_exception(
                $msg,
                [
                    'code' => BEAR::CODE_BAD_REQUEST,
                    'info' => $info
                ]
            );
        }
    }
}
