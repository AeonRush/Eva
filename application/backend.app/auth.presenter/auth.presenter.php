<?php
namespace BackEndApp\Presenter;
class Auth extends \Eva\Presenter {
    public function __construct() {
        parent::__construct();
        $this->view->layout = 'frontend.app/default';
    }
    public function index(){
        $this->render('index');
    }
};