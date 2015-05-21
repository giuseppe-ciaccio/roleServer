<?php
class Application_Form_RedirForm extends Zend_Form {

	public function init() {
		$login = $this->addElement('submit', 'redirect', array(
			'required' => false,
			'ignore' => true,
			'label' => 'Redirecting...(click to go immediately)',
		));
	}

	public function injectRequestValues(array $request_values) {
		foreach ($request_values as $k => $v) {
			$this->addElement('hidden', $k, array('value' => $v));
		}
	}

}
