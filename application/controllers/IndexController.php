<?php

class IndexController extends Zend_Controller_Action
{

    public function init() {

    }

    protected function getForm() {
	$formAction = $this->view->url(array(
			'controller'	=> 'index',
			'action'	=> 'process'), 'default');
    	$form = new Application_Form_RoleLoginForm(array(
    			'action' => $formAction,
    			'method' => 'post',
    	));
	return $form;
    }


    public function indexAction() {
    	$form = $this->getForm();
	/*
	 * This also injects the redirect url, namely
	 * $this->getRequest()->getParam('url')
	 */
	$form->injectRequestValues($this->getRequest()->getParams());
    	$this->view->form = $form;
    }


    public function processAction() {
    	
	$request = $this->getRequest();
	if (!$request->isPost())
		return $this->_helper->redirector('index');

    	// TODO: per ora non esegue alcun controllo e restituisce una asserzione
	// preconfezionata apposta.
    	require_once realpath(APPLICATION_PATH . '/../library/xmlseclibs/xmlseclibs.php');

    	$assertion = file_get_contents(realpath(APPLICATION_PATH . '/assertion/assertionOk2.xml'));

    	try {
    		$key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'private'));
		$config = new Zend_Config_Ini(realpath(APPLICATION_PATH . '/configs/application.ini'), 'production');
    		$key->loadKey(realpath($config->keyPath), true, false);
    		$sigNode = new XMLSecurityDSig();
    		$doc = new DOMDocument();
    		$doc->loadXML($assertion);

		/* The xml elem. to be signed: whole assertion in this case */
    		$toSign = $doc->getElementsByTagName("Assertion")->item(0);

    		$sigNode->setCanonicalMethod('http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
		/*
		 * Establish a logical link between the element to be signed
		 * and the digest of that element (aka "reference")
		 * tobe included in the signature being built.
		 * The link is a unique id. reported in the "reference"
		 * as well as added to the xml element being signed.
		 */
    		$sigNode->addReference($toSign, XMLSecurityDSig::SHA256);

    		$sigNode->add509Cert(realpath($config->certifPath), true, true);

    		$sigNode->sign($key,$toSign);

    		$sigNode->canonicalizeSignedInfo();

//$log = Zend_Registry::get('log');
//$log->log($doc->saveXML(),0);
    		$assertion64 = base64_encode($doc->saveXML());

    	} catch(Exception $e) {
		/* Erase the private key from memory as no longer needed */
		$key->loadKey(null, false, false);
		var_dump($e->getMessage());
		die();
	}
    	
	/* Erase the private key from memory as no longer needed */
    	$key->loadKey(null, false, false);

	/*
	 * Redirecting the browser carrying POST parameters requires a hack.
	 * The simple redirect (302) does not allow POST method.
	 * The hack uses a fictious form prefilled with the parameters
	 * coming from login form (except the useless or dangerous or
	 * conflicting ones) and made hidden, plus a "redirect" button
	 * being clicked by javascript (or by the user himself, if he likes).
	 * The javascript code is in views/scripts/index/process.phtml ,
	 * whereas the fictious form is in forms/RedirForm.php .
	 * https://stackoverflow.com/questions/46582/response-redirect-with-post-instead-of-get
	 * https://stackoverflow.com/questions/2865289/php-redirection-with-post-parameters
	 */
	$post = $request->getPost();
	$redirectUrl = $post['url'];
	unset($post['login']);
	unset($post['url']);
	unset($post['password']);
	$post['assertion'] = $assertion64;
    	$form = new Application_Form_RedirForm(array(
    			'action' => $redirectUrl,
    			'method' => 'post',
    			'id' => 'dirtyhack'
    	));
    	$form->injectRequestValues($post);
    	$this->view->form = $form;

    }

}
