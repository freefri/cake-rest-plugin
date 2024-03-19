<?php
declare(strict_types=1);

namespace RestApi\Lib;

use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Http\Exception\InternalErrorException;
use Cake\Routing\RouteBuilder;

abstract class RestPlugin extends BasePlugin
{
    protected string $routePath = '';

    public function __construct(array $options = [])
    {
        if (isset($options['tablePrefix'])) {
            Configure::write(
                'App.RestPlugin.' . $this->getBaseNamespace() . '.tablePrefix',
                (string)$options['tablePrefix']
            );
            unset($options['tablePrefix']);
        }
        if (isset($options['routePath'])) {
            $this->routePath = (string)$options['routePath'];
        }
        parent::__construct($options);
    }

    protected abstract function routeConnectors(RouteBuilder $builder): void;

    public function routes(RouteBuilder $routes): void
    {
        $routes->plugin(
            $this->name,
            ['path' => $this->routePath],
            function (RouteBuilder $builder) {
                $this->routeConnectors($builder);
            }
        );
        parent::routes($routes);
    }

    public static function getTablePrefix(): string
    {
        return self::getTablePrefixGeneric(self::getBaseNamespace());
    }

    public static function getTablePrefixGeneric(string $pluginNamespace): string
    {
        if ($pluginNamespace === 'RestApi') {
            throw new InternalErrorException('RestPlugin::getTablePrefix() must be called from child class');
        }
        $res = Configure::read('App.RestPlugin.' . $pluginNamespace . '.tablePrefix');
        if (!$res) {
            return '';
        }
        return $res;
    }

    private static function getBaseNamespace()
    {
        return explode('\\', get_called_class())[0] ?? '';
    }
}
