<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;

/**
 * Class ControllerRender
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render
 */
class ControllerRender
{
    /**
     * @var null
     */
    protected $render = null;

    /**
     * Model constructor.
     * @param Render $render
     */
    public function __construct(Render $render)
    {
        $this->render = $render;
    }

    public function template()
    {
        $extensionName = $this->render->getExtensionName();
        $controllerName = $this->render->getControllerName();
        $actionName = $this->render->getActionName();

        if (!file_exists('public/typo3conf/ext/' . $extensionName . '/Classes/Controller/' . $controllerName . 'Controller.php')) {
            mkdir('public/typo3conf/ext/' . $extensionName . '/Resources/Private/Templates/' . $controllerName, 0777, true);
            file_put_contents(
                'public/typo3conf/ext/' . $extensionName . '/Classes/Controller/' . $controllerName  . 'Controller.php',
                '<?php
declare(strict_types=1);
namespace Digitalwerk\DwPageTypes\Controller;

use Digitalwerk\DwBoilerplate\Controller\ActionController;

/**
 * Class ' . $controllerName . 'Controller
 * @package Digitalwerk\DwPageTypes\Controller
 */
class ' . $controllerName . 'Controller extends ActionController
{
    /**
     * ' . ucfirst($actionName) . ' action
     */
    public function ' . $actionName . 'Action()
    {

    }
}'
            );
        } else {
            GeneralCreateCommandUtility::importStringInToFileAfterString(
                'public/typo3conf/ext/' . $extensionName . '/Classes/Controller/' . $controllerName . 'Controller.php',
                [
                    "
    /**
    * " . ucfirst($actionName) . " action
    */
    public function " . $actionName . "Action()
    {

    }
                    "
                ],
                "class " . $controllerName . "Controller extends ActionController",
                1

            );
        }
    }
}
