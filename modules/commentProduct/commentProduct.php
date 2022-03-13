<?php 

if(!defined('_PS_VERSION_')) //check if VESRION is define inside app/AppKernel.php for security purpose
    return false;

require_once(_PS_MODULE_DIR_ . "commentProduct/commentProductClass.php");
class CommentProduct extends Module implements \PrestaShop\PrestaShop\Core\Module\WidgetInterface
{
    private $templateFile;

    public function __construct()
    {   
        $this->name = 'commentproduct';
        $this->author = 'aloui Mohamed Habib';
        $this->version = '1.0';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName =  $this->trans('Product comment', array(),'Modules.CommentProduct.Admin'); //name displayed in backoffice 
        $this->description = $this->trans('Allow store users to leave a comment for product', array(),'Modules.CommentProduct.Admin'); //backoffice description
        $this->ps_versions_compliancy = array( 'min' => '1.7', 'max' => _PS_VERSION_);

        $this->templateFile = 'module:commentProduct/views/templates/widget/CommentProduct.tpl'; //template route
    }
        ///////implementing methods inherited from WidgetInterface
    public function renderWidget($hookName = null , array $configuration = []) 
    {
        $this->smarty->assign($this->getWidgetVariables($hookName,$configuration));
        return $this->fetch($this->templateFile);
    }

    public function install()  //executed when module is installed (created tables needed) (override parent method)
    {
        return parent::install() &&  //execute parent install 
        Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ .'product_comments`(
            `id_comment` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` int(10) NOT NULL,
            `product_id` int(10) NOT NULL,
            `comment` varchar(255) NOT NULL,
            PRIMARY KEY (`id_comment`)
        )ENGINE= ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;'); //parent means that we are overriding parent method
    }

    public function uninstall() //executed when module is uninstalled (delete tables)
    {
        return parent::uninstall()&&
        $this->registerHook('displayFooterProduct') //hook where the module is displayed can be more then one hook
        &&
            Db::getInstance()->execute('DROP TABLE IF EXISTS`' . _DB_PREFIX_ . 'product_comments`');
    }

    public function getWidgetVariables($hookName, array $configuration)
    {   
        //handle form submit
        $this->context =Context::getContext() ;//toget loggedIN userID see below*
        $message="";
        $comments=[];
        if(Tools::isSubmit('comment'))//comment is the name of the input form(textarea in this case)
        {
            $commentProduct = new CommentProductClass(); //instance of class db model
            $commentProduct->comment=Tools::getValue('comment');
            $commentProduct->product_id=Tools::getValue('id_product');
            $commentProduct->user_id=$this->context->customer->id; //*below get the loggedIN userID
            $commentProduct->save();
            
            if($commentProduct->save())
            $message= true;
            else{
            $message=false;
            }
            //print_r(Tools::getAllValues()); Print all values that has came from postMethod //////print befor the .tpl page
         } 
         $comments= Db::getInstance()->executeS('
            SELECT * FROM `' . _DB_PREFIX_ . 'product_comments` WHERE product_id = ' . (int)Tools::getValue('id_product'));

        $sql = new DbQuery();   //symfony db query buider 
        $sql->select('*');
        $sql->from('product_comments','pc');
        $sql->innerJoin( 'customer' , 'c' , 'pc.user_id = c.id_customer');
        $sql->where('pc.product_id = ' . (int)Tools::getValue('id_product'));

      // echo '<pre>';
        //var_dump(DB::getInstance()->executeS($sql)); //var_dump display special things
      // echo '</pre>';

        return array(
            'messageResult' => $message,//Sending data to the frontend after post method and adding to DB
            'comments' => DB::getInstance()->executeS($sql)
        );
    }
}