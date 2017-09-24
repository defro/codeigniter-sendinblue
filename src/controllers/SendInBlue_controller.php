<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class SendInBlue_controller
 * copy this file in your /application/controllers directory
 */

class SendInBlue_controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');

        $this->load->add_package_path(FCPATH . 'vendor/defro/codeigniter-sendinblue/src');

        $this->load->config('sendinblue');

        $this->load->library('sendinblue', array(
            'sendinblue_api_key' => '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'
        ));
    }

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     *        http://example.com/index.php/sendinblue
     *    - or -
     *        http://example.com/index.php/sendinblue/index
     */
    public function index($method = 'getAccount')
    {
        $data = array('method' => $method);

        $data['sendinblue_api_key'] = $this->config->item('sendinblue_api_key');

        $methods = get_class_methods($this->sendinblue);
        foreach ($methods AS $key => $methodName) {
            if (!preg_match('/^get(.*)s$|(getAccount)/', $methodName)) {
                unset($methods[$key]);
                continue;
            }
            $data['methods'] = $methods;
        }
        sort($data['methods']);

        $data['result'] = 'Unknown method'; // by default
        if (in_array($method, $methods)) {
            try {
                $data['result'] = $this->sendinblue->$method();
            } catch (SendInBlue_Exception $e) {
                $data['result'] = 'Exception when calling SendInBlue->' . $method . ': ' . $e->getMessage();
            }
        }

        $this->load->view('sendinblue/index', $data);
    }

    public function createContact()
    {
        $rand = rand(1,99);
        $email = 'j.gaujard+' . $rand . '@gmail.com';

        $attribute = new SendinBlue\Client\Model\CreateAttribute();
        $attributes = array(
            $attribute->setName('NOM')->setValue('Gaujard ' . $rand),
            $attribute->setName('PRENOM')->setValue('Joël ' . $rand)
        );

        $contact = new SendinBlue\Client\Model\CreateContact();
        $contact
            ->setEmail($email)
            ->setEmailBlacklisted(FALSE)
            ->setSmsBlacklisted(FALSE)
            ->setListIds(array(2,5,6))
            ->setAttributes($attributes)
        ;

        try {
            $result = $this->sendinblue->createContact($contact);
        } catch (SendInBlue_Exception $e) {
            $result = 'Exception when calling SendInBlue->createContact: ' . $e->getMessage();
        }

        try {
            $control = $this->sendinblue->getContactInfo($email);
        } catch (SendInBlue_Exception $e) {
            $control = 'Exception when calling SendInBlue->getContactInfo: ' . $e->getMessage();
        }

        $this->load->view('sendinblue/create', array(
            'method' => 'createContact',
            'sent' => $contact,
            'result' => $result,
            'control' => $control,
            'methods' => array('createContact')
        ));
    }

}
