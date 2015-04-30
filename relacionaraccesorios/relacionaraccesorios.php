<?php
/**
 * 2015 Francisco Gonzalez
 *
 *
 * @author Francisco Gonzalez
 * @copyright  2015 Francisco Gonzalez
 * @version  Release: $Revision: 1.6 $
 * @license Commercial
 */

if (!defined('_CAN_LOAD_FILES_'))
    exit;

class RelacionarAccesorios extends Module
{
    public function __construct()
    {
        $this->name = 'relacionaraccesorios';
        $this->tab = 'front_office_features';
        $this->version = '2.0';
        $this->author = 'Francisco Gonzalez';

        parent::__construct();

        $this->displayName = $this->l('Gestion de Relacion Accesorios');
        $this->description = $this->l('Este es un modulo para la gestion de accesorios y relacionarlos rapidamente con un excel.');
    }

    public function install()
    {
        if (!parent::install()
            OR !$this->installModuleTab('AdminRelacionarAccesorios', array(1 => 'Relacionar Accesorios Tab', 2 => 'Relacionar Accesorios Tab', 3 => 'Relacionar Accesorios Tab'), 1)
        )
            return false;
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()
            OR !$this->uninstallModuleTab('AdminRelacionarAccesorios')
        )
            return false;
        return true;
    }


    private function installModuleTab($tabClass, $tabName, $idTabParent)
    {
   
        $tab = new Tab();
        $tab->name = $tabName;
        $tab->class_name = $tabClass;
        $tab->module = $this->name;
        $tab->id_parent = $idTabParent;
        if (!$tab->save())
            return false;
        return true;
    }

    private function uninstallModuleTab($tabClass)
    {
        $idTab = Tab::getIdFromClassName($tabClass);
        if ($idTab != 0) {
            $tab = new Tab($idTab);
            $tab->delete();
            return true;
        }
        return false;
    }

}