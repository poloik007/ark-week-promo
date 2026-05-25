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

        if (Tools::isSubmit('submit' . $this->module->name)) {

            $output = $this->postValidation();           
        }

        return $output . $this->displayForm();
    }

    public function postValidation(){
         $output = '';

        if (Tools::isSubmit('submit' . $this->module->name)) {

            // retrieve the value set by the user           
            $ark_bgColor  = (string) Tools::getValue('ARKWEEKPROMO_BG_COLOR');
            $ark_txtColor = (string) Tools::getValue('ARKWEEKPROMO_TEXT_COLOR');
            $ark_enabled  = (int) Tools::getValue('ARKWEEKPROMO_ENABLED');

            $errors = [];
        
            $languages = Language::getLanguages(true);

            foreach ($languages as $lang) {
                $ark_text = (string) Tools::getValue('ARKWEEKPROMO_TEXT_' . $lang['id_lang']);

                if (empty($ark_text) || !Validate::isGenericName($ark_text)) {
                    $errors[] = $this->module->l('Badge text is invalid for') . ' ' . $lang['name'];
                    continue;
                }

                Configuration::updateValue('ARKWEEKPROMO_TEXT_' . $lang['id_lang'], $ark_text);
            }

            // Validate hex colors -- got from an example online
            if (!Validate::isColor($ark_bgColor)) {
                $errors[] = $this->module->l('Background color must be a valid hex color (e.g. #e74c3c).');
            }

            if (!Validate::isColor($ark_txtColor)) {
                $errors[] = $this->module->l('Text color must be a valid hex color (e.g. #ffffff).');
            }

            if (!empty($errors)) {
                // Show all errors at once
                $output = $this->module->displayError(implode('<br>', $errors));
            } else {
                Configuration::updateValue('ARKWEEKPROMO_ENABLED',    $ark_enabled);
                Configuration::updateValue('ARKWEEKPROMO_BG_COLOR',   $ark_bgColor);
                Configuration::updateValue('ARKWEEKPROMO_TEXT_COLOR', $ark_txtColor);

                $output = $this->module->displayConfirmation($this->module->l('Settings updated'));
            }
        }

        // display message
        return $output;
    }

    public function displayForm() {
        $languages = Language::getLanguages(true);

        $inputs = [
            [
                'type'   => 'switch',
                'label'  => $this->module->l('Enable Badge'),
                'name'   => 'ARKWEEKPROMO_ENABLED',
                'values' => [
                    ['id' => 'active_on',  'value' => 1, 'label' => $this->module->l('Yes')],
                    ['id' => 'active_off', 'value' => 0, 'label' => $this->module->l('No')],
                ],
            ],
            [
                'type'  => 'color',
                'label' => $this->module->l('Background Color'),
                'name'  => 'ARKWEEKPROMO_BG_COLOR',
            ],
            [
                'type'  => 'color',
                'label' => $this->module->l('Text Color'),
                'name'  => 'ARKWEEKPROMO_TEXT_COLOR',
            ],
        ];

        foreach ($languages as $lang) {
            $inputs[] = [
                'type'  => 'text',
                'label' => $this->module->l('Badge Text') . ' — ' . $lang['name'],
                'name'  => 'ARKWEEKPROMO_TEXT_' . $lang['id_lang'],
            ];
        }

        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Arkon Week Promo Settings'),
                ],
                'input'  => $inputs,
                'submit' => [
                    'title' => $this->module->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];
        

        $helper = new HelperForm();

        $helper->table = $this->module->name;
        $helper->name_controller = $this->module->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->module->name]);
        $helper->submit_action = 'submit' . $this->module->name;

        $helper->fields_value['ARKWEEKPROMO_ENABLED']    = Configuration::get('ARKWEEKPROMO_ENABLED');
        $helper->fields_value['ARKWEEKPROMO_BG_COLOR']   = Configuration::get('ARKWEEKPROMO_BG_COLOR');
        $helper->fields_value['ARKWEEKPROMO_TEXT_COLOR'] = Configuration::get('ARKWEEKPROMO_TEXT_COLOR');

        foreach ($languages as $lang) {
            $helper->fields_value['ARKWEEKPROMO_TEXT_' . $lang['id_lang']] = Configuration::get('ARKWEEKPROMO_TEXT_' . $lang['id_lang']);
        }

        return $helper->generateForm([$form]);
    }
}