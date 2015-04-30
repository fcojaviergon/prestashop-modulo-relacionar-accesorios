<?php
include_once(PS_ADMIN_DIR . '/../classes/AdminTab.php');

/*

Documentación DB class: http://doc.prestashop.com/display/PS16/Best+Practices+of+the+Db+Class

*/
class AdminRelacionarAccesorios extends AdminTab
{
    public $url;
    public $id_lang;
    public $path;

    public function __construct()
    {
        $this->url = __PS_BASE_URI__ . "modules/relacionaraccesorios/";
        $this->id_lang = intval(Configuration::get('PS_LANG_DEFAULT'));
        $this->path = _PS_MODULE_DIR_ . "relacionaraccesorios/";
        parent::__construct();
    }

    public function display()
    {
        global $cookie, $currentIndex;
        echo '
        <style type="text/css">
            table.iap {
                padding: 0;
                border: 1px solid #DFD5C3;
                border-collapse:collapse;
                background-color:white;
            }
            td.iap {
                border:1px solid #DFD5C3;
                color :#963;
                padding:5px;
            }
            td.iapo {
                background-color:#F4E8CD;
            }
        </style>
    <form action="' . $_SERVER['REQUEST_URI'] . '" method="post" enctype="multipart/form-data">
            <fieldset><legend>' . $this->l('Relacionar Accesorios Por Referencia (SKU)') . '</legend>
                <div>' .
             $this->l('Este modulo:') . '
                    <br/>' .
             $this->l('- Lee un archivo csv y relaciona acesorios por sku donde la primera columna es el producto y la segunda coluna es un listado de sku para accesorios.') . '
                    <br/>' .
             
             $this->l('Los campos deben estar separados por ",", los accesorios por ";" y delimitado por comillas (")') . '
                    <br/>' .
             $this->l('A continuación se muestra la lista de campos disponibles:') . '
                    <br/>
                    <br/>
                    <table class="iap">
                        <tr>
                                <td class="iap iapo">SKU</td>
                                <td class="iap iapo">' .
             $this->l('Campo Requerido') . '<br/>' .
             $this->l('El SKU (identificador del producto') . '<br/>' .
             $this->l('Sin "/"') . '
                                </td>
                        </tr>
                        <tr>
                              
                                <td class="iap iapo">ACCESORIOS</td>
                                <td class="iap iapo">
                                    ' . $this->l('Campo Requerido') . '<br/>' .
             $this->l('Campo con los SKU separados por ,') . '<br/>' .'
                                </td>
                        </tr>
                    </table>
                </div>
                <br/>
                <div class="margin-form">
                <p>Insertar: <input type="radio" name="accion" value="insertar" checked /></p>
                <p>Eliminar: <input type="radio" name="accion" value="eliminar"/></p>
                </div>
                <div class="margin-form"><input type="file" name="relacionaraccesorios"/></div>
                <div class="margin-form"><input type="submit" name="submitImportrelacionaraccesorios" value="' . $this->l('Importar') . '" class="button" /><div>
            </fieldset>
        </form>';
    }

