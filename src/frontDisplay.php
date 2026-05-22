<?php 

namespace ArkweekPromo;

use Configuration;

class FrontDisplay{

    private $context;
    private $module;

    public function __construct($context, $module){
        $this->context = $context;
        $this->module  = $module;
    }

    public function render($product): string{
        if (!(bool) Configuration::get('ARKWEEKPROMO_ENABLED')) {
            return '';
        }

        // handle both object and array
        $reduction = is_array($product) ? $product['reduction'] : $product->reduction;
        $available = is_array($product) ? $product['available_for_order'] : $product->available_for_order;

        if (empty($reduction) || empty($available)) {
            return '';
        }

        $this->context->smarty->assign([
            'badge_text'       => Configuration::get('ARKWEEKPROMO_TEXT'),
            'badge_bg_color'   => Configuration::get('ARKWEEKPROMO_BG_COLOR'),
            'badge_text_color' => Configuration::get('ARKWEEKPROMO_TEXT_COLOR'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/badge.tpl');
    }
}
