<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

// require_once __DIR__ . '/src/adminConfig.php';
// require_once __DIR__ . '/src/frontDisplay.php';

// use ArkweekPromo\AdminConfig;
// use ArkweekPromo\FrontDisplay;

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
            && Configuration::updateValue('ARKWEEKPROMO_ENABLED', 1)
            // && Configuration::updateValue('ARKWEEKPROMO_TEXT', 'Promocja tygodnia')
            && Configuration::updateValue('ARKWEEKPROMO_BG_COLOR', '#e74c3c')
            && Configuration::updateValue('ARKWEEKPROMO_TEXT_COLOR', '#ffffff');
    }

    public function uninstall(){
        foreach ($languages as $lang) {
            Configuration::deleteByName('ARKWEEKPROMO_TEXT_' . $lang['id_lang']);            
        }

        return parent::uninstall()
            && Configuration::deleteByName('ARKWEEKPROMO_ENABLED')
            // && Configuration::deleteByName('ARKWEEKPROMO_TEXT')
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

    // Tentative to separate the admin logic from the main module class. 
    // public function getContent(){

    //     $adminConfig = new AdminConfig($this->context, $this);
    //     return $adminConfig->getContent();

    // }

    private function renderBadge($product){
        
        if (!(bool) Configuration::get('ARKWEEKPROMO_ENABLED')) { //Configuration always return a string, so we need to cast it to boolean.
            return ''; 
        }

        // handle both object and array
        $reduction = is_array($product) ? $product['reduction'] : $product->reduction;
        $available = is_array($product) ? $product['available_for_order'] : $product->available_for_order;

        if (empty($reduction) || empty($available)) {
            return '';
        }

        $this->context->smarty->assign([
            // 'badge_text'       => Configuration::get('ARKWEEKPROMO_TEXT'),
            'badge_text'       => Configuration::get('ARKWEEKPROMO_TEXT_' . $this->context->language->id),
            'badge_bg_color'   => Configuration::get('ARKWEEKPROMO_BG_COLOR'),
            'badge_text_color' => Configuration::get('ARKWEEKPROMO_TEXT_COLOR'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/badge.tpl');
    }

    public function getContent(){
        $output = '';

        // this part is executed only when the form is submitted
        if (Tools::isSubmit('submit' . $this->name)) {
            // retrieve the value set by the user

            
            // $ark_text     = (string) Tools::getValue('ARKWEEKPROMO_TEXT');            
            $ark_bgColor  = (string) Tools::getValue('ARKWEEKPROMO_BG_COLOR');
            $ark_txtColor = (string) Tools::getValue('ARKWEEKPROMO_TEXT_COLOR');
            $ark_enabled  = (int) Tools::getValue('ARKWEEKPROMO_ENABLED');

            $errors = [];
            
            // if (empty($ark_text) || !Validate::isGenericName($ark_text)) {
            //     $errors[] = $this->l('Badge text is invalid or empty.');
            // }

            foreach ($languages as $lang) {
                $ark_text = (string) Tools::getValue('ARKWEEKPROMO_TEXT_' . $lang['id_lang']);

                if (empty($ark_text) || !Validate::isGenericName($ark_text)) {
                    $errors[] = $this->l('Badge text is invalid for') . ' ' . $lang['name'];
                    continue;
                }

                Configuration::updateValue('ARKWEEKPROMO_TEXT_' . $lang['id_lang'], $ark_text);
            }

            // Validate hex colors -- got from an example online
            if (!Validate::isColor($ark_bgColor)) {
                $errors[] = $this->l('Background color must be a valid hex color (e.g. #e74c3c).');
            }

            if (!Validate::isColor($ark_txtColor)) {
                $errors[] = $this->l('Text color must be a valid hex color (e.g. #ffffff).');
            }

            if (!empty($errors)) {
                // Show all errors at once
                $output = $this->displayError(implode('<br>', $errors));
            } else {
                Configuration::updateValue('ARKWEEKPROMO_ENABLED',    $ark_enabled);
                // Configuration::updateValue('ARKWEEKPROMO_TEXT',       $ark_text);
                Configuration::updateValue('ARKWEEKPROMO_BG_COLOR',   $ark_bgColor);
                Configuration::updateValue('ARKWEEKPROMO_TEXT_COLOR', $ark_txtColor);

                $output = $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        // display any message, then the form
        return $output . $this->displayForm();
    }

    public function displayForm() {
        // $form = [
        //     'form' => [
        //         'legend' => [
        //             'title' => $this->l('Arkon Week Promo Settings'),
        //         ],
        //         'input' => [
        //             [
        //                 'type'    => 'switch',
        //                 'label'   => $this->l('Enable Badge'),
        //                 'name'    => 'ARKWEEKPROMO_ENABLED',
        //                 'values'  => [
        //                     ['id' => 'active_on',  'value' => 1, 'label' => $this->l('Yes')],
        //                     ['id' => 'active_off', 'value' => 0, 'label' => $this->l('No')],
        //                 ],
        //             ],
        //             // [
        //             //     'type'  => 'text',
        //             //     'label' => $this->l('Badge Text'),
        //             //     'name'  => 'ARKWEEKPROMO_TEXT',
        //             // ],
        //             [
        //                 'type'  => 'color',
        //                 'label' => $this->l('Background Color'),
        //                 'name'  => 'ARKWEEKPROMO_BG_COLOR',
        //             ],
        //             [
        //                 'type'  => 'color',
        //                 'label' => $this->l('Text Color'),
        //                 'name'  => 'ARKWEEKPROMO_TEXT_COLOR',
        //             ],
        //         ],
        //         'submit' => [
        //             'title' => $this->l('Save'),
        //             'class' => 'btn btn-default pull-right',
        //         ],
        //     ],
        // ];


        //================ multilanguage form generation ============================
        $languages = Language::getLanguages(true);

        $inputs = [
            [
                'type'   => 'switch',
                'label'  => $this->l('Enable Badge'),
                'name'   => 'ARKWEEKPROMO_ENABLED',
                'values' => [
                    ['id' => 'active_on',  'value' => 1, 'label' => $this->l('Yes')],
                    ['id' => 'active_off', 'value' => 0, 'label' => $this->l('No')],
                ],
            ],
            [
                'type'  => 'color',
                'label' => $this->l('Background Color'),
                'name'  => 'ARKWEEKPROMO_BG_COLOR',
            ],
            [
                'type'  => 'color',
                'label' => $this->l('Text Color'),
                'name'  => 'ARKWEEKPROMO_TEXT_COLOR',
            ],
        ];

        // Add one text input per language dynamically
        foreach ($languages as $lang) {
            $inputs[] = [
                'type'  => 'text',
                'label' => $this->l('Badge Text') . ' — ' . $lang['name'],
                'name'  => 'ARKWEEKPROMO_TEXT_' . $lang['id_lang'],
            ];
        }

        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Arkon Week Promo Settings'),
                ],
                'input'  => $inputs,
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];
        

        $helper = new HelperForm();

        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;

        //$helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');

        $helper->fields_value['ARKWEEKPROMO_ENABLED']    = Configuration::get('ARKWEEKPROMO_ENABLED');
        // $helper->fields_value['ARKWEEKPROMO_TEXT']       = Configuration::get('ARKWEEKPROMO_TEXT');
        $helper->fields_value['ARKWEEKPROMO_BG_COLOR']   = Configuration::get('ARKWEEKPROMO_BG_COLOR');
        $helper->fields_value['ARKWEEKPROMO_TEXT_COLOR'] = Configuration::get('ARKWEEKPROMO_TEXT_COLOR');

        foreach ($languages as $lang) {
            $helper->fields_value['ARKWEEKPROMO_TEXT_' . $lang['id_lang']] = Configuration::get('ARKWEEKPROMO_TEXT_' . $lang['id_lang']);
        }

        return $helper->generateForm([$form]);
    }

}