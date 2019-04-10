<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
$output = array(
    'request_id' => md5(date('YmdHis')),
    'message' => 'Apis Digi5',
    'code' => 403,
    'success' => false,
    'data' => [
        'count' => 1,
        'result' => 'Para utilização entre em contato conosco através do telefone 11 9 7599 3627 ou webmaster@digi5.com.br',
        'errors' => []
    ]
);
header('Content-Type: application/json');
header('Accept: application/json');
http_response_code(403);
echo json_encode($output);
die;