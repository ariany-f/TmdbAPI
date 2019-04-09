<?php
namespace App\Controller;

use App\Controller\Component\OrderComponent;
use App\Controller\Component\UtilsComponent;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use DateInterval;
use DateTime;

/**
 * Store Controller
 */
class TesteController extends AppController
{
    /**
     * Do Cake
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('GatewayMoip');
        $this->loadComponent('Order');
        $this->Auth->allow([
            'index'
        ]);
    }

    /**
     * Index method
     */
    public function index()
    {
        /**
         * So no debug
         */
        if(Configure::read('debug') != true)
        {
            die;
        }
        /***************************************************************************************************************
         * Testes
         */

        UtilsComponent::pr(UtilsComponent::server());







        /***************************************************************************************************************
         * Fim dos testes
         */
        die;
    }

    /**
     * View method
     */
    public function view($id = null)
    {
        die;
    }

    /**
     * Add method
     */
    public function add()
    {
        die;
    }

    /**
     * Edit method
     */
    public function edit($id = null)
    {
        die;
    }

    /**
     * Delete method
     */
    public function delete($id = null)
    {
        die;
    }
}
