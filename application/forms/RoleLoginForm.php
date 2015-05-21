<?php
class Application_Form_RoleLoginForm extends Zend_Form {

	public function init() {
		$username = $this->addElement('text', 'username', array(
			'filters' => array('StringTrim', 'StringToLower'),
			'validators' => array(
					'EmailAddress',
					array('StringLength', false, array(3, 200)),
			),
			'required' => true,
			'label' => 'Your username:',
		));

		$password = $this->addElement('password', 'password', array(
			'filters' => array('StringTrim'),
			'validators' => array(
					'Alnum',
					array('StringLength', false, array(6, 20)),
			),
			'required' => true,
			'label' => 'Password:',
		));

		$login = $this->addElement('submit', 'login', array(
			'required' => false,
			'ignore' => true,
			'label' => 'Login',
		));

		// display a 'failed authentication' message if necessary;
		// so we need to add a decorator.
		$this->setDecorators(array(
			'FormElements',
			array('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form')),
			array('Description', array('placement' => 'prepend')),
			'Form'
		));
	}

	public function injectRequestValues(array $request_values) {
		unset($request_values['module']);
		unset($request_values['action']);
		unset($request_values['controller']);

		foreach ($request_values as $k => $v) {
			$this->addElement('hidden', $k, array('value' => $v));
		}
	}

}
