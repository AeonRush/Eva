<?php
namespace BackEndApp\Presenter;
class Home extends \Eva\Presenter {
    public function __construct() {
        parent::__construct();
        $this->view->layout = 'frontend.app/default';
    }
    public function index(){
        $this->view->render('index');
    }
};