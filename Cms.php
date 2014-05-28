<?php
/**
 * EventManager class file.
 * @copyright (c) 2013, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\cmsBootstrap;
use yii\base\Component;

/**
 * Attaches events to all app models
 * 
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class Cms extends Component
{
    /**
     * @var EventManager app event manager component 
     */
    protected $_eventManager;
    
    public function getEventManager()
    {
        return $this->_eventManager
            ? $this->_eventManager
            : $this->_eventManager 
                = \bariew\eventManager\EventBootstrap::getEventManager(\Yii::$app);
    }
}