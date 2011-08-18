<?php
/**
 * Modeldriven base class
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Dispatcher_Action_ModelDriven extends Anole_Dispatcher_Action_Base implements Anole_Dispatcher_Action_Interface_ModelDriven {
    private $_model;
    protected $_model_class = 'Anole_ActiveRecord_Base';
    /**
     * get the model object
     *
     * @return Anole_ActiveRecord_Base
     */
	public function wiredModel(){
		if(is_null($this->_model)){
			$this->_model = new $this->_model_class();
		}
		return $this->_model;
	}
}
/**vim:sw=4 et ts=4 **/
?>