<?php
declare(strict_types=1);

namespace RestApi\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Event\EventInterface;

class ApiRestCorsComponent extends Component
{
    public static function load(Controller $controller)
    {
        $controller->loadComponent('ApiCors');
    }

    protected function getAllowedCors()
    {
        return ['http://localhost:8080', 'http://localhost:8081'];
    }

    public function beforeFilter(EventInterface $event)
    {
        /** @var Controller $controller */
        $controller = $event->getSubject();
        if ($controller) {
            $response = $controller->getResponse();
            $response->withDisabledCache();

            $responseBuilder = $response->cors($controller->getRequest());

            $allowedCors = $this->getAllowedCors();
            $currentOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
            if ($currentOrigin && in_array($currentOrigin, $allowedCors)) {
                $responseBuilder->allowOrigin([$currentOrigin])
                    ->allowCredentials();
            }
            if ($controller->getRequest()->is('options')) {
                $responseBuilder
                    ->allowMethods(['POST', 'GET', 'PATCH', 'PUT', 'DELETE'])
                    ->allowHeaders(['Authorization', 'Content-Type', 'Accept-Language'])
                    ->maxAge(3600);
                $response = $responseBuilder->build();
                $controller->setResponse($response);
                return $response;
            }
            $response = $responseBuilder->build();
            $controller->setResponse($response);
        }
    }
}