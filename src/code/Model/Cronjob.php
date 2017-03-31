<?php

class Loewenstark_OrderAddMissingName_Model_Cronjob extends Loewenstark_OrderAddMissingName_Model_Abstract
{
    protected function getConfig()
    {
        $obj = new Varien_Object();
        $obj->setInfoEMailAddress('l.weickert@loewenstark.com');
        $obj->setFrom(Mage::getStoreConfig('trans_email/ident_general/email'));
        return $obj;
    }

    public function run()
    {
        $fromDate = date('Y-m-d H:i:s', strtotime("-200 day"));

        /* Get the collection */
        $orders = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToFilter('created_at', array('from' => $fromDate))
                ->addAttributeToFilter('check_missing_names', array('neq' => 1))
        //->addAttributeToFilter('increment_id', array('eq' => '100030136')) //just for dev
        ;

        foreach ($orders as $order)
        {
            //Zend_Debug::dump($order->getId());
            //Zend_Debug::dump($order->getIncrementId());
            $this->processOrder($order);
        }
    }

    public function processOrder($order)
    {
        $billing_address = $order->getBillingAddress();
        $shipping_address = $order->getShippingAddress();

        //fix adresses
        $this->checkAdress($order, $billing_address);
        $this->checkAdress($order, $shipping_address);

        //mark order as checked
        $this->setFlag($order);
    }

    public function checkAdress($order, $address)
    {
        $names = array();
        $firstname = trim($address->getData('firstname'));
        $lastname = trim($address->getData('lastname'));

        //check if everything is okay
        if ($firstname != '' && $lastname != '')
            return true;

        //extract names
        if ($firstname == '')
        {
            $names = explode(' ', $address->getData('lastname'));
        } elseif ($lastname == '')
        {
            $names = explode(' ', $address->getData('firstname'));
        }

        if (count($names) == 2)
        {
            //we got two names :D Save it!
            Mage::log('Change adress firstname/lastname for order ' . $order->getId() . '. Old name was: ' . $firstname . ' ' . $lastname . '.', null, 'loewenstark_orderaddmissingname.log');

            $address->setData('firstname', $names[0]);
            $address->setData('lastname', $names[1]);
            $address->save();
        } else
        {
            //not enough or to many names -> Send E-Mail to store owner
            Mage::log('Could not split firstname/lastname for order ' . $order->getId() . '. Send Mail to Store Owner.', null, 'loewenstark_orderaddmissingname.log');
            $this->sendInfoMail($order);
        }
    }

    /**
     * 
     * @return $this
     */
    public function sendInfoMail($order)
    {
        $email = trim($this->getConfig()->getInfoEMailAddress());
        if (empty($email))
        {
            return $this;
        }

        $html = 'Der Vor-/Nachname konnte für die Bestellung #' . $order->getIncrementId() . ' nicht automatisch getrennt werden.';
        $subject = 'Vor- oder Nachname fehlt für Bestellung #' . $order->getIncrementId() . '';

        $mail = new Zend_Mail('UTF-8');
        $mail->setFrom($this->getConfig()->getFrom(), '');
        $mail->setBodyHtml($html);
        if (strstr($email, ';'))
        {
            $i = 0;
            foreach (explode(';', $email) as $_mail)
            {
                $_mail = trim($_mail);
                if (!empty($_mail))
                {
                    $i++;
                    if ($i == 1)
                    {
                        $mail->addTo($_mail, '');
                    } else
                    {
                        $mail->addCc($_mail, '');
                    }
                }
            }
        } else
        {
            $mail->addTo($email, '');
        }
        $mail->setSubject($subject);
        $mail->send();
    }

    public function setFlag($order)
    {
        $query = $this->_writeConnection()->update(
                $this->getTableName('sales/order'), array('check_missing_names' => 1), array('entity_id = ?' => $order->getId())
        );
    }

}
