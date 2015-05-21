<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
        protected function _initLogging() {
                $log = new Zend_Log(new Zend_Log_Writer_Stream(
			'/tmp/roleServer.log'));
                Zend_Registry::set('log',$log);
        }
}

