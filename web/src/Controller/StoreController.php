<?php
namespace App\Controller;


/**
 * Store Controller
 */
class StoreController extends AppController
{
    /**
     * Do Cake
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow([
            // Nenhum
        ]);
    }

    /**
     * Lista os produtos disponiveis
     * @param $action
     */
    public function products($action = null)
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
            case 'insurance':
                /**
                 *  Instancia Produto
                 */
                $product = new ProductController();

                /**
                 * Acao determinada pelo method
                 */
                switch ($this->request->getMethod())
                {
                    case 'GET':
                        $product->index($post, $action);
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

    /**
     * Acoes para o seguro
     * @param null $action
     * @throws \Exception
     */
    public function insurance($action = null, $id = null)
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
            /**
             * Venda empresa cliente
             */
            case 'buyb2c':
                /**
                 *  Instancia Order correspondente
                 */
                $order = new OrderBuyb2cController();

                /**
                 * Acao determinada pelo method
                 */
                switch ($this->request->getMethod())
                {
                    case 'POST':
                        $order->add($post);
                    break;

                    case 'DELETE':
                        $order->delete($post);
                    break;

                    case 'PUT':
                        $order->edit($post);
                    break;

                    case 'VIEW':
                        $order->view($post, $id);
                        break;

                    default:
                        $this->methodNotPermitted();
                }
            break;

            /**
             * Venda empresa cliente
             */
            case 'buyb2c-retry':
                /**
                 *  Instancia Order correspondente
                 */
                $order = new OrderBuyb2cController();

                /**
                 * Acao determinada pelo method
                 */
                switch ($this->request->getMethod())
                {
                    case 'POST':
                        $order->retry($post);
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