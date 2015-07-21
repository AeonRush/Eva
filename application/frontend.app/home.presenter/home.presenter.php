<?php
namespace FrontEndApp\Presenter;
class Home extends \Eva\Presenter {
    public function index(){
        # $data = \app::model('test')->where('id = 45324')->select('*');
        $this->view->render('index');
    }
};
/// 2015 : AeonRush