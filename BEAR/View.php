<?php
/**
 * This file is part of the BEAR.Saturday package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * ビュー
 *
 * @Singleton
 *
 * @config string adapter     ビューアダプタークラス
 * @config bool   ua_sniffing UAスニッフィング？
 */
class BEAR_View extends BEAR_Factory
{
    /**
     * Constructor
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    /**
     * テンプレート名の取得
     *
     * @return array
     *
     * @todo Smarty以外のViewアダプタ
     */
    public function factory()
    {
        $options = $this->_config['enable_ua_sniffing'] ? ['injector' => 'onInjectUaSniffing'] : [];
        // 'BEAR_View_Smarty_Adapter' as default
        return BEAR::factory($this->_config['adapter'], $this->_config, $options);
    }
}
