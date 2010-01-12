<?php
/**
 * Test the injector based factory.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  Kolab_Session
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_Session
 */

/**
 * Prepare the test setup.
 */
require_once dirname(__FILE__) . '/../../Autoload.php';

/**
 * Test the injector based factory.
 *
 * Copyright 2009-2010 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @category Kolab
 * @package  Kolab_Session
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_Session
 */
class Horde_Kolab_Session_Class_Factory_InjectorTest extends Horde_Kolab_Session_SessionTestCase
{
    public function setUp()
    {
        $this->injector = new Horde_Injector(new Horde_Injector_TopLevel());
        Horde_Kolab_Session_Factory_Injector::setupMockServerFactory($this->injector);
    }

    public function testMethodGetserverHasResultHordekolabserver()
    {
        $factory = new Horde_Kolab_Session_Factory_Injector(
            array('server' => array()), $this->injector
        );
        $this->assertType('Horde_Kolab_Server_Composite_Interface', $factory->getServer());
    }

    public function testMethodGetsessionauthHasResultHordekolabsessionauth()
    {
        $factory = new Horde_Kolab_Session_Factory_Injector(
            array('server' => array()), $this->injector
        );
        $this->assertType('Horde_Kolab_Session_Auth_Interface', $factory->getSessionAuth());
    }

    public function testMethodGetsessionconfigurationHasResultArray()
    {
        $factory = new Horde_Kolab_Session_Factory_Injector(
            array('server' => array()), $this->injector
        );
        $this->assertType('array', $factory->getSessionConfiguration());
    }

    public function testMethodGetsessionstorageHasResultHordekolabsessionstorage()
    {
        $factory = new Horde_Kolab_Session_Factory_Injector(
            array('server' => array()), $this->injector
        );
        $this->assertType('Horde_Kolab_Session_Storage_Interface', $factory->getSessionStorage());
    }
}