    public function postProcess()
    {
        global $currentIndex;
        if (Tools::isSubmit('submitImportrelacionaraccesorios')) {
            set_time_limit(0);
            $fileName = dirname(__FILE__) . '/imports/' . date('Ymdhis') . '_products_relacionados.csv';
            if (!isset($_FILES['relacionaraccesorios']['tmp_name']) OR empty($_FILES['relacionaraccesorios']['tmp_name']))
                $this->_errors[] = Tools::displayError($this->l('no file selected'));
            elseif (!file_exists($_FILES['relacionaraccesorios']['tmp_name']) OR !move_uploaded_file($_FILES['relacionaraccesorios']['tmp_name'], $fileName))
                $this->_errors[] = Tools::displayError($this->l('an error occured while uploading and copying file'));
            elseif ($file = fopen($fileName, "r")) {
                $row = 1;
                $columns_id = array();
                $columns_name = array();

                while (($data = fgetcsv($file, "", ",")) !== FALSE && (!isset($errors))) {
                    $num = count($data);
                    if ($row == 1) {
                        if (!in_array("SKU", $data)) {
                            $this->_errors[] = Tools::displayError($this->l('Su archivo debe tener una columna "SKU".'));
                        }elseif (!in_array("ACCESORIOS", $data)) {
                            $this->_errors[] = Tools::displayError($this->l('Su archivo debe tener una columna "ACCESORIOS".'));
                        }
                        else {
                            for ($c = 0; $c < $num; $c++) {
                                $columns_id[$data[$c]] = $c;
                                $columns_name[$c] = $data[$c];
                            }
                        }
                    }
                    else {
                        //Si el Producto existe en la base de datos...
                        if (Product::existsRefInDatabase($data[$columns_id['SKU']])) {
                            $accesorios = explode(";",$data[$columns_id['ACCESORIOS']]);
                            foreach ($accesorios as $key => $value) {
                                $accion = Tools::getValue('accion');

                                if($accion == "insertar"){
                                    if(!empty($value)){
                                         if(!$this->saveAccesorio($data[$columns_id['SKU']], $value))
                                            $this->_errors[] = Tools::displayError($this->l('Un error ha ocurrido en la linea:') . $row );
                                    }
                                }else if($accion == "eliminar"){
                                    if(!empty($value)){
                                         if(!$this->deleteAccesorio($data[$columns_id['SKU']], $value))
                                            $this->_errors[] = Tools::displayError($this->l('Un error ha ocurrido en la linea:') . $row );
                                    }
                                }else{
                                     $this->_errors[] = Tools::displayError($this->l('Sin accion'));
                                }                                
                            }

                        }
                        else
                            $this->_errors[] = Tools::displayError($this->l('Un error ha ocurrido en la linea:') . $row . ' ' . $this->l('producto') . ' ' . $data[$columns_id["SKU"]] . $this->l(' no existe.'));
                
                        
                    }
                    $row++;
                }
                fclose($file);
                //Tools::redirectAdmin($currentIndex . '&conf=3&token=' . $this->token);
            }
            else
                $this->_errors[] = Tools::displayError($this->l('Error al abrir el archivo'));
        }
    }

    function getProductIdBySKU($sku)
    {
        $row = Db::getInstance()->getRow("
        SELECT `id_product`
        FROM " . _DB_PREFIX_ . "product p
        WHERE p.`reference` = '". $sku."'");

        return $row['id_product'];
    }   

    function saveAccesorio($sku, $sku_accesorio) {

        
        $id_product = intval($this->getProductIdBySKU($sku));
        $id_accesorio = intval($this->getProductIdBySKU($sku_accesorio));
        //echo $id_product;
        //echo $id_accesorio;
        if ($id_product == 0 || $id_accesorio==0) { return false; }


           $sql = 'SELECT id_product_1 FROM ' . _DB_PREFIX_ . 'accessory WHERE id_product_1 = ' . $id_product.' AND id_product_2 = '.$id_accesorio;

           $row = Db::getInstance()->getRow($sql,$use_cache = 0);

           if (!empty(isset($row['id_product_1']))){

           }else {
             
                 if (!Db::getInstance()->insert('accessory', 
                    array(
                        'id_product_1' => intval($id_product),
                        'id_product_2' => intval($id_accesorio),
                        )
                    )){
                    return false;
                }       
            
           }
        
        return true;
                
    }

    function deleteAccesorio($sku, $sku_accesorio) {

        
        $id_product = intval($this->getProductIdBySKU($sku));
        $id_accesorio = intval($this->getProductIdBySKU($sku_accesorio));
        //echo $id_product;
        //echo $id_accesorio;
        if ($id_product == 0 || $id_accesorio==0) { return false; }


           $sql = 'SELECT id_product_1 FROM ' . _DB_PREFIX_ . 'accessory WHERE id_product_1 = ' . $id_product.' AND id_product_2 = '.$id_accesorio;

           $row = Db::getInstance()->getRow($sql,$use_cache = 0);

           if (!empty(isset($row['id_product_1']))){

            if (!Db::getInstance()->delete('accessory', 'id_product_1 = '.$row['id_product_1'], 0 , 0)){
                return false;
            }


           }else {
                
           }
        
        return true;
                
    }



}

?>