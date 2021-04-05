<?php

/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyrigh;t 2007-2021 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */


use Doctrine\Common\Collections\Expr\Value;
use GeoIp2\Model\City;
use GeoIp2\Record\City as RecordCity;
use PrestaShop\PrestaShop\Core\Import\Importer;
use GeoIP2\WebService\Client;
use GeoIp2\Database\Reader;





if (!defined('_PS_VERSION_')) {
    exit;
}

class Tarea2 extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'tarea2';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Ruben';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('tarea2');
        $this->description = $this->l('modulo creado para realizar la tarea 2 ');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('TAREA2_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&  $this->registerHook('displayHeader') &&
            $this->registerHook('backOfficeHeader') &&  $this->registerHook('displayNav2');
    }

    public function uninstall()
    {
        Configuration::deleteByName('TAREA2_LIVE_MODE');



        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitTarea2Module')) == true) {
            $this->postProcess();
        } {
            if (!parent::uninstall() || !$this->unregisterHook('displayNav2'))
                return false;
            return true;
        } {
            if (!parent::uninstall() || !$this->unregisterHook('displayNav2'))
                return false;
            return true;
        }


        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitTarea2Module';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'TAREA2_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'TAREA2_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'TAREA2_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'TAREA2_LIVE_MODE' => Configuration::get('TAREA2_LIVE_MODE', true),
            'TAREA2_ACCOUNT_EMAIL' => Configuration::get('TAREA2_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'TAREA2_ACCOUNT_PASSWORD' => Configuration::get('TAREA2_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
            $this->context->controller->addCSS($this->_path . '/views/css/estilo.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
        $this->context->controller->addCSS($this->_path . '/views/css/estilo.css');
    }
    public function hookDisplayNav2()
    {
       

        $reader = new GeoIp2\Database\Reader(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_);

       

        $record = $reader->city('87.218.18.237');


        $ciudad = $record->city->name;

        $url = "http://api.openweathermap.org/data/2.5/weather?APPID=d4b3044a5fa5b21323639b5aab155c62&q=" . $ciudad . ",es&units=metric&lang=es";





        $tiempo = json_decode(file_get_contents($url), true);



        $temperatura = $tiempo['main']['temp'];
        $nubosidad = $tiempo['clouds']['all'];
        $velocidad = $tiempo['wind']['speed'];




        $record2 = $reader->city('195.135.249.53');


        $GLOBALS['smarty']->assign("ciudad", $ciudad);
        $GLOBALS['smarty']->assign("temperatura", $temperatura);
        $GLOBALS['smarty']->assign("nubosidad", $nubosidad);
        $GLOBALS['smarty']->assign("velocidad", $velocidad);
        return $this->display(__FILE__, 'views/templates/hook/tarea2.tpl');
    }
}
