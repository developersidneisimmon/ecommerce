<?php

/**
 * Customer
 *
 * @version    1.0
 * @package    model
 * @subpackage admin
 * @author     Sidnei Simmon
 */
class Customer extends TRecord {

    const TABLENAME = 'customer';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'max'; // {max, serial}

    /**
     * Constructor method
     */

    public function __construct($id = NULL) {
        parent::__construct($id);
        parent::addAttribute('external_id');
        parent::addAttribute('type');
        parent::addAttribute('country');
        parent::addAttribute('document_type');
        parent::addAttribute('document_number');
        parent::addAttribute('name');
        parent::addAttribute('email');
        parent::addAttribute('password');
        parent::addAttribute('birthday');
        parent::addAttribute('gender');
        parent::addAttribute('status');
        parent::addAttribute('created_at');
        parent::addAttribute('updated_at');
    }

    /**
     * Valida o campo unico
     * @param Customer $customer
     * @param type $field
     * @return boolean
     * @throws Exception
     */
    public static function verifyUniqueFieldFromCustomer(Customer $customer, $field) {
        // Procura o cliente pelo campo unico
        $object = Customer::where($field, '=', $customer->{$field})->load();
        if (!empty($object[0])) {
            /**
             *  Se um cliente novo entrar com um campo unico que já existe 
             *  Ou um cliente ja cadastrado entrar com dados de outro que já existe
             */
            if (empty($customer->id) or $customer->id <> $object[0]->id) {

                switch ($field) {
                    case 'email':
                        $message = "O e-mail: " . $customer->{$field} . " já esta sendo usado!";
                        break;
                    case 'document_number':
                        $message = "O documento: " . $customer->{$field} . " já esta sendo usado!";
                        break;
                }
                throw new Exception($message);
            }
        }
        // Senão retorna verdadeiro/passou no verificação
        return true;
    }

    public function getContacts() {
        return false;
    }

    public function getSkills() {
        return false;
    }

}
