<?php

/**
 * CustomerFormView
 *
 * @version    1.0
 * @author     Sidnei Simmon
 */
class CustomerFormView extends TPage {

    private $form; // form
    private $contacts;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct() {
        parent::__construct();

        // Client HTTP
        $result = ZipCodeRequest::getAddressByZipCode(37704252);
        /**
          array (size=2)
          'code' => int 200
          'response' =>
          object(stdClass)[34]
          public 'zipcode' => string '37704252' (length=8)
          public 'street' => string 'Rua Trinta e Um' (length=15)
          public 'neighborhood' => string 'São José' (length=10)
          public 'city' => string 'Poços de Caldas' (length=16)
          public 'state' => string 'MG' (length=2)
         */
        // creates the form
        $this->form = new \Adianti\Wrapper\BootstrapFormBuilder('form_customer');
        $this->form->setFormTitle('Cliente Detalhes');
        $this->form->style = "width:1000px";

        // cria os campos do formulario
        $id = new \Adianti\Widget\Form\TEntry('id');
        $id->setSize('150');

        // Tipo de cliente fisica/juridica
        $type = new Adianti\Widget\Form\TCombo('type');
        $type->setDefaultOption(false);
        $type->addItems(['individual' => 'Fisica', 'corporation' => 'Jurídica']);

        $document_number = new \Adianti\Widget\Form\TEntry('document_number');

        $name = new \Adianti\Widget\Form\TEntry('name');
        $name->addValidation('<b>nome</b> é obrigatório', new \Adianti\Validator\TRequiredValidator);

        $email = new \Adianti\Widget\Form\TEntry('email');
        $email->addValidation('<b>email</b> é obrigatório', new \Adianti\Validator\TEmailValidator);

        $birthday = new Adianti\Widget\Form\TDate('birthday');
        $birthday->setMask('dd/mm/yyyy');

        $gender = new Adianti\Widget\Form\TCombo('gender');
        $gender->setDefaultOption(false);
        $gender->addItems(['M' => 'Masculino', 'F' => 'Feminino']);

        $status = new Adianti\Widget\Form\TCombo('status');
        $status->setDefaultOption(false);
        $status->addItems(['A' => 'Ativo', 'I' => 'Inativo']);

        $created_at = new Adianti\Widget\Form\TEntry('created_at');
        $created_at->setSize('150');

        $updated_at = new Adianti\Widget\Form\TEntry('updated_at');
        $updated_at->setSize('150');

        //$category_id    = new TDBCombo('category_id', 'permission', 'Category', 'id', 'name');
        //$city_id->setAction(new TAction(array('CitySeek', 'onReload')));
        // add the combo options
        // define some properties for the form fields
        $id->setEditable(false);
        $created_at->setEditable(false);
        $updated_at->setEditable(false);

        $this->form->appendPage('Dados Cadastrais');
        $this->form->addFields([new TLabel('Código')], [$id], [new TLabel('Tipo')], [$type]);
        $this->form->addFields([new TLabel('Situação')], [$status], [new TLabel('Documento')], [$document_number]);
        $this->form->addFields([new TLabel('Nome')], [$name], [new TLabel('E-mail')], [$email]);
        $this->form->addFields([new TLabel('Genero')], [$gender], [new TLabel('Data nascimento')], [$birthday]);
        $this->form->addFields([new TLabel('Data cadastro')], [$created_at], [new TLabel('Data atualizado')], [$updated_at]);
        //$this->form->addFields( [ new TLabel('Category') ], [ $category_id ], [ new TLabel('Gender') ], [ $gender ] );
        //$this->form->addFields( [ new TLabel('City') ],     [ $city_id ], [ new TLabel('Name') ], [ $city_name ] );

        $this->form->appendPage('Endereço de Entrega');

        // create the form fields
        $cep = new \Adianti\Widget\Form\TEntry('cep');
        $cep->setSize('150');


        // add the fields inside the form
        $this->form->addContent([new TLabel('Cep')], [$cep]);


        //$skill_list = new TDBCheckGroup('skill_list', 'permission', 'Skill', 'id', 'name');
        // $this->form->addFields( [ new TLabel('Skill') ],     [ $skill_list ] );


        $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addAction('Lista', new TAction(array('CustomerDataGridView', 'onReload')), 'fa:table blue');


        $this->form->appendPage('Contacts');
        $contact_type = new TEntry('contact_type[]');
        $contact_type->setSize('100%');

        $contact_value = new TEntry('contact_value[]');
        $contact_value->setSize('100%');

        $this->contacts = new TFieldList;
        $this->contacts->addField('<b>Type</b>', $contact_type);
        $this->contacts->addField('<b>Value</b>', $contact_value);
        $this->form->addField($contact_type);
        $this->form->addField($contact_value);
        $this->contacts->enableSorting();

        $this->form->addContent([new TLabel('Contacts')], [$this->contacts]);

        // wrap the page content
        $vbox = new TVBox;
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'CustomerDataGridView'));
        $vbox->add($this->form);

        // add the form inside the page
        parent::add($vbox);
    }

    /**
     * method onSave
     * Executed whenever the user clicks at the save button
     */
    public function onSave() {
        try {

            // Obtem os campos
            $customer = $this->form->getData('Customer');

            // Valida o campo documento de acordo com tipo de cliente
            $document_number = $this->form->getField('document_number');

            // Verifica o tipo de cliente
            if ($customer->type == 'corporation') {
                $document_number->addValidation('<b>documento</b> é obrigatório', new \Adianti\Validator\TCNPJValidator);
            } else {
                $document_number->addValidation('<b>documento</b> é obrigatório', new \Adianti\Validator\TCPFValidator);
            }

            $this->form->setData($customer);

            // Valçida os campos
            $this->form->validate();

            // open a transaction with database 'permission'
            TTransaction::open('permission');

            // Valida duplicidade de email e documento
            Customer::verifyUniqueFieldFromCustomer($customer, 'email');
            Customer::verifyUniqueFieldFromCustomer($customer, 'document_number');

            // trata os valores antes de gravar
            if (!empty($customer->birthday)) {
                $customer->birthday = DateNormalize::createDateFromFormat($customer->birthday, 'd/m/Y', 'Y-m-d H:i:s');
            }

            if (!empty($customer->created_at)) {
                // Atualiza o cadastro
                $customer->updated_at = date('Y-m-d H:i:s');
                $updated_at = $this->form->getField('updated_at');
                $updated_at->setValue(DateNormalize::createDateFromFormat($customer->updated_at, 'Y-m-d H:i:s', 'd/m/Y'));
                // Remove do objeto o campo created_at
                unset($customer->created_at);
            } else {
                // Data do cadastro
                $customer->created_at = date('Y-m-d H:i:s');
                $created_at = $this->form->getField('created_at');
                $created_at->setValue(DateNormalize::createDateFromFormat($customer->created_at, 'Y-m-d H:i:s', 'd/m/Y'));
            }

            if (!empty($param['contact_type']) AND is_array($param['contact_type'])) {
                foreach ($param['contact_type'] as $row => $contact_type) {
                    if ($contact_type) {
                        $contact = new Contact;
                        $contact->type = $contact_type;
                        $contact->value = $param['contact_value'][$row];

                        // add the contact to the customer
                        $customer->addContact($contact);
                    }
                }
            }

            if (!empty($param['skill_list'])) {
                foreach ($param['skill_list'] as $skill_id) {
                    // add the skill to the customer
                    $customer->addSkill(new Skill($skill_id));
                }
            }

            // stores the object in the database
            $customer->store();

            $id = $this->form->getField('id');
            $id->setValue($customer->id);

            // shows the success message
            new TMessage('info', 'Cadastro salvo com sucesso!');

            TTransaction::close(); // close the transaction
        } catch (Exception $e) { // in case of exception
            // shows the exception error message
            new TMessage('error', $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * method onEdit
     * Edit a record data
     */
    function onEdit($param) {
        try {

            if (!empty($param['id'])) {
                // open a transaction with database 'permission'
                TTransaction::open('permission');

                // load the Active Record according to its ID
                $customer = new Customer($param['id']);

                // load the contacts (composition)
                $contacts = $customer->getContacts();

                if ($contacts) {
                    $this->contacts->addHeader();
                    foreach ($contacts as $contact) {
                        $contact_detail = new stdClass;
                        $contact_detail->contact_type = $contact->type;
                        $contact_detail->contact_value = $contact->value;

                        $this->contacts->addDetail($contact_detail);
                    }

                    $this->contacts->addCloneAction();
                } else {
                    $this->onClear($param);
                }

                // load the skills (aggregation)
                $skills = $customer->getSkills();
                $skill_list = array();
                if ($skills) {
                    foreach ($skills as $skill) {
                        $skill_list[] = $skill->id;
                    }
                }
                $customer->skill_list = $skill_list;

                // Trata os campos   
                if (!empty($customer->created_at)) {
                    $customer->created_at = DateNormalize::createDateFromFormat($customer->created_at, 'Y-m-d H:i:s', 'd/m/Y');
                }

                if (!empty($customer->updated_at)) {
                    $customer->updated_at = DateNormalize::createDateFromFormat($customer->updated_at, 'Y-m-d H:i:s', 'd/m/Y');
                }

                if (!empty($customer->birthday)) {
                    $customer->birthday = DateNormalize::createDateFromFormat($customer->birthday, 'Y-m-d H:i:s', 'd/m/Y');
                }

                // Prenche o formulário com dados do cliente
                $this->form->setData($customer);

                // close the transaction
                TTransaction::close();
            } else {
                $this->onClear($param);
            }
        } catch (Exception $e) { // in case of exception
            // shows the exception error message
            new TMessage('error', $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * Clear form
     */
    public function onClear($param) {
        $this->form->clear();

        $this->contacts->addHeader();
        $this->contacts->addDetail(new stdClass);
        $this->contacts->addCloneAction();
    }

}
