<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/src/adminConfig.php';
require_once __DIR__ . '/src/frontDisplay.php';

use ArkweekPromo\AdminConfig;
use ArkweekPromo\FrontDisplay;

class Arkweekpromo extends Module{

    public function __construct(){
        $this->name = 'arkweekpromo';
        $this->tab = 'front_office_features';
        $this->version = '1.0.1';
        $this->author = 'Gabriel Del Fiaco';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_,
        ];

        parent::__construct();

        $this->displayName = $this->l('Week Promo Badge');
        $this->description = $this->l('Displays a promotional badge on discounted products that are on stock');        
    }

    public function install(){
        $languages = Language::getLanguages(true);

        foreach ($languages as $lang) {
            $defaultText = ($lang['iso_code'] === 'pl') ? 'Promocja tygodnia' : 'Deal of the week';

            Configuration::updateValue('ARKWEEKPROMO_TEXT_' . $lang['id_lang'], $defaultText );
        }

        return parent::install()
            && $this->registerHook('displayProductPriceBlock')
            && $this->registerHook('actionFrontControllerSetMedia')
            && Configuration::updateValue('ARKWEEKPROMO_ENABLED', 1)            
            && Configuration::updateValue('ARKWEEKPROMO_BG_COLOR', '#e74c3c')
            && Configuration::updateValue('ARKWEEKPROMO_TEXT_COLOR', '#ffffff');
    }

    public function uninstall(){
        $languages = Language::getLanguages(true);

        foreach ($languages as $lang) {
            Configuration::deleteByName('ARKWEEKPROMO_TEXT_' . $lang['id_lang']);            
        }

        return parent::uninstall()
            && Configuration::deleteByName('ARKWEEKPROMO_ENABLED')            
            && Configuration::deleteByName('ARKWEEKPROMO_BG_COLOR')
            && Configuration::deleteByName('ARKWEEKPROMO_TEXT_COLOR');
    }

    public function hookDisplayProductPriceBlock($params) {

        $controllerName = $this->context->controller->php_self;
        
        if (!in_array($controllerName, ['category', 'product'])) { // only render on listing and product page
            return '';
        }

        if ($params['type'] !== 'old_price') { //hook from the block product_price_and_shipping.
            return '';
        }

        return $this->renderBadge($params['product']);
    }

    //https://devdocs.prestashop-project.org/9/themes/concepts/asset-management/
    public function hookActionFrontControllerSetMedia(){

        // var_dump('/modules/' . $this->name . '/css/style.css');
        // die();

        $this->context->controller->registerStylesheet(
            'module-' . $this->name . '-style',
            '/modules/' . $this->name . '/views/css/ark-style.css',
            ['media' => 'all', 'priority' => 200]
        );

    }

    public function renderBadge($product){
        $frontDisplay = new FrontDisplay($this->context, $this);
        return $frontDisplay->render($product);            
    }

    public function getContent() {
        $adminConfig = new AdminConfig($this);
        return $adminConfig->getContent();
    }

}