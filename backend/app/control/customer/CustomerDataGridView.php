<?php
/**
 * CustomerDataGridView
 *
 * @version    1.0
 * @author     Sidnei Simmon
 */
class CustomerDataGridView extends TPage {

    private $form;      // search form
    private $datagrid;  // listing
    private $pageNavigation;
    private $loaded;

    /**
     * Class constructor
     * Creates the page, the search form and the listing
     */
    public function __construct() {
        parent::__construct();

        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_customer');
        $this->form->setFormTitle('Pesquisar Clientes');
        $this->form->class = 'tform';

        // create the form fields
        $id = new TEntry('id');
        $name = new TEntry('name');
        $email = new TEntry('email');
        $document_number = new TEntry('document_number');

        $name->setValue(TSession::getValue('customer_filter_id'));
        $name->setValue(TSession::getValue('customer_filter_name'));
        $email->setValue(TSession::getValue('customer_filter_email'));
        $document_number->setValue(TSession::getValue('customer_filter_document_number'));

        $this->form->addFields([new TLabel('id')], [$id], [new TLabel('Nome')], [$name]);
        $this->form->addFields([new TLabel('E-mail')], [$email], [new TLabel('Documento')], [$document_number]);

        $name->setSize('100%');
        $email->setSize('100%');
        $document_number->setSize('100%');

        $this->form->addAction('Buscar', new TAction(array($this, 'onSearch')), 'fa:search blue');
        $this->form->addAction('CSV', new TAction(array($this, 'onExportCSV')), 'fa:table');
        $this->form->addAction('Novo', new TAction(array('CustomerFormView', 'onEdit')), 'fa:plus green');

        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->datatable = 'true';
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Cód.', 'center', 50);
        $column_name = new TDataGridColumn('name', _t('Name'), 'left');
        $column_email = new TDataGridColumn('email', _t('Email'), 'left');
        $column_document_type = new TDataGridColumn('document_number', 'Documento', 'left');
        $created_at = new Adianti\Widget\Datagrid\TDataGridColumn('created_at', 'Cadastrado', 'left');
        // Formata o campo
        $created_at->setTransformer(function($field){
            iF(!empty($field)){
            $create = DateTime::createFromFormat('Y-m-d H:i:s', $field);
            return $create->format('d/m/Y H:i');
            }
            return $field;
        });

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_name);
        $this->datagrid->addColumn($column_email);
        $this->datagrid->addColumn($column_document_type);
        $this->datagrid->addColumn($created_at);

