<?php 

namespace ArkweekPromo;

use Tools;
use Validate;
use Configuration;
use Language;
use HelperForm;
use AdminController;

class AdminConfig{

    public function __construct($module){
        $this->module = $module;
    }

    public function getContent(){
        $output = '';

        // this part is executed only when the form is submitted
        if (Tools::isSubmit('submit' . $this->name)) {

            $output = $this->displayForm();            
        }

        // display any message, then the form
        return $output . $this->displayForm();
    }

    public function postValidation(){
        $ark_text     = (string) Tools::getValue('ARKWEEKPROMO_TEXT');
        $ark_bgColor  = (string) Tools::getValue('ARKWEEKPROMO_BG_COLOR');
        $ark_txtColor = (string) Tools::getValue('ARKWEEKPROMO_TEXT_COLOR');
        $ark_enabled  = (int) Tools::getValue('ARKWEEKPROMO_ENABLED');

        $errors = [];

        if (empty($ark_text) || !Validate::isGenericName($ark_text)) {
            $errors[] = $this->l('Badge text is invalid or empty.');
        }

        if (!Validate::isColor($ark_bgColor)) {
            $errors[] = $this->l('Background color must be a valid hex color.');
        }

        if (!Validate::isColor($ark_txtColor)) {
            $errors[] = $this->l('Text color must be a valid hex color.');
        }

        if (!empty($errors)) {
            // Show all errors at once
            $output = $this->displayError(implode('<br>', $errors));
        } else {
            Configuration::updateValue('ARKWEEKPROMO_ENABLED',    $ark_enabled);
            Configuration::updateValue('ARKWEEKPROMO_TEXT',       $ark_text);
            Configuration::updateValue('ARKWEEKPROMO_BG_COLOR',   $ark_bgColor);
            Configuration::updateValue('ARKWEEKPROMO_TEXT_COLOR', $ark_txtColor);

            $output = $this->displayConfirmation(
                $this->l('Settings updated')
            );
        }
    }

    public function displayForm() {
        // Init Fields form array
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Arkon Week Promo Settings'),
                ],
                'input' => [
                [
                    'type'    => 'switch',
                    'label'   => $this->l('Enable Badge'),
                    'name'    => 'ARKWEEKPROMO_ENABLED',
                    'values'  => [
                        ['id' => 'active_on',  'value' => 1, 'label' => $this->l('Yes')],
                        ['id' => 'active_off', 'value' => 0, 'label' => $this->l('No')],
                    ],
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Badge Text'),
                    'name'  => 'ARKWEEKPROMO_TEXT',
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
            ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;

        // Default language
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');

        // Load current saved values into the form
        $helper->fields_value['ARKWEEKPROMO_ENABLED']    = Configuration::get('ARKWEEKPROMO_ENABLED');
        $helper->fields_value['ARKWEEKPROMO_TEXT']       = Configuration::get('ARKWEEKPROMO_TEXT');
        $helper->fields_value['ARKWEEKPROMO_BG_COLOR']   = Configuration::get('ARKWEEKPROMO_BG_COLOR');
        $helper->fields_value['ARKWEEKPROMO_TEXT_COLOR'] = Configuration::get('ARKWEEKPROMO_TEXT_COLOR');

        return $helper->generateForm([$form]);
    }
}