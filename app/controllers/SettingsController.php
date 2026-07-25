<?php
class SettingsController extends Controller{
    public function __construct(){
        $this -> layout = 'main';
    }

    # Function that takes user to the index
    public function index(){
        $this -> view('settings/index', []);
    }
}