        // create EDIT action
        $action_edit = new TDataGridAction(array('CustomerFormView', 'onEdit'));
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('fa:pencil-square-o blue fa-lg');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);

        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        $action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('fa:trash-o red fa-lg');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);

        // create the datagrid model
        $this->datagrid->createModel();

        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);

        parent::add($container);
    }

    /**
     * method onSearch()
     * Register the filter in the session when the user performs a search
     */
    function onSearch() {
        // get the search form data
        $data = $this->form->getData();

        // Limpa os filtros       
        TSession::setValue('customer_filter_name', '');
        TSession::setValue('customer_filter_id', '');
        TSession::setValue('customer_filter_email', '');
        TSession::setValue('customer_filter_document_number', '');

        // Caso exista filtros
        if (!empty($data->name)) {
            TSession::setValue('customer_filter_name', $data->name);
        }

        if (!empty($data->id)) {
            TSession::setValue('customer_filter_id', $data->id);
        }

        if (!empty($data->email)) {
            TSession::setValue('customer_filter_email', $data->email);
        }

        if (!empty($data->document_number)) {
            TSession::setValue('customer_filter_document_number', $data->document_number);
        }

        // fill the form with data again
        $this->form->setData($data);

        $param = array();
        $param['offset'] = 0;
        $param['first_page'] = 1;
        $this->onReload($param);
    }

    /**
     * method onReload()
     * Load the datagrid with the database objects
     */
    function onReload($param = NULL) {
        try {
            // open a transaction with database 'permission'
            TTransaction::open('permission');

            // creates a repository for Customer
            $repository = new TRepository('Customer');
            $limit = 10;

            // creates a criteria
            $criteria = new TCriteria;

            $newparam = $param; // define new parameters
            // if (isset($newparam['order']) AND $newparam['order'] == 'city->name')
            // {
            //     $newparam['order'] = '(select name from city where city_id = id)';
            //   }
            // default order
            if (empty($newparam['order'])) {
                $newparam['order'] = 'id';
                $newparam['direction'] = 'asc';
            }

            $criteria->setProperties($newparam); // order, offset
            $criteria->setProperty('limit', $limit);

            if (TSession::getValue('customer_filter_name')) {
                // add the filter stored in the session to the criteria
                $criteria->add(new \Adianti\Database\TFilter('name', 'LIKE', "%" . TSession::getValue('customer_filter_name') . "%"));
            }
            if (TSession::getValue('customer_filter_id')) {
                // add the filter stored in the session to the criteria
                $criteria->add(new \Adianti\Database\TFilter('id', '=', (int) TSession::getValue('customer_filter_id')));
            }
            if (TSession::getValue('customer_filter_email')) {
                // add the filter stored in the session to the criteria
                $criteria->add(new \Adianti\Database\TFilter('email', 'LIKE', "%" . TSession::getValue('customer_filter_email') . "%"));
            }
            if (TSession::getValue('customer_filter_document_number')) {
                // add the filter stored in the session to the criteria
                $criteria->add(new \Adianti\Database\TFilter('document_number', 'LIKE', "%" . TSession::getValue('customer_filter_document_number') . "%"));
            }

            // load the objects according to criteria
            $customers = $repository->load($criteria, FALSE);
            $this->datagrid->clear();
            if ($customers) {
                foreach ($customers as $customer) {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($customer);
                }
            }

            // reset the criteria for record count
            $criteria->resetProperties();
            $count = $repository->count($criteria);

            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            // total row
            $row = $this->datagrid->addRow();
            $row->style = 'height: 30px; background: whitesmoke';
            $cell = $row->addCell($count . ' records');
            $cell->colspan = 6;
            $cell->style = 'text-align:center';

            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        } catch (Exception $e) { // in case of exception
            // shows the exception error message
            new TMessage('error', $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * Export to CSV
     */
    function onExportCSV() {
        $this->onSearch();

        try {
            // open a transaction with database 'permission'
            TTransaction::open('permission');

            // creates a repository for Customer
            $repository = new TRepository('Customer');

            // creates a criteria
            $criteria = new TCriteria;

            if (TSession::getValue('customer_filter1')) {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('customer_filter1'));
            }

            if (TSession::getValue('customer_filter2')) {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('customer_filter2'));
            }

            $csv = '';
            // load the objects according to criteria
            $customers = $repository->load($criteria);
            if ($customers) {
                foreach ($customers as $customer) {
                    $csv .= $customer->id . ';' .
                            $customer->name . ';' .
                            $customer->city_name . "\n";
                }
                file_put_contents('app/output/customers.csv', $csv);
                TPage::openFile('app/output/customers.csv');
            }
            // close the transaction
            TTransaction::close();
        } catch (Exception $e) { // in case of exception
            // shows the exception error message
            new TMessage('error', $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * method onDelete()
     * executed whenever the user clicks at the delete button
     * Ask if the user really wants to delete the record
     */
    function onDelete($param) {
        // define the next action
        $action1 = new TAction(array($this, 'Delete'));
        $action1->setParameters($param); // pass 'key' parameter ahead
        // shows a dialog to the user
        new TQuestion('Do you really want to delete ?', $action1);
    }

    /**
     * method Delete()
     * Delete a record
     */
    function Delete($param) {
        return;
        try {
            // get the parameter $key
            $key = $param['id'];

            // open a transaction with database 'permission'
            TTransaction::open('permission');

            // instantiates object Customer
            $customer = new Customer($key);

            // deletes the object from the database
            $customer->delete();

            // close the transaction
            TTransaction::close();

            // reload the listing
            $this->onReload($param);

            // shows the success message
            new TMessage('info', "Record Deleted");
        } catch (Exception $e) { // in case of exception
            // shows the exception error message
            new TMessage('error', $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * method show()
     * Shows the page
     */
    function show() {
        // check if the datagrid is already loaded
        if (!$this->loaded) {
            $this->onReload(func_get_arg(0));
        }
        parent::show();
    }

}
