<?php
namespace App\Controller;

use App\Controller\Component\UtilsComponent;

/**
 * Helper Controller
 */
class HelperController extends AppController
{
    /**
     * Do Cake
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow([
            'index'
            // Nenhum
        ]);
    }


    public function index()
    {

    }

    /**
     * Trata dos dados auxiliares ao cadastro
     * @param $action
     */
    public function registration($action = null)
    {
        /**
         * Post json decode
         */
        $post = $this->request->input('json_decode', true);
        if(isset($post['request_id']))
        {
            $this->request_id = $post['request_id'];
        }

        switch ($action)
        {
            case 'occupation':
                /**
                 *  Instancia Occupation
                 */
                $occupation = new OccupationController();

                /**
                 * Acao determinada pelo method
                 */
                switch ($this->request->getMethod())
                {
                    case 'POST':
                        $occupation->add($post, $action);
                    break;

                    default:
                        $this->methodNotPermitted();
                }
            break;

            case 'status':
                /**
                 *  Instancia Status
                 */
                $occupation = new StatusController();

                /**
                 * Acao determinada pelo method
                 */
                switch ($this->request->getMethod())
                {
                    case 'GET':
                        $occupation->index($post, $action);
                        break;

                    default:
                        $this->methodNotPermitted();
                }
            break;

            default;
                $this->endPointActionNotExists($action);
        }

        /**
         * Saida com erro
         */
        $this->generateOutput();
    }
}